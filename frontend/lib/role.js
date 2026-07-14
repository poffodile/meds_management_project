import { createContext, useSyncExternalStore } from 'react';
import { usePage } from '@inertiajs/react';

/**
 * Portal role for the current page: 'manager' or 'carer'.
 *
 * The REAL role comes from the logged-in user (shared by the server as
 * `auth.user.role`). Managers can PREVIEW the carer view — a view-only
 * override kept in localStorage and shared app-wide via a tiny store, so the
 * header toggle instantly re-renders every page (buttons show/hide).
 *
 * NOTE: preview only changes what's shown. Real permission checks
 * ("manager can override, carer cannot") are enforced on the server.
 *
 * These hooks read the role via usePage() (not React context), so they work
 * anywhere — including page components that render their OWN AppShell.
 */

// Kept for backwards-compatibility with existing imports; no longer required.
export const RoleContext = createContext('carer');

const VIEW_AS_KEY = 'careone-view-as';
const listeners = new Set();

function getViewAs() {
    if (typeof window === 'undefined') return 'manager';
    return window.localStorage.getItem(VIEW_AS_KEY) === 'carer' ? 'carer' : 'manager';
}

/** Set the manager's preview mode ('manager' | 'carer') and notify all subscribers. */
export function setViewAs(value) {
    try { window.localStorage.setItem(VIEW_AS_KEY, value); } catch (e) { /* ignore */ }
    listeners.forEach((l) => l());
}

function subscribe(cb) {
    listeners.add(cb);
    return () => listeners.delete(cb);
}

/** Current preview choice ('manager' | 'carer'), reactive. */
export const useViewAs = () => useSyncExternalStore(subscribe, getViewAs, () => 'manager');

/** The user's REAL role from the server — ignores the preview override. */
export const useRealRole = () => (usePage()?.props?.auth?.user?.role ?? 'carer');

/** Effective role for the UI: managers may preview 'carer'; others get their real role. */
export function useRole() {
    const real = useRealRole();
    const viewAs = useViewAs();
    return real === 'manager' ? viewAs : real;
}
