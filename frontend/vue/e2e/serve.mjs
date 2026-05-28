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

function buildWizardConfig(requestUrl) {
  const params = new URL(requestUrl, 'http://127.0.0.1').searchParams;
  const debug = params.get('debug') || '';

  const config = {
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
    debug,
    startOfWeek: 1,
    wizard: {
      stepRoute: 'Sök resa',
      stepDate: 'Välj datum',
      from: 'Från',
      to: 'Till',
      searchTrip: 'Sök resa',
      monthNames: [
        'januari',
        'februari',
        'mars',
        'april',
        'maj',
        'juni',
        'juli',
        'augusti',
        'september',
        'oktober',
        'november',
        'december',
      ],
      weekdayAbbrev: ['mån', 'tis', 'ons', 'tors', 'fre', 'lör', 'sön'],
    },
    labels: {
      routeTitle: 'Planera resa med Lennakatten',
      routeIntro: 'Välj avgång, ankomst och restyp.',
      tripSingle: 'Enkel resa',
      tripReturn: 'Tur och retur',
      back: '← Tillbaka',
      stepDate: 'Välj datum',
    },
  };

  if (debug === 'date') {
    config.wizard.debugPresets = {
      date: {
        step: 'date',
        tripType: 'single',
        from: 1,
        to: 2,
        fromTitle: 'Uppsala',
        toTitle: 'Märsta',
        calendarYear: 2026,
        calendarMonth: 5,
        calendarDays: { '2026-05-15': 'ok', '2026-05-16': 'none' },
      },
    };
  }

  return config;
}

function buildMonthConfig() {
  return {
    app: 'month',
    ajaxurl: '/wp-admin/admin-ajax.php',
    nonce: 'e2e-test',
    monthTitle: 'maj 2026',
    monthAriaLabel: 'Månadskalender, maj 2026',
    tableCaption: 'Trafikdagar för maj 2026',
    weekdayHeaders: ['mån', 'tis', 'ons', 'tors', 'fre', 'lör', 'sön'],
    weekdayFirst: 5,
    weekdayFirstSunday: 0,
    daysInMonth: 31,
    startMonday: true,
    atts: { legend: true, show_counts: true, nav: false },
    dates: {
      10: { running: true, count: 2, ymd: '2026-05-10' },
      17: { running: true, count: 1, ymd: '2026-05-17' },
    },
    strings: { loading: 'Laddar...' },
    legendServiceDay: 'Trafikdag',
    legendCountHint: 'antal per dag',
    legendClickHint: 'Klicka för att visa tidtabell',
  };
}

function renderAppHtml(app, config) {
  return `<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="utf-8" />
  <title>MRT ${app} e2e</title>
</head>
<body>
  <div class="mrt-vue-root" data-mrt-vue-app="${app}">
    <script type="application/json" class="mrt-vue-config">${JSON.stringify(config)}</script>
  </div>
  <script src="/${jsRel}"></script>
</body>
</html>`;
}

function renderWizardHtml(requestUrl) {
  return renderAppHtml('wizard', buildWizardConfig(requestUrl));
}

function renderMonthHtml() {
  return renderAppHtml('month', buildMonthConfig());
}

const mime = {
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
};

const port = Number(process.env.MRT_E2E_PORT || 5199);

http
  .createServer((req, res) => {
    const rawUrl = req.url || '/';
    const pathOnly = rawUrl.split('?')[0] || '/';
    if (pathOnly === '/' || pathOnly === '/wizard') {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(renderWizardHtml(rawUrl));
      return;
    }
    if (pathOnly === '/month') {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(renderMonthHtml());
      return;
    }
    if (pathOnly === '/wp-admin/admin-ajax.php' && req.method === 'POST') {
      res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
      res.end(
        JSON.stringify({
          success: true,
          data: { html: '<p>Tidtabell för vald dag</p>' },
        }),
      );
      return;
    }
    const rel = pathOnly.replace(/^\//, '');
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
