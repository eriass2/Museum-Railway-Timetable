import { afterEach, describe, expect, it, vi } from 'vitest';
import { canUseWebShare, shareText } from '../src/utils/webShare';

describe('webShare', () => {
  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  it('reports unavailable share API', () => {
    vi.stubGlobal('navigator', {});
    expect(canUseWebShare()).toBe(false);
  });

  it('copies text when Web Share is unavailable', async () => {
    const writeText = vi.fn().mockResolvedValue(undefined);
    vi.stubGlobal('navigator', { clipboard: { writeText } });
    await expect(
      shareText({ title: 'Din resa', text: 'Uppsala → Fjällnora' }),
    ).resolves.toBe('copied');
    expect(writeText).toHaveBeenCalledWith('Uppsala → Fjällnora');
  });

  it('uses Web Share when available', async () => {
    const share = vi.fn().mockResolvedValue(undefined);
    vi.stubGlobal('navigator', { share });
    await expect(shareText({ title: 'Din resa', text: 'Resa' })).resolves.toBe('shared');
    expect(share).toHaveBeenCalledWith({ title: 'Din resa', text: 'Resa' });
  });

  it('returns cancelled when user dismisses share sheet', async () => {
    const share = vi.fn().mockRejectedValue(new DOMException('aborted', 'AbortError'));
    vi.stubGlobal('navigator', { share });
    await expect(shareText({ title: 'Din resa', text: 'Resa' })).resolves.toBe('cancelled');
  });
});
