import { Box, Text } from '@mantine/core';
import { IconShieldLock, IconPill } from '@tabler/icons-react';
import { palette, statusPalette } from '@frontend/tokens';

/**
 * Reusable medication-stock atoms — shared by the Stock, Stock 2 and (where
 * relevant) Controlled-drug pages so the look stays identical and lives in ONE
 * place. All colours come from the unified palette in frontend/tokens.js.
 */

const numeric = { fontVariantNumeric: 'tabular-nums', fontFeatureSettings: '"tnum" 1' };

// Neutral icon tile for a medicine (purple = controlled, slate = normal).
export function MedTile({ controlled, size = 38, radius = 11, icon = 18 }) {
    return (
        <Box style={{ width: size, height: size, borderRadius: radius, flexShrink: 0, display: 'grid', placeItems: 'center', background: palette.tileBg, border: `1px solid ${palette.tileBorder}`, color: controlled ? palette.controlledIcon : palette.normalIcon }}>
            {controlled ? <IconShieldLock size={icon} stroke={1.8} /> : <IconPill size={icon} stroke={1.8} />}
        </Box>
    );
}

// Quiet controlled-drug tag pill.
export function CdTag({ schedule }) {
    return (
        <Box style={{ flexShrink: 0, fontSize: 10, fontWeight: 700, letterSpacing: 0.3, color: 'light-dark(#6E5A94, #CBBCE4)', background: 'light-dark(#F0ECF8, #221C2B)', borderRadius: 6, padding: '1px 6px' }}>
            CD{schedule ? ` ${schedule}` : ''}
        </Box>
    );
}

// 5-state status mark — "In stock" is plain quiet text (colour only marks
// problems); low/out/expiring/expired get a small muted, dark-aware chip.
export function StockStatusChip({ bucket }) {
    const s = statusPalette[bucket];
    if (!s) return null;
    if (bucket === 'healthy') return <Text fz={12} c={palette.ink2}>In stock</Text>;
    return (
        <Box style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '3px 10px 3px 8px', borderRadius: 999, fontSize: 11, fontWeight: 600, background: s.bg, color: s.c }}>
            <Box w={6} h={6} style={{ borderRadius: '50%', background: s.dot, flexShrink: 0 }} />{s.label}
        </Box>
    );
}

// Thin muted fill bar — replaces Mantine <Progress> so bar colours stay calm.
// `maxW` caps the track length (so it needn't span the whole column).
export function MutedBar({ pct, hex, mb, maxW }) {
    return (
        <Box mb={mb} style={{ height: 5, maxWidth: maxW, borderRadius: 999, background: palette.progressTrack, overflow: 'hidden' }}>
            <Box style={{ width: `${pct}%`, height: '100%', borderRadius: 999, background: hex, opacity: 0.9 }} />
        </Box>
    );
}

export { numeric as stockNumeric };
