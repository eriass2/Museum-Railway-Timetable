import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@mrt-assets': path.resolve(__dirname, '../../assets'),
    },
  },
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, '../../assets/dist/vue'),
    emptyOutDir: true,
    rollupOptions: {
      input: path.resolve(__dirname, 'src/main.ts'),
      output: {
        // WordPress enqueues a classic <script> (no type="module"); IIFE avoids export syntax errors.
        format: 'iife',
        name: 'MRTVuePublic',
        inlineDynamicImports: true,
        entryFileNames: 'assets/[name]-[hash].js',
      },
    },
  },
});
