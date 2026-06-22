/** Minimal admin REST fixtures for static Playwright mounts. */

const defaultSettings = {
  enabled: true,
  note: '',
  operator_name: '',
  ticket_url: '',
  min_transfer_minutes: 0,
  max_transfer_minutes: 120,
  max_transfers: 2,
  afternoon_return_threshold_minutes: 900,
};

/** @type {typeof defaultSettings} */
let settingsState = { ...defaultSettings };

const e2eTimetableDetail = {
  id: 1,
  title: 'GRÖN',
  type: 'green',
  dates: ['2026-06-01', '2026-06-08'],
  services: [
    {
      id: 101,
      service_number: '901',
      direction: 'outbound',
      start_station_id: 1,
      end_station_id: 1,
      route_id: 1,
      route_name: 'Huvudlinje',
      train_type_id: 1,
      train_type_name: 'Ångtåg',
      train_type_icon_key: 'steam',
      destination: 'Faringe',
    },
  ],
  lines: [],
  routes: [{ id: 1, title: 'Huvudlinje' }],
  train_types: [{ id: 1, name: 'Ångtåg', icon_key: 'steam' }],
};

const e2eStopTimes = {
  route_id: 1,
  stations: [
    {
      id: 1,
      name: 'Uppsala',
      sequence: 1,
      stops_here: true,
      arrival_time: '',
      departure_time: '10:00',
      pickup_mode: 'scheduled',
      dropoff_mode: 'scheduled',
      approximate_time: false,
    },
  ],
};

export function resetAdminRestFixtures() {
  settingsState = { ...defaultSettings };
}

function matchTimetableDetail(rel) {
  const m = rel.match(/^timetables\/(\d+)$/);
  return m ? Number(m[1]) : null;
}

function matchTimetableDeviations(rel) {
  const m = rel.match(/^timetables\/(\d+)\/deviations$/);
  return m ? Number(m[1]) : null;
}

function matchServiceStopTimes(rel) {
  const m = rel.match(/^services\/(\d+)\/stop-times$/);
  return m ? Number(m[1]) : null;
}

function matchServiceDeparture(rel) {
  const m = rel.match(/^services\/(\d+)\/departure$/);
  return m ? Number(m[1]) : null;
}

export function buildAdminRestResponse(pathOnly, restPrefix, options = {}) {
  const rel = pathOnly.slice(restPrefix.length).replace(/^\/+/, '').replace(/\/$/, '');
  const method = String(options.method || 'GET').toUpperCase();

  if (rel === 'settings' && (method === 'POST' || method === 'PATCH')) {
    settingsState = { ...settingsState, ...(options.body || {}) };
    return { ...settingsState };
  }

  const timetableId = matchTimetableDetail(rel);
  if (timetableId !== null) {
    return { ...e2eTimetableDetail, id: timetableId };
  }

  if (matchTimetableDeviations(rel) !== null) {
    return { rows: [] };
  }

  const serviceId = matchServiceStopTimes(rel);
  if (serviceId !== null && method === 'GET') {
    return e2eStopTimes;
  }

  if (matchServiceDeparture(rel) !== null && method === 'PUT') {
    return { saved: true };
  }

  if (rel.match(/^services\/(\d+)\/stop-times$/) && method === 'PUT') {
    return e2eStopTimes;
  }

  const routes = {
    dashboard: {
      stats: {
        stations: 2,
        routes: 1,
        timetables: 1,
        services: 3,
        train_types: 1,
        prices_configured: 1,
        stations_without_zones: 0,
      },
      warnings: [],
      next_traffic: [],
      traffic_today: null,
      links: {},
      can_manage: true,
      can_operate: true,
    },
    timetables: {
      items: [{ id: 1, title: 'GRÖN', dates_count: 5, trips_count: 3 }],
    },
    stations: {
      items: [
        {
          id: 1,
          title: 'Uppsala',
          station_type: 'station',
          bus_suffix: false,
          lat: '',
          lng: '',
          display_order: 0,
          train_change_map: {
            '71': { typeName: 'Dieseltåg', serviceNumber: '61' },
          },
        },
      ],
    },
    routes: {
      items: [
        {
          id: 1,
          title: 'Huvudlinje',
          start_station: 1,
          end_station: 1,
          station_ids: [1],
          stations: [{ id: 1, name: 'Uppsala' }],
        },
      ],
    },
    settings: { ...settingsState },
    'settings/prices': {
      matrix: {
        single: { adult: { 1: 100, 2: 150 }, child_4_15: { 1: 30, 2: 30 } },
        return: { adult: { 1: 160, 2: 220 } },
        day: { adult: { 1: 280, 2: 280 } },
      },
      ticket_types: { single: 'Enkel', return: 'Retur', day: 'Dagskort' },
      categories: { adult: 'Vuxen', child_4_15: 'Barn 4–15' },
      zones: [1, 2],
      zone_cap: 2,
      afternoon_return: { adult: 160, child_4_15: 60 },
    },
    'train-types': {
      items: [{ id: 1, name: 'Ångtåg', slug: 'angtag', icon_key: 'steam' }],
      icon_keys: ['steam', 'diesel'],
    },
  };

  if (rel in routes) {
    return routes[rel];
  }

  return null;
}
