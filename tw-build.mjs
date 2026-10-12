// Regenerate public/assets/app.css from src/index.css, scanning PHP views.
// Usage: node tw-build.mjs   (requires @tailwindcss/cli devDependency)
import { execSync } from 'child_process';
execSync('./node_modules/.bin/tailwindcss -i src/index.css -o public/assets/app.css --minify', { stdio: 'inherit' });
