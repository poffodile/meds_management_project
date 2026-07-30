import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useMediaQuery } from '@mantine/hooks';
import { notifications } from '@mantine/notifications';
import {
    Box, Group, Stack, Text, Button, ThemeIcon, TextInput, ActionIcon, ScrollArea,
    Tooltip, Divider, Select, Textarea, NumberInput, Checkbox, Modal,
} from '@mantine/core';
import {
    IconBox, IconSearch, IconRefresh, IconDownload, IconPlus, IconX, IconCheck,
    IconPill, IconShieldLock, IconSelector, IconChevronUp, IconChevronDown, IconChevronRight,
    IconLayoutGrid, IconArrowsExchange, IconShoppingCart, IconTrash,
    IconArrowsSort, IconUser, IconClipboardCheck, IconBarcode, IconAlertTriangle,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';
import { palette, statusPalette } from '@frontend/tokens';
import { MedTile, CdTag, StockStatusChip, MutedBar } from '@frontend/components/MedStockAtoms';
import { HEADING_FONT } from '@frontend/lib/font';

const PAGE = '/frontend2/stock-2';
const ADJUST = '/frontend2/stock-2/adjust';
const COUNT = '/frontend2/stock-2/count';
const ORDER = '/frontend2/stock-2/order';
const ORDER_RECEIVE = '/frontend2/stock-2/order/receive';
const ORDER_CANCEL = '/frontend2/stock-2/order/cancel';

/* ── Design tokens — from the unified palette in frontend/tokens.js (single source) ── */
const TXT = palette.ink;          // primary text (name kept for reach)
const INK2 = palette.ink2;        // secondary text
const FAINT = palette.faint;      // captions / units
const LINE = palette.line;
const numeric = { fontVariantNumeric: 'tabular-nums', fontFeatureSettings: '"tnum" 1' };
// Table surface keeps the soft blue wash in light mode, flat dark-6 in dark.
const BANNER_GRADIENT = palette.cardBg;
const BANNER_GRADIENT_REV = palette.tableBg;
const card = {
    background: palette.cardBg,
    borderRadius: 18,
    border: '1px solid light-dark(#ECEEF2, var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,29,54,0.04), 0 18px 40px -30px rgba(16,29,54,0.42)',
};

// Stock health buckets — unified 5-state palette (dark-mode aware).
const STATUS = statusPalette;
// Status column uses the shared StockStatusChip (kept name StatusChip for reach).
const StatusChip = StockStatusChip;

const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;
function bucketOf(m) {
    if (m.expired) return 'expired';
    if (isOut(m)) return 'out';
    if (m.low) return 'low';
    if (m.expiring_soon) return 'expiring';
    return 'healthy';
}
// Stock bar — how full relative to (roughly) twice the reorder level.
// `hex` is the muted fill used by the calm Box bar; `color` kept for reference.
function stockBar(m) {
    const s = m.stock_level;
    if (s === null || s === undefined) return { pct: 0, color: 'gray', hex: '#C3CBD6' };
    const n = Number(s);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    if (m.expired || isOut(m)) return { pct, color: 'red', hex: '#B4544A' };
    if (pct < 25 || n <= 5) return { pct, color: 'orange', hex: '#BF8A3C' };
    if (pct < 45 || n <= 12) return { pct, color: 'yellow', hex: '#C9A94E' };
    return { pct, color: 'teal', hex: '#3E8E77' };
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
    if (daysLeft <= 7) return '#B4544A';
    if (daysLeft <= 14) return '#BF8A3C';
    return INK2;
}

// Transaction type → label + muted colour.
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

// Slide-in detail panel — med details + per-med movements + (managers) an inline adjust form.
// Stock adjustment form — receive / dispose / return / correct ONE medication.
// Owns its own form state so it's reusable both in the detail panel and in the
// top-of-page "Adjust stock" modal. `key={med.id}` on the caller re-inits it per med.
function AdjustForm({ med, onDone }) {
    const form = useForm({
        mar_sheet_id: med.id, transaction_type: 'received', quantity: '', expiry_date: '',
        is_controlled: !!med.is_controlled, cd_schedule: med.cd_schedule ?? '',
        reason: '', disposal_method: '', witness_name: '', notes: '', batch_number: '', supplier: '', barcode: med.barcode ?? '',
    });
    const toast = (message, ok = true) => notifications.show({
        message, autoClose: ok ? 3500 : 5000, withBorder: true,
        icon: ok ? <IconCheck size={20} stroke={3} /> : <IconAlertTriangle size={20} stroke={2.4} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: ok ? '#1F9E93' : '#C0413A', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: ok ? '#1F9E93' : '#C0413A', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 },
            description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 },
            closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });
    const submit = () => {
        if (form.data.quantity !== '' && Number(form.data.quantity) < 0) { toast('Quantity cannot be negative.', false); return; }
        form.post(ADJUST, {
            preserveScroll: true,
            onSuccess: () => { toast(`${med.medication_name} stock updated.`); form.reset(); onDone?.(); },
            onError: (e) => toast(Object.values(e)[0] || 'Could not save the adjustment — please try again.', false),
        });
    };
    const isDisposal = form.data.transaction_type === 'disposed';
    const isReceived = form.data.transaction_type === 'received';
    // Shared field shape — softly rounded inputs, quiet labels; keeps the form calm and consistent.
    const R = 11;
    // Contextual accent for the movement type — muted, meaning-led (received = teal, disposed = rose, etc.).
    const typeColor = isReceived ? 'light-dark(#208280, #45C1BF)'
        : isDisposal ? 'light-dark(#A6506A, #D394A9)'
        : form.data.transaction_type === 'returned' ? 'light-dark(#4E6B9A, #93ACD6)'
        : 'light-dark(#9C6B22, #E6C594)';
    const fieldStyles = {
        label: { fontSize: 10.5, fontWeight: 600, color: FAINT, marginBottom: 2, textTransform: 'uppercase', letterSpacing: 0.4 },
        description: { fontSize: 9.5, marginBottom: 2 },
        input: { borderRadius: R, border: '1px solid light-dark(#CBD4E0, var(--mantine-color-dark-4))' },
    };
    // Meaning-tinted grouping panels — soft, not bright.
    const batchBox = { borderRadius: 12, padding: '9px 10px', background: 'light-dark(#EFF6F3, rgba(62,142,119,0.07))', border: '1px solid light-dark(#D5E7DF, rgba(62,142,119,0.20))' };
    const cdBox = { borderRadius: 12, padding: '9px 10px', background: 'light-dark(#F5F0F8, rgba(155,104,152,0.08))', border: '1px solid light-dark(#E6DCED, rgba(155,104,152,0.22))' };
    return (
        <Stack gap={8}>
            <Select label="Type" data={ADJUST_TYPES} size="xs" value={form.data.transaction_type} onChange={(v) => form.setData('transaction_type', v)} radius={R} styles={{ ...fieldStyles, input: { ...fieldStyles.input, color: typeColor, fontWeight: 600 } }} comboboxProps={{ withinPortal: true, zIndex: 1300, radius: 12, shadow: 'lg' }} />
            <Group grow gap={8} align="flex-start">
                <NumberInput label="Quantity" placeholder="e.g. 28" size="xs" min={0} value={form.data.quantity} onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} description="Blank = details only" radius={R} styles={fieldStyles} />
                <TextInput label={isReceived ? 'Batch expiry' : 'New expiry'} type="date" size="xs" value={form.data.expiry_date} onChange={(e) => form.setData('expiry_date', e.currentTarget.value)} error={form.errors.expiry_date} description={isReceived ? 'Pack use-by date' : 'New use-by date'} radius={R} styles={fieldStyles} />
            </Group>

            {isReceived && (
                <Box style={batchBox}>
                    <Text style={{ fontSize: 9.5, fontWeight: 700, letterSpacing: 0.5, textTransform: 'uppercase' }} c="light-dark(#2F7D5B, #A2E0C1)" mb={6}>New batch</Text>
                    <Group grow gap={8}>
                        <TextInput label="Batch / lot no." placeholder="On the pack" size="xs" value={form.data.batch_number} onChange={(e) => form.setData('batch_number', e.currentTarget.value)} radius={R} styles={fieldStyles} />
                        <TextInput label="Supplier" placeholder="Supplying pharmacy" size="xs" value={form.data.supplier} onChange={(e) => form.setData('supplier', e.currentTarget.value)} radius={R} styles={fieldStyles} />
                    </Group>
                    <Text fz={9.5} c={FAINT} mt={5} lh={1.35}>Booking stock in creates a batch with this expiry — used earliest-first.</Text>
                </Box>
            )}

            {isDisposal && (
                <TextInput label="Disposal method" placeholder="How it was disposed of" size="xs" value={form.data.disposal_method} onChange={(e) => form.setData('disposal_method', e.currentTarget.value)} radius={R} styles={fieldStyles} />
            )}

            <Box style={cdBox}>
                <Checkbox label="Controlled drug" checked={form.data.is_controlled} onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)} radius={5} size="xs"
                    styles={{ label: { fontSize: 12, fontWeight: 600, color: 'light-dark(#6F446C, #CBBCE4)' },
                        input: form.data.is_controlled ? { backgroundColor: 'light-dark(#6F446C, #9B6898)', borderColor: 'light-dark(#6F446C, #9B6898)', cursor: 'pointer' } : { cursor: 'pointer' } }} />
                {form.data.is_controlled && (
                    <Group grow gap={8} mt={8}>
                        <TextInput label="CD schedule" placeholder="e.g. 2, 3" size="xs" value={form.data.cd_schedule} onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)} radius={R} styles={fieldStyles} />
                        <TextInput label="Witness" placeholder="Second staff member" size="xs" value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} radius={R} styles={fieldStyles} />
                    </Group>
                )}
            </Box>

            <Textarea label="Notes / reason" placeholder="Optional note about this movement…" size="xs" autosize minRows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} radius={R} styles={{ label: fieldStyles.label, input: { borderRadius: R, border: '1px solid light-dark(#CBD4E0, var(--mantine-color-dark-4))' } }} />
            <TextInput label="Barcode / GTIN" placeholder="Scan or type the pack barcode" size="xs" leftSection={<IconBarcode size={14} />} value={form.data.barcode} onChange={(e) => form.setData('barcode', e.currentTarget.value)} description="Lets a scanner jump straight to this medicine" radius={R} styles={fieldStyles} />
            <Button fullWidth h={36} radius={11} mt={2} loading={form.processing} onClick={submit} styles={{ root: { fontWeight: 650, fontSize: 13 } }} style={{ background: palette.primaryBtn, color: '#fff', boxShadow: 'light-dark(0 8px 18px -12px rgba(22,35,59,0.55), 0 8px 18px -12px rgba(31,158,147,0.6))' }}>Save adjustment</Button>
        </Stack>
    );
}

// Top-of-page "Adjust stock" — client-first picker (resident → medication), then
// the full AdjustForm for the chosen med. Mirrors the Disposal modal so a manager
// can adjust ANY medication deliberately, without hunting for its table row.
function AdjustModal({ opened, onClose, meds = [], residents = [] }) {
    const [pickResident, setPickResident] = useState('');
    const [pickMed, setPickMed] = useState('');
    const hasUnassigned = meds.some((m) => !m.client_id);
    const residentList = [
        ...residents.map((r) => ({ value: String(r.id), label: r.name })),
        ...(hasUnassigned ? [{ value: '__none__', label: 'No resident linked' }] : []),
    ];
    const medData = meds
        .filter((m) => (pickResident === '__none__' ? !m.client_id : String(m.client_id) === String(pickResident)))
        .map((m) => ({ value: String(m.id), label: `${m.medication_name}${m.is_controlled ? ' · CD' : ''}` }));
    const chosenMed = meds.find((m) => String(m.id) === String(pickMed));
    const close = () => { setPickResident(''); setPickMed(''); onClose(); };
    return (
        <Modal opened={opened} onClose={close} title={<Text fw={650} fz="lg" c={TXT} style={{ letterSpacing: -0.2 }}>Adjust stock</Text>} radius={18} centered size="md" overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}>
            <Stack gap={12}>
                <Select label="Resident" placeholder="Choose a resident…" searchable data={residentList}
                    value={pickResident} onChange={(v) => { setPickResident(v || ''); setPickMed(''); }} comboboxProps={{ withinPortal: true, zIndex: 1300 }} />
                <Select label="Medication" placeholder={pickResident ? 'Choose a medication…' : 'Pick a resident first'} searchable disabled={!pickResident} data={medData}
                    value={pickMed} onChange={(v) => setPickMed(v || '')} comboboxProps={{ withinPortal: true, zIndex: 1300 }} nothingFoundMessage="No medications for this resident" />
                {chosenMed
                    ? (
                        <>
                            <Text fz={11.5} c="dimmed" mt={-6}>In stock: <Text span fw={700} c={TXT}>{chosenMed.stock_level ?? '—'} {chosenMed.unit ?? 'units'}</Text>{chosenMed.is_controlled ? ' · Controlled drug' : ''}</Text>
                            <Divider my={2} color={LINE} />
                            <AdjustForm key={chosenMed.id} med={chosenMed} onDone={close} />
                        </>
                    )
                    : <Text fz={12} c={FAINT} mt={4}>Pick a resident, then a medication, to receive, dispose, return or correct its stock.</Text>}
            </Stack>
        </Modal>
    );
}

function MedPanel({ med, transactions, canAdjust, onClose, onDone }) {
    const cap = { fontSize: 10, textTransform: 'uppercase', fontWeight: 700, letterSpacing: 0.5 };
    const movements = transactions.filter((t) => t.medication_name === med.medication_name);
    const forecast = computeForecast(med, transactions);
    // Split the (otherwise very tall) panel into tabs so nothing runs off the page.
    const tabs = [
        { k: 'overview', l: 'Overview' },
        ...(canAdjust ? [{ k: 'adjust', l: 'Adjust' }] : []),
        { k: 'history', l: `History${movements.length ? ` · ${movements.length}` : ''}` },
    ];
    const [tab, setTab] = useState('overview');
    const showTab = tabs.some((t) => t.k === tab) ? tab : 'overview';

    return (
        <Box style={{ ...card, padding: '18px 20px', width: '100%' }}>
            {/* Identity — always visible */}
            <Group justify="space-between" wrap="nowrap" mb={12}>
                <Group gap={11} wrap="nowrap" style={{ minWidth: 0 }}>
                    <MedTile controlled={med.is_controlled} size={40} radius={12} icon={20} />
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                            <Text fz="sm" fw={600} c={TXT} truncate style={{ fontFamily: HEADING_FONT }}>{med.medication_name}</Text>
                            {med.is_controlled && <CdTag schedule={med.cd_schedule} />}
                        </Group>
                        <Text fz="xs" c={FAINT} truncate>{med.resident ?? 'No resident linked'}</Text>
                    </Box>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="xl" onClick={onClose} aria-label="Close"><IconX size={18} /></ActionIcon>
            </Group>
            <Divider mb={14} color={LINE} />

            {/* Stock hero — always visible */}
            <Group justify="space-between" align="center" mb={12}>
                <Box>
                    <Text fz={30} fw={600} c={TXT} lh={1} style={{ ...numeric, letterSpacing: -0.8, fontFamily: HEADING_FONT }}>{med.stock_level ?? '—'} <Text span fz={13} c={FAINT} fw={500}>{med.unit ?? 'units'}</Text></Text>
                    <Text fz={11.5} c={FAINT} mt={3}>Reorder at {med.reorder_level ?? '—'}</Text>
                </Box>
                <StatusChip bucket={bucketOf(med)} />
            </Group>
            <MutedBar pct={stockBar(med).pct} hex={stockBar(med).hex} mb={14} />

            {/* Segmented tabs */}
            <Group gap={4} wrap="nowrap" mb={16} style={{ background: 'light-dark(#EDF1F6, var(--mantine-color-dark-7))', borderRadius: 11, padding: 4, border: '1px solid light-dark(#DFE5EE, rgba(255,255,255,0.08))' }}>
                {tabs.map((t) => {
                    const on = showTab === t.k;
                    return (
                        <Box key={t.k} component="button" type="button" onClick={() => setTab(t.k)}
                            style={{ flex: 1, border: 'none', cursor: 'pointer', borderRadius: 8, padding: '7px 10px', fontSize: 12, fontWeight: on ? 700 : 600, whiteSpace: 'nowrap',
                                background: on ? 'light-dark(#FFFFFF, var(--mantine-color-dark-5))' : 'transparent',
                                color: on ? TXT : 'light-dark(#5B6879, #9AA6B6)',
                                boxShadow: on ? '0 1px 2px rgba(16,29,54,0.12), 0 4px 10px -6px rgba(16,29,54,0.30)' : 'none',
                                transition: 'background .14s ease, color .14s ease' }}>
                            {t.l}
                        </Box>
                    );
                })}
            </Group>

            {/* Overview — cover / expiry / controlled / batches */}
            {showTab === 'overview' && (
                <>
                    {forecast && (
                        <Box mb={14} style={{ borderRadius: 12, padding: '10px 14px', background: 'light-dark(#F4F7FB, rgba(255,255,255,0.03))', border: '1px solid light-dark(#E7ECF3, rgba(255,255,255,0.06))' }}>
                            <Group justify="space-between" align="center" wrap="nowrap">
                                <Box style={{ minWidth: 0 }}>
                                    <Text style={cap} c={FAINT}>Stock cover</Text>
                                    <Text fz={19} fw={650} c={forecastTone(forecast.daysLeft)} lh={1.15} style={numeric}>≈ {forecast.daysLeft} day{forecast.daysLeft === 1 ? '' : 's'} left</Text>
                                </Box>
                                <Text fz={11} c={FAINT} ta="right" style={{ flexShrink: 0 }}>{forecast.perDay.toFixed(1)} {med.unit ?? 'units'}/day<br />over {forecast.basisDays} day{forecast.basisDays === 1 ? '' : 's'}</Text>
                            </Group>
                        </Box>
                    )}

                    <Group gap={0} grow>
                        <Box><Text c={FAINT} mb={2} style={cap}>Expiry</Text><Text fz="sm" fw={600} c={med.expired ? '#B4544A' : med.expiring_soon ? '#BF8A3C' : TXT} style={numeric}>{med.expiry_date ?? '—'}</Text></Box>
                        <Box><Text c={FAINT} mb={2} style={cap}>Controlled</Text><Text fz="sm" fw={600} c={TXT}>{med.is_controlled ? `Yes${med.cd_schedule ? ` · ${med.cd_schedule}` : ''}` : 'No'}</Text></Box>
                    </Group>

                    {med.batches && med.batches.length > 0 && (
                        <>
                            <Divider my={14} label={<Text style={cap} c={FAINT}>Batches · {med.batches.length} · earliest expiry used first</Text>} labelPosition="left" />
                            <Stack gap={8}>
                                {med.batches.map((b, i) => (
                                    <Group key={b.id} justify="space-between" wrap="nowrap" gap={8}
                                        style={{ padding: '8px 12px', borderRadius: 10,
                                            background: i === 0 ? 'light-dark(#EAF4EF, rgba(62,142,119,0.12))' : 'light-dark(#F7F9FB, rgba(255,255,255,0.03))',
                                            border: i === 0 ? '1px solid light-dark(#CDE6DA, rgba(62,142,119,0.28))' : `1px solid ${LINE}` }}>
                                        <Box style={{ minWidth: 0 }}>
                                            <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                                                <Text fz={12.5} fw={600} c={TXT} truncate>{b.batch_number || 'No lot no.'}</Text>
                                                {i === 0 && <Box style={{ flexShrink: 0, display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 10, fontWeight: 700, color: '#2F7D5B', background: 'light-dark(#EAF4EF, rgba(62,142,119,0.16))', borderRadius: 6, padding: '1px 6px' }}><Box w={5} h={5} style={{ borderRadius: '50%', background: '#3E8E77' }} />Use next</Box>}
                                            </Group>
                                            <Text fz={11} c={FAINT} truncate>{b.expiry_date ? `Exp ${b.expiry_date}` : 'No expiry'}{b.supplier ? ` · ${b.supplier}` : ''}</Text>
                                        </Box>
                                        <Text fz={13} fw={650} c={TXT} style={{ flexShrink: 0, ...numeric }}>{b.quantity}{med.unit ? ` ${med.unit}` : ''}</Text>
                                    </Group>
                                ))}
                            </Stack>
                        </>
                    )}
                </>
            )}

            {/* Adjust — shared form (managers only) */}
            {showTab === 'adjust' && canAdjust && <AdjustForm med={med} onDone={onDone} />}

            {/* History — recent movements */}
            {showTab === 'history' && (
                movements.length === 0
                    ? <Text fz="xs" c="dimmed">No stock movements recorded for this medication.</Text>
                    : (
                        <ScrollArea.Autosize mah="calc(100vh - 420px)" type="hover" offsetScrollbars>
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
                                                {t.notes && <Text fz={11.5} c={FAINT} lh={1.35}>{t.notes}</Text>}
                                            </Box>
                                        </Group>
                                    );
                                })}
                            </Stack>
                        </ScrollArea.Autosize>
                    )
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
            // Show the server's OWN message — it knows how many actually applied vs were
            // skipped (e.g. a CD with no witness). The old hardcoded "N reconciled" claimed
            // success for items the server may have skipped (review Stock I-3).
            onSuccess: (page) => {
                const f = page?.props?.flash || {};
                if (f.error) toast(f.error, false);
                else toast(f.success || 'Stock count posted.');
                setCounts({});
            },
            onError: (e) => toast((e && Object.values(e)[0]) || 'Could not post the stock count — please try again.', false),
            onFinish: () => setProcessing(false),
        });
    };

    const postBtn = canAdjust
        ? <Button size="sm" radius={10} loading={processing} disabled={!discrepancies.length} leftSection={<IconClipboardCheck size={15} />} onClick={post} styles={{ root: { fontWeight: 600 } }} style={{ background: palette.primaryBtn }}>Post {discrepancies.length || ''} correction{discrepancies.length === 1 ? '' : 's'}</Button>
        : null;

    return (
        <ViewCard title="Stock count" subtitle="Enter the counted quantity for each item — differences are flagged and posted as corrections" action={postBtn}>
            {!canAdjust && <Text fz="sm" c="dimmed" mb={10}>You can review counts, but only a manager can post corrections.</Text>}
            <Group gap={14} wrap="nowrap" pl={4} pr={12} pb={10} style={headRow}>
                <Text style={{ ...headCell, flex: '0 1 1020px' }}>Medication</Text>
                <Text style={{ ...headCell, width: 80, textAlign: 'right', marginLeft: AIR }}>Expected</Text>
                <Text style={{ ...headCell, width: 110, marginLeft: AIR }}>Counted</Text>
                <Text style={{ ...headCell, width: 92, textAlign: 'right', marginLeft: AIR, paddingRight: 14 }}>Difference</Text>
            </Group>
            <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                {rows.map(({ m, entry, has, expected, diff }, idx) => {
                    const tone = diff === 0 ? '#2F7D5B' : (diff < 0 ? '#B4544A' : '#BF8A3C');
                    const witnessMissing = has && diff !== 0 && m.is_controlled && !(entry.witness || '').trim();
                    return (
                        <Box key={m.id} px={4} py={12} style={rowBorder(idx)}>
                            <Group gap={14} wrap="nowrap" align="center">
                                <MedTile controlled={m.is_controlled} size={34} radius={10} icon={16} />
                                <Box style={{ flex: '0 1 1020px', minWidth: 0 }}>
                                    <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                                        <Text fz={13.5} fw={600} c={TXT} truncate>{m.medication_name}</Text>
                                        {m.is_controlled && <CdTag schedule={m.cd_schedule} />}
                                    </Group>
                                    <Text fz={11.5} c={FAINT} truncate>{m.resident ?? '—'}</Text>
                                </Box>
                                <Text fz={14} fw={650} c={TXT} style={{ width: 80, textAlign: 'right', flexShrink: 0, marginLeft: AIR, ...numeric }}>{expected ?? '—'}</Text>
                                <NumberInput size="xs" w={110} min={0} hideControls placeholder="count" style={{ flexShrink: 0, marginLeft: AIR }} disabled={!canAdjust}
                                    value={entry.counted ?? ''} onChange={(v) => setField(m.id, 'counted', v)} />
                                <Box style={{ width: 92, textAlign: 'right', flexShrink: 0, marginLeft: AIR, paddingRight: 14 }}>
                                    {has && diff !== null
                                        ? <Text fz={13} fw={650} c={tone} style={numeric}>{diff === 0 ? 'Match' : `${diff > 0 ? '+' : ''}${diff}`}</Text>
                                        : <Text fz={12} c={FAINT}>—</Text>}
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
        <Box style={{ ...card, padding: '22px 24px' }}>
            <Group justify="space-between" align="flex-start" wrap="wrap" gap="sm" mb={16}>
                <Box>
                    <Text fz={17} fw={650} c={TXT} style={{ letterSpacing: -0.2, fontFamily: HEADING_FONT }}>{title}</Text>
                    {subtitle && <Text fz={12} c={FAINT} mt={2}>{subtitle}</Text>}
                </Box>
                {action}
            </Group>
            {children}
        </Box>
    );
}
const rowBorder = (idx) => ({ borderTop: idx ? `1px solid ${LINE}` : 'none' });
const emptyRow = (msg) => <Text fz="sm" c={FAINT} ta="center" py={48}>{msg}</Text>;

// Shared sub-table column-header styling + airy trailing-column spacing — echoes the
// Stock overview so all five tables read as one family. AIR is the single knob for the
// gap before each right-grouped trailing column (tune here, applies everywhere).
const headCell = { fontSize: 10.5, fontWeight: 800, textTransform: 'uppercase', letterSpacing: 0.6, color: 'light-dark(#55647D, #9AA8BE)' };
const headRow = { borderBottom: '1px solid light-dark(#E3E8EF, var(--mantine-color-dark-4))' };
const AIR = 22;

// All stock movements, newest first.
function TransactionsView({ transactions }) {
    if (!transactions.length) return <ViewCard title="Transactions" subtitle="All stock movements">{emptyRow('No stock movements recorded yet.')}</ViewCard>;
    return (
        <ViewCard title="Transactions" subtitle={`${transactions.length} movement${transactions.length === 1 ? '' : 's'} · newest first`}>
            <Group gap={14} wrap="nowrap" pl={4} pr={12} pb={10} style={headRow}>
                <Text style={{ ...headCell, width: 118, flexShrink: 0 }}>Date</Text>
                <Text style={{ ...headCell, width: 116, flexShrink: 0 }}>Type</Text>
                <Text style={{ ...headCell, flex: '2 1 200px' }}>Medication</Text>
                <Text style={{ ...headCell, width: 96, flexShrink: 0, textAlign: 'right', marginLeft: AIR }}>Amount</Text>
                <Text style={{ ...headCell, width: 86, flexShrink: 0, textAlign: 'right', marginLeft: AIR }} visibleFrom="sm">Balance</Text>
                <Text style={{ ...headCell, width: 120, flexShrink: 0, marginLeft: AIR }} visibleFrom="md">Staff</Text>
            </Group>
            <ScrollArea.Autosize mah="calc(100vh - 300px)" type="auto" offsetScrollbars scrollbarSize={8}>
                {transactions.map((t, idx) => {
                    const meta = TXN[t.type] || { label: t.type, c: '#98A1AB', sign: '' };
                    return (
                        <Group key={t.id} gap={14} wrap="nowrap" align="center" px={4} py={13} style={rowBorder(idx)}>
                            <Box style={{ width: 118, flexShrink: 0 }}><Text fz={12.5} fw={600} c={TXT} style={numeric}>{t.date}</Text></Box>
                            <Box style={{ width: 116, flexShrink: 0 }}><Group gap={7} wrap="nowrap"><Box w={6} h={6} style={{ borderRadius: '50%', background: meta.c, flexShrink: 0 }} /><Text fz={12.5} fw={600} c={INK2} truncate>{meta.label}</Text></Group></Box>
                            <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13} fw={600} c={TXT} truncate>{t.medication_name}</Text>{(t.reason || t.notes) && <Text fz={11.5} c={FAINT} truncate>“{t.reason || t.notes}”</Text>}</Box>
                            <Box style={{ width: 96, flexShrink: 0, textAlign: 'right', marginLeft: AIR }}><Text fz={13} fw={650} c={meta.c} style={numeric}>{meta.sign}{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text></Box>
                            <Box style={{ width: 86, flexShrink: 0, textAlign: 'right', marginLeft: AIR }} visibleFrom="sm"><Text fz={12} c={FAINT} style={numeric}>bal {t.balance_after ?? '—'}</Text></Box>
                            <Box style={{ width: 120, flexShrink: 0, marginLeft: AIR }} visibleFrom="md"><Text fz={12} c={FAINT} truncate>{t.performed_by ?? '—'}</Text></Box>
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

    const toast = (message, ok = true) => notifications.show({
        message, autoClose: ok ? 3500 : 5000, withBorder: true, icon: ok ? <IconCheck size={20} stroke={3} /> : <IconAlertTriangle size={20} stroke={2.4} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: ok ? '#1F9E93' : '#C0413A', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: ok ? '#1F9E93' : '#C0413A', width: 38, height: 38, borderRadius: '50%' },
            body: { marginInlineStart: 8 }, description: { color: '#FFFFFF', fontSize: 15, fontWeight: 700 }, closeButton: { color: 'rgba(255,255,255,0.65)' },
        },
    });
    // First server/validation error message, whatever field it came back under.
    const firstErr = (e) => (e && typeof e === 'object' ? Object.values(e)[0] : null) || 'Something went wrong — please try again.';

    const needs = meds.filter((m) => m.low || isOut(m)).sort((a, b) => Number(a.stock_level ?? 0) - Number(b.stock_level ?? 0));
    const onOrderIds = new Set(orders.map((o) => o.mar_sheet_id));
    const suggested = (m) => {
        const target = m.reorder_level ? Number(m.reorder_level) * 2 : Math.max(Number(m.stock_level ?? 0), 10);
        return Math.max(1, Math.round(target - Number(m.stock_level ?? 0)));
    };

    const openOrder = (m) => { orderForm.clearErrors(); orderForm.setData({ mar_sheet_id: String(m.id), quantity: suggested(m), supplier: '', notes: '' }); setReceiveFor(null); setOrderFor(m.id); };
    const placeOrder = () => orderForm.post(ORDER, { preserveScroll: true, onSuccess: () => { toast('Order placed.'); setOrderFor(null); orderForm.reset(); }, onError: (e) => toast(firstErr(e), false) });
    const openReceive = (o) => { recvForm.clearErrors(); recvForm.setData({ order_id: String(o.id), quantity: o.quantity, expiry_date: '', batch_number: '' }); setOrderFor(null); setReceiveFor(o.id); };
    const receive = () => recvForm.post(ORDER_RECEIVE, { preserveScroll: true, onSuccess: () => { toast('Order received and booked in.'); setReceiveFor(null); recvForm.reset(); }, onError: (e) => toast(firstErr(e), false) });
    const cancelOrder = (o) => router.post(ORDER_CANCEL, { order_id: o.id }, { preserveScroll: true, onSuccess: () => toast('Order cancelled.'), onError: (e) => toast(firstErr(e), false) });

    return (
        <Stack gap={16}>
            {/* On order */}
            {orders.length > 0 && (
                <ViewCard title="On order" subtitle={`${orders.length} open order${orders.length === 1 ? '' : 's'} · receive to book stock in`}>
                    <Group gap={14} wrap="nowrap" pl={4} pr={12} pb={10} style={headRow}>
                        <Text style={{ ...headCell, flex: '2 1 200px' }}>Medication</Text>
                        <Text style={{ ...headCell, width: 120, marginLeft: AIR }} visibleFrom="sm">Ordered</Text>
                        {canAdjust && <Text style={{ ...headCell, width: 118, marginLeft: AIR, textAlign: 'right' }}>Actions</Text>}
                    </Group>
                    {orders.map((o, idx) => (
                        <Box key={o.id} px={4} py={12} style={rowBorder(idx)}>
                            <Group gap={14} wrap="nowrap" align="center">
                                <Box style={{ width: 34, height: 34, borderRadius: 10, flexShrink: 0, display: 'grid', placeItems: 'center', background: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))', border: '1px solid light-dark(#EBEEF3, rgba(255,255,255,0.07))', color: '#3E8E77' }}><IconShoppingCart size={16} stroke={1.8} /></Box>
                                <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13.5} fw={600} c={TXT} truncate>{o.medication_name}</Text><Text fz={11.5} c={FAINT} truncate>{[o.resident, o.supplier].filter(Boolean).join(' · ') || '—'}</Text></Box>
                                <Box style={{ width: 120, flexShrink: 0, marginLeft: AIR }} visibleFrom="sm"><Text fz={12} c={FAINT}>Ordered <Text span fw={650} c={TXT} style={numeric}>{o.quantity}</Text>{o.ordered_at ? ` · ${o.ordered_at}` : ''}</Text></Box>
                                {canAdjust && (
                                    <Group gap={6} wrap="nowrap" style={{ flexShrink: 0, marginLeft: AIR, width: 118, justifyContent: 'flex-end' }}>
                                        <Button size="xs" radius={9} onClick={() => openReceive(o)} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#2F7D5B, #1F9E93)' }}>Receive</Button>
                                        <ActionIcon variant="subtle" color="gray" radius="xl" aria-label="Cancel order" onClick={() => cancelOrder(o)}><IconX size={15} /></ActionIcon>
                                    </Group>
                                )}
                            </Group>
                            {receiveFor === o.id && canAdjust && (
                                <Group gap={10} mt={10} pl={48} wrap="wrap" align="flex-end">
                                    <NumberInput size="xs" label="Received qty" w={110} min={0} value={recvForm.data.quantity} onChange={(v) => recvForm.setData('quantity', v)} error={recvForm.errors.quantity} />
                                    <TextInput size="xs" label="Batch expiry" type="date" w={150} value={recvForm.data.expiry_date} onChange={(e) => recvForm.setData('expiry_date', e.currentTarget.value)} />
                                    <TextInput size="xs" label="Lot no." w={130} value={recvForm.data.batch_number} onChange={(e) => recvForm.setData('batch_number', e.currentTarget.value)} />
                                    <Button size="xs" radius={9} loading={recvForm.processing} onClick={receive} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#2F7D5B, #1F9E93)' }}>Book in</Button>
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
                        <>
                        <Group gap={14} wrap="nowrap" pl={4} pr={12} pb={10} style={headRow}>
                            <Text style={{ ...headCell, flex: '2 1 180px' }}>Medication</Text>
                            <Text style={{ ...headCell, width: 172, marginLeft: AIR }} visibleFrom="sm">Current</Text>
                            <Text style={{ ...headCell, width: 96, textAlign: 'right', marginLeft: AIR }}>Suggested</Text>
                            <Text style={{ ...headCell, width: 110, textAlign: 'right', marginLeft: AIR }}>Order</Text>
                        </Group>
                        <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                            {needs.map((m, idx) => {
                                const st = STATUS[bucketOf(m)];
                                const already = onOrderIds.has(m.id);
                                return (
                                    <Box key={m.id} px={4} py={12} style={rowBorder(idx)}>
                                        <Group gap={14} wrap="nowrap" align="center">
                                            <MedTile controlled={m.is_controlled} size={34} radius={10} icon={16} />
                                            <Box style={{ flex: '2 1 180px', minWidth: 0 }}><Text fz={13.5} fw={600} c={TXT} truncate>{m.medication_name}</Text><Text fz={11.5} c={FAINT} truncate>{m.resident ?? '—'}</Text></Box>
                                            <Box style={{ width: 172, flexShrink: 0, marginLeft: AIR }} visibleFrom="sm"><Text fz={12} c={FAINT}>Have <Text span fw={650} c={TXT} style={numeric}>{m.stock_level ?? '—'}</Text> · reorder at {m.reorder_level ?? '—'}</Text></Box>
                                            <Box style={{ width: 96, flexShrink: 0, textAlign: 'right', marginLeft: AIR }}><Text fz={11} c={FAINT}>suggest</Text><Text fz={13} fw={650} c="#2F7D5B" style={numeric}>+{suggested(m)}</Text></Box>
                                            <Box style={{ width: 110, flexShrink: 0, textAlign: 'right', marginLeft: AIR }}>
                                                {already
                                                    ? <Box style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '3px 10px 3px 8px', borderRadius: 999, fontSize: 11, fontWeight: 600, background: 'light-dark(#EAF4EF, rgba(62,142,119,0.16))', color: '#2F7D5B' }}><Box w={5} h={5} style={{ borderRadius: '50%', background: '#3E8E77' }} />On order</Box>
                                                    : canAdjust
                                                        ? <Button size="xs" radius={9} variant="default" leftSection={<IconShoppingCart size={14} stroke={1.8} />} onClick={() => openOrder(m)} styles={{ root: { fontWeight: 600 } }}>Order</Button>
                                                        : <StatusChip bucket={m.bucket} />}
                                            </Box>
                                        </Group>
                                        {orderFor === m.id && canAdjust && (
                                            <Group gap={10} mt={10} pl={48} wrap="wrap" align="flex-end">
                                                <NumberInput size="xs" label="Quantity" w={110} min={1} value={orderForm.data.quantity} onChange={(v) => orderForm.setData('quantity', v)} error={orderForm.errors.quantity} />
                                                <TextInput size="xs" label="Supplier" w={170} placeholder="Supplying pharmacy" value={orderForm.data.supplier} onChange={(e) => orderForm.setData('supplier', e.currentTarget.value)} />
                                                <TextInput size="xs" label="Notes" w={170} value={orderForm.data.notes} onChange={(e) => orderForm.setData('notes', e.currentTarget.value)} />
                                                <Button size="xs" radius={9} loading={orderForm.processing} onClick={placeOrder} styles={{ root: { fontWeight: 600 } }} style={{ background: palette.primaryBtn }}>Place order</Button>
                                                <Button size="xs" radius={8} variant="default" onClick={() => setOrderFor(null)}>Cancel</Button>
                                            </Group>
                                        )}
                                    </Box>
                                );
                            })}
                        </ScrollArea.Autosize>
                        </>
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

    const toast = (message, ok = true) => notifications.show({
        message, autoClose: ok ? 3500 : 5000, withBorder: true, icon: ok ? <IconCheck size={20} stroke={3} /> : <IconAlertTriangle size={20} stroke={2.4} />,
        styles: {
            root: { padding: '16px 22px', minWidth: 340, borderRadius: 24, backgroundColor: 'light-dark(#13233F, #18243D)', borderColor: ok ? '#C43D6B' : '#C0413A', boxShadow: '0 22px 48px -12px rgba(19,35,63,0.62)' },
            icon: { backgroundColor: ok ? '#C43D6B' : '#C0413A', width: 38, height: 38, borderRadius: '50%' },
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
        if (Object.keys(errs).length) {
            form.setError(errs);
            // Loud, unmissable feedback — the inline errors alone are easy to overlook.
            const missing = [];
            if (errs.mar_sheet_id) missing.push('medication');
            if (errs.quantity) missing.push('quantity');
            if (errs.reason) missing.push('reason');
            if (errs.disposal_method) missing.push('disposal method');
            if (errs.witness_name) missing.push('witness');
            toast(`Please complete: ${missing.join(', ')}.`, false);
            return;
        }
        // Preserve the med's controlled flag/schedule so the adjust endpoint doesn't clear it.
        form.transform((d) => ({ ...d, is_controlled: selectedMed?.is_controlled ? 1 : 0, cd_schedule: selectedMed?.cd_schedule ?? '' }))
            .post(ADJUST, {
                preserveScroll: true,
                onSuccess: () => { toast(`Disposal recorded${selectedMed ? ` for ${selectedMed.medication_name}` : ''}.`); closeModal(); },
                onError: (e) => toast(Object.values(e)[0] || 'Could not record the disposal — please try again.', false),
            });
    };

    const recordBtn = canAdjust
        ? <Button size="sm" radius={10} leftSection={<IconTrash size={15} />} onClick={() => setOpen(true)} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#A6506A, #B4657E)' }}>Record disposal</Button>
        : null;

    return (
        <>
            <ViewCard title="Disposal" subtitle={list.length ? `${list.length} disposal${list.length === 1 ? '' : 's'} · newest first` : 'Medication disposed of'} action={recordBtn}>
                {!list.length
                    ? emptyRow('No disposals recorded yet.')
                    : (
                        <>
                        <Group gap={14} wrap="nowrap" pl={4} pr={12} pb={10} style={headRow}>
                            <Text style={{ ...headCell, flex: '2 1 200px' }}>Medication</Text>
                            <Text style={{ ...headCell, width: 118, marginLeft: AIR }} visibleFrom="sm">Date</Text>
                            <Text style={{ ...headCell, width: 96, textAlign: 'right', marginLeft: AIR }}>Quantity</Text>
                            <Text style={{ ...headCell, width: 120, marginLeft: AIR }} visibleFrom="md">Staff</Text>
                        </Group>
                        <ScrollArea.Autosize mah="calc(100vh - 300px)" type="auto" offsetScrollbars scrollbarSize={8}>
                            {list.map((t, idx) => (
                                <Group key={t.id} gap={14} wrap="nowrap" align="center" px={4} py={13} style={rowBorder(idx)}>
                                    <Box style={{ width: 36, height: 36, borderRadius: 11, flexShrink: 0, display: 'grid', placeItems: 'center', background: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))', border: '1px solid light-dark(#EBEEF3, rgba(255,255,255,0.07))', color: '#A6506A' }}><IconTrash size={16} stroke={1.8} /></Box>
                                    <Box style={{ flex: '2 1 200px', minWidth: 0 }}><Text fz={13.5} fw={600} c={TXT} truncate>{t.medication_name}</Text>{(t.reason || t.notes) && <Text fz={11.5} c={FAINT} truncate>“{t.reason || t.notes}”</Text>}</Box>
                                    <Box style={{ width: 118, flexShrink: 0, marginLeft: AIR }} visibleFrom="sm"><Text fz={12.5} c={FAINT} style={numeric}>{t.date}</Text></Box>
                                    <Box style={{ width: 96, flexShrink: 0, textAlign: 'right', marginLeft: AIR }}><Text fz={13} fw={650} c="#A6506A" style={numeric}>−{t.quantity}{t.unit ? ` ${t.unit}` : ''}</Text></Box>
                                    <Box style={{ width: 120, flexShrink: 0, marginLeft: AIR }} visibleFrom="md"><Text fz={12} c={FAINT} truncate>{t.performed_by ?? '—'}</Text></Box>
                                </Group>
                            ))}
                        </ScrollArea.Autosize>
                        </>
                    )}
            </ViewCard>

            <Modal opened={open} onClose={closeModal} title={<Text fw={650} fz="lg" c={TXT} style={{ letterSpacing: -0.2 }}>Record disposal</Text>} radius={18} centered size="md" overlayProps={{ backgroundOpacity: 0.55, blur: 3 }}>
                <Stack gap={12}>
                    <Select label="Resident" placeholder="Choose a resident…" searchable data={residentList}
                        value={pickResident} onChange={(v) => { setPickResident(v || ''); form.setData('mar_sheet_id', ''); }} comboboxProps={{ withinPortal: true, zIndex: 1300 }} />
                    <Select label="Medication" placeholder={pickResident ? 'Choose a medication…' : 'Pick a resident first'} searchable disabled={!pickResident} data={medData}
                        value={form.data.mar_sheet_id} onChange={(v) => form.setData('mar_sheet_id', v || '')} error={form.errors.mar_sheet_id} comboboxProps={{ withinPortal: true, zIndex: 1300 }} nothingFoundMessage="No medications for this resident" />
                    {selectedMed && (
                        <Text fz={11.5} c="dimmed" mt={-6}>In stock: <Text span fw={700} c={TXT}>{selectedMed.stock_level ?? '—'} {selectedMed.unit ?? 'units'}</Text>{selectedMed.is_controlled ? ' · Controlled drug' : ''}</Text>
                    )}
                    <Group grow gap={10} align="flex-start">
                        <NumberInput label="Quantity to dispose" placeholder="e.g. 6" min={0} value={form.data.quantity} onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} />
                        <Select label="Reason" placeholder="Why…" data={DISPOSAL_REASONS} value={form.data.reason} onChange={(v) => form.setData('reason', v || '')} error={form.errors.reason} comboboxProps={{ withinPortal: true, zIndex: 1300 }} />
                    </Group>
                    <Select label="Disposal method" placeholder="How it was disposed of…" data={DISPOSAL_METHODS} value={form.data.disposal_method} onChange={(v) => form.setData('disposal_method', v || '')} error={form.errors.disposal_method} comboboxProps={{ withinPortal: true, zIndex: 1300 }} />
                    <TextInput label={`Witness${needWitness ? ' (required for CDs)' : ''}`} placeholder="Second staff member" value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} error={form.errors.witness_name} />
                    {needWitness && <Text fz={11} c="#9C6B22" mt={-6}>Controlled drug — a second person must witness the disposal (e.g. CD destruction kit).</Text>}
                    <Textarea label="Notes" placeholder="Optional detail about this disposal…" autosize minRows={2} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                    <Group justify="flex-end" gap={10} mt={4}>
                        <Button variant="default" radius={10} onClick={closeModal} styles={{ root: { fontWeight: 600 } }}>Cancel</Button>
                        <Button radius={10} loading={form.processing} leftSection={<IconTrash size={15} />} onClick={submit} styles={{ root: { fontWeight: 600 } }} style={{ background: 'light-dark(#A6506A, #B4657E)' }}>Record disposal</Button>
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

    const [view, setView] = useState('overview');
    const [tab, setTab] = useState('all');
    const [query, setQuery] = useState('');
    const [residentF, setResidentF] = useState('any');
    const [cdF, setCdF] = useState('all');
    const [sort, setSort] = useState({ key: 'medication', dir: 'asc' });
    const [selected, setSelected] = useState(null);
    const [panelMed, setPanelMed] = useState(null);
    const [adjustOpen, setAdjustOpen] = useState(false);
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
                <Text component="span" fz={10} fw={700} tt="uppercase" c={active ? TXT : FAINT} style={{ letterSpacing: 0.7 }}>{label}</Text>
                <Box component="span" style={{ display: 'inline-flex', color: active ? '#3E8E77' : undefined, opacity: active ? 1 : 0.4 }}>
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
        { k: 'all', l: 'All items', n: rows.length, tint: '#3E5170' },
        { k: 'low', l: 'Low stock', n: counts.low, tint: '#BF8A3C' },
        { k: 'out', l: 'Out of stock', n: counts.out, tint: '#B4544A' },
        { k: 'expiring', l: 'Expiring soon', n: counts.expiring, tint: '#8A6FAE' },
        { k: 'expired', l: 'Expired', n: counts.expired, tint: '#A6506A' },
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
            <Box px={{ base: 0, sm: 10 }} pb={14}>
                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb={20}>
                    <Stack gap={12}>
                        <Group gap={14} wrap="nowrap" align="center">
                            <ThemeIcon size={44} radius={13} style={{ background: 'light-dark(#F4F6FA, rgba(255,255,255,0.05))', color: 'light-dark(#16233B, #C9D3E2)', border: '1px solid light-dark(#E7EBF1, rgba(255,255,255,0.08))', flexShrink: 0 }}><IconBox size={22} stroke={1.7} /></ThemeIcon>
                            <Box style={{ minWidth: 0 }}>
                                <Text fz={24} fw={650} c={TXT} lh={1.15} style={{ letterSpacing: -0.4, fontFamily: HEADING_FONT }}>Medication stock</Text>
                                <Text fz={12.5} c={INK2} mt={2}>{[`${total} medication${total === 1 ? '' : 's'}`, attention ? `${attention} need attention` : 'all healthy'].join('  ·  ')}</Text>
                            </Box>
                        </Group>
                        <Group gap={8} wrap="nowrap" style={{ width: 'fit-content', background: 'light-dark(#E3E9F2, rgba(255,255,255,0.05))', borderRadius: 999, padding: '3px 4px 3px 12px' }}>
                            <Box w={7} h={7} style={{ borderRadius: '50%', background: '#1F9E93', boxShadow: '0 0 0 3px rgba(31,158,147,0.18)' }} />
                            <Text fz={11.5} fw={600} c="light-dark(#48576E, #A9B6CB)">Live · data as of {loadedTime}</Text>
                            <Tooltip label="Refresh now" withArrow><ActionIcon size="sm" variant="filled" radius="xl" aria-label="Refresh data" onClick={() => router.reload()} style={{ background: palette.primaryBtn }}><IconRefresh size={13} stroke={2.6} /></ActionIcon></Tooltip>
                        </Group>
                    </Stack>
                    <Group gap={10} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <TextInput
                            h={42} radius={11} w={isSm ? '100%' : 196}
                            placeholder="Scan barcode…"
                            leftSection={<IconBarcode size={16} stroke={1.8} />}
                            value={scan} onChange={(e) => setScan(e.currentTarget.value)}
                            onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); runScan(); } }}
                            rightSection={scan ? <ActionIcon size="sm" variant="subtle" color="gray" radius="xl" aria-label="Clear scan" onClick={() => setScan('')}><IconX size={13} /></ActionIcon> : null}
                            styles={{ input: { height: 42, borderRadius: 11, borderColor: 'light-dark(#C4CDDA, var(--mantine-color-dark-4))', boxShadow: '0 1px 2px rgba(16,29,54,0.07), 0 6px 16px -10px rgba(16,29,54,0.28)' } }}
                        />
                        <Tooltip label="Open the Controlled Drug register" withArrow openDelay={300}>
                            <Button variant="default" h={42} radius={11} leftSection={<IconShieldLock size={16} stroke={1.8} color="#8A6FAE" />}
                                onClick={() => router.visit('/frontend2/controlled-drugs')}
                                styles={{ root: { fontWeight: 600, borderColor: 'light-dark(#C4CDDA, var(--mantine-color-dark-4))', boxShadow: '0 1px 2px rgba(16,29,54,0.07), 0 6px 16px -10px rgba(16,29,54,0.28)' } }}>CD register</Button>
                        </Tooltip>
                        <Tooltip label="Export the current filtered view as CSV" withArrow openDelay={300}>
                            <Button variant="default" h={42} radius={11} leftSection={<IconDownload size={16} stroke={1.8} />} onClick={exportCsv}
                                styles={{ root: { fontWeight: 600, borderColor: 'light-dark(#C4CDDA, var(--mantine-color-dark-4))', boxShadow: '0 1px 2px rgba(16,29,54,0.07), 0 6px 16px -10px rgba(16,29,54,0.28)' } }}>Export</Button>
                        </Tooltip>
                        {canAdjust && (
                            <Tooltip label="Receive, dispose, return or correct any medication's stock" withArrow openDelay={300}>
                                <Button h={42} radius={11} leftSection={<IconPlus size={16} stroke={2} />} onClick={() => setAdjustOpen(true)}
                                    styles={{ root: { fontWeight: 600 } }}
                                    style={{ background: palette.primaryBtn, paddingInline: 20, boxShadow: 'light-dark(0 10px 22px -10px rgba(22,35,59,0.55), 0 10px 22px -10px rgba(31,158,147,0.6))' }}>Adjust stock</Button>
                            </Tooltip>
                        )}
                    </Group>
                </Group>

                {/* Sub-view tabs — Stock overview / Transactions / Reorder / Disposal */}
                <Group gap={4} wrap="nowrap" mb={20} style={{ width: 'fit-content', maxWidth: '100%', overflowX: 'auto', background: 'light-dark(#EDF1F6, var(--mantine-color-dark-7))', borderRadius: 13, padding: 5, border: '1px solid light-dark(#D3DBE6, rgba(255,255,255,0.12))', boxShadow: '0 1px 2px rgba(16,29,54,0.05), 0 8px 20px -14px rgba(16,29,54,0.35)' }}>
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
                                onMouseEnter={(e) => { if (!on) { e.currentTarget.style.color = 'light-dark(#16233B, #FFFFFF)'; e.currentTarget.style.background = 'light-dark(#FFFFFF, rgba(255,255,255,0.04))'; e.currentTarget.style.boxShadow = 'inset 0 0 0 1px light-dark(#DCE3EC, rgba(255,255,255,0.10))'; } }}
                                onMouseLeave={(e) => { if (!on) { e.currentTarget.style.color = 'light-dark(#44536B, #B7C1D0)'; e.currentTarget.style.background = 'transparent'; e.currentTarget.style.boxShadow = 'inset 0 0 0 1px light-dark(#E1E7EF, rgba(255,255,255,0.07))'; } }}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 8, border: 'none', cursor: 'pointer', borderRadius: 10,
                                    padding: '8px 16px', fontSize: 13, fontWeight: on ? 700 : 650, whiteSpace: 'nowrap', transition: 'color .14s ease, background .14s ease, box-shadow .14s ease',
                                    background: on ? 'light-dark(#FFFFFF, var(--mantine-color-dark-5))' : 'transparent',
                                    color: on ? 'light-dark(#16233B, #FFFFFF)' : 'light-dark(#44536B, #B7C1D0)',
                                    boxShadow: on ? '0 1px 2px rgba(16,29,54,0.12), 0 5px 12px -6px rgba(16,29,54,0.30)' : 'inset 0 0 0 1px light-dark(#E1E7EF, rgba(255,255,255,0.07))',
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
                                <TextInput
                                    ref={searchRef}
                                    size="md" radius={12} w={440} maw="100%"
                                    placeholder="Search medication or resident…"
                                    onFocus={() => setSearchFocus(true)} onBlur={() => setSearchFocus(false)}
                                    leftSection={<IconSearch size={16} stroke={1.8} color="light-dark(#939DAD, #6C7688)" />}
                                    leftSectionWidth={38}
                                    value={query} onChange={(e) => setQuery(e.currentTarget.value)}
                                    rightSection={query
                                        ? <ActionIcon size="md" variant="subtle" color="gray" radius="xl" aria-label="Clear search" onClick={() => setQuery('')}><IconX size={15} /></ActionIcon>
                                        : <Box component="kbd" style={{ fontSize: 11, fontWeight: 600, fontFamily: 'inherit', color: 'light-dark(#8A97AB, #7D8AA0)', background: 'light-dark(#F1F3F7, rgba(255,255,255,0.06))', border: '1px solid light-dark(#E4E8EE, rgba(255,255,255,0.10))', borderRadius: 6, padding: '2px 8px', lineHeight: 1 }}>/</Box>}
                                    rightSectionWidth={40}
                                    styles={{ input: { height: 42, minHeight: 42, borderRadius: 12, fontSize: 13.5, fontWeight: 500,
                                        border: searchFocus ? '1px solid light-dark(#8FA0B8, rgba(255,255,255,0.30))' : '1px solid light-dark(#C4CDDA, var(--mantine-color-dark-4))',
                                        background: 'light-dark(#FFFFFF, var(--mantine-color-dark-7))',
                                        boxShadow: searchFocus
                                            ? '0 0 0 3px light-dark(rgba(22,35,59,0.08), rgba(255,255,255,0.06)), 0 10px 22px -10px rgba(16,29,54,0.34)'
                                            : '0 1px 2px rgba(16,29,54,0.07), 0 6px 16px -10px rgba(16,29,54,0.28)',
                                        transition: 'border-color .15s ease, box-shadow .15s ease' } }}
                                />
                            </Box>
                            <Group gap={10} wrap="wrap" style={{ flexShrink: 0 }}>
                                {INLINE.filter((f) => f.key !== 'status').map((f) => (
                                    <Group key={f.key} gap={9} wrap="nowrap" style={{
                                        flexShrink: 0, background: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))',
                                        border: '1px solid light-dark(#E4E8EE, var(--mantine-color-dark-4))', borderRadius: 12,
                                        padding: '5px 9px', boxShadow: '0 1px 2px rgba(16,29,54,0.04)',
                                    }}>
                                        <Box style={{ width: 28, height: 28, borderRadius: 8, flexShrink: 0, display: 'grid', placeItems: 'center', background: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))', border: '1px solid light-dark(#EBEEF3, rgba(255,255,255,0.07))', color: 'light-dark(#5E6878, #97A2B3)' }}>
                                            <f.icon size={15} stroke={1.8} />
                                        </Box>
                                        <Box style={{ flexShrink: 0 }}>
                                            <Text fz={8.5} fw={700} tt="uppercase" c={FAINT} lh={1} style={{ letterSpacing: 0.6, marginBottom: 2 }}>{f.label}</Text>
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

                        <Divider mb={16} color={LINE} />

                        {/* Row 2 — status pills */}
                        <Group gap={7} wrap="wrap" mb={16}>
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
                                            boxShadow: on ? `0 6px 16px -8px ${t.tint}` : 'none',
                                            transition: 'border-color .14s ease, box-shadow .14s ease',
                                        }}>
                                        <Box component="span" w={7} h={7} style={{ borderRadius: '50%', background: on ? 'rgba(255,255,255,0.9)' : t.tint, flexShrink: 0 }} />
                                        <Text fz={12.5} fw={600} c={on ? '#FFFFFF' : 'light-dark(#44536B, #C3CDDD)'}>{t.l}</Text>
                                        <Box component="span" style={{
                                            ...numeric, minWidth: 21, textAlign: 'center', borderRadius: 999, padding: '1px 7px', fontSize: 11.5, fontWeight: 700,
                                            background: on ? 'rgba(255,255,255,0.22)' : 'light-dark(#F1F3F7, rgba(255,255,255,0.06))',
                                            color: on ? '#FFFFFF' : 'light-dark(#6A7A92, #94A2B8)',
                                        }}>{t.n}</Box>
                                    </Box>
                                );
                            })}
                        </Group>

                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={4} pb={10} style={{ borderBottom: `1px solid ${LINE}` }}>
                            {sortHead('Medication', 'medication', { flex: '0 1 820px' })}
                            {sortHead('Stock', 'stock', { flex: '0 0 165px' }, { visibleFrom: 'sm' })}
                            {sortHead('Expiry', 'expiry', { width: 96, marginLeft: 92 }, { visibleFrom: 'md' })}
                            {sortHead('Status', 'status', { width: 110, marginLeft: 92 })}
                            <Box style={{ width: 18 }} />
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" fw={500} c={FAINT} ta="center" py={48}>{query ? `No matches for “${query}”.` : 'No medications in this view.'}</Text>
                            : (
                                <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                                    {shown.map((m, idx) => {
                                        const st = STATUS[m.bucket];
                                        const bar = stockBar(m);
                                        const fc = computeForecast(m, transactions);
                                        return (
                                            <Group key={m.id} gap={14} wrap="nowrap" align="center" px={6} py={7}
                                                role="button" tabIndex={0} aria-label={`${m.medication_name}, ${m.stock_level ?? 'unknown'} ${m.unit ?? 'units'}, ${st.label}`}
                                                onClick={() => openPanel(m)}
                                                onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openPanel(m); } }}
                                                style={{ cursor: 'pointer', borderTop: idx ? `1px solid ${LINE}` : 'none', borderRadius: 12, transition: 'background .15s' }}
                                                onFocus={(e) => { e.currentTarget.style.background = 'light-dark(#F2F5F9, var(--mantine-color-dark-5))'; e.currentTarget.style.outline = '2px solid #3E8E77'; e.currentTarget.style.outlineOffset = '-2px'; }}
                                                onBlur={(e) => { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.outline = 'none'; }}
                                                onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#F8FAFC, var(--mantine-color-dark-5))'; }}
                                                onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                                                {/* Medication */}
                                                <Group gap={13} wrap="nowrap" style={{ flex: '0 1 820px', minWidth: 0 }}>
                                                    <MedTile controlled={m.is_controlled} size={38} radius={11} icon={18} />
                                                    <Box style={{ minWidth: 0 }}>
                                                        <Group gap={7} wrap="nowrap" style={{ minWidth: 0 }}>
                                                            <Text fz={13.5} fw={600} c={TXT} truncate>{m.medication_name}</Text>
                                                            {m.is_controlled && <CdTag schedule={m.cd_schedule} />}
                                                        </Group>
                                                        <Text fz={11.5} c={FAINT} truncate mt={1}>{m.resident ?? '—'}</Text>
                                                    </Box>
                                                </Group>
                                                {/* Stock */}
                                                <Box style={{ flex: '0 0 165px', minWidth: 0 }} visibleFrom="sm">
                                                    <Group gap={8} wrap="nowrap" mb={13}>
                                                        <Text fz={13.5} fw={650} c={TXT} lh={1} style={numeric}>{m.stock_level ?? '—'}</Text>
                                                        <Text fz={11} c={FAINT}>{m.unit ?? 'units'}</Text>
                                                        {fc && <Text fz={10.5} fw={600} c={forecastTone(fc.daysLeft)} style={numeric}>· ≈{fc.daysLeft}d left</Text>}
                                                    </Group>
                                                    <MutedBar pct={bar.pct} hex={bar.hex} maxW={150} />
                                                </Box>
                                                {/* Expiry — bottom offset (18 = bar mb 13 + bar height 5) so the
                                                    text rides up onto the same line as the Stock number */}
                                                <Box style={{ width: 96, flexShrink: 0, marginLeft: 92 }} visibleFrom="md">
                                                    <Text fz={12.5} fw={600} c={m.expired ? '#B4544A' : m.expiring_soon ? '#BF8A3C' : TXT} lh={1.2} mb={18} style={numeric}>{m.expiry_date ?? '—'}</Text>
                                                </Box>
                                                {/* Status — same 18px offset to align with the Stock number */}
                                                <Box style={{ width: 110, flexShrink: 0, marginLeft: 92 }}>
                                                    <Box mb={18}><StatusChip bucket={m.bucket} /></Box>
                                                </Box>
                                                <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0, marginLeft: 'auto' }}><IconChevronRight size={16} /></ActionIcon>
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

                {/* Top-level Adjust stock — resident→medication picker, then the shared AdjustForm */}
                <AdjustModal opened={adjustOpen} onClose={() => setAdjustOpen(false)} meds={meds} residents={residents} />

                </>)}
            </Box>
        </AppShell>
    );
}
