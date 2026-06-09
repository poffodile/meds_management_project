import { describe, it, expect, vi } from 'vitest';
import { screen, fireEvent } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import QuickActionItem from '@frontend/components/QuickActionItem';

describe('QuickActionItem', () => {
    it('shows the label + description and fires onClick', () => {
        const onClick = vi.fn();
        renderWithMantine(<QuickActionItem label="Add PRN" description="Record PRN medication" onClick={onClick} />);
        expect(screen.getByText('Add PRN')).toBeInTheDocument();
        expect(screen.getByText('Record PRN medication')).toBeInTheDocument();
        fireEvent.click(screen.getByText('Add PRN'));
        expect(onClick).toHaveBeenCalledOnce();
    });

    it('renders an anchor when given an href', () => {
        renderWithMantine(<QuickActionItem label="View MAR Report" href="/mar" />);
        expect(screen.getByText('View MAR Report').closest('a')).toHaveAttribute('href', '/mar');
    });
});
