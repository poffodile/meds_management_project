import { useState, useMemo, useEffect } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Box, Group, Text, Badge, Button, ActionIcon, ThemeIcon, SegmentedControl,
    Modal, Select, Textarea, ScrollArea,
} from '@mantine/core';
import { notifications } from '@mantine/notifications';
import {
    IconChevronLeft, IconChevronRight, IconAlertTriangle, IconCheck, IconCircleCheck,
    IconPill, IconUser,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#8493A8, #6C7C93)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';

const ENDPOINT = '/frontend2/medication-2/missed-doses';
const CLINICAL_ACTIONS = [
    'Contacted GP / prescriber',
    'Contacted pharmacy',
    'Dose given late',
    'Monitored resident — no action needed',
    'Escalated to manager / senior',
    'Documented, no further action',
    'Other (see notes)',
];

/** Resolve panel for one outstanding (or resolved) dose. */
function ResolvePanel({ item, date, onClose }) {
    const [editing, setEditing] = useState(!item.resolved);
    const form = useForm({
        mar_sheet_id: item.mar_sheet_id, review_date: date, time_slot: item.slot,
        dose_kind: item.kind, code: item.code ?? '',
        clinical_action: item.clinical_action ?? '', notes: item.notes ?? '',
    });

    const submit = () => form.post(`${ENDPOINT}/resolve`, {
        preserveScroll: true,
        onSuccess: () => { notifications.show({ color: 'teal', message: `${item.resident_name}'s dose resolved.` }); onClose(); },
        onError: (e) => notifications.show({ color: 'red', title: 'Not saved', message: Object.values(e ?? {})[0] || 'Could not resolve this dose.' }),
    });
    const undo = () => router.post(`${ENDPOINT}/unresolve`, { mar_sheet_id: item.mar_sheet_id, review_date: date, time_slot: item.slot }, {
        preserveScroll: true,
        onSuccess: () => { notifications.show({ color: 'gray', message: 'Resolution removed.' }); onClose(); },
    });

    return (
        <Modal opened onClose={onClose} centered radius="md" size="md"
            title={<Text fw={750} c={TXT}>{item.resolved && !editing ? 'Resolved dose' : 'Resolve missed dose'}</Text>}>
            <Box p="sm" mb="md" style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 10 }}>
                <Text fz={14} fw={700} c={TXT}>{item.medication_name}</Text>
                <Text fz={12.5} c={MUTED}>{item.resident_name}{item.room ? ` · Room ${item.room}` : ''} · {item.slot} · {item.kind === 'not_given' ? 'Not given' : 'Missed'}</Text>
            </Box>

            {item.resolved && !editing ? (
                <>
                    <Text fz={12} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.4 }}>Action taken</Text>
                    <Text fz="sm" c={TXT} mb="xs">{item.clinical_action || '—'}</Text>
                    {item.notes && <><Text fz={12} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.4 }}>Notes</Text><Text fz="sm" c={MUTED} mb="xs">{item.notes}</Text></>}
                    {item.reviewed_by && <Text fz={11.5} c={FAINT}>Reviewed by {item.reviewed_by}{item.reviewed_at ? ` · ${item.reviewed_at}` : ''}</Text>}
                    <Group justify="space-between" mt="lg">
                        <Button variant="subtle" color="red" radius="xl" onClick={undo}>Undo resolution</Button>
                        <Button variant="light" radius="xl" onClick={() => setEditing(true)}>Edit</Button>
                    </Group>
                </>
            ) : (
                <>
                    <Select label="Action taken" placeholder="Choose the clinical action" radius="md" mb="sm"
                        data={CLINICAL_ACTIONS} value={form.data.clinical_action}
                        onChange={(v) => form.setData('clinical_action', v ?? '')} error={form.errors.clinical_action} searchable />
                    <Textarea label="Notes (optional)" radius="md" autosize minRows={2} placeholder="Anything else worth recording"
                        value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                    <Group justify="flex-end" mt="lg" gap="sm">
                        <Button variant="default" radius="xl" onClick={onClose}>Cancel</Button>
                        <Button color="teal" radius="xl" loading={form.processing} onClick={submit}>Mark resolved</Button>
                    </Group>
                </>
            )}
        </Modal>
    );
}

function Stat({ label, value, tone }) {
    return (
        <Box style={{ flex: 1, minWidth: 96, background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 12, padding: '10px 14px' }}>
            <Text fz={22} fw={800} c={tone || TXT} style={{ fontVariantNumeric: 'tabular-nums', letterSpacing: '-0.02em' }}>{value}</Text>
            <Text fz={11.5} fw={600} c={MUTED}>{label}</Text>
        </Box>
    );
}

export default function MissedDoses({ items = [], stats = {}, date, prevDate, nextDate, todayDate, home }) {
    const [filter, setFilter] = useState('outstanding');
    const [active, setActive] = useState(null);

    const flash = usePage().props?.flash;
    useEffect(() => {
        if (flash?.error) notifications.show({ color: 'red', title: 'Something went wrong', message: flash.error });
    }, [flash?.error]);

    const shown = useMemo(() => items.filter((i) => (
        filter === 'all' ? true : filter === 'resolved' ? i.resolved : !i.resolved
    )), [items, filter]);

    const go = (d) => router.get(ENDPOINT, { date: d }, { preserveScroll: true, preserveState: true });

    return (
        <AppShell title="Missed doses" section="Medication 2">
            <Head title="Missed doses — Medication 2" />
            <Box maw={760} mx="auto">
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Box>
                        <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Missed doses</Text>
                        <Text fz={13} c={MUTED}>{home ? `${home} · ` : ''}review and resolve</Text>
                    </Box>
                    <Group gap={6} wrap="nowrap">
                        <ActionIcon variant="default" radius="xl" size="lg" onClick={() => go(prevDate)} aria-label="Previous day"><IconChevronLeft size={16} /></ActionIcon>
                        <Text fz={13} fw={700} c={TXT} style={{ fontVariantNumeric: 'tabular-nums', minWidth: 96, textAlign: 'center' }}>{date}{date === todayDate ? ' · Today' : ''}</Text>
                        <ActionIcon variant="default" radius="xl" size="lg" onClick={() => go(nextDate)} disabled={date >= todayDate} aria-label="Next day"><IconChevronRight size={16} /></ActionIcon>
                    </Group>
                </Group>

                <Group gap="sm" mb="md" wrap="wrap">
                    <Stat label="Outstanding" value={stats.outstanding ?? 0} tone={(stats.outstanding ?? 0) > 0 ? ORANGE : TEAL} />
                    <Stat label="Missed" value={stats.missed ?? 0} />
                    <Stat label="Not given" value={stats.not_given ?? 0} />
                    <Stat label="Resolved" value={stats.resolved ?? 0} tone={TEAL} />
                </Group>

                <SegmentedControl fullWidth size="xs" radius="xl" mb="md" value={filter} onChange={setFilter}
                    data={[{ label: 'Outstanding', value: 'outstanding' }, { label: 'Resolved', value: 'resolved' }, { label: 'All', value: 'all' }]} />

                <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
                    {shown.length === 0 ? (
                        <Box py={54} ta="center">
                            <ThemeIcon variant="light" color="teal" size={46} radius="xl" mx="auto" mb="sm"><IconCircleCheck size={24} /></ThemeIcon>
                            <Text fz="sm" fw={700} c={TXT}>{filter === 'outstanding' ? 'Nothing outstanding' : 'Nothing to show'}</Text>
                            <Text fz="xs" c={MUTED}>{filter === 'outstanding' ? 'Every missed dose for this day has been reviewed.' : 'No doses match this filter.'}</Text>
                        </Box>
                    ) : (
                        <ScrollArea.Autosize mah={560} type="hover">
                            {shown.map((i) => (
                                <Box key={i.id} style={{ borderTop: `1px solid ${HAIR}`, padding: '12px 14px' }}>
                                    <Group gap="sm" wrap="nowrap" align="flex-start">
                                        <ThemeIcon variant="light" color={i.resolved ? 'teal' : (i.kind === 'not_given' ? 'red' : 'orange')} size={34} radius="md" style={{ flexShrink: 0 }}>
                                            {i.resolved ? <IconCheck size={16} /> : <IconAlertTriangle size={16} />}
                                        </ThemeIcon>
                                        <Box style={{ flex: 1, minWidth: 0 }}>
                                            <Text fz={13.5} fw={700} c={TXT} truncate>{i.medication_name}</Text>
                                            <Text fz={12} c={MUTED}>{i.resident_name}{i.room ? ` · Room ${i.room}` : ''} · {i.slot}</Text>
                                            <Group gap={6} mt={3}>
                                                <Badge size="xs" variant="light" radius="sm" color={i.kind === 'not_given' ? 'red' : 'orange'}>{i.kind === 'not_given' ? 'Not given' : 'Missed'}</Badge>
                                                {i.resolved && <Badge size="xs" variant="light" radius="sm" color="teal">Resolved</Badge>}
                                            </Group>
                                        </Box>
                                        <Button size="xs" radius="xl" variant={i.resolved ? 'subtle' : 'light'} color={i.resolved ? 'gray' : 'teal'}
                                            onClick={() => setActive(i)} style={{ flexShrink: 0 }}>
                                            {i.resolved ? 'View' : 'Resolve'}
                                        </Button>
                                    </Group>
                                </Box>
                            ))}
                        </ScrollArea.Autosize>
                    )}
                </Box>
            </Box>

            {active && <ResolvePanel item={active} date={date} onClose={() => setActive(null)} />}
        </AppShell>
    );
}
