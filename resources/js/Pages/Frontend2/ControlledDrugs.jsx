import { useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import { useDisclosure, useMediaQuery } from '@mantine/hooks';
import {
    Box, Group, Stack, Text, Button, ThemeIcon, TextInput, ScrollArea,
} from '@mantine/core';
import {
    IconShieldLock, IconPill, IconTruckDelivery, IconTrash, IconArrowBackUp, IconAdjustments, IconActivity,
    IconSearch, IconPlus, IconDownload, IconCheck, IconShieldCheck, IconAlertTriangle,
} from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';
import AddCdEntryModal from '@frontend/features/medications/AddCdEntryModal';
import { palette } from '@frontend/tokens';
import { HEADING_FONT } from '@frontend/lib/font';

const STORE = '/frontend2/controlled-drugs';

/* ── Design tokens — from the unified palette in frontend/tokens.js (single source) ── */
const INK = palette.ink;                            // primary text
const INK2 = palette.ink2;                          // secondary text
const FAINT = palette.faint;                        // captions / units
const IDLE_TAB = 'light-dark(#67707F, #909AAA)';    // inactive tab label
const LINE = palette.line;
const TABLE_BG = palette.tableBg;                    // soft blue wash (light) / flat (dark)
const numeric = { fontVariantNumeric: 'tabular-nums', fontFeatureSettings: '"tnum" 1' };

const card = {
    background: palette.cardBg,
    borderRadius: 18,
    border: '1px solid light-dark(#ECEEF2, var(--mantine-color-dark-4))',
    boxShadow: '0 1px 2px rgba(16,29,54,0.04), 0 18px 40px -30px rgba(16,29,54,0.42)',
};
const num = (v) => (v === null || v === undefined ? '—' : v);

// Register action types → muted, sophisticated hues (never fully saturated).
const ACTION = {
    administered: { label: 'Administered', Icon: IconPill, flow: -1, hex: '#B4544A' },
    received: { label: 'Received', Icon: IconTruckDelivery, flow: 1, hex: '#3E8E77' },
    disposed: { label: 'Disposed', Icon: IconTrash, flow: -1, hex: '#BF8A3C' },
    returned: { label: 'Returned', Icon: IconArrowBackUp, flow: 1, hex: '#4E6B9A' },
    adjustment: { label: 'Adjustment', Icon: IconAdjustments, flow: 0, hex: '#8A6FAE' },
};
const metaOf = (t) => ACTION[t] ?? { label: t || '—', Icon: IconActivity, flow: 0, hex: '#8A93A2' };
const flowHex = (flow) => (flow < 0 ? '#B4544A' : flow > 0 ? '#3E8E77' : '#8A93A2');

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
        { k: 'all', l: 'All', n: stats.total },
        { k: 'administered', l: 'Administered', n: stats.administered },
        { k: 'received', l: 'Received', n: stats.received },
        { k: 'out', l: 'Out', n: stats.out },
        { k: 'adjustment', l: 'Adjustment', n: entries.filter((e) => e.action_type === 'adjustment').length },
    ];

    return (
        <AppShell title="Controlled drugs">
            <Head title="Controlled drugs" />
            <Box px={{ base: 0, sm: 8 }} pb={16} style={{ zoom: laptop ? 0.9 : undefined }}>
                {/* Header */}
                <Group justify="space-between" align="center" wrap="wrap" gap="md" mb={22}>
                    <Group gap={14} wrap="nowrap">
                        <ThemeIcon size={44} radius={13} style={{ background: 'light-dark(#F4F6FA, rgba(255,255,255,0.05))', color: 'light-dark(#16233B, #C9D3E2)', border: '1px solid light-dark(#E7EBF1, rgba(255,255,255,0.08))' }}>
                            <IconShieldLock size={22} stroke={1.7} />
                        </ThemeIcon>
                        <Box>
                            <Text fz={21} fw={650} c={INK} lh={1.15} style={{ letterSpacing: -0.4, fontFamily: HEADING_FONT }}>Controlled drug register</Text>
                            <Text fz={12.5} c={INK2} mt={2}>{['Append-only record', home, `${stats.total} entries`].filter(Boolean).join('  ·  ')}</Text>
                        </Box>
                    </Group>
                    <Group gap={10} wrap="wrap" style={{ flex: isSm ? '1 1 100%' : undefined, justifyContent: isSm ? 'space-between' : undefined }}>
                        <Button variant="default" radius={11} leftSection={<IconDownload size={16} stroke={1.8} />} onClick={exportCsv}
                            styles={{ root: { fontWeight: 600, borderColor: 'light-dark(#E4E8EE, var(--mantine-color-dark-4))' } }}>Export</Button>
                        <Button radius={11} leftSection={<IconPlus size={16} stroke={2} />} onClick={add.open}
                            styles={{ root: { fontWeight: 600 } }}
                            style={{ background: palette.primaryBtn, paddingInline: 18, boxShadow: 'light-dark(0 10px 22px -10px rgba(22,35,59,0.55), 0 10px 22px -10px rgba(31,158,147,0.6))' }}>Add entry</Button>
                    </Group>
                </Group>

                {/* Register + rail */}
                <Box style={{ display: 'flex', flexWrap: 'wrap', gap: 20, alignItems: 'flex-start' }}>
                    <Box style={{ ...card, background: TABLE_BG, flex: '3 1 460px', minWidth: 0, padding: isSm ? '16px 14px' : '22px 24px' }}>
                        <Group justify="space-between" align="center" pb={16} wrap="wrap" gap="sm">
                            <Box>
                                <Text fz={16} fw={650} c={INK} style={{ letterSpacing: -0.2, fontFamily: HEADING_FONT }}>Register</Text>
                                <Text fz={12} c={FAINT} mt={1}>{shown.length} {shown.length === 1 ? 'entry' : 'entries'} shown</Text>
                            </Box>
                            <Group gap="sm" wrap="wrap">
                                <Group gap={2} style={{ background: 'light-dark(#F1F3F7, var(--mantine-color-dark-7))', borderRadius: 11, padding: 3, border: '1px solid light-dark(#E9ECF1, transparent)' }}>
                                    {TABS.map((t) => {
                                        const on = tab === t.k;
                                        return (
                                            <Box key={t.k} component="button" onClick={() => setTab(t.k)}
                                                onMouseEnter={(e) => { if (!on) e.currentTarget.style.color = 'light-dark(#16233B, #E3E8EF)'; }}
                                                onMouseLeave={(e) => { if (!on) e.currentTarget.style.color = IDLE_TAB; }}
                                                style={{
                                                    border: 'none', cursor: 'pointer', borderRadius: 8, padding: '5px 12px', fontSize: 12.5, fontWeight: 600, whiteSpace: 'nowrap',
                                                    transition: 'color .14s ease, background .14s ease, box-shadow .14s ease',
                                                    background: on ? 'light-dark(#FFFFFF, var(--mantine-color-dark-5))' : 'transparent',
                                                    color: on ? 'light-dark(#16233B, #FFFFFF)' : IDLE_TAB,
                                                    boxShadow: on ? '0 1px 2px rgba(16,29,54,0.10), 0 4px 10px -6px rgba(16,29,54,0.25)' : 'none',
                                                }}>
                                                {t.l}<span style={{ ...numeric, opacity: 0.5, marginLeft: 6, fontWeight: 600 }}>{t.n}</span>
                                            </Box>
                                        );
                                    })}
                                </Group>
                                <TextInput placeholder="Search med or resident…" leftSection={<IconSearch size={15} stroke={1.8} />} value={search}
                                    onChange={(e) => setSearch(e.currentTarget.value)} radius={10} w={{ base: '100%', sm: 216 }}
                                    styles={{ input: { borderColor: 'light-dark(#E6EAF0, var(--mantine-color-dark-4))' } }} />
                            </Group>
                        </Group>
                        {/* Column header */}
                        <Group gap={14} wrap="nowrap" px={4} pb={9} style={{ borderBottom: `1px solid ${LINE}` }}>
                            <Text fz={10} fw={700} c={FAINT} tt="uppercase" style={{ flex: '2 1 200px', letterSpacing: 0.7 }}>Medication · resident</Text>
                            <Text fz={10} fw={700} c={FAINT} tt="uppercase" style={{ width: 128, letterSpacing: 0.7 }} visibleFrom="md">Action</Text>
                            <Text fz={10} fw={700} c={FAINT} tt="uppercase" style={{ width: 70, letterSpacing: 0.7, textAlign: 'right' }}>Qty</Text>
                            <Text fz={10} fw={700} c={FAINT} tt="uppercase" style={{ width: 66, letterSpacing: 0.7, textAlign: 'right' }} visibleFrom="sm">Balance</Text>
                            <Text fz={10} fw={700} c={FAINT} tt="uppercase" style={{ width: 96, letterSpacing: 0.7 }} visibleFrom="md">Witness</Text>
                        </Group>
                        {shown.length === 0
                            ? <Text fz="sm" c={FAINT} ta="center" py={52}>No register entries match.</Text>
                            : (
                            <ScrollArea.Autosize mah="calc(100vh - 344px)" type="auto" offsetScrollbars scrollbarSize={8}>
                                {shown.map((e, idx) => {
                                    const am = metaOf(e.action_type);
                                    const fh = flowHex(am.flow);
                                    return (
                                        <Group key={e.id ?? idx} gap={14} wrap="nowrap" align="center" px={6} py={14}
                                            style={{ borderTop: idx ? `1px solid ${LINE}` : 'none', borderRadius: 12, transition: 'background .15s' }}
                                            onMouseEnter={(ev) => { ev.currentTarget.style.background = 'light-dark(#F8FAFC, var(--mantine-color-dark-5))'; }}
                                            onMouseLeave={(ev) => { ev.currentTarget.style.background = 'transparent'; }}>
                                            <Group gap={13} wrap="nowrap" style={{ flex: '2 1 200px', minWidth: 0 }}>
                                                <Box style={{ width: 38, height: 38, borderRadius: 11, flexShrink: 0, display: 'grid', placeItems: 'center', background: 'light-dark(#F4F6F9, rgba(255,255,255,0.05))', border: '1px solid light-dark(#EBEEF3, rgba(255,255,255,0.07))', color: am.hex }}>
                                                    <am.Icon size={18} stroke={1.8} />
                                                </Box>
                                                <Box style={{ minWidth: 0 }}>
                                                    <Group gap={7} wrap="nowrap">
                                                        <Text fz={13.5} fw={600} c={INK} truncate>{e.medication_name}</Text>
                                                        {e.cd_schedule && (
                                                            <Box style={{ flexShrink: 0, fontSize: 10, fontWeight: 700, letterSpacing: 0.3, color: '#8A6FAE', background: 'light-dark(#F3EFFA, rgba(138,111,174,0.18))', borderRadius: 6, padding: '1px 6px' }}>{e.cd_schedule}</Box>
                                                        )}
                                                    </Group>
                                                    <Text fz={11.5} c={FAINT} truncate mt={1}>{[e.client_name, e.entry_date, e.entry_time].filter(Boolean).join('  ·  ')}</Text>
                                                </Box>
                                            </Group>
                                            <Box style={{ width: 128, flexShrink: 0 }} visibleFrom="md">
                                                <Group gap={7} wrap="nowrap">
                                                    <Box w={6} h={6} style={{ borderRadius: '50%', background: am.hex, flexShrink: 0 }} />
                                                    <Text fz={12.5} fw={600} c={INK2} truncate>{am.label}</Text>
                                                </Group>
                                            </Box>
                                            <Box style={{ width: 70, flexShrink: 0, textAlign: 'right' }}>
                                                <Text fz={14} fw={650} c={fh} lh={1} style={numeric}>{am.flow ? (am.flow > 0 ? '+' : '−') : ''}{num(e.dose_quantity)}</Text>
                                                <Text fz={10} c={FAINT} mt={2}>{e.unit ?? 'dose'}</Text>
                                            </Box>
                                            <Box style={{ width: 66, flexShrink: 0, textAlign: 'right' }} visibleFrom="sm">
                                                <Text fz={13.5} fw={600} c={INK} lh={1} style={numeric}>{e.balance_after ?? '—'}</Text>
                                                <Text fz={10} c={FAINT} mt={2}>balance</Text>
                                            </Box>
                                            <Box style={{ width: 96, flexShrink: 0 }} visibleFrom="md">
                                                {e.witness_name
                                                    ? <Group gap={5} wrap="nowrap"><IconCheck size={13} stroke={2.4} color="#3E8E77" /><Text fz={11.5} fw={600} c="#3E8E77" truncate>{e.witness_name}</Text></Group>
                                                    : <Group gap={5} wrap="nowrap"><IconAlertTriangle size={12} stroke={2} color="#BF8A3C" /><Text fz={11.5} fw={600} c="#BF8A3C">No witness</Text></Group>}
                                            </Box>
                                        </Group>
                                    );
                                })}
                            </ScrollArea.Autosize>
                            )}
                    </Box>

                    {/* Right rail */}
                    <Stack gap={18} align={railBelow ? (isSm ? 'stretch' : 'center') : 'stretch'} style={{ flex: railBelow ? '1 1 100%' : '1 1 300px', minWidth: 0, maxWidth: railBelow ? undefined : 340 }}>
                        {/* By action */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Text fz={13.5} fw={650} c={INK} mb={18} style={{ letterSpacing: -0.1, fontFamily: HEADING_FONT }}>Activity by action</Text>
                            <Stack gap={16}>
                                {byAction.arr.length === 0
                                    ? <Text fz="sm" c={FAINT}>No entries yet.</Text>
                                    : byAction.arr.map((b) => (
                                        <Box key={b.k}>
                                            <Group justify="space-between" wrap="nowrap" mb={7}>
                                                <Group gap={7} wrap="nowrap">
                                                    <Box w={6} h={6} style={{ borderRadius: '50%', background: b.hex, flexShrink: 0 }} />
                                                    <Text fz={12.5} fw={600} c={INK2} truncate>{b.label}</Text>
                                                </Group>
                                                <Text fz={12.5} fw={650} c={INK} style={numeric}>{b.count}</Text>
                                            </Group>
                                            <Box style={{ height: 5, borderRadius: 999, background: 'light-dark(#F0F2F6, var(--mantine-color-dark-5))', overflow: 'hidden' }}>
                                                <Box style={{ width: `${(b.count / byAction.max) * 100}%`, height: '100%', borderRadius: 999, background: b.hex, opacity: 0.9 }} />
                                            </Box>
                                        </Box>
                                    ))}
                            </Stack>
                        </Box>

                        {/* Compliance */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Group gap={11} mb={11} wrap="nowrap" align="flex-start">
                                <ThemeIcon size={34} radius={11} style={{ background: stats.missingWitness ? 'light-dark(#FBF2E4, rgba(191,138,60,0.16))' : 'light-dark(#EAF4EF, rgba(62,142,119,0.16))', color: stats.missingWitness ? '#BF8A3C' : '#3E8E77', border: '1px solid transparent' }}>
                                    {stats.missingWitness ? <IconAlertTriangle size={18} stroke={1.8} /> : <IconCheck size={18} stroke={2.2} />}
                                </ThemeIcon>
                                <Text fz={14.5} fw={650} c={INK} lh={1.25} style={{ letterSpacing: -0.1, fontFamily: HEADING_FONT }}>{stats.missingWitness ? `${stats.missingWitness} without a witness` : 'All entries witnessed'}</Text>
                            </Group>
                            <Text fz={12.5} c={INK2} lh={1.55} mb={stats.missingWitness ? 15 : 0}>A second signatory must witness every controlled-drug administration. Entries missing one need review.</Text>
                            {stats.missingWitness > 0 && (
                                <Button fullWidth radius={10} onClick={() => setTab('nowitness')} variant="light"
                                    styles={{ root: { fontWeight: 600 } }}
                                    style={{ background: 'light-dark(#FBF2E4, rgba(191,138,60,0.16))', color: '#A26E22' }}>Review these</Button>
                            )}
                        </Box>

                        {/* Audit ready */}
                        <Box style={{ ...card, width: '100%', maxWidth: railBelow && !isSm ? 460 : undefined, padding: '22px 24px' }}>
                            <Group gap={11} mb={9} wrap="nowrap" align="flex-start">
                                <ThemeIcon size={34} radius={11} style={{ background: 'light-dark(#EAF0F6, rgba(78,107,154,0.16))', color: '#4E6B9A', border: '1px solid transparent' }}><IconShieldCheck size={18} stroke={1.8} /></ThemeIcon>
                                <Text fz={14.5} fw={650} c={INK} style={{ letterSpacing: -0.1, fontFamily: HEADING_FONT }}>Audit ready</Text>
                            </Group>
                            <Text fz={12.5} c={INK2} lh={1.55}>The register is append-only with a running balance, witness and timestamp on every entry — export it for CQC and pharmacy audits.</Text>
                        </Box>
                    </Stack>
                </Box>
            </Box>

            <AddCdEntryModal opened={addOpened} onClose={add.close} residents={residents} medsByClient={medsByClient} lastBalances={lastBalances} action={STORE} />
        </AppShell>
    );
}
