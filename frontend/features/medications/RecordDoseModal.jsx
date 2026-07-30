import { useEffect } from 'react';
import { Select, TextInput, Textarea, Text, Badge, Avatar, Group, Box } from '@mantine/core';
import { IconAlertTriangle } from '@tabler/icons-react';
import { useForm } from '@inertiajs/react';
import { notifications } from '@mantine/notifications';
import FormModal from '@frontend/components/FormModal';
import { MED_CODES, REASON_REQUIRED_CODES, REFUSAL_REASONS, OMISSION_REASONS } from '@frontend/lib/medicationCodes';
import { avatarColor, initials } from '@frontend/lib/avatarColor';

/** Age in whole years from an ISO date-of-birth. */
function ageFromDob(dob) {
    if (!dob) return null;
    const d = new Date(dob);
    if (Number.isNaN(d.getTime())) return null;
    const now = new Date();
    let a = now.getFullYear() - d.getFullYear();
    const m = now.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < d.getDate())) a -= 1;
    return a >= 0 && a < 130 ? a : null;
}

/** "12 Mar 1948" from an ISO date-of-birth, or null. */
function dobLabel(dob) {
    if (!dob) return null;
    const d = new Date(dob);
    if (Number.isNaN(d.getTime())) return null;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

/**
 * RecordDoseModal — record the outcome of a single dose in the Medication Round.
 * The dose details come from the selected row; "Given" auto-deducts stock server-side.
 */
export default function RecordDoseModal({ opened, onClose, row, resident = null, allergies = [], witnessOptions = [], date, presetCode, endpoint = '/medication/medication-round-react/record' }) {
    const form = useForm({
        mar_sheet_id: '',
        date: date ?? '',
        time_slot: '',
        code: 'A',
        dose_given: '',
        witnessed_by: '',
        witness_user_id: '',
        reason: '',
        notes: '',
    });
    // When staff options are supplied, the witness is PICKED (so they can be notified to
    // confirm — issue #14 / A2); otherwise the old free-text field is used (legacy screens).
    const pickWitness = witnessOptions.length > 0;

    const needsReason = REASON_REQUIRED_CODES.includes(form.data.code);
    const reasonOptions = form.data.code === 'R' ? REFUSAL_REASONS : OMISSION_REASONS;

    useEffect(() => {
        if (row) {
            form.setData('mar_sheet_id', row.mar_sheet_id);
            form.setData('date', date);
            form.setData('time_slot', row.slot ?? '');
            form.setData('code', presetCode ?? row.code ?? 'A');
            form.setData('dose_given', row.dose_given ?? row.dose ?? '');
            form.setData('witnessed_by', row.witnessed_by ?? '');
            form.setData('witness_user_id', '');
            form.setData('reason', row.reason ?? '');
            form.setData('notes', row.notes ?? '');
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [row, date, presetCode]);

    const submit = () => {
        // Controlled drugs require a witness to administer (also enforced server-side).
        if (row?.is_controlled && form.data.code === 'A' && !String(form.data.witnessed_by).trim()) {
            form.setError('witnessed_by', 'A witness is required to administer a controlled drug.');
            return;
        }
        // Refused / not-given / omitted must carry a reason (also enforced server-side).
        if (REASON_REQUIRED_CODES.includes(form.data.code) && !String(form.data.reason).trim()) {
            form.setError('reason', 'Please choose a reason.');
            return;
        }
        form.post(endpoint, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => { form.setData('notes', ''); form.setData('witnessed_by', ''); form.setData('witness_user_id', ''); form.setData('reason', ''); onClose(); },
            // Field errors (code/reason/witnessed_by) render inline; a server error with no
            // matching field (e.g. prescription-not-found) would otherwise be invisible and
            // the modal would sit there as if nothing happened (audit CR-07). The modal is
            // deliberately NOT closed on error — the carer has not succeeded.
            onError: (errors) => {
                const shown = ['code', 'reason', 'witnessed_by'];
                const orphan = Object.entries(errors ?? {}).find(([k]) => !shown.includes(k));
                if (orphan) {
                    notifications.show({ color: 'red', title: 'Not recorded', message: orphan[1], autoClose: 6000 });
                }
            },
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
            {/* Resident identity at the point of commit — you should see WHO you are
                recording against here, not only behind the modal (review I1 / HAZ-02). */}
            {resident && (
                <Box mb="xs" p="xs"
                    style={{ background: 'light-dark(#F7F9FC, #14202F)', border: '1px solid light-dark(#E1E7F0, #22303F)', borderRadius: 10 }}>
                    <Group gap="sm" wrap="nowrap" align="center">
                        <Avatar src={resident.photo || undefined} size={40} radius="md" color={avatarColor(resident.name)}>
                            {initials(resident.name)}
                        </Avatar>
                        <Box style={{ minWidth: 0 }}>
                            <Text fw={700} size="sm" truncate>{resident.name}</Text>
                            <Text size="xs" c="dimmed">
                                {[ageFromDob(resident.dob) != null && `${ageFromDob(resident.dob)}y`, dobLabel(resident.dob), resident.room && `Room ${resident.room}`]
                                    .filter(Boolean).join(' · ') || '—'}
                            </Text>
                        </Box>
                    </Group>
                    {allergies.length > 0 && (
                        <Group gap={5} mt={7} wrap="wrap">
                            {allergies.map((a, i) => (
                                <Badge key={i} size="xs" color="red" variant="light" radius="sm" leftSection={<IconAlertTriangle size={10} />}>{a}</Badge>
                            ))}
                        </Group>
                    )}
                </Box>
            )}
            <Text size="sm" c="dimmed">
                {row.medication_name}{row.dose ? ` · ${row.dose}` : ''} · {row.slot}
            </Text>
            {row.is_controlled && (
                <Badge color="grape" variant="light" radius="sm">
                    Controlled drug{row.cd_schedule ? ` · ${row.cd_schedule}` : ''} — witness required
                </Badge>
            )}
            <Select
                label="Outcome"
                data={MED_CODES}
                value={form.data.code}
                onChange={(v) => {
                    form.setData('code', v);
                    if (!REASON_REQUIRED_CODES.includes(v)) form.setData('reason', '');
                }}
                error={form.errors.code}
                required
            />
            {needsReason && (
                <Select
                    label="Reason"
                    placeholder="Choose a reason"
                    data={reasonOptions}
                    value={form.data.reason}
                    onChange={(v) => form.setData('reason', v ?? '')}
                    error={form.errors.reason}
                    required
                    searchable
                />
            )}
            <TextInput
                label="Dose given"
                value={form.data.dose_given}
                onChange={(e) => form.setData('dose_given', e.currentTarget.value)}
                error={form.errors.dose_given}
            />
            {pickWitness ? (
                <Select
                    label="Witnessed by"
                    placeholder={row.is_controlled ? 'Choose the witnessing staff member' : 'Optional'}
                    data={witnessOptions}
                    searchable
                    nothingFoundMessage="No staff match"
                    required={row.is_controlled}
                    value={form.data.witness_user_id || null}
                    onChange={(v) => {
                        form.setData('witness_user_id', v ?? '');
                        const opt = witnessOptions.find((o) => o.value === v);
                        form.setData('witnessed_by', opt ? opt.label : '');
                    }}
                    error={form.errors.witnessed_by}
                    description={row.is_controlled ? 'They’ll be asked to confirm the signature on their own account.' : undefined}
                />
            ) : (
                <TextInput
                    label="Witnessed by"
                    placeholder={row.is_controlled ? 'Second staff member (required)' : 'Optional'}
                    required={row.is_controlled}
                    value={form.data.witnessed_by}
                    onChange={(e) => form.setData('witnessed_by', e.currentTarget.value)}
                    error={form.errors.witnessed_by}
                />
            )}
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
