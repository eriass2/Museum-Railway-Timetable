import { describe, expect, it, vi } from 'vitest';
import {
  buildMrtRestUrl,
  buildMrtRestUrlFromConfig,
  resolveMrtRestBase,
  resolveMrtRestNonce,
} from '../src/api/restUrl';

describe('buildMrtRestUrl', () => {
  it('joins path under wp-json base', () => {
    const url = buildMrtRestUrl(
      'https://example.test/wp-json/museum-railway-timetable/v1/',
      'timetables/42/overview',
    );
    expect(url).toBe(
      'https://example.test/wp-json/museum-railway-timetable/v1/timetables/42/overview',
    );
  });

  it('supports plain permalinks via rest_route query param', () => {
    const url = buildMrtRestUrl(
      'https://test3.example/index.php?rest_route=/museum-railway-timetable/v1',
      'timetables/123/overview',
    );
    expect(url).toBe(
      'https://test3.example/index.php?rest_route=%2Fmuseum-railway-timetable%2Fv1%2Ftimetables%2F123%2Foverview',
    );
  });

  it('appends extra query params on rest_route installs', () => {
    const url = buildMrtRestUrl(
      'https://test3.example/index.php?rest_route=/museum-railway-timetable/v1',
      'timetables/day',
      { date: '2026-06-06', train_type: 'angtag' },
    );
    const parsed = new URL(url);
    expect(parsed.searchParams.get('rest_route')).toBe(
      '/museum-railway-timetable/v1/timetables/day',
    );
    expect(parsed.searchParams.get('date')).toBe('2026-06-06');
    expect(parsed.searchParams.get('train_type')).toBe('angtag');
  });

  it('resolveMrtRestBase uses PHP-provided restUrl unchanged', () => {
    const base = 'https://ditt-site.local/wp-json/museum-railway-timetable/v1';
    expect(resolveMrtRestBase({ restUrl: base })).toBe(`${base}/`);
  });

  it('resolveMrtRestBase falls back to same-origin when restUrl missing', () => {
    const origin = 'https://live.example';
    vi.stubGlobal('window', { location: { origin } });
    expect(resolveMrtRestBase({})).toBe(
      `${origin}/wp-json/museum-railway-timetable/v1/`,
    );
    vi.unstubAllGlobals();
  });

  it('resolveMrtRestNonce uses restNonce from config', () => {
    expect(resolveMrtRestNonce({ restNonce: 'abc' })).toBe('abc');
    expect(resolveMrtRestNonce({})).toBe('');
  });

  it('buildMrtRestUrlFromConfig joins config base and path', () => {
    const url = buildMrtRestUrlFromConfig(
      { restUrl: 'https://example.test/wp-json/museum-railway-timetable/v1/' },
      'journey/search',
    );
    expect(url).toBe(
      'https://example.test/wp-json/museum-railway-timetable/v1/journey/search',
    );
  });

  it('strips query string from path segment (use query arg instead)', () => {
    const url = buildMrtRestUrl(
      'https://test3.example/index.php?rest_route=/museum-railway-timetable/v1',
      'export/csv?include_prices=1',
      { include_settings: '0' },
    );
    const parsed = new URL(url);
    expect(parsed.searchParams.get('rest_route')).toBe(
      '/museum-railway-timetable/v1/export/csv',
    );
    expect(parsed.searchParams.get('include_settings')).toBe('0');
  });
});
