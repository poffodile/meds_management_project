import { Head } from '@inertiajs/react';
import { Box, Group, Text, Button, Badge, Table } from '@mantine/core';
import { IconPrinter, IconArrowLeft } from '@tabler/icons-react';

// MAR outcome codes → label + colour (matches the round's record codes).
const CODE = {
    A: { label: 'Given', color: 'green' },
    S: { label: 'Sleeping', color: 'teal' },
    R: { label: 'Refused', color: 'red' },
    W: { label: 'Withheld', color: 'orange' },
    N: { label: 'Not available', color: 'gray' },
    O: { label: 'Omitted', color: 'grape' },
};

const dayLabel = (iso) => {
    const d = new Date(`${iso}T00:00:00`);
    return { num: d.getDate(), wd: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'][d.getDay()] };
};

export default function MarReport({ resident, meds = [], days = [], from, to }) {
    return (
        <>
            <Head title={`MAR — ${resident?.name ?? ''}`} />
            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    @page { size: landscape; margin: 9mm; }
                }
                body { background: #fff; }
            `}</style>

            <Box p="lg" style={{ maxWidth: 1500, margin: '0 auto' }}>
                <Group justify="space-between" mb="md" className="no-print">
                    <Button variant="subtle" color="gray" leftSection={<IconArrowLeft size={16} />} onClick={() => window.history.back()}>Back</Button>
                    <Button leftSection={<IconPrinter size={16} />} onClick={() => window.print()}>Print</Button>
                </Group>

                <Group justify="space-between" align="flex-end" mb="sm" wrap="wrap">
                    <Box>
                        <Text fz={24} fw={800}>Medication Administration Record</Text>
                        <Text c="dimmed">{resident?.name ?? '—'}{resident?.dob ? ` · DOB ${resident.dob}` : ''}</Text>
                    </Box>
                    <Text c="dimmed" fz="sm">{from} → {to}</Text>
                </Group>

                <Group gap="xs" mb="md">
                    {Object.entries(CODE).map(([k, v]) => (
                        <Badge key={k} variant="light" color={v.color} radius="sm" tt="none">{k} = {v.label}</Badge>
                    ))}
                </Group>

                {meds.length === 0 ? (
                    <Text c="dimmed">No active medications for this resident in the selected range.</Text>
                ) : (
                    <Box style={{ overflowX: 'auto' }}>
                        <Table withTableBorder withColumnBorders striped="even" stickyHeader style={{ fontSize: 12 }}>
                            <Table.Thead>
                                <Table.Tr>
                                    <Table.Th style={{ minWidth: 170 }}>Medication</Table.Th>
                                    <Table.Th style={{ minWidth: 56 }}>Slot</Table.Th>
                                    {days.map((d) => {
                                        const dl = dayLabel(d);
                                        return (
                                            <Table.Th key={d} ta="center" style={{ minWidth: 26, padding: '4px 2px' }}>
                                                <div style={{ fontWeight: 700 }}>{dl.num}</div>
                                                <div style={{ fontSize: 9, color: '#999' }}>{dl.wd}</div>
                                            </Table.Th>
                                        );
                                    })}
                                </Table.Tr>
                            </Table.Thead>
                            <Table.Tbody>
                                {meds.flatMap((m) => m.slots.map((s, si) => (
                                    <Table.Tr key={`${m.medication_name}-${s.slot}-${si}`}>
                                        {si === 0 && (
                                            <Table.Td rowSpan={m.slots.length} style={{ verticalAlign: 'top' }}>
                                                <Text fz="xs" fw={700}>{m.medication_name}</Text>
                                                <Text fz={10} c="dimmed">{m.dosage}{m.is_controlled ? ' · CD' : ''}{m.as_required ? ' · PRN' : ''}</Text>
                                            </Table.Td>
                                        )}
                                        <Table.Td><Text fz="xs" fw={600}>{s.slot}</Text></Table.Td>
                                        {s.cells.map((c, ci) => (
                                            <Table.Td key={ci} ta="center"
                                                style={{ color: c ? `var(--mantine-color-${CODE[c]?.color ?? 'gray'}-7)` : '#ccc', fontWeight: 700 }}>
                                                {c ?? '·'}
                                            </Table.Td>
                                        ))}
                                    </Table.Tr>
                                )))}
                            </Table.Tbody>
                        </Table>
                    </Box>
                )}
            </Box>
        </>
    );
}
