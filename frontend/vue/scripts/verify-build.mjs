/**
 * Verify Vite dist output without WordPress (run after `npm run build`).
 */
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const vueRoot = join(dirname(fileURLToPath(import.meta.url)), '..');
const distDir = join(vueRoot, '../../assets/dist/vue');
const manifestPath = join(distDir, '.vite/manifest.json');

function readDistText(relPath) {
  const path = join(distDir, String(relPath).replace(/^\//, ''));
  if (!existsSync(path)) {
    fail(`missing dist file ${relPath}`);
  }
  return readFileSync(path, 'utf8');
}

function assertNoLegacyComponentRules(cssText, label) {
  const forbidden = [
    /\.mrt-trip-card\s*\{/,
    /\.mrt-route-layout__stations\s*\{/,
    /\.mrt-segmented\s*\{/,
    /\.mrt-detail-panel\s*\{/,
  ];
  for (const pattern of forbidden) {
    if (pattern.test(cssText)) {
      fail(`${label} still contains legacy component rule ${pattern}`);
    }
  }
}

if (!existsSync(manifestPath)) {
  fail(`missing manifest — run "npm run build" in frontend/vue first`);
}

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
const entry = manifest['src/main.ts'] ?? manifest['src/main.js'];
if (!entry?.file) {
  fail('manifest has no src/main.ts entry');
}

const jsRel = entry.file.replace(/^\//, '');
const jsPath = join(distDir, jsRel);
if (!existsSync(jsPath)) {
  fail(`missing bundle file ${jsRel}`);
}

const code = readFileSync(jsPath, 'utf8');
if (!code.includes('import') && !code.includes('export')) {
  fail('expected ES module entry (import/export) — check vite.config.ts output.format');
}

const dynamicImports = Array.isArray(entry.dynamicImports) ? entry.dynamicImports : [];
if (dynamicImports.length < 4) {
  fail(`expected 4 lazy app chunks in manifest, got ${dynamicImports.length}`);
}

for (const chunkKey of dynamicImports) {
  const chunk = manifest[chunkKey];
  if (!chunk?.file) {
    fail(`manifest missing chunk for ${chunkKey}`);
  }
  const chunkPath = join(distDir, String(chunk.file).replace(/^\//, ''));
  if (!existsSync(chunkPath)) {
    fail(`missing chunk file ${chunk.file}`);
  }
}

const forbiddenInEntry = ['wizardStrings(', 'initLegacyWizard', 'jQuery is not defined'];
for (const token of forbiddenInEntry) {
  if (code.includes(token)) {
    fail(`entry bundle contains forbidden token: ${token}`);
  }
}

const expectedBase =
  process.env.MRT_VITE_BASE ??
  '/wp-content/plugins/museum-railway-timetable/assets/dist/vue/';
if (!code.includes(expectedBase.replace(/\/$/, ''))) {
  fail(
    `entry bundle missing Vite base path (${expectedBase}) — check vite.config.ts base`,
  );
}

const requiredInEntry = ['data-mrt-vue-app', 'import('];
for (const token of requiredInEntry) {
  if (!code.includes(token)) {
    fail(`entry bundle missing expected marker: ${token}`);
  }
}

const wizardChunkKey = dynamicImports.find((key) => String(key).includes('JourneyWizardApp'));
if (wizardChunkKey) {
  const wizardChunk = manifest[wizardChunkKey];
  const wizardPath = join(distDir, String(wizardChunk.file).replace(/^\//, ''));
  const wizardCode = readFileSync(wizardPath, 'utf8');
  if (!wizardCode.includes('JourneyWizardApp') && !wizardCode.includes('mrt-journey-wizard')) {
    fail('wizard chunk missing expected wizard markers');
  }
  if (Array.isArray(entry.css) && entry.css.length > 0) {
    const mainCss = readDistText(entry.css[0]);
    assertNoLegacyComponentRules(mainCss, 'main entry CSS');
  }
  if (Array.isArray(wizardChunk.css) && wizardChunk.css.length > 0) {
    const wizardCss = readDistText(wizardChunk.css[0]);
    if (!wizardCss.includes('mrt-journey-wizard')) {
      fail('wizard chunk CSS missing .mrt-journey-wizard styles');
    }
  } else {
    fail('wizard chunk has no CSS output — scoped styles may be missing');
  }
}

const adminPath = join(distDir, 'assets/admin.js');
if (!existsSync(adminPath)) {
  fail('missing admin bundle assets/admin.js — run full npm run build');
}

const adminCode = readFileSync(adminPath, 'utf8');
const adminMarkers = ['mrt-admin-shell', 'mrt-admin-app', 'mrt-admin-async'];
for (const marker of adminMarkers) {
  if (!adminCode.includes(marker)) {
    fail(`admin bundle missing expected marker: ${marker}`);
  }
}

const tripPdfPath = join(distDir, 'assets/trip-pdf.js');
if (!existsSync(tripPdfPath)) {
  fail('missing trip-pdf.js — run full npm run build (vite.pdf.config.ts)');
}

console.log(
  `vue verify-build: OK (${jsRel}, ${(code.length / 1024).toFixed(1)} KiB entry; ${dynamicImports.length} app chunks; admin.js ${(readFileSync(adminPath, 'utf8').length / 1024).toFixed(1)} KiB; trip-pdf.js ${(readFileSync(tripPdfPath, 'utf8').length / 1024).toFixed(1)} KiB)`,
);
