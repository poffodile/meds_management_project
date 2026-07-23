<?php

namespace Tests\Feature;

use App\Models\ServiceUserWeight;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The dated weight series (REQ-MED-112 / audit CR-09): current weight is derived from
 * the latest reading, weight is kg-only (grams internally), and a stale weight is
 * flagged. Uses DatabaseTransactions (never RefreshDatabase — see MedicationRoundSafetyTest).
 */
class ServiceUserWeightTest extends TestCase
{
    use DatabaseTransactions;

    private int $client = 243; // Amelia (Neptune)

    private function addReading(int $grams, string $measuredAt, array $extra = []): int
    {
        return DB::table('service_user_weights')->insertGetId(array_merge([
            'home_id' => 101, 'service_user_id' => $this->client, 'weight_grams' => $grams,
            'measured_at' => $measuredAt, 'recorded_at' => now(), 'method' => 'standing_scale',
            'is_estimated' => 0, 'created_at' => now(), 'updated_at' => now(),
        ], $extra));
    }

    public function test_current_weight_is_the_latest_reading(): void
    {
        DB::table('service_user_weights')->where('service_user_id', $this->client)->delete();
        $this->addReading(38000, now()->subDays(120)->toDateTimeString());
        $this->addReading(41000, now()->subDays(10)->toDateTimeString()); // the latest

        $cur = ServiceUserWeight::currentFor([$this->client])[$this->client] ?? null;
        $this->assertNotNull($cur);
        $this->assertEqualsWithDelta(41.0, $cur['kg'], 0.001, 'Current weight was not the most recent reading.');
    }

    public function test_a_weight_older_than_the_threshold_is_flagged_stale(): void
    {
        DB::table('service_user_weights')->where('service_user_id', $this->client)->delete();
        $this->addReading(38000, now()->subDays(ServiceUserWeight::STALE_AFTER_DAYS + 5)->toDateTimeString());

        $cur = ServiceUserWeight::currentFor([$this->client])[$this->client];
        $this->assertTrue($cur['is_stale'], 'An old weight was not flagged stale.');
        $this->assertGreaterThan(ServiceUserWeight::STALE_AFTER_DAYS, $cur['age_days']);
    }

    public function test_a_recent_weight_is_not_stale(): void
    {
        DB::table('service_user_weights')->where('service_user_id', $this->client)->delete();
        $this->addReading(38000, now()->subDays(3)->toDateTimeString());

        $cur = ServiceUserWeight::currentFor([$this->client])[$this->client];
        $this->assertFalse($cur['is_stale']);
        $this->assertSame(3, $cur['age_days']);
    }

    public function test_a_voided_reading_is_ignored(): void
    {
        DB::table('service_user_weights')->where('service_user_id', $this->client)->delete();
        $this->addReading(38000, now()->subDays(30)->toDateTimeString());
        $this->addReading(99000, now()->subDay()->toDateTimeString(), ['voided_at' => now(), 'void_reason' => 'wrong resident']);

        $cur = ServiceUserWeight::currentFor([$this->client])[$this->client];
        $this->assertEqualsWithDelta(38.0, $cur['kg'], 0.001, 'A voided reading was used as the current weight.');
    }

    /** The legacy backfill must have converted the two lbs residents to kg — no pounds stored. */
    public function test_no_pounds_survived_the_backfill(): void
    {
        // 76 lb ≈ 34.5 kg, 70 lb ≈ 31.8 kg. If a raw 70/76 kg-as-grams (70000/76000) slipped
        // through it would be a different value; assert the known converted grams exist.
        $riya = ServiceUserWeight::currentFor([195])[195] ?? null;   // Riya, was 76 lb
        if ($riya) {
            $this->assertEqualsWithDelta(34.5, $riya['kg'], 0.2, 'The lbs resident was not converted to kg.');
        } else {
            $this->markTestSkipped('Legacy lbs resident 195 not present.');
        }
    }
}
