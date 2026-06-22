import type { MrtRestConfig } from '../../config/types';
import { mrtRestRequest } from '../../api/mrtRest';
import type { TripSummaryTextInput } from './tripSummaryText';

type TripSummaryPdfResponse = {
  filename: string;
  content_base64: string;
};

/** Slug for PDF download filename (ASCII-safe, preserves Swedish letters as a/o). */
export function tripSummaryPdfFilename(tripName: string): string {
  const slug = tripName
    .toLowerCase()
    .replace(/å/g, 'a')
    .replace(/ä/g, 'a')
    .replace(/ö/g, 'o')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  return `${slug || 'resa'}.pdf`;
}

function downloadBase64Pdf(filename: string, contentBase64: string): boolean {
  try {
    const binary = atob(contentBase64);
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i += 1) {
      bytes[i] = binary.charCodeAt(i);
    }
    const blob = new Blob([bytes], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = filename;
    anchor.click();
    URL.revokeObjectURL(url);
    return true;
  } catch {
    return false;
  }
}

/** Request a server-rendered PDF and trigger download. */
export async function downloadTripSummaryPdf(
  input: TripSummaryTextInput,
  config: MrtRestConfig,
): Promise<boolean> {
  if (typeof document === 'undefined') {
    return false;
  }

  const result = await mrtRestRequest<TripSummaryPdfResponse>(config, {
    method: 'POST',
    path: 'journey/trip-summary/pdf',
    body: input as unknown as Record<string, unknown>,
  });

  if (!result.success || !result.data?.content_base64) {
    return false;
  }

  const filename = result.data.filename?.trim() || tripSummaryPdfFilename(input.downloadName);
  return downloadBase64Pdf(filename, result.data.content_base64);
}
