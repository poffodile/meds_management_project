import { Card, Group, Text, Avatar, Box, Anchor, Stack, Button, ActionIcon } from '@mantine/core';
import { IconAlertTriangle, IconCalendar, IconMapPin, IconWeight, IconChevronDown } from '@tabler/icons-react';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import MetricChip from '@frontend/components/MetricChip';

/**
 * ResidentCard — the selected resident's clinical header (mockup centre panel):
 * photo + identity (DOB · gender · NHS / Room · Weight / Allergies / Care Plan),
 * a column of MetricChips, and a pink safety banner. Fields render only when
 * present so Phase B/C data can be wired in later without changing this card.
 *
 * Props: resident {...}, metrics:[{icon,label,value,color}], onViewProfile.
 */
export default function ResidentCard({ resident = {}, metrics = [], onViewProfile }) {
    const {
        name, photo, dob, age, gender, nhs, room, weight, weightUnit,
        allergies = [], conditions = [], carePlanHref, riskFlags = [],
    } = resident;
    const hasStrip = allergies.length > 0 || riskFlags.length > 0 || conditions.length > 0;

    return (
        <Card withBorder radius="lg" padding="lg" style={{ borderLeft: '4px solid var(--mantine-color-indigo-5)' }}>
            <Group align="flex-start" justify="space-between" wrap="wrap" gap="lg">
                <Group align="flex-start" gap="md" wrap="nowrap" style={{ flex: 1, minWidth: 280 }}>
                    <Avatar src={photo || undefined} color={avatarColor(name ?? '')} radius="md" size={88}>
                        {initials(name ?? '')}
                    </Avatar>
                    <Box style={{ minWidth: 0 }}>
                        <Group gap="xs" align="center" wrap="wrap">
                            <Text fz={22} fw={700}>{name}</Text>
                            <Button variant="default" size="xs" radius="md" onClick={onViewProfile}>View Profile</Button>
                            <ActionIcon variant="default" size="md" radius="md"><IconChevronDown size={16} /></ActionIcon>
                        </Group>

                        <Group gap="md" mt={8} wrap="wrap">
                            {dob && <Group gap={5} wrap="nowrap"><IconCalendar size={14} color="var(--mantine-color-gray-6)" /><Text size="sm">DOB: {dob}{age != null ? ` (${age})` : ''}</Text></Group>}
                            {gender && <Text size="sm" c="dimmed">{gender}</Text>}
                            {nhs && <Text size="sm" c="dimmed">NHS: {nhs}</Text>}
                        </Group>
                        <Group gap="md" mt={4} wrap="wrap">
                            {room && <Group gap={5} wrap="nowrap"><IconMapPin size={14} color="var(--mantine-color-gray-6)" /><Text size="sm">Room {room}</Text></Group>}
                            {weight && <Group gap={5} wrap="nowrap"><IconWeight size={14} color="var(--mantine-color-gray-6)" /><Text size="sm">Weight: {weight}{weightUnit ? ` ${weightUnit}` : ''}</Text></Group>}
                        </Group>
                        {allergies.length > 0 && (
                            <Text size="sm" mt={6}>
                                <Text span c="dimmed">Allergies: </Text>
                                <Text span c="red.7" fw={600}>{allergies.join(', ')}</Text>
                            </Text>
                        )}
                        {carePlanHref && (
                            <Group gap={5} mt={4}>
                                <Text span size="sm" c="dimmed">Care Plan:</Text>
                                <Anchor href={carePlanHref} size="sm">View Care Plan</Anchor>
                            </Group>
                        )}
                    </Box>
                </Group>

                {metrics.length > 0 && (
                    <Stack gap={10} style={{ minWidth: 200 }}>
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
                    {carePlanHref && <Anchor href={carePlanHref} size="sm" ml="auto">View All</Anchor>}
                </Group>
            )}
        </Card>
    );
}
