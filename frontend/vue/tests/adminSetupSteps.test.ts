import { describe, expect, it } from 'vitest';
import type { AdminClientConfig } from '../src/admin/types';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../src/admin/utils/dashboard/adminSetupSteps';

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
      setupStepPrices: 'Konfigurera biljettpriser',
      setupStepStationZones: 'Tilldela priszoner till alla stationer',
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
        prices_configured: 1,
        stations_without_zones: 0,
      },
      mockCfg(),
    );
    expect(isAdminSetupComplete(steps)).toBe(true);
  });

  it('requires prices and station zones', () => {
    const steps = buildAdminSetupSteps(
      {
        stations: 2,
        routes: 1,
        timetables: 3,
        services: 10,
        prices_configured: 0,
        stations_without_zones: 1,
      },
      mockCfg(),
    );
    expect(steps.find((s) => s.id === 'prices')?.done).toBe(false);
    expect(steps.find((s) => s.id === 'station_zones')?.done).toBe(false);
  });
});
