import { createContext, useContext } from 'react';

/**
 * Current portal role for the page: 'manager' or 'carer'.
 * Provided by the AppShell (real role comes from the logged-in user; the shell
 * also lets you PREVIEW the other role during development).
 *
 * NOTE: this only changes what's shown on screen. Real permission checks
 * ("manager can override, carer cannot") are enforced on the server.
 */
export const RoleContext = createContext('carer');

export const useRole = () => useContext(RoleContext);
