<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\DynamicFormBuilder;

class ShiftDocument extends Model
{
    protected $table = 'shift_documents';

    protected $guarded = [];

    public function shift()
    {
        return $this->belongsTo(ScheduledShift::class, 'shift_id');
    }

    public function formTemplate()
    {
        return $this->belongsTo(DynamicFormBuilder::class, 'form_template_id');
    }

    public static function scheduleShiftFormSave($req)
    {
        $singleData = ShiftDocument::find($req['shift_document_id']);
        $singleData->is_form_filled = 1;
        $singleData->pattern = json_encode($req['data']);
        $singleData->save();
        return $singleData;
    }

    public static function scheduleShiftFormFetch($req)
    {
        $singleData = ShiftDocument::find($req['shift_document_id']);
        $formTemplate = DynamicFormBuilder::where('id', $singleData->form_id)->first();
        return ['pattern_value' => $singleData->pattern, 'pattern' => $formTemplate->pattern];
    }
}
