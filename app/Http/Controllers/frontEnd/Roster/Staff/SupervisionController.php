<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\DynamicFormBuilder;
use App\Http\Controllers\Controller;
use App\Models\Staff\StaffSupervision;
use App\Models\Staff\StaffSupervisionForm;
use App\Services\Staff\StaffSupervisionService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SupervisionController extends Controller
{
    protected $supervisions;
    public function __Construct(StaffSupervisionService $supervisions)
    {
        $this->supervisions = $supervisions;
    }

    public function index()
    {
        $home_ids = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];

        $userList = User::where('home_id', $home_id)->latest()->get();
        $data['userList'] = $userList;
        $supervisionType = [
            ['key' => 'one_to_one', 'name' => 'Formal 1:1'],
            ['key' => 'informal', 'name' => 'Informal'],
            ['key' => 'group', 'name' => 'Group'],
            ['key' => 'probation_review', 'name' => 'Probation Review'],
            ['key' => 'spot_check', 'name' => 'Spot Check'],
        ];
        $frenquencyList = [
            ['key' => 30, 'name' => 'Monthly'],
            ['key' => 42, 'name' => '6 Weekly'],
            ['key' => 56, 'name' => '8 Weekly'],
            ['key' => 90, 'name' => 'Quarterly'],
        ];
        $data['supervisionType'] = $supervisionType;
        $data['frenquencyList'] = $frenquencyList;
        $data['dynamic_form_builder'] = DynamicFormBuilder::getFormList();
        return view("frontEnd/roster/staff/supervision_management", $data);
    }

    public function record_saved(Request $request)
    {
        try {
            DB::beginTransaction();
            // die;
            $validator = Validator::make($request->all(), [
                'id' => 'nullable',
                'member_id' => 'required',
                'supervisor_id' => 'required',
                'date' => 'required|date',
                'supervision_type' => 'required',
                'note' => 'nullable',
                'comment' => 'nullable',
            ], [
                'member_id.required' => 'Please select a staff member.',
                'supervisor_id.required' => 'Please select a supervisor.',
                'date.required' => 'Please select a date for the supervision.',
                'date.date' => 'The date must be a valid date format.',
                'supervision_type.required' => 'Please select a supervision type.',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->toArray(),
                ], 422);
            }
            // return $request->all();
            $home_ids = Auth::user()->home_id;
            $user_id = Auth::user()->id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            if ($request->id) {
                $data = StaffSupervision::find($request->id);
                if (!$data) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Supervision record not found.'
                    ], 404);
                }
                $data->user_id = $user_id;
                $data->home_id = $home_id;
                $data->member_id = $request->member_id;
                $data->supervisor_id = $request->supervisor_id;
                $data->frequency = $request->frequency;
                $data->date = $request->date;
                $data->supervision_type = $request->supervision_type;
                $data->note = $request->type == 'schedule' ? "Scheduled for {$request->time}. " . $request->note : $request->note;
                $data->comment = $request->comment;
                $data->type = $request->type;
                $request->type == 'schedule' ? $data->time = $request->time : "";
                $data->save();
            } else {
                $data = new StaffSupervision;
                $data->user_id = $user_id;
                $data->home_id = $home_id;
                $data->member_id = $request->member_id;
                $data->supervisor_id = $request->supervisor_id;
                $data->frequency = $request->frequency;
                $data->date = $request->date;
                $data->supervision_type = $request->supervision_type;
                $data->note = $request->type == 'schedule' ? "Scheduled for {$request->time}. " . $request->note : $request->note;
                $data->comment = $request->comment;
                $data->type = $request->type;
                $request->type == 'schedule' ? $data->time = $request->time : "";
                $data->save();
            }
            $selectedAttachmentIds = $request->selectedAttachmentIds ? json_decode($request->selectedAttachmentIds) : [];
            $dynamic_form_id = $request->dynamic_form_id ? json_decode($request->dynamic_form_id) : [];
            if ($request->has('attachments')) {

                foreach ($request->attachments as $index => $item) {

                    $file = $request->file("attachments.$index.file");

                    if ($file) {

                        $imageName = time() . Str::random(10) . '.' . $file->getClientOriginalExtension();

                        $destinationPath = public_path('uploads/supervision/documents');

                        $file->move($destinationPath, $imageName);

                        $doc_file_path = 'uploads/supervision/documents/' . $imageName;

                        $doc = new StaffSupervisionForm();
                        $doc->staff_supervision_id = $data->id;
                        $doc->doc_name = $item['doc_name'];
                        $doc->doc_type = $item['doc_type'];
                        $doc->doc_required = $item['doc_required'] ?? 0;
                        $doc->doc_path = $doc_file_path;
                        $doc->save();
                    }
                }
            }
            if (count($dynamic_form_id) > 0) {
                foreach ($dynamic_form_id as $i) {
                    $doc = new StaffSupervisionForm();
                    $doc->dynamic_form_id = $i;
                    $doc->staff_supervision_id = $data->id;
                    $doc->save();
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Supervision record saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function fetch_supervision(Request $req)
    {
        try {
            $home_ids = Auth::user()->home_id;
            $user_id = Auth::user()->id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $status = $req->status;
            $search = $req->search;

            $reqData = [
                'filter' => $status,
                'search' => $search,
                'home_id' => $home_id,
            ];
            $list = $this->supervisions->list($reqData);
            return response()->json($list);
            $subQuery = StaffSupervision::with([
                'members:id,name,image',
                'supervisors:id,name,image'
            ])
                ->where('home_id', $home_id);
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

            $record = $subQuery->latest()->paginate(25);
            $supervision_type_arr =  [
                'one_to_one' => 'Formal 1:1',
                'informal' => 'Informal',
                'group' => 'Group',
                'probation_review' => 'Probation Review',
                'spot_check' => 'Spot Check',
            ];
            $record->getCollection()->transform(function ($q) use ($supervision_type_arr) {

                $statusText = "<span class='careBadg greenbadges'>Pending</span>";

                if ($q->date) {
                    $dueDate = Carbon::parse($q->date)->addDays($q->frequency);
                    $today = Carbon::today();
                    $diff = $today->diffInDays($dueDate, false); // negative = overdue

                    if ($diff < 0) {

                        $statusText = "<span class='careBadg redbadges'>Overdue</span>";
                    } elseif ($diff <= 7) {
                        $statusText = "<span class='careBadg yellowbadges'>Due Soon</span>";
                    } else {
                        $statusText = "<span class='careBadg greenbadges'>On Track</span>";
                    }
                }

                // $statusText = "<span class='careBadg greenbadges'>on track</span>";
                return [
                    'id' => $q->id,
                    'member_name' => ucfirst($q->members->name) ?: '',
                    'supervisor_name' => ucfirst($q->supervisors->name) ?: '',
                    'supervision_type' => $supervision_type_arr[$q->supervision_type] ?? 'N/A',
                    'date' => $q->date ? Carbon::parse($q->date)->format('d M, Y') : 'N/A',
                    'time' => $q->time ? $q->time : 'N/A',
                    'type' => $q->type ? $q->type : 'N/A',
                    'status' => $statusText,
                    'next_due' => $q->date ? Carbon::parse($q->date)->addDays($q->frequency)->format('d M, Y') : 'N/A',
                    'note' => $q->note ?? "Supervisor Note",
                    'comment' => $q->comment ?? "Supervisor Comments",
                ];
            });
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supervision record not found.'
                ], 404);
            }
            $allRecords = StaffSupervision::with([
                'members:id,name,image',
            ])->where('home_id', $home_id)->get();
            $counts = [
                'total' => $allRecords->count(),
                'overdue' => 0,
                'due_soon' => 0,
                'on_track' => 0,
                'overdue_text' => ''
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
            return response()->json([
                'success' => true,
                'data' => $record->items(), // 👈 important
                'pagination' => [
                    'total' => $record->total(),
                    'per_page' => $record->perPage(),
                    'current_page' => $record->currentPage(),
                    'total_pages' => $record->lastPage(),
                ],
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function supervision_delete(Request $req)
    {
        try {
            DB::beginTransaction();
            $data = StaffSupervision::find($req->id);
            $data->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Delete successfully !!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function supervision_details(Request $req)
    {
        try {
            $validator = Validator::make($req->all(), [
                'id' => [
                    'required',
                    Rule::exists('staff_supervisions', 'id')
                        ->whereNull('deleted_at')
                ],
            ], [
                'id.exists' => 'Data Not Found !!',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            $data = $this->supervisions->details($req->id);
            if (isset($data['success']) && !$data['success']) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Data Not Found !!',
                ], 422);
            }
            return response()->json([
                'status' => true,
                'message' => 'Supervisions Details',
                'data' => $data

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),

            ], 500);
        }
    }

    public function supervision_webview_form($id)
    {
        $singleData = StaffSupervisionForm::find($id);
        if (!$singleData) {
            return response(view('frontEnd.error_404'), 404);
        }
        $data['singleData'] = $singleData;
        $data['formTemplate'] = DynamicFormBuilder::where('id', $singleData->dynamic_form_id)->first();
        return view('frontEnd.roster.staff.superVisionFormwebview', $data);
    }

    public function supervisionFormSave(Request $req)
    {
        $data = $this->supervisions->formSave($req);
        if ($data) {
            return response()->json(['status' => true, 'message' => 'Form Saved Successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Form not Saved']);
    }
    public function supervisionFormFetch(Request $request)
    {
        return $this->supervisions->formFetch($request);
    }
}
