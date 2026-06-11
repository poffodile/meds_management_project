import { Card, Group, Text, Avatar, Box, Anchor, Stack } from '@mantine/core';
import { IconAlertTriangle } from '@tabler/icons-react';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import MetricChip from '@frontend/components/MetricChip';

/**
 * ResidentCard — the selected resident's clinical header (mockup centre panel):
 * photo + identity (Room · Gender · NHS, DOB/age · Weight), a compact column of
 * MetricChips, and a pink safety banner (allergies / risks / conditions).
 *
 * Fields render only when present, so Phase B/C data can be wired in later
 * without changing this component. Metrics wrap below the identity on narrow
 * widths; the name never overflows.
 *
 * Props: resident {...}, metrics:[{icon,label,value,color}], onViewProfile.
 */
export default function ResidentCard({ resident = {}, metrics = [], onViewProfile }) {
    const {
        name, photo, dob, age, gender, nhs, room, weight, weightUnit,
        allergies = [], conditions = [], carePlanHref, riskFlags = [],
    } = resident;

    const line1 = [room && `Room ${room}`, gender, nhs && `NHS ${nhs}`].filter(Boolean);
    const line2 = [
        dob && `DOB: ${dob}${age != null ? ` (${age})` : ''}`,
        weight && `Weight: ${weight}${weightUnit ? ` ${weightUnit}` : ''}`,
    ].filter(Boolean);
    const hasStrip = allergies.length > 0 || riskFlags.length > 0 || conditions.length > 0;

    return (
        <Card withBorder radius="lg" padding="lg">
            <Group align="flex-start" justify="space-between" wrap="wrap" gap="lg">
                <Group align="flex-start" gap="md" wrap="nowrap" style={{ flex: 1, minWidth: 260 }}>
                    <Avatar src={photo || undefined} color={avatarColor(name ?? '')} radius="md" size={72}>
                        {initials(name ?? '')}
                    </Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap="xs" wrap="wrap" align="center">
                            <Text fz={20} fw={700}>{name}</Text>
                            {(carePlanHref || onViewProfile) && (
                                <Anchor href={carePlanHref || undefined} onClick={onViewProfile} size="sm">View Profile</Anchor>
                            )}
                        </Group>
                        {line1.length > 0 && <Text size="sm" c="dimmed" mt={4}>{line1.join('  ·  ')}</Text>}
                        {line2.length > 0 && <Text size="sm" c="dimmed" mt={2}>{line2.join('  ·  ')}</Text>}
                    </Box>
                </Group>

                {metrics.length > 0 && (
                    <Stack gap={8} style={{ minWidth: 190 }}>
                        {metrics.map((m, i) => <MetricChip key={i} {...m} />)}
                    </Stack>
                )}
            </Group>

            {hasStrip && (
                <Group mt="md" py="xs" px="md" gap="lg" wrap="wrap" align="center"
                    style={{ background: 'var(--mantine-color-red-0)', borderRadius: 10 }}>
                    {allergies.length > 0 && (
                        <Group gap={6} wrap="nowrap">
                            <IconAlertTriangle size={16} color="var(--mantine-color-red-6)" />
                            <Text size="sm" c="red.7" fw={600}>Allergy: {allergies.join(', ')}</Text>
                        </Group>
                    )}
                    {riskFlags.map((r, i) => <Text key={`r${i}`} size="sm" c="red.7" fw={500}>{r.label}</Text>)}
                    {conditions.map((c, i) => <Text key={`c${i}`} size="sm" c="red.7" fw={500}>{c}</Text>)}
                    {carePlanHref && <Anchor href={carePlanHref} size="sm" ml="auto">View Full</Anchor>}
                </Group>
            )}
        </Card>
    );
}
