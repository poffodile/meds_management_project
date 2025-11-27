<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
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
            'home_id'              => 'required|string|max:255',
            'name'                 => 'required|string|max:255',
            'user_name'            => 'required|string|max:255|unique:user,user_name',
            'email'                => 'required|email|max:255|unique:user,email',
            'department'           => 'required|integer|exists:company_departments,id',
            'job_title'            => 'required|string|max:255',
            'phone_no'             => 'required|string|max:20',
            'description'          => 'nullable|string|max:500',
            'payroll'              => 'nullable|string|max:255',
            'date_of_joining'      => 'required|date',
            'date_of_leaving'      => 'nullable|date|after_or_equal:date_of_joining',
            'holiday_entitlement'  => 'nullable|numeric|min:0|max:365',
            'image'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'send_credentials'     => 'nullable|boolean',
            'password'             => 'required|string|min:6'
        ];
    }

    public function messages()
    {
        return [
            'home_id.required' => 'Home is required.',
            'name.required' => 'Name is required.',
            'user_name.required' => 'Username is required.',
            'user_name.unique' => 'This username is already taken.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already in use.',
            'department.required' => 'Department is required.',
            'job_title.required' => 'Job title is required.',
            'password.required' => 'Password is required.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
