import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  cleanupPrintElement,
  DEFAULT_PRINT_CLONE_ID,
  DEFAULT_PRINT_HTML_CLASS,
  printElement,
  resolvePrintSource,
} from '../src/utils/printElement';

function mockDom() {
  const htmlClassList = { add: vi.fn(), remove: vi.fn() };
  const removedIds: string[] = [];
  const appendChild = vi.fn();
  const getElementById = vi.fn((id: string) =>
    removedIds.includes(id)
      ? null
      : ({ remove: () => removedIds.push(id) } as unknown as HTMLElement),
  );
  const querySelector = vi.fn();
  const cloneNode = vi.fn(() => ({ id: '' }) as HTMLElement);

  vi.stubGlobal('document', {
    documentElement: { classList: htmlClassList },
    body: { appendChild },
    getElementById,
    querySelector,
  });

  return { htmlClassList, appendChild, querySelector, cloneNode };
}

describe('resolvePrintSource', () => {
  it('returns null for empty input', () => {
    expect(resolvePrintSource(null)).toBeNull();
    expect(resolvePrintSource(undefined)).toBeNull();
  });

  it('returns element when passed directly', () => {
    const el = {} as HTMLElement;
    expect(resolvePrintSource(el)).toBe(el);
  });
});

describe('printElement', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('calls window.print without clone when source is missing', () => {
    mockDom();
    const print = vi.fn();
    vi.stubGlobal('window', {
      print,
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
    });

    printElement(null);

    expect(print).toHaveBeenCalledOnce();
  });

  it('clones source, toggles html class, and prints', () => {
    const { htmlClassList, appendChild, cloneNode } = mockDom();
    const source = { cloneNode } as unknown as HTMLElement;
    const print = vi.fn();
    const addEventListener = vi.fn();
    vi.stubGlobal('window', {
      print,
      addEventListener,
      removeEventListener: vi.fn(),
    });

    printElement(source);

    expect(cloneNode).toHaveBeenCalledWith(true);
    expect(appendChild).toHaveBeenCalledOnce();
    expect(htmlClassList.add).toHaveBeenCalledWith(DEFAULT_PRINT_HTML_CLASS);
    expect(print).toHaveBeenCalledOnce();
    expect(addEventListener).toHaveBeenCalledWith('afterprint', expect.any(Function));
  });

  it('cleanupPrintElement removes clone and html class', () => {
    const { htmlClassList } = mockDom();
    const remove = vi.fn();
    vi.stubGlobal('document', {
      ...document,
      getElementById: vi.fn(() => ({ remove }) as unknown as HTMLElement),
      documentElement: { classList: htmlClassList },
    });

    cleanupPrintElement();

    expect(remove).toHaveBeenCalledOnce();
    expect(htmlClassList.remove).toHaveBeenCalledWith(DEFAULT_PRINT_HTML_CLASS);
  });

  it('resolvePrintSource uses querySelector for string selector', () => {
    const el = {} as HTMLElement;
    const querySelector = vi.fn(() => el);
    vi.stubGlobal('document', { querySelector });

    expect(resolvePrintSource('#trip')).toBe(el);
    expect(querySelector).toHaveBeenCalledWith('#trip');
  });

  it('afterprint handler cleans up clone and class', () => {
    const { htmlClassList, cloneNode } = mockDom();
    const source = { cloneNode } as unknown as HTMLElement;
    let afterPrintHandler: (() => void) | undefined;
    const removeEventListener = vi.fn();
    vi.stubGlobal('window', {
      print: vi.fn(),
      addEventListener: vi.fn((_event: string, handler: () => void) => {
        afterPrintHandler = handler;
      }),
      removeEventListener,
    });

    printElement(source, { cloneId: 'test-clone' });
    afterPrintHandler?.();

    expect(htmlClassList.remove).toHaveBeenCalledWith(DEFAULT_PRINT_HTML_CLASS);
    expect(removeEventListener).toHaveBeenCalledWith('afterprint', afterPrintHandler);
  });
});
