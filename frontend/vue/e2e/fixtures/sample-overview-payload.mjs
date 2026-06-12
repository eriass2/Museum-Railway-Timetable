/**
 * Sample timetable overview JSON for static E2E (matches TimetableOverviewPayload shape).
 */
export function buildSampleOverviewPayload(scope = 'timetable') {
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
        columns: [
          {
            serviceNumber: '93',
            trainTypeName: 'Ånglok',
            trainTypeSlug: 'anglok',
            iconKey: 'anglok',
            isSpecial: false,
            specialName: '',
          },
        ],
        rows: [
          {
            kind: 'from',
            label: 'Från Uppsala Östra',
            cells: [{ text: '09:00' }],
          },
          {
            kind: 'station',
            label: 'Selknä*',
            cells: [{ text: '10:15' }],
          },
          {
            kind: 'busDeparture',
            label: 'Från Selknä*',
            cells: [{ text: '10:53', busServiceNumber: 'B1' }],
          },
          {
            kind: 'busArrival',
            label: 'Till Fjällnora*',
            cells: [{ text: '11:00', busServiceNumber: 'B1' }],
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
