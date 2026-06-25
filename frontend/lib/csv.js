/**
 * Tiny client-side CSV export — no backend needed.
 *
 *   downloadCsv('file.csv', [{ header, value: (row) => any }], rows)
 *
 * Exports exactly the rows you pass in (so callers hand it the *filtered* list).
 * Quotes/commas/newlines are escaped; a BOM is prepended so Excel reads UTF-8.
 */
function escapeCell(v) {
    if (v === null || v === undefined) return '';
    const s = String(v);
    return /["\n,]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
}

export function downloadCsv(filename, columns, rows) {
    const head = columns.map((c) => escapeCell(c.header)).join(',');
    const body = rows.map((r) => columns.map((c) => escapeCell(c.value(r))).join(',')).join('\n');
    const csv = `﻿${head}\n${body}`;

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
