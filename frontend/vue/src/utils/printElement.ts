export type PrintElementOptions = {
  cloneId?: string;
  htmlClass?: string;
};

export const DEFAULT_PRINT_CLONE_ID = 'mrt-print-clone';
export const DEFAULT_PRINT_HTML_CLASS = 'mrt-print-summary';

export function resolvePrintSource(
  source: HTMLElement | string | null | undefined,
): HTMLElement | null {
  if (!source) {
    return null;
  }
  if (typeof source === 'string') {
    return document.querySelector<HTMLElement>(source);
  }
  return source;
}

export function cleanupPrintElement(options?: PrintElementOptions): void {
  const cloneId = options?.cloneId ?? DEFAULT_PRINT_CLONE_ID;
  const htmlClass = options?.htmlClass ?? DEFAULT_PRINT_HTML_CLASS;
  document.getElementById(cloneId)?.remove();
  document.documentElement.classList.remove(htmlClass);
}

/** Print a DOM subtree without the rest of the page (clone + hide siblings via CSS). */
export function printElement(
  source: HTMLElement | string | null | undefined,
  options?: PrintElementOptions,
): void {
  const cloneId = options?.cloneId ?? DEFAULT_PRINT_CLONE_ID;
  const htmlClass = options?.htmlClass ?? DEFAULT_PRINT_HTML_CLASS;
  const element = resolvePrintSource(source);
  if (!element) {
    window.print();
    return;
  }

  cleanupPrintElement({ cloneId, htmlClass });
  const clone = element.cloneNode(true) as HTMLElement;
  clone.id = cloneId;
  document.body.appendChild(clone);
  document.documentElement.classList.add(htmlClass);

  const afterPrint = () => {
    cleanupPrintElement({ cloneId, htmlClass });
    window.removeEventListener('afterprint', afterPrint);
  };
  window.addEventListener('afterprint', afterPrint);
  window.print();
}
