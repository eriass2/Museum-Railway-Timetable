import { defineConfig } from 'vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Lazy-loaded IIFE: html2pdf + jsPDF + html2canvas (not in main public bundle). */
export default defineConfig({
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: path.resolve(__dirname, '../../assets/dist/vue'),
    emptyOutDir: false,
    rollupOptions: {
      input: path.resolve(__dirname, 'src/wizard/utils/pdf-vendor-entry.ts'),
      output: {
        format: 'iife',
        name: 'MRTTripPdfVendor',
        entryFileNames: 'assets/trip-pdf.js',
        inlineDynamicImports: true,
      },
    },
  },
});
