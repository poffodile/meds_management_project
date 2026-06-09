import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import RoundProgressDonut from '@frontend/components/RoundProgressDonut';

describe('RoundProgressDonut', () => {
    it('shows the centre ratio and legend', () => {
        renderWithMantine(<RoundProgressDonut completed={8} dueSoon={2} overdue={1} notStarted={1} />);
        expect(screen.getByText('8/12')).toBeInTheDocument();
        expect(screen.getByText('Completed')).toBeInTheDocument();
        expect(screen.getByText('Overdue')).toBeInTheDocument();
    });
});
