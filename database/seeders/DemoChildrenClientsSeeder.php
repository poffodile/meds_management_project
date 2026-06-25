<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Adds 5 demo children clients (with medications) to the active medication home,
 * so there's plenty to test the Medication Round with: regular doses across all
 * rounds, PRN meds, low-stock items, and controlled drugs (witness flow).
 *
 * Idempotent: clients are keyed by a demo user_name and skipped if already present,
 * and meds are only added when the client has none yet. Built for the children's
 * support-group context (child names + age-appropriate medications).
 */
class DemoChildrenClientsSeeder extends Seeder
{
    public function run(): void
    {
        // The home the owner actually works in (the medication round they browse).
        // Change this if you seed demo clients into a different home.
        $homeId = 101;
        $template = DB::table('service_user')->where('home_id', $homeId)->first()
            ?? DB::table('service_user')->first();
        if (!$template) {
            $this->command?->warn('No template service_user found; aborting.');
            return;
        }
        $tpl = (array) $template;
        $now = now();

        $children = [
            ['key' => 'demo_amelia_hughes', 'name' => 'Amelia Hughes',  'dob' => '2014-04-12', 'gender' => 'F', 'weight' => '38', 'allergies' => 'Penicillin', 'room' => 'C1', 'diet' => 'Normal diet',   'mobility' => 'Independent',         'desc' => 'Amelia enjoys art and joined the home in 2024.'],
            ['key' => 'demo_jacob_bennett', 'name' => 'Jacob Bennett',  'dob' => '2012-09-03', 'gender' => 'M', 'weight' => '45', 'allergies' => null,         'room' => 'C2', 'diet' => 'Normal diet',   'mobility' => 'Independent',         'desc' => 'Jacob is keen on football and music.'],
            ['key' => 'demo_sofia_martins', 'name' => 'Sofia Martins',  'dob' => '2015-11-20', 'gender' => 'F', 'weight' => '31', 'allergies' => 'Tree nuts',  'room' => 'C3', 'diet' => 'Soft diet',     'mobility' => 'Uses walking frame', 'desc' => 'Sofia likes reading and quiet activities.'],
            ['key' => 'demo_leo_walsh',     'name' => 'Leo Walsh',      'dob' => '2013-06-15', 'gender' => 'M', 'weight' => '41', 'allergies' => null,         'room' => 'C4', 'diet' => 'Diabetic diet', 'mobility' => 'Independent',         'desc' => 'Leo enjoys cycling and video games.'],
            ['key' => 'demo_maya_patel',    'name' => 'Maya Patel',     'dob' => '2016-02-28', 'gender' => 'F', 'weight' => '29', 'allergies' => 'Lactose',    'room' => 'C5', 'diet' => 'Normal diet',   'mobility' => 'Independent',         'desc' => 'Maya loves dancing and drawing.'],
        ];

        // med flags: prn=>true (as-required), cd=>true (controlled). low stock where stock<=reorder.
        $medSets = [
            'demo_amelia_hughes' => [
                ['name' => 'Paracetamol 250mg/5ml Oral Suspension', 'dosage' => '250mg/5ml', 'dose' => '10 ml', 'route' => 'Oral',    'slots' => ['08:00', '18:00'], 'stock' => 80, 'reorder' => 20, 'reason' => 'Pain relief'],
                ['name' => 'Salbutamol 100mcg Inhaler',             'dosage' => '100mcg',    'dose' => '2 puffs', 'route' => 'Inhaled', 'slots' => ['08:00', '20:00'], 'stock' => 4,  'reorder' => 5,  'reason' => 'Asthma'],
                ['name' => 'Ibuprofen 100mg/5ml Oral Suspension',   'dosage' => '100mg/5ml', 'dose' => '5 ml',  'route' => 'Oral',    'slots' => ['08:00'], 'prn' => true, 'prn_details' => 'For pain or fever, max 3 times daily', 'stock' => 60, 'reorder' => 15, 'reason' => 'Pain / fever'],
            ],
            'demo_jacob_bennett' => [
                ['name' => 'Methylphenidate 10mg Tablet', 'dosage' => '10mg', 'dose' => '1 tablet', 'route' => 'Oral', 'slots' => ['08:00', '12:00'], 'cd' => true, 'cd_schedule' => '2', 'stock' => 28, 'reorder' => 10, 'reason' => 'ADHD'],
                ['name' => 'Melatonin 2mg Tablet',        'dosage' => '2mg',  'dose' => '1 tablet', 'route' => 'Oral', 'slots' => ['21:00'], 'stock' => 20, 'reorder' => 10, 'reason' => 'Sleep support'],
                ['name' => 'Cetirizine 5mg Tablet',       'dosage' => '5mg',  'dose' => '1 tablet', 'route' => 'Oral', 'slots' => ['08:00'], 'stock' => 30, 'reorder' => 10, 'reason' => 'Hay fever'],
            ],
            'demo_sofia_martins' => [
                ['name' => 'Amoxicillin 250mg Capsule', 'dosage' => '250mg', 'dose' => '1 capsule', 'route' => 'Oral', 'slots' => ['08:00', '14:00', '20:00'], 'stock' => 21, 'reorder' => 7, 'reason' => 'Infection'],
                ['name' => 'Paracetamol 500mg Tablet',  'dosage' => '500mg', 'dose' => '1 tablet',  'route' => 'Oral', 'slots' => ['08:00'], 'prn' => true, 'prn_details' => 'For pain, max 4 times daily', 'stock' => 40, 'reorder' => 12, 'reason' => 'Pain relief'],
                ['name' => 'Omeprazole 10mg Capsule',   'dosage' => '10mg',  'dose' => '1 capsule', 'route' => 'Oral', 'slots' => ['08:00'], 'stock' => 28, 'reorder' => 10, 'reason' => 'Reflux'],
            ],
            'demo_leo_walsh' => [
                ['name' => 'Methylphenidate 10mg Tablet', 'dosage' => '10mg',  'dose' => '1 tablet', 'route' => 'Oral',    'slots' => ['08:00', '12:00'], 'cd' => true, 'cd_schedule' => '2', 'stock' => 6,  'reorder' => 10, 'reason' => 'ADHD'],
                ['name' => 'Salbutamol 100mcg Inhaler',   'dosage' => '100mcg', 'dose' => '2 puffs', 'route' => 'Inhaled', 'slots' => ['08:00', '20:00'], 'stock' => 8,  'reorder' => 5,  'reason' => 'Asthma'],
                ['name' => 'Melatonin 2mg Tablet',        'dosage' => '2mg',   'dose' => '1 tablet', 'route' => 'Oral',    'slots' => ['21:00'], 'stock' => 18, 'reorder' => 10, 'reason' => 'Sleep support'],
            ],
            'demo_maya_patel' => [
                ['name' => 'Cetirizine 5mg/5ml Oral Solution', 'dosage' => '5mg/5ml', 'dose' => '5 ml',   'route' => 'Oral', 'slots' => ['08:00'], 'stock' => 30, 'reorder' => 10, 'reason' => 'Allergies'],
                ['name' => 'Ibuprofen 100mg/5ml Oral Suspension', 'dosage' => '100mg/5ml', 'dose' => '5 ml', 'route' => 'Oral', 'slots' => ['08:00'], 'prn' => true, 'prn_details' => 'For pain or fever, max 3 times daily', 'stock' => 55, 'reorder' => 15, 'reason' => 'Pain / fever'],
                ['name' => 'Paracetamol 250mg/5ml Oral Suspension', 'dosage' => '250mg/5ml', 'dose' => '10 ml', 'route' => 'Oral', 'slots' => ['12:00', '18:00'], 'stock' => 50, 'reorder' => 15, 'reason' => 'Pain relief'],
            ],
        ];

        $created = 0;
        foreach ($children as $i => $c) {
            $existing = DB::table('service_user')->where('user_name', $c['key'])->where('home_id', $homeId)->first();
            if ($existing) {
                $clientId = (int) $existing->id;
            } else {
                $row = $tpl;
                unset($row['id']);
                $nhs = str_pad((string) (9500000000 + (($i * 7349) % 1000000000)), 10, '0', STR_PAD_LEFT);
                $row = array_merge($row, [
                    'name'            => $c['name'],
                    'user_name'       => $c['key'],
                    'email'           => $c['key'] . '@demo.local',
                    'phone_no'        => '',
                    'mobile'          => '',
                    'date_of_birth'   => $c['dob'],
                    'gender'          => $c['gender'],
                    'weight'          => $c['weight'],
                    'weight_unit'     => 'kg',
                    'height_unit'     => 'cm',
                    'allergies'       => $c['allergies'],
                    'room_number'     => $c['room'],
                    'nhs_number'      => substr($nhs, 0, 3) . ' ' . substr($nhs, 3, 3) . ' ' . substr($nhs, 6),
                    'diet'            => $c['diet'],
                    'suMobility'      => $c['mobility'],
                    'image'           => '',
                    'short_description' => $c['desc'],
                    'personal_info'   => $c['desc'],
                    'admission_number' => 'DEMO' . (1000 + $i),
                    'section'         => '',
                    'status'          => 1,
                    'is_deleted'      => 0,
                    'home_id'         => $homeId,
                    'security_code'   => Str::random(16),
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
                $clientId = (int) DB::table('service_user')->insertGetId($row);
                $created++;
            }

            $hasMeds = DB::table('mar_sheets')->where('client_id', $clientId)->where('is_deleted', 0)->where('mar_status', 'active')->exists();
            if ($hasMeds) {
                continue;
            }

            // Spread each child's meds across all four rounds (morning/lunch/evening/night)
            // so they're visible whichever round is open. Round map: 08:00=morning,
            // 12:00=lunch, 14:00=evening, 20:00=night.
            $spread = [['08:00', '20:00'], ['12:00'], ['14:00']];
            foreach (($medSets[$c['key']] ?? []) as $idx => $m) {
                $isPrn = !empty($m['prn']);
                $isCd  = !empty($m['cd']);
                DB::table('mar_sheets')->insert([
                    'home_id'               => $homeId,
                    'client_id'             => $clientId,
                    'medication_name'       => $m['name'],
                    'dosage'                => $m['dosage'],
                    'dose'                  => $m['dose'],
                    'route'                 => $m['route'],
                    'frequency'             => $isPrn ? 'as required' : 'scheduled',
                    'time_slots'            => json_encode($isPrn ? ($m['slots'] ?? ['08:00']) : $spread[$idx % count($spread)]),
                    'as_required'           => $isPrn ? 1 : 0,
                    'prn_details'           => $m['prn_details'] ?? null,
                    'reason_for_medication' => $m['reason'] ?? null,
                    'prescribed_by'         => 'Dr Smith',
                    'prescriber'            => 'GP',
                    'pharmacy'              => 'Boots Pharmacy',
                    'start_date'            => '2026-05-01',
                    'end_date'              => null,
                    'expiry_date'           => null,
                    'is_controlled'         => $isCd ? 1 : 0,
                    'cd_schedule'           => $m['cd_schedule'] ?? null,
                    'stock_level'           => $m['stock'] ?? null,
                    'reorder_level'         => $m['reorder'] ?? null,
                    'storage_requirements'  => $isCd ? 'CD cabinet' : 'Room temperature',
                    'allergies_warnings'    => null,
                    'mar_status'            => 'active',
                    'discontinued'          => 0,
                    'created_by'            => 15,
                    'is_deleted'            => 0,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }
        }

        $this->command?->info("Demo children clients seeded into home {$homeId} ({$created} new).");
    }
}
