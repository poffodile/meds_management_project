<?php

namespace App\Models\Staff;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffSupervision extends Model
{
    use HasFactory, SoftDeletes;

    public function members()
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }
    public function supervisors()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }
    public function attachments()
    {
        return $this->hasMany(StaffSupervisionForm::class, 'staff_supervision_id', 'id');
    }
}
