import { Box, useComputedColorScheme } from '@mantine/core';
import logoWhite from '@frontend/assets/logo-careoneos.png';
import logoNavy from '@frontend/assets/logo-careoneos-dark.png';

/**
 * Care One OS logo that picks the right asset for its surface.
 *
 *   tone="onDark"  — always the white wordmark (e.g. a navy surface).
 *   tone="onLight" — always the navy wordmark (a fixed white/light surface).
 *   tone="auto"    — follows the colour scheme: navy in light mode, white in dark
 *                    mode. Renders a single <img> chosen at render time (not two
 *                    CSS-hidden ones) so only one logo ever shows.
 *
 * Both assets share the same coloured ring + teal "OS"; only the wordmark colour
 * differs, so swapping is purely a contrast concern.
 */
export default function BrandLogo({ tone = 'auto', height = 24, alt = 'Care One OS', style, ...rest }) {
    const scheme = useComputedColorScheme('light');
    const imgStyle = { height, width: 'auto', display: 'block', ...style };

    let src = logoNavy;
    if (tone === 'onDark') src = logoWhite;
    else if (tone === 'onLight') src = logoNavy;
    else src = scheme === 'dark' ? logoWhite : logoNavy;

    return <Box component="img" src={src} alt={alt} style={imgStyle} {...rest} />;
}
