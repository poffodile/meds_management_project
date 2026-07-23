import { Component } from 'react';
import { Box, Stack, Text, Button, Group, ThemeIcon } from '@mantine/core';
import { IconAlertTriangle, IconRefresh } from '@tabler/icons-react';

/**
 * Catches render errors so one bad row cannot take the whole page down.
 *
 * This matters most during a medication round: without a boundary, a single
 * malformed prop blanks the screen for every carer trying to administer, with
 * no route back except a manual reload. The fallback deliberately says nothing
 * reassuring about the underlying record — a carer must not read "we recovered"
 * as "the dose was saved".
 */
export default class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { error: null };
    }

    static getDerivedStateFromError(error) {
        return { error };
    }

    componentDidCatch(error, info) {
        // Left as console output deliberately: this project has no client-side
        // error reporting sink yet. Wire one here when it exists.
        console.error('Render error caught by ErrorBoundary:', error, info?.componentStack);
    }

    render() {
        if (!this.state.error) return this.props.children;

        return (
            <Box p="xl" style={{ display: 'flex', justifyContent: 'center' }}>
                <Stack gap="sm" align="center" maw={520} ta="center">
                    <ThemeIcon size={44} radius="xl" color="red" variant="light">
                        <IconAlertTriangle size={24} />
                    </ThemeIcon>
                    <Text fw={700} fz="lg">This page couldn't be displayed</Text>
                    <Text c="dimmed" fz="sm" lh={1.5}>
                        Something went wrong while drawing this screen. Nothing you were
                        looking at has been changed, but anything you had part-way through
                        entering has been lost and was not saved.
                    </Text>
                    <Text c="dimmed" fz="xs" lh={1.5}>
                        If you were part-way through a medication round, check the resident's
                        record before recording the dose again.
                    </Text>
                    <Group gap="xs" mt="xs">
                        <Button
                            leftSection={<IconRefresh size={15} />}
                            onClick={() => window.location.reload()}
                        >
                            Reload the page
                        </Button>
                    </Group>
                    {this.state.error?.message && (
                        <Text c="dimmed" fz={10} mt="sm" style={{ fontFamily: 'monospace', wordBreak: 'break-word' }}>
                            {this.state.error.message}
                        </Text>
                    )}
                </Stack>
            </Box>
        );
    }
}
