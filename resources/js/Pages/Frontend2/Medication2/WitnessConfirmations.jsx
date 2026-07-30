import { router, Head, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { Box, Group, Text, Badge, Button, ThemeIcon } from '@mantine/core';
import { notifications } from '@mantine/notifications';
import { IconShieldCheck, IconPill, IconCheck } from '@tabler/icons-react';
import AppShell from '@frontend2/Layouts/AppShell';

const TXT = 'light-dark(#13233F, #E9EDF4)';
const MUTED = 'light-dark(#4A5A72, #A6B3C6)';
const FAINT = 'light-dark(#586780, #9BA9BD)';
const TEAL = 'light-dark(#1B9C90, #3BC3B4)';
const HAIR = 'light-dark(#E1E7F0, #22303F)';
const SURFACE = 'light-dark(#FFFFFF, #14202F)';
const SOFT = 'light-dark(#F7F9FC, #101A27)';

const ACTION_LABEL = {
    administered: 'Administered',
    received: 'Received',
    disposed: 'Disposed',
    returned: 'Returned',
    adjustment: 'Recount',
};

const ENDPOINT = '/frontend2/medication-2/witness-confirmations';

/** One signature awaiting the current user's confirmation. */
function SignatureCard({ item }) {
    const confirm = () => router.post(`${ENDPOINT}/${item.id}/confirm`, {}, {
        preserveScroll: true,
        onSuccess: () => notifications.show({ color: 'teal', message: 'Signature confirmed.' }),
        onError: (e) => notifications.show({ color: 'red', title: 'Not confirmed', message: Object.values(e ?? {})[0] || 'Could not confirm this signature.' }),
    });

    const qty = item.dose_quantity != null ? `${item.dose_quantity}${item.unit ? ` ${item.unit}` : ''}` : null;

    return (
        <Box style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16, padding: 16 }}>
            <Group justify="space-between" wrap="nowrap" align="flex-start">
                <Group gap="sm" wrap="nowrap" align="flex-start" style={{ minWidth: 0 }}>
                    <ThemeIcon variant="light" color="grape" size={38} radius="md" style={{ flexShrink: 0 }}><IconPill size={18} /></ThemeIcon>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap={7} wrap="nowrap">
                            <Text fz="sm" fw={700} c={TXT} truncate>{item.medication_name}</Text>
                            <Badge size="xs" variant="light" color="grape" radius="sm">CD</Badge>
                        </Group>
                        <Text fz={12.5} c={MUTED}>
                            {[
                                ACTION_LABEL[item.action_type] ?? item.action_type,
                                qty,
                                item.client_name,
                            ].filter(Boolean).join(' · ')}
                        </Text>
                        <Text fz={11.5} c={FAINT} mt={2}>
                            {[
                                item.recorded_by && `recorded by ${item.recorded_by}`,
                                item.entry_date,
                                item.entry_time,
                            ].filter(Boolean).join(' · ')}
                        </Text>
                        <Text fz={11.5} c={MUTED} mt={4}>You were named as the witness.</Text>
                    </Box>
                </Group>
                <Button size="xs" radius="xl" color="teal" leftSection={<IconCheck size={14} />} onClick={confirm} style={{ flexShrink: 0 }}>
                    Confirm
                </Button>
            </Group>
        </Box>
    );
}

export default function WitnessConfirmations({ pending = [] }) {
    const flash = usePage().props?.flash;
    useEffect(() => {
        if (flash?.error) notifications.show({ color: 'red', title: 'Something went wrong', message: flash.error });
    }, [flash?.error]);

    return (
        <AppShell title="Signatures awaiting you" section="Medication 2">
            <Head title="Witness confirmations — Medication 2" />
            <Box maw={680} mx="auto">
                <Group gap="sm" align="center" mb="md">
                    <ThemeIcon variant="light" color="grape" size={38} radius="md"><IconShieldCheck size={20} /></ThemeIcon>
                    <Box>
                        <Text fz={22} fw={800} c={TXT} style={{ letterSpacing: '-0.02em' }}>Signatures awaiting you</Text>
                        <Text fz={13} c={MUTED}>Controlled drugs where you were named as the witness — confirm you were there.</Text>
                    </Box>
                </Group>

                {pending.length === 0 ? (
                    <Box py={54} ta="center" style={{ background: SURFACE, border: `1px solid ${HAIR}`, borderRadius: 16 }}>
                        <ThemeIcon variant="light" color="teal" size={46} radius="xl" mx="auto" mb="sm"><IconCheck size={22} /></ThemeIcon>
                        <Text fz="sm" fw={700} c={TXT}>Nothing to confirm</Text>
                        <Text fz="xs" c={MUTED}>No controlled-drug signatures are waiting for you.</Text>
                    </Box>
                ) : (
                    <Box style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        {pending.map((item) => <SignatureCard key={item.id} item={item} />)}
                    </Box>
                )}

                <Box mt="md" p="sm" style={{ background: SOFT, border: `1px solid ${HAIR}`, borderRadius: 12 }}>
                    <Text fz={11.5} c={FAINT}>
                        Confirming records that you witnessed this controlled-drug movement. If you can’t confirm — you weren’t there, or it’s wrong — leave it and speak to a manager, who can override it (recorded as a manager override).
                    </Text>
                </Box>
            </Box>
        </AppShell>
    );
}
