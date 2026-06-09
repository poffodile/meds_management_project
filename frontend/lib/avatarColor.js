import { avatarColors } from '@frontend/tokens';

/**
 * Deterministic avatar helpers for initials avatars (used where a resident/user
 * has no photo). Same name → same colour, every time, every page.
 */
export function avatarColor(name = '') {
    let h = 0;
    for (let i = 0; i < name.length; i++) h = (h + name.charCodeAt(i)) % avatarColors.length;
    return avatarColors[h];
}

/** First letters of the first two words: "Mary Smith" -> "MS". */
export function initials(name = '') {
    return name.split(' ').filter(Boolean).map((w) => w[0]).slice(0, 2).join('').toUpperCase() || '?';
}
