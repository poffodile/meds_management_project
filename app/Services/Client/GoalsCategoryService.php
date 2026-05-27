<?php

namespace App\Services\Client;

use App\Models\GoalsCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoalsCategoryService
{
    public function store(array $data): GoalsCategory
    {
        DB::beginTransaction();
        try {
            $goalsCategory = GoalsCategory::updateOrCreate(['id' => $data['id'] ?? null], $data);
            DB::commit();
            return $goalsCategory;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving Goals Category: ' . $e->getMessage());
            throw $e;
        }
    }

    public function list(array $filters = [], array $columns = ['*'])
    {
        $query = GoalsCategory::select($columns);

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . trim($filters['search']) . '%');
        }

        $orderBy = $filters['order_by'] ?? 'id';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        return $query;
    }

    public function details($id): ?GoalsCategory
    {
        return GoalsCategory::find($id);
    }

    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            $table = GoalsCategory::find($id);
            if ($table) {
                $table->delete();
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete Goals Category: ' . $e->getMessage());
            throw $e;
        }
    }

    public function status_change($id, $status): ?GoalsCategory
    {
        DB::beginTransaction();
        try {
            $table = GoalsCategory::find($id);
            if ($table) {
                $table->status = $status;
                $table->save();
            }
            DB::commit();
            return $table;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error status change Goals Category: ' . $e->getMessage());
            throw $e;
        }
    }
}
