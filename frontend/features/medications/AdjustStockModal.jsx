import { useState } from 'react';
import { Select, NumberInput, TextInput, Textarea, Checkbox } from '@mantine/core';
import { useForm } from '@inertiajs/react';
import FormModal from '@frontend/components/FormModal';
import ConfirmDialog from '@frontend/components/ConfirmDialog';

const TYPE_OPTIONS = [
    { value: 'received', label: 'Received (stock in)' },
    { value: 'disposed', label: 'Disposed' },
    { value: 'returned', label: 'Returned' },
    { value: 'correction', label: 'Correction' },
];

/**
 * AdjustStockModal — the "Adjust stock" form for the Medication Stock page.
 * Feature-specific, so it lives under frontend/features/medications/.
 * Posts to the React stock-adjust endpoint; disposals require a confirmation.
 */
export default function AdjustStockModal({ opened, onClose, meds = [] }) {
    const [confirmOpen, setConfirmOpen] = useState(false);

    const form = useForm({
        mar_sheet_id: '',
        transaction_type: 'received',
        quantity: '',
        expiry_date: '',
        is_controlled: false,
        cd_schedule: '',
        reason: '',
        disposal_method: '',
        witness_name: '',
        notes: '',
    });

    const medOptions = meds.map((m) => ({
        value: String(m.id),
        label: m.medication_name + (m.resident ? ` — ${m.resident}` : ''),
    }));

    const onMedChange = (value) => {
        form.setData('mar_sheet_id', value ?? '');
        const med = meds.find((m) => String(m.id) === value);
        if (med) {
            // Preserve the medication's controlled-drug details (the backend overwrites them).
            form.setData('is_controlled', !!med.is_controlled);
            form.setData('cd_schedule', med.cd_schedule ?? '');
        }
    };

    const submit = () => {
        form.post('/medication/stock-react/adjust', {
            preserveScroll: true,
            onSuccess: () => { setConfirmOpen(false); form.reset(); onClose(); },
        });
    };

    const handleSubmit = () => {
        // Disposals are sensitive — ask for confirmation first.
        if (form.data.transaction_type === 'disposed') {
            setConfirmOpen(true);
        } else {
            submit();
        }
    };

    const isDisposed = form.data.transaction_type === 'disposed';

    return (
        <>
            <FormModal
                opened={opened}
                onClose={onClose}
                title="Adjust stock"
                onSubmit={handleSubmit}
                submitting={form.processing}
                submitLabel="Save"
            >
                <Select
                    label="Medication"
                    placeholder="Pick a medication"
                    data={medOptions}
                    value={form.data.mar_sheet_id}
                    onChange={onMedChange}
                    error={form.errors.mar_sheet_id}
                    searchable
                    required
                />
                <Select
                    label="Type"
                    data={TYPE_OPTIONS}
                    value={form.data.transaction_type}
                    onChange={(v) => form.setData('transaction_type', v)}
                    error={form.errors.transaction_type}
                    required
                />
                <NumberInput
                    label="Quantity"
                    placeholder="e.g. 28"
                    min={0}
                    value={form.data.quantity}
                    onChange={(v) => form.setData('quantity', v)}
                    error={form.errors.quantity}
                />
                <TextInput
                    label="Expiry date"
                    type="date"
                    value={form.data.expiry_date}
                    onChange={(e) => form.setData('expiry_date', e.currentTarget.value)}
                    error={form.errors.expiry_date}
                />
                <Checkbox
                    label="Controlled drug"
                    checked={form.data.is_controlled}
                    onChange={(e) => form.setData('is_controlled', e.currentTarget.checked)}
                />
                {form.data.is_controlled && (
                    <TextInput
                        label="CD schedule"
                        placeholder="schedule_2 … schedule_5"
                        value={form.data.cd_schedule}
                        onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)}
                    />
                )}
                {isDisposed && (
                    <TextInput
                        label="Disposal method"
                        value={form.data.disposal_method}
                        onChange={(e) => form.setData('disposal_method', e.currentTarget.value)}
                    />
                )}
                <TextInput
                    label="Witness name"
                    value={form.data.witness_name}
                    onChange={(e) => form.setData('witness_name', e.currentTarget.value)}
                />
                <TextInput
                    label="Reason"
                    value={form.data.reason}
                    onChange={(e) => form.setData('reason', e.currentTarget.value)}
                />
                <Textarea
                    label="Notes"
                    autosize
                    minRows={2}
                    value={form.data.notes}
                    onChange={(e) => form.setData('notes', e.currentTarget.value)}
                />
            </FormModal>

            <ConfirmDialog
                opened={confirmOpen}
                onClose={() => setConfirmOpen(false)}
                onConfirm={submit}
                title="Confirm disposal"
                message={`Record a disposal of ${form.data.quantity || 0} unit(s)? This is logged for audit.`}
                confirmLabel="Confirm disposal"
                confirmColor="orange"
                loading={form.processing}
            />
        </>
    );
}
