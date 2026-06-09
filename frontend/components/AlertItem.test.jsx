import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import AlertItem from '@frontend/components/AlertItem';

describe('AlertItem', () => {
    it('shows the title and description', () => {
        renderWithMantine(<AlertItem severity="danger" title="1 Overdue Medication" description="Sarah Jones — Warfarin 3mg" />);
        expect(screen.getByText('1 Overdue Medication')).toBeInTheDocument();
        expect(screen.getByText('Sarah Jones — Warfarin 3mg')).toBeInTheDocument();
    });
});
