<?php

namespace App\Services\Staff;

use App\Models\Staff\StaffReportIncidents;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffReportIncidentService
{

    public function store(array $data): StaffReportIncidents
    {
        DB::beginTransaction();
        try {
            $data['date_time'] = Carbon::parse($data['date_time'])->format('Y-m-d H:i:s');
            $countData = StaffReportIncidents::count();
            if ($countData < 10) {
                $ref = '000';
            } else if ($countData >= 10 && $countData < 100) {
                $ref = '00';
            } else if ($countData > 100 && $countData < 1000) {
                $ref = '0';
            }
            $data['ref'] =  "INC-" . time() . '-' . $ref . $countData + 1;
            // return $data['ref'];
            $incident = StaffReportIncidents::updateOrCreate(['id' => $data['id'] ?? null], $data);
            if (isset($data['is_safeguarding']) && $data['is_safeguarding'] == 1) {
                $sfArr = $data['safeguarding_detail'] ?? [];
                $incident->safeguarddetails()->sync($sfArr);
            }
            DB::commit();
            return $incident;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving incident report:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }


    public function list(array $filters = [])
    {
        $query = StaffReportIncidents::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date_time', [$filters['start_date'], $filters['end_date']]);
        }
        if (!empty($filters['Safeguarding']) && $filters['Safeguarding'] == 1) {
            $query->where('is_safeguarding', 1);
        }
        if (!empty($filters['incident_type_id']) && $filters['incident_type_id'] != 0) {
            $query->where('incident_type_id', $filters['incident_type_id']);
        }
        if (!empty($filters['status']) && $filters['status'] != 0) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search_incident'])) {
            $keyword = $filters['search_incident'];

            $query->where(function ($q) use ($keyword, $filters) {

                $q->where('what_happened', 'LIKE', "%{$keyword}%")

                    ->orWhereHas('clients', function ($clientQuery) use ($keyword) {
                        $clientQuery->where('name', 'LIKE', "%{$keyword}%");
                    })->orWhereHas('incidentType', function ($clientQuery) use ($keyword, $filters) {
                        $clientQuery->where('type', 'LIKE', "%{$keyword}%")
                            ->where('status', 1)->where('home_id', $filters['home_id']);
                    });
            });
        }
        return $query->with([
            'incidentType:id,type',
            'clients:id,name'
        ])->latest()->paginate(2);
    }
    public function report_details($id)
    {
        return StaffReportIncidents::with([
            'incidentType:id,type',
            'clients:id,name',
            'safeguarddetails'
        ])->find($id);
    }
}
