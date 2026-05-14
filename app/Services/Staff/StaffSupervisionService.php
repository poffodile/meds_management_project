<?php

namespace App\Services\Staff;

use App\DynamicFormBuilder;
use Carbon\Carbon;
use App\Models\Staff\StaffSupervision;
use App\Models\Staff\StaffSupervisionForm;
class StaffSupervisionService
{
    public function store(array $data): StaffTask
    {
        
    }


    public function list(array $filters = [])
    {
        try {
            // return $filters;
            $home_id = $filters['home_id'] ?? null;
            $user_id = $filters['user_id'] ?? "";
            $status = $filters['filter'] ?? "";
            $assinged_user_id = $filters['assinged_user_id'] ?? "";
            $search = $filters['search'] ?? "";
            $subQuery = StaffSupervision::with([
                'members:id,name,image',
                'supervisors:id,name,image'
            ]);
            if (!empty($home_id)) {
                $subQuery->where('home_id', $home_id);
            }
            if (!empty($user_id)) {
                $subQuery->where('user_id', $user_id);
            }
            if (!empty($assinged_user_id)) {
                $subQuery->where('member_id', $assinged_user_id);
            }
            if ($search) {
                $subQuery->where(function ($q) use ($search) {
                    $q->whereHas('members', function ($m) use ($search) {
                        $m->where('name', 'like', "%$search%");
                    });
                    // ->orWhereHas('supervisors', function ($s) use ($search) {
                    //     $s->where('name', 'like', "%$search%");
                    // });
                });
            }

            if ($status) {
                $today = Carbon::today()->format('Y-m-d');
                $after7 = Carbon::today()->addDays(7)->format('Y-m-d');

                $subQuery->whereRaw("DATE_ADD(date, INTERVAL frequency DAY) IS NOT NULL");

                if ($status == 'overdue') {
                    $subQuery->whereRaw("DATE_ADD(date, INTERVAL frequency DAY) < ?", [$today]);
                }

                if ($status == 'due_soon') {
                    $subQuery->whereRaw("DATE_ADD(date, INTERVAL frequency DAY) BETWEEN ? AND ?", [$today, $after7]);
                }

                if ($status == 'on_track') {
                    $subQuery->whereRaw("DATE_ADD(date, INTERVAL frequency DAY) > ?", [$after7]);
                }
            }

            $record = $subQuery->latest()->paginate(15);

            $supervision_type_arr =  [
                'one_to_one' => 'Formal 1:1',
                'informal' => 'Informal',
                'group' => 'Group',
                'probation_review' => 'Probation Review',
                'spot_check' => 'Spot Check',
            ];
            $record->getCollection()->transform(function ($q) use ($supervision_type_arr) {
                $statusText = "Pending";
                if ($q->date) {
                    $dueDate = Carbon::parse($q->date)->addDays($q->frequency);
                    $today = Carbon::today();
                    $diff = $today->diffInDays($dueDate, false); // negative = overdue

                    if ($diff < 0) {
                        $statusText = "Overdue";
                    } elseif ($diff <= 7) {
                        $statusText = "Due Soon";
                    } else {
                        $statusText = "On Track";
                    }
                }
                return [
                    'id' => $q->id,
                    'member_name' => ucfirst($q->members->name) ?: '',
                    'supervisor_name' => ucfirst($q->supervisors->name) ?: '',
                    'supervision_type' => $supervision_type_arr[$q->supervision_type] ?? '',
                    'date' => $q->date ? Carbon::parse($q->date)->format('d M, Y') : '',
                    'time' => $q->time ? $q->time : '',
                    'type' => $q->type ? $q->type : '',
                    'status' => $statusText,
                    'next_due' => $q->date ? Carbon::parse($q->date)->addDays($q->frequency)->format('d M, Y') : '',
                    // 'note' => $q->note ?? "Supervisor Note",
                    // 'comment' => $q->comment ?? "Supervisor Comments",
                ];
            });
            // if (!$record) {
            //     return [
            //         'status' => false,
            //     ];
            // }
            $subAllRecords = StaffSupervision::with([
                'members:id,name,image',
            ]);
            if (!empty($home_id)) {
                $subAllRecords->where('home_id', $home_id);
            }
            if (!empty($user_id)) {
                $subAllRecords->where('user_id', $user_id);
            }
            $subAllRecords->where('home_id', $home_id);
            $allRecords = $subAllRecords->get();

            $counts = [
                'total' => $allRecords->count(),
                'overdue' => 0,
                'due_soon' => 0,
                'on_track' => 0,
                'overdue_text' => '',
            ];

            $today = Carbon::today();
            $overdueNames = [];
            foreach ($allRecords as $q) {

                if (!$q->date) continue;

                $dueDate = Carbon::parse($q->date)->addDays($q->frequency);
                $diff = $today->diffInDays($dueDate, false);

                if ($diff < 0) {
                    $overdueNames[] = $q->members->name;
                    $counts['overdue']++;
                } elseif ($diff <= 7) {
                    $counts['due_soon']++;
                } else {
                    $counts['on_track']++;
                }
            }
            $limit = 5; // kitne names show karne hain

            $total = count($overdueNames);

            if ($total > 0) {

                $shown = array_slice($overdueNames, 0, $limit);

                $text = implode(', ', $shown);

                if ($total > $limit) {
                    $remaining = $total - $limit;
                    $text .= " and {$remaining} more";
                }

                $counts['overdue_text'] = "The following staff need supervision: {$text}.";
            }
            return  [
                'success' => true,
                'data' => $record->items(), // 👈 important
                'pagination' => [
                    'total' => $record->total(),
                    'per_page' => $record->perPage(),
                    'current_page' => $record->currentPage(),
                    'total_pages' => $record->lastPage(),
                ],
                'counts' => $counts
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function details($id)
    {
        $data = StaffSupervision::with([
            'members:id,name,image',
            'supervisors:id,name,image',
            'attachments'
        ])->find($id);
        if (!$data) {
            return ['success' => false];
        }
        $supervision_type_arr =  [
            'one_to_one' => 'Formal 1:1',
            'informal' => 'Informal',
            'group' => 'Group',
            'probation_review' => 'Probation Review',
            'spot_check' => 'Spot Check',
        ];
        $statusText = "Pending";
        if ($data->date) {
            $dueDate = Carbon::parse($data->date)->addDays($data->frequency);
            $today = Carbon::today();
            $diff = $today->diffInDays($dueDate, false); // negative = overdue

            if ($diff < 0) {
                $statusText = "Overdue";
            } elseif ($diff <= 7) {
                $statusText = "Due Soon";
            } else {
                $statusText = "On Track";
            }
        }
        $attachmentsArr = [];
        foreach ($data->attachments as $i) {
            $doc_name = $i->doc_name;
            $doc_type = $i->doc_type;
            $created_at = $i->created_at->format('Y-m-d');
             $type = 'attachment';
              $doc_path =  url('public/' . $i->doc_path); // url('uploads/supervision/documents/'. );
            if ($i->dynamic_form_id) {
                $dFB = DynamicFormBuilder::find($i->dynamic_form_id);
                $doc_name = $dFB->title;
                $doc_type = 'TEST';
                $type = 'form';
                 $doc_path = '';
            }
            $attachmentsArr[] = [
                'id' => $i->id,
                'doc_name' => $doc_name,
                'doc_type' => $doc_type,
                'created_at' => $created_at,
                'type' => $type,
                'doc_path' => $doc_path,
            ];
        }
        return [
            'id' => $data->id,
            'home_id' => $data->home_id,
            'user_id' => $data->user_id,
            'member_id' => $data->member_id,
            'supervisor_id' => $data->supervisor_id,
            'member_name' => $data->members->name ?? "",
            'supervisor_name' => $data->supervisors->name ?? "",
            'supervision_type' => $supervision_type_arr[$data->supervision_type],
            'date' => $data->date ? Carbon::parse($data->date)->format('d M, Y') : '',
            'time' => $data->time ?? "",
            'note' => $data->note ?? "",
            'comment' => $data->comment ?? "",
            'status' => $statusText,
            'type' => $data->type,
            'next_due' => $data->date ? Carbon::parse($data->date)->addDays($data->frequency)->format('d M, Y') : '',
            // 'attachments' => $data->attachments,
            'attachments' => $attachmentsArr,
        ];
    }
    public function formSave($req)
    {
        // return $req;
        $singleData = StaffSupervisionForm::find($req['supervision_form_id']);
        $singleData->is_form_filled = 1;
        $singleData->pattern_data = json_encode($req['data']);
        $singleData->save();
        return $singleData;
    }
    public function formFetch($request)
    {
        $singleData = StaffSupervisionForm::find($request['supervision_form_id']);
        $formTemplate = DynamicFormBuilder::where('id', $singleData->dynamic_form_id)->first();
        return ['pattern_value' => $singleData->pattern_data, 'pattern' => $formTemplate->pattern];
    }
}
