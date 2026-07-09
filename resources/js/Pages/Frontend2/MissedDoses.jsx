import { useMemo, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Button, ThemeIcon, TextInput, ActionIcon, SimpleGrid, Avatar, ScrollArea, Tooltip, RingProgress, Divider, Select, Textarea,
} from '@mantine/core';
import {
    IconAlertTriangle, IconChevronLeft, IconChevronRight, IconDownload, IconCheck,
    IconShieldCheck, IconAlertCircle, IconX, IconRefresh,
    IconSearch, IconSelector, IconChevronUp, IconChevronDown,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import ResolveDoseModal from '@frontend/features/medications/ResolveDoseModal';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

const PAGE = '/frontend2/missed-doses';
const RESOLVE = '/frontend2/missed-doses/resolve';
const UNRESOLVE = '/frontend2/missed-doses/unresolve';

const TXT = 'light-dark(#1E2F4D, #E8EBF1)';
// Soft card — the same lifted, borderless-ish style as Split B.
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 20,
    border: '1px solid light-dark(#EEF1F4, rgba(150,172,205,0.18))',
    boxShadow: '0 10px 30px -18px rgba(20,50,80,0.22)',
};

// Three outcome buckets the mockup filters by. Soft pastel tones (no sharp fills).
const OUTCOME = {
    refused: { label: 'Refused', c: '#8A4E82', bg: 'light-dark(#F2E8F0, rgba(138,78,130,0.22))', dot: '#C43D6B' },
    omitted: { label: 'Omitted', c: '#8A6D3B', bg: 'light-dark(#F4EEDD, rgba(138,109,59,0.22))', dot: '#6B4E86' },
    overdue: { label: 'Overdue', c: '#C2660F', bg: 'light-dark(#FBE9D7, rgba(194,102,15,0.22))', dot: '#E8842B' },
};

// Map a dose issue → bucket + a friendly reason line.
function classify(i) {
    if (i.kind === 'missed') return { key: 'overdue', reason: 'Not given — overdue' };
    switch (i.code) {
        case 'R': return { key: 'refused', reason: 'Resident refused' };
        case 'S': return { key: 'omitted', reason: 'Asleep / resting', label: 'Asleep' };
        case 'W': return { key: 'omitted', reason: 'Withheld — clinical', label: 'Withheld' };
        case 'N': return { key: 'omitted', reason: 'Not available', label: 'Not available' };
        case 'O': default: return { key: 'omitted', reason: 'Omitted' };
    }
}

const fmtRange = (d) => {
    const t = Date.parse(d);
    return Number.isNaN(t) ? d : new Date(t).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};
function relDay(d, today) {
    if (d === today) return 'Today';
    const a = Date.parse(d), b = Date.parse(today);
    if (!Number.isNaN(a) && !Number.isNaN(b) && Math.round((b - a) / 86400000) === 1) return 'Yesterday';
    return fmtRange(d);
}

// Number-chip tint per category dot colour → { pale background, dark number }.
const CHIP = {
    '#C43D6B': { bg: '#F7DBE6', fg: '#B23461' }, // refused (pink)
    '#6B4E86': { bg: '#E6DCF1', fg: '#5C4275' }, // omitted (purple)
    '#E8842B': { bg: '#FBE4CD', fg: '#B9660F' }, // overdue (orange)
    '#3A7CA5': { bg: '#DAEAF3', fg: '#2E6689' }, // follow-ups (blue)
};

function StatCard({ label, value, dot, num, highlight, active, onClick, hint }) {
    const clickable = Boolean(onClick);
    const chip = CHIP[dot] ?? { bg: '#FFFFFF', fg: '#13233F' };
    const inner = (
        <Box onClick={onClick} role={clickable ? 'button' : undefined} tabIndex={clickable ? 0 : undefined}
            onKeyDown={clickable ? (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); } } : undefined}
            style={{
                background: '#13233F', borderRadius: 999,
                // Outline: subtle by default; the pill's OWN colour when selected (or always, for the
                // highlighted Overdue); teal only on hover (set in the handlers below).
                border: `${active ? 2 : 1.5}px solid ${active || highlight ? dot : 'rgba(255,255,255,0.16)'}`,
                padding: '7px 8px 7px 15px', cursor: clickable ? 'pointer' : 'default',
                display: 'inline-flex', alignItems: 'center', gap: 10, whiteSpace: 'nowrap',
                boxShadow: '0 8px 20px -14px rgba(19,35,63,0.5)',
                transition: 'transform .15s ease, box-shadow .15s ease, border-color .15s ease',
            }}
            onMouseEnter={clickable ? (e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 12px 24px -12px rgba(19,35,63,0.6)'; e.currentTarget.style.borderColor = '#45C1BF'; } : undefined}
            onMouseLeave={clickable ? (e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = '0 8px 20px -14px rgba(19,35,63,0.5)'; e.currentTarget.style.borderColor = active || highlight ? dot : 'rgba(255,255,255,0.16)'; } : undefined}>
            <Box w={9} h={9} style={{ borderRadius: '50%', background: dot, flexShrink: 0 }} />
            <Text fz={12.5} fw={600} c="#FFFFFF">{label}</Text>
            <Box style={{ background: chip.bg, borderRadius: 999, minWidth: 24, height: 21, padding: '0 8px', display: 'inline-flex', alignItems: 'center', justifyContent: 'center' }}>
                <Text fz={13} fw={800} c={chip.fg} lh={1}>{value}</Text>
            </Box>
        </Box>
    );
    return hint ? <Tooltip label={hint} withArrow openDelay={300}>{inner}</Tooltip> : inner;
}

// TRIAL — light "white pill" stat variant (Image #9): white pill, coloured left cap, number + label;
// full colour outline + coloured text when highlighted or selected.
function PillLite({ label, value, color, highlight, active, onClick, hint }) {
    const clickable = Boolean(onClick);
    const on = active || highlight;
    const rest = 'light-dark(#EAEEF3, var(--mantine-color-dark-4))';
    const inner = (
        <Box onClick={onClick} role={clickable ? 'button' : undefined} tabIndex={clickable ? 0 : undefined}
            onKeyDown={clickable ? (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); } } : undefined}
            style={{
                background: 'light-dark(#FFFFFF, var(--mantine-color-dark-6))', borderRadius: 999,
                border: `1.5px solid ${on ? color : rest}`,
                boxShadow: '0 6px 16px -12px rgba(20,50,80,0.3)',
                display: 'inline-flex', alignItems: 'center', gap: 11, padding: '7px 18px 7px 9px',
                cursor: clickable ? 'pointer' : 'default', whiteSpace: 'nowrap',
                transition: 'transform .15s ease, border-color .15s ease, box-shadow .15s ease',
            }}
            onMouseEnter={clickable ? (e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.borderColor = color; } : undefined}
            onMouseLeave={clickable ? (e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.borderColor = on ? color : rest; } : undefined}>
            <Box style={{ width: 5, height: 24, borderRadius: 999, background: color, flexShrink: 0 }} />
            <Text fz={20} fw={800} c={highlight ? color : TXT} lh={1}>{value}</Text>
            <Text fz={13} fw={600} c={highlight ? color : 'dimmed'}>{label}</Text>
        </Box>
    );
    return hint ? <Tooltip label={hint} withArrow openDelay={300}>{inner}</Tooltip> : inner;
}

const ACTION_OPTIONS = ['No action needed', 'Re-administered', 'GP informed', 'Family informed', 'Pharmacy informed', 'Recorded for audit', 'Other'];

// Slide-in detail panel — resolve form for outstanding doses, read-only + edit/undo for resolved ones.
function DosePanel({ item, date, onClose, onDone }) {
    const [editing, setEditing] = useState(!item.resolved);
    const form = useForm({
        mar_sheet_id: item.mar_sheet_id, review_date: date, time_slot: item.slot,
        dose_kind: item.kind, code: item.code ?? '',
        clinical_action: item.clinical_action ?? '', notes: item.notes ?? '',
    });
    const submit = () => form.post(RESOLVE, { preserveScroll: true, onSuccess: onDone });
    const undo = () => router.post(UNRESOLVE, { mar_sheet_id: item.mar_sheet_id, review_date: date, time_slot: item.slot }, { preserveScroll: true, onSuccess: onDone });
    const cap = { fontSize: 10, textTransform: 'uppercase', fontWeight: 700, letterSpacing: 0.5 };
    return (
        <Box style={{ ...card, padding: '18px 20px', width: '100%' }}>
            <Group justify="space-between" wrap="nowrap" mb={12}>
                <Group gap={10} wrap="nowrap" style={{ minWidth: 0 }}>
                    <Avatar color={avatarColor(item.resident_name ?? '')} radius="xl" size={40}>{initials(item.resident_name ?? '')}</Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz="sm" fw={700} c={TXT} truncate>{item.resident_name}{item.room ? ` · Room ${item.room}` : ''}</Text>
                        <Text fz="xs" c="dimmed" truncate>{item.medication_name} · {item.slot}</Text>
                    </Box>
                </Group>
                <ActionIcon variant="subtle" color="gray" radius="xl" onClick={onClose} aria-label="Close"><IconX size={18} /></ActionIcon>
            </Group>
            <Divider mb={14} />
            {editing ? (
                <Stack gap={12}>
                    <Text fz={15} fw={800} c={TXT}>{item.resolved ? 'Edit resolution' : 'Resolve dose'}</Text>
                    <Select label="Clinical action" placeholder="What was done?" data={ACTION_OPTIONS} value={form.data.clinical_action} onChange={(v) => form.setData('clinical_action', v ?? '')} error={form.errors.clinical_action} required comboboxProps={{ withinPortal: true }} />
                    <Textarea label="Notes" autosize minRows={3} value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                    <Group grow mt={4}>
                        {item.resolved && <Button variant="default" radius={10} onClick={() => setEditing(false)}>Cancel</Button>}
                        <Button radius={10} loading={form.processing} onClick={submit} style={{ background: 'light-dark(#3A7CA5, #1F9E93)', color: '#fff' }}>{item.resolved ? 'Save changes' : 'Mark resolved'}</Button>
                    </Group>
                </Stack>
            ) : (
                <Stack gap={13}>
                    <Group justify="space-between" align="flex-start" wrap="nowrap" gap="sm">
                        <Badge color="teal" variant="light" radius="sm" leftSection={<IconCheck size={12} stroke={3} />}>Resolved</Badge>
                        {(() => {
                            const last = (item.history || [])[(item.history || []).length - 1];
                            const at = last ? last.at : item.reviewed_at;
                            const by = last ? last.by : item.reviewed_by;
                            return at ? (
                                <Text fz={10.5} c="dimmed" ta="right" style={{ lineHeight: 1.3, flexShrink: 0 }}>
                                    <Text span style={cap} c="dimmed">Last updated</Text><br />{at}{by ? ` · ${by}` : ''}
                                </Text>
                            ) : null;
                        })()}
                    </Group>
                    <Box><Text c="dimmed" mb={3} style={cap}>Clinical action</Text><Text fz="sm" fw={600} c={TXT}>{item.clinical_action || '—'}</Text></Box>
                    <Box><Text c="dimmed" mb={3} style={cap}>Notes</Text><Text fz="sm" c={TXT} style={{ whiteSpace: 'pre-wrap' }}>{item.notes || 'No notes recorded.'}</Text></Box>
                    <Box>
                        <Text c="dimmed" mb={8} style={cap}>Change log <Text span c="dimmed" fw={400}>· {(item.history || []).length} {((item.history || []).length === 1) ? 'entry' : 'entries'}</Text></Text>
                        <ScrollArea.Autosize mah={230} type="hover" offsetScrollbars>
                        <Stack gap={10} pr={6}>
                            {(item.history || []).map((h, i, arr) => {
                                const m = { resolved: '#1F8A5B', edited: '#C2660F', removed: '#C43D6B' }[h.action] || '#98A1AB';
                                const lbl = { resolved: 'Resolved', edited: 'Edited', removed: 'Removed' }[h.action] || h.action;
                                const prev = i > 0 ? arr[i - 1] : null;
                                const actChg = prev && (prev.clinical_action || '') !== (h.clinical_action || '');
                                const noteChg = prev && (prev.notes || '') !== (h.notes || '');
                                return (
                                    <Group key={i} gap={9} wrap="nowrap" align="flex-start">
                                        <Box w={7} h={7} style={{ borderRadius: '50%', background: m, flexShrink: 0, marginTop: 5 }} />
                                        <Box style={{ minWidth: 0 }}>
                                            <Text fz={12.5} c={TXT} lh={1.3}><Text span fw={700} c={m}>{lbl}</Text>{h.by ? ` · ${h.by}` : ''}</Text>
                                            <Text fz={11} c="dimmed" mb={2}>{h.at}</Text>
                                            <Text fz={11.5} c={TXT} lh={1.35}>{h.clinical_action || '—'}{h.notes ? ` · “${h.notes}”` : ''}</Text>
                                            {prev && (actChg || noteChg) && (
                                                <Text fz={11} c="#98A1AB" lh={1.35} mt={1}>was: {actChg ? (prev.clinical_action || '—') : ''}{actChg && noteChg ? ' · ' : ''}{noteChg ? `“${prev.notes || '—'}”` : ''}</Text>
                                            )}
                                        </Box>
                                    </Group>
                                );
                            })}
                            {!(item.history && item.history.length) && (
                                <Text fz="xs" c="dimmed">Resolved by {item.reviewed_by || '—'} · {item.reviewed_at || '—'} (no earlier changes recorded).</Text>
                            )}
                        </Stack>
                        </ScrollArea.Autosize>
                    </Box>
                    <Group grow mt={4}>
                        <Button variant="light" color="gray" radius={10} onClick={() => setEditing(true)}>Edit</Button>
                        <Button variant="light" color="red" radius={10} onClick={undo}>Undo</Button>
                    </Group>
                </Stack>
            )}
        </Box>
    );
}

export default function MissedDoses({ items = [], date, prevDate, nextDate, todayDate, home }) {
    const railBelow = useMediaQuery('(max-width: 1000px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    // Uniform page scale — the whole page renders at this zoom on every screen size.
    // Tune this one number to scale the entire Missed doses page up/down consistently.
    const PAGE_ZOOM = 0.9;
    const [tab, setTab] = useState('all');
    // Master-detail: `selected` drives the open state; `panelItem` holds the content so it stays
    // rendered while the panel animates closed (smooth collapse). Resolve & view both use this.
    const [selected, setSelected] = useState(null);
    const [panelItem, setPanelItem] = useState(null);
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState({ key: 'when', dir: 'asc' });

    const reload = (params) => router.get(PAGE, { date, ...params }, { preserveScroll: true, preserveState: true });
    // Data-freshness stamp — recomputes each time a fresh payload arrives, so the shown time reflects the current data.
    const loadedAt = useMemo(() => new Date(), [items]);
    const loadedTime = loadedAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const openPanel = (i) => { setPanelItem(i); setSelected(i); };
    const closePanel = () => setSelected(null);
    const openResolve = openPanel;
    const openView = openPanel;

    // Decorate every item with its bucket + reason once.
    const rows = useMemo(() => items.map((i) => {
        const c = classify(i);
        return { ...i, key: c.key, reason: c.reason, outLabel: c.label || OUTCOME[c.key].label };
    }), [items]);

    const stats = useMemo(() => ({
        refused: rows.filter((r) => r.key === 'refused').length,
        omitted: rows.filter((r) => r.key === 'omitted').length,
        overdue: rows.filter((r) => r.key === 'overdue' && !r.resolved).length,
        followups: rows.filter((r) => !r.resolved).length,
    }), [rows]);

    const shown = useMemo(() => {
        const q = query.trim().toLowerCase();
        let list = rows.filter((r) => (tab === 'all' ? true : tab === 'resolved' ? r.resolved : r.key === tab));
        if (q) list = list.filter((r) => [r.resident_name, r.room ? `room ${r.room}` : '', r.medication_name, r.reason, r.outLabel]
            .some((v) => String(v ?? '').toLowerCase().includes(q)));
        const val = (r) => {
            switch (sort.key) {
                case 'resident': return String(r.resident_name ?? '');
                case 'medication': return String(r.medication_name ?? '');
                case 'outcome': return (r.resolved ? '2' : '1') + String(r.outLabel ?? '');
                default: return String(r.slot ?? '');
            }
        };
        list = [...list].sort((a, b) => val(a).localeCompare(val(b), undefined, { numeric: true, sensitivity: 'base' }));
        if (sort.dir === 'desc') list.reverse();
        return list;
    }, [rows, tab, query, sort]);

    const toggleSort = (k) => setSort((s) => ({ key: k, dir: s.key === k && s.dir === 'asc' ? 'desc' : 'asc' }));
    // Clickable column header with an asc/desc chevron (idle shows a faint up/down selector).
    const sortHead = (label, k, style, vis = {}) => {
        const active = sort.key === k;
        return (
            <Box component="button" type="button" onClick={() => toggleSort(k)} {...vis}
                style={{ ...style, display: 'flex', alignItems: 'center', gap: 3, background: 'none', border: 'none', padding: 0, cursor: 'pointer' }}>
                <Text component="span" fz={10} fw={700} tt="uppercase" c={active ? 'light-dark(#1E2F4D, #E8EBF1)' : 'dimmed'} style={{ letterSpacing: 0.6 }}>{label}</Text>
                <Box component="span" style={{ display: 'inline-flex', color: active ? '#1F9E93' : undefined, opacity: active ? 1 : 0.4 }}>
                    {active ? (sort.dir === 'asc' ? <IconChevronUp size={12} stroke={2.6} /> : <IconChevronDown size={12} stroke={2.6} />) : <IconSelector size={12} stroke={1.8} />}
                </Box>
            </Box>
        );
    };

    // "By reason" — grouped counts with the bucket colour, biggest first.
    const byReason = useMemo(() => {
        const m = new Map();
        rows.forEach((r) => {
            const cur = m.get(r.reason) || { reason: r.reason, count: 0, color: OUTCOME[r.key].dot };
            cur.count += 1; m.set(r.reason, cur);
        });
        const arr = [...m.values()].sort((a, b) => b.count - a.count);
        const max = arr.reduce((n, x) => Math.max(n, x.count), 1);
        return { arr, max };
    }, [rows]);

    const firstOutstanding = shown.find((r) => !r.resolved) || rows.find((r) => !r.resolved) || null;

    const exportCsv = () => {
        const head = ['Resident', 'Room', 'Medication', 'When', 'Outcome', 'Reason', 'Resolved'];
        const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const lines = shown.map((r) => [r.resident_name, r.room, r.medication_name, `${r.slot} ${relDay(date, todayDate)}`, r.outLabel, r.reason, r.resolved ? 'Yes' : 'No'].map(esc).join(','));
        const blob = new Blob([[head.join(','), ...lines].join('\n')], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = `missed-doses-${date}.csv`;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 0);
    };

    // Full audit trail — every event (not just the filtered view) + the recorded resolution.
    const exportAudit = () => {
        const head = ['Resident', 'Room', 'Medication', 'Slot', 'Date', 'Outcome', 'Reason', 'Resolved', 'Clinical action', 'Notes', 'Reviewed by', 'Reviewed at', 'Change log'];
        const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const histStr = (r) => (r.history || []).map((h) => `${h.action} by ${h.by || '?'} @ ${h.at || '?'}: ${h.clinical_action || ''}${h.notes ? ' — ' + h.notes : ''}`).join('  |  ');
        const lines = rows.map((r) => [r.resident_name, r.room, r.medication_name, r.slot, date, r.outLabel, r.reason, r.resolved ? 'Yes' : 'No', r.clinical_action, r.notes, r.reviewed_by, r.reviewed_at, histStr(r)].map(esc).join(','));
        const blob = new Blob([[head.join(','), ...lines].join('\n')], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = `missed-doses-audit-${date}.csv`;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 0);
    };

    const TABS = [
        { k: 'all', l: 'All', n: rows.length },
        { k: 'refused', l: 'Refused', n: stats.refused },
        { k: 'omitted', l: 'Omitted', n: stats.omitted },
        { k: 'overdue', l: 'Overdue', n: rows.filter((r) => r.key === 'overdue').length },
        { k: 'resolved', l: 'Resolved', n: rows.filter((r) => r.resolved).length },
    ];

    return (
        <AppShell title="Missed doses" section="Medication">
            <Head title="Missed & refused doses" />
            <Box px={{ base: 0, sm: 10 }} pb={14} style={{ zoom: PAGE_ZOOM }}>
                {/* Header */}
                <Group justify="space-between" align="flex-start" wrap="wrap" gap="md" mb={20}>
                    <Stack gap={12}>
                        <Group gap={13} wrap="nowrap" align="center">
                            <ThemeIcon size={44} radius={13} style={{ background: 'light-dark(#FBE9EC, rgba(196,61,107,0.18))', color: '#C43D6B', flexShrink: 0 }}><IconAlertTriangle size={23} stroke={1.9} /></ThemeIcon>
                            <Box style={{ minWidth: 0 }}>
                                <Text fz={20} fw={800} c={TXT} lh={1.2}>Missed &amp; refused doses</Text>
                                <Text fz={12.5} fw={500} c="light-dark(#586A85, #9AA8BE)" mt={2}>{[relDay(date, todayDate), home, `${stats.followups} need follow-up`].filter(Boolean).join(' · ')}</Text>
                            </Box>
                        </Group>
                        <Group gap={8} wrap="nowrap" style={{ width: 'fit-content', background: 'light-dark(#E3E9F2, rgba(255,255,255,0.05))', borderRadius: 999, padding: '3px 4px 3px 12px' }}>
                            <Box w={7} h={7} style={{ borderRadius: '50%', background: '#1F9E93', boxShadow: '0 0 0 3px rgba(31,158,147,0.18)' }} />
                            <Text fz={11.5} fw={600} c="light-dark(#48576E, #A9B6CB)">Live · data as of {loadedTime}</Text>
                            <Tooltip label="Refresh now" withArrow><ActionIcon size="sm" variant="filled" radius="xl" aria-label="Refresh data" onClick={() => router.reload()} style={{ background: 'light-dark(#3A7CA5, #1F9E93)' }}><IconRefresh size={13} stroke={2.6} /></ActionIcon></Tooltip>
                        </Group>
                    </Stack>
                    <Group gap={10} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <Group gap={6} wrap="nowrap" style={{ ...card, borderRadius: 12, padding: '5px 6px', boxShadow: '0 2px 6px rgba(20,50,80,0.05)' }}>
                            <Tooltip label="Previous day" withArrow><ActionIcon variant="subtle" color="gray" radius="xl" aria-label="Previous day" onClick={() => reload({ date: prevDate })}><IconChevronLeft size={16} /></ActionIcon></Tooltip>
                            <TextInput variant="unstyled" type="date" size="xs" value={date || ''} onChange={(e) => reload({ date: e.currentTarget.value })} styles={{ input: { fontWeight: 700, fontSize: 13, color: 'var(--mantine-color-text)', width: 132, textAlign: 'center', cursor: 'pointer' } }} />
                            <Tooltip label="Next day" withArrow><ActionIcon variant="subtle" color="gray" radius="xl" aria-label="Next day" onClick={() => reload({ date: nextDate })}><IconChevronRight size={16} /></ActionIcon></Tooltip>
                            <Button size="compact-sm" variant="subtle" color="indigo" radius="xl" onClick={() => reload({ date: todayDate })} styles={{ label: { fontSize: 13, fontWeight: 700 } }}>Today</Button>
                        </Group>
                        <Button variant="default" radius={12} leftSection={<IconShieldCheck size={16} />} onClick={exportAudit}>Download audit</Button>
                        <Button radius={12} leftSection={<IconDownload size={16} />} onClick={exportCsv}
                            style={{ background: 'light-dark(#3A7CA5, #1F9E93)', paddingInline: 18, boxShadow: 'light-dark(0 8px 18px rgba(58,124,165,0.28), 0 8px 18px rgba(31,158,147,0.35))' }}>Export report</Button>
                    </Group>
                </Group>

                {/* Distribution summary — full-width donut + horizontal legend. */}
                {(() => {
                    const SEG = [
                        { label: 'Refused', count: stats.refused, color: '#C43D6B', tab: 'refused' },
                        { label: 'Omitted', count: stats.omitted, color: '#6B4E86', tab: 'omitted' },
                        { label: 'Overdue unresolved', count: stats.overdue, color: '#E8842B', tab: 'overdue' },
                        { label: 'Follow-ups pending', count: stats.followups, color: '#3A7CA5', tab: 'followups' },
                    ];
                    const total = SEG.reduce((n, s) => n + s.count, 0);
                    const pct = (n) => (total ? Math.round((n / total) * 100) : 0);
                    const sections = SEG.filter((s) => s.count > 0).map((s) => ({ value: pct(s.count), color: s.color }));
                    const numColor = (label) => (label === 'Overdue unresolved' ? '#E8842B' : label === 'Follow-ups pending' ? '#3A7CA5' : TXT);
                    return (
                        <Box style={{ ...card, marginBottom: 20, padding: isSm ? '13px 14px' : '14px 24px' }}>
                            <Group align="center" gap={isSm ? 16 : 30} wrap="wrap">
                                <RingProgress
                                    size={124} thickness={14} roundCaps
                                    sections={sections.length ? sections : [{ value: 100, color: 'light-dark(#EEF1F5, var(--mantine-color-dark-5))' }]}
                                    label={<Box ta="center"><Text fz={25} fw={800} c={TXT} lh={1}>{total}</Text><Text fz={11.5} c="dimmed">events</Text></Box>}
                                />
                                <Box style={{ flex: 1, minWidth: 0, display: 'flex', flexWrap: 'wrap' }}>
                                    {SEG.map((s, i) => (
                                        <Box key={s.label} component="button" type="button"
                                            onClick={() => (s.tab === 'followups' ? (firstOutstanding && openResolve(firstOutstanding)) : setTab(tab === s.tab ? 'all' : s.tab))}
                                            onMouseEnter={(e) => { if (tab !== s.tab) e.currentTarget.style.background = 'light-dark(#EEF2F7, rgba(255,255,255,0.04))'; }}
                                            onMouseLeave={(e) => { e.currentTarget.style.background = tab === s.tab ? 'light-dark(#E9EEF5, rgba(255,255,255,0.06))' : 'transparent'; }}
                                            style={{
                                                flex: '1 1 130px', minWidth: 0, textAlign: 'left', cursor: 'pointer', border: 'none', borderRadius: 12,
                                                padding: isSm ? '8px 12px' : '5px 18px', transition: 'background .14s',
                                                background: tab === s.tab ? 'light-dark(#E9EEF5, rgba(255,255,255,0.06))' : 'transparent',
                                                boxShadow: i ? 'inset 1px 0 0 light-dark(#E9ECF1, rgba(255,255,255,0.08))' : 'none',
                                            }}>
                                            <Group gap={8} wrap="nowrap" mb={6} style={{ minWidth: 0 }}>
                                                <Box w={9} h={9} style={{ borderRadius: '50%', background: s.color, flexShrink: 0 }} />
                                                <Text fz={14} fw={600} c="light-dark(#586A85, #9AA8BE)" truncate>{s.label}</Text>
                                            </Group>
                                            <Group gap={9} align="baseline" wrap="nowrap">
                                                <Text fz={34} fw={800} c={numColor(s.label)} lh={1}>{s.count}</Text>
                                                <Text fz={14} fw={600} c="dimmed">{pct(s.count)}%</Text>
                                            </Group>
                                        </Box>
                                    ))}
                                </Box>
                            </Group>
                        </Box>
                    );
                })()}

                {/* Log + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 20, alignItems: 'flex-start' }}>
                    {/* Log */}
                    <Box style={{ ...card, flex: '1 1 480px', minWidth: 0, padding: isSm ? '16px 14px' : '22px 24px' }}>
                        <Group justify="space-between" align="center" pb={14} wrap="wrap" gap="sm">
                            <Group gap={22} wrap="nowrap" style={{ flex: '1 1 240px', minWidth: 0 }}>
                                <Text fz={18} fw={800} c={TXT}>Log</Text>
                                <TextInput
                                    size="xs" radius="md" flex="1 1 auto" maw={280}
                                    placeholder="Search resident, room or medication…"
                                    leftSection={<IconSearch size={14} />}
                                    value={query} onChange={(e) => setQuery(e.currentTarget.value)}
                                    rightSection={query ? <ActionIcon size="sm" variant="subtle" color="gray" radius="xl" aria-label="Clear search" onClick={() => setQuery('')}><IconX size={12} /></ActionIcon> : null}
                                />
                            </Group>
                            <Group gap={3} wrap="nowrap" style={{ background: 'light-dark(#E3E9F2, var(--mantine-color-dark-8))', borderRadius: 11, padding: 4 }}>
                                {TABS.map((t) => {
                                    const on = tab === t.k;
                                    return (
                                        <Box key={t.k} component="button" type="button" onClick={() => setTab(t.k)}
                                            onMouseEnter={(e) => { if (!on) e.currentTarget.style.background = 'light-dark(#D5DEEA, var(--mantine-color-dark-6))'; }}
                                            onMouseLeave={(e) => { if (!on) e.currentTarget.style.background = 'transparent'; }}
                                            style={{
                                                display: 'inline-flex', alignItems: 'center', gap: 6, border: 'none', cursor: 'pointer', borderRadius: 8,
                                                padding: '5px 9px 5px 11px', fontSize: 12.5, fontWeight: 600, whiteSpace: 'nowrap', transition: 'background .12s ease',
                                                background: on ? 'light-dark(#ffffff, rgba(69,193,191,0.16))' : 'transparent',
                                                color: on ? 'light-dark(#1E2F4D, #6FE0DB)' : 'light-dark(#5A6B84, #94A2B8)',
                                                boxShadow: on ? 'light-dark(0 1px 2px rgba(16,24,40,0.12), inset 0 0 0 1px rgba(69,193,191,0.5))' : 'none',
                                            }}>
                                            {t.l}
                                            <Box component="span" style={{
                                                minWidth: 18, textAlign: 'center', padding: '1px 6px', borderRadius: 999, fontSize: 11, fontWeight: 700,
                                                background: on ? 'light-dark(#E4F1F1, rgba(69,193,191,0.22))' : 'light-dark(#D3DBE8, rgba(255,255,255,0.06))',
                                                color: on ? 'light-dark(#1F8A8A, #6FE0DB)' : 'light-dark(#6A7A92, #94A2B8)',
                                            }}>{t.n}</Box>
                                        </Box>
                                    );
                                })}
                            </Group>
                        </Group>
                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={4} pb={10} style={{ borderBottom: '1px solid light-dark(#E3E8EF, var(--mantine-color-dark-4))' }}>
                            {sortHead('Resident', 'resident', { flex: '2 1 180px' })}
                            {sortHead('Medication', 'medication', { flex: '2 1 160px' }, { visibleFrom: 'sm' })}
                            {sortHead('When', 'when', { width: 90 }, { visibleFrom: 'md' })}
                            {sortHead('Outcome', 'outcome', { width: 96 })}
                            <Box style={{ width: 18 }} />
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py={48}>{query ? `No matches for “${query}”.` : 'Nothing to review for this view.'}</Text>
                            : (
                            <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                            {shown.map((r, idx) => {
                                const o = OUTCOME[r.key];
                                return (
                                    <Group key={r.id} gap={14} wrap="nowrap" align="center" px={4} py={17}
                                        onClick={() => (r.resolved ? openView(r) : openResolve(r))}
                                        style={{ cursor: 'pointer', borderTop: idx ? '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))' : 'none', borderRadius: 10, transition: 'background .15s' }}
                                        onMouseEnter={(e) => { e.currentTarget.style.background = 'light-dark(#E4EAF3, var(--mantine-color-dark-5))'; }}
                                        onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
                                        {/* Resident */}
                                        <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 180px', minWidth: 0 }}>
                                            <Avatar color={avatarColor(r.resident_name ?? '')} radius="xl" size={38}>{initials(r.resident_name ?? '')}</Avatar>
                                            <Box style={{ minWidth: 0 }}>
                                                <Text fz={13.5} fw={700} c={TXT} truncate>{r.resident_name}</Text>
                                                <Text fz={11.5} c="dimmed" truncate>{r.room ? `Room ${r.room}` : '—'}</Text>
                                            </Box>
                                        </Group>
                                        {/* Medication + reason */}
                                        <Box style={{ flex: '2 1 160px', minWidth: 0 }} visibleFrom="sm">
                                            <Text fz={13} fw={600} c={TXT} truncate>{r.medication_name}</Text>
                                            <Text fz={11.5} c={r.resolved ? '#1F8A5B' : o.c} truncate>
                                                {r.resolved ? (r.clinical_action ? `Resolved · ${r.clinical_action}` : 'Resolved') : r.reason}
                                            </Text>
                                        </Box>
                                        {/* When */}
                                        <Box style={{ width: 90, flexShrink: 0 }} visibleFrom="md">
                                            <Text fz={13} fw={700} c={TXT} lh={1.2}>{r.slot}</Text>
                                            <Text fz={11} c="dimmed">{relDay(date, todayDate)}</Text>
                                        </Box>
                                        {/* Outcome */}
                                        <Box style={{ width: 96, flexShrink: 0 }}>
                                            {r.resolved
                                                ? <Box style={{ display: 'inline-flex', alignItems: 'center', gap: 4, padding: '4px 10px', borderRadius: 999, fontSize: 10.5, fontWeight: 800, letterSpacing: 0.4, background: 'light-dark(#E7F3E8, rgba(31,138,91,0.2))', color: '#1F8A5B' }}><IconCheck size={11} stroke={3} />RESOLVED</Box>
                                                : <Box style={{ display: 'inline-flex', alignItems: 'center', padding: '4px 11px', borderRadius: 999, fontSize: 10.5, fontWeight: 800, letterSpacing: 0.5, background: o.bg, color: o.c }}>{r.outLabel.toUpperCase()}</Box>}
                                        </Box>
                                        <ActionIcon variant="subtle" color="gray" radius="xl" style={{ flexShrink: 0 }}><IconChevronRight size={16} /></ActionIcon>
                                    </Group>
                                );
                            })}
                            </ScrollArea.Autosize>
                            )}
                    </Box>

                    {/* Detail panel — slides in from the right on row click (replaces the popups + rail).
                        panelItem stays mounted until the collapse transition ends, so closing is smooth too. */}
                    <Box onTransitionEnd={() => { if (!selected) setPanelItem(null); }}
                        style={{ flexBasis: selected ? (railBelow ? '100%' : 400) : 0, flexGrow: 0, flexShrink: 1, minWidth: 0, alignSelf: 'stretch', opacity: selected ? 1 : 0, transition: 'flex-basis .32s ease, opacity .28s ease', overflow: 'hidden' }}>
                        {panelItem && <DosePanel key={panelItem.id} item={panelItem} date={date} onClose={closePanel} onDone={closePanel} />}
                    </Box>
                </Box>
            </Box>
        </AppShell>
    );
}
