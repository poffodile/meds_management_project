import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Box, Group, Text, Button, Select, Textarea, TextInput, ActionIcon } from '@mantine/core';
import { IconX, IconAlertTriangle, IconClock, IconPencil, IconBell } from '@tabler/icons-react';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import { REFUSAL_REASONS, OMISSION_REASONS } from '@frontend/lib/medicationCodes';

/**
 * AdministerModal — the Care One OS "Administer Medication" modal (ported from the
 * administer-modal.html mockup) combined with the live record flow used by the
 * frontend2 Medication Round. Posts to `${endpoint}/record` (applyRecord).
 *
 * The mockup's 8 outcomes are richer than the backend's 6 MAR codes, so each maps
 * to the nearest code and the precise outcome label is preserved in `reason`
 * (see #16/#17 — extend the code set once the meds dictionary lands).
 */
const FONT = "'Hanken Grotesk', -apple-system, BlinkMacSystemFont, system-ui, sans-serif";
const T = {
    ink: '#13233F', slate: '#3a4858', muted: '#69727C', faint: '#98A1AB', hair: '#A2AAB4',
    line: '#E4E8EC', line2: '#F0F2F4', wash2: '#FBFBFC', green: '#1F8A5B', amber: '#B5601A',
    warnBg: '#F8F4F0', warnLine: '#EFE6DC', tealBg: '#E9F6F4', tealLine: '#CDEEEB', danger: '#C0453B',
};

// outcome → nearest MAR code; label kept in `reason` so nothing is lost.
const OUTCOMES = [
    { k: 'given', label: 'Given', code: 'A', color: '#1F8A5B', bg: '#EDF4DD' },
    { k: 'refused', label: 'Refused', code: 'R', color: '#795076', bg: '#F1E9EF' },
    { k: 'missed', label: 'Missed', code: 'O', color: '#B23B30', bg: '#F8E7E5' },
    { k: 'held', label: 'Held', code: 'W', color: '#B5601A', bg: '#FDEBDB' },
    { k: 'delayed', label: 'Delayed', code: 'O', color: '#B5601A', bg: '#FDEBDB' },
    { k: 'vomited', label: 'Vomited', code: 'O', color: '#795076', bg: '#F1E9EF' },
    { k: 'away', label: 'Resident away', code: 'N', color: '#69727C', bg: '#EEF1F4' },
    { k: 'selfadmin', label: 'Self-administered', code: 'A', color: '#2C7E7A', bg: '#E2F4F2' },
];
// A "why" dropdown only makes sense for these — the other outcomes describe themselves.
const REASON_STATUSES = ['refused', 'missed'];
const isGivenLike = (s) => s === 'given' || s === 'selfadmin';

export default function AdministerModal({ ctx, date, adminBy = 'You', endpoint, redirectTo, onClose }) {
    const [status, setStatus] = useState(ctx?.status ?? 'given');
    const [reason, setReason] = useState('');
    const [reasonOther, setReasonOther] = useState('');
    const [witness, setWitness] = useState('');
    const [witnessName, setWitnessName] = useState('');
    const [notes, setNotes] = useState('');
    const [signed, setSigned] = useState(false);
    const [busy, setBusy] = useState(false);
    if (!ctx) return null;

    const row = ctx.row;
    const highRisk = Boolean(row.is_controlled);
    const oc = OUTCOMES.find((o) => o.k === status) ?? OUTCOMES[0];
    const showWitness = (isGivenLike(status) && highRisk) || status === 'refused';
    const witnessRequired = (status === 'given' && highRisk) || status === 'refused';
    const witnessResolved = (witness && witness !== '__other') || (witness === '__other' && witnessName.trim().length > 1);
    const witnessLabel = witness === '__other' ? (witnessName.trim() || 'this witness') : witness;
    const needReason = REASON_STATUSES.includes(status);
    const reasonOptions = status === 'refused' ? REFUSAL_REASONS : OMISSION_REASONS;
    const reasonIsOther = reason.startsWith('Other');
    const reasonOk = reason.trim() && (!reasonIsOther || reasonOther.trim().length > 1);
    const blocked = (needReason && !reasonOk) || (witnessRequired && !witnessResolved) || !signed || busy;

    const submit = () => {
        if (blocked) return;
        setBusy(true);
        const w = witness === '__other' ? witnessName.trim() : witness;
        const chosenReason = reasonIsOther ? reasonOther.trim() : reason;
        const reasonOut = status === 'given' ? '' : status === 'selfadmin' ? 'Self-administered' : (needReason && chosenReason ? `${oc.label} — ${chosenReason}` : oc.label);
        const notesOut = status === 'selfadmin' ? `Self-administered${notes.trim() ? ` · ${notes.trim()}` : ''}` : notes;
        router.post(`${endpoint}/record`, {
            mar_sheet_id: row.mar_sheet_id, date, time_slot: row.slot || 'PRN', code: oc.code,
            dose_given: row.dose ?? '', reason: reasonOut, notes: notesOut,
            witnessed_by: w, redirect_to: redirectTo,
        }, { preserveScroll: true, preserveState: true, onFinish: () => { setBusy(false); onClose(); } });
    };

    const confirmLabel = witnessRequired ? 'Confirm & co-sign' : (isGivenLike(status) ? 'Confirm & sign' : `Record ${oc.label.toLowerCase()}`);

    return (
        <Box style={{ position: 'fixed', inset: 0, zIndex: 320, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 24, fontFamily: FONT }}>
            <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            <Box onClick={onClose} style={{ position: 'absolute', inset: 0, background: 'rgba(19,35,63,0.4)', backdropFilter: 'blur(4px)' }} />
            <Box style={{ position: 'relative', width: 540, maxWidth: '100%', maxHeight: '92vh', overflowY: 'auto', background: '#fff', borderRadius: 22, boxShadow: '0 24px 70px rgba(19,35,63,0.28)' }}>
                {/* Header */}
                <Group align="flex-start" wrap="nowrap" gap={13} style={{ padding: '20px 22px', borderBottom: `1px solid ${T.line2}` }}>
                    <Box style={{ width: 44, height: 44, flexShrink: 0, borderRadius: 12, display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 14, fontWeight: 700, color: '#fff', background: avatarColor(ctx.residentName ?? '') }}>{initials(ctx.residentName ?? '')}</Box>
                    <Box style={{ flex: 1, minWidth: 0 }}>
                        <Text style={{ fontSize: 16, fontWeight: 700, color: T.ink, letterSpacing: '-0.015em' }}>{row.medication_name}</Text>
                        <Text style={{ fontSize: 12.5, color: T.muted }}>{[ctx.residentName, ctx.residentRoom && `Room ${ctx.residentRoom}`, row.dose, row.route].filter(Boolean).join(' · ')}</Text>
                    </Box>
                    <ActionIcon variant="subtle" color="gray" onClick={onClose} style={{ borderRadius: 9 }}><IconX size={17} /></ActionIcon>
                </Group>

                {/* Body */}
                <Box style={{ padding: '18px 22px' }}>
                    {highRisk && (
                        <Group align="flex-start" wrap="nowrap" gap={10} style={{ background: T.warnBg, border: `1px solid ${T.warnLine}`, borderRadius: 12, padding: '11px 13px', marginBottom: 14 }}>
                            <IconAlertTriangle size={16} color={T.amber} style={{ flexShrink: 0, marginTop: 1 }} />
                            <Text style={{ fontSize: 12, color: '#7A5A33', lineHeight: 1.45 }}><b>Controlled / high-risk medication.</b> Witness administration and follow the CD policy.</Text>
                        </Group>
                    )}

                    <Text style={{ fontSize: 11, fontWeight: 700, color: T.faint, textTransform: 'uppercase', letterSpacing: '0.05em', marginBottom: 9 }}>Outcome</Text>
                    <Group gap={7} wrap="wrap">
                        {OUTCOMES.map((o) => {
                            const on = o.k === status;
                            return (
                                <Box key={o.k} component="button" onClick={() => { setStatus(o.k); setReason(''); setReasonOther(''); }}
                                    style={{ padding: '8px 13px', borderRadius: 10, fontSize: 12.5, fontWeight: 700, cursor: 'pointer', border: `1.5px solid ${on ? o.color : '#ECEEF1'}`, background: on ? o.bg : '#fff', color: on ? o.color : T.muted, fontFamily: FONT, transition: 'all .12s' }}>
                                    {o.label}
                                </Box>
                            );
                        })}
                    </Group>

                    {needReason && (
                        <Box style={{ marginTop: 16 }}>
                            <Text style={{ fontSize: 12, fontWeight: 700, color: T.slate, marginBottom: 7 }}>Reason <span style={{ color: T.danger }}>*</span></Text>
                            <Select data={reasonOptions.map((r) => ({ value: r, label: r }))} value={reason || null} onChange={(v) => setReason(v || '')}
                                placeholder="Select a reason…" comboboxProps={{ withinPortal: false }}
                                styles={{ input: { fontFamily: FONT, borderRadius: 12, borderColor: T.line } }} />
                            {reasonIsOther && (
                                <TextInput mt={8} value={reasonOther} onChange={(e) => setReasonOther(e.currentTarget.value)} placeholder="Specify the reason…"
                                    styles={{ input: { fontFamily: FONT, borderRadius: 12, borderColor: T.line } }} />
                            )}
                        </Box>
                    )}

                    {showWitness && (
                        <Box style={{ marginTop: 16 }}>
                            <Text style={{ fontSize: 12, fontWeight: 700, color: T.slate, marginBottom: 7 }}>Witness {witnessRequired && <span style={{ color: T.danger }}>*</span>} <span style={{ fontWeight: 500, color: T.hair }}>— second staff member to co-sign</span></Text>
                            <Select data={[
                                { value: 'Sarah Mensah (Senior Carer)', label: 'Sarah Mensah (Senior Carer)' },
                                { value: 'David Lewis (Nurse)', label: 'David Lewis (Nurse)' },
                                { value: 'Priya Shah (Care Manager)', label: 'Priya Shah (Care Manager)' },
                                { value: '__other', label: 'Someone else — type name…' },
                            ]} value={witness || null} onChange={(v) => { setWitness(v || ''); if (v !== '__other') setWitnessName(v || ''); }}
                                placeholder="Select a witness…" comboboxProps={{ withinPortal: false }}
                                styles={{ input: { fontFamily: FONT, borderRadius: 12, borderColor: T.line } }} />
                            {witness === '__other' && <TextInput mt={8} value={witnessName} onChange={(e) => setWitnessName(e.currentTarget.value)} placeholder="Type the witness's full name…" styles={{ input: { fontFamily: FONT, borderRadius: 12, borderColor: T.line } }} />}
                            {witnessResolved && (
                                <Group align="flex-start" wrap="nowrap" gap={10} style={{ background: T.tealBg, border: `1px solid ${T.tealLine}`, borderRadius: 12, padding: '11px 13px', marginTop: 10 }}>
                                    <IconBell size={16} color="#2C7E7A" style={{ flexShrink: 0, marginTop: 1 }} />
                                    <Text style={{ fontSize: 12, color: '#2C5F5B', lineHeight: 1.45 }}>A co-sign alert will be sent to <b>{witnessLabel}</b> to confirm they witnessed this administration. The record is provisional until they sign.</Text>
                                </Group>
                            )}
                        </Box>
                    )}

                    <Box style={{ marginTop: 16 }}>
                        <Text style={{ fontSize: 12, fontWeight: 700, color: T.slate, marginBottom: 7 }}>Notes <span style={{ fontWeight: 500, color: T.hair }}>(optional)</span></Text>
                        <Textarea value={notes} onChange={(e) => setNotes(e.currentTarget.value)} placeholder="e.g. taken with water, no concerns" autosize minRows={2} styles={{ input: { fontFamily: FONT, borderRadius: 12, borderColor: T.line } }} />
                    </Box>

                    <Box style={{ marginTop: 16 }}>
                        <Text style={{ fontSize: 12, fontWeight: 700, color: T.slate, marginBottom: 7 }}>Staff signature <span style={{ color: T.danger }}>*</span></Text>
                        <Box onClick={() => setSigned((s) => !s)} style={{ minHeight: 52, borderRadius: 12, border: `1.5px dashed ${signed ? T.green : '#D4DAE0'}`, background: signed ? '#F4FAEF' : T.wash2, display: 'flex', alignItems: 'center', justifyContent: 'center', cursor: 'pointer', transition: 'all .15s' }}>
                            {signed
                                ? <Text style={{ fontFamily: "'Brush Script MT','Segoe Script',cursive", fontSize: 23, color: T.ink }}>{adminBy.split(' · ')[0]}</Text>
                                : <Group gap={7} wrap="nowrap"><IconPencil size={15} color={T.hair} /><Text style={{ fontSize: 12.5, color: T.hair }}>Tap to sign as {adminBy}</Text></Group>}
                        </Box>
                    </Box>
                </Box>

                {/* Footer */}
                <Group align="center" wrap="nowrap" gap={10} style={{ padding: '14px 22px', borderTop: `1px solid ${T.line2}` }}>
                    <Group gap={6} wrap="nowrap" style={{ flex: 1 }}><IconClock size={13} color={T.hair} /><Text style={{ fontSize: 11, color: T.hair }}>Timestamped on save</Text></Group>
                    <Button variant="default" onClick={onClose} style={{ fontFamily: FONT, borderRadius: 11, color: T.slate }}>Cancel</Button>
                    <Button onClick={submit} disabled={blocked} loading={busy} style={{ fontFamily: FONT, borderRadius: 11, background: blocked ? '#C9D0D7' : T.ink, paddingInline: 22 }}>{confirmLabel}</Button>
                </Group>
            </Box>
        </Box>
    );
}
