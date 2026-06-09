import { describe, it, expect, vi } from 'vitest';
import { screen, fireEvent } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import FormModal from '@frontend/components/FormModal';

describe('FormModal', () => {
    it('renders the title and children and submits on Save', () => {
        const onSubmit = vi.fn();
        renderWithMantine(
            <FormModal opened onClose={() => {}} title="Add item" onSubmit={onSubmit} submitLabel="Save">
                <input aria-label="Name" />
            </FormModal>
        );
        expect(screen.getByText('Add item')).toBeInTheDocument();
        expect(screen.getByLabelText('Name')).toBeInTheDocument();
        fireEvent.click(screen.getByText('Save'));
        expect(onSubmit).toHaveBeenCalledOnce();
    });

    it('calls onClose when Cancel is pressed', () => {
        const onClose = vi.fn();
        renderWithMantine(
            <FormModal opened onClose={onClose} title="Add item" onSubmit={() => {}}>
                <input aria-label="Name" />
            </FormModal>
        );
        fireEvent.click(screen.getByText('Cancel'));
        expect(onClose).toHaveBeenCalledOnce();
    });
});
