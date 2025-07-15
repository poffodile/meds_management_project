<?php

namespace App\Models\Rota;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffWorker extends Model
{
    use HasFactory;

    protected $fillable=[
        'home_id',
        'surname',
        'forename',
        'address',
        'postCode',
        'DOB',
        'account_num',
        'sort_code',
        'status',
        'rate_of_pay',
        'level',
        'start_date',
        'job_role',
        'NIN',
        'starter_declaration',
        'probation_start_date',
        'probation_end_date',
        'probation_renew_date',
        'probation_enrollered',
        'student_loan',
        'dbs_clear',
        'dbs_number',
        'dbs_service_update',
        'leave_date',
        'email',
        'mobile',
        'deleted_at'
    ];
}
