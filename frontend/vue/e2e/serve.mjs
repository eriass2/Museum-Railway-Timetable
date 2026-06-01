/**
 * Minimal static server for Playwright: wizard mount + Vite dist assets.
 */
import http from 'node:http';
import { readFileSync, existsSync } from 'node:fs';
import { join, dirname, extname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { buildSampleOverviewPayload } from './fixtures/sample-overview-payload.mjs';
import { buildAdminRestResponse } from './fixtures/admin-rest.mjs';
import { MRT_REST_JSON_PREFIX } from '../shared/restNamespace.mjs';

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

const adminJsRel = 'assets/admin.js';
if (!existsSync(join(distDir, adminJsRel))) {
  console.error('e2e/serve: run npm run build (admin.js missing)');
  process.exit(1);
}

const REST_PREFIX = MRT_REST_JSON_PREFIX;
const port = Number(process.env.MRT_E2E_PORT || 5199);

function restClientConfig() {
  return {
    restUrl: `http://127.0.0.1:${port}${REST_PREFIX}/`,
    restNonce: 'e2e-test',
  };
}

function buildWizardConfig(requestUrl) {
  const params = new URL(requestUrl, 'http://127.0.0.1').searchParams;
  const debug = params.get('debug') || '';

  const config = {
    app: 'wizard',
    ...restClientConfig(),
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
      priceTitle: 'Priser',
      priceDash: '—',
      priceZoneLabel: '%d zoner',
      priceTickets: { adult: 'Vuxen' },
      priceCategories: {},
      priceMatrixByZone: {
        single: { adult: { 2: 100 } },
        return: { adult: { 2: 180 } },
      },
      priceStationZones: { 1: [1], 2: [2] },
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

  const mockConnection = {
    service_id: 101,
    from_departure: '09:00',
    to_arrival: '10:30',
    duration_minutes: 90,
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

  if (debug === 'outbound') {
    config.wizard.debugPresets = {
      outbound: {
        step: 'outbound',
        tripType: 'return',
        from: 1,
        to: 2,
        fromTitle: 'Uppsala',
        toTitle: 'Märsta',
        date: '2026-05-15',
        outboundConnections: [mockConnection, { ...mockConnection, service_id: 102, from_departure: '11:00', to_arrival: '12:30' }],
      },
    };
  }

  if (debug === 'return') {
    config.wizard.debugPresets = {
      return: {
        step: 'return',
        tripType: 'return',
        from: 1,
        to: 2,
        fromTitle: 'Uppsala',
        toTitle: 'Märsta',
        date: '2026-05-15',
        outbound: mockConnection,
        returnConnections: [{ ...mockConnection, service_id: 201, from_departure: '14:00', to_arrival: '15:30' }],
      },
    };
  }

  if (debug === 'summary') {
    config.wizard.debugPresets = {
      summary: {
        step: 'summary',
        tripType: 'return',
        from: 1,
        to: 2,
        fromTitle: 'Uppsala',
        toTitle: 'Märsta',
        date: '2026-05-15',
        outbound: mockConnection,
        inbound: { ...mockConnection, service_id: 201, from_departure: '14:00', to_arrival: '15:30' },
      },
    };
  }

  return config;
}

function buildMonthConfig() {
  return {
    app: 'month',
    ...restClientConfig(),
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
      5: { running: false, count: 0, ymd: '2026-05-05' },
      10: { running: true, count: 2, ymd: '2026-05-10' },
      17: { running: true, count: 1, ymd: '2026-05-17' },
    },
    strings: { loading: 'Laddar...', errorGeneric: 'Kunde inte ladda tidtabellen.' },
    legendServiceDay: 'Trafikdag',
    legendCountHint: 'antal per dag',
    legendClickHint: 'Klicka för att visa tidtabell',
  };
}

function buildOverviewConfig() {
  return {
    app: 'overview',
    ...restClientConfig(),
    timetableId: 1,
    strings: { loading: 'Laddar...', errorGeneric: 'Kunde inte ladda översikten.' },
  };
}

function readRequestBody(req) {
  return new Promise((resolve) => {
    const chunks = [];
    req.on('data', (chunk) => chunks.push(chunk));
    req.on('end', () => resolve(Buffer.concat(chunks).toString('utf8')));
  });
}

function buildAdminClientConfig() {
  return {
    restUrl: `http://127.0.0.1:${port}${REST_PREFIX}/`,
    restNonce: 'e2e-test',
    initialRoute: 'dashboard',
    adminBase: `http://127.0.0.1:${port}/admin?page=mrt_app`,
    canManage: true,
    canOperate: true,
    isDevMode: true,
  };
}

function renderAdminHtml() {
  const config = buildAdminClientConfig();
  return `<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="utf-8" />
  <title>MRT admin e2e</title>
</head>
<body class="wp-admin">
  <div class="wrap mrt-admin-vue-wrap">
    <div id="mrt-admin-app" data-mrt-admin-app="1"></div>
  </div>
  <script>window.mrtAdminVue = ${JSON.stringify(config)};</script>
  <script src="/${adminJsRel}"></script>
</body>
</html>`;
}

async function handleRestRequest(req, res, pathOnly, requestUrl) {
  const query = new URL(requestUrl, 'http://127.0.0.1').searchParams;
  if (query.get('fail') === 'rest') {
    res.writeHead(403, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(JSON.stringify({ message: 'REST-fel (e2e)' }));
    return;
  }

  const adminPayload = buildAdminRestResponse(pathOnly, REST_PREFIX);
  if (adminPayload !== null) {
    res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(JSON.stringify(adminPayload));
    return;
  }

  let payload = { overview: buildSampleOverviewPayload('timetable') };
  if (pathOnly.endsWith('/timetables/day')) {
    payload = { overview: buildSampleOverviewPayload('day') };
  }

  res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
  res.end(JSON.stringify(payload));
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

function renderMonthHtml(requestUrl) {
  const config = buildMonthConfig();
  const params = new URL(requestUrl, 'http://127.0.0.1').searchParams;
  if (params.get('fail') === 'rest') {
    config.restUrl = `${config.restUrl}?fail=rest`;
  }
  return renderAppHtml('month', config);
}

function renderOverviewHtml() {
  return renderAppHtml('overview', buildOverviewConfig());
}

const mime = {
  '.js': 'application/javascript',
  '.css': 'text/css',
  '.json': 'application/json',
};

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
      res.end(renderMonthHtml(rawUrl));
      return;
    }
    if (pathOnly === '/overview') {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(renderOverviewHtml());
      return;
    }
    if (pathOnly === '/admin') {
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(renderAdminHtml());
      return;
    }
    if (pathOnly.startsWith(REST_PREFIX)) {
      void handleRestRequest(req, res, pathOnly, rawUrl);
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
