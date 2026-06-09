import { useEffect } from 'react';
import { Select, TextInput, Textarea, Text } from '@mantine/core';
import { useForm } from '@inertiajs/react';
import FormModal from '@frontend/components/FormModal';
import { MED_CODES } from '@frontend/lib/medicationCodes';

/**
 * RecordDoseModal — record the outcome of a single dose in the Medication Round.
 * The dose details come from the selected row; "Given" auto-deducts stock server-side.
 */
export default function RecordDoseModal({ opened, onClose, row, date, presetCode }) {
    const form = useForm({
        mar_sheet_id: '',
        date: date ?? '',
        time_slot: '',
        code: 'A',
        dose_given: '',
        witnessed_by: '',
        notes: '',
    });

    useEffect(() => {
        if (row) {
            form.setData('mar_sheet_id', row.mar_sheet_id);
            form.setData('date', date);
            form.setData('time_slot', row.slot ?? '');
            form.setData('code', presetCode ?? row.code ?? 'A');
            form.setData('dose_given', row.dose ?? '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [row, date, presetCode]);

    const submit = () => {
        form.post('/medication/medication-round-react/record', {
            preserveScroll: true,
            onSuccess: () => { form.setData('notes', ''); form.setData('witnessed_by', ''); onClose(); },
        });
    };

    if (!row) return null;

    return (
        <FormModal
            opened={opened}
            onClose={onClose}
            title="Record dose"
            onSubmit={submit}
            submitting={form.processing}
            submitLabel="Record"
        >
            <Text size="sm" c="dimmed">
                {row.medication_name}{row.dose ? ` · ${row.dose}` : ''} · {row.slot}
            </Text>
            <Select
                label="Outcome"
                data={MED_CODES}
                value={form.data.code}
                onChange={(v) => form.setData('code', v)}
                error={form.errors.code}
                required
            />
            <TextInput
                label="Dose given"
                value={form.data.dose_given}
                onChange={(e) => form.setData('dose_given', e.currentTarget.value)}
                error={form.errors.dose_given}
            />
            <TextInput
                label="Witnessed by"
                value={form.data.witnessed_by}
                onChange={(e) => form.setData('witnessed_by', e.currentTarget.value)}
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
