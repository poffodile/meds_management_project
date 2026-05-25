<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\AccessLevel;
use App\Http\Controllers\Controller;
use App\Models\CompanyDepartment;
use App\Models\OnboardingConfigForm;
use App\Services\Staff\OnboardingConfigService;
use App\Services\Staff\StaffonBoardingService;
use App\Services\Staff\StaffService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class staffonboardingController extends Controller
{
    protected StaffService $staffService;
    protected OnboardingConfigService $onboardingConfigService;
    protected StaffonBoardingService $staffOnboardingService;

    public function __construct(StaffService $staffService, OnboardingConfigService $onboardingConfigService, StaffonBoardingService $staffOnboardingService)
    {
        $this->staffService = $staffService;
        $this->onboardingConfigService = $onboardingConfigService;
        $this->staffOnboardingService = $staffOnboardingService;
    }

    public function index()
    {
        $homeIds = explode(',', auth()->user()->home_id);
        $homeId  = $homeIds[0] ?? null;
        $company_departments = CompanyDepartment::getActiveCompanyDepartment();
        $data['company_departments'] = $company_departments;
        $data['access_level'] = AccessLevel::where('home_id', $homeId)->where('is_deleted', 0)->get();
        return view('frontEnd/roster/staff/staff_onboarding/staff_onboarding', $data);
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
        $departmentFilter = trim($req->department ?? '');
        $access_levelFilter = trim($req->access_level ?? '');

        $staffQuery = $this->staffService->allStaff($homeId);
        $staffQuery->select('id', 'home_id', 'name', 'email', 'status', 'department', 'access_level', 'dbs_expiry_date');
        $today = Carbon::today();
        $nextWeek = Carbon::today()->addDays(7);

        $dbs_expired = (clone $staffQuery)
            ->whereDate('dbs_expiry_date', '<', $today)
            ->count();

        // 🟡 Expiring within 7 days
        $dbs_expiring_soon = (clone $staffQuery)
            ->whereBetween('dbs_expiry_date', [$today, $nextWeek])
            ->count();
        $totalStaffCount = (clone $staffQuery)->count();
        // total stages
        if (!empty($search)) {
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        if (!empty($access_levelFilter)) {
            $staffQuery->where(function ($q) use ($access_levelFilter) {
                $q->where('access_level', $access_levelFilter);
            });
        }
        if (!empty($departmentFilter)) {
            $staffQuery->where(function ($q) use ($departmentFilter) {
                $q->where('department', $departmentFilter);
            });
        }

        // paginate first
        $staff = $staffQuery->latest()->paginate(25);

        // transform + filter
        $totalStagesMap = [];
        $collection = $staff->getCollection()->map(function ($item) use (&$totalStagesMap) {
            // $isexists = $this->onboardingConfigService
            //     ->loadWorkFlow(['type' => 'staff', 'care_type' => $item->department, 'access_level' => $item->access_level])
            //     ->where('status', 1)
            //     ->exists();
            $filters = ['type' => 'staff', 'care_type' => $item->department, 'access_level' => $item->access_level];
            $workflow = $this->onboardingConfigService
                ->loadWorkFlow($filters)
                ->withCount([
                    'getstages as getstages_count' => function ($q) use ($item) {
                        $q->where('status', 1)
                            ->where('required_stage', 1)
                            ->where(function ($q1) use ($item) {
                                $q1->whereNull('selected_user_ids')
                                    ->orWhereJsonLength('selected_user_ids', 0)
                                    ->orWhereJsonContains('selected_user_ids', (string) $item->id);
                            });
                    },

                    'getstages as onboardingforms_count' => function ($q) use ($item) {
                        $q->where('status', 1)
                            ->where('required_stage', 1)
                            ->where(function ($q1) use ($item) {
                                $q1->whereNull('selected_user_ids')
                                    ->orWhereJsonLength('selected_user_ids', 0)
                                    ->orWhereJsonContains('selected_user_ids', (string) $item->id);
                            })
                            ->whereHas('onboardingforms', function ($q2) use ($item) {
                                $q2->where('user_id', $item->id);
                            });
                    }
                ])

                ->where('status', 1)
                ->first();

            $item->workflow = $workflow;

            $total = $workflow->getstages_count ?? 0;
            $completed = $workflow->onboardingforms_count ?? 0;
            $totalStagesMap[$item->id] = $total;
            $item->form_percentage = $total > 0
                ? round(($completed / $total) * 100, 0)
                : 0;

            $item->completed_forms = $completed;

            return $item;
        });

        // ✅ Apply status filter
        if ($statusFilter == 'approved') {
            $collection = $collection->filter(function ($item) {
                return $item->completed_forms > 0
                    && $item->completed_forms == $item->workflow->getstages_count;
            });
        }

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
        $staffIds = $this->staffService->allStaff($homeId)->pluck('id');
        // 🔴 Expired

        $forms = OnboardingConfigForm::whereIn('user_id', $staffIds)
            ->select('user_id', 'onboarding_config_stage_id')
            ->distinct()
            ->get()
            ->groupBy('user_id');

        $in_progress_count = 0;

        foreach ($staffIds as $userId) {

            $completed = isset($forms[$userId]) ? $forms[$userId]->count() : 0;

            // ✅ user-wise totalStages lo
            $totalStages = $totalStagesMap[$userId] ?? 0;

            if ($completed > 0 && $completed < $totalStages) {
                $in_progress_count++;
            }
        }
        $in_progress_count = $staff->getCollection()->filter(function ($item) {
            return $item->completed_forms > 0 &&
                $item->completed_forms < ($item->workflow->getstages_count ?? 0);
        })->count();
        // reset collection after filter
        $staff->setCollection($collection->values());
        $data_count  = [
            'total_staff' => $totalStaffCount,
            'fit_work' => 0,
            'fit_work_per' => "0%",
            'in_progress' => $in_progress_count,
            'dbs_expiring' => $dbs_expiring_soon,
            'dbs_expired' => $dbs_expired,
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
                'total' => $staff->total(),
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
            $staffQuery = User::select('id', 'name', 'status', 'department','home_id', 'access_level')->find($req->user_id);
            // $isexists = $this->onboardingConfigService
            //     ->loadWorkFlow(['type' => 'staff', 'care_type' => $staffQuery->department])
            //     ->where('status', 1)
            //     ->exists();
            $home_id = explode(',', $staffQuery->home_id)[0];
            $filters = [
                'type' => 'staff',
                'care_type' => $staffQuery->department,
                'access_level' => $staffQuery->access_level,
                'home_id'=>$home_id
            ];
            // $filters = ['type' => 'staff'];
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
        // echo "<pre>";print_r($req->all());die;
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
