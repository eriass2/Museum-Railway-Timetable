/**
 * Minimal static server for Playwright: wizard mount + Vite dist assets.
 */
import http from 'node:http';
import { readFileSync, existsSync } from 'node:fs';
import { join, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const distDir = join(root, '../../assets/dist/vue');
const manifestPath = join(distDir, '.vite/manifest.json');

if (!existsSync(manifestPath)) {
  console.error('e2e/serve: run npm run build first');
  process.exit(1);
}

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));
const entry = manifest['src/main.ts'] || manifest['src/main.js'];
const jsRel = String(entry?.file || '').replace(/^\//, '');
if (!jsRel) {
  console.error('e2e/serve: manifest missing main entry');
  process.exit(1);
}

const wizardConfig = {
  app: 'wizard',
  ajaxurl: '/wp-admin/admin-ajax.php',
  nonce: 'e2e-test',
  stations: [
    { id: 1, title: 'Uppsala' },
    { id: 2, title: 'Märsta' },
  ],
  ticketUrl: '',
  timetableId: 0,
  embedded: true,
  debug: '',
  heroSubtitle: 'E2E fixture',
  startOfWeek: 1,
  wizard: {
    stepRoute: 'Sök resa',
    stepDate: 'Datum',
    from: 'Från',
    to: 'Till',
    searchTrip: 'Sök resa',
  },
  labels: {
    routeTitle: 'Sök din resa',
    back: '← Tillbaka',
  },
};

const indexHtml = `<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="utf-8" />
  <title>MRT wizard e2e</title>
</head>
<body>
  <div class="mrt-vue-root" data-mrt-vue-app="wizard">
    <script type="application/json" class="mrt-vue-config">${JSON.stringify(wizardConfig)}</script>
  </div>
  <script src="/${jsRel}"></script>
</body>
</html>`;

const mime = {
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
};

const port = Number(process.env.MRT_E2E_PORT || 5199);

http
  .createServer((req, res) => {
    const url = req.url?.split('?')[0] || '/';
    if (url === '/' || url === '/wizard') {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(indexHtml);
      return;
    }
    const rel = url.replace(/^\//, '');
    const filePath = join(distDir, rel);
    if (!filePath.startsWith(distDir) || !existsSync(filePath)) {
      res.writeHead(404);
      res.end('Not found');
      return;
    }
    const ext = extname(filePath);
    res.writeHead(200, { 'Content-Type': mime[ext] || 'application/octet-stream' });
    res.end(readFileSync(filePath));
  })
  .listen(port, () => {
    console.log(`e2e/serve listening on http://127.0.0.1:${port}`);
  });
