import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Admin bundle: fixed filename (built after public bundle; do not empty outDir). */
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@mrt-assets': path.resolve(__dirname, '../../assets'),
    },
  },
  build: {
    manifest: false,
    outDir: path.resolve(__dirname, '../../assets/dist/vue'),
    emptyOutDir: false,
    rollupOptions: {
      input: path.resolve(__dirname, 'src/admin/main-admin.ts'),
      output: {
        format: 'iife',
        name: 'MRTVueAdmin',
        inlineDynamicImports: true,
        entryFileNames: 'assets/admin.js',
      },
    },
  },
});
