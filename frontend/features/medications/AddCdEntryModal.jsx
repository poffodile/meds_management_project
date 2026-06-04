import { Select, Autocomplete, NumberInput, TextInput, Textarea, Group } from '@mantine/core';
import { useForm } from '@inertiajs/react';
import FormModal from '@frontend/components/FormModal';

const ACTION_OPTIONS = [
    { value: 'administered', label: 'Administered' },
    { value: 'received', label: 'Received' },
    { value: 'disposed', label: 'Disposed' },
    { value: 'returned', label: 'Returned' },
    { value: 'adjustment', label: 'Adjustment' },
];

const pad = (n) => String(n).padStart(2, '0');

/**
 * AddCdEntryModal — add a Controlled Drugs register entry.
 * Picks a resident, then their medications; auto-fills the running balance.
 */
export default function AddCdEntryModal({ opened, onClose, residents = [], medsByClient = {}, lastBalances = {} }) {
    const d = new Date();
    const today = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const nowTime = `${pad(d.getHours())}:${pad(d.getMinutes())}`;

    const form = useForm({
        client_id: '',
        mar_sheet_id: '',
        medication_name: '',
        cd_schedule: '',
        action_type: 'administered',
        entry_date: today,
        entry_time: nowTime,
        dose_quantity: '',
        unit: '',
        balance_before: '',
        balance_after: '',
        witness_name: '',
        notes: '',
    });

    const residentOptions = residents.map((r) => ({ value: String(r.id), label: r.name }));
    const meds = form.data.client_id ? (medsByClient[form.data.client_id] ?? []) : [];
    const medNames = meds.map((m) => m.name);

    const onResidentChange = (value) => {
        form.setData('client_id', value ?? '');
        form.setData('medication_name', '');
        form.setData('mar_sheet_id', '');
        form.setData('balance_before', '');
    };

    const onMedNameChange = (value) => {
        form.setData('medication_name', value);
        const match = meds.find((m) => m.name === value);
        form.setData('mar_sheet_id', match ? String(match.id) : '');
        const bal = lastBalances[`${form.data.client_id}|${value}`];
        if (bal !== undefined && bal !== null) {
            form.setData('balance_before', bal);
        }
    };

    const submit = () => {
        form.post('/medication/controlled-drugs-react', {
            preserveScroll: true,
            onSuccess: () => { form.reset(); onClose(); },
        });
    };

    return (
        <FormModal
            opened={opened}
            onClose={onClose}
            title="Add register entry"
            onSubmit={submit}
            submitting={form.processing}
            submitLabel="Add entry"
            size="lg"
        >
            <Select
                label="Resident"
                placeholder="Pick a resident"
                data={residentOptions}
                value={form.data.client_id}
                onChange={onResidentChange}
                error={form.errors.client_id}
                searchable
                required
            />
            <Autocomplete
                label="Medication"
                placeholder="Type or pick a medication"
                data={medNames}
                value={form.data.medication_name}
                onChange={onMedNameChange}
                error={form.errors.medication_name}
                required
            />
            <Group grow>
                <Select
                    label="Action"
                    data={ACTION_OPTIONS}
                    value={form.data.action_type}
                    onChange={(v) => form.setData('action_type', v)}
                    error={form.errors.action_type}
                    required
                />
                <TextInput
                    label="CD schedule"
                    placeholder="schedule_2 … schedule_5"
                    value={form.data.cd_schedule}
                    onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)}
                />
            </Group>
            <Group grow>
                <TextInput
                    label="Date" type="date"
                    value={form.data.entry_date}
                    onChange={(e) => form.setData('entry_date', e.currentTarget.value)}
                    error={form.errors.entry_date}
                    required
                />
                <TextInput
                    label="Time" type="time"
                    value={form.data.entry_time}
                    onChange={(e) => form.setData('entry_time', e.currentTarget.value)}
                    error={form.errors.entry_time}
                    required
                />
            </Group>
            <Group grow>
                <NumberInput
                    label="Dose given" min={0}
                    value={form.data.dose_quantity}
                    onChange={(v) => form.setData('dose_quantity', v)}
                    error={form.errors.dose_quantity}
                />
                <TextInput
                    label="Unit" placeholder="tablet(s), ml…"
                    value={form.data.unit}
                    onChange={(e) => form.setData('unit', e.currentTarget.value)}
                />
            </Group>
            <Group grow>
                <NumberInput
                    label="Balance before"
                    value={form.data.balance_before}
                    onChange={(v) => form.setData('balance_before', v)}
                    error={form.errors.balance_before}
                />
                <NumberInput
                    label="Balance after"
                    value={form.data.balance_after}
                    onChange={(v) => form.setData('balance_after', v)}
                    error={form.errors.balance_after}
                    required
                />
            </Group>
            <TextInput
                label="Witness name"
                value={form.data.witness_name}
                onChange={(e) => form.setData('witness_name', e.currentTarget.value)}
                error={form.errors.witness_name}
                required
            />
            <Textarea
                label="Notes" autosize minRows={2}
                value={form.data.notes}
                onChange={(e) => form.setData('notes', e.currentTarget.value)}
            />
        </FormModal>
    );
}
