<?php

namespace App\Models\ServiceUserManagement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ServiceUser;

class ServiceUserExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'home_id',
        'service_user_id',
        'expense_date',
        'title',
        'amount',
        'notes'
    ];

    public function serviceUser()
    {
        return $this->belongsTo(ServiceUser::class, 'service_user_id');
    }
}
