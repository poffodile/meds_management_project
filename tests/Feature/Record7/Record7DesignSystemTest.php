<?php

namespace Tests\Feature\Record7;

use Tests\TestCase;

/**
 * Record7 is a design system, not a pile of per-screen styling.
 *
 * These are the rules that keep it one:
 *   every colour, size, space, radius and shadow comes from r7-tokens.css;
 *   no page or component carries a hard-coded value;
 *   changing one token changes every screen;
 *   and both themes stay legible.
 *
 * All file-based, so they run anywhere without a browser.
 */
class Record7DesignSystemTest extends TestCase
{
    private function tokens(): string
    {
        return file_get_contents(resource_path('css/record7/r7-tokens.css'));
    }

    private function stylesheet(): string
    {
        return file_get_contents(resource_path('css/record7/r7.css'));
    }

    private function withoutComments(string $source): string
    {
        $source = preg_replace('#/\*.*?\*/#s', '', $source);

        return preg_replace('#^\s*//.*$#m', '', $source);
    }

    /* ── Everything comes from the token file ────────────────────────────── */

    public function test_the_component_stylesheet_contains_no_hard_coded_colours(): void
    {
        $css = $this->withoutComments($this->stylesheet());

        preg_match_all('/#[0-9A-Fa-f]{3,8}\b/', $css, $hex);
        preg_match_all('/\b(?:rgba?|hsla?)\s*\(/i', $css, $functional);

        $this->assertSame([], $hex[0],
            'r7.css must take every colour from a token: '.implode(', ', array_unique($hex[0])));

        $this->assertSame([], $functional[0],
            'r7.css must take every colour from a token, including rgb and hsl values.');
    }

    public function test_no_record7_page_or_component_contains_a_hard_coded_colour(): void
    {
        $offenders = [];

        foreach ($this->record7SourceFiles() as $file) {
            $source = $this->withoutComments(file_get_contents($file));

            // Ignore the showcase's token NAMES, which are strings like
            // '--r7-colour-primary' rather than values.
            $source = preg_replace('/--r7-[a-z0-9-]+/i', '', $source);

            if (preg_match_all('/#[0-9A-Fa-f]{6}\b|\brgba?\s*\(/i', $source, $m)) {
                $offenders[] = basename($file).': '.implode(', ', array_unique($m[0]));
            }
        }

        $this->assertSame([], $offenders,
            'Record7 pages must use tokens, never literal colours. '.implode(' | ', $offenders));
    }

    public function test_the_stylesheet_hard_codes_no_pixel_sizes(): void
    {
        $css = $this->withoutComments($this->stylesheet());

        // Media-query breakpoints are the deliberate exception: a breakpoint is
        // not a design value a screen consumes, and a custom property cannot be
        // used inside a media condition at all.
        $css = preg_replace('/@media[^{]*\{/', '', $css);

        // A zero is a zero in any unit, and env() fallbacks must state one.
        $css = str_replace('0px', '0', $css);

        preg_match_all('/:\s*[^;{}]*?(?<![\w-])(\d+)px/', $css, $pixels);

        $this->assertSame([], $pixels[0],
            'Sizes belong in r7-tokens.css: '.implode(', ', array_unique($pixels[0])));
    }

    /* ── The token file actually covers what was asked for ───────────────── */

    public function test_the_token_file_covers_every_category(): void
    {
        $tokens = $this->tokens();

        foreach ([
            '--r7-colour-primary', '--r7-colour-accent',
            '--r7-surface-page', '--r7-surface-raised', '--r7-surface-solid',
            '--r7-text-primary', '--r7-text-secondary', '--r7-text-on-solid',
            '--r7-border-subtle', '--r7-border-strong', '--r7-border-width',
            '--r7-state-success', '--r7-state-warning', '--r7-state-error', '--r7-state-info',
            '--r7-font-display', '--r7-font-body',
            '--r7-size-base', '--r7-weight-semi', '--r7-leading-body',
            '--r7-space-4', '--r7-radius-md', '--r7-shadow-md',
            '--r7-focus-ring', '--r7-hover-tint', '--r7-control-height',
        ] as $token) {
            $this->assertStringContainsString($token.':', $tokens, $token.' is missing');
        }
    }

    public function test_every_token_the_stylesheet_uses_is_actually_defined(): void
    {
        preg_match_all('/var\((--r7-[a-z0-9-]+)/i', $this->stylesheet(), $used);

        $tokens = $this->tokens();
        $missing = [];

        foreach (array_unique($used[1]) as $token) {
            if (! str_contains($tokens, $token.':')) {
                $missing[] = $token;
            }
        }

        $this->assertSame([], $missing,
            'The stylesheet uses tokens that are not defined: '.implode(', ', $missing));
    }

    public function test_the_documented_typefaces_are_the_only_ones_named(): void
    {
        $tokens = $this->tokens();

        $this->assertStringContainsString('"Sora"', $tokens);
        $this->assertStringContainsString('"Outfit"', $tokens);

        foreach (['IBM Plex', 'Manrope', 'Plus Jakarta', 'JetBrains'] as $wrong) {
            $this->assertStringNotContainsString($wrong, $tokens, $wrong.' is not the Record7 typeface');
        }
    }

    /* ── Contrast, in both themes ────────────────────────────────────────── */

    /**
     * Every pairing that a person has to read, checked against WCAG 2.2 AA.
     *
     * Normal text needs 4.5 to 1. Large text, and the border of a control,
     * need 3 to 1. These are the pairings that actually appear on screen —
     * buttons, the logo tile, selected controls, links, errors and focus.
     */
    public function test_text_and_controls_meet_contrast_in_both_themes(): void
    {
        $failures = [];

        foreach (['light', 'dark'] as $theme) {
            $t = $this->themeTokens($theme);

            $pairs = [
                ['body text', $t['--r7-text-primary'], $t['--r7-surface-page'], 4.5],
                ['body text on a card', $t['--r7-text-primary'], $t['--r7-surface-raised'], 4.5],
                ['secondary text', $t['--r7-text-secondary'], $t['--r7-surface-page'], 4.5],
                ['secondary text on a card', $t['--r7-text-secondary'], $t['--r7-surface-raised'], 4.5],
                ['primary button label', $t['--r7-text-on-solid'], $t['--r7-surface-solid'], 4.5],
                ['logo tile', $t['--r7-text-on-solid'], $t['--r7-surface-solid'], 4.5],
                ['link', $t['--r7-text-accent'], $t['--r7-surface-page'], 4.5],
                ['link on a card', $t['--r7-text-accent'], $t['--r7-surface-raised'], 4.5],
                ['error text', $t['--r7-state-error'], $t['--r7-surface-page'], 4.5],
                ['error text on its own surface', $t['--r7-state-error'], $t['--r7-state-error-surface'], 4.5],
                ['success text on its own surface', $t['--r7-state-success'], $t['--r7-state-success-surface'], 4.5],
                ['warning text on its own surface', $t['--r7-state-warning'], $t['--r7-state-warning-surface'], 4.5],
                ['info text on its own surface', $t['--r7-state-info'], $t['--r7-state-info-surface'], 4.5],
                ['safety warning text', $t['--r7-state-danger'], $t['--r7-state-danger-surface'], 4.5],
                ['selected control label', $t['--r7-text-accent'], $t['--r7-surface-accent'], 4.5],
                // The sign-in brand panel. A note here once used the rule
                // colour and measured 1.39 to 1 — invisible in practice.
                ['brand panel heading', $t['--r7-brand-panel-ink'], $t['--r7-brand-panel'], 4.5],
                ['brand panel supporting text', $t['--r7-brand-panel-muted'], $t['--r7-brand-panel'], 4.5],
                ['brand panel accent', $t['--r7-brand-panel-accent'], $t['--r7-brand-panel'], 4.5],
                // The smallest text on the deep panel — the right numbers, the
                // access notice. It is the pairing most likely to be let go.
                ['brand panel dim text', $t['--r7-brand-panel-dim'], $t['--r7-brand-panel'], 4.5],
                // The shift briefing is its own ground — deep navy on the warm
                // theme, deeper than the page on the dark one — so every word
                // written on it needs checking against IT, not against the
                // panel token it used to borrow.
                ['briefing heading', $t['--r7-brand-panel-ink'], $t['--r7-briefing-top'], 4.5],
                ['briefing body', $t['--r7-brand-panel-muted'], $t['--r7-briefing-top'], 4.5],
                ['briefing dim text', $t['--r7-brand-panel-dim'], $t['--r7-briefing-top'], 4.5],
                ['briefing accent', $t['--r7-brand-panel-accent'], $t['--r7-briefing-top'], 4.5],
                // The interaction colour, wherever it carries meaning.
                ['interactive text', $t['--r7-colour-interactive'], $t['--r7-surface-page'], 4.5],
                ['active step marker', $t['--r7-colour-interactive'], $t['--r7-surface-page'], 3.0],
                // Non-text: 3 to 1 is the bar for a control boundary.
                ["focus ring", $t["--r7-colour-interactive"], $t['--r7-surface-page'], 3.0],
                ['input border', $t['--r7-border-strong'], $t['--r7-surface-page'], 3.0],
                ['solid surface against the page', $t['--r7-surface-solid'], $t['--r7-surface-page'], 3.0],
            ];

            foreach ($pairs as [$what, $foreground, $background, $minimum]) {
                $ratio = $this->contrast($foreground, $background);

                if ($ratio < $minimum) {
                    $failures[] = sprintf(
                        '%s theme, %s: %.2f to 1 (needs %.1f) — %s on %s',
                        $theme, $what, $ratio, $minimum, $foreground, $background
                    );
                }
            }
        }

        $this->assertSame([], $failures, "Contrast failures:\n".implode("\n", $failures));
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    /** Every token value for one theme, with light values as the base. */
    private function themeTokens(string $theme): array
    {
        $tokens = $this->tokens();
        $values = [];

        $light = $this->block($tokens, '.r7-root {');
        preg_match_all('/(--r7-[a-z0-9-]+):\s*(#[0-9A-Fa-f]{3,8})/i', $light, $m, PREG_SET_ORDER);

        foreach ($m as $match) {
            $values[$match[1]] = strtoupper($match[2]);
        }

        if ($theme === 'dark') {
            $dark = $this->block($tokens, '.r7-root[data-theme="dark"] {');
            preg_match_all('/(--r7-[a-z0-9-]+):\s*(#[0-9A-Fa-f]{3,8})/i', $dark, $m, PREG_SET_ORDER);

            foreach ($m as $match) {
                $values[$match[1]] = strtoupper($match[2]);
            }
        }

        return $values;
    }

    private function block(string $source, string $selector): string
    {
        $start = strpos($source, $selector);

        $this->assertNotFalse($start, 'Could not find the block '.$selector);

        $end = strpos($source, "\n}", $start);

        return substr($source, $start, $end - $start);
    }

    /** WCAG relative-luminance contrast ratio between two hex colours. */
    private function contrast(string $a, string $b): float
    {
        $l1 = $this->luminance($a);
        $l2 = $this->luminance($b);

        [$light, $dark] = $l1 >= $l2 ? [$l1, $l2] : [$l2, $l1];

        return ($light + 0.05) / ($dark + 0.05);
    }

    private function luminance(string $hex): float
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $channels = [];

        foreach ([0, 2, 4] as $offset) {
            $value = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $value <= 0.03928
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    private function record7SourceFiles(): array
    {
        $files = [resource_path('js/r7.jsx')];

        foreach ([resource_path('js/record7'), resource_path('js/R7Pages')] as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $entry) {
                if ($entry->isFile() && in_array($entry->getExtension(), ['jsx', 'js'], true)) {
                    $files[] = $entry->getPathname();
                }
            }
        }

        return $files;
    }
}
