import { useMemo, useState } from 'react';
import { Table, TextInput, Group, Pagination, Text, UnstyledButton } from '@mantine/core';

/**
 * DataTable — the reusable list table for the whole app.
 * Gives every page search + sortable columns + pagination for free.
 *
 * Props:
 *   columns   — [{ key, label, render?(row), sortable? }]   (render is optional; sortable defaults true)
 *   data      — array of row objects (each ideally has an `id`)
 *   searchable — show a search box that filters across all columns (default false)
 *   pageSize  — rows per page (default 10)
 *   emptyMessage — shown when there are no rows
 *   minWidth  — horizontal scroll threshold (default 600)
 */
function sortData(data, key, dir) {
    if (!key) return data;
    const sorted = [...data].sort((a, b) => {
        const av = a[key];
        const bv = b[key];
        if (av == null && bv == null) return 0;
        if (av == null) return 1;
        if (bv == null) return -1;
        if (typeof av === 'number' && typeof bv === 'number') return av - bv;
        return String(av).localeCompare(String(bv), undefined, { numeric: true });
    });
    return dir === 'desc' ? sorted.reverse() : sorted;
}

export default function DataTable({
    columns,
    data = [],
    searchable = false,
    pageSize = 10,
    emptyMessage = 'No data found.',
    minWidth = 600,
}) {
    const [query, setQuery] = useState('');
    const [sort, setSort] = useState({ key: null, dir: 'asc' });
    const [page, setPage] = useState(1);

    const filtered = useMemo(() => {
        if (!query.trim()) return data;
        const q = query.toLowerCase();
        return data.filter((row) =>
            columns.some((c) => String(row[c.key] ?? '').toLowerCase().includes(q))
        );
    }, [data, query, columns]);

    const sorted = useMemo(() => sortData(filtered, sort.key, sort.dir), [filtered, sort]);

    const totalPages = Math.max(1, Math.ceil(sorted.length / pageSize));
    const current = Math.min(page, totalPages);
    const paged = sorted.slice((current - 1) * pageSize, current * pageSize);

    const toggleSort = (col) => {
        if (col.sortable === false) return;
        setSort((s) => (s.key === col.key ? { key: col.key, dir: s.dir === 'asc' ? 'desc' : 'asc' } : { key: col.key, dir: 'asc' }));
        setPage(1);
    };

    return (
        <>
            {searchable && (
                <Group mb="sm" justify="flex-end">
                    <TextInput
                        placeholder="Search…"
                        value={query}
                        onChange={(e) => { setQuery(e.currentTarget.value); setPage(1); }}
                        w={260}
                    />
                </Group>
            )}

            <Table.ScrollContainer minWidth={minWidth}>
                <Table striped highlightOnHover verticalSpacing="sm">
                    <Table.Thead>
                        <Table.Tr>
                            {columns.map((c) => {
                                const sortable = c.sortable !== false;
                                const active = sort.key === c.key;
                                return (
                                    <Table.Th key={c.key}>
                                        {sortable ? (
                                            <UnstyledButton onClick={() => toggleSort(c)} style={{ fontWeight: 700, fontSize: 'inherit' }}>
                                                {c.label}{active ? (sort.dir === 'asc' ? ' ▲' : ' ▼') : ''}
                                            </UnstyledButton>
                                        ) : c.label}
                                    </Table.Th>
                                );
                            })}
                        </Table.Tr>
                    </Table.Thead>
                    <Table.Tbody>
                        {paged.length ? paged.map((row, i) => (
                            <Table.Tr key={row.id ?? i}>
                                {columns.map((c) => (
                                    <Table.Td key={c.key}>{c.render ? c.render(row) : (row[c.key] ?? '—')}</Table.Td>
                                ))}
                            </Table.Tr>
                        )) : (
                            <Table.Tr>
                                <Table.Td colSpan={columns.length}>
                                    <Text c="dimmed" ta="center" py="lg">{emptyMessage}</Text>
                                </Table.Td>
                            </Table.Tr>
                        )}
                    </Table.Tbody>
                </Table>
            </Table.ScrollContainer>

            {totalPages > 1 && (
                <Group justify="space-between" mt="md">
                    <Text size="sm" c="dimmed">{sorted.length} result{sorted.length === 1 ? '' : 's'}</Text>
                    <Pagination total={totalPages} value={current} onChange={setPage} size="sm" />
                </Group>
            )}
        </>
    );
}
