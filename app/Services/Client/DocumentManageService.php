<?php

namespace App\Services\Client;

use App\Models\ClientDocumentManage;
use App\Models\ClientEmergencyContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentManageService
{
    public function store(array $data)
    {
        DB::beginTransaction();
        $doc_file = null;
        try {
            //   return $data;

            $doc = new ClientDocumentManage;
            $doc->home_id = $data['home_id'];
            $doc->user_id = $data['user_id'];
            $doc->client_id = $data['client_id'];
            $doc->document_type = $data['document_type'];
            $doc->doc_name = $data['doc_name'];
            !empty($data['doc_expiry_date']) ? $doc->expiry_date = $data['doc_expiry_date'] : '';
            $doc->access_level_id = $data['doc_access_level_id'];
            $doc->tags = $data['doc_tags'];
            $doc->is_confidential = isset($data['is_confidential']) ? 1 : 0;
            $doc->note = $data['doc_notes'];
            $doc->save();
            if (request()->hasFile('doc_files')) {
                if (!empty($doc->file)) {
                    $oldPath = public_path('uploads/client/documents/' . $doc->file);

                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $image     = request()->file('doc_files');
                $imageName = time() . Str::random() . '.' . $image->getClientOriginalExtension();
                $sizeInKB = round($image->getSize() / 1024, 2);
                $image->move('public/uploads/client/documents', $imageName);
                $doc->file = $imageName;
                $doc->file_size = $sizeInKB;
                $doc->save();

                $doc_file = public_path('uploads/client/documents/' . $imageName);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            if (!empty($doc_file) && File::exists($doc_file)) {
                File::delete($doc_file);
            }
            throw $e;
        }
    }


    public function list(array $filters = [])
    {
        // echo "<pre>";print_r($filters);die;
        $query = ClientDocumentManage::query();

        if (!empty($filters['home_id'])) {
            $query->where('home_id', $filters['home_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        return $query;
    }
    public function details($id)
    {
        return ClientDocumentManage::find($id);
    }
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $table = ClientDocumentManage::find($id);
            if (!empty($table->file)) {
                $path = public_path('uploads/client/documents/' . $table->file);

                if (File::exists($path)) {
                    File::delete($path);
                }
            }

            $table->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error delete Do Not Attempt CPR:', [
            //     'error' => $e->getMessage(),
            //     'data'  => $data
            // ]);
            throw $e;
        }
    }
}
