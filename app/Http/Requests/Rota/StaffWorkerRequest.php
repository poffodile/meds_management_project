<?php

namespace App\Http\Requests\Rota;

use Illuminate\Foundation\Http\FormRequest;

class StaffWorkerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'home_id' => 'nullable|string|max:255',
            'staff_id' => 'nullable|integer',
            'surname' => 'required|string|max:255',
            'forename' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'postCode' => 'required|string|max:10',
            'DOB' => 'required|date',
            'account_num' => 'required|string|max:20',
            'sort_code' => 'required|string|max:8',
            'status' => 'nullable|in:residential,supported_accomodation,parental,foundations_for_life,office_staff,leavers',
            'rate_of_pay' => 'required|numeric|min:0',
            'level' => 'nullable|in:qualified,unqualified',
            'start_date' => 'required|date',
            'job_role' => 'required|string|max:255',
            'NIN' => 'required|string|max:255',
            'starter_declaration' => 'nullable|numeric|in:1,2,3',
            'probation_start_date' => 'required|date',
            'probation_end_date' => 'required|date|after:probation_start_date',
            'probation_renew_date' => 'nullable|date|after:probation_end_date',
            'probation_enrollered' => 'nullable|boolean',
            'dbs_clear' => 'nullable|boolean',
            'dbs_number' => 'nullable|string|max:255',
            'dbs_service_update' => 'nullable|boolean',
            'student_loan' => 'nullable|in:no_student_loan,postgraduate,plan_1,plan_2,plan_4',
            'leave_date' => 'nullable|date|after:start_date',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|integer|min:11',
            'deleted_at' => 'nullable|date'
        ];
    }
}
