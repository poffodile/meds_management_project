<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use App\Models\OnboardingConfigForm;
use App\Services\Staff\OnboardingConfigService;
use App\Services\Staff\StaffonBoardingService;
use App\ServiceUser;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class clientonboardingController extends Controller
{
    protected OnboardingConfigService $onboardingConfigService;
    protected StaffonBoardingService $staffOnboardingService;

    public function __construct(OnboardingConfigService $onboardingConfigService, StaffonBoardingService $staffOnboardingService)
    {
        $this->onboardingConfigService = $onboardingConfigService;
        $this->staffOnboardingService = $staffOnboardingService;
    }
    public function index()
    {
        return view('frontEnd/roster/client/client_onboarding/client_onboarding');
    }

    public function loadData(Request $req)
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;

        if (!$homeId) {
            return response()->json([
                'status' => false,
                'message' => 'Home ID not found'
            ]);
        }

        $search = trim($req->search ?? '');
        $statusFilter = trim($req->status ?? '');
        $department = trim($req->department ?? '');

        $staffQuery = ServiceUser::select('id', 'home_id', 'earning_scheme_label_id', 'name', 'user_name', 'phone_no', 'date_of_birth', 'department', 'child_type', 'room_type', 'current_location', 'street', 'care_needs', 'suFundingType', 'status', 'is_deleted')
            ->where(['home_id' => $homeId, 'is_deleted' => 0]);
        $total_client = (clone $staffQuery)->count();
        $active_client = (clone $staffQuery)->where('status', 1)->count();
        if (!empty($search)) {
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($department)) {
            $staffQuery->where(function ($q) use ($department) {
                $q->where('department', $department);
            });
        }
        if ($statusFilter == 'active') {
            $staffQuery->where(function ($q) {
                $q->where('status', 1);
            });
        }
        // paginate first

        $staff = $staffQuery->latest()->paginate(25);

        // transform + filter

        $totalStages = [];
        $collection = $staff->getCollection()->map(function ($item) use (&$totalStages) {
            $filters = ['type' => 'client', 'care_type' => $item->department,];
            $workflow = $this->onboardingConfigService
                ->loadWorkFlow($filters)
                ->where('status', 1)
                ->withCount([
                    'getstages as getstages_count' => function ($q) use ($item) {
                        $q->where('status', 1)->where('required_stage', 1)->where(function ($q1) use ($item) {
                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $item->id);
                        });
                    },
                    'getstages as onboardingforms_count' => function ($q) use ($item) {
                        $q->where('status', 1)->where('required_stage', 1)
                            ->where(function ($q1) use ($item) {
                                $q1->whereNull('selected_user_ids')
                                    ->orWhereJsonLength('selected_user_ids', 0)
                                    ->orWhereJsonContains('selected_user_ids', (string) $item->id);
                            })->whereHas('onboardingforms', function ($q2) use ($item) {
                                $q2->where('user_id', $item->id);
                            });
                    }
                ])->with([
                    'getstages' => function ($q) use ($item) {
                        $q->select('id', 'onboarding_config_id', 'stage_name', 'required_stage')->where('required_stage', 1)
                            ->where(function ($q1) use ($item) {
                                $q1->whereNull('selected_user_ids')
                                    ->orWhereJsonLength('selected_user_ids', 0)
                                    ->orWhereJsonContains('selected_user_ids', (string) $item->id);
                            })
                            ->withCount(['onboardingforms as is_active' => function ($q2) use ($item) {
                                $q2->where('user_id', $item->id);
                            }]);
                        // ->whereHas('onboardingforms', function ($q2) use ($item) {
                        //     $q2->where('user_id', $item->id);
                        // });
                    },
                ])
                ->first();

            // print_r($workflow);


            $total = $workflow->getstages_count ?? 0;
            $completed = $workflow->onboardingforms_count ?? 0;
            $totalStages[$item->id] = $total;


            if ($workflow && $workflow->auto_activate == 1 && $total == $completed) {
                if ($item->status != 1) {
                    $item->status = 1;
                    $item->save();
                }
            }
            $item->workflow = $workflow ?? null;
            $item->form_percentage = $total > 0
                ? round((($completed ?? 0) / ($total ?? 0)) * 100, 0)
                : 0;
            $item->completed_forms = $completed;
            return $item;
        });
        // ✅ Apply status filter


        if ($statusFilter == 'not_started') {
            $collection = $collection->filter(function ($item) {
                return $item->completed_forms == 0;
            });
        }

        if ($statusFilter == 'in_progress') {
            $collection = $collection->filter(function ($item) {
                return $item->completed_forms > 0
                    && $item->completed_forms < $item->workflow->getstages_count;
            });
        }
        $staffss = ServiceUser::where([
            'home_id' => $homeId,
            'is_deleted' => 0
        ])->get(['id', 'department']);

        $staffIds = $staffss->pluck('id');
        $forms = OnboardingConfigForm::whereIn('user_id', $staffIds)
            ->where('type', 'client')
            ->select('user_id', \DB::raw('COUNT(DISTINCT onboarding_config_stage_id) as completed_forms'))
            ->groupBy('user_id')
            ->pluck('completed_forms', 'user_id');

        $in_progress_count = 0;

        foreach ($staffss as $row) {
            //echo "$userId  ----  $department\n";
            $userId = $row->id;
            $department = $row->department;
            $item = (object)['id' => $userId];
            $filters = ['type' => 'client', 'care_type' => $department];
            $workflow = $this->onboardingConfigService
                ->loadWorkFlow($filters)
                ->where('status', 1)
                ->withCount(['getstages as total' => function ($q) use ($userId) {
                    $q->where('status', 1)
                        ->where('required_stage', 1)
                        ->where(function ($q1) use ($userId) {
                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $userId);
                        });
                }])
                ->first();

            $total = $workflow->total ?? 0;
            $completed = $forms[$userId] ?? 0;

            if ($completed > 0 && $completed < $total) {
                $in_progress_count++;
            }
        }

        // reset collection after filter
        $staff->setCollection($collection->values());

        $data_count  = [
            'total_client' => $total_client,
            'active_client' => $active_client,
            'active_client_per' => $active_client > 0 && $total_client > 0 ? number_format(($active_client / $total_client) * 100, 2) : '0.0',
            'in_progress' => $in_progress_count,
            'not_started' => (($total_client - $completed) - $in_progress_count) ?? 0,
        ];
        $nextPage = null;

        if (empty($statusFilter)) {
            // normal case
            $nextPage = $staff->nextPageUrl();
        } else {
            // custom filter case
            $nextPage = $staff->perPage() < $collection->count() ? $staff->nextPageUrl() : null;
        }
        return response()->json([
            'status' => true,
            'data_count' => $data_count,
            'data'   => $staff->items(),
            'next_page' => $nextPage,
            'pagination' => [
                'total' =>  $collection->count(),
                'per_page' => $staff->perPage(),
                'current_page' => $staff->currentPage(),
                'total_pages' => $staff->lastPage(),
                'next_page' => $nextPage
            ]
        ]);
    }
    function loadUserDetails(Request $req)
    {
        try {
            $staffQuery = ServiceUser::select('id', 'name', 'department','home_id')->find($req->user_id);
            $filters = [
                'type' => 'client',
                'care_type' => $staffQuery->department,
                'home_id'=>$staffQuery->home_id
            ];
            $workflowData =  $this->onboardingConfigService->loadWorkFlow($filters)->where('status', 1)
                ->withCount([
                    'getstages as getstages_count' => function ($q) use ($req) {
                        $q->where('status', 1)->where('required_stage', 1)->where(function ($q1) use ($req) {
                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $req->user_id);
                        });
                    },
                    'getstages as onboardingforms_count' => function ($q) use ($req) {
                        $q->where('status', 1)->where('required_stage', 1)->where(function ($q1) use ($req) {
                            $q1->whereNull('selected_user_ids')
                                ->orWhereJsonLength('selected_user_ids', 0)
                                ->orWhereJsonContains('selected_user_ids', (string) $req->user_id);
                        })
                            ->whereHas('onboardingforms', function ($q2) use ($req) {
                                $q2->where('user_id', $req->user_id);
                            });
                    }
                ])
                ->first();
            $stages = [];
            if ($workflowData) {
                $total = $workflowData->getstages_count ?? 0;
                $completed = $workflowData->onboardingforms_count ?? 0;
                $workflowData->form_percentage = $total > 0
                    ? round(($completed / $total) * 100, 0)
                    : 0;
                $stages =  $this->onboardingConfigService->loadWorkflowStages(['workflow_id' => $workflowData->id, 'selected_user_ids' => (string) $req->user_id])
                    ->with(['onboardingforms' => function ($q) use ($req) {
                        $q->where('user_id', $req->user_id);
                    }])
                    ->where('status', 1)->orderBy('order_no')->get();
            }
            return response()->json([
                'status' => true,
                'message' => '',
                'userData' => $staffQuery,
                'workflowData' => $workflowData,
                'workflowStages'   => $stages,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => true,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    function loadforms(Request $req)
    {
        return    $this->staffOnboardingService->formFetch($req);
    }
    function saveforms(Request $req)
    {
        $data = $this->staffOnboardingService->formSave($req);

        if ($data['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Form Saved',
                'data' => $data['data']
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Form not Saved'], 500);
    }
}
