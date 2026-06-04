import { Card, Text } from '@mantine/core';

/**
 * Small dashboard stat tileokay .
 * Example shared component — lives in /frontend, imported via the @frontend alias.
 */
export default function StatCard({ label, value, color }) {
    return (
        <Card withBorder radius="md" padding="lg">
            <Text size="xs" tt="uppercase" fw={700} c="dimmed">{label}</Text>
            <Text fw={700} fz={30} c={color} mt={4}>{value}</Text>
        </Card>
    );
}
