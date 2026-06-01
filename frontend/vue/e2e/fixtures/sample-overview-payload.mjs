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
    iconUrls: {},
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
