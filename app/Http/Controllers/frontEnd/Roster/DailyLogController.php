<?php

namespace App\Http\Controllers\frontEnd\Roster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth,DB,Session;
use Illuminate\Support\Facades\Validator;
use App\ServiceUser;
use App\User;
use App\Models\RosterDailyLog;
use App\Models\DailyLogCategory;
use App\Models\DailyLogSubCategory;
use App\Models\AccompanyingStaff;

class DailyLogController extends Controller
{
    public function index(){
        $home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id = $ex_home_ids[0];

        $data['client'] = ServiceUser::select('id','home_id','earning_scheme_label_id','name','user_name','phone_no','date_of_birth','child_type','room_type','current_location','street','care_needs','status','is_deleted')
        ->where(['home_id'=>$home_id,'is_deleted'=>0])->get();
       
        $data['categorys'] = DailyLogCategory::select('id','home_id','category','status')
        ->with(['subCategorys'=>function($q){
            $q->select('id','home_id','daily_cat_id','sub_cat','icon','color','status');
        }])
        ->where('status',1)->get();
        $data['accompanying_staff'] = User::where(['is_deleted'=>0,'status'=>1])->get();
        // echo "<pre>";print_r($data['followUpCount']);die;

        return view('frontEnd.roster.daily_log.daily_log',$data);
    }
    public function save_daily_log(Request $request){
        // echo "<pre>";print_r($request->all());die;
         $validator = Validator::make($request->all(), [
            'date'=>'required',
            'entry_type_id'=>'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        try{
            DB::beginTransaction();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id = $ex_home_ids[0];
            $requestData = $request->all();
            $requestData['home_id'] = $home_id;
            $requestData['user_id'] = Auth::user()->id;
            $requestData['available_for_overtime'] = $request->available_for_overtime ?? 0;
            $getId = RosterDailyLog::updateOrCreate(['id' => $requestData['id'] ?? null],$requestData);
            if($request->has('accompanyingstaff_id')){
                for($i=0;$i<count($request->accompanyingstaff_id);$i++){
                    $accompanying_staff = new AccompanyingStaff;
                    $accompanying_staff->roster_daily_log_id = $getId->id;
                    $accompanying_staff->staff_id = $request->accompanyingstaff_id[$i];
                    $accompanying_staff->save();
                }
            }
            DB::commit();
            Session::flash('success','Saved successfully done.');
            return response()->json([
                'success'  => true,
                'message' => "Saved successfully done.",
                'data'=>json_decode('{}')
            ]);

        }catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error saving SOS Alert: ' . $e->getMessage(),
            ];
        }
    }
    public function daily_log_delete(Request $request){
        $validator = Validator::make($request->all(), [
            'id'=>'required|exists:roster_daily_logs,id',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first()
            ];
        }
        $dailyLog = RosterDailyLog::find($request->id);
        $dailyLog->deleted_at = now();
        $dailyLog->save();
        Session::flash('success','Daily Log deleted successfully done.');
        return [
                'success' => true,
                'message' => 'Daily Log deleted successfully done.'
            ];
    }
    public function daily_log_loadData(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $date = $request->date ?? now()->toDateString();
        $home_ids = Auth::user()->home_id;
		$ex_home_ids = explode(',', $home_ids);
		$home_id = $ex_home_ids[0];

        $baseQuery = RosterDailyLog::with([
                'subCategorys.dailyLogCategory',
                'accompanyingStaffs:id,roster_daily_log_id,staff_id',
                'clients:id,name'
            ])
            ->where('home_id', $home_id)
            ->where('user_id', Auth::id())
            ->whereDate('date', $date)
            ->when($request->filled('search_dailyLog'), function ($q) use ($request) {
                $q->where('visitor_name', 'like', '%' . $request->search_dailyLog . '%');
            });
        $total= (clone $baseQuery)->count();

        $visitorsCount = (clone $baseQuery)
                    ->whereHas('subCategorys', fn($q)=>$q->where('daily_cat_id',1))
                    ->count();

        $outingsCount= (clone $baseQuery)
                    ->whereHas('subCategorys', fn($q)=>$q->where('daily_cat_id',2))
                    ->count();

        $followUpCount= (clone $baseQuery)
                ->where('available_for_overtime',1)
                ->count();

        $allData = (clone $baseQuery)
            ->orderByDesc('id')
            ->paginate(50);
        
        $visitorsData = (clone $baseQuery)
            ->whereHas('subCategorys', fn ($q) => $q->where('daily_cat_id', 1))
            ->orderByDesc('id')
            ->paginate(50);
        
        $outingsData = (clone $baseQuery)
            ->whereHas('subCategorys', fn ($q) => $q->where('daily_cat_id', 2))
            ->orderByDesc('id')
            ->paginate(50);
        
        $medicalData = (clone $baseQuery)
            ->whereHas('subCategorys', fn ($q) => $q->where('id', 3))
            ->orderByDesc('id')
            ->paginate(50);
        
        $familyData = (clone $baseQuery)
            ->whereHas('subCategorys', fn ($q) => $q->where('id', 2))
            ->orderByDesc('id')
            ->paginate(50);
        // return $allData;
        $allHtmlData = $this->htmlDataPrepare($allData,$request->tab);
        // return $allHtmlData;
        $visitorsHtmlData = $this->htmlDataPrepare($visitorsData,$request->tab);
        $outingsHtmlData = $this->htmlDataPrepare($outingsData,$request->tab);
        $medicalHtmlData = $this->htmlDataPrepare($medicalData,$request->tab);
        $falmilyHtmlData = $this->htmlDataPrepare($familyData,$request->tab);
       return response()->json([
            'success'=>true,
            'allHtmlData'=>$allHtmlData,
            'visitorsHtmlData'=>$visitorsHtmlData,
            'outingsHtmlData'=>$outingsHtmlData,
            'medicalHtmlData'=>$medicalHtmlData,
            'falmilyHtmlData'=>$falmilyHtmlData,
            'total'=>$total,
            'visitorsCount'=>$visitorsCount,
            'outingsCount'=>$outingsCount,
            'followUpCount'=>$followUpCount,
            'pagination' => [
                'all_pagination' => [
                    'next_page_url' => $allData->nextPageUrl(),
                    'prev_page_url' => $allData->previousPageUrl(),
                ],
                'visitors_pagination' => [
                    'next_page_url' => $visitorsData->nextPageUrl(),
                    'prev_page_url' => $visitorsData->previousPageUrl(),
                ],
                'outings_pagination' => [
                    'next_page_url' => $outingsData->nextPageUrl(),
                    'prev_page_url' => $outingsData->previousPageUrl(),
                ],
                'medical_pagination' => [
                    'next_page_url' => $medicalData->nextPageUrl(),
                    'prev_page_url' => $medicalData->previousPageUrl(),
                ],
                'family_pagination' => [
                    'next_page_url' => $familyData->nextPageUrl(),
                    'prev_page_url' => $familyData->previousPageUrl(),
                ],
            ],
        ]);
    }
    public function htmlDataPrepare($query,$tab){
        // DB::enableQueryLog();
        
        // $queries = DB::getQueryLog();
        // echo "<pre>";print_r($queries);
        $html_data = '';
        foreach ($query as $val) {

            $subCategory = $val->subCategorys;
            $color = ($subCategory && $subCategory->color) ? $subCategory->color : "#1d69e3";
            $bgColor = ($subCategory && $subCategory->background_color) ? $subCategory->background_color : "#dbeafe";
            $icon = ($subCategory && $subCategory->icon) ? $subCategory->icon : "fa fa-user";
            $subCat = ($subCategory && $subCategory->sub_cat) ? $subCategory->sub_cat : '';
            $is_outing = 0;
            $client_name = $val->clients->name ?? "";
            $arrival = "In";
            $departure = "Out";
            $cardColor = '';
            
            if ($subCategory && $subCategory->dailyLogCategory && $subCategory->dailyLogCategory->id == 2) {
                $is_outing = 1;
                $arrival = "Left";
                $departure = "Returned";
                $cardColor = 'style="background:#ecfeff ;border: 1px solid #a5f3fc;"';
            }
            $accStaffIds = $val->accompanyingStaffs->pluck('staff_id')->toArray();
            $date_timeDivClass = '';
            if (empty($val->arrival_time) && empty($val->departure_time)) {
                $date_timeDivClass = '';
            }else if($is_outing == 1 && empty($val->arrival_time)){
                $date_timeDivClass = 'bg-yellow-70';
            }else if($is_outing == 1 && empty($val->departure_time)){
                $date_timeDivClass = 'bg-yellow-70';
            }else if($is_outing == 0 && empty($val->arrival_time)){
                $date_timeDivClass = '';
            }else if($is_outing == 0 && empty($val->departure_time)){
                $date_timeDivClass = 'bg-green-70';
            }
            
            if($tab == 1){
                $html_data .= '
                <div class="stepTimelineContentBx">
                    <div class="entryCardbxTimeline">
                        <div class="step-timeline">
                            <div class="step-item">
                                <span class="step-dot" 
                                    style="color:' . $color . '; background:' . $bgColor . ';">
                                    ' . (!empty($val->arrival_time) ? date('H:i', strtotime($val->arrival_time)) : '') . '
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="entryCardbx">
                        <div class="planCard" '.$cardColor.'>
                            <div class="planTop">
                                <div class="planTitle">
                                    <span class="heartIcon blueLightclr" style="color:' . $color . '; background:' . $bgColor . ';"> <i class="' . $icon . '"></i></span>' . ($val->visitor_name ?? '') . '
                                    <span class="roundBtntag blueLightclr"
                                        style="color:' . $color . '; background:' . $bgColor . ';">
                                        ' . $subCat . '
                                    </span>

                                    <div class="inORoutTime '.$date_timeDivClass.'">';
                                    if (empty($val->arrival_time) && empty($val->departure_time)) {
                                        $html_data .= '<span><i class="bx bx-clock"></i></span>';
                                    }else{
                                        if (!empty($val->arrival_time) && !empty($val->departure_time)) {
                                            $html_data .= '
                                            <span class="gayClrIcon">'.$arrival.':</span>
                                            <span>' . date('H:i', strtotime($val->arrival_time)) . '</span>
                                            <span class="gayClrIcon"><i class="bx bx-arrow-right"></i></span>
                                            <span class="gayClrIcon">'.$departure.':</span>
                                            <span>' . date('H:i', strtotime($val->departure_time)) . '</span>';
                                        }else if($is_outing == 1 && empty($val->arrival_time)){
                                            $html_data .= '
                                                    <span><i class="bx bx-clock darkOrangeTextp"></i></span>
                                                    <span class="gayClrIcon">Returned:</span>
                                                    <span class="darkOrangeTextp">' . date('H:i', strtotime($val->departure_time)) . '</span>
                                                <div>
                                                    <span class="careBadg yellowDarkAni onSiteHov">On Site</span>
                                                </div>';
                                        }else if($is_outing == 1 && empty($val->departure_time)){
                                            $html_data .= '
                                                        <span><i class="bx bx-clock darkOrangeTextp"></i></span>
                                                        <span class="gayClrIcon">Left:</span>
                                                        <span class="darkOrangeTextp">' . date('H:i', strtotime($val->arrival_time)) . '</span>
                                                    <div>
                                                        <span class="careBadg yellowDarkAni onSiteHov">Out</span>
                                                    </div>';
                                        }else if($is_outing == 0 && empty($val->arrival_time)){
                                            $html_data .= '<span class="gayClrIcon">'.$departure.':</span>
                                            <span>' . date('H:i', strtotime($val->departure_time)) . '</span>';
                                        }else if($is_outing == 0 && empty($val->departure_time)){
                                            $html_data .= '<div class="d-flex gap-3 align-items-center">
                                                <div>
                                                    <span><i class="bx bx-clock darkGreenTextp"></i></span>
                                                    <span class="gayClrIcon">In:</span>
                                                    <span class="darkGreenTextp">' . date('H:i', strtotime($val->departure_time)) . '</span>
                                                </div>
                                                <div>
                                                    <span class="careBadg redDarkGreenAni onSiteHov">On Site
                                                    </span>
                                                </div>
                                            </div>';
                                        }
                                    }
                                    $html_data .= '
                                    </div>
                                </div>

                                <div class="planActions">
                                    <button type="button" class="editRosterDailyLog"
                                        data-id="' . $val->id . '"
                                        data-date="' . $val->date . '"
                                        data-visitor_name="' . $val->visitor_name . '"
                                        data-entry_type_id="' . $val->entry_type_id . '"
                                        data-org_company="' . $val->org_company . '"
                                        data-purpose_visit="' . $val->purpose_visit . '"
                                        data-client_id="' . $val->client_id . '"
                                        data-arrival_time="' . $val->arrival_time . '"
                                        data-departure_time="' . $val->departure_time . '"
                                        data-notes="' . $val->notes . '"
                                        data-available_for_overtime="' . $val->available_for_overtime . '"
                                        data-follow_details="' . $val->follow_details . '"
                                        data-destination="' . $val->destination . '"
                                        data-transport_id="' . $val->transport_id . '"
                                        data-risk_assessment="' . $val->risk_assessment . '"
                                        data-outing_summary="' . $val->outing_summary . '"
                                        data-is_outing="' . $is_outing . '"
                                        data-accompanying_staffs=\'' . json_encode($accStaffIds) . '\'>
                                        <i class="bx bx-pencil"></i>
                                    </button>

                                    <button class="danger delete_rosterDailyLog" 
                                        type="button" data-id="' . $val->id . '">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="AddFirstDetailsEntry">
                                <div class="planFooter">
                                    <span>' . ($val->org_company ?? '') . '</span>
                                </div>
                                <div class="planFooter mb-3">
                                    <span>' . ($val->purpose_visit ?? '') . '</span>
                                </div>';
                                if($is_outing == 1){
                                $html_data .= '<div class=" mb-3">
                                    <div class="d-flex align-items-center gap-3">';
                                        if($val->destination){
                                            $html_data .= '<div>
                                                <p class="textGray fs13 mb-0"><i class="bx bx-location fs16 me-1 cyanText"></i>' . $val->destination . '</p>
                                            </div>';
                                        }if($val->transport_id){
                                        $html_data .= '<div>
                                            <p class="textGray fs13 mb-0"><i class="bx bx-car fs16  me-1 cyanText"></i>walking</p>

                                        </div>';
                                        }
                                        if($val->risk_assessment == 1){
                                            $html_data .= '<div>
                                                <span class="careBadg greenbadges"> <i class="bx bx-check fs16"></i> Risk Assessed</span>
                                            </div>';
                                        }
                                    $html_data .= '</div>
                                </div>
                                <div class="planFooter mb-3">
                                    <span class="italicFont">"Outing Outcome"</span>
                                </div>';
                                }
                                $html_data .= '<div class="planFooter">
                                    <span>' . ($val->notes ?? '') . '</span>
                                </div>';

                                if ($val->available_for_overtime == 1) {
                                    $html_data .= '
                                    <div class="planFooter">
                                        <span class="redalrttext">
                                            <i class="bx bx-alert-circle"></i>
                                            Follow-up: ' . ($val->follow_details ?? 'Required') . '
                                        </span>
                                    </div>';
                                }

                            $html_data .= '
                            </div>
                        </div>
                    </div>
                </div>';
            }else{
                $html_data .= '
                            <div class="p-4 rtcozCardInDe rounded8 bgWhite mb-3" '.$cardColor.'>
                                <div class="d-flex justify-content-between">
                                    <div class="d-flex gap-4 w100">
                                        <div class="bgIconStaffT rounded50" style="color:' . $color . '; background:' . $bgColor . ';">
                                            <i class="' . $icon . ' f20"></i>
                                        </div>
                                        <div class="flex1">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="h5Head">';
                                                    if ($is_outing == 0) {
                                                        $html_data .= ($val->visitor_name ?? '');
                                                    } else {
                                                        $html_data .= $client_name;
                                                    }
                                                $html_data .= '</h5>

                                                <div class="d-flex gap-3">
                                                    <div>
                                                        <span class="careBadg" style="color:' . $color . '; background:' . $bgColor . ';">
                                                            ' . $subCat . '
                                                        </span>
                                                    </div>

                                                    <div class="planActions">
                                                        <button type="button" class="editRosterDailyLog ms-0"
                                                            data-id="' . $val->id . '"
                                                            data-date="' . $val->date . '"
                                                            data-visitor_name="' . $val->visitor_name . '"
                                                            data-entry_type_id="' . $val->entry_type_id . '"
                                                            data-org_company="' . $val->org_company . '"
                                                            data-purpose_visit="' . $val->purpose_visit . '"
                                                            data-client_id="' . $val->client_id . '"
                                                            data-arrival_time="' . $val->arrival_time . '"
                                                            data-departure_time="' . $val->departure_time . '"
                                                            data-notes="' . $val->notes . '"
                                                            data-available_for_overtime="' . $val->available_for_overtime . '"
                                                            data-follow_details="' . $val->follow_details . '"
                                                            data-destination="' . $val->destination . '"
                                                            data-transport_id="' . $val->transport_id . '"
                                                            data-risk_assessment="' . $val->risk_assessment . '"
                                                            data-outing_summary="' . $val->outing_summary . '"
                                                            data-is_outing="' . $is_outing . '"
                                                            data-accompanying_staffs=\'' . json_encode($accStaffIds) . '\'>
                                                            <i class="bx bx-pencil"></i>
                                                        </button>
                                                    </div>

                                                    <div class="planActions">
                                                        <button class="danger delete_rosterDailyLog ms-0" type="button" data-id="' . $val->id . '">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>';
                                            if ($is_outing == 0) {
                                                $html_data .= '
                                                <p class="textGray fs13">' . ($val->org_company ?? '') . '</p>';
                                            }

                            $html_data .= '<div class="d-flex gap-3 mb-3 align-items-center">
                                                <div class="inORoutTime '.$date_timeDivClass.'">';
                                                    if (empty($val->arrival_time) && empty($val->departure_time)) {
                                                        $html_data .= '<span><i class="bx bx-clock"></i></span>';
                                                    }else{
                                                        if (!empty($val->arrival_time) && !empty($val->departure_time)) {
                                                            $html_data .= '
                                                            <span class="gayClrIcon">'.$arrival.':</span>
                                                            <span>' . date('H:i', strtotime($val->arrival_time)) . '</span>
                                                            <span class="gayClrIcon"><i class="bx bx-arrow-right"></i></span>
                                                            <span class="gayClrIcon">'.$departure.':</span>
                                                            <span>' . date('H:i', strtotime($val->departure_time)) . '</span>';
                                                        }else if($is_outing == 1 && empty($val->arrival_time)){
                                                            $html_data .= '
                                                                    <span><i class="bx bx-clock darkOrangeTextp"></i></span>
                                                                    <span class="gayClrIcon">Returned:</span>
                                                                    <span class="darkOrangeTextp">' . date('H:i', strtotime($val->departure_time)) . '</span>
                                                                <div>
                                                                    <span class="careBadg yellowDarkAni onSiteHov">On Site</span>
                                                                </div>';
                                                        }else if($is_outing == 1 && empty($val->departure_time)){
                                                            $html_data .= '
                                                                        <span><i class="bx bx-clock darkOrangeTextp"></i></span>
                                                                        <span class="gayClrIcon">Left:</span>
                                                                        <span class="darkOrangeTextp">' . date('H:i', strtotime($val->arrival_time)) . '</span>
                                                                    <div>
                                                                        <span class="careBadg yellowDarkAni onSiteHov">Out</span>
                                                                    </div>';
                                                        }else if($is_outing == 0 && empty($val->arrival_time)){
                                                            $html_data .= '<span class="gayClrIcon">'.$departure.':</span>
                                                            <span>' . date('H:i', strtotime($val->departure_time)) . '</span>';
                                                        }else if($is_outing == 0 && empty($val->departure_time)){
                                                            $html_data .= '<div class="d-flex gap-3 align-items-center">
                                                                <div>
                                                                    <span><i class="bx bx-clock darkGreenTextp"></i></span>
                                                                    <span class="gayClrIcon">In:</span>
                                                                    <span class="darkGreenTextp">' . date('H:i', strtotime($val->departure_time)) . '</span>
                                                                </div>
                                                                <div>
                                                                    <span class="careBadg redDarkGreenAni onSiteHov">On Site
                                                                    </span>
                                                                </div>
                                                            </div>';
                                                        }
                                                    }

                            $html_data .= '</div>';

                                                if ($is_outing == 0 && $client_name) {
                                                    $html_data .= '<p class="textGray fs13 mb-0">
                                                        <i class="bx bx-user"></i>
                                                        ' . $client_name . '
                                                    </p>';
                                                }

                            $html_data .= '</div>
                                            <div>';
                                            if($val->purpose_visit){
                                                $html_data .= '<p class="fs13">
                                                    <span class="font700 fas13">Purpose :</span>
                                                    <span class="textGray">' . ($val->purpose_visit ?? '') . '</span>
                                                </p>';
                                            }
                                                $html_data .= '<p class="textGray fs13">' . ($val->notes ?? '') . '</p>';

                                                if ($val->available_for_overtime == 1) {
                                                    $html_data .= '
                                                    <div class="bg-orange-50 p-3 rounded8" style="width:90%;">
                                                        <p class="fs13 orangeIcon mb-0">
                                                            <i class="bx bx-alert-circle f18"></i>
                                                            <span class="font700 middleAlign">Follow-up required :</span>
                                                            <span class="middleAlign"> ' . $val->follow_details . '</span>
                                                        </p>
                                                    </div>';
                                                }

                            $html_data .= '
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>';
            }
        }
        return $html_data;
    }
}
