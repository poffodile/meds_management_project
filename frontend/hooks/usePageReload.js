import { router } from '@inertiajs/react';

/**
 * Returns a reload(params) function for an Inertia GET endpoint that preserves
 * scroll + component state — the date/filter reload pattern repeated across the
 * medication pages.
 *
 *   const reload = usePageReload('/medication/medication-round-react');
 *   reload({ date: '2026-06-09' });
 */
export function usePageReload(endpoint, options = {}) {
    return (params = {}) =>
        router.get(endpoint, params, { preserveScroll: true, preserveState: true, ...options });
}
