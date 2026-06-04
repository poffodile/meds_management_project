import { Modal, Button, Group, Stack, TextInput, Textarea, Select, Checkbox, Divider, Box } from '@mantine/core';
import { useForm } from '@inertiajs/react';

const PRIORITY_OPTIONS = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
];

const pad = (n) => String(n).padStart(2, '0');
const rowStyle = { border: '1px solid var(--mantine-color-gray-3)', borderRadius: 8 };

/**
 * AddHandoverModal — create a shift handover.
 * Has repeatable rows for client updates, medication concerns and priority alerts,
 * and two submit buttons (Save draft / Submit).
 */
export default function AddHandoverModal({ opened, onClose, serviceUsers = [], defaultDate }) {
    const d = new Date();
    const today = defaultDate || `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const nowTime = `${pad(d.getHours())}:${pad(d.getMinutes())}`;

    const form = useForm({
        location: '',
        handover_date: today,
        handover_time: nowTime,
        from_carer_name: '',
        to_carer_name: '',
        general_notes: '',
        client_updates: [],
        medication_concerns: [],
        priority_alerts: [],
        submit_action: 'draft',
    });

    const residentOptions = serviceUsers.map((s) => ({ value: String(s.id), label: s.name }));
    const nameFor = (id) => serviceUsers.find((s) => String(s.id) === String(id))?.name ?? '';

    const addItem = (field, blank) => form.setData(field, [...form.data[field], blank]);
    const removeItem = (field, idx) => form.setData(field, form.data[field].filter((_, i) => i !== idx));
    const patchItem = (field, idx, patch) => form.setData(field, form.data[field].map((it, i) => (i === idx ? { ...it, ...patch } : it)));

    const submit = (action) => {
        form.transform((data) => ({ ...data, submit_action: action }))
            .post('/medication/shift-handover-react', {
                preserveScroll: true,
                onSuccess: () => { form.reset(); onClose(); },
            });
    };

    return (
        <Modal opened={opened} onClose={onClose} title="New handover" size="lg" centered>
            <Stack>
                <Group grow>
                    <TextInput label="From carer" value={form.data.from_carer_name} onChange={(e) => form.setData('from_carer_name', e.currentTarget.value)} />
                    <TextInput label="To carer" value={form.data.to_carer_name} onChange={(e) => form.setData('to_carer_name', e.currentTarget.value)} />
                </Group>
                <Group grow>
                    <TextInput label="Date" type="date" value={form.data.handover_date} onChange={(e) => form.setData('handover_date', e.currentTarget.value)} error={form.errors.handover_date} required />
                    <TextInput label="Time" type="time" value={form.data.handover_time} onChange={(e) => form.setData('handover_time', e.currentTarget.value)} error={form.errors.handover_time} required />
                    <TextInput label="Location" value={form.data.location} onChange={(e) => form.setData('location', e.currentTarget.value)} />
                </Group>
                <Textarea label="General notes" autosize minRows={2} value={form.data.general_notes} onChange={(e) => form.setData('general_notes', e.currentTarget.value)} />

                <Divider label="Client updates" labelPosition="left" />
                {form.data.client_updates.map((u, idx) => (
                    <Box key={idx} p="xs" style={rowStyle}>
                        <Group justify="space-between" mb={4}>
                            <Select placeholder="Resident" data={residentOptions} value={u.client_id ? String(u.client_id) : null}
                                onChange={(v) => patchItem('client_updates', idx, { client_id: v, client_name: nameFor(v) })} searchable w={200} />
                            <Group gap="xs">
                                <Select placeholder="Priority" data={PRIORITY_OPTIONS} value={u.priority ?? null} onChange={(v) => patchItem('client_updates', idx, { priority: v })} w={130} />
                                <Button size="xs" variant="subtle" color="red" onClick={() => removeItem('client_updates', idx)}>Remove</Button>
                            </Group>
                        </Group>
                        <Textarea placeholder="Update…" autosize minRows={1} value={u.update ?? ''} onChange={(e) => patchItem('client_updates', idx, { update: e.currentTarget.value })} />
                    </Box>
                ))}
                <Button size="xs" variant="light" onClick={() => addItem('client_updates', { client_id: '', client_name: '', update: '', priority: 'low' })}>+ Add client update</Button>

                <Divider label="Medication concerns" labelPosition="left" />
                {form.data.medication_concerns.map((c, idx) => (
                    <Box key={idx} p="xs" style={rowStyle}>
                        <Group justify="space-between" mb={4}>
                            <Select placeholder="Resident" data={residentOptions} value={c.client_id ? String(c.client_id) : null}
                                onChange={(v) => patchItem('medication_concerns', idx, { client_id: v, client_name: nameFor(v) })} searchable w={200} />
                            <Group gap="xs">
                                <Checkbox label="Action required" checked={!!c.action_required} onChange={(e) => patchItem('medication_concerns', idx, { action_required: e.currentTarget.checked })} />
                                <Button size="xs" variant="subtle" color="red" onClick={() => removeItem('medication_concerns', idx)}>Remove</Button>
                            </Group>
                        </Group>
                        <Textarea placeholder="Concern…" autosize minRows={1} value={c.concern ?? ''} onChange={(e) => patchItem('medication_concerns', idx, { concern: e.currentTarget.value })} />
                    </Box>
                ))}
                <Button size="xs" variant="light" onClick={() => addItem('medication_concerns', { client_id: '', client_name: '', concern: '', action_required: false })}>+ Add concern</Button>

                <Divider label="Priority alerts" labelPosition="left" />
                {form.data.priority_alerts.map((a, idx) => (
                    <Group key={idx} gap="xs">
                        <TextInput placeholder="Alert…" value={a.alert ?? ''} onChange={(e) => patchItem('priority_alerts', idx, { alert: e.currentTarget.value })} style={{ flex: 1 }} />
                        <Button size="xs" variant="subtle" color="red" onClick={() => removeItem('priority_alerts', idx)}>Remove</Button>
                    </Group>
                ))}
                <Button size="xs" variant="light" onClick={() => addItem('priority_alerts', { alert: '' })}>+ Add alert</Button>

                <Divider my="xs" />
                <Group justify="flex-end">
                    <Button variant="default" onClick={onClose}>Cancel</Button>
                    <Button variant="default" loading={form.processing} onClick={() => submit('draft')}>Save draft</Button>
                    <Button loading={form.processing} onClick={() => submit('submitted')}>Submit</Button>
                </Group>
            </Stack>
        </Modal>
    );
}
