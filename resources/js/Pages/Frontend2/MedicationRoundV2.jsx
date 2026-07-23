import { useState, useMemo, useRef, useEffect } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Avatar, Button, ActionIcon, Collapse, Tooltip,
    Textarea, Select, TextInput,
} from '@mantine/core';
import {
    IconChevronRight, IconCheck, IconX, IconAlertTriangle, IconClockHour4, IconPill,
    IconLock, IconLockOpen, IconPlayerPause, IconPlayerPlay, IconSun, IconSoup, IconSunset,
    IconMoon, IconScan, IconShieldCheck, IconClock,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { CODE_LABELS, isGivenCode } from '@frontend/lib/medicationCodes';
import { initials } from '@frontend/lib/avatarColor';

const ENDPOINT = '/frontend2/medication-round-v2';
const JAKARTA = "'Plus Jakarta Sans', system-ui, sans-serif";
const PAGE_ZOOM = 1; // whole-page scale knob (1 = 100% / unchanged). Adjust to taste.

// Mockup palette
const ACCENT = '#3A7CA5';       // teal-blue — primary
const INK = '#13233F';          // raw navy — backgrounds / signature-pad stroke only
const TXT = 'light-dark(#13233F, #E8EBF1)';   // primary text — adapts to dark mode
const CARD_BG = 'light-dark(#ffffff, var(--mantine-color-dark-6))';
const CARD_BD = 'light-dark(#EEF1F4, var(--mantine-color-dark-4))';
const SOFT = 'light-dark(#f7f9fb, var(--mantine-color-dark-7))';   // inner panels / inputs / row hover
const LINE = 'light-dark(#eef1f4, var(--mantine-color-dark-4))';   // hairline dividers
const GIVEN = '#88B13F';        // green (bar/dot); dark text variant:
const GIVEN_D = '#5e7d27';
const OVERDUE = '#F58321';       // orange (bar/dot); dark text variant:
const OVERDUE_D = '#cf6a12';
const REFUSE = '#B8557A';        // pink
const PRN_C = '#795076';         // purple
const MUTED = '#9aa4ae';
const SCHED = '#c2cad2';

const card = {
    background: CARD_BG,
    borderRadius: 22,
    boxShadow: '0 10px 30px -18px rgba(20,50,80,0.22)',
    border: `1px solid ${CARD_BD}`,
};
const ROUND_ICONS = { morning: IconSun, lunchtime: IconSoup, evening: IconSunset, night: IconMoon };
// Bundled theme tokens so sibling pages (e.g. the per-resident give-meds page) share the exact look.
export const THEME = { ACCENT, INK, TXT, CARD_BG, CARD_BD, SOFT, LINE, GIVEN, GIVEN_D, OVERDUE, OVERDUE_D, REFUSE, PRN_C, MUTED, SCHED, JAKARTA, ROUND_ICONS, card };
const NO_ALLERGY = /^(no|none|nil|n\/?a|na|none known|no known allergies|no allergies|unknown)$/i;
export const cleanAllergies = (list) => (list ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));

// Delegates to the shared list. 'S' (asleep) is NOT given — see medicationCodes.js.
export const isGiven = (c) => isGivenCode(c);

export function fmtDate(d) {
    if (!d) return '';
    const t = Date.parse(d);
    if (Number.isNaN(t)) return d;
    return new Date(t).toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}
export function metrics(resident) {
    const sched = (resident.rows ?? []).filter((r) => !r.as_required);
    const total = sched.length;
    const done = sched.filter((r) => r.code).length;
    const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
    const nextDue = sched.filter((r) => !r.code && r.slot).map((r) => r.slot).sort()[0] ?? null;
    const complete = total > 0 && done === total;
    return { total, done, overdue, nextDue, complete };
}
export function statusOf(m) {
    if (m.total === 0) return { label: 'No meds', color: MUTED };
    if (m.complete) return { label: 'Complete', color: GIVEN_D };
    if (m.overdue > 0) return { label: `${m.overdue} overdue`, color: OVERDUE_D };
    return { label: `${m.total - m.done} due`, color: ACCENT };
}
// Is this med a live action (uncoded + due/overdue/PRN)?
export const isActionable = (row) => !row.code && (row.as_required || ['overdue', 'due_now', 'due'].includes(row.status));

const REASONS = ['Resident refused', 'Asleep / resting', 'Nil by mouth', 'Away from home / on leave',
    'In hospital', 'Medication unavailable', 'Clinical decision — withheld', 'Vomited dose', 'Other (see notes)'];

/* ------------------------------- Signature pad ------------------------------- */
export function SigPad({ placeholder, borderColor = '#cdd6de', bg = '#fcfdfe' }) {
    const ref = useRef(null);
    const drawing = useRef(false);
    const [inked, setInked] = useState(false);
    useEffect(() => {
        const c = ref.current;
        const resize = () => {
            const r = c.getBoundingClientRect();
            if (!r.width) return;
            const dpr = window.devicePixelRatio || 1;
            c.width = r.width * dpr; c.height = r.height * dpr;
            const x = c.getContext('2d');
            x.setTransform(dpr, 0, 0, dpr, 0, 0);
            x.lineWidth = 2; x.lineCap = 'round'; x.lineJoin = 'round'; x.strokeStyle = INK;
        };
        resize();
        window.addEventListener('resize', resize);
        return () => window.removeEventListener('resize', resize);
    }, []);
    const at = (e) => { const r = ref.current.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; };
    const down = (e) => { drawing.current = true; const x = ref.current.getContext('2d'); const p = at(e); x.beginPath(); x.moveTo(p.x, p.y); setInked(true); e.preventDefault(); };
    const move = (e) => { if (!drawing.current) return; const x = ref.current.getContext('2d'); const p = at(e); x.lineTo(p.x, p.y); x.stroke(); };
    const up = () => { drawing.current = false; };
    const clear = () => { const c = ref.current; c.getContext('2d').clearRect(0, 0, c.width, c.height); setInked(false); };
    return (
        <Box style={{ position: 'relative' }}>
            <canvas ref={ref} onPointerDown={down} onPointerMove={move} onPointerUp={up} onPointerLeave={up}
                style={{ width: '100%', height: 88, border: `1.5px dashed ${borderColor}`, borderRadius: 12, background: bg, display: 'block', touchAction: 'none' }} />
            {!inked && <Text style={{ position: 'absolute', left: 14, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} fz={12.5} c="#b3bcc6">{placeholder}</Text>}
            <Button size="compact-xs" variant="default" onClick={clear} style={{ position: 'absolute', top: 8, right: 8, fontFamily: JAKARTA }}>Clear</Button>
        </Box>
    );
}

/* ------------------------------- Admin modal ------------------------------- */
const OUTCOMES = [{ k: 'Given', code: 'A', c: ACCENT }, { k: 'Refused', code: 'R', c: REFUSE }, { k: 'Omitted', code: 'O', c: OVERDUE_D }];

export function AdminModal({ ctx, date, adminBy, onClose, endpoint = ENDPOINT, redirectTo }) {
    const [outcome, setOutcome] = useState(ctx?.outcome ?? 'Given');
    const [scanned, setScanned] = useState(false);
    const [reason, setReason] = useState(REASONS[0]);
    const [notes, setNotes] = useState('');
    const [witness, setWitness] = useState('');
    const [busy, setBusy] = useState(false);
    if (!ctx) return null;
    const row = ctx.row;
    const oc = OUTCOMES.find((o) => o.k === outcome) ?? OUTCOMES[0];
    const witnessRequired = row.is_controlled || outcome !== 'Given';

    const submit = () => {
        setBusy(true);
        router.post(`${endpoint}/record`, {
            mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot || 'PRN', code: oc.code,
            dose_given: row.dose ?? '', reason: outcome === 'Given' ? '' : reason, notes,
            witnessed_by: witness, redirect_to: redirectTo,
        }, { preserveScroll: true, preserveState: true, onFinish: () => { setBusy(false); onClose(); } });
    };

    return (
        <Box style={{ position: 'fixed', inset: 0, zIndex: 300, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 16, fontFamily: JAKARTA }}>
            <Box onClick={onClose} style={{ position: 'absolute', inset: 0, background: 'rgba(19,35,63,0.55)' }} />
            <Box style={{ position: 'relative', width: '100%', maxWidth: 560, maxHeight: '90vh', display: 'flex', flexDirection: 'column', background: CARD_BG, borderRadius: 22, boxShadow: '0 30px 80px -20px rgba(19,35,63,0.5)', overflow: 'hidden' }}>
                {/* header */}
                <Group justify="space-between" align="flex-start" wrap="nowrap" style={{ padding: '22px 24px 4px' }}>
                    <Box style={{ minWidth: 0 }}>
                        <Text fz={11} fw={700} c={MUTED} style={{ letterSpacing: 0.6 }}>RECORD ADMINISTRATION</Text>
                        <Text fz={20} fw={800} c={TXT} mt={6}>{row.medication_name}</Text>
                        <Text fz={13} c="#7a8590">{[ctx.residentName, ctx.residentRoom && `Room ${ctx.residentRoom}`].filter(Boolean).join(' · ')}</Text>
                    </Box>
                    <ActionIcon variant="light" color="gray" size={34} radius={10} onClick={onClose}><IconX size={17} /></ActionIcon>
                </Group>
                {/* body */}
                <Box style={{ padding: '14px 24px 8px', overflow: 'auto' }}>
                    <Text fz={12} fw={700} c={TXT} mb={8}>Outcome</Text>
                    <Group grow gap={8} wrap="nowrap">
                        {OUTCOMES.map((o) => {
                            const on = o.k === outcome;
                            return <Button key={o.k} variant={on ? 'filled' : 'default'} radius={11} onClick={() => setOutcome(o.k)}
                                style={{ fontFamily: JAKARTA, background: on ? o.c : SOFT, color: on ? '#fff' : '#7a8590', borderColor: on ? 'transparent' : 'light-dark(#e3e6ea, var(--mantine-color-dark-4))' }}>{o.k}</Button>;
                        })}
                    </Group>

                    {/* barcode */}
                    <Group gap={12} wrap="nowrap" onClick={() => setScanned((s) => !s)} mt={16}
                        style={{ cursor: 'pointer', padding: '14px 16px', borderRadius: 12, background: scanned ? 'rgba(136,177,63,0.12)' : SOFT, border: `1.5px solid ${scanned ? '#bcdd9e' : '#e3e6ea'}` }}>
                        <IconScan size={20} color={scanned ? GIVEN_D : '#7a8590'} />
                        <Text fz={13.5} fw={600} c={scanned ? GIVEN_D : '#7a8590'} style={{ flex: 1 }}>{scanned ? 'Medication verified — barcode matched' : 'Scan medication barcode to verify'}</Text>
                        <Text fz={11} fw={700} c="#b3bcc6">TAP</Text>
                    </Group>

                    {outcome !== 'Given' && (
                        <>
                            <Text fz={12} fw={700} c={TXT} mt={18} mb={8}>Reason <span style={{ color: REFUSE }}>*</span></Text>
                            <Select data={REASONS} value={reason} onChange={setReason} allowDeselect={false} radius={11} styles={{ input: { fontFamily: JAKARTA, background: SOFT, borderColor: 'light-dark(#e3e6ea, var(--mantine-color-dark-4))' } }} />
                        </>
                    )}

                    <Text fz={12} fw={700} c={TXT} mt={18} mb={8}>Notes</Text>
                    <Textarea value={notes} onChange={(e) => setNotes(e.currentTarget.value)} placeholder="Add any relevant detail…" autosize minRows={2} radius={11} styles={{ input: { fontFamily: JAKARTA, background: SOFT, borderColor: 'light-dark(#e3e6ea, var(--mantine-color-dark-4))' } }} />

                    <Text fz={12} fw={700} c={TXT} mt={18} mb={8}>Administered by</Text>
                    <TextInput value={adminBy} readOnly radius={11} styles={{ input: { fontFamily: JAKARTA, background: SOFT, borderColor: 'light-dark(#e3e6ea, var(--mantine-color-dark-4))', fontWeight: 600, color: TXT } }} mb={10} />
                    <SigPad placeholder="Sign here" />

                    {witnessRequired && (
                        <Box mt={20} style={{ padding: 16, background: 'light-dark(#faf7fa, var(--mantine-color-dark-7))', border: '1px solid light-dark(#ecdfea, var(--mantine-color-dark-4))', borderRadius: 14 }}>
                            <Group gap={8} mb={3} wrap="nowrap"><IconShieldCheck size={16} color={PRN_C} /><Text fz={13} fw={700} c="#5c3f59">Witness signature</Text></Group>
                            <Text fz={11.5} c="#9a7f97" mb={12}>Required for controlled drugs and refusals. A second signatory confirms this record.</Text>
                            <TextInput value={witness} onChange={(e) => setWitness(e.currentTarget.value)} placeholder="Witness name & role" radius={11} styles={{ input: { fontFamily: JAKARTA, background: CARD_BG, borderColor: 'light-dark(#ecdfea, var(--mantine-color-dark-4))' } }} mb={10} />
                            <SigPad placeholder="Witness signs here" borderColor="#d8c3d5" bg="#fffdff" />
                        </Box>
                    )}
                </Box>
                {/* footer */}
                <Group justify="space-between" wrap="nowrap" style={{ padding: '16px 24px', borderTop: '1px solid light-dark(#eef1f4, var(--mantine-color-dark-4))' }}>
                    <Group gap={7} wrap="nowrap"><IconClock size={14} color={MUTED} /><Text fz={11.5} c={MUTED}>Timestamped on save</Text></Group>
                    <Group gap={10} wrap="nowrap">
                        <Button variant="default" radius={11} onClick={onClose} style={{ fontFamily: JAKARTA }}>Cancel</Button>
                        <Button radius={11} loading={busy} onClick={submit} style={{ fontFamily: JAKARTA, background: ACCENT, boxShadow: '0 8px 18px rgba(58,124,165,0.28)' }}>
                            {outcome === 'Given' ? 'Confirm & sign' : `Record ${outcome.toLowerCase()}`}
                        </Button>
                    </Group>
                </Group>
            </Box>
        </Box>
    );
}

/* ------------------------------- Round tab (matches Medication Round 1 — navy pill) ------------------------------- */
function RoundTab({ round, active, done, total, onClick }) {
    const Icon = ROUND_ICONS[round.key] ?? IconClockHour4;
    return (
        <Box component="button" onClick={onClick} style={{
            display: 'flex', alignItems: 'center', gap: 8, cursor: 'pointer', padding: '9px 14px', borderRadius: 12,
            background: active ? ACCENT : 'transparent', border: 'none', fontFamily: JAKARTA, transition: 'background .12s', whiteSpace: 'nowrap',
            boxShadow: active ? '0 6px 14px rgba(58,124,165,0.25)' : 'none',
        }}>
            <Icon size={17} stroke={1.8} color={active ? '#fff' : (round.key === 'morning' ? '#E8A93B' : round.key === 'lunchtime' ? '#5BB4E8' : round.key === 'night' ? '#6E5BE6' : '#3E6FB0')} />
            <Text fz="sm" fw={700} c={active ? '#fff' : TXT}>{round.label}</Text>
            <Badge size="sm" radius="sm" variant="filled" color="gray"
                style={{ background: active ? 'rgba(255,255,255,0.22)' : '#EEF0F4', color: active ? '#fff' : '#667085' }}>
                {done}/{total}
            </Badge>
        </Box>
    );
}

/* ------------------------------- Med line ------------------------------- */
export function MedLineV2({ row, locked, onAct, isSm }) {
    const recorded = Boolean(row.code);
    const given = isGiven(row.code);
    const overdue = row.status === 'overdue';
    const actionable = isActionable(row);

    const icon = recorded
        ? { bg: given ? 'rgba(136,177,63,0.16)' : 'rgba(184,85,122,0.14)', el: given ? <IconCheck size={15} stroke={3} color={GIVEN_D} /> : <IconX size={15} stroke={2.6} color={REFUSE} /> }
        : overdue || actionable
            ? { bg: 'rgba(245,131,33,0.16)', el: <IconClockHour4 size={15} stroke={2.4} color={OVERDUE_D} /> }
            : { bg: '#eef1f4', el: <IconClockHour4 size={15} stroke={2.2} color={MUTED} /> };

    const subParts = [row.route, row.dose].filter(Boolean);
    if (recorded && row.recorded_at) subParts.push(`${given ? 'given' : (CODE_LABELS[row.code] ?? 'recorded').toLowerCase()} ${row.recorded_at}`);
    else if (overdue) subParts.push(`due ${row.slot} · overdue`);
    else if (row.slot) subParts.push(`due ${row.slot}`);
    const subColor = (overdue && !recorded) ? OVERDUE_D : MUTED;

    const right = recorded
        ? <Badge radius={8} style={{ background: given ? 'rgba(136,177,63,0.16)' : 'rgba(184,85,122,0.14)', color: given ? GIVEN_D : REFUSE, fontWeight: 700 }}>{given ? 'Given' : (CODE_LABELS[row.code] ?? 'Recorded')}</Badge>
        : locked
            ? <Text fz={11.5} c={MUTED}>Round ended</Text>
            : actionable
                ? (
                    <Group gap={8} wrap="nowrap" justify={isSm ? 'flex-end' : 'flex-start'} style={{ width: isSm ? '100%' : undefined }}>
                        <Button size="compact-sm" variant="default" radius={9} onClick={() => onAct(row, 'Omitted')} style={{ fontFamily: JAKARTA, color: '#7a8590' }}>Omit</Button>
                        <Button size="compact-sm" variant="default" radius={9} onClick={() => onAct(row, 'Refused')} style={{ fontFamily: JAKARTA, color: REFUSE, borderColor: '#e6cdd6' }}>Refuse</Button>
                        <Button size="compact-sm" radius={9} onClick={() => onAct(row, 'Given')} style={{ fontFamily: JAKARTA, background: ACCENT, paddingInline: 18 }}>Give</Button>
                    </Group>
                )
                : <Badge radius={8} style={{ background: '#eef1f4', color: '#7a8590', fontWeight: 700 }}>Scheduled</Badge>;

    return (
        <Box style={{ padding: '12px 0', borderTop: '1px solid light-dark(#eef1f4, var(--mantine-color-dark-4))' }}>
            <Group gap={12} wrap="nowrap" align="center">
                <Box style={{ width: 30, height: 30, flex: '0 0 30px', display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: 8, background: icon.bg }}>{icon.el}</Box>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={6} wrap="nowrap">
                        <Text fz={13.5} fw={700} c={TXT} truncate>{row.medication_name}</Text>
                        {row.is_controlled && <Badge size="xs" radius="sm" color="grape" variant="light">CD</Badge>}
                    </Group>
                    <Text fz={12} c={subColor} fw={overdue && !recorded ? 600 : 400} truncate>{subParts.join(' · ') || '—'}</Text>
                    {recorded && (row.reason || row.notes || row.witnessed_by || row.recorded_by) && (
                        <Text fz={11} c={MUTED} mt={3} lh={1.35}>
                            {[row.reason, row.notes && `“${row.notes}”`, row.witnessed_by && `Witnessed by ${row.witnessed_by}`, row.recorded_by && `by ${row.recorded_by}`].filter(Boolean).join(' · ')}
                        </Text>
                    )}
                </Box>
                {!isSm && right}
            </Group>
            {isSm && <Box mt={8}>{right}</Box>}
        </Box>
    );
}

/* ------------------------------- Resident row (click-through to the give-meds page) ------------------------------- */
function ResidentRowV2({ resident, isNext, onOpen, isMobile, isSm, isFirst }) {
    const m = metrics(resident);
    const st = statusOf(m);
    const scheduled = (resident.rows ?? []).filter((r) => !r.as_required);
    const firstMed = scheduled[0];
    const allergies = cleanAllergies(resident.allergies);
    const pct = m.total ? (m.done / m.total) * 100 : 0;

    return (
        <Group gap={isSm ? 8 : 14} wrap="nowrap" align="center" px={isSm ? 2 : 6} py={isSm ? 12 : 14}
            style={{ cursor: 'pointer', borderTop: isFirst ? 'none' : '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))', borderRadius: 12 }}
            onClick={onOpen}
            onMouseEnter={(e) => { e.currentTarget.style.background = SOFT; }}
            onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}>
            <Group gap={13} wrap="nowrap" style={{ flex: '2 1 220px', minWidth: 0 }}>
                <Box style={{ width: 40, height: 40, flex: '0 0 40px', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 12.5, fontWeight: 700, background: m.complete ? '#e6f0e2' : '#dce4ef', color: m.complete ? GIVEN_D : ACCENT }}>
                    {initials(resident.name ?? '')}
                </Box>
                <Box style={{ minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz="sm" fw={700} c={TXT} truncate>{resident.name}</Text>
                        {isNext && <Badge radius={6} style={{ background: ACCENT, color: '#fff', fontSize: 9.5, fontWeight: 800, letterSpacing: 0.4 }}>NEXT</Badge>}
                        {allergies.length > 0 && <Tooltip label={`Allergies: ${allergies.join(', ')}`}><IconAlertTriangle size={13} color="#e0684f" /></Tooltip>}
                    </Group>
                    <Text fz="xs" c={MUTED} truncate>{[`Room ${resident.room || '—'}`, firstMed?.medication_name ?? (m.complete ? 'all doses given' : null)].filter(Boolean).join(' · ')}</Text>
                </Box>
            </Group>
            {!isSm && <Text fz="sm" fw={700} c={m.nextDue ? (m.overdue ? OVERDUE_D : INK) : '#b3bcc6'} style={{ width: isMobile ? 64 : 90, flexShrink: 0 }}>{m.nextDue || '—'}</Text>}
            {!isMobile && (
                <Group gap={9} wrap="nowrap" align="center" style={{ flex: '1 1 150px', minWidth: 110 }}>
                    <Box style={{ flex: 1, height: 5, borderRadius: 3, background: '#EEF1F4', overflow: 'hidden' }}><Box style={{ width: `${pct}%`, height: '100%', background: m.complete ? GIVEN : OVERDUE }} /></Box>
                    <Text fz="xs" fw={700} c={MUTED} style={{ width: 26 }}>{m.done}/{m.total}</Text>
                </Group>
            )}
            <Group gap={7} wrap="nowrap" justify={isSm ? 'center' : 'flex-start'} style={{ width: isSm ? 16 : 120, flexShrink: 0 }}>
                <Tooltip label={st.label} disabled={!isSm}><Box style={{ width: 8, height: 8, borderRadius: '50%', background: st.color, flexShrink: 0 }} /></Tooltip>
                {!isSm && <Text fz="xs" fw={600} style={{ color: st.color }} truncate>{st.label}</Text>}
            </Group>
            <IconChevronRight size={17} color="#c2cad2" style={{ flexShrink: 0 }} />
        </Group>
    );
}

/* ------------------------------- Page ------------------------------- */
export default function MedicationRoundV2({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {}, home }) {
    const isMobile = useMediaQuery('(max-width: 768px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    const page = usePage().props;
    const isManager = page?.auth?.user?.role === 'manager';
    const adminBy = `${page?.auth?.user?.name ?? 'User'} · ${isManager ? 'Care Manager' : 'Carer'}`;

    const [activeRound, setActiveRound] = useState(currentRound);
    const [expandedId, setExpandedId] = useState(null);
    const [paused, setPaused] = useState(false);
    const [tab, setTab] = useState('all');
    const [modalCtx, setModalCtx] = useState(null);

    const meta = rounds.find((r) => r.key === activeRound) ?? rounds[0] ?? { key: activeRound, label: 'Round', window: '' };
    const residents = grid[meta.key] ?? [];
    const roundClosed = Boolean(closures?.[meta.key]);
    const closure = closures?.[meta.key] ?? null;

    const roundCounts = useMemo(() => {
        const out = {};
        rounds.forEach((r) => {
            const sched = (grid[r.key] ?? []).flatMap((res) => (res.rows ?? []).filter((row) => !row.as_required));
            out[r.key] = { done: sched.filter((s) => s.code).length, total: sched.length };
        });
        return out;
    }, [rounds, grid]);
    const roundDone = roundCounts[meta.key]?.done ?? 0;
    const roundTotal = roundCounts[meta.key]?.total ?? 0;

    const nextResidentId = useMemo(() => {
        let best = null;
        residents.forEach((r) => (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.slot).forEach((row) => {
            if (!best || row.slot < best.slot) best = { id: r.client_id, slot: row.slot };
        }));
        return best?.id ?? null;
    }, [residents]);

    const withM = residents.map((r) => ({ r, m: metrics(r) }));
    const overdueCount = withM.filter((x) => x.m.overdue > 0).length;
    const doneCount = withM.filter((x) => x.m.complete).length;
    const shown = withM.filter((x) => tab === 'overdue' ? x.m.overdue > 0 : tab === 'done' ? x.m.complete : true).map((x) => x.r);

    const day = useMemo(() => {
        const sched = Object.values(grid).flat().flatMap((r) => (r.rows ?? []).filter((row) => !row.as_required));
        const given = sched.filter((r) => isGiven(r.code)).length;
        const overdue = sched.filter((r) => !r.code && r.status === 'overdue').length;
        const scheduled = sched.filter((r) => !r.code && r.status !== 'overdue').length;
        return { given, overdue, scheduled, total: given + overdue + scheduled };
    }, [grid]);
    const gDeg = day.total ? (day.given / day.total) * 360 : 0;
    const oDeg = day.total ? (day.overdue / day.total) * 360 : 0;

    const alert = useMemo(() => {
        let best = null;
        residents.forEach((r) => (r.rows ?? []).filter((row) => !row.as_required && !row.code && row.status === 'overdue' && row.slot).forEach((row) => {
            if (!best || row.slot < best.slot) best = { slot: row.slot, med: row.medication_name };
        }));
        return best;
    }, [residents]);

    const activity = useMemo(() => {
        const out = [];
        Object.values(grid).flat().forEach((r) => (r.rows ?? []).forEach((row) => {
            if (row.code && row.recorded_at) out.push({
                med: row.medication_name, prn: row.as_required, given: isGiven(row.code),
                label: row.as_required && isGiven(row.code) ? 'PRN given' : (isGiven(row.code) ? 'given' : (CODE_LABELS[row.code] ?? 'recorded')),
                at: row.recorded_at, by: row.recorded_by,
            });
        }));
        return out.sort((a, b) => String(b.at).localeCompare(String(a.at))).slice(0, 6);
    }, [grid]);

    const statusWord = roundClosed ? 'completed' : paused ? 'paused' : (roundDone === 0 ? 'not started' : roundDone >= roundTotal ? 'review' : 'in progress');
    const RoundIcon = ROUND_ICONS[meta.key] ?? IconClockHour4;

    const openModal = (row, residentName, residentRoom, outcome) => setModalCtx({ row, residentName, residentRoom, outcome });
    const endRound = () => router.post(`${ENDPOINT}/end-round`, { date, round: meta.key }, { preserveScroll: true });
    const reopenRound = () => router.post(`${ENDPOINT}/reopen-round`, { date, round: meta.key }, { preserveScroll: true });

    return (
        <AppShell title="Medication round">
            <Head title="Medication round">
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            </Head>
            <Box px={{ base: 0, sm: 10 }} pb={14} style={{ fontFamily: JAKARTA, '--mantine-font-family': JAKARTA, color: TXT, zoom: PAGE_ZOOM }}>
                {/* Round context strip */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb={18}>
                    <Group gap={14} wrap="nowrap">
                        <Box style={{ width: 46, height: 46, borderRadius: 13, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(58,124,165,0.14)' }}>
                            <RoundIcon size={22} stroke={2} color="#d98a3b" />
                        </Box>
                        <Box>
                            <Text fz={18} fw={800} c={TXT} lh={1.2}>{meta.label} round</Text>
                            <Text fz={12.5} c={MUTED}>{[meta.window, fmtDate(date), statusWord].filter(Boolean).join(' · ')}</Text>
                        </Box>
                    </Group>
                    <Group gap={12} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <Group gap={9} wrap="nowrap" style={{ padding: '9px 14px', borderRadius: 12, background: CARD_BG, boxShadow: '0 2px 6px rgba(20,50,80,0.05)' }}>
                            <Text fz={13} fw={600} c="#7a8590">Progress</Text>
                            <Text fz={13.5} fw={800} c={ACCENT}>{roundDone} / {roundTotal}</Text>
                        </Group>
                        {roundClosed
                            ? (isManager && <Button radius={999} variant="default" leftSection={<IconLockOpen size={15} />} onClick={reopenRound} style={{ fontFamily: JAKARTA }}>Re-open</Button>)
                            : (
                                <>
                                    <Button radius={999} variant="default" leftSection={paused ? <IconPlayerPlay size={14} /> : <IconPlayerPause size={14} />} onClick={() => setPaused((p) => !p)}
                                        style={{ fontFamily: JAKARTA, border: '1.5px solid #cddbe6', color: ACCENT, paddingInline: 18 }}>{paused ? 'Resume' : 'Pause'}</Button>
                                    <Button radius={999} leftSection={<IconLock size={15} />} onClick={endRound}
                                        style={{ fontFamily: JAKARTA, background: ACCENT, paddingInline: 22, boxShadow: '0 8px 18px rgba(58,124,165,0.28)' }}>End round</Button>
                                </>
                            )}
                    </Group>
                </Group>

                {/* Day-part selector */}
                <Box style={{ ...card, borderRadius: 14, boxShadow: '0 2px 6px rgba(20,50,80,0.05)', padding: 6, marginBottom: 18, overflowX: 'auto' }}>
                    <Group gap={8} wrap="nowrap" style={{ minWidth: 'max-content' }}>
                        {rounds.map((r) => <RoundTab key={r.key} round={r} active={r.key === activeRound}
                            done={roundCounts[r.key]?.done ?? 0} total={roundCounts[r.key]?.total ?? 0}
                            onClick={() => { setActiveRound(r.key); setExpandedId(null); setTab('all'); }} />)}
                    </Group>
                </Box>

                {/* Main + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 22, alignItems: 'flex-start' }}>
                    <Box style={{ ...card, flex: '3 1 460px', minWidth: 0, padding: isSm ? '16px 14px' : '24px 26px' }}>
                        <Group justify="space-between" align="center" pb={16} wrap="wrap" gap="sm">
                            <Text fz={17} fw={800} c={TXT}>Residents to give meds</Text>
                            <Group gap={6} style={{ background: 'light-dark(#F1F4F7, var(--mantine-color-dark-8))', borderRadius: 10, padding: 4 }}>
                                {[{ k: 'all', l: `All ${residents.length}` }, { k: 'overdue', l: `Overdue ${overdueCount}` }, { k: 'done', l: `Done ${doneCount}` }].map((t) => (
                                    <Box key={t.k} component="button" onClick={() => setTab(t.k)} style={{
                                        border: 'none', cursor: 'pointer', borderRadius: 7, padding: '6px 13px', fontSize: 12.5, fontFamily: JAKARTA,
                                        fontWeight: tab === t.k ? 700 : 600, background: tab === t.k ? CARD_BG : 'transparent', color: tab === t.k ? TXT : '#7a8590',
                                        boxShadow: tab === t.k ? '0 1px 2px rgba(19,35,63,0.08)' : 'none',
                                    }}>{t.l}</Box>
                                ))}
                            </Group>
                        </Group>
                        {!isMobile && (
                            <Group gap={14} wrap="nowrap" px={6} pb={12} style={{ borderBottom: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))' }}>
                                <Text fz={10.5} fw={700} tt="uppercase" c={MUTED} style={{ flex: '2 1 220px', letterSpacing: 0.6 }}>Resident</Text>
                                {!isSm && <Text fz={10.5} fw={700} tt="uppercase" c={MUTED} style={{ width: 90, letterSpacing: 0.6 }}>Next due</Text>}
                                <Text fz={10.5} fw={700} tt="uppercase" c={MUTED} style={{ flex: '1 1 150px', letterSpacing: 0.6 }}>Progress</Text>
                                <Text fz={10.5} fw={700} tt="uppercase" c={MUTED} style={{ width: 120, letterSpacing: 0.6 }}>Status</Text>
                                <Box style={{ width: 17 }} />
                            </Group>
                        )}
                        {shown.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py={48}>No residents in this view.</Text>
                            : shown.map((r, idx) => <ResidentRowV2 key={r.client_id} resident={r} isMobile={isMobile} isSm={isSm} isNext={r.client_id === nextResidentId} isFirst={idx === 0}
                                onOpen={() => router.get(`${ENDPOINT}/resident/${r.client_id}`, { date, round: meta.key }, { preserveScroll: true })} />)}
                    </Box>

                    {/* Right rail */}
                    <Stack gap={28} style={{ flex: '1 1 320px', minWidth: 0, maxWidth: isMobile ? undefined : 350 }}>
                        {/* Today */}
                        <Box style={{ ...card, padding: isSm ? '18px 16px' : '22px 24px' }}>
                            <Text fz={17} fw={700} c={TXT} mb={16}>Today</Text>
                            <Group gap={20} wrap="nowrap" align="center">
                                <Box style={{ position: 'relative', width: 92, height: 92, flex: '0 0 92px', borderRadius: '50%', background: day.total ? `conic-gradient(${GIVEN} 0deg ${gDeg}deg, ${OVERDUE} ${gDeg}deg ${gDeg + oDeg}deg, #E1E7ED ${gDeg + oDeg}deg 360deg)` : '#E1E7ED' }}>
                                    <Box style={{ position: 'absolute', inset: 12, borderRadius: '50%', background: CARD_BG, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
                                        <Text fz={20} fw={800} c={TXT} lh={1}>{day.given}/{day.total}</Text>
                                        <Text fz={10} c={MUTED} mt={2}>given</Text>
                                    </Box>
                                </Box>
                                <Stack gap={9} style={{ flex: 1 }}>
                                    {[{ c: GIVEN, l: 'Given', v: day.given }, { c: OVERDUE, l: 'Overdue', v: day.overdue }, { c: SCHED, l: 'Scheduled', v: day.scheduled }].map((s) => (
                                        <Group key={s.l} justify="space-between" wrap="nowrap">
                                            <Group gap={8} wrap="nowrap"><Box style={{ width: 8, height: 8, borderRadius: '50%', background: s.c }} /><Text fz={12.5} c="#5F6B76">{s.l}</Text></Group>
                                            <Text fz={13} fw={700} c={TXT}>{s.v}</Text>
                                        </Group>
                                    ))}
                                </Stack>
                            </Group>
                        </Box>

                        {/* Alert */}
                        {alert && !roundClosed && (
                            <Box style={{ ...card, border: '1px solid #F4D2B4', padding: isSm ? '18px 16px' : '20px 22px' }}>
                                <Group gap={9} mb={10} wrap="nowrap">
                                    <Box style={{ width: 32, height: 32, borderRadius: 9, display: 'flex', alignItems: 'center', justifyContent: 'center', background: 'rgba(245,131,33,0.14)' }}><IconAlertTriangle size={17} color={OVERDUE} /></Box>
                                    <Text fz={16} fw={700} c={TXT}>Alert</Text>
                                </Group>
                                <Text fz={12.5} c="#7a8590" lh={1.5} mb={13}><b style={{ color: TXT }}>{alert.med}</b> was due at {alert.slot} and is now overdue. Record or omit before the round ends.</Text>
                                <Button component={Link} href="/frontend2/missed-doses" fullWidth radius={12}
                                    rightSection={<IconChevronRight size={15} />} style={{ fontFamily: JAKARTA, background: 'rgba(245,131,33,0.12)', color: OVERDUE_D }}>Resolve now</Button>
                            </Box>
                        )}

                        {/* Recent activity */}
                        <Box style={{ ...card, padding: isSm ? '18px 16px' : '22px 24px' }}>
                            <Text fz={17} fw={700} c={TXT} mb={14}>Recent activity</Text>
                            {activity.length === 0
                                ? <Text fz="sm" c="dimmed">No doses recorded yet today.</Text>
                                : activity.map((a, i) => (
                                    <Group key={i} gap={12} wrap="nowrap" align="flex-start" style={{ padding: '10px 0', borderBottom: i < activity.length - 1 ? '1px solid light-dark(#F1F4F7, var(--mantine-color-dark-4))' : 'none' }}>
                                        <Box style={{ marginTop: 5, width: 8, height: 8, flex: '0 0 8px', borderRadius: '50%', background: a.prn ? PRN_C : a.given ? GIVEN : OVERDUE }} />
                                        <Box style={{ flex: 1, minWidth: 0 }}>
                                            <Text fz={13} fw={600} c={TXT} truncate>{a.med} {a.label}</Text>
                                            <Text fz={11.5} c={MUTED}>{a.at}{a.by ? ` · by ${a.by}` : ''}</Text>
                                        </Box>
                                    </Group>
                                ))}
                        </Box>
                    </Stack>
                </Box>

                {closure?.by && <Text fz="xs" c="dimmed" mt="md">Round ended by {closure.by}{closure.at ? ` at ${closure.at}` : ''}.</Text>}
            </Box>

            {modalCtx && <AdminModal ctx={modalCtx} date={date} adminBy={adminBy} onClose={() => setModalCtx(null)} />}
        </AppShell>
    );
}
