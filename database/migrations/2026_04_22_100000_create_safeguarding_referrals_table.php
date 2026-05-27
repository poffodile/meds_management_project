<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safeguarding_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('home_id');
            $table->unsignedInteger('client_id')->nullable();
            $table->string('reference_number', 50)->nullable();

            // Concern details
            $table->unsignedInteger('reported_by');
            $table->dateTime('date_of_concern');
            $table->string('location_of_incident', 500)->nullable();
            $table->text('details_of_concern');
            $table->text('immediate_action_taken')->nullable();

            // Classification
            $table->json('safeguarding_type');
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['reported', 'under_investigation', 'safeguarding_plan', 'closed'])->default('reported');
            $table->boolean('ongoing_risk')->default(false);

            // People involved
            $table->json('alleged_perpetrator')->nullable();
            $table->json('witnesses')->nullable();
            $table->boolean('capacity_to_make_decisions')->nullable();
            $table->text('client_wishes')->nullable();

            // Multi-agency notifications
            $table->boolean('police_notified')->default(false);
            $table->string('police_reference', 100)->nullable();
            $table->dateTime('police_notification_date')->nullable();
            $table->boolean('local_authority_notified')->default(false);
            $table->string('local_authority_reference', 100)->nullable();
            $table->dateTime('local_authority_notification_date')->nullable();
            $table->boolean('cqc_notified')->default(false);
            $table->dateTime('cqc_notification_date')->nullable();
            $table->boolean('family_notified')->default(false);
            $table->text('family_notification_details')->nullable();
            $table->boolean('advocate_involved')->default(false);
            $table->text('advocate_details')->nullable();

            // Strategy meeting
            $table->json('strategy_meeting')->nullable();

            // Plan & outcome
            $table->json('safeguarding_plan')->nullable();
            $table->string('outcome', 50)->nullable();
            $table->text('outcome_details')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->dateTime('closed_date')->nullable();

            // Audit
            $table->unsignedInteger('created_by');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('home_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('risk_level');
            $table->index('is_deleted');
        });

        // Seed safeguarding_types for home 8 (Aries)
        $existingCount = DB::table('safeguarding_types')->where('home_id', 8)->count();
        if ($existingCount === 0) {
            $types = [
                'Physical Abuse', 'Emotional/Psychological Abuse', 'Neglect',
                'Domestic Abuse', 'Self-Neglect', 'Sexual Abuse',
                'Financial Abuse', 'Discriminatory Abuse', 'Modern Slavery',
                'Organisational Abuse',
            ];
            $now = now();
            foreach ($types as $type) {
                DB::table('safeguarding_types')->insert([
                    'home_id' => 8,
                    'type' => $type,
                    'status' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Seed sample referrals for home 8
        $now = now();
        DB::table('safeguarding_referrals')->insert([
            [
                'home_id' => 8,
                'client_id' => 27,
                'reference_number' => 'SAFE-2026-04-0001',
                'reported_by' => 194,
                'date_of_concern' => '2026-04-18 09:30:00',
                'location_of_incident' => 'Resident lounge area',
                'details_of_concern' => 'Staff member observed bruising on resident\'s upper arm during morning care. Resident became distressed when asked about it and said another resident had grabbed them during an altercation over the TV remote yesterday afternoon.',
                'immediate_action_taken' => 'Injury photographed and documented. Resident reassured and separated from the other resident. Both residents monitored closely.',
                'safeguarding_type' => json_encode(['Physical Abuse']),
                'risk_level' => 'medium',
                'status' => 'under_investigation',
                'ongoing_risk' => true,
                'alleged_perpetrator' => json_encode(['name' => 'Another resident (name withheld)', 'relationship' => 'Fellow resident', 'details' => 'Resident in adjacent room, history of agitation in communal areas']),
                'witnesses' => json_encode([['name' => 'Sarah Jenkins', 'role' => 'Care Assistant', 'statement' => 'I saw the two residents arguing over the remote at approximately 3pm. I intervened and separated them but did not see physical contact.']]),
                'capacity_to_make_decisions' => true,
                'client_wishes' => 'Resident wants to stay but does not want to be near the other resident',
                'police_notified' => false,
                'police_reference' => null,
                'police_notification_date' => null,
                'local_authority_notified' => true,
                'local_authority_reference' => 'SA-2026-0412',
                'local_authority_notification_date' => '2026-04-18 14:00:00',
                'cqc_notified' => false,
                'cqc_notification_date' => null,
                'family_notified' => true,
                'family_notification_details' => 'Daughter contacted by phone, informed of incident and actions taken',
                'advocate_involved' => false,
                'advocate_details' => null,
                'strategy_meeting' => null,
                'safeguarding_plan' => null,
                'outcome' => null,
                'outcome_details' => null,
                'lessons_learned' => null,
                'closed_date' => null,
                'created_by' => 194,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'home_id' => 8,
                'client_id' => 27,
                'reference_number' => 'SAFE-2026-04-0002',
                'reported_by' => 194,
                'date_of_concern' => '2026-04-10 11:00:00',
                'location_of_incident' => 'Care home - resident room',
                'details_of_concern' => 'Family member reported that resident\'s personal money (approximately £200) has gone missing from their bedside drawer over the past two weeks. Resident has dementia and cannot account for the money.',
                'immediate_action_taken' => 'Room secured. All staff informed. Manager notified. Visitor log reviewed for the past 2 weeks.',
                'safeguarding_type' => json_encode(['Financial Abuse']),
                'risk_level' => 'high',
                'status' => 'safeguarding_plan',
                'ongoing_risk' => false,
                'alleged_perpetrator' => json_encode(['name' => 'Unknown', 'relationship' => 'Unknown', 'details' => 'Could be staff, visitor, or another resident. Investigation ongoing.']),
                'witnesses' => json_encode([['name' => 'Margaret Williams', 'role' => 'Daughter (NOK)', 'statement' => 'Noticed money missing when checking mum\'s drawer during visit. Usually keeps about £300 for hairdresser and personal items.']]),
                'capacity_to_make_decisions' => false,
                'client_wishes' => null,
                'police_notified' => true,
                'police_reference' => 'LOG-2026-04-10-8821',
                'police_notification_date' => '2026-04-10 15:00:00',
                'local_authority_notified' => true,
                'local_authority_reference' => 'SA-2026-0398',
                'local_authority_notification_date' => '2026-04-10 14:30:00',
                'cqc_notified' => true,
                'cqc_notification_date' => '2026-04-11 09:00:00',
                'family_notified' => true,
                'family_notification_details' => 'Daughter fully involved throughout. Updated daily on investigation progress.',
                'advocate_involved' => false,
                'advocate_details' => null,
                'strategy_meeting' => json_encode(['required' => true, 'date' => '2026-04-12T14:00:00', 'outcome' => 'Police to investigate. Care home to implement lockable storage for all residents. CCTV review of corridor outside room.']),
                'safeguarding_plan' => json_encode(['agreed_actions' => ['Lockable storage provided for all residents', 'CCTV reviewed for past 2 weeks', 'Staff reminded of handling residents\' property policy', 'Police investigation ongoing'], 'responsible_persons' => ['Care Home Manager', 'Police', 'Safeguarding Team'], 'timescales' => 'Review in 1 week', 'monitoring_arrangements' => 'Daily checks on resident welfare. Weekly safeguarding review.']),
                'outcome' => null,
                'outcome_details' => null,
                'lessons_learned' => null,
                'closed_date' => null,
                'created_by' => 194,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'home_id' => 8,
                'client_id' => null,
                'reference_number' => 'SAFE-2026-03-0001',
                'reported_by' => 194,
                'date_of_concern' => '2026-03-15 14:00:00',
                'location_of_incident' => 'Dining room',
                'details_of_concern' => 'Resident reported feeling belittled and shouted at by a member of staff during lunchtime. Resident was upset and refused to eat the rest of their meal.',
                'immediate_action_taken' => 'Resident comforted. Staff member removed from floor and spoken to by manager. Witness statements taken from other staff present.',
                'safeguarding_type' => json_encode(['Emotional/Psychological Abuse']),
                'risk_level' => 'low',
                'status' => 'closed',
                'ongoing_risk' => false,
                'alleged_perpetrator' => json_encode(['name' => 'Staff member (name in confidential file)', 'relationship' => 'Care staff', 'details' => 'Agency staff on second shift at the home']),
                'witnesses' => json_encode([['name' => 'Tom Brown', 'role' => 'Senior Carer', 'statement' => 'I heard raised voice from the dining area. When I entered, the staff member was standing over the resident who looked upset.']]),
                'capacity_to_make_decisions' => true,
                'client_wishes' => 'Does not want the staff member to provide their care again',
                'police_notified' => false,
                'police_reference' => null,
                'police_notification_date' => null,
                'local_authority_notified' => false,
                'local_authority_reference' => null,
                'local_authority_notification_date' => null,
                'cqc_notified' => false,
                'cqc_notification_date' => null,
                'family_notified' => true,
                'family_notification_details' => 'Son informed by phone',
                'advocate_involved' => false,
                'advocate_details' => null,
                'strategy_meeting' => null,
                'safeguarding_plan' => null,
                'outcome' => 'substantiated',
                'outcome_details' => 'Investigation confirmed the staff member raised their voice inappropriately. Agency informed and staff member will not return to this home. All staff reminded of dignity and respect standards.',
                'lessons_learned' => 'Agency staff inductions need to include explicit expectations around communication with residents. Supervision of agency staff during initial shifts.',
                'closed_date' => '2026-03-22 16:00:00',
                'created_by' => 194,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'home_id' => 8,
                'client_id' => 27,
                'reference_number' => 'SAFE-2026-04-0003',
                'reported_by' => 194,
                'date_of_concern' => '2026-04-20 08:00:00',
                'location_of_incident' => 'Resident bedroom',
                'details_of_concern' => 'During morning care, resident found with soiled clothing and bedding that appeared unchanged overnight. Night staff unable to explain. Resident has pressure sore risk and this constitutes a neglect concern.',
                'immediate_action_taken' => 'Resident washed, changed and made comfortable immediately. Skin integrity check completed — no new pressure damage. Night staff statements taken. Incident logged.',
                'safeguarding_type' => json_encode(['Neglect']),
                'risk_level' => 'critical',
                'status' => 'reported',
                'ongoing_risk' => true,
                'alleged_perpetrator' => null,
                'witnesses' => null,
                'capacity_to_make_decisions' => false,
                'client_wishes' => null,
                'police_notified' => false,
                'police_reference' => null,
                'police_notification_date' => null,
                'local_authority_notified' => false,
                'local_authority_reference' => null,
                'local_authority_notification_date' => null,
                'cqc_notified' => false,
                'cqc_notification_date' => null,
                'family_notified' => false,
                'family_notification_details' => null,
                'advocate_involved' => false,
                'advocate_details' => null,
                'strategy_meeting' => null,
                'safeguarding_plan' => null,
                'outcome' => null,
                'outcome_details' => null,
                'lessons_learned' => null,
                'closed_date' => null,
                'created_by' => 194,
                'is_deleted' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('safeguarding_referrals');
        DB::table('safeguarding_types')->where('home_id', 8)->delete();
    }
};
