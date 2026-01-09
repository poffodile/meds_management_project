<?php
namespace App\Services\Staff;

use App\Models\WorkingHour;
use App\Models\WorkingHourDate;
use App\Models\WorkingHourSchedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkingHoursService
{
    public function save($user, $request)
    {
        DB::transaction(function () use ($user, $request) {

            $schedule = WorkingHourSchedule::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'pattern' => $request->pattern,
                    'timezone' => 'Europe/London',
                    'start_date' => now()->startOfWeek()
                ]
            );

            // Clear old data
            WorkingHour::where('schedule_id', $schedule->id)->delete();
            WorkingHourDate::where('schedule_id', $schedule->id)->delete();

            match ($request->pattern) {
                'weekly' => $this->saveWeekly($schedule, $request->days),
                'alternate' => $this->saveAlternate($schedule, $request->weeks),
                'specific_dates' => $this->saveSpecificDates($schedule, $request->dates),
            };
        });
    }

    private function saveWeekly($schedule, $days)
    {
        foreach ($days as $day => $data) {
            WorkingHour::create([
                'schedule_id' => $schedule->id,
                'week_type' => 'all',
                'day_of_week' => $day,
                'is_working' => $data['enabled'],
                'start_time' => $data['enabled'] ? $data['start'] : null,
                'end_time' => $data['enabled'] ? $data['end'] : null
            ]);
        }
    }

    private function saveAlternate($schedule, $weeks)
    {
        foreach (['week1', 'week2'] as $weekType) {
            if (!isset($weeks[$weekType])) continue;

            foreach ($weeks[$weekType] as $day => $data) {
                WorkingHour::create([
                    'schedule_id' => $schedule->id,
                    'week_type' => $weekType,
                    'day_of_week' => $day,
                    'is_working' => $data['enabled'],
                    'start_time' => $data['enabled'] ? $data['start'] : null,
                    'end_time' => $data['enabled'] ? $data['end'] : null
                ]);
            }
        }
    }

    private function saveSpecificDates($schedule, $dates)
    {
        foreach ($dates as $item) {
            WorkingHourDate::create([
                'schedule_id' => $schedule->id,
                'work_date' => Carbon::parse($item['date'])->toDateString(),
                'is_working' => true,
                'start_time' => $item['start'],
                'end_time' => $item['end']
            ]);
        }
    }
}
