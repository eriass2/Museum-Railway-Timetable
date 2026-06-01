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
      min_transfer_minutes: 3,
      max_transfer_minutes: 120,
    },
    'settings/prices': {
      matrix: { single: { adult: { 1: 100, 2: 150 } } },
      ticket_types: { single: 'Enkel' },
      categories: { adult: 'Vuxen' },
      zones: [1, 2],
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
