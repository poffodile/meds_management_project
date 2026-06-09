import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import MetricChip from '@frontend/components/MetricChip';

describe('MetricChip', () => {
    it('shows the label and value', () => {
        renderWithMantine(<MetricChip label="PRN Available" value={2} color="blue" />);
        expect(screen.getByText('PRN Available')).toBeInTheDocument();
        expect(screen.getByText('2')).toBeInTheDocument();
    });
});
