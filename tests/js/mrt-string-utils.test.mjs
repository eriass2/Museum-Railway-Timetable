import { test, describe } from 'node:test';
import assert from 'node:assert/strict';
import { loadAssetInWindow } from './load-asset.mjs';

describe('MRTStringUtils', () => {
    const { window } = loadAssetInWindow('mrt-string-utils.js');
    const SU = window.MRTStringUtils;

    test('escapeHtml escapes special characters', () => {
        assert.equal(SU.escapeHtml('<a & "'), '&lt;a &amp; &quot;');
        assert.equal(SU.escapeHtml(">'"), '&gt;&#039;');
    });

    test('escapeHtml null becomes empty string', () => {
        assert.equal(SU.escapeHtml(null), '');
    });
});
