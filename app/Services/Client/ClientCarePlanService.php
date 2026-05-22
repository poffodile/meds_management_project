<?php

namespace App\Services\Client;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Overview;
use App\Models\Objective;
use App\Models\CarePlanTask;
use App\Models\CarePlanPharmacy;
use App\Models\CarePlanMedication;
use App\Models\CarePlanPreference;
use App\Models\CarePlanEmergencyInformation;
use App\Models\CarePlanRisk;

class ClientCarePlanService
{
    
    public function store_overview(array $data): Overview
    {
        DB::beginTransaction();
        try{
            $clientCarePlanOverView = Overview::updateOrCreate(['id' => $data['id'] ?? null],$data);
            DB::commit();
            return $clientCarePlanOverView;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client Overview:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_objective(array $data): array
    {
        DB::beginTransaction();
        try{
            $clientCarePlanObjective = [];

            foreach($data as $obj){
                $clientCarePlanObjective[] = Objective::updateOrCreate(
                    ['id' => $obj['obj_id'] ?? null],
                    $obj
                );
            }
            DB::commit();
            return $clientCarePlanObjective;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client Objective:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_task(array $data): array
    {
        DB::beginTransaction();
        try{
            $clientCarePlanTask = [];

            foreach($data as $task){
                $clientCarePlanTask[] = CarePlanTask::updateOrCreate(
                    ['id' => $task['task_id'] ?? null],
                    $task
                );
            }
            DB::commit();
            return $clientCarePlanTask;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client care plan Task:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_pharmacy(array $data): CarePlanPharmacy
    {
        DB::beginTransaction();
        try{
            $carePlanPharmacy = CarePlanPharmacy::updateOrCreate(['id' => $data['pharmacy_id'] ?? null],$data);
            DB::commit();
            return $carePlanPharmacy;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client CarePlanPharmacy:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_medication(array $data): array
    {
        DB::beginTransaction();
        try{
            $carePlanMedication = [];

            foreach($data as $medi){
                $carePlanMedication[] = CarePlanMedication::updateOrCreate(
                    ['id' => $medi['medi_id'] ?? null],
                    $medi
                );
            }
            DB::commit();
            return $carePlanMedication;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client CarePlanMedication:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_preferences(array $data): CarePlanPreference
    {
        DB::beginTransaction();
        try{
            $carePlanPreferences = CarePlanPreference::updateOrCreate(['id' => $data['preferences_id'] ?? null],$data);
            DB::commit();
            return $carePlanPreferences;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client CarePlanPreference:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_emergency(array $data): CarePlanEmergencyInformation
    {
        DB::beginTransaction();
        try{
            $carePlanEmergency = CarePlanEmergencyInformation::updateOrCreate(['id' => $data['emergency_id'] ?? null],$data);
            DB::commit();
            return $carePlanEmergency;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client CarePlanEmergencyInformation:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }
    public function store_risk(array $data): array
    {
        DB::beginTransaction();
        try{
            $carePlanRisk = [];

            foreach($data as $medi){
                $carePlanRisk[] = CarePlanRisk::updateOrCreate(
                    ['id' => $medi['risk_id'] ?? null],
                    $medi
                );
            }
            DB::commit();
            return $carePlanRisk;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client CarePlanRisk:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
        
    }

    
    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = Overview::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        $query->with([
            'clients'
        ]);
        $query->withCount([
            'objectives',
            'tasks',
            'medications'
        ]);
        return $query->orderBy('id');
    }
    public function details($id){
        $caerPlanDetails = Overview::with(['objectives','tasks','risks','pharmacy','preferences','emergencyInfo','medications','clients'])->find($id);
        return $caerPlanDetails;
    }
    public function delete($id){
        DB::beginTransaction();
        try{
            $table = Overview::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete Client Care Task:', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);
            throw $e;
        }
    }
    public function objective_delete($id){
        DB::beginTransaction();
        try{
            $table = Objective::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Care Task:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
    public function task_delete($id){
        DB::beginTransaction();
        try{
            $table = CarePlanTask::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Care Task:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
    public function medical_delete($id){
        DB::beginTransaction();
        try{
            $table = CarePlanMedication::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Care Task:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
    public function risk_delete($id){
        DB::beginTransaction();
        try{
            $table = CarePlanRisk::find($id);
            $table->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Client Care Task:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
