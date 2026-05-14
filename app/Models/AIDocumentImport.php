<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIDocumentImport extends Model
{
    protected $table = 'ai_document_imports';

    protected $fillable = [
        'home_id',
        'client_id',
        'import_type',
        'uploaded_by',
        'original_filename',
        'stored_path',
        'file_size',
        'file_mime',
        'extracted_text_length',
        'import_status',
        'extracted_data',
        'imported_categories',
        'import_summary',
        'ai_model',
        'tokens_input',
        'tokens_output',
        'generation_time_ms',
        'error_message',
        'is_deleted',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'imported_categories' => 'array',
        'import_summary' => 'array',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'generation_time_ms' => 'integer',
        'is_deleted' => 'integer',
    ];

    public function scopeForHome($query, int $homeId)
    {
        return $query->where('home_id', $homeId);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    public function client()
    {
        return $this->belongsTo(\App\ServiceUser::class, 'client_id');
    }

    public function uploader()
    {
        return $this->belongsTo(\App\User::class, 'uploaded_by');
    }
}
