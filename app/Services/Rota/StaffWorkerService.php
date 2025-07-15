<?php

namespace App\Services\Rota;

use App\Models\Rota\StaffWorker;
use Illuminate\Support\Carbon;

class StaffWorkerService
{
    public function saveStaffWorkerData(array $data, int $homeId): StaffWorker
    {
        // dd($data);
        if (!empty($data['DOB'])) {
            $data['DOB'] = Carbon::createFromFormat('d-m-Y', $data['DOB'])->format('Y-m-d');
        }
        if (!empty($data['start_date'])) {
            $data['start_date'] = Carbon::createFromFormat('d-m-Y', $data['start_date'])->format('Y-m-d');
        }
        if (!empty($data['probation_start_date'])) {
            $data['probation_start_date'] = Carbon::createFromFormat('d-m-Y', $data['probation_start_date'])->format('Y-m-d');
        }
        if (!empty($data['probation_end_date'])) {
            $data['probation_end_date'] = Carbon::createFromFormat('d-m-Y', $data['probation_end_date'])->format('Y-m-d');
        }
        if (!empty($data['probation_renew_date'])) {
            $data['probation_renew_date'] = Carbon::createFromFormat('d-m-Y', $data['probation_renew_date'])->format('Y-m-d');
        }
        if (!empty($data['leave_date'])) {
            $data['leave_date'] = Carbon::createFromFormat('d-m-Y', $data['leave_date'])->format('Y-m-d');
        }

        $id = isset($data['staff_id']) ? $data['staff_id'] : null;
        // dd($data);
        return StaffWorker::updateOrCreate(['id' => $id], array_merge(['home_id' => $homeId], $data));
    }
    public function getStaffWorkerData($homeId)
    {
        return StaffWorker::where('home_id', $homeId)->where('deleted_at', null)->orderBy('created_at', 'desc')->get();
    }
    public function deleteStaffWorkerData($id)
    {
        return StaffWorker::where('id', $id)->update(['deleted_at' => now()]);
    }

    public function getData($id){
        return StaffWorker::where('id', $id)->first();
    }
   
   
}
