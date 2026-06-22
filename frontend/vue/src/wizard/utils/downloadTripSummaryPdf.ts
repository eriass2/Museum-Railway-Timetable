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

/** Fallback when the server omits filename (slugging is server-side only). */
export const TRIP_SUMMARY_PDF_FALLBACK_FILENAME = 'resa.pdf';

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

  const filename = result.data.filename?.trim() || TRIP_SUMMARY_PDF_FALLBACK_FILENAME;
  try {
    downloadBase64Pdf(filename, result.data.content_base64);
    return { ok: true };
  } catch {
    return { ok: false };
  }
}
