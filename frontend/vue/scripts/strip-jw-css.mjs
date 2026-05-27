/**
 * Remove legacy .mrt-jw-* selector lines from journey-wizard CSS (Vue uses mrt-journey-wizard__*).
 * Keeps --mrt-jw-* CSS variables.
 */
import { readFileSync, writeFileSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const cssDir = join(dirname(fileURLToPath(import.meta.url)), '../../../assets/journey-wizard');

function stripJwSelectors(content) {
  const lines = content.split('\n');
  const out = [];
  for (const line of lines) {
    if (/^\s*.*\.mrt-jw-[^;{]*,\s*$/.test(line)) {
      continue;
    }
    out.push(line);
  }
  let text = out.join('\n');
  text = text.replace(
    /\/\*\*?\s*Legacy aliases:[\s\S]*?\*\/\s*\n/g,
    '/**\n * Vue public UI uses .mrt-journey-wizard__* selectors.\n */\n\n',
  );
  text = text.replace(
    /\/\*\*?\s*Journey wizard reusable UI components \(mrt-jw-\*\)\.[\s\S]*?\*\/\s*\n/,
    '/**\n * Journey wizard components (mrt-journey-wizard__*).\n */\n\n',
  );
  return text;
}

let changed = 0;
for (const file of readdirSync(cssDir)) {
  if (!file.endsWith('.css')) {
    continue;
  }
  const path = join(cssDir, file);
  const before = readFileSync(path, 'utf8');
  const after = stripJwSelectors(before);
  if (after !== before) {
    writeFileSync(path, after);
    changed++;
    console.log(`stripped: ${file}`);
  }
}
console.log(`strip-jw-css: ${changed} file(s) updated`);
