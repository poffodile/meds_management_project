import { useState, useEffect } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
    Box, Group, Text, Badge, ThemeIcon, Avatar, Button, Collapse, Modal,
    Select, NumberInput, TextInput, Textarea, ScrollArea, Alert,
} from '@mantine/core';
import { notifications } from '@mantine/notifications';
import {
    IconShieldLock, IconPill, IconChevronDown, IconPlus, IconArrowDownRight,
    IconArrowUpRight, IconInfoCircle,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#8493A8, #6C7C93)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const RED = 'light-dark(#CE3F3F, #E56B6B)';
const PURPLE = 'light-dark(#6455D0, #8E7FEC)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';

const ENDPOINT = '/frontend2/medication-2/controlled-drugs';
const ACTION_LABEL = {
    administered: 'Administered', received: 'Received', disposed: 'Disposed',
    returned: 'Returned', adjustment: 'Recount',
};
const OUTGOING = ['administered', 'disposed', 'returned'];

/** Record a witnessed movement for one CD medicine. */
function MovementModal({ reg, onClose }) {
    const form = useForm({
        mar_sheet_id: reg.mar_sheet_id, action_type: 'received',
        quantity: '', witness_name: '', notes: '',
    });
    const isAdjust = form.data.action_type === 'adjustment';
    const submit = () => form.post(`${ENDPOINT}`, {
        preserveScroll: true,
        onSuccess: () => { notifications.show({ color: 'teal', message: 'Register entry recorded.' }); onClose(); },
        onError: (e) => notifications.show({ color: 'red', title: 'Not recorded', message: Object.values(e ?? {})[0] || 'Could not record this entry.' }),
    });

    return (
        <Modal opened onClose={onClose} centered radius="md" size="md"
            title={<Text fw={750} c={TXT}>Record register movement</Text>}>
            <Box p="sm" mb="md" style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 10 }}>
                <Text fz={14} fw={700} c={TXT}>{reg.medication_name}</Text>
                <Text fz={12.5} c={MUTED}>{reg.resident} · current balance {reg.balance}{reg.unit ? ` ${reg.unit}` : ''}</Text>
            </Box>
            <Select label="Movement" radius="md" mb="sm" data={[
                { value: 'received', label: 'Received (stock in)' },
                { value: 'disposed', label: 'Disposed' },
                { value: 'returned', label: 'Returned to pharmacy' },
                { value: 'adjustment', label: 'Recount (set balance)' },
            ]} value={form.data.action_type} onChange={(v) => form.setData('action_type', v)} />
            <NumberInput label={isAdjust ? 'Counted balance' : 'Quantity'} radius="md" mb="sm" min={0} hideControls
                description={isAdjust ? 'The actual amount counted — this becomes the new balance.' : undefined}
                value={form.data.quantity} onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} />
            <TextInput label="Witness" radius="md" mb="sm" placeholder="Second signatory’s name" required
                value={form.data.witness_name} onChange={(e) => form.setData('witness_name', e.currentTarget.value)} error={form.errors.witness_name} />
            <Textarea label="Notes (optional)" radius="md" autosize minRows={2}
                value={form.data.notes} onChange={(e) => form.setData('notes', e.currentTarget.value)} />
            <Group justify="flex-end" mt="lg" gap="sm">
                <Button variant="default" radius="xl" onClick={onClose}>Cancel</Button>
                <Button color="grape" radius="xl" loading={form.processing} onClick={submit}>Record entry</Button>
            </Group>
        </Modal>
    );
}

/** One CD medicine: current balance + its append-only ledger. */
function RegisterCard({ reg, onMove }) {
    const [open, setOpen] = useState(false);
    return (
        <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, overflow: 'hidden' }}>
            <Group gap="sm" p="sm" wrap="nowrap" align="center">
                <Avatar size={34} radius="md" color={avatarColor(reg.resident)}>{initials(reg.resident)}</Avatar>
                <Box style={{ flex: 1, minWidth: 0 }}>
                    <Group gap={6} wrap="nowrap">
                        <Text fz={14} fw={700} c={TXT} truncate>{reg.medication_name}</Text>
                        <Badge size="xs" variant="light" color="grape" radius="sm">Sch {reg.cd_schedule}</Badge>
                    </Group>
                    <Text fz={12} c={FAINT}>{reg.resident}</Text>
                </Box>
                <Box ta="right" style={{ flexShrink: 0 }}>
                    <Text fz={17} fw={800} c={TXT} style={{ fontVariantNumeric: 'tabular-nums' }}>{reg.balance}<Text span fz={11} c={FAINT} fw={500}> {reg.unit || ''}</Text></Text>
                    <Text fz={10} c={FAINT}>balance</Text>
                </Box>
            </Group>
            <Group gap={0} px="sm" pb="sm" justify="space-between">
                <Button size="xs" variant="subtle" color="gray" radius="xl" rightSection={<IconChevronDown size={13} style={{ transform: open ? 'rotate(180deg)' : 'none', transition: 'transform .15s' }} />} onClick={() => setOpen((o) => !o)}>
                    {reg.entries.length} movement{reg.entries.length === 1 ? '' : 's'}
                </Button>
                <Button size="xs" variant="light" color="grape" radius="xl" leftSection={<IconPlus size={13} />} onClick={() => onMove(reg)}>Record movement</Button>
            </Group>
            <Collapse in={open}>
                <Box style={{ borderTop: `1px solid ${HAIR}`, background: SOFT }}>
                    {reg.entries.length === 0 ? (
                        <Text fz={12.5} c={MUTED} p="sm" ta="center">No movements recorded yet. The balance shown is the opening stock.</Text>
                    ) : (
                        <ScrollArea.Autosize mah={280} type="hover">
                            {reg.entries.map((e) => {
                                const out = OUTGOING.includes(e.action);
                                return (
                                    <Box key={e.id} style={{ borderBottom: `1px solid ${HAIR}`, padding: '9px 14px' }}>
                                        <Group gap="sm" wrap="nowrap" align="flex-start">
                                            <ThemeIcon size={24} radius="md" variant="light" color={e.action === 'adjustment' ? 'blue' : out ? 'red' : 'teal'} style={{ flexShrink: 0, marginTop: 2 }}>
                                                {out ? <IconArrowDownRight size={13} /> : <IconArrowUpRight size={13} />}
                                            </ThemeIcon>
                                            <Box style={{ flex: 1, minWidth: 0 }}>
                                                <Group gap={6} wrap="nowrap">
                                                    <Text fz={12.5} fw={700} c={TXT}>{ACTION_LABEL[e.action] ?? e.action}</Text>
                                                    {e.quantity != null && <Text fz={12} c={out ? RED : TEAL} fw={650}>{out ? '−' : '+'}{e.quantity}</Text>}
                                                </Group>
                                                <Text fz={11} c={FAINT}>{e.date} {e.time} · witness {e.witness}{e.by ? ` · by ${e.by}` : ''}</Text>
                                                {e.notes && <Text fz={11} c={MUTED} mt={1}>{e.notes}</Text>}
                                            </Box>
                                            <Text fz={12.5} fw={700} c={TXT} style={{ flexShrink: 0, fontVariantNumeric: 'tabular-nums' }}>{e.balance_after}</Text>
                                        </Group>
                                    </Box>
                                );
                            })}
                        </ScrollArea.Autosize>
                    )}
                </Box>
            </Collapse>
        </Box>
    );
}

export default function ControlledDrugs({ registers = [], home }) {
    const [move, setMove] = useState(null);
    const flash = usePage().props?.flash;
    useEffect(() => {
        if (flash?.error) notifications.show({ color: 'red', title: 'Something went wrong', message: flash.error });
    }, [flash?.error]);

    const anyEntries = registers.some((r) => r.has_entries);

    return (
        <AppShell title="Controlled drugs" section="Medication 2">
            <Head title="Controlled drugs register — Medication 2" />
            <Box maw={760} mx="auto">
                <Group gap="sm" align="center" mb="md">
                    <ThemeIcon variant="light" color="grape" size={38} radius="md"><IconShieldLock size={20} /></ThemeIcon>
                    <Box>
                        <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Controlled drugs register</Text>
                        <Text fz={13} c={MUTED}>{home ? `${home} · ` : ''}witnessed running balance</Text>
                    </Box>
                </Group>

                {!anyEntries && registers.length > 0 && (
                    <Alert variant="light" color="grape" radius="md" mb="md" icon={<IconInfoCircle size={18} />}>
                        <Text fz={13}>Each balance below opens from the recorded stock. Administering a controlled drug on the round adds a witnessed entry automatically; receipts, disposals and recounts are recorded here.</Text>
                    </Alert>
                )}

                {registers.length === 0 ? (
                    <Box py={54} ta="center" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16 }}>
                        <ThemeIcon variant="light" color="gray" size={46} radius="xl" mx="auto" mb="sm"><IconShieldLock size={22} /></ThemeIcon>
                        <Text fz="sm" fw={700} c={TXT}>No controlled drugs</Text>
                        <Text fz="xs" c={MUTED}>No resident in this home has a controlled-drug prescription.</Text>
                    </Box>
                ) : (
                    <Box style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        {registers.map((r) => <RegisterCard key={r.mar_sheet_id} reg={r} onMove={setMove} />)}
                    </Box>
                )}

                <Text fz={11.5} c={FAINT} mt="md" ta="center">
                    Entries are append-only — a mistake is corrected with a new recount, never by editing history.
                </Text>
            </Box>

            {move && <MovementModal reg={move} onClose={() => setMove(null)} />}
        </AppShell>
    );
}
