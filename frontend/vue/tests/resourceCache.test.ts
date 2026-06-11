/**
 * @vitest-environment happy-dom
 */
import { ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { calendarMonthParams } from '../src/wizard/cache/cacheKeys';
import { createWizardResourceCache } from '../src/wizard/cache/resourceCache';

describe('createWizardResourceCache', () => {
  beforeEach(() => {
    sessionStorage.clear();
  });

  it('dedupes in-flight requests for the same key', async () => {
    const generation = ref(1);
    const cache = createWizardResourceCache(generation);
    const request = vi.fn().mockResolvedValue({ ok: true });
    const spec = {
      resource: 'calendar.month' as const,
      params: calendarMonthParams(1, 2, 'single', 2026, 6),
      request,
    };

    const [a, b] = await Promise.all([cache.load(spec), cache.load(spec)]);

    expect(a).toEqual({ ok: true });
    expect(b).toEqual({ ok: true });
    expect(request).toHaveBeenCalledTimes(1);
  });

  it('clears memory when cache generation changes', async () => {
    const generation = ref(1);
    const cache = createWizardResourceCache(generation);
    const spec = {
      resource: 'calendar.month' as const,
      params: calendarMonthParams(1, 2, 'single', 2026, 6),
      request: vi.fn().mockResolvedValue({ days: {} }),
    };
    await cache.load(spec);
    generation.value = 2;
    const request = vi.fn().mockResolvedValue({ days: { fresh: true } });
    await cache.load({ ...spec, request });

    expect(request).toHaveBeenCalledTimes(1);
  });

  it('restores session cache after refresh when generation unchanged', async () => {
    const generation = ref(3);
    const cache = createWizardResourceCache(generation);
    const spec = {
      resource: 'journey.search' as const,
      params: { leg: 'outbound', from: 1, to: 2, date: '2026-06-01', trip_type: 'single', outbound_arrival: '' },
      request: vi.fn().mockResolvedValue([{ service_id: 1 }]),
    };
    await cache.load(spec);

    const cache2 = createWizardResourceCache(ref(3));
    const request2 = vi.fn().mockResolvedValue([{ service_id: 99 }]);
    const hit = await cache2.load({ ...spec, request: request2 });

    expect(hit).toEqual([{ service_id: 1 }]);
    expect(request2).toHaveBeenCalledTimes(1);
  });
});
