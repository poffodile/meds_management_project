import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import PageHeader from '@frontend/components/PageHeader';

describe('PageHeader', () => {
    it('shows the title and subtitle', () => {
        renderWithMantine(<PageHeader title="Medication Stock" subtitle="Inventory and disposals" />);
        expect(screen.getByText('Medication Stock')).toBeInTheDocument();
        expect(screen.getByText('Inventory and disposals')).toBeInTheDocument();
    });

    it('renders actions on the right', () => {
        renderWithMantine(<PageHeader title="Stock" actions={<button>Add</button>} />);
        expect(screen.getByText('Add')).toBeInTheDocument();
    });
});
