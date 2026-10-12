<?php
/**
 * Minimal HTTP client — used to talk to the Turso REST API.
 *
 * Audit note: this sandbox's PHP has neither ext-curl nor ext-mbstring, and
 * Vercel Serverless PHP does not guarantee them either. Using streams keeps
 * the app portable across local dev and Vercel with zero extra extensions.
 */

declare(strict_types=1);

/** Lowercase-safe substring helper (mbstring-free). */
function str_contains_ci(string $haystack, string $needle): bool
{
    return $needle !== '' && str_contains(strtolower($haystack), strtolower($needle));
}

/**
 * POST a JSON statement to the Turso HTTP API and return decoded rows.
 *
 * @param array $stmts  e.g. [["q" => "SELECT ...", "params" => [...]]]
 * @return array        batch results: [ ["type"=>"ok","rows"=>[...]], ... ]
 */
function turso_query(array $stmts): array
{
    $token = getenv('TURSO_AUTH_TOKEN') ?: getenv('TURSO_DATABASE_TOKEN') ?: '';
    $url = rtrim((string) getenv('TURSO_DB_URL'), '/');
    if ($url === '') {
        throw new RuntimeException('TURSO_DB_URL is not set.');
    }
    // libsql-server expects the plain http(s) URL for /v2/pipeline
    $apiUrl = preg_replace('/^libsql:\/\//', 'https://', $url) . '/v2/pipeline';

    $payload = json_encode([
        'statements' => array_map(static fn($s) => [
            'stmt' => [
                'sqlite3' => [
                    'sql'    => $s['q'],
                    'params' => array_map('libsql_param', $s['params'] ?? []),
                ],
            ],
        ], $stmts),
    ]);

    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n"
                              . "Authorization: Bearer {$token}\r\n"
                              . "Accept: application/json\r\n",
            'content'       => $payload,
            'timeout'       => 8,
            'ignore_errors' => true,
        ],
    ]);

    $body = @file_get_contents($apiUrl, false, $context);
    if ($body === false) {
        throw new RuntimeException('Turso request failed (network error).');
    }
    $json = json_decode($body, true);
    if (!is_array($json) || !isset($json['results'])) {
        $err = is_array($json) ? ($json['error']['message'] ?? substr($body, 0, 200)) : substr($body, 0, 200);
        throw new RuntimeException('Turso error: ' . $err);
    }
    return $json['results'];
}

function libsql_param(mixed $v): array
{
    if (is_int($v))  return ['integer' => $v];
    if (is_float($v)) return ['float' => $v];
    if (is_null($v)) return ['null' => null];
    if (is_bool($v)) return ['integer' => $v ? 1 : 0];
    return ['text' => (string) $v];
}

/** Convert one batch result into an array of assoc-row arrays. */
function turso_rows(array $result): array
{
    if (($result['type'] ?? '') !== 'ok') {
        $msg = $result['error']['message'] ?? 'unknown statement error';
        throw new RuntimeException('Turso statement error: ' . $msg);
    }
    $rows = $result['rows'] ?? [];
    if (isset($rows['type'])) {            // {"type":"ok","cols":[..],"rows":[[..]]}
        $cols = $rows['cols'] ?? [];
        $out  = [];
        foreach ($rows['rows'] ?? [] as $r) {
            $row = [];
            foreach ($cols as $i => $col) {
                $row[$col['name'] ?? $col] = $r[$i]['value'] ?? $r[$i] ?? null;
            }
            $out[] = $row;
        }
        return $out;
    }
    return $rows;                          // already list of objects
}

/** Convenience: run one SELECT and return assoc rows. */
function turso_select(string $sql, array $params = []): array
{
    $results = turso_query([['q' => $sql, 'params' => $params]]);
    return turso_rows($results[0]);
}

/** Convenience: run one write statement. */
function turso_exec(string $sql, array $params = []): void
{
    turso_query([['q' => $sql, 'params' => $params]]);
}
