<?php

namespace App\Services\Client;

use App\Models\ClientEmergencyContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmergencyContactService
{

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            foreach ($data['emergency_full_name'] as $key => $item) {
                $d = $data['emergency_contact_id'][$key] ? ClientEmergencyContact::find($data['emergency_contact_id'][$key]) : new ClientEmergencyContact;
                $d->home_id = $data['home_id'];
                $d->user_id = $data['user_id'];
                $d->client_id = $data['client_id'];
                $d->name = $item;
                $d->phone_number = $data['emergency_phone_number'][$key];
                $d->relation = $data['emergency_relation'][$key];
                $d->save();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving Do Not Attempt CPR:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }


    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientEmergencyContact::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        return $query;
    }
    public function details($id)
    {
        return ClientEmergencyContact::find($id);
    }
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $table = ClientEmergencyContact::find($id);
            $table->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Do Not Attempt CPR:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
