<?php

namespace App\Services\Client;

use App\Models\ClientGoal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientGoalService
{
    /**
     * Store or update a Client Goal.
     *
     * @param array $data
     * @return ClientGoal
     * @throws \Exception
     */
    public function store(array $data): ClientGoal
    {
        DB::beginTransaction();
        try {
            $clientGoal = ClientGoal::updateOrCreate(
                ['id' => $data['id'] ?? null],
                $data
            );
            DB::commit();
            return $clientGoal;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Client Goal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a list/builder of Client Goals with optional filters and specific columns.
     *
     * @param array $filters
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function list(array $filters = [], array $columns = ['*'])
    {
        $query = ClientGoal::select($columns);

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['goal_category_id'])) {
            $query->where('goal_category_id', $filters['goal_category_id']);
        }

        if (!empty($filters['created_today'])) {
            $query->whereDate('created_at', date('Y-m-d'));
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['support_level'])) {
            $query->where('support_level', $filters['support_level']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . trim($filters['search']) . '%');
        }

        $orderBy = $filters['order_by'] ?? 'id';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query;
    }

    /**
     * Get details of a Client Goal.
     *
     * @param int|string $id
     * @return ClientGoal|null
     */
    public function details($id): ?ClientGoal
    {
        return ClientGoal::find($id);
    }

    /**
     * Delete a Client Goal.
     *
     * @param int|string $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            $clientGoal = ClientGoal::find($id);
            if ($clientGoal) {
                $clientGoal->delete();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting Client Goal: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change status of a Client Goal.
     *
     * @param int|string $id
     * @param string $status
     * @param string|null $myReflection
     * @return ClientGoal|null
     * @throws \Exception
     */
    public function status_change($id, $status, $myReflection = null): ?ClientGoal
    {
        DB::beginTransaction();
        try {
            $clientGoal = ClientGoal::find($id);
            if ($clientGoal) {
                $clientGoal->status = $status;
                if ($myReflection !== null) {
                    $clientGoal->my_reflection = $myReflection;
                }
                $clientGoal->save();
            }
            DB::commit();
            return $clientGoal;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error changing Client Goal status: ' . $e->getMessage());
            throw $e;
        }
    }
}
