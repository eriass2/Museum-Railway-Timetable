import html2pdf from 'html2pdf.js';
import type { Html2PdfFn } from './loadHtml2Pdf';

window.MRTHtml2Pdf = html2pdf as Html2PdfFn;
