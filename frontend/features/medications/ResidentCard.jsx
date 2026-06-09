import { Card, Group, Text, Avatar, Box, Button, Stack, Anchor, Divider, Badge } from '@mantine/core';
import { avatarColor, initials } from '@frontend/lib/avatarColor';
import MetricChip from '@frontend/components/MetricChip';
import RiskFlag from '@frontend/components/RiskFlag';

/**
 * ResidentCard — the selected resident's clinical header (mockup centre panel):
 * photo + identity (DOB/age/gender/NHS, room, weight), allergies, care-plan link,
 * a column of MetricChips (risk/PRN/regular counts), and a risk/allergy strip.
 *
 * Fields are rendered only when present, so Phase B/C data (NHS, gender, weight,
 * risk flags) can be wired in later without changing this component.
 *
 * Props: resident {...}, metrics:[{icon,label,value,color}], onViewProfile.
 */
export default function ResidentCard({ resident = {}, metrics = [], onViewProfile }) {
    const {
        name, photo, dob, age, gender, nhs, room, weight, weightUnit,
        allergies = [], conditions = [], carePlanHref, riskFlags = [],
    } = resident;

    const idLine = [
        dob ? `DOB: ${dob}${age != null ? ` (${age})` : ''}` : null,
        gender || null,
        nhs ? `NHS: ${nhs}` : null,
    ].filter(Boolean).join('  •  ');
    const roomLine = [
        room ? `Room ${room}` : null,
        weight ? `Weight ${weight}${weightUnit ? ` ${weightUnit}` : ''}` : null,
    ].filter(Boolean).join('  •  ');

    const hasStrip = allergies.length > 0 || riskFlags.length > 0 || conditions.length > 0;

    return (
        <Card withBorder radius="lg" padding="lg">
            <Group justify="space-between" align="flex-start" wrap="nowrap">
                <Group gap="md" align="flex-start" wrap="nowrap">
                    <Avatar src={photo || undefined} color={avatarColor(name ?? '')} radius="md" size={84}>
                        {initials(name ?? '')}
                    </Avatar>
                    <Box>
                        <Text fz={22} fw={700}>{name}</Text>
                        {idLine && <Text size="sm" c="dimmed" mt={2}>{idLine}</Text>}
                        {roomLine && <Text size="sm" c="dimmed" mt={2}>{roomLine}</Text>}
                        {allergies.length > 0 && (
                            <Text size="sm" mt={6}>
                                <Text span c="dimmed">Allergies: </Text>
                                <Text span c="red" fw={600}>{allergies.join(', ')}</Text>
                            </Text>
                        )}
                        {carePlanHref && (
                            <Anchor href={carePlanHref} size="sm" mt={4} style={{ display: 'inline-block' }}>View Care Plan</Anchor>
                        )}
                    </Box>
                </Group>

                <Stack gap="sm" miw={180}>
                    {onViewProfile && <Button variant="default" size="xs" onClick={onViewProfile}>View Profile</Button>}
                    {metrics.map((m, i) => <MetricChip key={i} {...m} />)}
                </Stack>
            </Group>

            {hasStrip && (
                <>
                    <Divider my="md" />
                    <Group gap="sm" wrap="wrap">
                        {allergies.length > 0 && <RiskFlag label={`Allergy: ${allergies.join(', ')}`} level="urgent" />}
                        {riskFlags.map((r, i) => <RiskFlag key={i} label={r.label} level={r.level} />)}
                        {conditions.map((c, i) => <Badge key={i} variant="light" color="gray" radius="sm">{c}</Badge>)}
                    </Group>
                </>
            )}
        </Card>
    );
}
