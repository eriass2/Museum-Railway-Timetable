import { describe, expect, it } from 'vitest';
import type { AdminClientConfig } from '../src/admin/types';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../src/admin/utils/adminSetupSteps';

function mockCfg(): AdminClientConfig {
  return {
    restUrl: 'http://example.test/wp-json/museum-railway-timetable/v1/',
    restNonce: 'nonce',
    initialRoute: '/dashboard',
    adminBase: 'http://example.test/wp-admin/admin.php?page=mrt_app',
    canManage: true,
    canOperate: true,
    isDevMode: false,
    trainTypeIconUrls: {},
    strings: {
      setupStepStations: 'Skapa minst en station',
      setupStepRoutes: 'Skapa minst en rutt med stationer',
      setupStepTimetables: 'Skapa en tidtabell',
      setupStepServices: 'Lägg till turer i en tidtabell',
    },
  };
}

describe('adminSetupSteps', () => {
  it('marks steps done from stats', () => {
    const steps = buildAdminSetupSteps(
      {
        stations: 1,
        routes: 0,
        timetables: 0,
        services: 0,
      },
      mockCfg(),
    );
    expect(steps[0].done).toBe(true);
    expect(steps[1].done).toBe(false);
    expect(isAdminSetupComplete(steps)).toBe(false);
  });

  it('is complete when all counts positive', () => {
    const steps = buildAdminSetupSteps(
      {
        stations: 2,
        routes: 1,
        timetables: 3,
        services: 10,
      },
      mockCfg(),
    );
    expect(isAdminSetupComplete(steps)).toBe(true);
  });
});
