import { usePage } from '@inertiajs/react';

/**
 * Read the Laravel flash bag shared by Inertia (`{ success, error }`).
 * Backed by the shared <FlashAlerts /> component.
 */
export function useFlash() {
    return usePage().props.flash ?? {};
}
