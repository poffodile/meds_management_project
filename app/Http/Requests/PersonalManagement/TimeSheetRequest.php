<?php

namespace App\Http\Requests\PersonalManagement;

use Illuminate\Foundation\Http\FormRequest;

class TimeSheetRequest extends FormRequest
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
            'time_sheet_id'  => 'nullable',
            'user_id'        => 'required|integer|exists:user,id',
            'date'           => 'required|date',
            'hours'          => 'nullable|numeric|min:0|max:24',
            'sleep'          => 'nullable|numeric|min:0|max:24',
            'wake_night'     => 'nullable|numeric|min:0|max:24',
            'disturbance'    => 'nullable|numeric|min:0|max:24',
            'annual_leave'   => 'nullable|numeric|min:0|max:24',
            'on_call'        => 'nullable|numeric|min:0|max:24',
            'comments'       => 'nullable|string|max:1000',
        ];
    }
}
