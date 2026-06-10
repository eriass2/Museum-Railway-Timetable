import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Public URL path to dist (must match plugin_dir_url + assets/dist/vue/). */
const viteBase =
  process.env.MRT_VITE_BASE ??
  '/wp-content/plugins/museum-railway-timetable/assets/dist/vue/';

export default defineConfig({
  base: viteBase,
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
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
        format: 'es',
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
      },
    },
  },
});
