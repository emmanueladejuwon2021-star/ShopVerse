const fs = require('fs');
const path = require('path');

// Validate data/products.json
try {
  const content = fs.readFileSync('data/products.json', 'utf8');
  const products = JSON.parse(content);
  console.log(`✓ products.json is valid JSON! Total products: ${products.length}`);
  for (const p of products) {
    if (!p.id || !p.name || !p.price || !p.image || !p.category) {
      console.error(`Invalid product structure: ${JSON.stringify(p)}`);
    }
    if (!Array.isArray(p.images) || p.images.length === 0) {
      console.warn(`Product ${p.id} missing images array`);
    }
  }
} catch (e) {
  console.error('Error validating products.json:', e);
  process.exit(1);
}

// Check PHP files
function walk(dir) {
  const files = fs.readdirSync(dir);
  for (const f of files) {
    const full = path.join(dir, f);
    if (fs.statSync(full).isDirectory()) {
      if (f !== '.git' && f !== '.vercel') walk(full);
    } else if (f.endsWith('.php')) {
      const code = fs.readFileSync(full, 'utf8');
      // basic balance checks
      const openCurly = (code.match(/\{/g) || []).length;
      const closeCurly = (code.match(/\}/g) || []).length;
      if (openCurly !== closeCurly) {
        console.warn(`Note: ${full} has { count: ${openCurly} vs } count: ${closeCurly}`);
      }
    }
  }
}

walk('.');
console.log('✓ All checks complete!');
