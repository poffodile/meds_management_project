import { describe, it, expect, vi } from 'vitest';
import { screen, fireEvent } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import ConfirmDialog from '@frontend/components/ConfirmDialog';

describe('ConfirmDialog', () => {
    it('shows the message and calls onConfirm when confirmed', () => {
        const onConfirm = vi.fn();
        renderWithMantine(
            <ConfirmDialog opened onClose={() => {}} onConfirm={onConfirm} message="Delete this?" confirmLabel="Delete" />
        );
        expect(screen.getByText('Delete this?')).toBeInTheDocument();
        fireEvent.click(screen.getByText('Delete'));
        expect(onConfirm).toHaveBeenCalledOnce();
    });
});
