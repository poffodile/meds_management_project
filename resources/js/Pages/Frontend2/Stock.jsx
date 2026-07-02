import { useState, useMemo } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { useDisclosure } from '@mantine/hooks';
import {
    Box, Group, Text, TextInput, Badge, Button, ThemeIcon, SimpleGrid, Progress,
    SegmentedControl, Modal, Select, NumberInput, Textarea, Checkbox, Stack,
} from '@mantine/core';
import {
    IconSearch, IconPlus, IconBox, IconAlertTriangle, IconCircleX, IconClock, IconCalendar, IconPill, IconShieldLock,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import { useRole } from '@frontend/lib/role';

const ADJUST = '/frontend2/stock/adjust';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 18,
    border: '1px solid light-dark(var(--mantine-color-gray-2), var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,24,40,0.04)',
};
const num = (v, unit) => (v === null || v === undefined ? '—' : `${v}${unit ? ' ' + unit : ''}`);
const isOut = (m) => m.stock_level !== null && m.stock_level !== undefined && Number(m.stock_level) === 0;

function bucketOf(m) {
    if (m.expired) return 'expired';
    if (isOut(m)) return 'out';
    if (m.low) return 'low';
    if (m.expiring_soon) return 'expiring';
    return 'healthy';
}
const STATUS = {
    healthy: { label: 'In stock', color: 'teal' },
    expiring: { label: 'Expiring soon', color: 'violet' },
    low: { label: 'Low stock', color: 'yellow' },
    out: { label: 'Out of stock', color: 'orange' },
    expired: { label: 'Expired', color: 'red' },
};

function stockBar(m) {
    const stock = m.stock_level;
    if (stock === null || stock === undefined) return { pct: 0, color: 'gray' };
    const n = Number(stock);
    const ref = m.reorder_level ? m.reorder_level * 2 : Math.max(n, 30);
    const pct = Math.min(100, Math.max(4, Math.round((n / ref) * 100)));
    let color = 'teal';
    if (m.expired || isOut(m)) color = 'red';
    else if (pct < 25 || n <= 5) color = 'orange';
    else if (pct < 45 || n <= 12) color = 'yellow';
    return { pct, color };
}

function Metric({ icon: Icon, label, value, color }) {
    return (
        <Box style={{ ...card, padding: 14 }}>
            <Group gap={10} wrap="nowrap">
                <ThemeIcon variant="light" color={color} size={38} radius="md"><Icon size={20} stroke={1.7} /></ThemeIcon>
                <Box><Text fz={24} fw={800} lh={1}>{value}</Text><Text fz="xs" c="dimmed">{label}</Text></Box>
            </Group>
        </Box>
    );
}

function AdjustModal({ opened, onClose, meds }) {
    const form = useForm({
        mar_sheet_id: '', transaction_type: 'received', quantity: '', expiry_date: '',
        is_controlled: false, cd_schedule: '', reason: '', disposal_method: '', witness_name: '', notes: '',
    });
    const medOptions = meds.map((m) => ({ value: String(m.id), label: m.medication_name + (m.resident ? ` — ${m.resident}` : '') }));
    const onMedChange = (value) => {
        form.setData('mar_sheet_id', value ?? '');
        const med = meds.find((m) => String(m.id) === value);
        if (med) { form.setData('is_controlled', !!med.is_controlled); form.setData('cd_schedule', med.cd_schedule ?? ''); }
    };
    const submit = () => form.post(ADJUST, { preserveScroll: true, onSuccess: () => { form.reset(); onClose(); } });

    return (
        <Modal opened={opened} onClose={onClose} title={<Text fw={800} fz="lg">Adjust stock</Text>} radius={18} centered size="md">
            <Stack gap="sm">
                <Select label="Medication" placeholder="Pick a medication" data={medOptions} value={form.data.mar_sheet_id}
                    onChange={onMedChange} error={form.errors.mar_sheet_id} searchable required />
                <Select label="Type" data={[
                    { value: 'received', label: 'Received (stock in)' }, { value: 'disposed', label: 'Disposed' },
                    { value: 'returned', label: 'Returned' }, { value: 'correction', label: 'Correction' },
                ]} value={form.data.transaction_type} onChange={(v) => form.setData('transaction_type', v)} required />
                <NumberInput label="Quantity" placeholder="e.g. 28" min={0} value={form.data.quantity}
                    onChange={(v) => form.setData('quantity', v)} error={form.errors.quantity} />
                <TextInput label="Expiry date" type="date" value={form.data.expiry_date}
                    onChange={(e) => form.setData('expiry_date', e.currentTarget.value)} error={form.errors.expiry_date} />
                <Checkbox label="Controlled drug" checked={form.data.is_controlled}
                    onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)} />
                <TextInput label="Witness name" value={form.data.witness_name}
                    onChange={(e) => form.setData('witness_name', e.currentTarget.value)} />
                <Textarea label="Notes" autosize minRows={2} value={form.data.notes}
                    onChange={(e) => form.setData('notes', e.currentTarget.value)} />
                <Group justify="flex-end" mt="xs">
                    <Button variant="default" radius="xl" onClick={onClose}>Cancel</Button>
                    <Button radius="xl" color="indigo" loading={form.processing} onClick={submit}>Save</Button>
                </Group>
            </Stack>
        </Modal>
    );
}

export default function Stock({ meds = [], stats = {} }) {
    const role = useRole();
    const isManager = role === 'manager';
    const [filter, setFilter] = useState('all');
    const [query, setQuery] = useState('');
    const [adjustOpened, adjust] = useDisclosure(false);

    const counts = useMemo(() => {
        const c = { healthy: 0, expiring: 0, low: 0, out: 0, expired: 0 };
        meds.forEach((m) => { c[bucketOf(m)]++; });
        return c;
    }, [meds]);

    const filtered = meds.filter((m) => {
        if (filter !== 'all' && bucketOf(m) !== filter) return false;
        const q = query.trim().toLowerCase();
        if (q && !`${m.medication_name} ${m.resident ?? ''}`.toLowerCase().includes(q)) return false;
        return true;
    });

    return (
        <AppShell title="Medication stock">
            <Head title="Medication stock" />
            <Box>
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb="lg">
                    <Box>
                        <Text fz={26} fw={800} lh={1.15}>Medication stock</Text>
                        <Text c="dimmed" fz="sm">{meds.length} item{meds.length === 1 ? '' : 's'} tracked.</Text>
                    </Box>
                    {isManager
                        ? <Button radius="xl" color="indigo" leftSection={<IconPlus size={16} />} onClick={adjust.open}>Adjust stock</Button>
                        : <Badge variant="light" color="gray" size="lg" radius="sm">View only</Badge>}
                </Group>

                <SimpleGrid cols={{ base: 2, sm: 5 }} spacing="md" mb="lg">
                    <Metric icon={IconBox} label="All items" value={stats.total ?? meds.length} color="indigo" />
                    <Metric icon={IconAlertTriangle} label="Low" value={stats.low ?? counts.low} color="yellow" />
                    <Metric icon={IconCircleX} label="Out" value={stats.out_of_stock ?? counts.out} color="orange" />
                    <Metric icon={IconClock} label="Expiring" value={stats.expiring_soon ?? counts.expiring} color="violet" />
                    <Metric icon={IconCalendar} label="Expired" value={stats.expired ?? counts.expired} color="red" />
                </SimpleGrid>

                <Box style={card}>
                    <Group justify="space-between" align="center" px="md" pt="md" pb="sm" wrap="wrap" gap="sm">
                        <SegmentedControl radius="xl" value={filter} onChange={setFilter}
                            data={[{ label: 'All', value: 'all' }, { label: 'Low', value: 'low' }, { label: 'Out', value: 'out' }, { label: 'Expiring', value: 'expiring' }, { label: 'Expired', value: 'expired' }]} />
                        <TextInput placeholder="Search meds or resident…" leftSection={<IconSearch size={16} />} value={query}
                            onChange={(e) => setQuery(e.currentTarget.value)} radius="xl" w={{ base: '100%', sm: 240 }} />
                    </Group>
                    {filtered.length === 0
                        ? <Text fz="sm" c="dimmed" ta="center" py={48}>No medications match.</Text>
                        : filtered.map((m) => {
                            const bar = stockBar(m);
                            const st = STATUS[bucketOf(m)];
                            return (
                                <Group key={m.id} gap="md" wrap="nowrap" align="center" px="md" py={12} style={{ borderTop: '1px solid light-dark(var(--mantine-color-gray-1), var(--mantine-color-dark-5))' }}>
                                    <ThemeIcon variant="light" color={m.is_controlled ? 'grape' : 'indigo'} size={38} radius="md">
                                        {m.is_controlled ? <IconShieldLock size={19} /> : <IconPill size={19} />}
                                    </ThemeIcon>
                                    <Box style={{ flex: '2 1 220px', minWidth: 0 }}>
                                        <Group gap={6} wrap="nowrap">
                                            <Text fz="sm" fw={700} truncate>{m.medication_name}</Text>
                                            {m.is_controlled && <Badge size="xs" color="grape" variant="light" radius="sm">CD</Badge>}
                                        </Group>
                                        <Text fz="xs" c="dimmed" truncate>{m.resident ?? '—'}</Text>
                                    </Box>
                                    <Box style={{ flex: '1 1 140px', minWidth: 90 }} visibleFrom="sm"><Progress value={bar.pct} color={bar.color} radius="xl" size="sm" /></Box>
                                    <Box style={{ width: 64, flexShrink: 0, textAlign: 'right' }}>
                                        <Text fz="sm" fw={800} lh={1}>{m.stock_level ?? '—'}</Text>
                                        <Text fz={10} c="dimmed">{m.unit ?? 'units'}</Text>
                                    </Box>
                                    <Box style={{ flexShrink: 0 }}><Badge color={st.color} variant="light" radius="sm">{st.label}</Badge></Box>
                                </Group>
                            );
                        })}
                    <Box py={4} />
                </Box>
            </Box>

            <AdjustModal opened={adjustOpened} onClose={adjust.close} meds={meds} />
        </AppShell>
    );
}
