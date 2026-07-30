import { useState, useEffect } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import {
    Box, Group, Text, Badge, Button, ActionIcon, ThemeIcon, Select, Textarea, TextInput,
    Modal, Checkbox, Divider,
} from '@mantine/core';
import { notifications } from '@mantine/notifications';
import {
    IconChevronLeft, IconChevronRight, IconClipboardText, IconPlus, IconTrash,
    IconCheck, IconAlertTriangle, IconPill, IconUser, IconClock,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#586780, #9BA9BD)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const ORANGE = 'light-dark(#DE7B1E, #EBA65A)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const PURPLE = 'light-dark(#6455D0, #8E7FEC)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';

const ENDPOINT = '/frontend2/medication-2/shift-handover';

const PRIORITY = {
    urgent: { c: RED, label: 'Urgent' },
    high: { c: ORANGE, label: 'High' },
    medium: { c: TEAL, label: 'Medium' },
    low: { c: MUTED, label: 'Low' },
};

const STATUS = {
    draft: { c: MUTED, label: 'Draft' },
    submitted: { c: ORANGE, label: 'Submitted' },
    acknowledged: { c: TEAL, label: 'Acknowledged' },
};

/** A submitted/draft handover in the day log. */
function HandoverCard({ h, isManager, date }) {
    const s = STATUS[h.status] ?? STATUS.draft;
    const acknowledge = () => router.post(`${ENDPOINT}/${h.id}/acknowledge`, { date }, {
        preserveScroll: true,
        onSuccess: () => notifications.show({ color: 'teal', message: 'Handover acknowledged.' }),
        onError: (e) => notifications.show({ color: 'red', title: 'Not acknowledged', message: Object.values(e ?? {})[0] || 'Could not acknowledge.' }),
    });

    return (
        <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
            <Group justify="space-between" wrap="nowrap" p="sm" style={{ background: SOFT, borderBottom: `1px solid ${HAIR}` }}>
                <Group gap="sm" wrap="nowrap" style={{ minWidth: 0 }}>
                    <ThemeIcon variant="light" color="indigo" size={34} radius="md"><IconClipboardText size={17} /></ThemeIcon>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={7} wrap="wrap" align="center">
                            <Text fz={13.5} fw={700} c={TXT}>{h.handover_time || '—'}</Text>
                            {h.location && <Text fz={12} c={MUTED}>· {h.location}</Text>}
                            <Badge size="xs" radius="sm" variant="light" style={{ background: `color-mix(in srgb, ${s.c} 14%, transparent)`, color: s.c }}>{s.label}</Badge>
                        </Group>
                        <Text fz={11.5} c={FAINT}>
                            {[h.from_carer_name && `from ${h.from_carer_name}`, h.to_carer_name && `to ${h.to_carer_name}`].filter(Boolean).join(' · ') || 'Shift handover'}
                        </Text>
                    </Box>
                </Group>
                {isManager && h.can_acknowledge && (
                    <Button size="xs" radius="xl" color="teal" variant="light" leftSection={<IconCheck size={13} />} onClick={acknowledge} style={{ flexShrink: 0 }}>Acknowledge</Button>
                )}
            </Group>

            <Box p="sm">
                {h.general_notes && <Text fz={13} c={TXT} mb={h.medication_concerns?.length || h.client_updates?.length || h.priority_alerts?.length ? 10 : 0} style={{ whiteSpace: 'pre-wrap' }}>{h.general_notes}</Text>}

                {h.priority_alerts?.length > 0 && (
                    <Box mb={10}>
                        {h.priority_alerts.map((a, i) => (
                            <Group key={i} gap={7} wrap="nowrap" mb={4}>
                                <IconAlertTriangle size={13} color="var(--mantine-color-red-6)" style={{ flexShrink: 0, marginTop: 2 }} />
                                <Text fz={12.5} c={TXT}>{a.alert ?? a}</Text>
                            </Group>
                        ))}
                    </Box>
                )}

                {h.medication_concerns?.length > 0 && (
                    <Box mb={10}>
                        <Text fz={11} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.4 }} mb={4}>Medication concerns</Text>
                        {h.medication_concerns.map((c, i) => (
                            <Group key={i} gap={7} wrap="nowrap" mb={4} align="flex-start">
                                <ThemeIcon size={18} radius="sm" variant="light" color="grape" style={{ flexShrink: 0, marginTop: 1 }}><IconPill size={11} /></ThemeIcon>
                                <Text fz={12.5} c={TXT}>
                                    {c.client_name && <Text span fw={650}>{c.client_name}: </Text>}{c.concern}
                                    {c.action_required && <Badge size="xs" color="orange" variant="light" radius="sm" ml={6}>Action needed</Badge>}
                                </Text>
                            </Group>
                        ))}
                    </Box>
                )}

                {h.client_updates?.length > 0 && (
                    <Box>
                        <Text fz={11} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.4 }} mb={4}>Resident updates</Text>
                        {h.client_updates.map((u, i) => {
                            const p = PRIORITY[u.priority] ?? null;
                            return (
                                <Group key={i} gap={7} wrap="nowrap" mb={4} align="flex-start">
                                    <ThemeIcon size={18} radius="sm" variant="light" color="blue" style={{ flexShrink: 0, marginTop: 1 }}><IconUser size={11} /></ThemeIcon>
                                    <Text fz={12.5} c={TXT}>
                                        {u.client_name && <Text span fw={650}>{u.client_name}: </Text>}{u.update}
                                        {p && <Badge size="xs" radius="sm" variant="light" ml={6} style={{ background: `color-mix(in srgb, ${p.c} 14%, transparent)`, color: p.c }}>{p.label}</Badge>}
                                    </Text>
                                </Group>
                            );
                        })}
                    </Box>
                )}
            </Box>

            {h.status === 'acknowledged' && (
                <Group gap={6} px="sm" pb="sm">
                    <IconCheck size={12} color="var(--mantine-color-teal-6)" />
                    <Text fz={11} c={FAINT}>Acknowledged{h.acknowledged_by ? ` by ${h.acknowledged_by}` : ''}{h.acknowledged_at ? ` · ${h.acknowledged_at}` : ''}</Text>
                </Group>
            )}
        </Box>
    );
}

/** The "new handover" builder modal. */
function NewHandoverModal({ opened, onClose, residents, staff, selfName, date, prefill }) {
    const form = useForm({
        location: '',
        handover_date: date,
        handover_time: new Date().toTimeString().slice(0, 5),
        from_carer_name: selfName ?? '',
        to_carer_name: '',
        general_notes: '',
        // Seed a medication concern when the round flagged one (round → handover).
        client_updates: [],
        medication_concerns: prefill ? [{ client_id: prefill.client_id ?? null, client_name: prefill.client_name ?? '', concern: prefill.concern ?? '', action_required: true }] : [],
        priority_alerts: [],
        submit_action: 'submitted',
    });

    useEffect(() => { form.setData('handover_date', date); /* eslint-disable-next-line */ }, [date]);

    const addConcern = () => form.setData('medication_concerns', [...form.data.medication_concerns, { client_id: null, client_name: '', concern: '', action_required: false }]);
    const addUpdate = () => form.setData('client_updates', [...form.data.client_updates, { client_id: null, client_name: '', update: '', priority: 'medium' }]);
    const addAlert = () => form.setData('priority_alerts', [...form.data.priority_alerts, { alert: '' }]);

    const setConcern = (i, k, v) => { const a = [...form.data.medication_concerns]; a[i] = { ...a[i], [k]: v }; form.setData('medication_concerns', a); };
    const setUpdate = (i, k, v) => { const a = [...form.data.client_updates]; a[i] = { ...a[i], [k]: v }; form.setData('client_updates', a); };
    const setAlert = (i, v) => { const a = [...form.data.priority_alerts]; a[i] = { alert: v }; form.setData('priority_alerts', a); };
    const rm = (key, i) => form.setData(key, form.data[key].filter((_, j) => j !== i));

    const pickResident = (i, setter) => (val) => {
        const r = residents.find((x) => x.value === val);
        setter(i, 'client_id', val ? Number(val) : null);
        setter(i, 'client_name', r ? r.label : '');
    };

    const submit = (action) => {
        form.setData('submit_action', action);
        form.transform((d) => ({ ...d, submit_action: action }))
            .post(`${ENDPOINT}`, {
                preserveScroll: true,
                onSuccess: () => { notifications.show({ color: 'teal', message: action === 'submitted' ? 'Handover submitted.' : 'Draft saved.' }); form.reset(); onClose(); },
                onError: (e) => notifications.show({ color: 'red', title: 'Not saved', message: Object.values(e ?? {})[0] || 'Could not save the handover.' }),
            });
    };

    return (
        <Modal opened={opened} onClose={onClose} centered radius="md" size="lg" title={<Text fw={750} c={TXT}>New shift handover</Text>}>
            <Group grow gap="sm" mb="sm">
                <TextInput label="Time" type="time" radius="md" value={form.data.handover_time} onChange={(e) => form.setData('handover_time', e.currentTarget.value)} error={form.errors.handover_time} />
                <TextInput label="Location (optional)" radius="md" placeholder="e.g. Ground floor" value={form.data.location} onChange={(e) => form.setData('location', e.currentTarget.value)} />
            </Group>
            <Group grow gap="sm" mb="sm">
                <TextInput label="From (handing over)" radius="md" value={form.data.from_carer_name} onChange={(e) => form.setData('from_carer_name', e.currentTarget.value)} />
                <Select label="To (taking over)" radius="md" placeholder="Choose staff" data={staff} searchable clearable
                    value={staff.find((s) => s.label === form.data.to_carer_name)?.value ?? null}
                    onChange={(v) => form.setData('to_carer_name', staff.find((s) => s.value === v)?.label ?? '')} />
            </Group>

            <Textarea label="General notes" radius="md" autosize minRows={3} mb="md" placeholder="What the next shift needs to know…"
                value={form.data.general_notes} onChange={(e) => form.setData('general_notes', e.currentTarget.value)} />

            {/* Priority alerts */}
            <Divider label="Priority alerts" labelPosition="left" mb="xs" />
            {form.data.priority_alerts.map((a, i) => (
                <Group key={i} gap={6} mb={6} wrap="nowrap">
                    <TextInput style={{ flex: 1 }} radius="md" placeholder="Something the next shift must not miss" value={a.alert} onChange={(e) => setAlert(i, e.currentTarget.value)} />
                    <ActionIcon variant="subtle" color="red" onClick={() => rm('priority_alerts', i)}><IconTrash size={15} /></ActionIcon>
                </Group>
            ))}
            <Button size="xs" variant="subtle" color="gray" radius="xl" leftSection={<IconPlus size={13} />} onClick={addAlert} mb="md">Add alert</Button>

            {/* Medication concerns */}
            <Divider label="Medication concerns" labelPosition="left" mb="xs" />
            {form.data.medication_concerns.map((c, i) => (
                <Box key={i} mb={8} p="xs" style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 10 }}>
                    <Group gap={6} mb={6} wrap="nowrap">
                        <Select style={{ flex: 1 }} radius="md" size="xs" placeholder="Resident (optional)" data={residents} searchable clearable
                            value={c.client_id ? String(c.client_id) : null} onChange={pickResident(i, setConcern)} />
                        <ActionIcon variant="subtle" color="red" onClick={() => rm('medication_concerns', i)}><IconTrash size={15} /></ActionIcon>
                    </Group>
                    <Textarea radius="md" size="xs" autosize minRows={2} placeholder="The concern" mb={6} value={c.concern} onChange={(e) => setConcern(i, 'concern', e.currentTarget.value)} />
                    <Checkbox size="xs" label="Action required by the next shift" checked={c.action_required} onChange={(e) => setConcern(i, 'action_required', e.currentTarget.checked)} />
                </Box>
            ))}
            <Button size="xs" variant="subtle" color="gray" radius="xl" leftSection={<IconPlus size={13} />} onClick={addConcern} mb="md">Add medication concern</Button>

            {/* Resident updates */}
            <Divider label="Resident updates" labelPosition="left" mb="xs" />
            {form.data.client_updates.map((u, i) => (
                <Box key={i} mb={8} p="xs" style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 10 }}>
                    <Group gap={6} mb={6} wrap="nowrap">
                        <Select style={{ flex: 1 }} radius="md" size="xs" placeholder="Resident (optional)" data={residents} searchable clearable
                            value={u.client_id ? String(u.client_id) : null} onChange={pickResident(i, setUpdate)} />
                        <Select w={110} radius="md" size="xs" data={[{ value: 'urgent', label: 'Urgent' }, { value: 'high', label: 'High' }, { value: 'medium', label: 'Medium' }, { value: 'low', label: 'Low' }]}
                            value={u.priority} onChange={(v) => setUpdate(i, 'priority', v)} />
                        <ActionIcon variant="subtle" color="red" onClick={() => rm('client_updates', i)}><IconTrash size={15} /></ActionIcon>
                    </Group>
                    <Textarea radius="md" size="xs" autosize minRows={2} placeholder="The update" value={u.update} onChange={(e) => setUpdate(i, 'update', e.currentTarget.value)} />
                </Box>
            ))}
            <Button size="xs" variant="subtle" color="gray" radius="xl" leftSection={<IconPlus size={13} />} onClick={addUpdate} mb="md">Add resident update</Button>

            <Group justify="flex-end" gap="sm" mt="md">
                <Button variant="default" radius="xl" onClick={() => submit('draft')} loading={form.processing}>Save draft</Button>
                <Button color="teal" radius="xl" onClick={() => submit('submitted')} loading={form.processing}>Submit handover</Button>
            </Group>
        </Modal>
    );
}

export default function ShiftHandover({ handovers = [], residents = [], staff = [], selfName, prefill = null, selectedDate, prevDate, nextDate, todayDate, isManager = false }) {
    const [newOpen, setNewOpen] = useState(false);
    const flash = usePage().props?.flash;
    useEffect(() => {
        if (flash?.error) notifications.show({ color: 'red', title: 'Something went wrong', message: flash.error });
    }, [flash?.error]);
    // Arrived from the round's "flag to handover" → open the builder with the concern seeded.
    useEffect(() => { if (prefill) setNewOpen(true); }, [prefill]);

    const go = (d) => router.get(ENDPOINT, { date: d }, { preserveScroll: true, preserveState: true });
    const isToday = selectedDate === todayDate;

    return (
        <AppShell title="Shift handover" section="Medication 2">
            <Head title="Shift handover — Medication 2" />
            <Box maw={760} mx="auto">
                <Group justify="space-between" align="center" mb="md" wrap="wrap" gap="sm">
                    <Group gap="sm" align="center">
                        <ThemeIcon variant="light" color="indigo" size={38} radius="md"><IconClipboardText size={20} /></ThemeIcon>
                        <Box>
                            <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Shift handover</Text>
                            <Text fz={13} c={MUTED}>Pass on what the next shift needs to know</Text>
                        </Box>
                    </Group>
                    <Button radius="xl" color="teal" leftSection={<IconPlus size={16} />} onClick={() => setNewOpen(true)}>New handover</Button>
                </Group>

                {/* Day navigation */}
                <Group justify="space-between" mb="md" p="xs" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 12 }}>
                    <ActionIcon variant="subtle" color="gray" radius="md" onClick={() => go(prevDate)} aria-label="Previous day"><IconChevronLeft size={18} /></ActionIcon>
                    <Group gap={8}>
                        <IconClock size={14} color="var(--mantine-color-dimmed)" />
                        <Text fz={13.5} fw={700} c={TXT}>{isToday ? 'Today' : selectedDate}</Text>
                    </Group>
                    <ActionIcon variant="subtle" color="gray" radius="md" onClick={() => go(nextDate)} aria-label="Next day" disabled={isToday}><IconChevronRight size={18} /></ActionIcon>
                </Group>

                {handovers.length === 0 ? (
                    <Box py={54} ta="center" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16 }}>
                        <ThemeIcon variant="light" color="gray" size={46} radius="xl" mx="auto" mb="sm"><IconClipboardText size={22} /></ThemeIcon>
                        <Text fz="sm" fw={700} c={TXT}>No handovers for this day</Text>
                        <Text fz="xs" c={MUTED}>Start one with “New handover”.</Text>
                    </Box>
                ) : (
                    <Box style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        {handovers.map((h) => <HandoverCard key={h.id} h={h} isManager={isManager} date={selectedDate} />)}
                    </Box>
                )}
            </Box>

            <NewHandoverModal key={prefill ? 'prefill' : 'blank'} opened={newOpen} onClose={() => setNewOpen(false)} residents={residents} staff={staff} selfName={selfName} date={selectedDate} prefill={prefill} />
        </AppShell>
    );
}
