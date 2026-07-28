import { useState, useEffect } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { notifications } from '@mantine/notifications';
import {
    Box, Group, Text, Badge, Avatar, Button, ActionIcon, ThemeIcon,
    SegmentedControl, Tooltip, Progress, Modal, SimpleGrid,
} from '@mantine/core';
import {
    IconChevronLeft, IconChevronRight, IconCheck, IconCircleX, IconBan, IconPill,
    IconAlertTriangle, IconLock, IconLockOpen, IconUser, IconArrowRight,
    IconShieldLock, IconPlayerPlay, IconInfoCircle,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import RecordDoseModal from '@frontend/features/medications/RecordDoseModal';
import { CODE_LABELS, isGivenCode } from '@frontend/lib/medicationCodes';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { useRole } from '@frontend/lib/role';

const ENDPOINT = '/frontend2/medication-2/round';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const PURPLE = 'light-dark(#6455D0, #8E7FEC)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';

/** Age in whole years from an ISO date-of-birth. */
function ageFromDob(dob) {
    if (!dob) return null;
    const d = new Date(dob);
    if (Number.isNaN(d.getTime())) return null;
    const now = new Date();
    let a = now.getFullYear() - d.getFullYear();
    const m = now.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a -= 1;
    return a >= 0 && a < 130 ? a : null;
}

const NO_ALLERGY = /^(no|none|nil|n\/?a|na|nkda|none known|no known allergies|no allergies|unknown)$/i;
const cleanAllergies = (list) => (list ?? []).filter((a) => a && !NO_ALLERGY.test(String(a).trim()));

/** "weighed today" / "weighed 4 days ago" from the derived weight's age. */
function weightAgeLabel(w) {
    const d = w?.age_days;
    if (d == null) return 'weighed —';
    if (d <= 0) return 'weighed today';
    if (d === 1) return 'weighed yesterday';
    if (d < 42) return `weighed ${d} days ago`;
    const weeks = Math.round(d / 7);
    if (d < 365) return `weighed ${weeks} weeks ago`;
    return `weighed ${Math.round(d / 30)} months ago`;
}

/** One medicine line inside the focused resident. */
function MedLine({ row, locked, onGiven, onOutcome }) {
    const recorded = Boolean(row.code);
    const prn = row.as_required ? row.prn : null;
    const prnBlocked = Boolean(prn?.blocked);

    const tone = recorded
        ? (isGivenCode(row.code) ? TEAL : (row.code === 'R' ? RED : ORANGE))
        : row.status === 'overdue' ? ORANGE : row.as_required ? PURPLE : TEAL;

    const slotLabel = row.as_required ? 'PRN' : (row.slot || '—');

    return (
        <Box py={12} style={{ borderTop: `1px solid ${HAIR}` }}>
            <Group gap="sm" wrap="nowrap" align="flex-start">
                <Text fz={12} fw={800} c={tone} w={44} style={{ flexShrink: 0, fontVariantNumeric: 'tabular-nums' }}>{slotLabel}</Text>
                <ThemeIcon variant="light" color={row.is_controlled ? 'grape' : 'teal'} size={30} radius="md" style={{ flexShrink: 0 }}>
                    <IconPill size={16} />
                </ThemeIcon>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz="sm" fw={650} c={TXT} style={{ letterSpacing: '-0.01em' }}>{row.medication_name}</Text>
                        {row.is_controlled && <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>}
                    </Group>
                    {row.dose && <Text fz={13} fw={700} c={TEAL} mt={1}>Give {row.dose}</Text>}
                    <Text fz={12} c={MUTED} mt={1}>
                        {[row.strength, row.route, row.indication && `For ${row.indication}`].filter(Boolean).join(' · ') || '—'}
                    </Text>

                    {/* Administration instruction — ONLY a genuine directive (PRN details today),
                        clearly labelled so it isn't confused with the indication above. A dedicated
                        "do not crush / with food" field does not exist yet (review C1/HAZ-22, pending
                        pharmacist + CSO) — so we do NOT dress the indication up as a directive, and
                        the accent is neutral (teal), not warning-orange. */}
                    {row.instruction && (
                        <Group gap={6} wrap="nowrap" align="flex-start" mt={5}
                            style={{ background: SOFT, border: `1px solid ${HAIR}`, borderLeft: `2.5px solid ${TEAL}`, borderRadius: 8, padding: '5px 9px' }}>
                            <IconInfoCircle size={13} color="var(--mantine-color-teal-6)" style={{ flexShrink: 0, marginTop: 1 }} />
                            <Text fz={12} c={TXT} style={{ lineHeight: 1.35 }}>
                                <Text component="span" fz={10.5} fw={800} c={MUTED} tt="uppercase" style={{ letterSpacing: '0.04em' }}>Instruction </Text>
                                {row.instruction}
                            </Text>
                        </Group>
                    )}

                    {/* Stock, when low — surfaced so a carer knows before recording. */}
                    {row.low_stock && row.stock != null && (
                        <Text fz={11.5} c={ORANGE} fw={650} mt={2}>Low stock: {row.stock}{row.unit ? ` ${row.unit}` : ''} left</Text>
                    )}

                    {/* PRN state — visible before the tap (audit IM-08). */}
                    {!recorded && prn && (
                        <Text fz={11.5} mt={3} fw={prnBlocked ? 650 : 400} c={prnBlocked ? RED : MUTED} style={{ fontVariantNumeric: 'tabular-nums' }}>
                            {[
                                prn.max_daily != null && `${prn.given_today ?? 0}/${prn.max_daily} today`,
                                prn.block_reason,
                                !prnBlocked && prn.next_available && `next due ${prn.next_available}`,
                            ].filter(Boolean).join(' · ')}
                        </Text>
                    )}

                    {/* Recorded outcome + its trail. */}
                    {recorded && (
                        <Group gap={6} mt={4} wrap="wrap">
                            <Badge variant="light" radius="sm" style={{ background: `color-mix(in srgb, ${tone} 15%, transparent)`, color: tone }}>
                                {CODE_LABELS[row.code] ?? row.code}
                            </Badge>
                            {(row.reason || row.notes || row.witnessed_by || row.recorded_by) && (
                                <Text fz={11.5} c={MUTED}>
                                    {[
                                        row.reason,
                                        row.notes && `“${row.notes}”`,
                                        row.witnessed_by && `witness ${row.witnessed_by}`,
                                        row.recorded_by && `by ${row.recorded_by}`,
                                        row.recorded_at,
                                    ].filter(Boolean).join(' · ')}
                                </Text>
                            )}
                        </Group>
                    )}
                </Box>
            </Group>

            {/* Actions — only when not recorded and the round is open. */}
            {!recorded && !locked && (
                <Group gap={8} mt={10} pl={56} wrap="nowrap">
                    <Tooltip label={prnBlocked ? (prn.block_reason || 'Not due yet') : 'Record as given'} disabled={!prnBlocked} withArrow>
                        <Button size="xs" radius="xl" color="teal" leftSection={<IconCheck size={14} />}
                            disabled={prnBlocked} onClick={() => onGiven(row)}>Given</Button>
                    </Tooltip>
                    <Tooltip label="Refused" withArrow>
                        <ActionIcon size="lg" radius="xl" variant="light" color="red" onClick={() => onOutcome(row, 'R')}><IconCircleX size={16} /></ActionIcon>
                    </Tooltip>
                    <Tooltip label="Other outcome" withArrow>
                        <ActionIcon size="lg" radius="xl" variant="light" color="orange" onClick={() => onOutcome(row, 'O')}><IconBan size={16} /></ActionIcon>
                    </Tooltip>
                </Group>
            )}
            {locked && !recorded && <Text fz="xs" c={MUTED} mt={8} pl={56}>Round ended — recording locked</Text>}
        </Box>
    );
}

/**
 * Round-wide outcome tally for the end-of-round summary. Computed from the same rows
 * the roster and focused view read, so the summary can never disagree with them.
 * `outstanding` = scheduled doses with no outcome — on lock these are left unrecorded
 * for this round (the server writes only a closure marker, not per-dose outcomes —
 * review C2/HAZ-10). Only scheduled doses are tallied: a given PRN dose does not carry
 * a `code` on its grid row (BuildsMedicationRound: null slot ⇒ null admin), so CD/PRN
 * "given" cannot be counted here honestly and is deliberately not shown.
 */
function roundOutcomeSummary(residents) {
    let given = 0, refused = 0, notGiven = 0, outstanding = 0;
    for (const r of residents) {
        for (const row of (r.rows ?? [])) {
            if (row.code) {
                if (isGivenCode(row.code)) given += 1;
                else if (row.code === 'R') refused += 1;
                else notGiven += 1;
            } else if (!row.as_required) {
                outstanding += 1;
            }
        }
    }
    return { given, refused, notGiven, outstanding };
}

/** A scheduled dose whose time hasn't arrived yet (owner: confirm before recording early). */
const isFutureSlot = (row) => !row.as_required && (row.status === 'upcoming' || row.status === 'later');
/** Done = every scheduled (non-PRN) dose has an outcome. PRN is never "outstanding" for completion. */
const isDoneResident = (r) => (r.rows ?? []).filter((x) => !x.as_required).every((x) => x.code);

/**
 * Per-resident rollup for the overview roster. Computed here in ONE place so the roster
 * and the focused view can never disagree (the audit's HAZ-17 was 13 pages each deriving
 * "given" their own way). Display-only; the write decisions stay server-side.
 */
function residentStatus(r) {
    const sched = (r.rows ?? []).filter((x) => !x.as_required);
    const done = sched.filter((x) => x.code).length;
    const total = sched.length;
    const overdue = sched.some((x) => !x.code && x.status === 'overdue');
    const anyRecorded = (r.rows ?? []).some((x) => x.code);
    let state = 'not_started';
    if (total > 0 && done >= total) state = 'complete';
    else if (overdue) state = 'overdue';
    else if (anyRecorded) state = 'in_progress';
    return {
        state, done, total, overdue,
        hasAllergy: cleanAllergies(r.allergies).length > 0,
        hasCd: (r.rows ?? []).some((x) => x.is_controlled && !x.code),          // CD still to give
        hasHighRisk: (r.risk_flags ?? []).some((x) => (x?.level ?? '') === 'high'),
    };
}

/** One roster row — read-only; tapping opens that resident in the focused flow. */
function RosterRow({ resident, isNext, onOpen }) {
    const s = residentStatus(resident);
    const age = ageFromDob(resident.dob);
    const statusEl = s.state === 'complete'
        ? <Group gap={4} wrap="nowrap"><IconCheck size={13} color="var(--mantine-color-teal-6)" /><Text fz={12} fw={700} c={TEAL}>Complete</Text></Group>
        : s.state === 'overdue'
            ? <Group gap={4} wrap="nowrap"><IconAlertTriangle size={13} color="var(--mantine-color-orange-6)" /><Text fz={12} fw={700} c={ORANGE}>Overdue</Text></Group>
            : <Text fz={12} c={MUTED} style={{ fontVariantNumeric: 'tabular-nums' }}>{s.done}/{s.total} given</Text>;

    return (
        <Box component="button" onClick={() => onOpen(resident)}
            style={{
                display: 'block', width: '100%', textAlign: 'left', cursor: 'pointer', background: 'transparent',
                border: 0, borderLeft: `3px solid ${isNext ? TEAL : 'transparent'}`,
                borderTop: `1px solid ${HAIR}`, padding: '12px 12px 12px 13px', color: 'inherit',
            }}>
            <Group gap="sm" wrap="nowrap" align="flex-start">
                <Avatar src={resident.photo || undefined} size={38} radius="md" color={avatarColor(resident.name)}>
                    {initials(resident.name)}
                </Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={7} wrap="nowrap">
                        <Text fz={14} fw={700} c={TXT} truncate>{resident.name}</Text>
                        {age != null && <Text fz={12} c={MUTED} style={{ flexShrink: 0 }}>{age}y</Text>}
                        {isNext && <Badge size="xs" variant="light" color="teal" radius="sm" style={{ flexShrink: 0 }}>Up next</Badge>}
                    </Group>
                    <Group gap={10} mt={3} wrap="wrap" align="center">
                        {statusEl}
                        {s.hasAllergy && <Badge size="xs" color="red" variant="light" radius="sm" leftSection={<IconAlertTriangle size={10} />}>Allergy</Badge>}
                        {s.hasCd && <Badge size="xs" color="grape" variant="light" radius="sm" leftSection={<IconShieldLock size={10} />}>CD due</Badge>}
                        {s.hasHighRisk && <Badge size="xs" color="orange" variant="light" radius="sm">High risk</Badge>}
                    </Group>
                </Box>
                <IconChevronRight size={16} color="var(--mantine-color-dimmed)" style={{ flexShrink: 0, marginTop: 6 }} />
            </Group>
        </Box>
    );
}

export default function Medication2Round({ rounds = [], grid = {}, date, currentRound = 'morning', closures = {}, home }) {
    const [activeRound, setActiveRound] = useState(currentRound);
    const [view, setView] = useState('overview');         // 'overview' | 'focused'
    const [focus, setFocus] = useState(0);
    const [recordRow, setRecordRow] = useState(null);
    const [recordCode, setRecordCode] = useState('A');
    const [modalOpen, setModalOpen] = useState(false);
    const [earlyRow, setEarlyRow] = useState(null);        // pending "record early?" confirm
    const [endOpen, setEndOpen] = useState(false);         // "end round" confirm
    const isManager = useRole() === 'manager';

    const residents = grid[activeRound] ?? [];
    const roundClosed = Boolean(closures?.[activeRound]);
    const closure = closures?.[activeRound] ?? null;
    const meta = rounds.find((r) => r.key === activeRound) ?? { label: activeRound, window: '' };

    // Round change resets to the overview at the top.
    useEffect(() => { setFocus(0); setView('overview'); }, [activeRound]);

    const idx = Math.min(focus, Math.max(0, residents.length - 1));
    const resident = residents[idx] ?? null;

    const doneCount = residents.filter(isDoneResident).length;
    const firstOutstanding = residents.findIndex((r) => !isDoneResident(r));
    const nextResident = firstOutstanding >= 0 ? residents[firstOutstanding] : null;
    const outstandingDoses = residents.reduce((n, r) => n + (r.rows ?? []).filter((x) => !x.as_required && !x.code).length, 0);
    const overdueResidents = residents.filter((r) => residentStatus(r).overdue).length;
    const allergyResidents = residents.filter((r) => residentStatus(r).hasAllergy).length;
    const cdResidents = residents.filter((r) => residentStatus(r).hasCd).length;
    const summary = roundOutcomeSummary(residents);   // end-of-round tally (used when closed)

    // Surface server flash + one-tap errors (audit CR-07).
    const flash = usePage().props?.flash;
    useEffect(() => {
        if (flash?.error) notifications.show({ color: 'red', title: 'Not recorded', message: flash.error, autoClose: 6000 });
        else if (flash?.success) notifications.show({ color: 'teal', message: flash.success });
    }, [flash?.error, flash?.success]);

    // --- navigation ---
    const startRound = () => { setFocus(firstOutstanding >= 0 ? firstOutstanding : 0); setView('focused'); };
    const openResident = (r) => { const i = residents.indexOf(r); setFocus(i >= 0 ? i : 0); setView('focused'); };
    const goNext = () => setFocus((f) => Math.min(f + 1, residents.length - 1));
    const goPrev = () => setFocus((f) => Math.max(f - 1, 0));

    // --- recording ---
    const openRecord = (row, code) => { setRecordRow(row); setRecordCode(code); setModalOpen(true); };
    const doGive = (row) => {
        // One-tap only for a plain scheduled, non-CD dose. CD (witness) and PRN go via the
        // modal so the extra fields are captured.
        if (!row.is_controlled && !row.as_required && row.slot) {
            router.post(`${ENDPOINT}/record`, { mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot, code: 'A', dose_given: row.dose ?? '' }, {
                preserveScroll: true,
                preserveState: true,
                onError: (errors) => notifications.show({ color: 'red', title: 'Not recorded', message: Object.values(errors ?? {})[0] || 'This dose could not be recorded.', autoClose: 6000 }),
            });
        } else {
            openRecord(row, 'A');
        }
    };
    const giveDose = (row) => {
        if (roundClosed) return;
        // Owner: a dose whose scheduled time hasn't arrived is confirmed, not blocked —
        // early administration legitimately happens. Refusing/other outcomes are never early.
        if (isFutureSlot(row)) { setEarlyRow(row); return; }
        doGive(row);
    };
    const outcomeDose = (row, code) => { if (!roundClosed) openRecord(row, code); };

    // --- end / reopen ---
    const confirmEnd = () => {
        router.post(`${ENDPOINT}/end`, { date, round: activeRound, outstanding: outstandingDoses }, {
            preserveScroll: true, onSuccess: () => setEndOpen(false),
        });
    };
    const reopenRound = () => router.post(`${ENDPOINT}/reopen`, { date, round: activeRound }, { preserveScroll: true });

    const age = resident ? ageFromDob(resident.dob) : null;
    const weight = resident?.weight_current ?? null;
    const allergies = resident ? cleanAllergies(resident.allergies) : [];
    const risks = resident?.risk_flags ?? [];

    return (
        <AppShell title="Medication round" section="Medication 2">
            <Head title="Medication round — Medication 2" />
            <Box maw={view === 'overview' ? 780 : 620} mx="auto">
                {/* Header + round selector (both views) */}
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Group gap="xs" wrap="nowrap">
                        {view === 'focused' && (
                            <ActionIcon variant="subtle" color="gray" size="lg" radius="md" onClick={() => setView('overview')} aria-label="Back to round overview">
                                <IconChevronLeft size={18} />
                            </ActionIcon>
                        )}
                        <Box>
                            <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>{meta.label} round</Text>
                            <Text fz={13} c={MUTED}>{home ? `${home} · ` : ''}{meta.window}</Text>
                        </Box>
                    </Group>
                    <SegmentedControl
                        size="xs" radius="xl" value={activeRound} onChange={setActiveRound}
                        data={rounds.map((r) => ({ label: r.label, value: r.key }))}
                    />
                </Group>

                {/* Progress + aggregate warnings (both views) */}
                {residents.length > 0 && (
                    <Box mb="lg">
                        <Group justify="space-between" mb={6}>
                            <Text fz={12.5} fw={700} c={MUTED}>
                                {view === 'focused' ? `Resident ${idx + 1} of ${residents.length}` : `${doneCount} of ${residents.length} residents complete`}
                            </Text>
                            <Text fz={12.5} fw={700} c={doneCount === residents.length ? TEAL : MUTED} style={{ fontVariantNumeric: 'tabular-nums' }}>
                                {doneCount} / {residents.length}
                            </Text>
                        </Group>
                        <Progress value={residents.length ? (doneCount / residents.length) * 100 : 0} size="sm" radius="xl" color={doneCount === residents.length ? 'teal' : 'teal'} />
                        {view === 'overview' && (overdueResidents > 0 || allergyResidents > 0 || cdResidents > 0) && (
                            <Text fz={12} c={MUTED} mt={7}>
                                {[
                                    overdueResidents > 0 && `⚠ ${overdueResidents} overdue`,
                                    allergyResidents > 0 && `${allergyResidents} with allergies`,
                                    cdResidents > 0 && `${cdResidents} CD due`,
                                ].filter(Boolean).join(' · ')}
                            </Text>
                        )}
                    </Box>
                )}

                {/* Empty state */}
                {residents.length === 0 && (
                    <Box py={60} ta="center">
                        <ThemeIcon variant="light" color="gray" size={52} radius="xl" mx="auto" mb="sm"><IconUser size={26} /></ThemeIcon>
                        <Text fz="md" fw={700} c={TXT}>No residents in this round</Text>
                        <Text fz="sm" c={MUTED}>Nothing is scheduled for the {meta.label.toLowerCase()} round.</Text>
                    </Box>
                )}

                {/* ===================== OVERVIEW ===================== */}
                {view === 'overview' && residents.length > 0 && (
                    <>
                        {/* Start / Resume */}
                        {!roundClosed && nextResident && (
                            <Button fullWidth size="md" radius="md" color="teal" mb="md"
                                leftSection={<IconPlayerPlay size={17} />} onClick={startRound}>
                                <Box>
                                    <Text fz={14} fw={750}>{doneCount > 0 ? 'Resume round' : 'Start round'}</Text>
                                    <Text fz={11.5} fw={500} style={{ opacity: 0.9 }}>Continues with {nextResident.name}</Text>
                                </Box>
                            </Button>
                        )}
                        {!roundClosed && !nextResident && (
                            <Box mb="md" p="sm" style={{ background: `color-mix(in srgb, ${TEAL} 10%, transparent)`, border: `1px solid ${HAIR}`, borderRadius: 12 }}>
                                <Group gap={8}><IconCheck size={16} color="var(--mantine-color-teal-6)" />
                                    <Text fz={13.5} fw={700} c={TEAL}>All scheduled doses recorded for this round.</Text></Group>
                            </Box>
                        )}
                        {roundClosed && (
                            <Box mb="md" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
                                <Group justify="space-between" wrap="wrap" gap="xs" p="sm" style={{ background: SOFT, borderBottom: `1px solid ${HAIR}` }}>
                                    <Group gap={7} wrap="nowrap">
                                        <IconLock size={15} color="var(--mantine-color-dimmed)" />
                                        <Box>
                                            <Text fz={13.5} fw={700} c={TXT}>Round ended — recording locked</Text>
                                            {(closure?.by || closure?.at) && (
                                                <Text fz={12} c={MUTED}>
                                                    {[closure?.by && `by ${closure.by}`, closure?.at && closure.at].filter(Boolean).join(' · ')}
                                                </Text>
                                            )}
                                        </Box>
                                    </Group>
                                    {isManager && <Button size="xs" radius="xl" variant="default" leftSection={<IconLockOpen size={14} />} onClick={reopenRound}>Re-open</Button>}
                                </Group>

                                <SimpleGrid cols={{ base: 2, xs: 4 }} spacing="xs" p="sm">
                                    {[
                                        { label: 'Given', value: summary.given, tone: TEAL },
                                        { label: 'Refused', value: summary.refused, tone: RED },
                                        { label: 'Not given', value: summary.notGiven, tone: ORANGE },
                                        { label: 'Outstanding', value: summary.outstanding, tone: summary.outstanding > 0 ? ORANGE : MUTED },
                                    ].map((t) => (
                                        <Box key={t.label} ta="center" py={10} style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 12 }}>
                                            <Text fz={24} fw={800} c={t.tone} style={{ fontVariantNumeric: 'tabular-nums', lineHeight: 1.1 }}>{t.value}</Text>
                                            <Text fz={11} fw={600} c={MUTED} mt={2}>{t.label}</Text>
                                        </Box>
                                    ))}
                                </SimpleGrid>

                                {summary.outstanding > 0 && (
                                    <Box px="sm" pb="sm">
                                        <Text fz={12} c={ORANGE} fw={600}>
                                            {summary.outstanding} scheduled dose{summary.outstanding === 1 ? '' : 's'} left unrecorded when the round was ended.
                                        </Text>
                                    </Box>
                                )}
                            </Box>
                        )}

                        {/* Roster */}
                        <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
                            {residents.map((r) => (
                                <RosterRow key={r.client_id} resident={r} isNext={!roundClosed && r === nextResident} onOpen={openResident} />
                            ))}
                        </Box>

                        {/* End round */}
                        {!roundClosed && (
                            <Group justify="flex-end" mt="md">
                                <Button variant="light" color="gray" radius="xl" leftSection={<IconLock size={15} />} onClick={() => setEndOpen(true)}>
                                    End round
                                </Button>
                            </Group>
                        )}
                    </>
                )}

                {/* ===================== FOCUSED ===================== */}
                {view === 'focused' && resident && (
                    <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 18, boxShadow: '0 1px 2px rgba(19,35,63,0.05), 0 12px 30px rgba(19,35,63,0.08)', overflow: 'hidden' }}>
                        {/* Identity banner — pinned. */}
                        <Box p="md" style={{ background: SOFT, borderBottom: `1px solid ${HAIR}` }}>
                            <Group gap="sm" wrap="nowrap" align="center">
                                <Avatar src={resident.photo || undefined} size={52} radius="md" color={avatarColor(resident.name)}>
                                    {initials(resident.name)}
                                </Avatar>
                                <Box style={{ flex: 1, minWidth: 0 }}>
                                    <Text fz={17} fw={750} c={TXT} truncate style={{ letterSpacing: '-0.01em' }}>{resident.name}</Text>
                                    <Group gap={6} wrap="wrap" align="center">
                                        {age != null && <Text fz={12.5} c={MUTED}><b style={{ color: 'var(--mantine-color-text)' }}>{age}y</b></Text>}
                                        {weight && (
                                            <Text fz={12.5} c={weight.is_stale ? ORANGE : MUTED} fw={weight.is_stale ? 650 : 400}>
                                                · {weight.kg} kg · {weightAgeLabel(weight)}{weight.is_stale ? ' ⚠' : ''}
                                            </Text>
                                        )}
                                        {resident.room && <Text fz={12.5} c={MUTED}>· Room {resident.room}</Text>}
                                        {resident.nhs && <Text fz={12.5} c={MUTED}>· NHS {resident.nhs}</Text>}
                                    </Group>
                                </Box>
                            </Group>
                            {(allergies.length > 0 || risks.length > 0) && (
                                <Group gap={7} mt={10} wrap="wrap">
                                    {allergies.map((a, i) => (
                                        <Badge key={`a${i}`} color="red" variant="light" radius="sm" leftSection={<IconAlertTriangle size={11} />}>{a}</Badge>
                                    ))}
                                    {risks.map((r, i) => (
                                        <Badge key={`r${i}`} color={r?.level === 'high' ? 'red' : 'orange'} variant="light" radius="sm">
                                            {typeof r === 'string' ? r : (r?.label ?? '')}
                                        </Badge>
                                    ))}
                                </Group>
                            )}
                        </Box>

                        <Box px="md" pb="xs">
                            {(resident.rows ?? []).length === 0 && (
                                <Text fz="sm" c={MUTED} py="lg" ta="center">No medications in this round.</Text>
                            )}
                            {(resident.rows ?? []).map((row, i) => (
                                <MedLine key={`${row.mar_sheet_id}-${row.slot ?? 'prn'}-${i}`} row={row} locked={roundClosed} onGiven={giveDose} onOutcome={outcomeDose} />
                            ))}
                        </Box>

                        <Group justify="space-between" p="md" style={{ borderTop: `1px solid ${HAIR}` }}>
                            <Button variant="subtle" color="gray" radius="xl" leftSection={<IconChevronLeft size={16} />} onClick={goPrev} disabled={idx === 0}>
                                Previous
                            </Button>
                            {idx >= residents.length - 1
                                ? <Button radius="xl" color="teal" variant="light" onClick={() => setView('overview')}>Back to overview</Button>
                                : <Button radius="xl" color="dark" rightSection={<IconArrowRight size={16} />} onClick={goNext}>Next resident</Button>}
                        </Group>
                    </Box>
                )}
            </Box>

            <RecordDoseModal
                opened={modalOpen}
                onClose={() => setModalOpen(false)}
                row={recordRow}
                resident={resident}
                allergies={allergies}
                date={date}
                presetCode={recordCode}
                endpoint={`${ENDPOINT}/record`}
            />

            {/* Record-early confirm (owner: soft confirm, not a hard block). */}
            <Modal opened={Boolean(earlyRow)} onClose={() => setEarlyRow(null)} title="Record this dose early?" centered radius="md" size="sm">
                <Text fz="sm" c={MUTED} mb="md">
                    {earlyRow?.medication_name} isn’t due until <b style={{ color: 'var(--mantine-color-text)' }}>{earlyRow?.slot}</b>. Recording it now marks it as given before its scheduled time.
                </Text>
                <Group justify="flex-end" gap="sm">
                    <Button variant="default" radius="xl" onClick={() => setEarlyRow(null)}>Cancel</Button>
                    <Button color="teal" radius="xl" onClick={() => { const r = earlyRow; setEarlyRow(null); doGive(r); }}>Record early</Button>
                </Group>
            </Modal>

            {/* End-round confirm (owner: confirm-and-allow when doses outstanding). */}
            <Modal opened={endOpen} onClose={() => setEndOpen(false)} title="End this round?" centered radius="md" size="sm">
                {outstandingDoses > 0 ? (
                    <Text fz="sm" c={MUTED} mb="md">
                        <b style={{ color: 'var(--mantine-color-orange-7)' }}>{outstandingDoses} scheduled dose{outstandingDoses === 1 ? '' : 's'} still outstanding.</b> Ending the round locks recording — these doses stay <b style={{ color: 'var(--mantine-color-text)' }}>unrecorded</b> for this round (they are not marked given, and no reason is captured). A manager can re-open the round to record them.
                    </Text>
                ) : (
                    <Text fz="sm" c={MUTED} mb="md">All scheduled doses are recorded. Ending the round locks it; a manager can re-open it if needed.</Text>
                )}
                <Group justify="flex-end" gap="sm">
                    <Button variant="default" radius="xl" onClick={() => setEndOpen(false)}>Cancel</Button>
                    <Button color={outstandingDoses > 0 ? 'orange' : 'teal'} radius="xl" onClick={confirmEnd}>End round</Button>
                </Group>
            </Modal>
        </AppShell>
    );
}
