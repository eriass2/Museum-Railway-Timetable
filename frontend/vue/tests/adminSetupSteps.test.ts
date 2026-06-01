import { describe, expect, it } from 'vitest';
import {
  buildAdminSetupSteps,
  isAdminSetupComplete,
} from '../src/admin/utils/adminSetupSteps';

describe('adminSetupSteps', () => {
  it('marks steps done from stats', () => {
    const steps = buildAdminSetupSteps({
      stations: 1,
      routes: 0,
      timetables: 0,
      services: 0,
    });
    expect(steps[0].done).toBe(true);
    expect(steps[1].done).toBe(false);
    expect(isAdminSetupComplete(steps)).toBe(false);
  });

  it('is complete when all counts positive', () => {
    const steps = buildAdminSetupSteps({
      stations: 2,
      routes: 1,
      timetables: 3,
      services: 10,
    });
    expect(isAdminSetupComplete(steps)).toBe(true);
  });
});
