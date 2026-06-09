import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import StatCard from '@frontend/components/StatCard';

describe('StatCard', () => {
    it('shows the label and value', () => {
        renderWithMantine(<StatCard label="In stock" value={42} />);
        expect(screen.getByText('In stock')).toBeInTheDocument();
        expect(screen.getByText('42')).toBeInTheDocument();
    });

    it('shows the sublabel when provided', () => {
        renderWithMantine(<StatCard label="Low" value={3} sublabel="needs reorder" />);
        expect(screen.getByText('needs reorder')).toBeInTheDocument();
    });
});
