/** Trigger a browser download from a base64-encoded zip payload. */
export function downloadBase64Zip(filename: string, contentBase64: string): void {
  downloadBase64File(filename, contentBase64, 'application/zip');
}

/** Trigger a browser download from a base64-encoded file payload. */
export function downloadBase64File(
  filename: string,
  contentBase64: string,
  mimeType: string,
): void {
  const binary = atob(contentBase64);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  const blob = new Blob([bytes], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = filename;
  anchor.click();
  URL.revokeObjectURL(url);
}

/** Trigger a browser download from a base64-encoded CSV payload. */
export function downloadBase64Csv(filename: string, contentBase64: string): void {
  downloadBase64File(filename, contentBase64, 'text/csv;charset=utf-8');
}
