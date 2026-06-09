import { describe, it, expect } from 'vitest';
import { screen, fireEvent, within } from '@testing-library/react';
import { renderWithMantine } from '@frontend/test/render';
import DataTable from '@frontend/components/DataTable';

const columns = [
    { key: 'name', label: 'Name' },
    { key: 'qty', label: 'Qty' },
];
const data = [
    { id: 1, name: 'Paracetamol', qty: 5 },
    { id: 2, name: 'Ibuprofen', qty: 12 },
    { id: 3, name: 'Aspirin', qty: 1 },
];

describe('DataTable', () => {
    it('renders all rows', () => {
        renderWithMantine(<DataTable columns={columns} data={data} />);
        expect(screen.getByText('Paracetamol')).toBeInTheDocument();
        expect(screen.getByText('Ibuprofen')).toBeInTheDocument();
        expect(screen.getByText('Aspirin')).toBeInTheDocument();
    });

    it('shows the empty message when there is no data', () => {
        renderWithMantine(<DataTable columns={columns} data={[]} emptyMessage="Nothing here." />);
        expect(screen.getByText('Nothing here.')).toBeInTheDocument();
    });

    it('filters rows via the search box', () => {
        renderWithMantine(<DataTable columns={columns} data={data} searchable />);
        fireEvent.change(screen.getByPlaceholderText('Search…'), { target: { value: 'ibu' } });
        expect(screen.getByText('Ibuprofen')).toBeInTheDocument();
        expect(screen.queryByText('Paracetamol')).not.toBeInTheDocument();
    });

    it('sorts by a column when its header is clicked', () => {
        renderWithMantine(<DataTable columns={columns} data={data} />);
        fireEvent.click(screen.getByText('Name'));
        const rows = screen.getAllByRole('row').slice(1); // drop header row
        const firstCell = within(rows[0]).getAllByRole('cell')[0];
        expect(firstCell).toHaveTextContent('Aspirin'); // alphabetical ascending
    });

    it('uses a custom render function for a column', () => {
        const cols = [{ key: 'qty', label: 'Qty', render: (r) => `${r.qty} units` }];
        renderWithMantine(<DataTable columns={cols} data={[{ id: 1, qty: 5 }]} />);
        expect(screen.getByText('5 units')).toBeInTheDocument();
    });
});
