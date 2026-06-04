import { Modal, Button, Group, Stack } from '@mantine/core';

/**
 * FormModal — a reusable pop-up that holds a form, with Cancel + Save buttons.
 *
 * Props:
 *   opened, onClose — control visibility
 *   title           — modal heading
 *   onSubmit        — called when Save (or Enter) is pressed
 *   submitting      — shows a loading spinner on Save
 *   submitLabel     — Save button text (default "Save")
 *   size            — modal width (default "md")
 *   children        — the form fields
 */
export default function FormModal({
    opened, onClose, title, onSubmit, submitting = false, submitLabel = 'Save', size = 'md', children,
}) {
    return (
        <Modal opened={opened} onClose={onClose} title={title} size={size} centered>
            <form onSubmit={(e) => { e.preventDefault(); onSubmit(); }}>
                <Stack>
                    {children}
                    <Group justify="flex-end" mt="sm">
                        <Button variant="default" type="button" onClick={onClose}>Cancel</Button>
                        <Button type="submit" loading={submitting}>{submitLabel}</Button>
                    </Group>
                </Stack>
            </form>
        </Modal>
    );
}
