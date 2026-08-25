<?php

namespace Tests\Feature\Record7;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The component showcase exists for developers and must never exist in
 * production.
 */
class Record7ShowcaseTest extends TestCase
{
    public function test_the_showcase_is_reachable_in_development(): void
    {
        $this->assertTrue(Route::has('record7.showcase'));

        $this->get('/record7/dev/components')
            ->assertOk()
            ->assertSee('Showcase', false);
    }

    public function test_the_showcase_needs_no_sign_in(): void
    {
        // It carries no real data, so requiring a login would only make it
        // harder to use while protecting nothing.
        $this->get('/record7/dev/components')->assertOk();
    }

    public function test_the_showcase_route_is_not_registered_in_production(): void
    {
        // The route file guards registration on the environment, so the URL
        // does not exist in production rather than merely refusing.
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertMatchesRegularExpression(
            "/if\s*\(\s*!\s*app\(\)->environment\('production'\)\s*\)\s*\{[^}]*record7\/dev\/components/s",
            $routes,
            'The showcase route must be registered only outside production.'
        );
    }

    public function test_the_controller_refuses_in_production_as_well(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Record7/ShowcaseController.php'));

        $this->assertStringContainsString(
            "abort_if(app()->environment('production'), 404)",
            $controller,
            'A cached route table can outlive an environment change, so the '
            .'controller must refuse too.'
        );
    }

    public function test_the_showcase_covers_every_component(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/Showcase.jsx'));

        foreach ([
            'Mark', 'ThemeToggle', 'PageHeading', 'SectionHeading', 'Button', 'TextLink',
            'Field', 'PasswordField', 'CodeInput', 'Notice', 'StatusLabel', 'SafetyWarning',
            'ConfirmPanel', 'StateBlock', 'HouseRow', 'PersonIdentity', 'MedicineIdentity',
            'AppNav',
        ] as $component) {
            $this->assertStringContainsString(
                "components/{$component}.jsx",
                $page,
                $component.' is missing from the showcase'
            );
        }
    }

    public function test_the_showcase_covers_every_state_of_the_shared_components(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/Showcase.jsx'));

        foreach ([
            'variant="primary"', 'variant="secondary"', 'variant="quiet"',
            'variant="warning"', 'variant="dangerous"', 'busy', 'disabled',
            'tone="success"', 'tone="warning"', 'tone="error"', 'tone="info"',
            'state="loading"', 'state="empty"', 'state="offline"',
            'state="restricted"', 'state="error"',
        ] as $state) {
            $this->assertStringContainsString($state, $page, $state.' is missing from the showcase');
        }
    }

    public function test_the_showcase_uses_only_invented_data(): void
    {
        $page = file_get_contents(resource_path('js/R7Pages/Showcase.jsx'));

        $this->assertStringContainsString('Fictional', $page);
        // No real credential from the fixture may appear here.
        $this->assertStringNotContainsString('Record7-Test-', $page);
    }
}
