export type AdminSetupStep = {
  id: string;
  label: string;
  done: boolean;
  route: string;
};

export function buildAdminSetupSteps(stats: Record<string, number>): AdminSetupStep[] {
  return [
    {
      id: 'stations',
      label: 'Skapa minst en station',
      done: (stats.stations ?? 0) > 0,
      route: '/stations-routes',
    },
    {
      id: 'routes',
      label: 'Skapa minst en rutt med stationer',
      done: (stats.routes ?? 0) > 0,
      route: '/stations-routes',
    },
    {
      id: 'timetables',
      label: 'Skapa en tidtabell',
      done: (stats.timetables ?? 0) > 0,
      route: '/timetables',
    },
    {
      id: 'services',
      label: 'Lägg till turer i en tidtabell',
      done: (stats.services ?? 0) > 0,
      route: '/timetables',
    },
  ];
}

export function isAdminSetupComplete(steps: AdminSetupStep[]): boolean {
  return steps.every((s) => s.done);
}
