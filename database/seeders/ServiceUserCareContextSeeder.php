<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fills the care-context fields (room, NHS, diet, mobility) for residents that
 * don't have them yet, with stable per-resident placeholder values. These are
 * dummy stand-ins until the real values flow in from the care plan / service-user
 * record — only blank fields are filled, so real data is never overwritten.
 */
class ServiceUserCareContextSeeder extends Seeder
{
    public function run(): void
    {
        $mobility = ['Independent', 'Uses walking frame', 'Wheelchair user', 'Needs assistance'];
        $diet     = ['Normal diet', 'Soft diet', 'Diabetic diet', 'Pureed diet'];

        $rows = DB::table('service_user')->select('id', 'room_number', 'nhs_number', 'diet', 'suMobility')->get();

        foreach ($rows as $u) {
            $k   = (int) $u->id;
            $set = [];

            if (trim((string) $u->room_number) === '') {
                $set['room_number'] = (string) (($k % 40) + 1);
            }
            if (trim((string) $u->nhs_number) === '') {
                $nhs = str_pad((string) (9000000000 + (($k * 6113) % 1000000000)), 10, '0', STR_PAD_LEFT);
                $set['nhs_number'] = substr($nhs, 0, 3) . ' ' . substr($nhs, 3, 3) . ' ' . substr($nhs, 6);
            }
            if (trim((string) $u->diet) === '') {
                $set['diet'] = $diet[$k % count($diet)];
            }
            if (trim((string) $u->suMobility) === '') {
                $set['suMobility'] = $mobility[$k % count($mobility)];
            }

            if (!empty($set)) {
                DB::table('service_user')->where('id', $k)->update($set);
            }
        }
    }
}
