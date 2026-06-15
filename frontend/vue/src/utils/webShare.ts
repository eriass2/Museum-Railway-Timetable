export type ShareTextInput = {
  title: string;
  text: string;
};

export type ShareTextResult = 'shared' | 'copied' | 'cancelled' | 'failed';

export function canUseWebShare(): boolean {
  return typeof navigator !== 'undefined' && typeof navigator.share === 'function';
}

async function copyTextToClipboard(text: string): Promise<boolean> {
  if (typeof navigator === 'undefined' || !navigator.clipboard?.writeText) {
    return false;
  }
  try {
    await navigator.clipboard.writeText(text);
    return true;
  } catch {
    return false;
  }
}

/** Web Share when available; otherwise copy plain text to the clipboard. */
export async function shareText(input: ShareTextInput): Promise<ShareTextResult> {
  const text = input.text.trim();
  if (text === '') {
    return 'failed';
  }

  if (canUseWebShare()) {
    try {
      await navigator.share({
        title: input.title.trim() || undefined,
        text,
      });
      return 'shared';
    } catch (error) {
      if (error instanceof DOMException && error.name === 'AbortError') {
        return 'cancelled';
      }
    }
  }

  return (await copyTextToClipboard(text)) ? 'copied' : 'failed';
}
