<?php

namespace Tests\Feature\Record7;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Record7 must be separable.
 *
 * These are the tests that keep the promise: its own database, its own bundle,
 * its own stylesheet, its own components and its own routes, borrowing no
 * design from frontend1, frontend2, frontend3 or frontend4. If someone later
 * imports an f4 component, writes an unscoped CSS rule, points a Record7 model
 * at the legacy database or leaves a dot separator in a label, this suite fails
 * before it ships.
 *
 * File-based assertions, so no fixture and no login are needed.
 */
class Record7IsolationTest extends TestCase
{
    private const OTHER_FRONT_ENDS = ['@frontend', '@frontend2', '@frontend3', '@frontend4'];

    /* ── Separate database ───────────────────────────────────────────────── */

    public function test_record7_uses_its_own_database_connection(): void
    {
        $this->assertNotNull(config('database.connections.record7'));

        $database = DB::connection('record7')->getDatabaseName();

        $this->assertStringStartsNotWith(
            'laravel',
            $database,
            'Record7 must not share the legacy database. It is pointed at: '.$database
        );
    }

    public function test_every_record7_table_is_prefixed_and_separate(): void
    {
        $tables = DB::connection('record7')
            ->select('SHOW TABLES FROM `'.DB::connection('record7')->getDatabaseName().'`');

        $names = array_map(fn ($row) => array_values((array) $row)[0], $tables);
        $names = array_values(array_diff($names, ['migrations']));

        $this->assertNotEmpty($names);

        foreach ($names as $name) {
            $this->assertStringStartsWith('record7_', $name, $name.' is not a Record7 table');
        }
    }

    public function test_the_legacy_tables_are_not_reachable_from_the_record7_connection(): void
    {
        $tables = DB::connection('record7')
            ->select('SHOW TABLES FROM `'.DB::connection('record7')->getDatabaseName().'`');
        $names = array_map(fn ($row) => array_values((array) $row)[0], $tables);

        foreach (['user', 'admin', 'home', 'service_user', 'mar_sheets'] as $legacy) {
            $this->assertNotContains($legacy, $names, 'The legacy table '.$legacy.' must not be here');
        }
    }

    /* ── Scoped stylesheet ───────────────────────────────────────────────── */

    public function test_every_record7_css_rule_is_scoped_under_the_root_class(): void
    {
        foreach (['r7.css', 'r7-tokens.css'] as $file) {
            $path = resource_path('css/record7/'.$file);
            $this->assertFileExists($path);

            $css = $this->withoutComments(file_get_contents($path));

            // Keyframe stops (0%, 50%, 100%) are not element selectors and
            // cannot carry a scope, so the animation blocks come out first.
            $css = preg_replace('/@keyframes[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/s', '', $css);

            $unscoped = [];

            preg_match_all('/([^{}]*)\{/', $css, $matches);

            foreach ($matches[1] as $prelude) {
                $prelude = trim(preg_replace('/\s+/', ' ', $prelude));

                if ($prelude === '' || str_starts_with($prelude, '@')) {
                    continue;
                }

                foreach (explode(',', $prelude) as $selector) {
                    $selector = trim($selector);

                    if ($selector !== '' && ! str_contains($selector, '.r7-root')) {
                        $unscoped[] = $selector;
                    }
                }
            }

            $this->assertSame([], $unscoped,
                $file.' has selectors outside .r7-root: '.implode(' | ', $unscoped));
        }
    }

    public function test_record7_css_imports_no_other_front_end_stylesheet(): void
    {
        $css = file_get_contents(resource_path('css/record7/r7.css'));

        preg_match_all('/@import\s+[\'"]([^\'"]+)[\'"]/', $css, $matches);

        foreach ($matches[1] as $import) {
            $this->assertStringStartsWith('./r7', $import,
                'Record7 may only import its own stylesheets; found: '.$import);
        }
    }

    /* ── The documented design direction ─────────────────────────────────── */

    public function test_the_documented_typefaces_are_the_ones_loaded(): void
    {
        $blade = file_get_contents(resource_path('views/r7.blade.php'));

        $this->assertStringContainsString('family=Outfit', $blade);
        $this->assertStringContainsString('Sora', $blade);

        foreach (['IBM+Plex', 'Manrope', 'Plus+Jakarta', 'Inter:'] as $wrongFace) {
            $this->assertStringNotContainsString($wrongFace, $blade,
                $wrongFace.' is not the documented Record7 typeface');
        }
    }

    public function test_the_tokens_carry_the_documented_palette(): void
    {
        $tokens = file_get_contents(resource_path('css/record7/r7-tokens.css'));

        $this->assertStringContainsString('--r7-colour-primary', $tokens);
        $this->assertStringContainsString('--r7-colour-accent', $tokens);
        $this->assertMatchesRegularExpression('/--r7-surface-page:\s*#F7F1E4/i', $tokens,
            'The page ground must be the documented warm cream');
    }

    /**
     * The direction rules out dot separators. This catches the middle-dot and
     * bullet characters used as separators in interface text.
     */
    public function test_no_record7_source_file_uses_a_dot_separator(): void
    {
        $offenders = [];

        foreach ($this->record7SourceFiles() as $file) {
            $source = $this->withoutComments(file_get_contents($file));

            if (preg_match('/[\x{00B7}\x{2022}\x{2027}\x{22C5}]/u', $source)) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame([], $offenders,
            'Dot separators are not used in Record7: '.implode(', ', $offenders));
    }

    /* ── Separate bundle ─────────────────────────────────────────────────── */

    public function test_the_record7_entry_point_loads_only_its_own_assets(): void
    {
        $entry = $this->withoutComments(file_get_contents(resource_path('js/r7.jsx')));

        $this->assertStringContainsString("import '../css/record7/r7.css'", $entry);
        $this->assertStringContainsString('./R7Pages/', $entry);
        $this->assertStringContainsString('r7-root', $entry);

        foreach (['app.css', 'f3.css', 'f4.css', 'F4Pages', 'F3Pages'] as $foreign) {
            $this->assertStringNotContainsString($foreign, $entry);
        }
    }

    public function test_no_record7_file_imports_from_another_front_end(): void
    {
        $offenders = [];

        foreach ($this->record7SourceFiles() as $file) {
            $source = $this->withoutComments(file_get_contents($file));

            foreach (self::OTHER_FRONT_ENDS as $alias) {
                if (preg_match('#[\'"]'.preg_quote($alias, '#').'/#', $source)) {
                    $offenders[] = basename($file).' imports '.$alias;
                }
            }
        }

        $this->assertSame([], $offenders, implode(', ', $offenders));
    }

    public function test_no_record7_php_class_references_a_frontend4_class(): void
    {
        $offenders = [];

        foreach ($this->record7PhpFiles() as $file) {
            $source = $this->withoutComments(file_get_contents($file));

            if (preg_match('/App\\\\(Models|Services|Http\\\\Controllers|Http\\\\Middleware)\\\\Frontend4/', $source)
                || preg_match('/Frontend4User|F4Controller/', $source)) {
                $offenders[] = basename($file);
            }
        }

        $this->assertSame([], $offenders,
            'Record7 must not depend on Frontend 4 code: '.implode(', ', $offenders));
    }

    public function test_the_record7_root_view_loads_only_the_record7_bundle(): void
    {
        $blade = file_get_contents(resource_path('views/r7.blade.php'));

        $this->assertStringContainsString("@vite(['resources/js/r7.jsx'])", $blade);

        foreach (['app.jsx', 'f3.jsx', 'f4.jsx', 'app.css'] as $foreign) {
            $this->assertStringNotContainsString($foreign, $blade);
        }
    }

    /* ── The other front ends are untouched ──────────────────────────────── */

    public function test_all_four_entry_points_are_registered(): void
    {
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach ([
            'resources/js/app.jsx', 'resources/js/f3.jsx',
            'resources/js/f4.jsx', 'resources/js/r7.jsx',
        ] as $entry) {
            $this->assertStringContainsString($entry, $vite);
        }

        $this->assertStringContainsString('@record7', $vite);
    }

    public function test_record7_did_not_disturb_the_legacy_or_frontend4_routes(): void
    {
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('frontend4.login'));
        $this->assertTrue(Route::has('frontend4.today'));

        $this->assertSame('login', Route::getRoutes()->getByName('login')->uri());
        $this->assertSame('frontend4/login', Route::getRoutes()->getByName('frontend4.login')->uri());
    }

    public function test_the_legacy_and_frontend4_guards_are_untouched(): void
    {
        $this->assertSame('users', config('auth.guards.web.provider'));
        $this->assertSame('frontend4_users', config('auth.guards.frontend4.provider'));
        $this->assertSame('record7_users', config('auth.guards.record7.provider'));
        $this->assertSame(
            \App\Models\Record7\User::class,
            config('auth.providers.record7_users.model')
        );
    }

    public function test_every_record7_route_lives_under_the_record7_prefix(): void
    {
        $strays = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();

            if ($name && str_starts_with($name, 'record7.') && ! str_starts_with($route->uri(), 'record7')) {
                $strays[] = $name.' => /'.$route->uri();
            }
        }

        $this->assertSame([], $strays, implode(', ', $strays));
    }

    public function test_record7_pages_render_through_the_record7_root_view(): void
    {
        $html = $this->get('/record7/login')->assertOk()->getContent();

        // The markup differs by build mode — Vite source path or hashed asset —
        // so accept either rather than depending on whether a dev server is up.
        $this->assertTrue(
            str_contains($html, 'resources/js/r7.jsx')
            || (bool) preg_match('#/build/assets/r7-[^"]+\.js#', $html),
            'The Record7 bundle was not served.'
        );

        foreach (['f4.jsx', 'f3.jsx', 'app.jsx', '/assets/f4-', '/assets/f3-', '/assets/app-'] as $foreign) {
            $this->assertStringNotContainsString($foreign, $html);
        }
    }

    /* ── Helpers ─────────────────────────────────────────────────────────── */

    private function record7SourceFiles(): array
    {
        return $this->filesUnder([
            resource_path('js/record7'),
            resource_path('js/R7Pages'),
            resource_path('css/record7'),
        ], [resource_path('js/r7.jsx')], ['jsx', 'js', 'css']);
    }

    private function record7PhpFiles(): array
    {
        return $this->filesUnder([
            app_path('Models/Record7'),
            app_path('Services/Record7'),
            app_path('Http/Controllers/Record7'),
            app_path('Http/Middleware/Record7'),
        ], [], ['php']);
    }

    private function filesUnder(array $roots, array $seed, array $extensions): array
    {
        $files = $seed;

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $entry) {
                if ($entry->isFile() && in_array(strtolower($entry->getExtension()), $extensions, true)) {
                    $files[] = $entry->getPathname();
                }
            }
        }

        return $files;
    }

    /** Comments name the other front ends deliberately; strip them first. */
    private function withoutComments(string $source): string
    {
        $source = preg_replace('#/\*.*?\*/#s', '', $source);

        return preg_replace('#^\s*//.*$#m', '', $source);
    }
}
