/**
 * Sample timetable overview JSON for static E2E (matches TimetableOverviewPayload shape).
 */

function overviewColumn(serviceNumber, overrides = {}) {
  return {
    serviceNumber,
    trainTypeName: 'Dieseltåg',
    trainTypeSlug: 'diesel',
    iconKey: 'diesel',
    isSpecial: false,
    specialName: '',
    ...overrides,
  };
}

function timeCells(values) {
  return values.map((text) => ({ text }));
}

function emptyCells(count) {
  return Array.from({ length: count }, () => ({ text: '' }));
}

/** Six trip columns — matches overview-mount horizontal layout check. */
const E2E_RAIL_COLUMNS = [
  overviewColumn('71'),
  overviewColumn('72'),
  overviewColumn('93', { trainTypeName: 'Ånglok', trainTypeSlug: 'anglok', iconKey: 'anglok' }),
  overviewColumn('94'),
  overviewColumn('95'),
  overviewColumn('96'),
];

export function buildSampleOverviewPayload(scope = 'timetable') {
  const columnCount = E2E_RAIL_COLUMNS.length;
  return {
    scope,
    timetableId: 1,
    title: scope === 'day' ? 'Tidtabell för vald dag' : 'E2E tidtabell',
    dateYmd: '2026-05-25',
    timetableType: 'green',
    typeBanner: { label: 'GRÖN TIDTABELL' },
    printKey: [
      { symbol: 'X', text: 'Stannar vid av- och påstigning när någon resenär ska på eller av.' },
      { symbol: '*', text: 'Busshållplats.' },
    ],
    iconUrls: {
      bus: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
    },
    groups: [
      {
        kind: 'rail',
        routeLabel: 'Uppsala Östra – Faringe',
        fromLabel: 'Från Uppsala Östra',
        toLabel: 'Till Faringe',
        columns: E2E_RAIL_COLUMNS,
        rows: [
          {
            kind: 'from',
            label: 'Från Uppsala Östra',
            cells: timeCells(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30']),
          },
          {
            kind: 'station',
            label: 'Selknä*',
            cells: timeCells(['10:15', '10:45', '11:15', '11:45', '12:15', '12:45']),
          },
          {
            kind: 'busDeparture',
            label: 'Från Selknä*',
            cells: [{ text: '10:53', busServiceNumber: 'B1' }, ...emptyCells(columnCount - 1)],
          },
          {
            kind: 'busArrival',
            label: 'Till Fjällnora*',
            cells: [{ text: '11:00', busServiceNumber: 'B1' }, ...emptyCells(columnCount - 1)],
          },
        ],
      },
    ],
  };
}

/** Overview with one cancelled rail column and one cancelled branch trip (E2E). */
export function buildCancelledOverviewPayload(scope = 'timetable') {
  return {
    scope,
    timetableId: 2,
    title: scope === 'day' ? 'Tidtabell för vald dag' : 'E2E inställd',
    dateYmd: '2026-06-06',
    timetableType: 'green',
    typeBanner: { label: 'GRÖN TIDTABELL' },
    printKey: [
      { symbol: 'Inställd', text: 'Inställd tur — avgår inte men visas i tabellen med genomstrukna tider.' },
      { symbol: '72 (Inställd)', text: 'Inställd' },
    ],
    iconUrls: {
      bus: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
    },
    groups: [
      {
        kind: 'rail',
        routeLabel: 'Uppsala Östra – Faringe',
        fromLabel: 'Från Uppsala Östra',
        toLabel: 'Till Faringe',
        columns: [
          {
            serviceNumber: '71',
            trainTypeName: 'Dieseltåg',
            trainTypeSlug: 'diesel',
            iconKey: 'diesel',
            isSpecial: false,
            specialName: '',
            highlightColor: '',
          },
          {
            serviceNumber: '72',
            trainTypeName: 'Dieseltåg',
            trainTypeSlug: 'diesel',
            iconKey: 'diesel',
            isSpecial: false,
            specialName: '',
            highlightColor: '',
            isCancelled: true,
            deviationNotice: 'Inställd',
          },
        ],
        rows: [
          {
            kind: 'from',
            label: 'Från Uppsala Östra',
            cells: [{ text: '09:00' }, { text: '11:00' }],
          },
          {
            kind: 'to',
            label: 'Till Faringe',
            cells: [{ text: '10:35' }, { text: '12:35' }],
          },
        ],
      },
      {
        kind: 'branch',
        routeLabel: 'Selknä – Fjällnora',
        fromLabel: 'Från Selknä',
        toLabel: 'Till Fjällnora',
        trips: [
          {
            trip: 'B1',
            fromTime: '10:53',
            toTime: '11:00',
            isCancelled: true,
            deviationNotice: 'Inställd',
            connectingTrains: [],
          },
        ],
      },
    ],
  };
}
