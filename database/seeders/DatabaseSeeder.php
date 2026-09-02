<?php

namespace Database\Seeders;

use App\Models\ApprovalLink;
use App\Models\Patient;
use App\Models\SymptomReport;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic clinical demo data.
     */
    public function run(): void
    {
        // 1. Patient: Maya Chen (Stage 3, Step 3 - Improving)
        $maya = Patient::create([
            'name' => 'Maya Chen',
            'injury_type' => 'Header collision during regional soccer match',
            'injury_date' => now()->subDays(10)->toDateString(),
            'current_stage' => 3,
            'activity_step' => 3,
            'parent_email' => 'maya.parents@example.com',
        ]);

        // Maya's 7-day symptom trajectory (Improving)
        $mayaReports = [
            ['day' => 7, 'text' => 'Moderate headache in morning, slight nausea after riding in car.', 'severity' => 'moderate', 'red_flag' => false, 'symptoms' => ['headache', 'nausea']],
            ['day' => 6, 'text' => 'Headache reduced to mild, took afternoon rest. No nausea.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['mild headache']],
            ['day' => 5, 'text' => 'Felt good at school half-day. No dizziness or light sensitivity.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['mild fatigue']],
            ['day' => 4, 'text' => 'Stationary bike for 15 mins. Heart rate elevated with no symptom recurrence.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 3, 'text' => 'Light jogging for 20 mins. Felt energized, zero headache.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 2, 'text' => 'Movement drills and passing soccer ball with coach. Tolerated well.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 1, 'text' => 'Completed moderate running drills. No dizziness, vision clear and sharp.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
        ];

        foreach ($mayaReports as $rep) {
            SymptomReport::create([
                'patient_id' => $maya->id,
                'report_text' => $rep['text'],
                'ai_severity' => $rep['severity'],
                'ai_red_flag' => $rep['red_flag'],
                'ai_safety_override' => false,
                'extracted_symptoms' => $rep['symptoms'],
                'reported_at' => now()->subDays($rep['day']),
            ]);
        }

        // Maya's Approval Links for Milestone 4 (Final Clearance)
        ApprovalLink::create([
            'patient_id' => $maya->id,
            'for_stage' => 4,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Elizabeth Vance, MD (Sports Neurologist)',
            'token' => 'maya-doctor-stage4-token-demo-9921',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        ApprovalLink::create([
            'patient_id' => $maya->id,
            'for_stage' => 4,
            'approver_role' => 'school',
            'approver_name' => 'Principal Robert Sterling (High School)',
            'token' => 'maya-school-stage4-token-demo-9922',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        ApprovalLink::create([
            'patient_id' => $maya->id,
            'for_stage' => 4,
            'approver_role' => 'parent',
            'approver_name' => 'David & Linda Chen (Parents)',
            'token' => 'maya-parent-stage4-token-demo-9923',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        // 2. Patient: Alex Rivera (Stage 1 - Initial Assessment)
        $alex = Patient::create([
            'name' => 'Alex Rivera',
            'injury_type' => 'Floor impact during basketball scrimmage',
            'injury_date' => now()->subDays(3)->toDateString(),
            'current_stage' => 1,
            'activity_step' => null,
            'parent_email' => 'alex.rivera@example.com',
        ]);

        $alexReports = [
            ['day' => 3, 'text' => 'Dizzy, light hurts eyes, constant throbbing headache.', 'severity' => 'moderate', 'red_flag' => false, 'symptoms' => ['dizziness', 'photophobia', 'throbbing headache']],
            ['day' => 2, 'text' => 'Slept 10 hours. Headache slightly better in dark room.', 'severity' => 'moderate', 'red_flag' => false, 'symptoms' => ['headache', 'drowsiness']],
            ['day' => 1, 'text' => 'Listened to audiobooks. Still slight pressure in temples.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['head pressure']],
        ];

        foreach ($alexReports as $rep) {
            SymptomReport::create([
                'patient_id' => $alex->id,
                'report_text' => $rep['text'],
                'ai_severity' => $rep['severity'],
                'ai_red_flag' => $rep['red_flag'],
                'ai_safety_override' => false,
                'extracted_symptoms' => $rep['symptoms'],
                'reported_at' => now()->subDays($rep['day']),
            ]);
        }

        // Alex's Doctor Approval Link for Milestone 2 (School Recovery)
        ApprovalLink::create([
            'patient_id' => $alex->id,
            'for_stage' => 2,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Marcus Thorne, MD (Pediatrician)',
            'token' => 'alex-doctor-stage2-token-demo-1101',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        // 3. Patient: Jordan Taylor (Stage 2 - School Recovery)
        $jordan = Patient::create([
            'name' => 'Jordan Taylor',
            'injury_type' => 'Fall during track and field hurdle training',
            'injury_date' => now()->subDays(8)->toDateString(),
            'current_stage' => 2,
            'activity_step' => null,
            'parent_email' => 'jordan.taylor@example.com',
        ]);

        $jordanReports = [
            ['day' => 5, 'text' => 'Attended morning classes. Needed 20 min quiet break after 2nd period.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['cognitive fatigue']],
            ['day' => 4, 'text' => 'No headache in class today. Finished reading without screen glare discomfort.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 3, 'text' => 'Full academic day completed with test accommodation.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 2, 'text' => 'Zero headache all day. Felt ready for light physical activity.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
            ['day' => 1, 'text' => 'No symptoms at all today during school.', 'severity' => 'mild', 'red_flag' => false, 'symptoms' => ['none']],
        ];

        foreach ($jordanReports as $rep) {
            SymptomReport::create([
                'patient_id' => $jordan->id,
                'report_text' => $rep['text'],
                'ai_severity' => $rep['severity'],
                'ai_red_flag' => $rep['red_flag'],
                'ai_safety_override' => false,
                'extracted_symptoms' => $rep['symptoms'],
                'reported_at' => now()->subDays($rep['day']),
            ]);
        }

        // Jordan's Approval Links for Milestone 3 (Graduated Physical Activity)
        ApprovalLink::create([
            'patient_id' => $jordan->id,
            'for_stage' => 3,
            'approver_role' => 'doctor',
            'approver_name' => 'Dr. Marcus Thorne, MD (Pediatrician)',
            'token' => 'jordan-doctor-stage3-token-demo-2201',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);

        ApprovalLink::create([
            'patient_id' => $jordan->id,
            'for_stage' => 3,
            'approver_role' => 'school',
            'approver_name' => 'Nurse Sarah Jenkins, RN (School Health Coordinator)',
            'token' => 'jordan-school-stage3-token-demo-2202',
            'expires_at' => now()->addDays(7),
            'is_used' => false,
        ]);
    }
}
