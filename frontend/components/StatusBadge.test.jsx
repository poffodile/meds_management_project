import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import StatusBadge from '@frontend/components/StatusBadge';

describe('StatusBadge', () => {
    it('shows the status text', () => {
        renderWithMantine(<StatusBadge status="received" />);
        expect(screen.getByText('received')).toBeInTheDocument();
    });

    it('uses a custom label when provided', () => {
        renderWithMantine(<StatusBadge status="low" label="Low stock" />);
        expect(screen.getByText('Low stock')).toBeInTheDocument();
    });
});
