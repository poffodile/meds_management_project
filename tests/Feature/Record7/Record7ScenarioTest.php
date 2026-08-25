<?php

namespace Tests\Feature\Record7;

use App\Models\Record7\AccessAuditEvent;
use App\Services\Record7\AccessPolicy;
use Illuminate\Support\Facades\Auth;

/**
 * The named scenarios from the supplied handoff, one test each.
 *
 * These are written in the handoff's own words so a reviewer can read the
 * document and this file side by side.
 */
class Record7ScenarioTest extends Record7TestCase
{
    private function policy(): AccessPolicy
    {
        return app(AccessPolicy::class);
    }

    /** "Olivia Carter sees Oakwood House and Rosewood House." */
    public function test_olivia_carter_sees_oakwood_house_and_rosewood_house(): void
    {
        $this->signIn('olivia.carter');

        $houses = $this->policy()->availableServices($this->user('olivia.carter'));
        $names = array_map(fn ($house) => $house->name, $houses);

        $this->assertSame(['Oakwood House', 'Rosewood House'], $names);

        $this->get('/record7/houses')
            ->assertOk()
            ->assertSee('Oakwood House', false)
            ->assertSee('Rosewood House', false);
    }

    /** "Daniel Evans sees the two houses he manages and can switch between them." */
    public function test_daniel_evans_manages_two_houses_and_can_switch(): void
    {
        $this->signIn('daniel.evans');

        $names = array_map(
            fn ($house) => $house->name,
            $this->policy()->availableServices($this->user('daniel.evans'))
        );
        $this->assertSame(['Oakwood House', 'Rosewood House'], $names);

        $this->post('/record7/houses', ['house_id' => $this->house('Oakwood House')->id]);
        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);

        $this->post('/record7/houses', ['house_id' => $this->house('Rosewood House')->id])
            ->assertRedirect('/record7');
        $this->get('/record7')->assertOk()->assertSee('Rosewood House', false);
    }

    /** "Priya Nair has organisation-administration access across all active houses." */
    public function test_priya_nair_has_organisation_administration_across_all_active_houses(): void
    {
        $this->signIn('priya.nair');

        $names = array_map(
            fn ($house) => $house->name,
            $this->policy()->availableServices($this->user('priya.nair'))
        );

        // All three ACTIVE houses. Willow House is inactive and must not appear.
        $this->assertSame(['Meadow View', 'Oakwood House', 'Rosewood House'], $names);
        $this->assertNotContains('Willow House', $names);

        $this->assertTrue($this->policy()->allows(
            $this->user('priya.nair'), 'manage_organisation', $this->house('Oakwood House')->id
        ));
    }

    /** "Noah Williams has one house and should enter Oakwood House automatically." */
    public function test_noah_williams_enters_oakwood_house_automatically(): void
    {
        $this->signIn('noah.williams');

        $this->get('/record7/houses')->assertRedirect('/record7');
        $this->get('/record7')->assertOk()->assertSee('Oakwood House', false);

        $event = AccessAuditEvent::where('event_type', 'house_selected')->latest('id')->firstOrFail();
        $this->assertTrue($event->metadata['automatic']);
    }

    /** Noah is the medication administrator with witness and controlled-drug access. */
    public function test_noah_williams_holds_witness_and_controlled_drug_access(): void
    {
        $noah = $this->user('noah.williams');
        $oakwood = $this->house('Oakwood House')->id;

        $this->assertTrue($this->policy()->allows($noah, 'administer_medication', $oakwood));
        $this->assertTrue($this->policy()->allows($noah, 'witness_medication', $oakwood));
        $this->assertTrue($this->policy()->allows($noah, 'manage_controlled_drugs', $oakwood));
    }

    /**
     * "Grace Taylor can enter Rosewood House but medication administration must
     * be denied because competency is not assessed and an explicit deny is
     * present."
     */
    public function test_grace_taylor_enters_rosewood_but_is_denied_medication_administration(): void
    {
        $this->signIn('grace.taylor');

        // She gets in — one house, so it opens automatically.
        $this->get('/record7/houses')->assertRedirect('/record7');
        $this->get('/record7')->assertOk()->assertSee('Rosewood House', false);

        $decision = $this->policy()->decide(
            $this->user('grace.taylor'), 'administer_medication', $this->house('Rosewood House')->id
        );

        $this->assertFalse($decision->allowed);
        $this->assertSame('explicit_deny', $decision->code);

        // Both reasons are genuinely present, and either alone would refuse.
        $this->assertTrue(
            $this->user('grace.taylor')->permissionRules()->where('effect', 'deny')->exists(),
            'The explicit deny must exist.'
        );
        $this->assertTrue(
            $this->user('grace.taylor')->competencies()->where('status', 'not_assessed')->exists(),
            'The unassessed competency must exist.'
        );
    }

    /** "Maya Thompson receives read-only audit access within Oakwood and Rosewood." */
    public function test_maya_thompson_has_read_only_audit_access_at_both_houses(): void
    {
        $maya = $this->user('maya.thompson');

        $names = array_map(fn ($house) => $house->name, $this->policy()->availableServices($maya));
        $this->assertSame(['Oakwood House', 'Rosewood House'], $names);

        foreach (['Oakwood House', 'Rosewood House'] as $house) {
            $id = $this->house($house)->id;
            $this->assertTrue($this->policy()->allows($maya, 'view_access_audit', $id), $house);
            $this->assertFalse($this->policy()->allows($maya, 'administer_medication', $id), $house);
            $this->assertFalse($this->policy()->allows($maya, 'manage_staff', $id), $house);
        }

        $this->signInAt('maya.thompson', 'Oakwood House');
        $this->get('/record7/access-audit')->assertOk();
    }

    /**
     * "Ethan Cole must be denied before any house information is revealed
     * because the account is suspended."
     */
    public function test_ethan_cole_is_denied_before_any_house_is_revealed(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        $this->post('/record7/login/credentials', [
            'username' => 'ethan.cole',
            'password' => self::PASSWORDS['ethan.cole'],
        ])->assertRedirect('/record7/login');

        $this->assertFalse(Auth::guard('record7')->check());

        $screen = $this->get('/record7/login')->assertOk();

        // His credentials are correct, so telling him they are wrong would be a
        // lie that sends him round a password reset which cannot help.
        $screen->assertSee('&quot;step&quot;:&quot;unavailable&quot;', false);
        $screen->assertDontSee('We could not sign you in with those details.', false);

        $content = $screen->getContent();

        // And it reveals nothing about him.
        foreach (['Oakwood', 'Rosewood', 'Meadow', 'Support Worker', 'suspended', 'Ethan'] as $secret) {
            $this->assertStringNotContainsString($secret, $content,
                'The unavailable screen must not reveal "'.$secret.'"');
        }

        // The precise reason is kept privately, in the audit, at high risk.
        $event = AccessAuditEvent::where('event_type', 'sign_in')
            ->where('event_result', 'denied')
            ->where('user_id', $this->user('ethan.cole')->id)
            ->latest('id')->firstOrFail();

        $this->assertSame('high', $event->risk_level);
        $this->assertSame('suspended', $event->metadata['refusal']);
        $this->assertTrue($event->metadata['credentials_were_correct']);
    }

    /**
     * The unavailable screen must NOT be reachable with a wrong password.
     *
     * Otherwise anyone could type a username with junk and learn from the
     * different answer that the account exists.
     */
    public function test_a_wrong_password_on_a_suspended_account_gives_the_ordinary_refusal(): void
    {
        $this->post('/record7/login/organisation', ['organisation' => self::ORGANISATION]);

        $this->from('/record7/login')->post('/record7/login/credentials', [
            'username' => 'ethan.cole',
            'password' => 'not-his-password',
        ])->assertSessionHas('error', 'We could not sign you in with those details.');

        $this->get('/record7/login')->assertDontSee('&quot;step&quot;:&quot;unavailable&quot;', false);
    }

    /** "Willow House is inactive and must not be selectable." */
    public function test_willow_house_is_never_selectable_by_anyone(): void
    {
        foreach (['olivia.carter', 'daniel.evans', 'priya.nair', 'sarah.ahmed'] as $username) {
            $names = array_map(
                fn ($house) => $house->name,
                $this->policy()->availableServices($this->user($username))
            );

            $this->assertNotContains('Willow House', $names, $username.' must not see Willow House');
        }
    }

    /** "Sarah Ahmed is the medication lead with oversight of all active houses." */
    public function test_sarah_ahmed_leads_medication_across_the_active_houses(): void
    {
        $sarah = $this->user('sarah.ahmed');

        $names = array_map(fn ($house) => $house->name, $this->policy()->availableServices($sarah));
        $this->assertSame(['Meadow View', 'Oakwood House', 'Rosewood House'], $names);

        $this->assertTrue($this->policy()->allows(
            $sarah, 'view_access_audit', $this->house('Oakwood House')->id
        ));
    }

    /** Every supplied account signs in, or is correctly refused. */
    public function test_every_supplied_account_behaves_as_documented(): void
    {
        // Eight sign-ins in one minute is exactly what the rate limiter exists
        // to stop, and it is tested on its own elsewhere. Here the question is
        // whether each account behaves as documented, so the limiter is stood
        // down rather than allowed to mask the answer.
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $expected = [
            'olivia.carter' => true,
            'daniel.evans' => true,
            'priya.nair' => true,
            'sarah.ahmed' => true,
            'noah.williams' => true,
            'maya.thompson' => true,
            'grace.taylor' => true,
            'ethan.cole' => false,
        ];

        foreach ($expected as $username => $shouldSignIn) {
            $this->post('/record7/sign-out');
            $this->signIn($username);

            $this->assertSame(
                $shouldSignIn,
                Auth::guard('record7')->check(),
                $username.($shouldSignIn ? ' should sign in' : ' must be refused')
            );
        }
    }
}
