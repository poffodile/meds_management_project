import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useMediaQuery } from '@mantine/hooks';
import { notifications } from '@mantine/notifications';
import {
    Box, Group, Stack, Text, Badge, Button, ThemeIcon, TextInput, ActionIcon, ScrollArea,
    Tooltip, RingProgress, Divider, Select, Textarea, NumberInput, Checkbox, Progress, Modal,
} from '@mantine/core';
import {
    IconBox, IconSearch, IconRefresh, IconDownload, IconPlus, IconX, IconCheck,
    IconPill, IconShieldLock, IconSelector, IconChevronUp, IconChevronDown, IconChevronRight,
    IconPackages, IconTrendingDown, IconPackageOff, IconHourglassHigh, IconCalendarX,
    IconLayoutGrid, IconArrowsExchange, IconShoppingCart, IconTrash,
    IconAdjustmentsHorizontal, IconArrowsSort, IconUser, IconClipboardCheck, IconBarcode,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';

const PAGE = '/frontend2/stock-2';
const ADJUST = '/frontend2/stock-2/adjust';
const COUNT = '/frontend2/stock-2/count';
const ORDER = '/frontend2/stock-2/order';
const ORDER_RECEIVE = '/frontend2/stock-2/order/receive';
const ORDER_CANCEL = '/frontend2/stock-2/order/cancel';

const TXT = 'light-dark(#1E2F4D, #E8EBF1)';
// Soft gradient for the donut card — light: blue-grey tint → white; dark: navy tint → deep navy.
const BANNER_GRADIENT = 'linear-gradient(125deg, light-dark(#DCE4F0, #223454) 0%, light-dark(#ECF0F7, #1A2842) 42%, light-dark(#FFFFFF, #141F35) 100%)';
// Centred version — the table uses this so the tint sits in the middle.
const BANNER_GRADIENT_REV = 'linear-gradient(125deg, light-dark(#FFFFFF, #131E33) 0%, light-dark(#ECF0F7, #1A2842) 30%, light-dark(#DCE4F0, #223454) 50%, light-dark(#ECF0F7, #1A2842) 70%, light-dark(#FFFFFF, #131E33) 100%)';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 20,
    border: '1px solid light-dark(#EEF1F4, rgba(150,172,205,0.18))',
    boxShadow: '0 10px 30px -18px rgba(20,50,80,0.22)',
};

// Stock health buckets — mutually exclusive (each med is exactly one), so a pie of them sums to the total.
const STATUS = {
    healthy:  { label: 'In stock',      c: '#1F8A5B', dot: '#28A76B', bg: 'light-dark(#E7F3E8, rgba(31,138,91,0.2))' },
    expiring: { label: 'Expiring soon', c: '#6B4E86', dot: '#8A63C9', bg: 'light-dark(#EDE6F6, rgba(138,99,201,0.22))' },
    low:      { label: 'Low stock',     c: '#B9660F', dot: '#E8842B', bg: 'light-dark(#FBE9D7, rgba(232,132,43,0.22))' },
    out:      { label: 'Out of stock',  c: '#C0413A', dot: '#E4574C', bg: 'light-dark(#FBE1DF, rgba(228,87,76,0.22))' },
    expired:  { label: 'Expired',       c: '#A6304A', dot: '#C43D6B', bg: 'light-dark(#F8DEE7, rgba(196,61,107,0.22))' },
};
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
function bucketOf(m) {
    if (m.expired) return 'expired';
    if (isOut(m)) return 'out';
    if (m.low) return 'low';
    if (m.expiring_soon) return 'expiring';
    return 'healthy';
}
// Stock bar — how full relative to (roughly) twice the reorder level.
function stockBar(m) {
    const s = m.stock_level;
    if (s === null || s === undefined) return { pct: 0, color: 'gray' };
    const n = Number(s);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    if (m.expired || isOut(m)) return { pct, color: 'red' };
    if (pct < 25 || n <= 5) return { pct, color: 'orange' };
    if (pct < 45 || n <= 12) return { pct, color: 'yellow' };
    return { pct, color: 'teal' };
}

// --- Days-of-stock forecast (#35) -----------------------------------------
// Heuristic: estimate a per-day usage rate from this med's `administered`
// history, then project how long the current stock will last.
// NOTE: this is a PLACEHOLDER on the current (dummy) dataset. Once the meds
// dictionary (#17) is wired in, the dosing interval/quantity from the
// formulary will give a far more accurate forecast — swap this out then.
function parseTxnDate(s) {
    if (!s) return null;
    const t = Date.parse(s);
    return Number.isNaN(t) ? null : t;
}
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
    // Need at least two dated administrations to trust a rate.
    if (events < 2 || total <= 0) return null;
    const spanDays = Math.max(1, (max - min) / 86400000);
    const perDay = total / spanDays;
    if (perDay <= 0) return null;
    return { perDay, daysLeft: Math.floor(stock / perDay), basisDays: Math.round(spanDays) };
}
// Colour a forecast by urgency — under a week red, under a fortnight amber.
function forecastTone(daysLeft) {
    if (daysLeft <= 7) return '#C0413A';
    if (daysLeft <= 14) return '#B9660F';
    return 'light-dark(#48586F, #9AA8BE)';
}

// Transaction type → label + colour.
const TXN = {
    received:     { label: 'Received',     c: '#1F8A5B', sign: '+' },
    disposed:     { label: 'Disposed',     c: '#C43D6B', sign: '−' },
    returned:     { label: 'Returned',     c: '#3A7CA5', sign: '−' },
    correction:   { label: 'Correction',   c: '#8A6D3B', sign: '±' },
    administered: { label: 'Administered', c: '#6B4E86', sign: '−' },
};

const ADJUST_TYPES = [
    { value: 'received', label: 'Received (stock in)' },
    { value: 'disposed', label: 'Disposed' },
    { value: 'returned', label: 'Returned' },
    { value: 'correction', label: 'Correction' },
];

// Structured disposal capture (#36) — reason codes + methods.
const DISPOSAL_REASONS = [
    { value: 'Expired', label: 'Expired' },
    { value: 'Damaged / spoiled', label: 'Damaged / spoiled' },
    { value: 'Discontinued', label: 'Discontinued (no longer prescribed)' },
    { value: 'Resident left / deceased', label: 'Resident left / deceased' },
    { value: 'Part-used remainder', label: 'Part-used remainder' },
    { value: 'Spillage / breakage', label: 'Spillage / breakage' },
    { value: 'Other', label: 'Other' },
];
const DISPOSAL_METHODS = [
    { value: 'Returned to pharmacy', label: 'Returned to pharmacy' },
    { value: 'Denatured (CD destruction kit)', label: 'Denatured (CD destruction kit)' },
    { value: 'Pharmaceutical waste bin', label: 'Pharmaceutical waste bin' },
    { value: 'Sharps bin', label: 'Sharps bin' },
    { value: 'Other', label: 'Other' },
];

// Pill filter chip — teal filled when active, white outline otherwise, with an icon + count badge.
function StatPill({ icon: Icon, label, value, active, tint, onClick }) {
    return (
        <Box component="button" type="button" onClick={onClick}
            onMouseEnter={(e) => { if (!active) { e.currentTarget.style.transform = 'translateY(-1px)'; e.currentTarget.style.boxShadow = '0 8px 16px -9px rgba(20,50,80,0.5)'; } }}
            onMouseLeave={(e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = active ? '0 8px 18px -8px rgba(28,156,144,0.6)' : '0 4px 12px -9px rgba(20,50,80,0.4)'; }}
            style={{
                display: 'inline-flex', alignItems: 'center', gap: 8, cursor: 'pointer', whiteSpace: 'nowrap',
                border: active ? '1px solid transparent' : '1px solid light-dark(#E6EAF0, rgba(150,172,205,0.22))',
                background: active ? 'linear-gradient(180deg, #23B2A4 0%, #1C9C90 100%)' : 'light-dark(#ffffff, var(--mantine-color-dark-6))',
                borderRadius: 999, padding: active ? '6px 8px 6px 13px' : '6px 13px',
                boxShadow: active ? '0 8px 18px -8px rgba(28,156,144,0.6)' : '0 4px 12px -9px rgba(20,50,80,0.4)',
                transition: 'transform .14s ease, box-shadow .14s ease',
            }}>
            <Icon size={16} stroke={1.9} color={active ? '#ffffff' : tint} />
            <Text fz={13} fw={active ? 700 : 600} c={active ? '#ffffff' : 'light-dark(#3E4C63, #C7D1E0)'}>{label}</Text>
            <Box style={{
                minWidth: 22, textAlign: 'center', borderRadius: 999, padding: '1px 7px', fontSize: 12, fontWeight: 800,
                background: active ? '#ffffff' : 'light-dark(#EEF1F6, rgba(255,255,255,0.06))',
                color: active ? '#1C9C90' : 'light-dark(#6A7A92, #94A2B8)',
            }}>{value}</Box>
        </Box>
    );
}

// Slide-in detail panel — med details + per-med movements + (managers) an inline adjust form.
function MedPanel({ med, transactions, canAdjust, onClose, onDone }) {
    const st = STATUS[bucketOf(med)];
    const form = useForm({
        mar_sheet_id: med.id, transaction_type: 'received', quantity: '', expiry_date: '',
        is_controlled: !!med.is_controlled, cd_schedule: med.cd_schedule ?? '',
        reason: '', disposal_method: '', witness_name: '', notes: '', batch_number: '', supplier: '', barcode: med.barcode ?? '',
    });
    const toast = (message) => notifications.show({
        message, autoClose: 3500, withBorder: true,
        icon: <IconCheck size={20} stroke={3} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: '#1F9E93', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: '#1F9E93', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 },
            description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 },
            closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });
    const submit = () => form.post(ADJUST, {
        preserveScroll: true,
        onSuccess: () => { toast(`${med.medication_name} stock updated.`); form.reset(); onDone(); },
    });
    const cap = { fontSize: 10, textTransform: 'uppercase', fontWeight: 700, letterSpacing: 0.5 };
    const isDisposal = form.data.transaction_type === 'disposed';
    const isReceived = form.data.transaction_type === 'received';
    const movements = transactions.filter((t) => t.medication_name === med.medication_name);

    return (
        <Box style={{ ...card, padding: '18px 20px', width: '100%' }}>
            <Group justify="space-between" wrap="nowrap" mb={12}>
                <Group gap={10} wrap="nowrap" style={{ minWidth: 0 }}>
                    <ThemeIcon variant="light" color={med.is_controlled ? 'grape' : 'indigo'} size={40} radius={12} style={{ flexShrink: 0 }}>
                        {med.is_controlled ? <IconShieldLock size={20} /> : <IconPill size={20} />}
                    </ThemeIcon>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={6} wrap="nowrap" style={{ minWidth: 0 }}>
                            <Text fz="sm" fw={700} c={TXT} truncate>{med.medication_name}</Text>
                            {med.is_controlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD{med.cd_schedule ? ` ${med.cd_schedule}` : ''}</Badge>}
                        </Group>
                        <Text fz="xs" c="dimmed" truncate>{med.resident ?? 'No resident linked'}</Text>
                    </Box>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="xl" onClick={onClose} aria-label="Close"><IconX size={18} /></ActionIcon>
            </Group>
            <Divider mb={14} />

            <Group justify="space-between" align="center" mb={12}>
                <Box>
                    <Text fz={30} fw={800} c={TXT} lh={1}>{med.stock_level ?? '—'} <Text span fz={13} c="dimmed" fw={600}>{med.unit ?? 'units'}</Text></Text>
                    <Text fz={11.5} c="dimmed" mt={2}>Reorder at {med.reorder_level ?? '—'}</Text>
                </Box>
                <Box style={{ display: 'inline-flex', alignItems: 'center', padding: '4px 12px', borderRadius: 999, fontSize: 11, fontWeight: 800, letterSpacing: 0.4, background: st.bg, color: st.c, boxShadow: '0 3px 8px -4px rgba(20,50,80,0.35)' }}>{st.label.toUpperCase()}</Box>
            </Group>
            <Progress value={stockBar(med).pct} color={stockBar(med).color} radius="xl" size="sm" mb={14} />

            {(() => {
                const f = computeForecast(med, transactions);
                if (!f) return null;
                return (
                    <Box mb={14} style={{ borderRadius: 12, padding: '10px 14px', background: 'light-dark(#F4F7FB, rgba(255,255,255,0.03))', border: '1px solid light-dark(#E7ECF3, rgba(255,255,255,0.06))' }}>
                        <Group justify="space-between" align="center" wrap="nowrap">
                            <Box style={{ minWidth: 0 }}>
                                <Text style={cap} c="dimmed">Stock cover</Text>
                                <Text fz={19} fw={800} c={forecastTone(f.daysLeft)} lh={1.1}>≈ {f.daysLeft} day{f.daysLeft === 1 ? '' : 's'} left</Text>
                            </Box>
                            <Text fz={11} c="dimmed" ta="right" style={{ flexShrink: 0 }}>{f.perDay.toFixed(1)} {med.unit ?? 'units'}/day<br />over {f.basisDays} day{f.basisDays === 1 ? '' : 's'}</Text>
                        </Group>
                    </Box>
                );
            })()}

            <Group gap={0} mb={4} grow>
                <Box><Text c="dimmed" mb={2} style={cap}>Expiry</Text><Text fz="sm" fw={600} c={med.expired ? '#C0413A' : med.expiring_soon ? '#B9660F' : TXT}>{med.expiry_date ?? '—'}</Text></Box>
                <Box><Text c="dimmed" mb={2} style={cap}>Controlled</Text><Text fz="sm" fw={600} c={TXT}>{med.is_controlled ? `Yes${med.cd_schedule ? ` · ${med.cd_schedule}` : ''}` : 'No'}</Text></Box>
            </Group>

            {med.batches && med.batches.length > 0 && (
                <>
                    <Divider my={14} label={<Text style={cap} c="dimmed">Batches · {med.batches.length} · earliest expiry used first</Text>} labelPosition="left" />
                    <Stack gap={8}>
                        {med.batches.map((b, i) => (
                            <Group key={b.id} justify="space-between" wrap="nowrap" gap={8}
                                style={{ padding: '8px 12px', borderRadius: 10,
                                    background: i === 0 ? 'light-dark(#E7F3E8, rgba(31,138,91,0.14))' : 'light-dark(#F4F7FB, rgba(255,255,255,0.03))',
                                    border: i === 0 ? '1px solid light-dark(#BFE3C8, rgba(31,138,91,0.3))' : '1px solid light-dark(#EEF1F5, rgba(255,255,255,0.05))' }}>
                                <Box style={{ minWidth: 0 }}>
                                    <Group gap={6} wrap="nowrap" style={{ minWidth: 0 }}>
                                        <Text fz={12.5} fw={700} c={TXT} truncate>{b.batch_number || 'No lot no.'}</Text>
                                        {i === 0 && <Badge size="xs" color="teal" variant="light" radius="sm" style={{ flexShrink: 0 }}>Use next</Badge>}
                                    </Group>
                                    <Text fz={11} c="dimmed" truncate>{b.expiry_date ? `Exp ${b.expiry_date}` : 'No expiry'}{b.supplier ? ` · ${b.supplier}` : ''}</Text>
                                </Box>
                                <Text fz={13} fw={800} c={TXT} style={{ flexShrink: 0 }}>{b.quantity}{med.unit ? ` ${med.unit}` : ''}</Text>
                            </Group>
                        ))}
                    </Stack>
                </>
            )}

            {canAdjust && (
                <>
                    <Divider my={14} label={<Text style={cap} c="dimmed">Adjust stock</Text>} labelPosition="left" />
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
                        {isReceived && <Text fz={11} c="dimmed" mt={-4}>Booking stock in creates a batch with this expiry — batches are used earliest-first.</Text>}
                        <Checkbox label="Controlled drug" checked={form.data.is_controlled} onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)} />
                        {form.data.is_controlled && (
                            <Group grow gap={10}>
                                <TextInput label="CD schedule" placeholder="e.g. 2, 3" value={form.data.cd_schedule} onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)} />
                                <TextInput label="Witness" placeholder="Second staff member" value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} />
                            </Group>
                        )}
                        {isDisposal && (
                            <TextInput label="Disposal method" placeholder="How it was disposed of" value={form.data.disposal_method} onChange={(e) => form.setData('disposal_method', e.currentTarget.value)} />
                        )}
                        <Textarea label="Notes / reason" placeholder="Optional note about this movement…" autosize minRows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                        <TextInput label="Barcode / GTIN" placeholder="Scan or type the pack barcode" leftSection={<IconBarcode size={16} />} value={form.data.barcode} onChange={(e) => form.setData('barcode', e.currentTarget.value)} description="Lets a scanner jump straight to this medicine" />
                        <Button radius={10} loading={form.processing} onClick={submit} style={{ background: 'light-dark(#1C325A, #1F9E93)', color: '#fff' }}>Save adjustment</Button>
                    </Stack>
                </>
            )}

            <Divider my={14} label={<Text style={cap} c="dimmed">Recent movements · {movements.length}</Text>} labelPosition="left" />
            {movements.length === 0
                ? <Text fz="xs" c="dimmed">No stock movements recorded for this medication.</Text>
                : (
                    <ScrollArea.Autosize mah={220} type="hover" offsetScrollbars>
                        <Stack gap={10} pr={6}>
                            {movements.map((t) => {
                                const meta = TXN[t.type] || { label: t.type, c: '#98A1AB', sign: '' };
                                return (
                                    <Group key={t.id} gap={9} wrap="nowrap" align="flex-start">
                                        <Box w={7} h={7} style={{ borderRadius: '50%', background: meta.c, flexShrink: 0, marginTop: 5 }} />
                                        <Box style={{ minWidth: 0, flex: 1 }}>
                                            <Group justify="space-between" wrap="nowrap" gap={8}>
                                                <Text fz={12.5} c={TXT} lh={1.3}><Text span fw={700} c={meta.c}>{meta.label}</Text>{t.performed_by ? ` · ${t.performed_by}` : ''}</Text>
                                                <Text fz={12.5} fw={700} c={meta.c} style={{ flexShrink: 0 }}>{meta.sign}{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text>
                                            </Group>
                                            <Text fz={11} c="dimmed">{t.date}{t.balance_after != null ? ` · balance ${t.balance_after}` : ''}</Text>
                                            {t.reason && <Text fz={11.5} c={TXT} lh={1.35}>“{t.reason}”</Text>}
                                        </Box>
                                    </Group>
                                );
                            })}
                        </Stack>
                    </ScrollArea.Autosize>
                )}
        </Box>
    );
}

// Stock count / reconciliation (#31) — enter counted quantities, flag discrepancies, post corrections.
function CountView({ meds = [], canAdjust }) {
    const [counts, setCounts] = useState({});
    const [processing, setProcessing] = useState(false);
    const setField = (id, field, value) => setCounts((c) => ({ ...c, [id]: { ...(c[id] || {}), [field]: value } }));

    const toast = (message, ok = true) => notifications.show({
        message, autoClose: 3800, withBorder: true, icon: ok ? <IconCheck size={20} stroke={3} /> : <IconAlertTriangle size={20} stroke={2.4} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: ok ? '#1F9E93' : '#C0413A', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: ok ? '#1F9E93' : '#C0413A', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 }, description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 }, closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });

    const rows = meds.map((m) => {
        const entry = counts[m.id] || {};
        const has = entry.counted !== undefined && entry.counted !== '' && entry.counted !== null;
        const counted = has ? Number(entry.counted) : null;
        const expected = m.stock_level === null || m.stock_level === undefined ? null : Number(m.stock_level);
        const diff = (has && expected !== null) ? counted - expected : null;
        return { m, entry, has, counted, expected, diff };
    });
    const discrepancies = rows.filter((r) => r.has && r.diff !== 0);
    const cdMissing = discrepancies.filter((r) => r.m.is_controlled && !(r.entry.witness || '').trim());

    const post = () => {
        if (!discrepancies.length) return;
        if (cdMissing.length) { toast(`Add a witness for ${cdMissing.length} controlled-drug count${cdMissing.length === 1 ? '' : 's'}.`, false); return; }
        const payload = discrepancies.map((r) => ({ mar_sheet_id: r.m.id, counted: r.counted, reason: r.entry.reason || '', witness_name: r.entry.witness || '' }));
        setProcessing(true);
        router.post(COUNT, { counts: payload }, {
            preserveScroll: true,
            onSuccess: () => { toast(`${payload.length} item${payload.length === 1 ? '' : 's'} reconciled.`); setCounts({}); },
            onFinish: () => setProcessing(false),
        });
    };

    const postBtn = canAdjust
        ? <Button size="sm" radius={10} color="teal" loading={processing} disabled={!discrepancies.length} leftSection={<IconClipboardCheck size={15} />} onClick={post}>Post {discrepancies.length || ''} correction{discrepancies.length === 1 ? '' : 's'}</Button>
        : null;

    return (
        <ViewCard title="Stock count" subtitle="Enter the counted quantity for each item — differences are flagged and posted as corrections" action={postBtn}>
            {!canAdjust && <Text fz="sm" c="dimmed" mb={10}>You can review counts, but only a manager can post corrections.</Text>}
            <Group gap={14} wrap="nowrap" px={4} pb={10} style={{ borderBottom: '1px solid light-dark(#E3E8EF, var(--mantine-color-dark-4))' }}>
                <Text fz={10.5} fw={800} tt="uppercase" c="light-dark(#55647D, #9AA8BE)" style={{ flex: '2 1 200px', letterSpacing: 0.6 }}>Medication</Text>
                <Text fz={10.5} fw={800} tt="uppercase" c="light-dark(#55647D, #9AA8BE)" style={{ width: 80, textAlign: 'right', letterSpacing: 0.6 }}>Expected</Text>
                <Text fz={10.5} fw={800} tt="uppercase" c="light-dark(#55647D, #9AA8BE)" style={{ width: 110, letterSpacing: 0.6 }}>Counted</Text>
                <Text fz={10.5} fw={800} tt="uppercase" c="light-dark(#55647D, #9AA8BE)" style={{ width: 92, textAlign: 'right', letterSpacing: 0.6 }}>Difference</Text>
            </Group>
            <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                {rows.map(({ m, entry, has, expected, diff }, idx) => {
                    const tone = diff === 0 ? '#1F8A5B' : (diff < 0 ? '#C0413A' : '#B9660F');
                    const witnessMissing = has && diff !== 0 && m.is_controlled && !(entry.witness || '').trim();
                    return (
                        <Box key={m.id} px={4} py={12} style={rowBorder(idx)}>
                            <Group gap={14} wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : 'indigo'} size={34} radius={10} style={{ flexShrink: 0 }}>{m.is_controlled ? <IconShieldLock size={16} /> : <IconPill size={16} />}</ThemeIcon>
                                <Box style={{ flex: '2 1 200px', minWidth: 0 }}>
                                    <Group gap={6} wrap="nowrap" style={{ minWidth: 0 }}>
                                        <Text fz={13.5} fw={700} c={TXT} truncate>{m.medication_name}</Text>
                                        {m.is_controlled && <Badge size="xs" color="grape" variant="light" radius="sm" style={{ flexShrink: 0 }}>CD</Badge>}
                                    </Group>
                                    <Text fz={11.5} c="dimmed" truncate>{m.resident ?? '—'}</Text>
                                </Box>
                                <Text fz={14} fw={800} c={TXT} style={{ width: 80, textAlign: 'right', flexShrink: 0 }}>{expected ?? '—'}</Text>
                                <NumberInput size="xs" w={110} min={0} hideControls placeholder="count" style={{ flexShrink: 0 }} disabled={!canAdjust}
                                    value={entry.counted ?? ''} onChange={(v) => setField(m.id, 'counted', v)} />
                                <Box style={{ width: 92, textAlign: 'right', flexShrink: 0 }}>
                                    {has && diff !== null
                                        ? <Text fz={13} fw={800} c={tone}>{diff === 0 ? 'Match' : `${diff > 0 ? '+' : ''}${diff}`}</Text>
                                        : <Text fz={12} c="dimmed">—</Text>}
                                </Box>
                            </Group>
                            {has && diff !== 0 && canAdjust && (
                                <Group gap={10} mt={8} pl={48} wrap="wrap">
                                    <TextInput size="xs" style={{ flex: '1 1 220px' }} placeholder="Reason for difference (optional)" value={entry.reason ?? ''} onChange={(e) => setField(m.id, 'reason', e.currentTarget.value)} />
                                    {m.is_controlled && (
                                        <TextInput size="xs" style={{ flex: '1 1 180px' }} placeholder="Witness (required for CD)" error={witnessMissing} value={entry.witness ?? ''} onChange={(e) => setField(m.id, 'witness', e.currentTarget.value)} />
                                    )}
                                </Group>
                            )}
                        </Box>
                    );
                })}
            </ScrollArea.Autosize>
            {discrepancies.length > 0 && (
                <Text fz={12.5} fw={700} c="light-dark(#48586F, #9AA8BE)" mt={12}>{discrepancies.length} discrepanc{discrepancies.length === 1 ? 'y' : 'ies'} ready to post{cdMissing.length ? ` · ${cdMissing.length} awaiting a CD witness` : ''}.</Text>
            )}
        </ViewCard>
    );
}

// Shared card wrapper for the Transactions / Reorder / Disposal sub-views.
function ViewCard({ title, subtitle, action, children }) {
    return (
        <Box style={{ ...card, background: BANNER_GRADIENT_REV, padding: '22px 24px' }}>
            <Group justify="space-between" align="flex-start" wrap="wrap" gap="sm" mb={14}>
                <Box>
                    <Text fz={22} fw={800} c={TXT}>{title}</Text>
                    {subtitle && <Text fz={12.5} c="dimmed" mt={2}>{subtitle}</Text>}
                </Box>
                {action}
            </Group>
            {children}
        </Box>
    );
}
const rowBorder = (idx) => ({ borderTop: idx ? '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))' : 'none' });
const emptyRow = (msg) => <Text fz="sm" c="dimmed" ta="center" py={48}>{msg}</Text>;

// All stock movements, newest first.
function TransactionsView({ transactions }) {
    if (!transactions.length) return <ViewCard title="Transactions" subtitle="All stock movements">{emptyRow('No stock movements recorded yet.')}</ViewCard>;
    return (
        <ViewCard title="Transactions" subtitle={`${transactions.length} movement${transactions.length === 1 ? '' : 's'} · newest first`}>
            <ScrollArea.Autosize mah="calc(100vh - 300px)" type="auto" offsetScrollbars scrollbarSize={8}>
                {transactions.map((t, idx) => {
                    const meta = TXN[t.type] || { label: t.type, c: '#98A1AB', sign: '' };
                    return (
                        <Group key={t.id} gap={14} wrap="nowrap" align="center" px={4} py={13} style={rowBorder(idx)}>
                            <Box style={{ width: 118, flexShrink: 0 }}><Text fz={12.5} fw={700} c={TXT}>{t.date}</Text></Box>
                            <Box style={{ width: 116, flexShrink: 0 }}><Box style={{ display: 'inline-flex', padding: '3px 10px', borderRadius: 999, fontSize: 10.5, fontWeight: 800, letterSpacing: 0.4, background: 'light-dark(#EEF2F7, rgba(255,255,255,0.05))', color: meta.c }}>{meta.label.toUpperCase()}</Box></Box>
                            <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13} fw={600} c={TXT} truncate>{t.medication_name}</Text>{t.reason && <Text fz={11.5} c="dimmed" truncate>“{t.reason}”</Text>}</Box>
                            <Box style={{ width: 96, flexShrink: 0, textAlign: 'right' }}><Text fz={13} fw={800} c={meta.c}>{meta.sign}{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text></Box>
                            <Box style={{ width: 86, flexShrink: 0, textAlign: 'right' }} visibleFrom="sm"><Text fz={12} c="dimmed">bal {t.balance_after ?? '—'}</Text></Box>
                            <Box style={{ width: 120, flexShrink: 0 }} visibleFrom="md"><Text fz={12} c="dimmed" truncate>{t.performed_by ?? '—'}</Text></Box>
                        </Group>
                    );
                })}
            </ScrollArea.Autosize>
        </ViewCard>
    );
}

// Items at or below their reorder level — what to buy, lowest stock first.
// Reorder → ordering (#32) — order low items, then receive open orders (books stock in).
function ReorderView({ meds = [], orders = [], canAdjust }) {
    const [orderFor, setOrderFor] = useState(null);
    const [receiveFor, setReceiveFor] = useState(null);
    const orderForm = useForm({ mar_sheet_id: '', quantity: '', supplier: '', notes: '' });
    const recvForm = useForm({ order_id: '', quantity: '', expiry_date: '', batch_number: '' });

    const toast = (message) => notifications.show({
        message, autoClose: 3500, withBorder: true, icon: <IconCheck size={20} stroke={3} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: '#1F9E93', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: '#1F9E93', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 }, description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 }, closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });

    const needs = meds.filter((m) => m.low || isOut(m)).sort((a, b) => Number(a.stock_level ?? 0) - Number(b.stock_level ?? 0));
    const onOrderIds = new Set(orders.map((o) => o.mar_sheet_id));
    const suggested = (m) => {
        const target = m.reorder_level ? Number(m.reorder_level) * 2 : Math.max(Number(m.stock_level ?? 0), 10);
        return Math.max(1, Math.round(target - Number(m.stock_level ?? 0)));
    };

    const openOrder = (m) => { orderForm.clearErrors(); orderForm.setData({ mar_sheet_id: String(m.id), quantity: suggested(m), supplier: '', notes: '' }); setReceiveFor(null); setOrderFor(m.id); };
    const placeOrder = () => orderForm.post(ORDER, { preserveScroll: true, onSuccess: () => { toast('Order placed.'); setOrderFor(null); orderForm.reset(); } });
    const openReceive = (o) => { recvForm.clearErrors(); recvForm.setData({ order_id: String(o.id), quantity: o.quantity, expiry_date: '', batch_number: '' }); setOrderFor(null); setReceiveFor(o.id); };
    const receive = () => recvForm.post(ORDER_RECEIVE, { preserveScroll: true, onSuccess: () => { toast('Order received and booked in.'); setReceiveFor(null); recvForm.reset(); } });
    const cancelOrder = (o) => router.post(ORDER_CANCEL, { order_id: o.id }, { preserveScroll: true, onSuccess: () => toast('Order cancelled.') });

    return (
        <Stack gap={16}>
            {/* On order */}
            {orders.length > 0 && (
                <ViewCard title="On order" subtitle={`${orders.length} open order${orders.length === 1 ? '' : 's'} · receive to book stock in`}>
                    {orders.map((o, idx) => (
                        <Box key={o.id} px={4} py={12} style={rowBorder(idx)}>
                            <Group gap={14} wrap="nowrap" align="center">
                                <ThemeIcon variant="light" color="teal" size={34} radius={10} style={{ flexShrink: 0 }}><IconShoppingCart size={16} /></ThemeIcon>
                                <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13.5} fw={700} c={TXT} truncate>{o.medication_name}</Text><Text fz={11.5} c="dimmed" truncate>{[o.resident, o.supplier].filter(Boolean).join(' · ') || '—'}</Text></Box>
                                <Box style={{ width: 120, flexShrink: 0 }} visibleFrom="sm"><Text fz={12} c="dimmed">Ordered <Text span fw={700} c={TXT}>{o.quantity}</Text>{o.ordered_at ? ` · ${o.ordered_at}` : ''}</Text></Box>
                                {canAdjust && (
                                    <Group gap={6} wrap="nowrap" style={{ flexShrink: 0 }}>
                                        <Button size="xs" radius={8} color="teal" onClick={() => openReceive(o)}>Receive</Button>
                                        <ActionIcon variant="subtle" color="gray" radius="xl" aria-label="Cancel order" onClick={() => cancelOrder(o)}><IconX size={15} /></ActionIcon>
                                    </Group>
                                )}
                            </Group>
                            {receiveFor === o.id && canAdjust && (
                                <Group gap={10} mt={10} pl={48} wrap="wrap" align="flex-end">
                                    <NumberInput size="xs" label="Received qty" w={110} min={0} value={recvForm.data.quantity} onChange={(v) => recvForm.setData('quantity', v)} error={recvForm.errors.quantity} />
                                    <TextInput size="xs" label="Batch expiry" type="date" w={150} value={recvForm.data.expiry_date} onChange={(e) => recvForm.setData('expiry_date', e.currentTarget.value)} />
                                    <TextInput size="xs" label="Lot no." w={130} value={recvForm.data.batch_number} onChange={(e) => recvForm.setData('batch_number', e.currentTarget.value)} />
                                    <Button size="xs" radius={8} color="teal" loading={recvForm.processing} onClick={receive}>Book in</Button>
                                    <Button size="xs" radius={8} variant="default" onClick={() => setReceiveFor(null)}>Cancel</Button>
                                </Group>
                            )}
                        </Box>
                    ))}
                </ViewCard>
            )}

            {/* Needs reordering */}
            <ViewCard title="Reorder" subtitle={needs.length ? `${needs.length} item${needs.length === 1 ? '' : 's'} at or below the reorder level` : 'Items at or below their reorder level'}>
                {!needs.length
                    ? emptyRow('Nothing needs reordering — every item is above its reorder level.')
                    : (
                        <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                            {needs.map((m, idx) => {
                                const st = STATUS[bucketOf(m)];
                                const already = onOrderIds.has(m.id);
                                return (
                                    <Box key={m.id} px={4} py={12} style={rowBorder(idx)}>
                                        <Group gap={14} wrap="nowrap" align="center">
                                            <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : 'indigo'} size={34} radius={10} style={{ flexShrink: 0 }}>{m.is_controlled ? <IconShieldLock size={16} /> : <IconPill size={16} />}</ThemeIcon>
                                            <Box style={{ flex: '2 1 180px', minWidth: 0 }}><Text fz={13.5} fw={700} c={TXT} truncate>{m.medication_name}</Text><Text fz={11.5} c="dimmed" truncate>{m.resident ?? '—'}</Text></Box>
                                            <Box style={{ width: 172, flexShrink: 0 }} visibleFrom="sm"><Text fz={12} c="dimmed">Have <Text span fw={700} c={TXT}>{m.stock_level ?? '—'}</Text> · reorder at {m.reorder_level ?? '—'}</Text></Box>
                                            <Box style={{ width: 96, flexShrink: 0, textAlign: 'right' }}><Text fz={11} c="dimmed">suggest</Text><Text fz={13} fw={800} c="#1F8A5B">+{suggested(m)}</Text></Box>
                                            <Box style={{ width: 110, flexShrink: 0, textAlign: 'right' }}>
                                                {already
                                                    ? <Badge color="teal" variant="light" radius="sm">On order</Badge>
                                                    : canAdjust
                                                        ? <Button size="xs" radius={8} variant="light" color="indigo" leftSection={<IconShoppingCart size={14} />} onClick={() => openOrder(m)}>Order</Button>
                                                        : <Box style={{ display: 'inline-flex', padding: '4px 11px', borderRadius: 999, fontSize: 10.5, fontWeight: 800, letterSpacing: 0.4, background: st.bg, color: st.c }}>{st.label.toUpperCase()}</Box>}
                                            </Box>
                                        </Group>
                                        {orderFor === m.id && canAdjust && (
                                            <Group gap={10} mt={10} pl={48} wrap="wrap" align="flex-end">
                                                <NumberInput size="xs" label="Quantity" w={110} min={1} value={orderForm.data.quantity} onChange={(v) => orderForm.setData('quantity', v)} error={orderForm.errors.quantity} />
                                                <TextInput size="xs" label="Supplier" w={170} placeholder="Supplying pharmacy" value={orderForm.data.supplier} onChange={(e) => orderForm.setData('supplier', e.currentTarget.value)} />
                                                <TextInput size="xs" label="Notes" w={170} value={orderForm.data.notes} onChange={(e) => orderForm.setData('notes', e.currentTarget.value)} />
                                                <Button size="xs" radius={8} color="indigo" loading={orderForm.processing} onClick={placeOrder}>Place order</Button>
                                                <Button size="xs" radius={8} variant="default" onClick={() => setOrderFor(null)}>Cancel</Button>
                                            </Group>
                                        )}
                                    </Box>
                                );
                            })}
                        </ScrollArea.Autosize>
                    )}
            </ViewCard>
        </Stack>
    );
}

// Disposal log — movements recorded as disposed, plus a structured capture form (#36).
function DisposalView({ transactions, meds = [], residents = [], canAdjust }) {
    const list = transactions.filter((t) => t.type === 'disposed');
    const [open, setOpen] = useState(false);
    const [pickResident, setPickResident] = useState('');
    const form = useForm({
        mar_sheet_id: '', transaction_type: 'disposed', quantity: '',
        reason: '', disposal_method: '', witness_name: '', notes: '', is_controlled: false, cd_schedule: '',
    });
    const selectedMed = meds.find((m) => String(m.id) === String(form.data.mar_sheet_id));
    const needWitness = !!selectedMed?.is_controlled;

    // Client-first flow: pick a resident (from the real residents list), then only their
    // medications are offered. Filter by client_id so it's robust to blank/duplicate names.
    const hasUnassigned = meds.some((m) => !m.client_id);
    const residentList = [
        ...residents.map((r) => ({ value: String(r.id), label: r.name })),
        ...(hasUnassigned ? [{ value: '__none__', label: 'No resident linked' }] : []),
    ];
    const medData = meds
        .filter((m) => (pickResident === '__none__' ? !m.client_id : String(m.client_id) === String(pickResident)))
        .map((m) => ({ value: String(m.id), label: `${m.medication_name}${m.is_controlled ? ' · CD' : ''}` }));
    const closeModal = () => { setOpen(false); setPickResident(''); form.reset(); form.clearErrors(); };

    const toast = (message) => notifications.show({
        message, autoClose: 3500, withBorder: true, icon: <IconCheck size={20} stroke={3} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: '#C43D6B', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: '#C43D6B', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 }, description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 }, closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });

    const submit = () => {
        form.clearErrors();
        const errs = {};
        if (!form.data.mar_sheet_id) errs.mar_sheet_id = 'Choose a medication.';
        if (!form.data.quantity || Number(form.data.quantity) <= 0) errs.quantity = 'Enter a quantity to dispose.';
        if (!form.data.reason) errs.reason = 'Pick a reason.';
        if (!form.data.disposal_method) errs.disposal_method = 'Pick a disposal method.';
        if (needWitness && !form.data.witness_name.trim()) errs.witness_name = 'A witness is required for controlled drugs.';
        if (Object.keys(errs).length) { form.setError(errs); return; }
        // Preserve the med's controlled flag/schedule so the adjust endpoint doesn't clear it.
        form.transform((d) => ({ ...d, is_controlled: selectedMed?.is_controlled ? 1 : 0, cd_schedule: selectedMed?.cd_schedule ?? '' }))
            .post(ADJUST, {
                preserveScroll: true,
                onSuccess: () => { toast(`Disposal recorded${selectedMed ? ` for ${selectedMed.medication_name}` : ''}.`); closeModal(); },
            });
    };

    const recordBtn = canAdjust
        ? <Button size="sm" radius={10} color="pink" leftSection={<IconTrash size={15} />} onClick={() => setOpen(true)}>Record disposal</Button>
        : null;

    return (
        <>
            <ViewCard title="Disposal" subtitle={list.length ? `${list.length} disposal${list.length === 1 ? '' : 's'} · newest first` : 'Medication disposed of'} action={recordBtn}>
                {!list.length
                    ? emptyRow('No disposals recorded yet.')
                    : (
                        <ScrollArea.Autosize mah="calc(100vh - 300px)" type="auto" offsetScrollbars scrollbarSize={8}>
                            {list.map((t, idx) => (
                                <Group key={t.id} gap={14} wrap="nowrap" align="center" px={4} py={13} style={rowBorder(idx)}>
                                    <ThemeIcon variant="light" color="red" size={36} radius={11} style={{ flexShrink: 0 }}><IconTrash size={16} /></ThemeIcon>
                                    <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13.5} fw={700} c={TXT} truncate>{t.medication_name}</Text>{t.reason && <Text fz={11.5} c="dimmed" truncate>“{t.reason}”</Text>}</Box>
                                    <Box style={{ width: 118, flexShrink: 0 }} visibleFrom="sm"><Text fz={12.5} c="dimmed">{t.date}</Text></Box>
                                    <Box style={{ width: 96, flexShrink: 0, textAlign: 'right' }}><Text fz={13} fw={800} c="#C43D6B">−{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text></Box>
                                    <Box style={{ width: 120, flexShrink: 0 }} visibleFrom="md"><Text fz={12} c="dimmed" truncate>{t.performed_by ?? '—'}</Text></Box>
                                </Group>
                            ))}
                        </ScrollArea.Autosize>
                    )}
            </ViewCard>

            <Modal opened={open} onClose={closeModal} title={<Text fw={800} fz="lg" c={TXT}>Record disposal</Text>} radius={18} centered size="md" overlayProps={{ backgroundOpacity: 0.55, blur: 3 }} zIndex={1000}>
                <Stack gap={12}>
                    <Select label="Resident" placeholder="Choose a resident…" searchable data={residentList}
                        value={pickResident} onChange={(v) => { setPickResident(v || ''); form.setData('mar_sheet_id', ''); }} comboboxProps={{ withinPortal: true }} />
                    <Select label="Medication" placeholder={pickResident ? 'Choose a medication…' : 'Pick a resident first'} searchable disabled={!pickResident} data={medData}
                        value={form.data.mar_sheet_id} onChange={(v) => form.setData('mar_sheet_id', v || '')} error={form.errors.mar_sheet_id} comboboxProps={{ withinPortal: true }} nothingFoundMessage="No medications for this resident" />
                    {selectedMed && (
                        <Text fz={11.5} c="dimmed" mt={-6}>In stock: <Text span fw={700} c={TXT}>{selectedMed.stock_level ?? '—'} {selectedMed.unit ?? 'units'}</Text>{selectedMed.is_controlled ? ' · Controlled drug' : ''}</Text>
                    )}
                    <Group grow gap={10} align="flex-start">
                        <NumberInput label="Quantity to dispose" placeholder="e.g. 6" min={0} value={form.data.quantity} onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} />
                        <Select label="Reason" placeholder="Why…" data={DISPOSAL_REASONS} value={form.data.reason} onChange={(v) => form.setData('reason', v || '')} error={form.errors.reason} comboboxProps={{ withinPortal: true }} />
                    </Group>
                    <Select label="Disposal method" placeholder="How it was disposed of…" data={DISPOSAL_METHODS} value={form.data.disposal_method} onChange={(v) => form.setData('disposal_method', v || '')} error={form.errors.disposal_method} comboboxProps={{ withinPortal: true }} />
                    <TextInput label={`Witness${needWitness ? ' (required for CDs)' : ''}`} placeholder="Second staff member" value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} error={form.errors.witness_name} />
                    {needWitness && <Text fz={11} c="#B9660F" mt={-6}>Controlled drug — a second person must witness the disposal (e.g. CD destruction kit).</Text>}
                    <Textarea label="Notes" placeholder="Optional detail about this disposal…" autosize minRows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                    <Group justify="flex-end" gap={10} mt={4}>
                        <Button variant="default" radius={10} onClick={closeModal}>Cancel</Button>
                        <Button color="pink" radius={10} loading={form.processing} leftSection={<IconTrash size={15} />} onClick={submit}>Record disposal</Button>
                    </Group>
                </Stack>
            </Modal>
        </>
    );
}

export default function Stock2({ meds = [], transactions = [], stats = {}, orders = [], residents = [] }) {
    const role = useRole();
    const canAdjust = role === 'manager';
    const railBelow = useMediaQuery('(max-width: 1000px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    const PAGE_ZOOM = 0.82;

    const [view, setView] = useState('overview');
    const [tab, setTab] = useState('all');
    const [query, setQuery] = useState('');
    const [residentF, setResidentF] = useState('any');
    const [cdF, setCdF] = useState('all');
    const [sort, setSort] = useState({ key: 'medication', dir: 'asc' });
    const [selected, setSelected] = useState(null);
    const [panelMed, setPanelMed] = useState(null);
    const [searchFocus, setSearchFocus] = useState(false);
    const searchRef = useRef(null);

    // Press "/" anywhere (outside a field) to jump into the search box.
    useEffect(() => {
        const onKey = (e) => {
            if (e.key === '/' && !/^(input|textarea|select)$/i.test(document.activeElement?.tagName || '')) {
                e.preventDefault();
                searchRef.current?.focus();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    const loadedAt = useMemo(() => new Date(), [meds]);
    const loadedTime = loadedAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    const openPanel = (m) => {
        if (selected && selected.id === m.id) { setSelected(null); return; }
        setPanelMed(m); setSelected(m);
    };
    const closePanel = () => setSelected(null);

    // Barcode scan (#34) — match a scanned/typed code to a medicine and open it.
    const [scan, setScan] = useState('');
    const runScan = () => {
        const v = scan.trim();
        if (!v) return;
        const hit = meds.find((m) => (m.barcode || '').trim() && (m.barcode || '').trim() === v);
        if (hit) {
            setView('overview'); setQuery(''); setTab('all');
            setPanelMed(hit); setSelected(hit); setScan('');
        } else {
            notifications.show({ message: `No medicine matches barcode “${v}”.`, color: 'red', autoClose: 3000 });
        }
    };

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
        const val = (m) => {
            switch (sort.key) {
                case 'stock': return Number(m.stock_level ?? -1);
                case 'expiry': return m.expiry_date ? Date.parse(m.expiry_date) || 0 : 0;
                case 'status': return ['expired', 'out', 'low', 'expiring', 'healthy'].indexOf(m.bucket);
                default: return String(m.medication_name ?? '').toLowerCase();
            }
        };
        list = [...list].sort((a, b) => {
            const x = val(a), y = val(b);
            const cmp = typeof x === 'number' ? x - y : String(x).localeCompare(String(y), undefined, { numeric: true });
            return sort.dir === 'asc' ? cmp : -cmp;
        });
        return list;
    }, [rows, tab, query, residentF, cdF, sort]);

    // Distinct residents present in the stock list, for the Resident filter.
    const residentOptions = useMemo(() => {
        const names = [...new Set(meds.map((m) => m.resident).filter(Boolean))].sort((a, b) => a.localeCompare(b));
        return [{ value: 'any', label: 'All residents' }, ...names.map((n) => ({ value: n, label: n }))];
    }, [meds]);

    const toggleSort = (k) => setSort((s) => ({ key: k, dir: s.key === k && s.dir === 'asc' ? 'desc' : 'asc' }));
    const sortHead = (label, k, style, vis = {}) => {
        const active = sort.key === k;
        return (
            <Box component="button" type="button" onClick={() => toggleSort(k)} {...vis}
                style={{ ...style, display: 'flex', alignItems: 'center', gap: 3, background: 'none', border: 'none', padding: 0, cursor: 'pointer' }}>
                <Text component="span" fz={10.5} fw={800} tt="uppercase" c={active ? TXT : 'light-dark(#55647D, #9AA8BE)'} style={{ letterSpacing: 0.6 }}>{label}</Text>
                <Box component="span" style={{ display: 'inline-flex', color: active ? '#1F9E93' : undefined, opacity: active ? 1 : 0.4 }}>
                    {active ? (sort.dir === 'asc' ? <IconChevronUp size={12} stroke={2.6} /> : <IconChevronDown size={12} stroke={2.6} />) : <IconSelector size={12} stroke={1.8} />}
                </Box>
            </Box>
        );
    };

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

    const TABS = [
        { k: 'all', l: 'All items', n: rows.length, tint: '#1C9C90' },
        { k: 'low', l: 'Low stock', n: counts.low, tint: '#E8842B' },
        { k: 'out', l: 'Out of stock', n: counts.out, tint: '#E4574C' },
        { k: 'expiring', l: 'Expiring soon', n: counts.expiring, tint: '#8A63C9' },
        { k: 'expired', l: 'Expired', n: counts.expired, tint: '#C43D6B' },
    ];

    // Inline filter/sort segments that live inside the table header.
    const INLINE = [
        { key: 'status', label: 'Status', w: 128, value: tab, set: (v) => setTab(v || 'all'), data: [
            { value: 'all', label: 'All status' }, { value: 'healthy', label: 'In stock' }, { value: 'expiring', label: 'Expiring soon' },
            { value: 'low', label: 'Low stock' }, { value: 'out', label: 'Out of stock' }, { value: 'expired', label: 'Expired' },
        ] },
        { key: 'resident', label: 'Resident', w: 150, icon: IconUser, tint: '#3A7CA5', value: residentF, set: (v) => setResidentF(v || 'any'), data: residentOptions },
        { key: 'cd', label: 'Controlled', w: 138, icon: IconShieldLock, tint: '#8A63C9', value: cdF, set: (v) => setCdF(v || 'all'), data: [
            { value: 'all', label: 'All meds' }, { value: 'cd', label: 'Controlled only' }, { value: 'noncd', label: 'Non-controlled' },
        ] },
        { key: 'sort', label: 'Sort', w: 156, icon: IconArrowsSort, tint: '#5A6B84', value: `${sort.key}-${sort.dir}`,
            set: (v) => { const i = (v || '').lastIndexOf('-'); setSort({ key: v.slice(0, i), dir: v.slice(i + 1) }); }, data: [
            { value: 'medication-asc', label: 'Medication A–Z' }, { value: 'medication-desc', label: 'Medication Z–A' },
            { value: 'stock-asc', label: 'Stock: low first' }, { value: 'stock-desc', label: 'Stock: high first' },
            { value: 'expiry-asc', label: 'Expiry: soonest' }, { value: 'status-asc', label: 'Most urgent' },
        ] },
    ];
    const anyFilter = tab !== 'all' || residentF !== 'any' || cdF !== 'all';

    // Donut — every med by health bucket (mutually exclusive → slices sum to the total).
    const SEG = [
        { key: 'healthy', label: 'In stock', count: counts.healthy, color: STATUS.healthy.dot },
        { key: 'expiring', label: 'Expiring soon', count: counts.expiring, color: STATUS.expiring.dot },
        { key: 'low', label: 'Low stock', count: counts.low, color: STATUS.low.dot },
        { key: 'out', label: 'Out of stock', count: counts.out, color: STATUS.out.dot },
        { key: 'expired', label: 'Expired', count: counts.expired, color: STATUS.expired.dot },
    ];
    const total = rows.length;
    const pct = (n) => (total ? Math.round((n / total) * 100) : 0);
    const sections = SEG.filter((s) => s.count > 0).map((s) => ({
        value: pct(s.count), color: s.color,
        tooltip: `${s.label} — ${s.count} ${s.count === 1 ? 'item' : 'items'} (${pct(s.count)}%)`,
    }));
    const attention = counts.low + counts.out + counts.expiring + counts.expired;

    return (
        <AppShell title="Medication stock" section="Medication">
            <Head title="Medication stock" />
            <Box px={{ base: 0, sm: 10 }} pb={14} style={{ zoom: PAGE_ZOOM }}>
                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb={20}>
                    <Stack gap={12}>
                        <Group gap={13} wrap="nowrap" align="center">
                            <ThemeIcon size={44} radius={13} style={{ background: 'light-dark(#E7EEF9, rgba(58,124,165,0.18))', color: '#3A7CA5', flexShrink: 0 }}><IconBox size={23} stroke={1.9} /></ThemeIcon>
                            <Box style={{ minWidth: 0 }}>
                                <Text fz={30} fw={800} c={TXT} lh={1.2}>Medication stock</Text>
                                <Text fz={12.5} fw={700} c="light-dark(#48586F, #9AA8BE)" mt={2}>{[`${total} medication${total === 1 ? '' : 's'}`, attention ? `${attention} need attention` : 'all healthy'].join(' · ')}</Text>
                            </Box>
                        </Group>
                        <Group gap={8} wrap="nowrap" style={{ width: 'fit-content', background: 'light-dark(#E3E9F2, rgba(255,255,255,0.05))', borderRadius: 999, padding: '3px 4px 3px 12px' }}>
                            <Box w={7} h={7} style={{ borderRadius: '50%', background: '#1F9E93', boxShadow: '0 0 0 3px rgba(31,158,147,0.18)' }} />
                            <Text fz={11.5} fw={600} c="light-dark(#48576E, #A9B6CB)">Live · data as of {loadedTime}</Text>
                            <Tooltip label="Refresh now" withArrow><ActionIcon size="sm" variant="filled" radius="xl" aria-label="Refresh data" onClick={() => router.reload()} style={{ background: 'light-dark(#1C325A, #1F9E93)' }}><IconRefresh size={13} stroke={2.6} /></ActionIcon></Tooltip>
                        </Group>
                    </Stack>
                    <Group gap={10} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <TextInput
                            h={42} radius={14} w={isSm ? '100%' : 200}
                            placeholder="Scan barcode…"
                            leftSection={<IconBarcode size={16} />}
                            value={scan} onChange={(e) => setScan(e.currentTarget.value)}
                            onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); runScan(); } }}
                            rightSection={scan ? <ActionIcon size="sm" variant="subtle" color="gray" radius="xl" aria-label="Clear scan" onClick={() => setScan('')}><IconX size={13} /></ActionIcon> : null}
                            styles={{ input: { height: 42, borderRadius: 14, boxShadow: '0 6px 16px -10px rgba(20,50,80,0.4)' } }}
                        />
                        <Tooltip label="Open the Controlled Drug register" withArrow openDelay={300}>
                            <Button variant="light" color="grape" h={42} radius={14} leftSection={<IconShieldLock size={16} />}
                                onClick={() => router.visit('/frontend2/controlled-drugs')}
                                style={{ boxShadow: '0 6px 16px -10px rgba(20,50,80,0.4)' }}>CD register</Button>
                        </Tooltip>
                        <Tooltip label="Export the current filtered view as CSV" withArrow openDelay={300}>
                            <Button variant="default" h={42} radius={14} leftSection={<IconDownload size={16} />} onClick={exportCsv}
                                style={{ boxShadow: '0 6px 16px -10px rgba(20,50,80,0.4)' }}>Export</Button>
                        </Tooltip>
                        {canAdjust && (
                            <Tooltip label="Pick a medication below, then adjust it" withArrow openDelay={300}>
                                <Button h={42} radius={14} leftSection={<IconPlus size={16} />} onClick={() => shown[0] && openPanel(shown[0])}
                                    style={{ background: 'light-dark(#1C325A, #1F9E93)', paddingInline: 20, boxShadow: 'light-dark(0 10px 22px -8px rgba(28,50,90,0.45), 0 10px 22px -8px rgba(31,158,147,0.45))' }}>Adjust stock</Button>
                            </Tooltip>
                        )}
                    </Group>
                </Group>

                {/* Sub-view tabs — Stock overview / Transactions / Reorder / Disposal */}
                <Group gap={4} wrap="nowrap" mb={20} style={{ width: 'fit-content', maxWidth: '100%', overflowX: 'auto', background: 'light-dark(#E9EDF4, var(--mantine-color-dark-8))', borderRadius: 14, padding: 5, border: '1px solid light-dark(#D5DDEA, rgba(150,172,205,0.22))', boxShadow: 'inset 0 1px 3px light-dark(rgba(19,35,63,0.10), rgba(0,0,0,0.30)), 0 10px 24px -14px rgba(19,35,63,0.5)' }}>
                    {[
                        { k: 'overview', l: 'Stock overview', icon: IconLayoutGrid },
                        { k: 'transactions', l: 'Transactions', icon: IconArrowsExchange },
                        { k: 'count', l: 'Stock count', icon: IconClipboardCheck },
                        { k: 'reorder', l: 'Reorder', icon: IconShoppingCart },
                        { k: 'disposal', l: 'Disposal', icon: IconTrash },
                    ].map((v) => {
                        const on = view === v.k;
                        return (
                            <Box key={v.k} component="button" type="button" onClick={() => setView(v.k)}
                                onMouseEnter={(e) => { if (!on) e.currentTarget.style.background = 'light-dark(#DCE2EC, var(--mantine-color-dark-6))'; }}
                                onMouseLeave={(e) => { if (!on) e.currentTarget.style.background = 'transparent'; }}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 8, border: 'none', cursor: 'pointer', borderRadius: 10,
                                    padding: '9px 17px', fontSize: 13.5, fontWeight: on ? 800 : 700, whiteSpace: 'nowrap', transition: 'background .12s ease',
                                    background: on ? 'light-dark(#ffffff, rgba(31,158,147,0.18))' : 'transparent',
                                    color: on ? 'light-dark(#1C325A, #6FE0DB)' : 'light-dark(#5A6B84, #94A2B8)',
                                    boxShadow: on ? 'light-dark(0 2px 6px -2px rgba(16,24,40,0.2), inset 0 0 0 1px rgba(69,193,191,0.5))' : 'none',
                                }}>
                                <v.icon size={16} stroke={1.9} />{v.l}
                            </Box>
                        );
                    })}
                </Group>

                {view === 'transactions' && <TransactionsView transactions={transactions} />}
                {view === 'count' && <CountView meds={rows} canAdjust={canAdjust} />}
                {view === 'reorder' && <ReorderView meds={rows} orders={orders} canAdjust={canAdjust} />}
                {view === 'disposal' && <DisposalView transactions={transactions} meds={rows} residents={residents} canAdjust={canAdjust} />}

                {view === 'overview' && (<>
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 20, alignItems: 'flex-start', justifyContent: 'center' }}>
                    {/* Table */}
                    <Box style={{ ...card, background: BANNER_GRADIENT_REV, flex: '1 1 480px', minWidth: 0, maxWidth: '96%', padding: isSm ? '16px 14px' : '22px 24px' }}>
                        {/* Row 1 — title + search + filter/sort bar */}
                        <Group justify="space-between" align="flex-start" wrap="wrap" gap="xl" pb={18}>
                            <Box style={{ flexShrink: 0, maxWidth: '100%' }}>
                                <Box style={{
                                    borderRadius: 999, padding: 2,
                                    background: searchFocus
                                        ? 'linear-gradient(120deg, #23B2A4 0%, #1C325A 100%)'
                                        : 'light-dark(linear-gradient(120deg, #C7D2E4 0%, #DCE4F0 100%), linear-gradient(120deg, rgba(150,172,205,0.4) 0%, rgba(255,255,255,0.08) 100%))',
                                    boxShadow: searchFocus
                                        ? '0 12px 28px -12px rgba(28,156,144,0.65)'
                                        : '0 12px 26px -12px rgba(19,35,63,0.4), 0 2px 6px -2px rgba(19,35,63,0.18)',
                                    transition: 'background .2s ease, box-shadow .2s ease',
                                }}>
                                    <TextInput
                                        ref={searchRef}
                                        size="md" radius="xl" w={440} maw="100%"
                                        placeholder="Search medication or resident…"
                                        onFocus={() => setSearchFocus(true)} onBlur={() => setSearchFocus(false)}
                                        leftSection={
                                            <ThemeIcon size={24} radius="xl" style={{ background: 'light-dark(#E4F3F1, rgba(31,158,147,0.20))', color: '#1F9E93', boxShadow: '0 2px 6px -3px rgba(31,158,147,0.6)' }}>
                                                <IconSearch size={13} stroke={2.2} />
                                            </ThemeIcon>
                                        }
                                        leftSectionWidth={40}
                                        value={query} onChange={(e) => setQuery(e.currentTarget.value)}
                                        rightSection={query
                                            ? <ActionIcon size="md" variant="subtle" color="gray" radius="xl" aria-label="Clear search" onClick={() => setQuery('')}><IconX size={15} /></ActionIcon>
                                            : <Box component="kbd" style={{ fontSize: 11, fontWeight: 700, fontFamily: 'inherit', color: 'light-dark(#8A97AB, #7D8AA0)', background: 'light-dark(#EEF2F8, rgba(255,255,255,0.06))', border: '1px solid light-dark(#DFE5EE, rgba(255,255,255,0.10))', borderRadius: 7, padding: '2px 8px', lineHeight: 1 }}>/</Box>}
                                        rightSectionWidth={40}
                                        styles={{ input: { height: 38, minHeight: 38, borderRadius: 999, border: '1px solid light-dark(#D5DDEA, rgba(150,172,205,0.28))', background: 'light-dark(#ffffff, var(--mantine-color-dark-7))', fontSize: 13.5, fontWeight: 500, boxShadow: 'inset 0 2px 5px rgba(19,35,63,0.08)' } }}
                                    />
                                </Box>
                            </Box>
                            <Group gap={10} wrap="wrap" style={{ flexShrink: 0 }}>
                                {INLINE.filter((f) => f.key !== 'status').map((f) => (
                                    <Group key={f.key} gap={9} wrap="nowrap" style={{
                                        flexShrink: 0, background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
                                        border: '1px solid light-dark(#E6EAF0, rgba(150,172,205,0.22))', borderRadius: 14,
                                        padding: '5px 8px', boxShadow: '0 4px 14px -10px rgba(20,50,80,0.5)',
                                    }}>
                                        <ThemeIcon size={30} radius={10} style={{ flexShrink: 0, background: `${f.tint}1A`, color: f.tint }}>
                                            <f.icon size={16} stroke={1.9} />
                                        </ThemeIcon>
                                        <Box style={{ flexShrink: 0 }}>
                                            <Text fz={8.5} fw={700} tt="uppercase" c="light-dark(#8A97AB, #7D8AA0)" lh={1} style={{ letterSpacing: 0.6, marginBottom: 2 }}>{f.label}</Text>
                                            <Select
                                                variant="unstyled" size="xs" w={f.w} allowDeselect={false}
                                                comboboxProps={{ withinPortal: true, radius: 12, shadow: 'lg', offset: 6 }}
                                                value={f.value} onChange={f.set} data={f.data}
                                                rightSection={<IconChevronDown size={13} stroke={2.2} />} rightSectionWidth={17}
                                                styles={{
                                                    input: { fontWeight: 700, fontSize: 13, color: TXT, minHeight: 18, height: 18, lineHeight: '18px', paddingRight: 17 },
                                                    section: { color: 'light-dark(#98A5B8, #7D8AA0)' },
                                                }}
                                            />
                                        </Box>
                                    </Group>
                                ))}
                                {anyFilter && (
                                    <Box component="button" type="button" onClick={() => { setTab('all'); setResidentF('any'); setCdF('all'); }}
                                        title="Reset filters" aria-label="Reset filters"
                                        onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#FBE1DF, rgba(228,87,76,0.16))'; e.currentTarget.style.color = '#C0413A'; e.currentTarget.style.borderColor = '#F0B3AE'; }}
                                        onMouseLeave={(e) => { e.currentTarget.style.background = 'light-dark(#ffffff, var(--mantine-color-dark-6))'; e.currentTarget.style.color = 'light-dark(#7A879B, #94A2B8)'; e.currentTarget.style.borderColor = 'light-dark(#E6EAF0, rgba(150,172,205,0.22))'; }}
                                        style={{ flexShrink: 0, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', alignSelf: 'center',
                                            border: '1px solid light-dark(#E6EAF0, rgba(150,172,205,0.22))', borderRadius: '50%', width: 40, height: 40, transition: 'background .14s, color .14s, border-color .14s',
                                            boxShadow: '0 4px 14px -10px rgba(20,50,80,0.5)',
                                            background: 'light-dark(#ffffff, var(--mantine-color-dark-6))', color: 'light-dark(#7A879B, #94A2B8)' }}>
                                        <IconX size={16} stroke={2.4} />
                                    </Box>
                                )}
                            </Group>
                        </Group>

                        <Divider mb={16} color="light-dark(#EDF1F6, var(--mantine-color-dark-4))" />

                        {/* Row 2 — status pills */}
                        <Group gap={7} wrap="wrap" mb={16}>
                            {TABS.map((t) => {
                                const on = tab === t.k;
                                return (
                                    <Box key={t.k} component="button" type="button" onClick={() => setTab(t.k)}
                                        onMouseEnter={(e) => { if (!on) { e.currentTarget.style.transform = 'translateY(-1px)'; e.currentTarget.style.boxShadow = '0 8px 16px -9px rgba(20,50,80,0.5)'; } }}
                                        onMouseLeave={(e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = on ? `0 8px 18px -8px ${t.tint}` : '0 4px 12px -9px rgba(20,50,80,0.4)'; }}
                                        style={{
                                            display: 'inline-flex', alignItems: 'center', gap: 8, cursor: 'pointer', whiteSpace: 'nowrap',
                                            border: on ? '1px solid transparent' : '1px solid light-dark(#E6EAF0, rgba(150,172,205,0.22))',
                                            background: on ? `linear-gradient(180deg, rgba(255,255,255,0.20) 0%, rgba(0,0,0,0.08) 100%), ${t.tint}` : 'light-dark(#ffffff, var(--mantine-color-dark-6))',
                                            borderRadius: 999, padding: on ? '6px 8px 6px 13px' : '6px 13px',
                                            boxShadow: on ? `0 8px 18px -8px ${t.tint}` : '0 4px 12px -9px rgba(20,50,80,0.4)',
                                            transition: 'transform .14s ease, box-shadow .14s ease',
                                        }}>
                                        <Box component="span" w={8} h={8} style={{ borderRadius: '50%', background: on ? '#ffffff' : t.tint, flexShrink: 0 }} />
                                        <Text fz={13} fw={on ? 700 : 600} c={on ? '#ffffff' : 'light-dark(#3E4C63, #C7D1E0)'}>{t.l}</Text>
                                        <Box component="span" style={{
                                            minWidth: 22, textAlign: 'center', borderRadius: 999, padding: '1px 7px', fontSize: 12, fontWeight: 800,
                                            background: on ? '#ffffff' : 'light-dark(#EEF1F6, rgba(255,255,255,0.06))',
                                            color: on ? t.tint : 'light-dark(#6A7A92, #94A2B8)',
                                        }}>{t.n}</Box>
                                    </Box>
                                );
                            })}
                        </Group>

                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={4} pb={10} style={{ borderBottom: '1px solid light-dark(#E3E8EF, var(--mantine-color-dark-4))' }}>
                            {sortHead('Medication', 'medication', { flex: '2 1 200px' })}
                            {sortHead('Stock', 'stock', { flex: '1 1 130px' }, { visibleFrom: 'sm' })}
                            {sortHead('Expiry', 'expiry', { width: 96 }, { visibleFrom: 'md' })}
                            {sortHead('Status', 'status', { width: 110 })}
                            <Box style={{ width: 18 }} />
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" fw={600} c="light-dark(#48586F, #9AA8BE)" ta="center" py={48}>{query ? `No matches for “${query}”.` : 'No medications in this view.'}</Text>
                            : (
                                <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                                    {shown.map((m, idx) => {
                                        const st = STATUS[m.bucket];
                                        const bar = stockBar(m);
                                        const fc = computeForecast(m, transactions);
                                        return (
                                            <Group key={m.id} gap={14} wrap="nowrap" align="center" px={4} py={15}
                                                role="button" tabIndex={0} aria-label={`${m.medication_name}, ${m.stock_level ?? 'unknown'} ${m.unit ?? 'units'}, ${st.label}`}
                                                onClick={() => openPanel(m)}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(m); } }}
                                                style={{ cursor: 'pointer', borderTop: idx ? '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))' : 'none', borderRadius: 10, transition: 'background .15s' }}
                                                onFocus={(e) => { e.currentTarget.style.background = 'light-dark(rgba(28,50,90,0.20), var(--mantine-color-dark-5))'; e.currentTarget.style.outline = '2px solid #1F9E93'; e.currentTarget.style.outlineOffset = '-2px'; }}
                                                onBlur={(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.outline = 'none'; }}
                                                onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(rgba(28,50,90,0.15), var(--mantine-color-dark-5))'; }}
                                                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                                                {/* Medication */}
                                                <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 200px', minWidth: 0 }}>
                                                    <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : 'indigo'} size={38} radius={11} style={{ flexShrink: 0, boxShadow: '0 4px 10px -5px rgba(20,50,80,0.4)' }}>
                                                        {m.is_controlled ? <IconShieldLock size={18} /> : <IconPill size={18} />}
                                                    </ThemeIcon>
                                                    <Box style={{ minWidth: 0 }}>
                                                        <Group gap={6} wrap="nowrap" style={{ minWidth: 0 }}>
                                                            <Text fz={13.5} fw={700} c={TXT} truncate>{m.medication_name}</Text>
                                                            {m.is_controlled && <Badge size="xs" color="grape" variant="light" radius="sm" style={{ flexShrink: 0 }}>CD</Badge>}
                                                        </Group>
                                                        <Text fz={11.5} c="dimmed" truncate>{m.resident ?? '—'}</Text>
                                                    </Box>
                                                </Group>
                                                {/* Stock */}
                                                <Box style={{ flex: '1 1 130px', minWidth: 0 }} visibleFrom="sm">
                                                    <Group gap={8} wrap="nowrap" mb={4}>
                                                        <Text fz={13} fw={800} c={TXT} lh={1}>{m.stock_level ?? '—'}</Text>
                                                        <Text fz={11} c="dimmed">{m.unit ?? 'units'}</Text>
                                                        {fc && <Text fz={10.5} fw={700} c={forecastTone(fc.daysLeft)}>· ≈{fc.daysLeft}d left</Text>}
                                                    </Group>
                                                    <Progress value={bar.pct} color={bar.color} radius="xl" size="sm" />
                                                </Box>
                                                {/* Expiry */}
                                                <Box style={{ width: 96, flexShrink: 0 }} visibleFrom="md">
                                                    <Text fz={12.5} fw={700} c={m.expired ? '#C0413A' : m.expiring_soon ? '#B9660F' : TXT} lh={1.2}>{m.expiry_date ?? '—'}</Text>
                                                </Box>
                                                {/* Status */}
                                                <Box style={{ width: 110, flexShrink: 0 }}>
                                                    <Box style={{ display: 'inline-flex', alignItems: 'center', padding: '4px 11px', borderRadius: 999, fontSize: 10.5, fontWeight: 800, letterSpacing: 0.4, background: st.bg, color: st.c, boxShadow: '0 3px 8px -4px rgba(20,50,80,0.35)' }}>{st.label.toUpperCase()}</Box>
                                                </Box>
                                                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}><IconChevronRight size={16} /></ActionIcon>
                                            </Group>
                                        );
                                    })}
                                </ScrollArea.Autosize>
                            )}
                    </Box>

                    {/* Detail panel — slides in on row click */}
                    <Box onTransitionEnd={() => { if (!selected) setPanelMed(null); }}
                        style={{ flexBasis: selected ? (railBelow ? '100%' : 420) : 0, flexGrow: 0, flexShrink: 1, minWidth: 0, alignSelf: 'stretch', opacity: selected ? 1 : 0, transition: 'flex-basis .32s ease, opacity .28s ease', overflow: 'hidden' }}>
                        {panelMed && <MedPanel key={panelMed.id} med={panelMed} transactions={transactions} canAdjust={canAdjust} onClose={closePanel} onDone={closePanel} />}
                    </Box>
                </Box>

                </>)}
            </Box>
        </AppShell>
    );
}
