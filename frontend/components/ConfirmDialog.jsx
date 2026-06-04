import { Modal, Button, Group, Text } from '@mantine/core';

/**
 * ConfirmDialog — a small "are you sure?" pop-up for sensitive/destructive actions.
 *
 * Props:
 *   opened, onClose — control visibility
 *   onConfirm       — called when the confirm button is pressed
 *   title           — heading (default "Are you sure?")
 *   message         — body text
 *   confirmLabel    — confirm button text (default "Confirm")
 *   confirmColor    — confirm button colour (default "red")
 *   loading         — spinner on the confirm button
 */
export default function ConfirmDialog({
    opened, onClose, onConfirm, title = 'Are you sure?', message,
    confirmLabel = 'Confirm', confirmColor = 'red', loading = false,
}) {
    return (
        <Modal opened={opened} onClose={onClose} title={title} size="sm" centered>
            {message && <Text size="sm" mb="md">{message}</Text>}
            <Group justify="flex-end">
                <Button variant="default" onClick={onClose}>Cancel</Button>
                <Button color={confirmColor} loading={loading} onClick={onConfirm}>{confirmLabel}</Button>
            </Group>
        </Modal>
    );
}
