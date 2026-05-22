<?php

namespace App\Services\Client;

use App\Models\ClientConsent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsentService
{

    public function store(array $data): ClientConsent
    {
        DB::beginTransaction();
        try {
            $consent = ClientConsent::updateOrCreate(['id' => $data['id'] ?? null], $data);
            DB::commit();
            return $consent;
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
        $query = ClientConsent::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $consent = $query
            ->orderBy('id', 'DESC')
            ->paginate(10);
        try {
            $today = date('Y-m-d');

            foreach ($consent as $item) {

                if (!empty($item->expiry_date) && strtotime($item->expiry_date) <= strtotime($today) && $item->status == 'Granted') {
                    $item->status = 'Expired';
                    $item->save();
                }
            }
        } catch (\Exception $e) {
        }
        return $consent;
    }
    public function details($id)
    {
        return ClientConsent::find($id);
    }
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $table = ClientConsent::find($id);
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
    public function changeStatus($id, $status)
    {
        DB::beginTransaction();
        try {
            $table = ClientConsent::find($id);
            $table->status = $status;
            $table->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error status change consent:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
    public function updateExpireStatus($home_id)
    {
        DB::beginTransaction();
        try {
            $today = date('Y-m-d');

            ClientConsent::where('home_id', $home_id)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $today)
                ->where('status', '!=', 'Expired')
                ->update(['status' => 'Expired']);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('update Expire Status:', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
