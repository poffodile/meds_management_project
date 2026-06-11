/**
 * Maps a Medication Round payload row into the display shape used by
 * <MedicationCard>. Kept here so the page and the resident cards share one
 * mapping. Pure presentation — no behaviour.
 */
const STATUS_DISPLAY = {
    overdue: { status: 'overdue', label: 'Overdue' },
    due_now: { status: 'due soon', label: 'Due Now' },
    upcoming: { status: 'due', label: 'Upcoming' },
    later: { status: 'due', label: 'Scheduled' },
    due: { status: 'due', label: 'PRN' },
};

export function toMed(row) {
    const d = STATUS_DISPLAY[row.status] ?? { status: 'due', label: null };
    return {
        name: row.medication_name,
        strength: row.strength,
        tags: [{ label: row.as_required ? 'PRN' : 'Regular', color: row.as_required ? 'grape' : 'blue' }],
        dose: row.dose,
        route: row.route,
        instruction: row.instruction,
        time: row.slot,
        status: row.code ? 'completed' : d.status,
        statusLabel: row.code ? null : d.label,
        stock: row.stock,
        stockUnit: row.unit,
        lowStock: row.low_stock,
        isControlled: row.is_controlled,
        cdSchedule: row.cd_schedule,
        code: row.code,
    };
}
