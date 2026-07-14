import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { useMediaQuery } from '@mantine/hooks';
import { notifications } from '@mantine/notifications';
import {
    Box, Group, Stack, Text, Button, ThemeIcon, TextInput, ActionIcon, ScrollArea,
    Select, Textarea, NumberInput, Checkbox, Menu, Modal,
} from '@mantine/core';
import {
    IconBox, IconSearch, IconPlus, IconX, IconCheck, IconPill, IconShieldLock,
    IconChevronRight, IconChevronDown, IconArrowsSort, IconUser, IconDotsVertical,
    IconBarcode, IconDownload, IconRefresh, IconTypography,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';
import { palette, statusPalette } from '@frontend/tokens';
import { MedTile, CdTag, StockStatusChip } from '@frontend/components/MedStockAtoms';
import { FONTS, useBodyFont, setBodyFont, useHeadingFont, setHeadingFont, HEADING_FONT } from '@frontend/lib/font';

/* ══════════════════════════════════════════════════════════════════════
   PREMIUM PREVIEW — a from-scratch rebuild of the stock page under the
   Apple-minimalist bar. Same data as /frontend2/stock-2, so open both to
   compare. Every recommendation is applied here:
     · header stripped to one primary action + an overflow menu
     · a single tidy control strip; status pills as the one colour lens
     · "In stock" rows carry NO chip — colour only marks problems
     · hand-tuned muted progress bars (no Mantine brights)
     · generous padding, hairline dividers, NO zoom hack
     · centred table + a calm, redesigned detail panel
   ═══════════════════════════════════════════════════════════════════════ */

const ADJUST = '/frontend2/stock/adjust';

// ── Design tokens — from the unified palette in frontend/tokens.js (single source) ──
const { ink: INK, ink2: INK2, faint: FAINT, line: LINE } = palette;
const numeric = { fontVariantNumeric: 'tabular-nums', fontFeatureSettings: '"tnum" 1' };
const card = {
    background: palette.cardBg,
    borderRadius: 18,
    border: '1px solid light-dark(#ECEEF2, var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,29,54,0.04), 0 18px 40px -30px rgba(16,29,54,0.42)',
};
const cap = { fontSize: 10, fontWeight: 700, letterSpacing: 0.7, textTransform: 'uppercase' };

// ── Stock health (muted hues; healthy is intentionally quiet) ─────────
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
function bucketOf(m) {
    if (m.expired) return 'expired';
    if (isOut(m)) return 'out';
    if (m.low) return 'low';
    if (m.expiring_soon) return 'expiring';
    return 'healthy';
}
// Unified 5-state status system (single source: frontend/tokens.js).
const STATUS = statusPalette;

// Thin muted stock bar — colour follows the health bucket.
function barOf(m) {
    const s = m.stock_level;
    if (s === null || s === undefined) return { pct: 0, hex: '#C3CBD6' };
    const n = Number(s);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    const b = bucketOf(m);
    const hex = b === 'expired' || b === 'out' ? '#B4544A' : b === 'low' ? '#BF8A3C' : b === 'expiring' ? '#8A6FAE' : '#3E8E77';
    return { pct, hex };
}

// ── Days-of-stock forecast (same heuristic as Stock 2) ────────────────
function parseTxnDate(s) { if (!s) return null; const t = Date.parse(s); return Number.isNaN(t) ? null : t; }
function computeForecast(med, transactions) {
    const stock = med.stock_level === null || med.stock_level === undefined ? null : Number(med.stock_level);
    if (stock === null || stock <= 0) return null;
    let total = 0, min = Infinity, max = -Infinity, events = 0;
    for (const t of transactions) {
        if (t.type !== 'administered' || t.medication_name !== med.medication_name || t.quantity == null) continue;
        const d = parseTxnDate(t.date);
        if (d === null) continue;
        total += Math.abs(Number(t.quantity)) || 0;
        min = Math.min(min, d); max = Math.max(max, d); events += 1;
    }
    if (events < 2 || total <= 0) return null;
    const spanDays = Math.max(1, (max - min) / 86400000);
    const perDay = total / spanDays;
    if (perDay <= 0) return null;
    return { perDay, daysLeft: Math.floor(stock / perDay), basisDays: Math.round(spanDays) };
}
const forecastTone = (d) => (d <= 7 ? '#B4544A' : d <= 14 ? '#BF8A3C' : INK2);

const TXN = {
    received:     { label: 'Received',     c: '#3E8E77', sign: '+' },
    disposed:     { label: 'Disposed',     c: '#A6506A', sign: '−' },
    returned:     { label: 'Returned',     c: '#4E6B9A', sign: '−' },
    correction:   { label: 'Correction',   c: '#9C6B22', sign: '±' },
    administered: { label: 'Administered', c: '#8A6FAE', sign: '−' },
};
const ADJUST_TYPES = [
    { value: 'received', label: 'Received (stock in)' },
    { value: 'disposed', label: 'Disposed' },
    { value: 'returned', label: 'Returned' },
    { value: 'correction', label: 'Correction' },
];

// ── Small shared pieces (from the design-system atoms; StatusMark = StockStatusChip) ──
const StatusMark = StockStatusChip;
const brandedToast = (message, hex = '#1F9E93', icon = <IconCheck size={20} stroke={3} />) => notifications.show({
    message, autoClose: 3500, withBorder: true, icon,
    styles: {
        root: { padding: '16px 22px', minWidth: 340, borderRadius: 22, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: hex, boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
        icon: { backgroundColor: hex, width: 38, height: 38, borderRadius: '50%' },
        body: { marginInlineStart: 8 }, description: { color: '#FFFFFF', fontSize: 15, fontWeight: 650 }, closeButton: { color: 'rgba(255,255,255,0.65)' },
    },
});

// ── Redesigned detail panel — one calm column, whitespace over dividers ─
function MedPanel({ med, transactions, canAdjust, onClose }) {
    const bucket = bucketOf(med);
    const bar = barOf(med);
    const f = computeForecast(med, transactions);
    const movements = transactions.filter((t) => t.medication_name === med.medication_name);
    const form = useForm({
        mar_sheet_id: med.id, transaction_type: 'received', quantity: '', expiry_date: '',
        is_controlled: !!med.is_controlled, cd_schedule: med.cd_schedule ?? '',
        reason: '', disposal_method: '', witness_name: '', notes: '', batch_number: '', supplier: '',
    });
    const isReceived = form.data.transaction_type === 'received';
    const submit = () => form.post(ADJUST, {
        preserveScroll: true,
        onSuccess: () => { brandedToast(`${med.medication_name} stock updated.`); form.reset(); onClose(); },
    });
    const Section = ({ label, children, mt = 24 }) => (
        <Box mt={mt}>
            <Text style={cap} c={FAINT} mb={12}>{label}</Text>
            {children}
        </Box>
    );

    return (
        <Box style={{ ...card, padding: '22px 22px', width: '100%' }}>
            {/* Identity */}
            <Group justify="space-between" wrap="nowrap" align="flex-start">
                <Group gap={12} wrap="nowrap" style={{ minWidth: 0 }}>
                    <MedTile controlled={med.is_controlled} size={44} radius={13} icon={21} />
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                            <Text fz={15} fw={650} c={INK} truncate style={{ letterSpacing: -0.2, fontFamily: HEADING_FONT }}>{med.medication_name}</Text>
                            {med.is_controlled && <CdTag schedule={med.cd_schedule} />}
                        </Group>
                        <Text fz={12} c={FAINT} truncate mt={2}>{med.resident ?? 'No resident linked'}</Text>
                    </Box>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="xl" onClick={onClose} aria-label="Close"><IconX size={18} /></ActionIcon>
            </Group>

            {/* Hero stat */}
            <Group justify="space-between" align="flex-end" mt={22} mb={12} wrap="nowrap">
                <Box>
                    <Text fz={40} fw={600} c={INK} lh={1} style={{ ...numeric, letterSpacing: -1.2, fontFamily: HEADING_FONT }}>
                        {med.stock_level ?? '—'} <Text span fz={15} c={FAINT} fw={500}>{med.unit ?? 'units'}</Text>
                    </Text>
                    <Text fz={12} c={FAINT} mt={6}>Reorder at {med.reorder_level ?? '—'}</Text>
                </Box>
                <StatusMark bucket={bucket} />
            </Group>
            <Box style={{ height: 6, borderRadius: 999, background: 'light-dark(#F0F2F6, var(--mantine-color-dark-5))', overflow: 'hidden' }}>
                <Box style={{ width: `${bar.pct}%`, height: '100%', borderRadius: 999, background: bar.hex, opacity: 0.9 }} />
            </Box>

            {/* Forecast (only when we can trust a rate) */}
            {f && (
                <Box mt={16} style={{ borderRadius: 12, padding: '12px 14px', background: 'light-dark(#F7F9FB, rgba(255,255,255,0.03))', border: `1px solid ${LINE}` }}>
                    <Group justify="space-between" align="center" wrap="nowrap">
                        <Box>
                            <Text style={cap} c={FAINT} mb={3}>Stock cover</Text>
                            <Text fz={19} fw={650} c={forecastTone(f.daysLeft)} lh={1.1} style={numeric}>≈ {f.daysLeft} day{f.daysLeft === 1 ? '' : 's'} left</Text>
                        </Box>
                        <Text fz={11} c={FAINT} ta="right">{f.perDay.toFixed(1)} {med.unit ?? 'units'}/day<br />over {f.basisDays} day{f.basisDays === 1 ? '' : 's'}</Text>
                    </Group>
                </Box>
            )}

            {/* Details — no divider lines, just a quiet grid */}
            <Section label="Details">
                <Group gap={0} grow align="flex-start">
                    <Box><Text fz={11} c={FAINT} mb={3}>Expiry</Text><Text fz={13.5} fw={600} c={med.expired ? '#B4544A' : med.expiring_soon ? '#BF8A3C' : INK} style={numeric}>{med.expiry_date ?? '—'}</Text></Box>
                    <Box><Text fz={11} c={FAINT} mb={3}>Controlled</Text><Text fz={13.5} fw={600} c={INK}>{med.is_controlled ? `Yes${med.cd_schedule ? ` · ${med.cd_schedule}` : ''}` : 'No'}</Text></Box>
                </Group>
            </Section>

            {/* Batches */}
            {med.batches && med.batches.length > 0 && (
                <Section label="Batches · earliest expiry used first">
                    <Stack gap={8}>
                        {med.batches.map((b, i) => (
                            <Group key={b.id} justify="space-between" wrap="nowrap" gap={8}
                                style={{ padding: '8px 12px', borderRadius: 10,
                                    background: i === 0 ? 'light-dark(#EAF4EF, rgba(62,142,119,0.12))' : 'light-dark(#F7F9FB, rgba(255,255,255,0.03))',
                                    border: i === 0 ? '1px solid light-dark(#CDE6DA, rgba(62,142,119,0.28))' : `1px solid ${LINE}` }}>
                                <Box style={{ minWidth: 0 }}>
                                    <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                                        <Text fz={12.5} fw={600} c={INK} truncate>{b.batch_number || 'No lot no.'}</Text>
                                        {i === 0 && <Box style={{ flexShrink: 0, display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 10, fontWeight: 700, color: '#2F7D5B', background: 'light-dark(#EAF4EF, rgba(62,142,119,0.16))', borderRadius: 6, padding: '1px 6px' }}><Box w={5} h={5} style={{ borderRadius: '50%', background: '#3E8E77' }} />Use next</Box>}
                                    </Group>
                                    <Text fz={11} c={FAINT} truncate>{b.expiry_date ? `Exp ${b.expiry_date}` : 'No expiry'}{b.supplier ? ` · ${b.supplier}` : ''}</Text>
                                </Box>
                                <Text fz={13} fw={650} c={INK} style={{ flexShrink: 0, ...numeric }}>{b.quantity}{med.unit ? ` ${med.unit}` : ''}</Text>
                            </Group>
                        ))}
                    </Stack>
                </Section>
            )}

            {/* Adjust (managers) */}
            {canAdjust && (
                <Section label="Adjust stock">
                    <Stack gap={10}>
                        <Select label="Type" data={ADJUST_TYPES} value={form.data.transaction_type} onChange={(v) => form.setData('transaction_type', v)} comboboxProps={{ withinPortal: true }} />
                        <Group grow gap={10} align="flex-start">
                            <NumberInput label="Quantity" placeholder="e.g. 28" min={0} value={form.data.quantity} onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} description="Blank = details only" />
                            <TextInput label={isReceived ? 'Batch expiry' : 'New expiry'} type="date" value={form.data.expiry_date} onChange={(e) => form.setData('expiry_date', e.currentTarget.value)} error={form.errors.expiry_date} />
                        </Group>
                        {isReceived && (
                            <Group grow gap={10}>
                                <TextInput label="Batch / lot no." placeholder="On the pack" value={form.data.batch_number} onChange={(e) => form.setData('batch_number', e.currentTarget.value)} />
                                <TextInput label="Supplier" placeholder="Supplying pharmacy" value={form.data.supplier} onChange={(e) => form.setData('supplier', e.currentTarget.value)} />
                            </Group>
                        )}
                        <Checkbox label="Controlled drug" checked={form.data.is_controlled} onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)} />
                        {form.data.is_controlled && (
                            <Group grow gap={10}>
                                <TextInput label="CD schedule" placeholder="e.g. 2, 3" value={form.data.cd_schedule} onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)} />
                                <TextInput label="Witness" placeholder="Second staff member" value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} />
                            </Group>
                        )}
                        <Textarea label="Notes / reason" placeholder="Optional note about this movement…" autosize minRows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                        <Button radius={10} loading={form.processing} onClick={submit} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#13233F, #45C1BF)', color: '#fff' }}>Save adjustment</Button>
                    </Stack>
                </Section>
            )}

            {/* Movements */}
            <Section label={`Recent movements · ${movements.length}`}>
                {movements.length === 0
                    ? <Text fz="xs" c={FAINT}>No stock movements recorded for this medication.</Text>
                    : (
                        <ScrollArea.Autosize mah={220} type="hover" offsetScrollbars>
                            <Stack gap={12} pr={6}>
                                {movements.map((t) => {
                                    const meta = TXN[t.type] || { label: t.type, c: '#98A1AB', sign: '' };
                                    return (
                                        <Group key={t.id} gap={9} wrap="nowrap" align="flex-start">
                                            <Box w={7} h={7} style={{ borderRadius: '50%', background: meta.c, flexShrink: 0, marginTop: 5 }} />
                                            <Box style={{ minWidth: 0, flex: 1 }}>
                                                <Group justify="space-between" wrap="nowrap" gap={8}>
                                                    <Text fz={12.5} c={INK} lh={1.3}><Text span fw={600} c={meta.c}>{meta.label}</Text>{t.performed_by ? ` · ${t.performed_by}` : ''}</Text>
                                                    <Text fz={12.5} fw={650} c={meta.c} style={{ flexShrink: 0, ...numeric }}>{meta.sign}{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text>
                                                </Group>
                                                <Text fz={11} c={FAINT} style={numeric}>{t.date}{t.balance_after != null ? ` · balance ${t.balance_after}` : ''}</Text>
                                                {t.reason && <Text fz={11.5} c={INK2} lh={1.35}>“{t.reason}”</Text>}
                                            </Box>
                                        </Group>
                                    );
                                })}
                            </Stack>
                        </ScrollArea.Autosize>
                    )}
            </Section>
        </Box>
    );
}

export default function Stock({ meds = [], transactions = [], residents = [] }) {
    const role = useRole();
    const canAdjust = role === 'manager';
    const railBelow = useMediaQuery('(max-width: 1000px)');
    const isSm = useMediaQuery('(max-width: 576px)');

    const [tab, setTab] = useState('all');
    const [query, setQuery] = useState('');
    const [residentF, setResidentF] = useState('any');
    const [cdF, setCdF] = useState('all');
    const [sort, setSort] = useState('medication-asc');
    const [selected, setSelected] = useState(null);
    const [panelMed, setPanelMed] = useState(null);
    const [scanOpen, setScanOpen] = useState(false);
    const [scan, setScan] = useState('');
    const searchRef = useRef(null);

    // Site-wide font preferences (shared store; apply to the whole app).
    const headingFont = useHeadingFont();
    const bodyFont = useBodyFont();

    useEffect(() => {
        const onKey = (e) => {
            if (e.key === '/' && !/^(input|textarea|select)$/i.test(document.activeElement?.tagName || '')) {
                e.preventDefault(); searchRef.current?.focus();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    const loadedTime = useMemo(() => new Date(), [meds]).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const openPanel = (m) => { if (selected && selected.id === m.id) { setSelected(null); return; } setPanelMed(m); setSelected(m); };

    const rows = useMemo(() => meds.map((m) => ({ ...m, bucket: bucketOf(m) })), [meds]);
    const counts = useMemo(() => {
        const c = { healthy: 0, expiring: 0, low: 0, out: 0, expired: 0 };
        rows.forEach((m) => { c[m.bucket] += 1; });
        return c;
    }, [rows]);

    const shown = useMemo(() => {
        const q = query.trim().toLowerCase();
        let list = rows.filter((m) => (tab === 'all' ? true : m.bucket === tab));
        if (residentF !== 'any') list = list.filter((m) => (m.resident ?? '') === residentF);
        if (cdF !== 'all') list = list.filter((m) => (cdF === 'cd' ? m.is_controlled : !m.is_controlled));
        if (q) list = list.filter((m) => `${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(q));
        const [key, dir] = sort.split('-');
        const val = (m) => {
            switch (key) {
                case 'stock': return Number(m.stock_level ?? -1);
                case 'expiry': return m.expiry_date ? Date.parse(m.expiry_date) || 0 : 0;
                case 'status': return ['expired', 'out', 'low', 'expiring', 'healthy'].indexOf(m.bucket);
                default: return String(m.medication_name ?? '').toLowerCase();
            }
        };
        list = [...list].sort((a, b) => {
            const x = val(a), y = val(b);
            const cmp = typeof x === 'number' ? x - y : String(x).localeCompare(String(y), undefined, { numeric: true });
            return dir === 'desc' ? -cmp : cmp;
        });
        return list;
    }, [rows, tab, query, residentF, cdF, sort]);

    const residentOptions = useMemo(() => {
        const names = [...new Set(meds.map((m) => m.resident).filter(Boolean))].sort((a, b) => a.localeCompare(b));
        return [{ value: 'any', label: 'All residents' }, ...names.map((n) => ({ value: n, label: n }))];
    }, [meds]);

    const attention = counts.low + counts.out + counts.expiring + counts.expired;

    const TABS = [
        { k: 'all', l: 'All items', n: rows.length, tint: '#3E5170' },
        { k: 'low', l: 'Low stock', n: counts.low, tint: '#BF8A3C' },
        { k: 'out', l: 'Out of stock', n: counts.out, tint: '#B4544A' },
        { k: 'expiring', l: 'Expiring soon', n: counts.expiring, tint: '#8A6FAE' },
        { k: 'expired', l: 'Expired', n: counts.expired, tint: '#A6506A' },
    ];
    const FILTERS = [
        { key: 'resident', label: 'Resident', icon: IconUser, value: residentF, set: (v) => setResidentF(v || 'any'), data: residentOptions, w: 150 },
        { key: 'cd', label: 'Controlled', icon: IconShieldLock, value: cdF, set: (v) => setCdF(v || 'all'), w: 138, data: [
            { value: 'all', label: 'All meds' }, { value: 'cd', label: 'Controlled only' }, { value: 'noncd', label: 'Non-controlled' }] },
        { key: 'sort', label: 'Sort', icon: IconArrowsSort, value: sort, set: (v) => setSort(v || 'medication-asc'), w: 150, data: [
            { value: 'medication-asc', label: 'Medication A–Z' }, { value: 'medication-desc', label: 'Medication Z–A' },
            { value: 'stock-asc', label: 'Stock: low first' }, { value: 'stock-desc', label: 'Stock: high first' },
            { value: 'expiry-asc', label: 'Expiry: soonest' }, { value: 'status-asc', label: 'Most urgent' }] },
    ];

    const exportCsv = () => {
        const head = ['Medication', 'Resident', 'Stock', 'Unit', 'Reorder level', 'Days cover', 'Expiry', 'Status', 'Controlled', 'CD schedule'];
        const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const lines = shown.map((m) => {
            const f = computeForecast(m, transactions);
            return [m.medication_name, m.resident, m.stock_level, m.unit, m.reorder_level, f ? `${f.daysLeft} days` : '', m.expiry_date, STATUS[m.bucket].label, m.is_controlled ? 'Yes' : 'No', m.cd_schedule].map(esc).join(',');
        });
        const blob = new Blob(['﻿' + [head.join(','), ...lines].join('\n')], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'medication-stock.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 0);
    };

    const runScan = () => {
        const v = scan.trim();
        if (!v) return;
        const hit = meds.find((m) => (m.barcode || '').trim() && (m.barcode || '').trim() === v);
        if (hit) { setScanOpen(false); setScan(''); setTab('all'); setQuery(''); setPanelMed(hit); setSelected(hit); }
        else brandedToast(`No medicine matches barcode “${v}”.`, '#B4544A', <IconX size={20} stroke={3} />);
    };

    return (
        <AppShell title="Medication stock" section="Medication">
            <Head title="Medication stock" />
            <Box px={{ base: 0, sm: 12 }} pb={20} style={{ maxWidth: 1220, marginInline: 'auto' }}>
                {/* Font pickers — set the fonts for the WHOLE app (persist across pages/refreshes) */}
                <Group justify="flex-end" gap={10} mb={14} wrap="wrap">
                    {[
                        { label: 'Heading font', value: headingFont, set: setHeadingFont },
                        { label: 'Body font', value: bodyFont, set: setBodyFont },
                    ].map((p) => (
                        <Group key={p.label} gap={10} wrap="nowrap" style={{ background: 'light-dark(#F1F3F7, var(--mantine-color-dark-7))', border: '1px solid light-dark(#E4E8EE, var(--mantine-color-dark-4))', borderRadius: 999, padding: '5px 6px 5px 14px' }}>
                            <IconTypography size={16} stroke={1.8} color="light-dark(#5E6878, #97A2B3)" />
                            <Text fz={11} fw={700} tt="uppercase" c={FAINT} style={{ letterSpacing: 0.6 }}>{p.label}</Text>
                            <Select size="xs" w={200} allowDeselect={false} value={p.value} onChange={(v) => p.set(v)} data={FONTS}
                                comboboxProps={{ withinPortal: true, radius: 12, shadow: 'lg' }}
                                rightSection={<IconChevronDown size={13} stroke={2.2} />}
                                styles={{ input: { fontWeight: 650, fontSize: 12.5, borderRadius: 999, borderColor: 'light-dark(#E4E8EE, var(--mantine-color-dark-4))', background: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))' } }} />
                        </Group>
                    ))}
                </Group>

                {/* Header — one primary action; everything else in the overflow menu */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb={26}>
                    <Group gap={14} wrap="nowrap" align="center">
                        <ThemeIcon size={46} radius={14} style={{ background: 'light-dark(#F4F6FA, rgba(255,255,255,0.05))', color: 'light-dark(#16233B, #C9D3E2)', border: '1px solid light-dark(#E7EBF1, rgba(255,255,255,0.08))', flexShrink: 0 }}><IconBox size={23} stroke={1.7} /></ThemeIcon>
                        <Box style={{ minWidth: 0 }}>
                            <Text fz={24} fw={650} c={INK} lh={1.15} style={{ letterSpacing: -0.5, fontFamily: HEADING_FONT }}>Medication stock</Text>
                            <Text fz={12.5} c={INK2} mt={2}>{[`${rows.length} medication${rows.length === 1 ? '' : 's'}`, attention ? `${attention} need attention` : 'all healthy', `updated ${loadedTime}`].join('  ·  ')}</Text>
                        </Box>
                    </Group>
                    <Group gap={10} wrap="nowrap">
                        {canAdjust && (
                            <Button h={42} radius={11} leftSection={<IconPlus size={16} stroke={2} />} onClick={() => shown[0] && openPanel(shown[0])}
                                styles={{ root: { fontWeight: 600 } }}
                                style={{ background: 'light-dark(#13233F, #45C1BF)', paddingInline: 20, boxShadow: 'light-dark(0 10px 22px -10px rgba(22,35,59,0.55), 0 10px 22px -10px rgba(31,158,147,0.6))' }}>Adjust stock</Button>
                        )}
                        <Menu shadow="lg" radius={14} width={214} position="bottom-end" offset={8} withArrow>
                            <Menu.Target>
                                <ActionIcon variant="default" size={42} radius={11} aria-label="More actions" style={{ borderColor: 'light-dark(#E4E8EE, var(--mantine-color-dark-4))' }}><IconDotsVertical size={18} stroke={1.8} /></ActionIcon>
                            </Menu.Target>
                            <Menu.Dropdown>
                                <Menu.Item leftSection={<IconBarcode size={16} stroke={1.8} />} onClick={() => setScanOpen(true)}>Scan barcode</Menu.Item>
                                <Menu.Item leftSection={<IconShieldLock size={16} stroke={1.8} color="#8A6FAE" />} component="a" href="/frontend2/controlled-drugs">CD register</Menu.Item>
                                <Menu.Item leftSection={<IconDownload size={16} stroke={1.8} />} onClick={exportCsv}>Export CSV</Menu.Item>
                                <Menu.Divider />
                                <Menu.Item leftSection={<IconRefresh size={16} stroke={1.8} />} onClick={() => window.location.reload()}>Refresh data</Menu.Item>
                            </Menu.Dropdown>
                        </Menu>
                    </Group>
                </Group>

                {/* Control strip — search + filters on one line */}
                <Group justify="space-between" align="center" wrap="wrap" gap={12} mb={16}>
                    <TextInput
                        ref={searchRef} size="md" radius={12} w={isSm ? '100%' : 400} maw="100%"
                        placeholder="Search medication or resident…"
                        leftSection={<IconSearch size={16} stroke={1.8} color="light-dark(#939DAD, #6C7688)" />}
                        value={query} onChange={(e) => setQuery(e.currentTarget.value)}
                        rightSection={query
                            ? <ActionIcon size="md" variant="subtle" color="gray" radius="xl" aria-label="Clear" onClick={() => setQuery('')}><IconX size={15} /></ActionIcon>
                            : <Box component="kbd" style={{ fontSize: 11, fontWeight: 600, fontFamily: 'inherit', color: FAINT, background: 'light-dark(#F1F3F7, rgba(255,255,255,0.06))', border: `1px solid ${LINE}`, borderRadius: 6, padding: '2px 8px', lineHeight: 1 }}>/</Box>}
                        rightSectionWidth={38}
                        styles={{ input: { height: 42, borderRadius: 12, fontSize: 13.5, borderColor: 'light-dark(#E4E8EE, var(--mantine-color-dark-4))' } }}
                    />
                    <Group gap={10} wrap="wrap">
                        {FILTERS.map((f) => (
                            <Group key={f.key} gap={9} wrap="nowrap" style={{ flexShrink: 0, background: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))', border: '1px solid light-dark(#E4E8EE, var(--mantine-color-dark-4))', borderRadius: 12, padding: '5px 9px', height: 42 }}>
                                <Box style={{ width: 26, height: 26, borderRadius: 8, flexShrink: 0, display: 'grid', placeItems: 'center', background: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))', color: INK2 }}><f.icon size={15} stroke={1.8} /></Box>
                                <Box style={{ flexShrink: 0 }}>
                                    <Text fz={8.5} fw={700} tt="uppercase" c={FAINT} lh={1} style={{ letterSpacing: 0.6, marginBottom: 2 }}>{f.label}</Text>
                                    <Select variant="unstyled" size="xs" w={f.w} allowDeselect={false} comboboxProps={{ withinPortal: true, radius: 12, shadow: 'lg', offset: 6 }}
                                        value={f.value} onChange={f.set} data={f.data}
                                        rightSection={<IconChevronDown size={13} stroke={2.2} />} rightSectionWidth={17}
                                        styles={{ input: { fontWeight: 650, fontSize: 13, color: INK, minHeight: 18, height: 18, lineHeight: '18px', paddingRight: 17 }, section: { color: FAINT } }} />
                                </Box>
                            </Group>
                        ))}
                    </Group>
                </Group>

                {/* Status lens */}
                <Group gap={7} wrap="wrap" mb={18}>
                    {TABS.map((t) => {
                        const on = tab === t.k;
                        return (
                            <Box key={t.k} component="button" type="button" onClick={() => setTab(t.k)}
                                onMouseEnter={(e) => { if (!on) e.currentTarget.style.borderColor = 'light-dark(#D3D9E2, rgba(255,255,255,0.16))'; }}
                                onMouseLeave={(e) => { if (!on) e.currentTarget.style.borderColor = 'light-dark(#E4E8EE, var(--mantine-color-dark-4))'; }}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 8, cursor: 'pointer', whiteSpace: 'nowrap',
                                    border: on ? '1px solid transparent' : '1px solid light-dark(#E4E8EE, var(--mantine-color-dark-4))',
                                    background: on ? t.tint : 'light-dark(#FFFFFF, var(--mantine-color-dark-6))',
                                    borderRadius: 999, padding: on ? '6px 8px 6px 12px' : '6px 12px',
                                    boxShadow: on ? `0 6px 16px -8px ${t.tint}` : 'none', transition: 'border-color .14s ease',
                                }}>
                                <Box component="span" w={7} h={7} style={{ borderRadius: '50%', background: on ? 'rgba(255,255,255,0.9)' : t.tint, flexShrink: 0 }} />
                                <Text fz={12.5} fw={600} c={on ? '#FFFFFF' : 'light-dark(#44536B, #C3CDDD)'}>{t.l}</Text>
                                <Box component="span" style={{ ...numeric, minWidth: 21, textAlign: 'center', borderRadius: 999, padding: '1px 7px', fontSize: 11.5, fontWeight: 700, background: on ? 'rgba(255,255,255,0.22)' : 'light-dark(#F1F3F7, rgba(255,255,255,0.06))', color: on ? '#FFFFFF' : 'light-dark(#6A7A92, #94A2B8)' }}>{t.n}</Box>
                            </Box>
                        );
                    })}
                </Group>

                {/* Table + detail panel */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 20, alignItems: 'flex-start', justifyContent: 'center' }}>
                    <Box style={{ ...card, background: palette.tableBg, flex: '1 1 520px', minWidth: 0, maxWidth: '100%', padding: isSm ? '10px 12px' : '14px 22px' }}>
                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={6} py={10} style={{ borderBottom: `1px solid ${LINE}` }}>
                            <Text style={{ ...cap, flex: '2 1 220px' }} c={FAINT}>Medication</Text>
                            <Text style={{ ...cap, flex: '1 1 150px' }} c={FAINT} visibleFrom="sm">Stock</Text>
                            <Text style={{ ...cap, width: 100 }} c={FAINT} visibleFrom="md">Expiry</Text>
                            <Text style={{ ...cap, width: 118 }} c={FAINT}>Status</Text>
                            <Box style={{ width: 18 }} />
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" c={FAINT} ta="center" py={56}>{query ? `No matches for “${query}”.` : 'No medications in this view.'}</Text>
                            : (
                                <ScrollArea.Autosize mah="calc(100vh - 300px)" type="auto" offsetScrollbars scrollbarSize={8}>
                                    {shown.map((m, idx) => {
                                        const bar = barOf(m);
                                        const fc = computeForecast(m, transactions);
                                        return (
                                            <Group key={m.id} gap={14} wrap="nowrap" align="center" px={6} py={17}
                                                role="button" tabIndex={0} aria-label={`${m.medication_name}, ${m.stock_level ?? 'unknown'} ${m.unit ?? 'units'}, ${STATUS[m.bucket].label}`}
                                                onClick={() => openPanel(m)}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(m); } }}
                                                style={{ cursor: 'pointer', borderTop: idx ? `1px solid ${LINE}` : 'none', borderRadius: 12, transition: 'background .15s' }}
                                                onFocus={(e) => { e.currentTarget.style.background = 'light-dark(#F2F5F9, var(--mantine-color-dark-5))'; e.currentTarget.style.outline = '2px solid #3E8E77'; e.currentTarget.style.outlineOffset = '-2px'; }}
                                                onBlur={(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.outline = 'none'; }}
                                                onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#F8FAFC, var(--mantine-color-dark-5))'; }}
                                                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                                                <Group gap={13} wrap="nowrap" style={{ flex: '2 1 220px', minWidth: 0 }}>
                                                    <MedTile controlled={m.is_controlled} size={38} radius={11} icon={18} />
                                                    <Box style={{ minWidth: 0 }}>
                                                        <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                                                            <Text fz={13.5} fw={600} c={INK} truncate>{m.medication_name}</Text>
                                                            {m.is_controlled && <CdTag schedule={m.cd_schedule} />}
                                                        </Group>
                                                        <Text fz={11.5} c={FAINT} truncate mt={1}>{m.resident ?? '—'}</Text>
                                                    </Box>
                                                </Group>
                                                <Box style={{ flex: '1 1 150px', minWidth: 0 }} visibleFrom="sm">
                                                    <Group gap={8} wrap="nowrap" mb={6}>
                                                        <Text fz={13.5} fw={650} c={INK} lh={1} style={numeric}>{m.stock_level ?? '—'}</Text>
                                                        <Text fz={11} c={FAINT}>{m.unit ?? 'units'}</Text>
                                                        {fc && <Text fz={10.5} fw={600} c={forecastTone(fc.daysLeft)} style={numeric}>· ≈{fc.daysLeft}d</Text>}
                                                    </Group>
                                                    <Box style={{ height: 5, borderRadius: 999, background: 'light-dark(#F0F2F6, var(--mantine-color-dark-5))', overflow: 'hidden' }}>
                                                        <Box style={{ width: `${bar.pct}%`, height: '100%', borderRadius: 999, background: bar.hex, opacity: 0.9 }} />
                                                    </Box>
                                                </Box>
                                                <Box style={{ width: 100, flexShrink: 0 }} visibleFrom="md">
                                                    <Text fz={12.5} fw={600} c={m.expired ? '#B4544A' : m.expiring_soon ? '#BF8A3C' : INK} style={numeric}>{m.expiry_date ?? '—'}</Text>
                                                </Box>
                                                <Box style={{ width: 118, flexShrink: 0 }}><StatusMark bucket={m.bucket} /></Box>
                                                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}><IconChevronRight size={16} /></ActionIcon>
                                            </Group>
                                        );
                                    })}
                                </ScrollArea.Autosize>
                            )}
                    </Box>

                    <Box onTransitionEnd={() => { if (!selected) setPanelMed(null); }}
                        style={{ flexBasis: selected ? (railBelow ? '100%' : 430) : 0, flexGrow: 0, flexShrink: 1, minWidth: 0, alignSelf: 'stretch', opacity: selected ? 1 : 0, transition: 'flex-basis .32s ease, opacity .28s ease', overflow: 'hidden' }}>
                        {panelMed && <MedPanel key={panelMed.id} med={panelMed} transactions={transactions} canAdjust={canAdjust} onClose={() => setSelected(null)} />}
                    </Box>
                </Box>
            </Box>

            {/* Scan barcode — a deliberate action from the overflow menu */}
            <Modal opened={scanOpen} onClose={() => { setScanOpen(false); setScan(''); }} title={<Text fw={650} fz="lg" c={INK} style={{ letterSpacing: -0.2 }}>Scan barcode</Text>} radius={18} centered size="sm" overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}>
                <Text fz={13} c={INK2} mb={12}>Scan or type a pack barcode to jump straight to that medicine.</Text>
                <TextInput autoFocus size="md" radius={11} placeholder="e.g. 5012345678900" leftSection={<IconBarcode size={17} stroke={1.8} />}
                    value={scan} onChange={(e) => setScan(e.currentTarget.value)} onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); runScan(); } }} />
                <Group justify="flex-end" gap={10} mt={16}>
                    <Button variant="default" radius={10} onClick={() => { setScanOpen(false); setScan(''); }} styles={{ root: { fontWeight: 600 } }}>Cancel</Button>
                    <Button radius={10} onClick={runScan} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#13233F, #45C1BF)' }}>Find</Button>
                </Group>
            </Modal>
        </AppShell>
    );
}
