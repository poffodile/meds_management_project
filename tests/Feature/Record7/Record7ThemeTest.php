<?php

namespace Tests\Feature\Record7;

use Tests\TestCase;

/**
 * The two theme faults that reached a reviewer's screen, kept out for good.
 *
 * WHAT WENT WRONG
 * The primary button, the brand tile and the selected filter all painted their
 * background from --r7-midnight and their text from --r7-ink-inverse. On the
 * dark theme both tokens resolved to #0E1922, so all three were the same colour
 * as their own text and simply vanished. The Sign in button could only be found
 * by hovering it.
 *
 * And the dark theme applied itself from the operating system, so a reviewer
 * expecting the documented warm cream got midnight with no way back.
 *
 * These read the stylesheet rather than a browser, so they run anywhere.
 */
class Record7ThemeTest extends TestCase
{
    private function tokens(): string
    {
        return file_get_contents(resource_path('css/record7/r7-tokens.css'));
    }

    private function stylesheet(): string
    {
        return file_get_contents(resource_path('css/record7/r7.css'));
    }

    /** Pull one token's value out of a named block. */
    private function token(string $block, string $name): ?string
    {
        $tokens = $this->tokens();
        $start = strpos($tokens, $block);

        if ($start === false) {
            return null;
        }

        $end = strpos($tokens, '}', $start);
        $slice = substr($tokens, $start, $end - $start);

        return preg_match('/'.preg_quote($name, '/').':\s*(#[0-9A-Fa-f]{3,8})/', $slice, $m)
            ? strtoupper($m[1])
            : null;
    }

    /* ── The vanishing controls ──────────────────────────────────────────── */

    public function test_a_filled_control_never_takes_its_colour_from_the_page_ink(): void
    {
        // --r7-midnight is the ink and the dark ground. Nothing filled may use
        // it as a background, because on the dark theme it becomes the page.
        $this->assertStringNotContainsString(
            'background: var(--r7-colour-primary)',
            $this->stylesheet(),
            'A filled control is using the ink token as its background. '
            .'Use --r7-surface-solid with --r7-text-on-solid instead.'
        );
    }

    public function test_the_solid_surface_and_its_text_differ_in_both_themes(): void
    {
        foreach ([
            'light' => '.r7-root {',
            'dark' => '.r7-root[data-theme="dark"] {',
        ] as $theme => $block) {
            $solid = $this->token($block, '--r7-surface-solid');
            $onSolid = $this->token($block, '--r7-text-on-solid');

            $this->assertNotNull($solid, "--r7-surface-solid must be defined for the {$theme} theme");
            $this->assertNotNull($onSolid, "--r7-text-on-solid must be defined for the {$theme} theme");

            $this->assertNotSame(
                $solid,
                $onSolid,
                "On the {$theme} theme a filled control and its own text are the same colour, "
                .'so the control is invisible.'
            );
        }
    }

    public function test_an_icon_on_a_filled_control_is_not_the_control_colour(): void
    {
        // The dark theme fills the primary button with the brand teal. An arrow
        // painted from the accent token was then teal on teal and disappeared —
        // the same fault as the vanishing button, one element further in.
        foreach ([
            'light' => '.r7-root {',
            'dark' => '.r7-root[data-theme="dark"] {',
        ] as $theme => $block) {
            $icon = $this->token($block, '--r7-icon-on-solid');

            $this->assertNotNull($icon, "--r7-icon-on-solid must be defined for the {$theme} theme");
            $this->assertNotSame(
                $icon,
                $this->token($block, '--r7-surface-solid'),
                "On the {$theme} theme the arrow on a filled control is the colour of the control."
            );
        }
    }

    public function test_the_solid_surface_is_not_the_page_colour_in_either_theme(): void
    {
        foreach ([
            'light' => '.r7-root {',
            'dark' => '.r7-root[data-theme="dark"] {',
        ] as $theme => $block) {
            $this->assertNotSame(
                $this->token($block, '--r7-surface-solid'),
                $this->token($block, '--r7-surface-page'),
                "On the {$theme} theme a filled control is the same colour as the page behind it."
            );
        }
    }

    public function test_the_primary_button_uses_the_solid_tokens(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.r7-btn--primary\s*\{\s*background:\s*var\(--r7-surface-solid\);\s*color:\s*var\(--r7-text-on-solid\)/',
            $this->stylesheet()
        );
    }

    /* ── Warm cream is the default ───────────────────────────────────────── */

    public function test_the_theme_is_a_choice_and_not_taken_from_the_operating_system(): void
    {
        $this->assertStringNotContainsString(
            'prefers-color-scheme',
            $this->tokens(),
            'Record7 opens in the documented warm cream on every device. '
            .'Dark is chosen by the person, not by their operating system.'
        );
    }

    public function test_the_default_ground_is_the_documented_warm_cream(): void
    {
        // Refined once, against a supplied design reference: a fractionally
        // deeper, less yellow cream. Still cream, still the default, still not
        // taken from the operating system — which is what this test is for.
        $this->assertSame('#F7F1E4', $this->token('.r7-root {', '--r7-surface-page'));
    }

    public function test_the_dark_theme_is_reachable_and_still_scoped(): void
    {
        $tokens = $this->tokens();

        $this->assertStringContainsString('.r7-root[data-theme="dark"]', $tokens);

        // Scoping still holds: the dark block is a .r7-root selector, so it
        // cannot leak into another front end.
        $this->assertStringNotContainsString(':root[data-theme', $tokens);
        $this->assertStringNotContainsString('html[data-theme', $tokens);
    }

    public function test_a_theme_toggle_is_actually_offered(): void
    {
        $this->assertFileExists(resource_path('js/record7/components/ThemeToggle.jsx'));
        $this->assertFileExists(resource_path('js/record7/useTheme.js'));

        // The two shells carry it, so every screen inside them has it.
        foreach ([
            'js/record7/components/AuthShell.jsx',
            'js/record7/components/AppShell.jsx',
        ] as $shell) {
            $this->assertStringContainsString(
                '<ThemeToggle />',
                file_get_contents(resource_path($shell)),
                $shell.' must offer the theme toggle'
            );
        }

        // And every page sits in one of them rather than hand-rolling a frame,
        // which is what guarantees the toggle is always reachable.
        foreach ([
            'js/R7Pages/Lock.jsx' => 'AuthShell',
            'js/R7Pages/Auth/SignIn.jsx' => 'AuthShell',
            'js/R7Pages/Auth/Verify.jsx' => 'AuthShell',
            'js/R7Pages/Auth/Houses.jsx' => 'AuthShell',
            'js/R7Pages/Today.jsx' => 'AppShell',
            'js/R7Pages/Audit.jsx' => 'AppShell',
        ] as $page => $shell) {
            $this->assertStringContainsString(
                "components/{$shell}.jsx",
                file_get_contents(resource_path($page)),
                $page.' must sit inside '.$shell
            );
        }
    }

    public function test_the_stored_theme_is_read_defensively(): void
    {
        $hook = file_get_contents(resource_path('js/record7/useTheme.js'));

        // Storage throws in private windows and where site data is blocked.
        $this->assertStringContainsString('catch', $hook);
        // Read on the first render, defaulting to light, and guarded.
        $this->assertStringContainsString('useState(stored)', $hook);
        $this->assertStringContainsString("return 'light';", $hook);
    }

    /* ── Seeing the password ─────────────────────────────────────────────── */

    public function test_every_password_field_offers_a_reveal_control(): void
    {
        $this->assertFileExists(resource_path('js/record7/components/PasswordField.jsx'));

        $component = file_get_contents(resource_path('js/record7/components/PasswordField.jsx'));

        $this->assertStringContainsString('Show', $component);
        $this->assertStringContainsString('Hide', $component);
        $this->assertStringContainsString('aria-pressed', $component);
        // It must start hidden: revealing is a deliberate act.
        $this->assertStringContainsString('useState(false)', $component);
    }

    public function test_no_record7_page_hand_rolls_a_bare_password_input(): void
    {
        $offenders = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('js/R7Pages'), \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $entry) {
            if (! $entry->isFile()) {
                continue;
            }

            if (str_contains(file_get_contents($entry->getPathname()), 'type="password"')) {
                $offenders[] = $entry->getFilename();
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Use PasswordField so the reveal control is always offered: '.implode(', ', $offenders)
        );
    }
}
