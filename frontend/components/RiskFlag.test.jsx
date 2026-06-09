import { describe, it, expect } from 'vitest';
import { screen } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import RiskFlag from '@frontend/components/RiskFlag';

describe('RiskFlag', () => {
    it('shows the risk label', () => {
        renderWithMantine(<RiskFlag label="Falls Risk" level="high" />);
        expect(screen.getByText('Falls Risk')).toBeInTheDocument();
    });
});
