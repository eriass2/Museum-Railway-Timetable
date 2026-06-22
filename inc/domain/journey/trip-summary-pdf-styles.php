<?php
/**
 * Print/PDF CSS for trip summary documents.
 *
 * @package Museum_Railway_Timetable
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared stylesheet for trip summary PDF (mirrors frontend tripSummaryDocument).
 */
function MRT_trip_summary_pdf_styles(): string {
	return '
@page { margin: 0; size: A4 portrait; }
body { margin: 0; padding: 14mm 12mm; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; line-height: 1.35; color: #000; background: #fff; }
[data-mrt-pdf-root] { margin: 0; padding: 0; }
h1 { margin: 0 0 2mm; font-size: 14pt; font-weight: bold; }
.meta { margin: 0 0 5mm; padding-bottom: 3mm; border-bottom: 1px solid #ccc; font-size: 9pt; color: #333; }
.card { page-break-inside: avoid; margin: 0; padding: 3mm 4mm; border: 1px solid #bbb; }
.legs-table { width: 100%; border-collapse: separate; border-spacing: 3mm 0; margin: 0 0 3mm; table-layout: fixed; }
.legs-cell { width: 50%; vertical-align: top; padding: 0; }
.card h2 { margin: 0 0 1.5mm; font-size: 8.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.04em; color: #1f4d2e; }
.time { margin: 0 0 1mm; font-size: 11pt; font-weight: bold; }
.route { margin: 0; font-size: 9pt; color: #333; }
.segments { margin: 1.5mm 0 0; padding: 1.5mm 0 0; border-top: 1px dotted #ccc; list-style: none; padding-left: 0; }
.segments li { margin: 0 0 1mm; font-size: 8.5pt; }
.transfer { font-size: 8pt; background: #fff9c4; padding: 1mm 2mm; }
.prices { margin-top: 3mm; padding-top: 3mm; border-top: 1px solid #ccc; }
.prices h2 { margin: 0 0 2mm; font-size: 9.5pt; font-weight: bold; }
.prices h3 { margin: 0 0 1.5mm; font-size: 8.5pt; font-weight: bold; }
.price-columns-table { width: 100%; border-collapse: separate; border-spacing: 3mm 0; margin: 0 0 2mm; table-layout: fixed; }
.price-column { width: 50%; vertical-align: top; padding: 0; }
.price-table { width: 100%; border-collapse: collapse; margin: 0 0 2mm; font-size: 8.5pt; }
.price-table td { padding: 1mm 0; border-bottom: 1px solid #eee; vertical-align: top; }
.price-label { padding-right: 4mm; }
.price-value { text-align: right; white-space: nowrap; width: 28%; font-weight: bold; }
.note { margin: 2mm 0 0; font-size: 8pt; color: #444; }
';
}
