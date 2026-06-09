/**
 * Small date helpers shared across the app — previously duplicated inline in
 * several modals/pages (`pad`, `formatDate`).
 */
export const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/** Zero-pad a number to 2 digits: 4 -> "04". */
export const pad = (n) => String(n).padStart(2, '0');

/** Format an ISO date string (YYYY-MM-DD) as "DD Mon YYYY". Returns '' if empty. */
export function formatDate(iso) {
    if (!iso) return '';
    const [y, m, d] = String(iso).split('-').map(Number);
    if (!y || !m || !d) return String(iso);
    return `${pad(d)} ${MONTHS[m - 1]} ${y}`;
}

/** Whole-years age from an ISO date of birth (YYYY-MM-DD). Returns null if unparseable. */
export function ageFromDob(iso) {
    if (!iso) return null;
    const [y, m, d] = String(iso).split('-').map(Number);
    if (!y || !m || !d) return null;
    const now = new Date();
    let age = now.getFullYear() - y;
    const beforeBirthday = now.getMonth() + 1 < m || (now.getMonth() + 1 === m && now.getDate() < d);
    if (beforeBirthday) age -= 1;
    return age >= 0 && age < 130 ? age : null;
}
