/** Minimal admin REST fixtures for static Playwright mounts. */

export function buildAdminRestResponse(pathOnly, restPrefix) {
  const rel = pathOnly.slice(restPrefix.length).replace(/^\/+/, '').replace(/\/$/, '');

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
    settings: {
      enabled: true,
      note: '',
      operator_name: '',
      ticket_url: '',
      min_transfer_minutes: 0,
      max_transfer_minutes: 120,
      max_transfers: 2,
      afternoon_return_threshold_minutes: 900,
    },
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
