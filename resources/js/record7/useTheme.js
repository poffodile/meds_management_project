import { useCallback, useEffect, useState } from 'react';

const KEY = 'record7.theme';

/**
 * Record7's light and dark setting.
 *
 * Record7 opens in warm cream on every device, whatever the phone or laptop is
 * set to, because warm cream is the documented direction and arriving at a
 * medicines product in an unexpected colour scheme is disorienting.
 *
 * Dark is offered because night shifts are real. It is something a person turns
 * on, and it is remembered on that device afterwards.
 *
 * Storage can throw — a private window, blocked site data — so every read and
 * write is guarded and the light default simply stands.
 */
function stored() {
    try {
        const value = window.localStorage.getItem(KEY);

        return value === 'dark' || value === 'light' ? value : 'light';
    } catch {
        return 'light';
    }
}

export default function useTheme() {
    // Read on the first render rather than in an effect: an effect runs after
    // paint, and after paint is exactly when a flash of the wrong theme shows.
    const [theme, setTheme] = useState(stored);

    useEffect(() => {
        const root = document.querySelector('.r7-root');

        if (!root) return;

        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
            document.documentElement.setAttribute('data-r7-theme', 'dark');
        } else {
            root.removeAttribute('data-theme');
            document.documentElement.removeAttribute('data-r7-theme');
        }
    }, [theme]);

    const toggle = useCallback(() => {
        setTheme((current) => {
            const next = current === 'dark' ? 'light' : 'dark';

            try {
                window.localStorage.setItem(KEY, next);
            } catch {
                // Nothing to do: the choice just will not survive this device.
            }

            return next;
        });
    }, []);

    return { theme, toggle, isDark: theme === 'dark' };
}
