/**
 * Verify Vite dist output without WordPress (run after `npm run build`).
 */
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import vm from 'node:vm';

const vueRoot = join(dirname(fileURLToPath(import.meta.url)), '..');
const distDir = join(vueRoot, '../../assets/dist/vue');
const manifestPath = join(distDir, '.vite/manifest.json');

function fail(message) {
  console.error(`vue verify-build: ${message}`);
  process.exit(1);
}

if (!existsSync(manifestPath)) {
  fail(`missing manifest — run "npm run build" in frontend/vue first`);
}

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
const entry = manifest['src/main.ts'] || manifest['src/main.js'];
if (!entry?.file) {
  fail('manifest has no src/main.ts entry');
}

const jsRel = entry.file.replace(/^\//, '');
const jsPath = join(distDir, jsRel);
if (!existsSync(jsPath)) {
  fail(`missing bundle file ${jsRel}`);
}

const code = readFileSync(jsPath, 'utf8');
if (!code.startsWith('(function')) {
  fail('expected IIFE bundle (starts with "(function") — check vite.config.js output.format');
}

const forbiddenInBundle = ['wizardStrings(', 'initLegacyWizard', 'jQuery is not defined'];
for (const token of forbiddenInBundle) {
  if (code.includes(token)) {
    fail(`bundle contains forbidden token: ${token}`);
  }
}

const requiredInBundle = ['data-mrt-vue-app', 'MonthCalendarApp', 'JourneyWizardApp'];
for (const token of requiredInBundle) {
  if (!code.includes(token)) {
    fail(`bundle missing expected marker: ${token}`);
  }
}

const sandbox = {
  window: {},
  document: {
    readyState: 'complete',
    querySelectorAll: () => [],
    addEventListener: () => {},
    createElement: () => ({
      style: {},
      setAttribute: () => {},
      appendChild: () => {},
      textContent: '',
    }),
    head: { appendChild: () => {} },
  },
  fetch: async () => ({ ok: false, json: async () => ({ success: false }) }),
};
sandbox.window = sandbox;

const context = vm.createContext(sandbox);

try {
  vm.runInContext(code, context, { timeout: 10000 });
} catch (err) {
  const message = err instanceof Error ? err.message : String(err);
  fail(`bundle threw on load: ${message}`);
}

console.log(`vue verify-build: OK (${jsRel}, ${(code.length / 1024).toFixed(1)} KiB)`);
