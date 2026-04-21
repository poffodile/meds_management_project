<?php

namespace App\Http\Controllers\frontEnd\Roster\Staff;

use App\Http\Controllers\Controller;
use App\Models\OnboardingConfigForm;
use App\Services\Staff\OnboardingConfigService;
use App\Services\Staff\StaffonBoardingService;
use App\Services\Staff\StaffService;
use App\User;
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
        return view('frontEnd/roster/staff/staff_onboarding/staff_onboarding');
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
        $filters = ['type' => 'staff'];
        $search = trim($req->search ?? '');
        $statusFilter = trim($req->status ?? '');

        $staffQuery = $this->staffService->allStaff($homeId);
        $staffQuery->select('id', 'home_id', 'name', 'email', 'status');
        $totalStaffCount = (clone $staffQuery)->count();
        if (!empty($search)) {
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // paginate first
        $staff = $staffQuery->latest()->paginate(15);

        // transform + filter
        $totalStages = 0;
        $collection = $staff->getCollection()->map(function ($item) use ($filters, &$totalStages) {

            $workflow = $this->onboardingConfigService
                ->loadWorkFlow($filters)
                ->withCount([
                    'getstages as getstages_count' => function ($q) {
                        $q->where('status', 1);
                    },
                    'getstages as onboardingforms_count' => function ($q) use ($item) {
                        $q->where('status', 1)
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
            $totalStages = $total;
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

        $forms = OnboardingConfigForm::whereIn('user_id', $staffIds)
            ->select('user_id', 'onboarding_config_stage_id')
            ->distinct()
            ->get()
            ->groupBy('user_id');

        $in_progress_count = 0;

        foreach ($staffIds as $userId) {
            $completed = isset($forms[$userId]) ? $forms[$userId]->count() : 0;
            // echo "$completed -- $totalStages\n";
            if ($completed > 0 && $completed < $totalStages) {
                $in_progress_count++;
            }
        }
        // reset collection after filter
        $staff->setCollection($collection->values());
        $data_count  = [
            'total_staff' => $totalStaffCount,
            'fit_work' => 0,
            'fit_work_per' => "0%",
            'in_progress' => $in_progress_count,
            'dbs_expiring' => 0,
            'dbs_expired' => 0,
        ];
        return response()->json([
            'status' => true,
            'data_count' => $data_count,
            'data'   => $staff->items(),
            'next_page' => $staff->nextPageUrl(),
            'pagination' => [
                'total' => $staff->total(),
                'per_page' => $staff->perPage(),
                'current_page' => $staff->currentPage(),
                'total_pages' => $staff->lastPage(),
                'next_page' => $staff->nextPageUrl()
            ]
        ]);
    }

    function loadUserDetails(Request $req)
    {
        try {
            $filters = ['type' => 'staff'];
            $staffQuery = User::select('id', 'name')->find($req->user_id);
            $workflowData =  $this->onboardingConfigService->loadWorkFlow($filters)->where('status', 1)
                ->withCount([
                    'getstages as getstages_count' => function ($q) {
                        $q->where('status', 1);
                    },
                    'getstages as onboardingforms_count' => function ($q) use ($req) {
                        $q->where('status', 1)
                            ->whereHas('onboardingforms', function ($q2) use ($req) {
                                $q2->where('user_id', $req->user_id);
                            });
                    }
                ])
                ->first();
            $stages=[];
            if ($workflowData) {
                $total = $workflowData->getstages_count ?? 0;
                $completed = $workflowData->onboardingforms_count ?? 0;
                $workflowData->form_percentage = $total > 0
                    ? round(($completed / $total) * 100, 0)
                    : 0;
                $stages =  $this->onboardingConfigService->loadWorkflowStages(['workflow_id' => $workflowData->id])
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
