import { describe, expect, it } from 'vitest';
import { pickWpMediaImage } from '../src/admin/composables/useWpMediaPicker';

describe('pickWpMediaImage', () => {
  it('returns null when wp.media is unavailable', async () => {
    await expect(pickWpMediaImage({ title: 'Test', button: 'OK' })).resolves.toBeNull();
  });
});
