import html2pdf from 'html2pdf.js';

declare global {
  interface Window {
    MRTHtml2Pdf?: typeof html2pdf;
  }
}

window.MRTHtml2Pdf = html2pdf;
