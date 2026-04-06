/**
 * Load plugin asset IIFE in Node vm (no browser).
 */
import vm from 'node:vm';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));

export const repoRoot = join(__dirname, '..', '..');

/**
 * @param {string} filename Basename under assets/, e.g. mrt-date-utils.js
 * @returns {import('node:vm').Context & { window: Record<string, unknown> }}
 */
export function loadAssetInWindow(filename) {
    const code = readFileSync(join(repoRoot, 'assets', filename), 'utf8');
    const sandbox = { window: {}, console };
    vm.createContext(sandbox);
    vm.runInContext(code, sandbox);
    return sandbox;
}
