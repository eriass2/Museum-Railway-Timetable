import { describe, expect, it } from 'vitest';
import { resolveMrtDevSiteUrl } from '../src/utils/mrtDevSiteUrl';

describe('resolveMrtDevSiteUrl', () => {
  it('prefers MRT_E2E_WP_SITE_URL', () => {
    expect(
      resolveMrtDevSiteUrl({
        MRT_E2E_WP_SITE_URL: 'http://localhost:8089/',
        MRT_DEV_SITE_URL: 'http://localhost:8080',
        MRT_WP_PORT: '8081',
      }),
    ).toBe('http://localhost:8089');
  });

  it('uses MRT_DEV_SITE_URL when demo site URL is unset', () => {
    expect(
      resolveMrtDevSiteUrl({
        MRT_DEV_SITE_URL: 'http://localhost:8089',
      }),
    ).toBe('http://localhost:8089');
  });

  it('builds URL from MRT_WP_PORT', () => {
    expect(resolveMrtDevSiteUrl({ MRT_WP_PORT: '8089' })).toBe('http://localhost:8089');
  });

  it('reads port from dotenv when process env is empty', () => {
    expect(resolveMrtDevSiteUrl({}, { MRT_WP_PORT: '8089' })).toBe('http://localhost:8089');
  });

  it('defaults to port 8080', () => {
    expect(resolveMrtDevSiteUrl({})).toBe('http://localhost:8080');
  });
});
