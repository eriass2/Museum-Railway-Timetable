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
            kind: 'busConnection',
            label: 'Anslutningsbuss:',
            cells: [
              {
                vehicles: [
                  {
                    typeName: 'Buss',
                    serviceNumber: 'B1',
                    iconKey: 'buss',
                    detail: '12:00 → Uppsala Östra',
                  },
                ],
              },
            ],
          },
        ],
      },
      {
        kind: 'branch',
        routeLabel: 'Selknä – Uppsala Östra',
        fromLabel: 'Från Selknä*',
        midLabel: 'Från Fjällnora*',
        toLabel: 'Till Uppsala Östra',
        trips: [
          {
            trip: 'B1',
            fromTime: '11:52',
            midTime: '12:00',
            toTime: '12:28',
            connectingTrains: [{ serviceNumber: '93', timeDisplay: '10:15' }],
          },
        ],
      },
    ],
  };
}
