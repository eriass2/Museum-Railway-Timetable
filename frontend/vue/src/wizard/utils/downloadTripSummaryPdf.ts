import type { MrtRestConfig } from '../../config/types';
import { mrtRestRequest } from '../../api/mrtRest';
import { downloadBase64Pdf } from '../../utils/downloadBase64File';
import type { TripSummaryTextInput } from './tripSummaryText';

type TripSummaryPdfResponse = {
  filename: string;
  content_base64: string;
};

export type DownloadTripSummaryPdfResult =
  | { ok: true }
  | { ok: false; message?: string };

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

/** Request a server-rendered PDF and trigger download. */
export async function downloadTripSummaryPdf(
  input: TripSummaryTextInput,
  config: MrtRestConfig,
): Promise<DownloadTripSummaryPdfResult> {
  if (typeof document === 'undefined') {
    return { ok: false };
  }

  const result = await mrtRestRequest<TripSummaryPdfResponse>(config, {
    method: 'POST',
    path: 'journey/trip-summary/pdf',
    body: input as unknown as Record<string, unknown>,
  });

  if (!result.success) {
    return { ok: false, message: result.message };
  }

  if (!result.data?.content_base64) {
    return { ok: false, message: result.message };
  }

  const filename = result.data.filename?.trim() || tripSummaryPdfFilename(input.downloadName);
  try {
    downloadBase64Pdf(filename, result.data.content_base64);
    return { ok: true };
  } catch {
    return { ok: false };
  }
}
