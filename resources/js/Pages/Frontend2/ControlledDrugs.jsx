import { useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Badge, Button, ThemeIcon, TextInput, SimpleGrid, ScrollArea, Tooltip, ActionIcon,
} from '@mantine/core';
import {
    IconShieldLock, IconPill, IconTruckDelivery, IconTrash, IconArrowBackUp, IconAdjustments, IconActivity,
    IconSearch, IconPlus, IconDownload, IconCheck, IconShieldCheck, IconAlertCircle, IconX,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';

const STORE = '/frontend2/controlled-drugs';
const TXT = 'light-dark(#1E2F4D, #E8EBF1)';
const card = {
    background: 'light-dark(#ffffff, var(--mantine-color-dark-6))',
    borderRadius: 20,
    border: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))',
    boxShadow: '0 10px 30px -18px rgba(20,50,80,0.22)',
};
const num = (v) => (v === null || v === undefined ? '—' : v);

// Register action types → colour, icon, stock direction.
const ACTION = {
    administered: { label: 'Administered', color: 'red', hex: '#D14343', Icon: IconPill, flow: -1 },
    received: { label: 'Received', color: 'teal', hex: '#0CA678', Icon: IconTruckDelivery, flow: 1 },
    disposed: { label: 'Disposed', color: 'orange', hex: '#E8842B', Icon: IconTrash, flow: -1 },
    returned: { label: 'Returned', color: 'indigo', hex: '#4263EB', Icon: IconArrowBackUp, flow: 1 },
    adjustment: { label: 'Adjustment', color: 'grape', hex: '#AE3EC9', Icon: IconAdjustments, flow: 0 },
};
const metaOf = (t) => ACTION[t] ?? { label: t || '—', color: 'gray', hex: '#98A1AB', Icon: IconActivity, flow: 0 };

function StatCard({ label, value, dot, active, onClick, hint }) {
    const clickable = Boolean(onClick);
    const inner = (
        <Box onClick={onClick} role={clickable ? 'button' : undefined} tabIndex={clickable ? 0 : undefined}
            onKeyDown={clickable ? (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); } } : undefined}
            style={{
                ...card, padding: '18px 20px', cursor: clickable ? 'pointer' : 'default',
                borderColor: active ? dot : undefined,
                boxShadow: active ? `0 0 0 2px ${dot}, ${card.boxShadow}` : card.boxShadow,
                transition: 'transform .15s ease, box-shadow .15s ease',
            }}
            onMouseEnter={clickable ? (e) => { e.currentTarget.style.transform = 'translateY(-2px)'; e.currentTarget.style.boxShadow = '0 12px 26px -14px rgba(20,50,80,0.32)'; } : undefined}
            onMouseLeave={clickable ? (e) => { e.currentTarget.style.transform = 'none'; e.currentTarget.style.boxShadow = active ? `0 0 0 2px ${dot}, ${card.boxShadow}` : card.boxShadow; } : undefined}>
            <Group gap={8} wrap="nowrap" mb={10}>
                <Box w={9} h={9} style={{ borderRadius: '50%', background: dot, flexShrink: 0 }} />
                <Text fz={13} c="dimmed" truncate>{label}</Text>
            </Group>
            <Text fz={34} fw={800} c={TXT} lh={1}>{value}</Text>
        </Box>
    );
    return hint ? <Tooltip label={hint} withArrow openDelay={300}>{inner}</Tooltip> : inner;
}

export default function ControlledDrugs({ entries = [], residents = [], medsByClient = {}, lastBalances = {}, home }) {
    const railBelow = useMediaQuery('(max-width: 1000px)');
    const isSm = useMediaQuery('(max-width: 576px)');
    const laptop = useMediaQuery('(min-width: 1001px) and (max-width: 1440px)');
    const [addOpened, add] = useDisclosure(false);
    const [search, setSearch] = useState('');
    const [tab, setTab] = useState('all');

    const stats = useMemo(() => ({
        total: entries.length,
        administered: entries.filter((e) => e.action_type === 'administered').length,
        received: entries.filter((e) => e.action_type === 'received').length,
        out: entries.filter((e) => e.action_type === 'disposed' || e.action_type === 'returned').length,
        missingWitness: entries.filter((e) => !e.witness_name).length,
    }), [entries]);

    const shown = useMemo(() => {
        const q = search.trim().toLowerCase();
        return entries.filter((e) => {
            if (q && !`${e.client_name} ${e.medication_name}`.toLowerCase().includes(q)) return false;
            if (tab === 'nowitness') return !e.witness_name;
            if (tab === 'out') return e.action_type === 'disposed' || e.action_type === 'returned';
            if (tab !== 'all') return e.action_type === tab;
            return true;
        });
    }, [entries, search, tab]);

    const byAction = useMemo(() => {
        const keys = ['administered', 'received', 'disposed', 'returned', 'adjustment'];
        const arr = keys.map((k) => ({ k, label: ACTION[k].label, hex: ACTION[k].hex, count: entries.filter((e) => e.action_type === k).length }))
            .filter((x) => x.count > 0).sort((a, b) => b.count - a.count);
        const max = arr.reduce((n, x) => Math.max(n, x.count), 1);
        return { arr, max };
    }, [entries]);

    const exportCsv = () => {
        const head = ['Medication', 'CD schedule', 'Resident', 'Date', 'Time', 'Action', 'Quantity', 'Balance', 'Witness', 'Recorded by'];
        const esc = (v) => `"${String(v ?? '').replace(/"/g, '""')}"`;
        const lines = shown.map((e) => [e.medication_name, e.cd_schedule, e.client_name, e.entry_date, e.entry_time, metaOf(e.action_type).label, e.dose_quantity, e.balance_after, e.witness_name, e.created_by].map(esc).join(','));
        const blob = new Blob([[head.join(','), ...lines].join('\n')], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'controlled-drugs-register.csv';
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 0);
    };

    const TABS = [
        { k: 'all', l: `All ${stats.total}` },
        { k: 'administered', l: `Administered ${stats.administered}` },
        { k: 'received', l: `Received ${stats.received}` },
        { k: 'out', l: `Out ${stats.out}` },
        { k: 'adjustment', l: `Adjustment ${entries.filter((e) => e.action_type === 'adjustment').length}` },
    ];

    return (
        <AppShell title="Controlled drugs">
            <Head title="Controlled drugs" />
            <Box px={{ base: 0, sm: 10 }} pb={14} style={{ zoom: laptop ? 0.9 : undefined }}>
                {/* Header */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb={20}>
                    <Group gap={14} wrap="nowrap">
                        <ThemeIcon size={46} radius={14} style={{ background: 'light-dark(#F4EAFB, rgba(174,62,201,0.18))', color: '#AE3EC9' }}><IconShieldLock size={24} stroke={1.8} /></ThemeIcon>
                        <Box>
                            <Text fz={20} fw={800} c={TXT} lh={1.15}>Controlled drugs</Text>
                            <Text fz={12.5} c="dimmed">{['Append-only register', home, `${stats.total} entries`].filter(Boolean).join(' · ')}</Text>
                        </Box>
                    </Group>
                    <Group gap={10} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <Button variant="default" radius={12} leftSection={<IconDownload size={16} />} onClick={exportCsv}>Export</Button>
                        <Button radius={12} leftSection={<IconPlus size={16} />} onClick={add.open}
                            style={{ background: 'light-dark(#13233F, #1F9E93)', paddingInline: 18, boxShadow: 'light-dark(0 8px 18px rgba(19,35,63,0.28), 0 8px 18px rgba(31,158,147,0.35))' }}>Add entry</Button>
                    </Group>
                </Group>

                {/* Stat cards */}
                <SimpleGrid cols={{ base: 2, md: 4 }} spacing={18} mb={20}>
                    <StatCard label="Entries" value={stats.total} dot="#AE3EC9" hint="Show all entries" active={tab === 'all'} onClick={() => setTab('all')} />
                    <StatCard label="Administered" value={stats.administered} dot={ACTION.administered.hex} hint="Filter to administered" active={tab === 'administered'} onClick={() => setTab(tab === 'administered' ? 'all' : 'administered')} />
                    <StatCard label="Received" value={stats.received} dot={ACTION.received.hex} hint="Filter to received" active={tab === 'received'} onClick={() => setTab(tab === 'received' ? 'all' : 'received')} />
                    <StatCard label="Disposed / returned" value={stats.out} dot={ACTION.disposed.hex} hint="Filter to disposed / returned" active={tab === 'out'} onClick={() => setTab(tab === 'out' ? 'all' : 'out')} />
                </SimpleGrid>

                {/* Register + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 20, alignItems: 'flex-start' }}>
                    <Box style={{ ...card, flex: '3 1 460px', minWidth: 0, padding: isSm ? '16px 14px' : '22px 24px' }}>
                        <Group justify="space-between" align="center" pb={14} wrap="wrap" gap="sm">
                            <Text fz={18} fw={800} c={TXT}>Register</Text>
                            <Group gap="sm" wrap="wrap">
                                <Group gap={3} style={{ background: 'light-dark(#EFE9DC, var(--mantine-color-dark-8))', borderRadius: 10, padding: 3 }}>
                                    {TABS.map((t) => (
                                        <Box key={t.k} component="button" onClick={() => setTab(t.k)}
                                            onMouseEnter={(e) => { if (tab !== t.k) e.currentTarget.style.background = 'light-dark(#E7DFCD, var(--mantine-color-dark-6))'; }}
                                            onMouseLeave={(e) => { if (tab !== t.k) e.currentTarget.style.background = 'transparent'; }}
                                            style={{
                                                border: 'none', cursor: 'pointer', borderRadius: 8, padding: '5px 11px', fontSize: 12.5, fontWeight: 600, whiteSpace: 'nowrap', transition: 'background .12s ease',
                                                background: tab === t.k ? 'light-dark(#ffffff, rgba(69,193,191,0.16))' : 'transparent', color: tab === t.k ? 'light-dark(#1E2F4D, #6FE0DB)' : 'light-dark(#667085, #94A2B8)',
                                                boxShadow: tab === t.k ? 'light-dark(0 1px 2px rgba(16,24,40,0.1), inset 0 0 0 1px rgba(69,193,191,0.5))' : 'none',
                                            }}>{t.l}</Box>
                                    ))}
                                </Group>
                                <TextInput placeholder="Search med or resident…" leftSection={<IconSearch size={16} />} value={search}
                                    onChange={(e) => setSearch(e.currentTarget.value)} radius="xl" w={{ base: '100%', sm: 210 }} />
                            </Group>
                        </Group>
                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={4} pb={10} c="dimmed" style={{ borderBottom: '1px solid light-dark(#EEF1F4, var(--mantine-color-dark-4))' }}>
                            <Text fz={10} fw={700} tt="uppercase" style={{ flex: '2 1 200px', letterSpacing: 0.6 }}>Medication · resident</Text>
                            <Text fz={10} fw={700} tt="uppercase" style={{ width: 128, letterSpacing: 0.6 }} visibleFrom="md">Action</Text>
                            <Text fz={10} fw={700} tt="uppercase" style={{ width: 70, letterSpacing: 0.6, textAlign: 'right' }}>Qty</Text>
                            <Text fz={10} fw={700} tt="uppercase" style={{ width: 66, letterSpacing: 0.6, textAlign: 'right' }} visibleFrom="sm">Balance</Text>
                            <Text fz={10} fw={700} tt="uppercase" style={{ width: 96, letterSpacing: 0.6 }} visibleFrom="md">Witness</Text>
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" c="dimmed" ta="center" py={48}>No register entries match.</Text>
                            : (
                            <ScrollArea.Autosize mah="calc(100vh - 340px)" type="auto" offsetScrollbars scrollbarSize={8}>
                                {shown.map((e, idx) => {
                                    const am = metaOf(e.action_type);
                                    const flowColor = am.flow < 0 ? '#D14343' : am.flow > 0 ? '#0CA678' : '#98A1AB';
                                    return (
                                        <Group key={e.id ?? idx} gap={14} wrap="nowrap" align="center" px={4} py={15}
                                            style={{ borderTop: idx ? '1px solid light-dark(#F3F5F8, var(--mantine-color-dark-5))' : 'none', borderRadius: 10, transition: 'background .15s' }}
                                            onMouseEnter={(ev) => { ev.currentTarget.style.background = 'light-dark(#FBF6EC, var(--mantine-color-dark-5))'; }}
                                            onMouseLeave={(ev) => { ev.currentTarget.style.background = 'transparent'; }}>
                                            <Group gap="sm" wrap="nowrap" style={{ flex: '2 1 200px', minWidth: 0 }}>
                                                <ThemeIcon variant="light" color={am.color} size={38} radius={11}><am.Icon size={19} /></ThemeIcon>
                                                <Box style={{ minWidth: 0 }}>
                                                    <Group gap={6} wrap="nowrap">
                                                        <Text fz={13.5} fw={700} c={TXT} truncate>{e.medication_name}</Text>
                                                        {e.cd_schedule && <Badge size="xs" color="grape" variant="light" radius="sm">{e.cd_schedule}</Badge>}
                                                    </Group>
                                                    <Text fz={11.5} c="dimmed" truncate>{[e.client_name, e.entry_date, e.entry_time].filter(Boolean).join(' · ')}</Text>
                                                </Box>
                                            </Group>
                                            <Box style={{ width: 128, flexShrink: 0 }} visibleFrom="md"><Badge color={am.color} variant="light" radius="sm" styles={{ label: { overflow: 'visible' } }}>{am.label}</Badge></Box>
                                            <Box style={{ width: 70, flexShrink: 0, textAlign: 'right' }}>
                                                <Text fz={14} fw={800} c={flowColor} lh={1}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                                                <Text fz={10} c="dimmed">{e.unit ?? 'dose'}</Text>
                                            </Box>
                                            <Box style={{ width: 66, flexShrink: 0, textAlign: 'right' }} visibleFrom="sm">
                                                <Text fz={13.5} fw={700} c={TXT} lh={1}>{e.balance_after ?? '—'}</Text>
                                                <Text fz={10} c="dimmed">balance</Text>
                                            </Box>
                                            <Box style={{ width: 96, flexShrink: 0 }} visibleFrom="md">
                                                {e.witness_name
                                                    ? <Group gap={5} wrap="nowrap"><IconCheck size={13} stroke={2.6} color="#1F8A5B" /><Text fz={11.5} fw={600} c="#1F8A5B" truncate>{e.witness_name}</Text></Group>
                                                    : <Group gap={5} wrap="nowrap"><IconX size={13} stroke={2.6} color="#C2660F" /><Text fz={11.5} fw={600} c="#C2660F">No witness</Text></Group>}
                                            </Box>
                                        </Group>
                                    );
                                })}
                            </ScrollArea.Autosize>
                            )}
                    </Box>

                    {/* Right rail */}
                    <Stack gap={20} align={railBelow ? (isSm ? 'stretch' : 'center') : 'stretch'} style={{ flex: railBelow ? '1 1 100%' : '1 1 300px', minWidth: 0, maxWidth: railBelow ? undefined : 340 }}>
                        {/* By action */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Text fz={16} fw={800} c={TXT} mb={16}>By action</Text>
                            <Stack gap={15}>
                                {byAction.arr.length === 0
                                    ? <Text fz="sm" c="dimmed">No entries yet.</Text>
                                    : byAction.arr.map((b) => (
                                        <Box key={b.k}>
                                            <Group justify="space-between" wrap="nowrap" mb={6}>
                                                <Text fz={13} fw={600} c={TXT} truncate>{b.label}</Text>
                                                <Text fz={13} fw={700} c="dimmed">{b.count}</Text>
                                            </Group>
                                            <Box style={{ height: 6, borderRadius: 999, background: 'light-dark(#EEF1F5, var(--mantine-color-dark-5))', overflow: 'hidden' }}>
                                                <Box style={{ width: `${(b.count / byAction.max) * 100}%`, height: '100%', borderRadius: 999, background: b.hex }} />
                                            </Box>
                                        </Box>
                                    ))}
                            </Stack>
                        </Box>

                        {/* Compliance */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Group gap={10} mb={10} wrap="nowrap">
                                <ThemeIcon size={34} radius={11} style={{ background: stats.missingWitness ? 'light-dark(#FBE9D7, rgba(194,102,15,0.18))' : 'light-dark(#E7F3E8, rgba(31,138,91,0.18))', color: stats.missingWitness ? '#C2660F' : '#1F8A5B' }}>
                                    {stats.missingWitness ? <IconAlertCircle size={19} stroke={1.8} /> : <IconCheck size={19} stroke={2.4} />}
                                </ThemeIcon>
                                <Text fz={16} fw={800} c={TXT}>{stats.missingWitness ? `${stats.missingWitness} without a witness` : 'All entries witnessed'}</Text>
                            </Group>
                            <Text fz={12.5} c="#7a8590" lh={1.5} mb={stats.missingWitness ? 14 : 0}>A second signatory must witness every controlled-drug administration. Entries missing one need review.</Text>
                            {stats.missingWitness > 0 && (
                                <Button fullWidth radius={12} onClick={() => setTab('nowitness')}
                                    style={{ background: 'light-dark(#FBE9D7, rgba(194,102,15,0.18))', color: '#B45309' }}>Review these</Button>
                            )}
                        </Box>

                        {/* Audit ready */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Group gap={10} mb={8} wrap="nowrap">
                                <ThemeIcon size={34} radius={11} style={{ background: 'light-dark(#E4F1F7, rgba(58,124,165,0.18))', color: '#3A7CA5' }}><IconShieldCheck size={19} stroke={1.8} /></ThemeIcon>
                                <Text fz={16} fw={800} c={TXT}>Audit ready</Text>
                            </Group>
                            <Text fz={12.5} c="#7a8590" lh={1.5}>The register is append-only with a running balance, witness and timestamp on every entry — export it for CQC and pharmacy audits.</Text>
                        </Box>
                    </Stack>
                </Box>
            </Box>

            <AddCdEntryModal opened={addOpened} onClose={add.close} residents={residents} medsByClient={medsByClient} lastBalances={lastBalances} action={STORE} />
        </AppShell>
    );
}
