import { useEffect } from 'react';
import { Select, Textarea, Text } from '@mantine/core';
import { useForm } from '@inertiajs/react';
import FormModal from '@frontend/components/FormModal';

const ACTION_OPTIONS = [
    'No action needed',
    'Re-administered',
    'GP informed',
    'Family informed',
    'Pharmacy informed',
    'Recorded for audit',
    'Other',
];

/**
 * ResolveDoseModal — record the clinical follow-up for a missed/not-given dose.
 * The dose details (sheet, slot, kind, code) come from the selected row.
 */
export default function ResolveDoseModal({ opened, onClose, item, date }) {
    const form = useForm({
        mar_sheet_id: '',
        review_date: date ?? '',
        time_slot: '',
        dose_kind: 'missed',
        code: '',
        clinical_action: '',
        notes: '',
    });

    // Populate the hidden dose fields whenever the selected row changes.
    useEffect(() => {
        if (item) {
            form.setData('mar_sheet_id', item.mar_sheet_id);
            form.setData('review_date', date);
            form.setData('time_slot', item.slot);
            form.setData('dose_kind', item.kind);
            form.setData('code', item.code ?? '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [item, date]);

    const submit = () => {
        form.post('/medication/missed-doses-react/resolve', {
            preserveScroll: true,
            onSuccess: () => { form.reset(); onClose(); },
        });
    };

    if (!item) return null;

    return (
        <FormModal
            opened={opened}
            onClose={onClose}
            title="Resolve dose"
            onSubmit={submit}
            submitting={form.processing}
            submitLabel="Mark resolved"
        >
            <Text size="sm" c="dimmed">
                {item.resident_name} · {item.medication_name} · {item.slot} ·{' '}
                {item.kind === 'missed' ? 'Missed' : 'Not given'}{item.code ? ` (${item.code})` : ''}
            </Text>
            <Select
                label="Clinical action"
                placeholder="What was done?"
                data={ACTION_OPTIONS}
                value={form.data.clinical_action}
                onChange={(v) => form.setData('clinical_action', v ?? '')}
                error={form.errors.clinical_action}
                required
            />
            <Textarea
                label="Notes"
                autosize
                minRows={2}
                value={form.data.notes}
                onChange={(e) => form.setData('notes', e.currentTarget.value)}
            />
        </FormModal>
    );
}
