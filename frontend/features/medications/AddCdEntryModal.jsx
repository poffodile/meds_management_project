import {
    Modal, Select, Autocomplete, NumberInput, TextInput, Textarea, Group, Box, Text, Button, ActionIcon, ThemeIcon,
} from '@mantine/core';
import { useForm } from '@inertiajs/react';
import {
    IconShieldLock, IconX, IconPill, IconTruckDelivery, IconTrash, IconArrowBackUp, IconAdjustments,
    IconUser, IconCalendarEvent, IconScale, IconUserCheck, IconNotes, IconArrowNarrowRight,
} from '@tabler/icons-react';
import { palette } from '@frontend/tokens';
import { HEADING_FONT } from '@frontend/lib/font';
import { pad } from '@frontend/lib/dateUtils';

// Pull the count off a dose string ("1 tablet" -> 1, "10 ml" -> 10).
const parseDoseQty = (dose) => { const m = String(dose ?? '').match(/[\d.]+/); return m ? Number(m[0]) : ''; };
// Pull the unit: the word in the dose ("1 tablet" -> "tablet"), else the dosage's unit ("10mg" -> "mg").
const parseUnit = (dose, dosage) => {
    const w = String(dose ?? '').replace(/[\d.,\s]+/, '').trim();
    if (w) return w;
    const ds = String(dosage ?? '').match(/[a-zA-Z%]+/);
    return ds ? ds[0] : '';
};

const INK = palette.ink;
const INK2 = palette.ink2;
const FAINT = palette.faint;
const LINE = palette.line;
const numeric = { fontVariantNumeric: 'tabular-nums', fontFeatureSettings: '"tnum" 1' };

// Movement types → the register's muted hues (kept in sync with ControlledDrugs.jsx).
const ACTIONS = [
    { value: 'administered', label: 'Administered', Icon: IconPill, flow: -1, hex: '#B4544A' },
    { value: 'received', label: 'Received', Icon: IconTruckDelivery, flow: 1, hex: '#3E8E77' },
    { value: 'disposed', label: 'Disposed', Icon: IconTrash, flow: -1, hex: '#BF8A3C' },
    { value: 'returned', label: 'Returned', Icon: IconArrowBackUp, flow: 1, hex: '#4E6B9A' },
    { value: 'adjustment', label: 'Adjustment', Icon: IconAdjustments, flow: 0, hex: '#8A6FAE' },
];
const actionOf = (v) => ACTIONS.find((a) => a.value === v) ?? ACTIONS[0];

// Shared input styling — hairline outline, tidy radius, tabular figures.
const field = {
    label: { fontSize: 12, fontWeight: 600, color: INK2, marginBottom: 5, letterSpacing: -0.1 },
    input: {
        borderRadius: 10, minHeight: 40, height: 40, fontSize: 13.5, fontWeight: 500,
        borderColor: 'light-dark(#E4E8EE, var(--mantine-color-dark-4))',
    },
    description: { fontSize: 11, color: FAINT, marginTop: 4 },
    error: { fontSize: 11 },
};

/* A small section wrapper: eyebrow label + a grid of fields on a subtle card. */
function Section({ icon: Icon, title, children }) {
    return (
        <Box>
            <Group gap={7} mb={11} wrap="nowrap">
                <Icon size={14} stroke={1.9} color={FAINT} />
                <Text fz={11} fw={700} c={FAINT} tt="uppercase" style={{ letterSpacing: 0.7 }}>{title}</Text>
            </Group>
            {children}
        </Box>
    );
}

/**
 * AddCdEntryModal — add a Controlled Drugs register entry.
 * Picks a resident, then their medications; auto-fills the running balance.
 */
export default function AddCdEntryModal({ opened, onClose, residents = [], medsByClient = {}, lastBalances = {}, action = '/medication/controlled-drugs-react' }) {
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

        // Auto-fill the dose details from the prescription so they don't need typing.
        if (match?.cd_schedule) form.setData('cd_schedule', match.cd_schedule);
        const qty = match ? parseDoseQty(match.dose) : '';
        const unit = match ? parseUnit(match.dose, match.dosage) : '';
        if (qty !== '') form.setData('dose_quantity', qty);
        if (unit) form.setData('unit', unit);

        // Balance before = last register balance if one exists, else the med's current
        // stock, else 0. Balance after is then derived from the dose.
        const last = lastBalances[`${form.data.client_id}|${value}`];
        const before = (last !== undefined && last !== null)
            ? last
            : (match && match.stock !== null && match.stock !== undefined ? match.stock : 0);
        form.setData('balance_before', before);
        recalcBalance(form.data.action_type, before, qty !== '' ? qty : form.data.dose_quantity);
    };

    // Auto-fill the running balance: out (administered/disposed) subtracts the dose,
    // in (received/returned) adds it. Adjustment is left for manual entry. Still editable.
    const recalcBalance = (act, before, dose) => {
        if (before === '' || before === null || before === undefined) return;
        const b = Number(before);
        if (Number.isNaN(b)) return;
        const q = Number(dose || 0);
        if (act === 'administered' || act === 'disposed') form.setData('balance_after', b - q);
        else if (act === 'received' || act === 'returned') form.setData('balance_after', b + q);
    };

    const submit = () => {
        form.post(action, {
            preserveScroll: true,
            onSuccess: () => { form.reset(); onClose(); },
        });
    };

    const meta = actionOf(form.data.action_type);
    const hasBalance = form.data.balance_before !== '' && form.data.balance_after !== '' && form.data.balance_after !== null;
    const sign = meta.flow < 0 ? '−' : meta.flow > 0 ? '+' : '';

    return (
        <Modal
            opened={opened}
            onClose={onClose}
            size={640}
            centered
            radius={20}
            padding={0}
            withCloseButton={false}
            overlayProps={{ backgroundOpacity: 0.45, blur: 4 }}
            styles={{
                content: { boxShadow: '0 30px 80px -20px rgba(16,29,54,0.45)', overflow: 'hidden' },
                body: { padding: 0 },
            }}
        >
            <Box style={{ display: 'flex', flexDirection: 'column', maxHeight: '88vh' }}>
                {/* Header band */}
                <Group
                    justify="space-between" wrap="nowrap" gap={12}
                    style={{
                        flexShrink: 0, padding: '18px 22px',
                        background: 'light-dark(linear-gradient(180deg,#FBFCFE,#F5F7FB), var(--mantine-color-dark-7))',
                        borderBottom: `1px solid ${LINE}`,
                    }}
                >
                    <Group gap={13} wrap="nowrap">
                        <ThemeIcon size={42} radius={12} style={{ background: 'light-dark(#F3EFFA, rgba(138,111,174,0.18))', color: '#8A6FAE', border: '1px solid light-dark(#EAE2F6, transparent)' }}>
                            <IconShieldLock size={21} stroke={1.7} />
                        </ThemeIcon>
                        <Box>
                            <Text fz={17} fw={680} c={INK} lh={1.15} style={{ letterSpacing: -0.3, fontFamily: HEADING_FONT }}>Add register entry</Text>
                            <Text fz={12} c={INK2} mt={2}>Controlled-drug movement · witnessed &amp; append-only</Text>
                        </Box>
                    </Group>
                    <ActionIcon variant="subtle" color="gray" radius={9} onClick={onClose}><IconX size={18} stroke={1.8} /></ActionIcon>
                </Group>

                {/* Scrollable body */}
                <Box style={{ overflowY: 'auto', padding: '20px 22px', display: 'flex', flexDirection: 'column', gap: 22 }}>
                    <Section icon={IconUser} title="Resident & medication">
                        <Group grow align="flex-start" gap={14}>
                            <Select
                                placeholder="Pick a resident"
                                data={residentOptions}
                                value={form.data.client_id}
                                onChange={onResidentChange}
                                error={form.errors.client_id}
                                searchable
                                comboboxProps={{ withinPortal: false }}
                                styles={field}
                            />
                            <Autocomplete
                                placeholder="Type or pick a medication"
                                data={medNames}
                                value={form.data.medication_name}
                                onChange={onMedNameChange}
                                error={form.errors.medication_name}
                                comboboxProps={{ withinPortal: false }}
                                styles={field}
                            />
                        </Group>
                    </Section>

                    <Section icon={IconCalendarEvent} title="Movement">
                        {/* Coloured action selector */}
                        <Group gap={8} wrap="wrap" mb={14}>
                            {ACTIONS.map((a) => {
                                const on = a.value === form.data.action_type;
                                return (
                                    <Box
                                        key={a.value} component="button" type="button"
                                        onClick={() => { form.setData('action_type', a.value); recalcBalance(a.value, form.data.balance_before, form.data.dose_quantity); }}
                                        style={{
                                            display: 'flex', alignItems: 'center', gap: 7, padding: '7px 12px', borderRadius: 10,
                                            cursor: 'pointer', fontFamily: 'inherit', fontSize: 12.5, fontWeight: 650,
                                            border: `1.5px solid ${on ? a.hex : 'light-dark(#E7EAEF, var(--mantine-color-dark-4))'}`,
                                            background: on ? `color-mix(in srgb, ${a.hex} 10%, transparent)` : 'transparent',
                                            color: on ? a.hex : INK2, transition: 'all .14s ease',
                                        }}
                                    >
                                        <a.Icon size={15} stroke={1.9} />
                                        {a.label}
                                    </Box>
                                );
                            })}
                        </Group>
                        <Group grow align="flex-start" gap={14}>
                            <TextInput
                                label="CD schedule"
                                placeholder="schedule_2 … schedule_5"
                                value={form.data.cd_schedule}
                                onChange={(e) => form.setData('cd_schedule', e.currentTarget.value)}
                                styles={field}
                            />
                            <TextInput
                                label="Date" type="date"
                                value={form.data.entry_date}
                                onChange={(e) => form.setData('entry_date', e.currentTarget.value)}
                                error={form.errors.entry_date}
                                styles={field}
                            />
                            <TextInput
                                label="Time" type="time"
                                value={form.data.entry_time}
                                onChange={(e) => form.setData('entry_time', e.currentTarget.value)}
                                error={form.errors.entry_time}
                                styles={field}
                            />
                        </Group>
                        <Group grow align="flex-start" gap={14} mt={14}>
                            <NumberInput
                                label="Dose" min={0}
                                value={form.data.dose_quantity}
                                onChange={(v) => { form.setData('dose_quantity', v); recalcBalance(form.data.action_type, form.data.balance_before, v); }}
                                error={form.errors.dose_quantity}
                                styles={field}
                            />
                            <TextInput
                                label="Unit" placeholder="tablet(s), ml…"
                                value={form.data.unit}
                                onChange={(e) => form.setData('unit', e.currentTarget.value)}
                                styles={field}
                            />
                        </Group>
                    </Section>

                    <Section icon={IconScale} title="Running balance">
                        <Group grow align="flex-start" gap={14}>
                            <NumberInput
                                label="Balance before"
                                value={form.data.balance_before}
                                onChange={(v) => { form.setData('balance_before', v); recalcBalance(form.data.action_type, v, form.data.dose_quantity); }}
                                error={form.errors.balance_before}
                                styles={field}
                            />
                            <NumberInput
                                label="Balance after"
                                description={form.data.action_type === 'adjustment' ? 'Enter the recounted balance' : 'Auto-calculated'}
                                value={form.data.balance_after}
                                onChange={(v) => form.setData('balance_after', v)}
                                error={form.errors.balance_after}
                                readOnly={form.data.action_type !== 'adjustment'}
                                styles={field}
                            />
                        </Group>
                    </Section>

                    <Section icon={IconUserCheck} title="Witness & notes">
                        <TextInput
                            label="Witness name"
                            placeholder="Second signatory who witnessed this"
                            value={form.data.witness_name}
                            onChange={(e) => form.setData('witness_name', e.currentTarget.value)}
                            error={form.errors.witness_name}
                            styles={field}
                        />
                        <Textarea
                            label="Notes" placeholder="Anything worth recording…" autosize minRows={2}
                            mt={14}
                            value={form.data.notes}
                            onChange={(e) => form.setData('notes', e.currentTarget.value)}
                            styles={{ ...field, input: { ...field.input, minHeight: undefined, height: undefined } }}
                            leftSection={<IconNotes size={15} stroke={1.7} color={FAINT} style={{ alignSelf: 'flex-start', marginTop: 10 }} />}
                        />
                    </Section>
                </Box>

                {/* Sticky footer with live movement summary */}
                <Group
                    justify="space-between" wrap="nowrap" gap={12}
                    style={{
                        flexShrink: 0, padding: '14px 22px',
                        borderTop: `1px solid ${LINE}`,
                        background: 'light-dark(#FBFCFE, var(--mantine-color-dark-7))',
                    }}
                >
                    <Group gap={9} wrap="nowrap" style={{ minWidth: 0 }}>
                        <Box style={{ display: 'grid', placeItems: 'center', width: 30, height: 30, borderRadius: 9, flexShrink: 0, background: `color-mix(in srgb, ${meta.hex} 12%, transparent)`, color: meta.hex }}>
                            <meta.Icon size={16} stroke={1.9} />
                        </Box>
                        <Box style={{ minWidth: 0 }}>
                            <Text fz={12.5} fw={650} c={INK} truncate lh={1.2}>
                                {meta.label}{form.data.dose_quantity !== '' && <span style={{ color: meta.hex }}>{'  '}{sign}{form.data.dose_quantity} {form.data.unit || ''}</span>}
                            </Text>
                            {hasBalance
                                ? (
                                    <Group gap={5} wrap="nowrap" mt={1}>
                                        <Text fz={11.5} c={FAINT} style={numeric}>{form.data.balance_before}</Text>
                                        <IconArrowNarrowRight size={13} color={FAINT} />
                                        <Text fz={11.5} fw={650} c={INK2} style={numeric}>{form.data.balance_after}</Text>
                                        <Text fz={11.5} c={FAINT}>in stock</Text>
                                    </Group>
                                )
                                : <Text fz={11.5} c={FAINT} mt={1}>Balance updates automatically</Text>}
                        </Box>
                    </Group>
                    <Group gap={10} wrap="nowrap" style={{ flexShrink: 0 }}>
                        <Button variant="default" radius={10} onClick={onClose} styles={{ root: { fontWeight: 600 } }}>Cancel</Button>
                        <Button
                            radius={10} onClick={submit} loading={form.processing}
                            styles={{ root: { fontWeight: 650, paddingInline: 20 } }}
                            style={{ background: palette.primaryBtn, boxShadow: 'light-dark(0 10px 22px -10px rgba(22,35,59,0.55), 0 10px 22px -10px rgba(31,158,147,0.6))' }}
                        >
                            Add entry
                        </Button>
                    </Group>
                </Group>
            </Box>
        </Modal>
    );
}
