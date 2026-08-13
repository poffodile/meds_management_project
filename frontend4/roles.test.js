import { describe, expect, it } from 'vitest';
import { NAV, P, navFor } from './roles';

function keys(can) {
    return navFor(can).flatMap((group) => group.items.map((item) => item.key));
}

describe('Frontend 4 navigation permissions', () => {
    it('shows only implemented workspaces the user may view', () => {
        expect(keys([P.VIEW_TODAY, P.VIEW_ROUND, P.VIEW_CLIENTS])).toEqual([
            'today', 'round', 'clients',
        ]);
    });

    it('exposes shipped handover and assurance routes but keeps unfinished routes hidden', () => {
        const visible = keys(Object.values(P));

        expect(visible).not.toContain('missed');
        expect(keys([P.VIEW_HANDOVER])).toContain('handover');
        expect(visible).not.toContain('cd');
        expect(visible).not.toContain('stock');
        expect(keys([P.VIEW_REPORTS])).toEqual(['assurance', 'reports']);
        expect(visible).not.toContain('admin');
        expect(NAV.filter((item) => item.available).map((item) => item.key)).toEqual([
            'today', 'round', 'clients', 'handover', 'assurance', 'reports',
        ]);
    });
});
