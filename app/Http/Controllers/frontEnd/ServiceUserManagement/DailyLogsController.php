<?php

namespace App\Http\Controllers\frontEnd\ServiceUserManagement;

use App\Http\Controllers\frontEnd\ServiceUserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\LogBook, App\ServiceUser, App\ServiceUserLogBook, App\LogBookComment, App\CategoryFrontEnd, App\DynamicFormBuilder, App\User, App\DynamicForm;
use Illuminate\Support\Arr;

class DailyLogsController extends ServiceUserManagementController
{
    public function index(Request $request)
    {
        if (isset($_GET['key'])) {
            $service_user_id = $_GET['key'];
        } else {
            $service_user_id = "";
        }

        $data = $request->input();
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id = $ex_home_ids[0];
        // $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = Carbon::today();

        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();

        //staff_member
        $staff_members = User::where('is_deleted', '0')
            ->where('home_id', $home_id)
            ->get();
        //$service_user_id ="";
        if ($service_user_id != '') {
            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')
                // ->where('su_log_book.user_id',$user_id)
                ->where('su_log_book.service_user_id', $service_user_id)->get()->toArray();

            //  echo "<pre>"; print_r($su_logs); die;
            if ($request->filter == '1') {


                if (!empty($data)) {
                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        // ->where('log_book.logType', 1)
                        ->whereIn('log_book.id', $su_logs)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->where('log_book.home_id', $home_id)
                        ->orderBy('date', 'desc');
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }

                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as &$key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            // $log_book_records = DB::table('log_book')
            //     ->select('log_book.*', 'user.name as staff_name')
            //     // ->where('log_book.logType', 1)
            //     ->whereIn('log_book.id', $su_logs)
            //     ->whereDate('log_book.date', '=', $today)
            //     ->join('user', 'log_book.user_id', '=', 'user.id')
            //     // ->join('category', 'log_book.category_id', '=', 'category.id')
            //     ->where('log_book.home_id',$home_id)
            //     ->orderBy('date', 'desc')->get();


            $today_date = Carbon::now()->format('Y-m-d');
            $oneMonthAgo = Carbon::now()->subMonth()->format('Y-m-d');

            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name')
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->whereIn('log_book.id', $su_logs)
                ->where('log_book.home_id', $home_id)
                ->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        $q->where('log_book.logType', 1)
                            ->whereDate('log_book.date', '=', $today);
                    })
                        ->orWhere(function ($q) use ($today) {
                            $q->where('log_book.logType', 2)
                                ->whereDate('log_book.start_date', '<=', $today)
                                ->whereDate('log_book.end_date', '>=', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                            $q->where('log_book.logType', 3)
                                ->whereDate('log_book.start_date', '<=', $today)
                                ->whereDate('log_book.end_date', '>=', $today);
                        });
                })
                // ->orderBy('log_book.dynamic_form_id') // Sort for grouping
                // ->orderByRaw("FIELD(log_book.logType, 1, 2, 3)") // Prioritize: daily > weekly > monthly
                ->orderBy('log_book.created_at', 'desc')
                ->get()
                ->unique('dynamic_form_id') // ✅ Only one log per dynamic_form_id
                ->values();

            //  echo "<pre>"; print_r($log_book_records); die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();


            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        } else {
            $su_home_id = ServiceUser::value('home_id');
            $service_user_name = ServiceUser::value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')->get()->toArray();
            // echo"<pre>"; print_r($su_logs); die;

            if ($request->filter == '1') {
                if (!empty($data)) {
                    //sourabh staff member and Child filter
                    if ($request->service_user == '' && $request->staff_member == '') {
                        $service_userss = ServiceUser::select('id')
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->get()->toArray();
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->whereIn('su_log_book.service_user_id', $service_userss)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member == '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    } else if ($request->service_user == '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            ->where('su_log_book.user_id', $request->staff_member)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')->where('su_log_book.user_id', $request->staff_member)->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    }

                    $log_book_records = DB::table('log_book')
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->join('dynamic_form', 'log_book.dynamic_form_id', '=', 'dynamic_form.id')
                        ->join('service_user', 'service_user.id', '=', 'dynamic_form.service_user_id')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color', 'service_user.name as child_name')
                        ->where('log_book.home_id', $home_id)
                        ->whereIn('log_book.id', $su_logss)
                        ->orderBy('date', 'desc');

                    // Staff Member
                    // if (isset($request->staff_member) && $request->staff_member != 'null') {
                    //     $log_book_records = $log_book_records->where('log_book.user_id', $request->staff_member);
                    // }

                    //    // Staff Member
                    // if (isset($request->service_user) && $request->service_user != 'null') {
                    //     $log_book_records = $log_book_records->where('dynamic_form.service_user_id', $request->service_user);
                    // }

                    // ✅ Log Type Filter
                    // log type filter
                    if ($request->has('log_type')) {
                        if ($request->log_type != 'all') {
                            // Apply specific log type filter
                            $log_book_records = $log_book_records->where('log_book.logType', $request->log_type);
                        } else {
                            // "all" means get all log types (do nothing special, just skip filtering)
                            $log_book_records = $log_book_records->whereIn('log_book.logType', ["1", "2", "3"]);
                            // or fetch dynamically from DB if log types may increase
                            // $logTypes = LogBook::distinct()->pluck('logType');
                            // $log_book_records = $log_book_records->whereIn('log_book.logType', $logTypes);
                        }
                    }

                    // Category filter
                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    // Date range filter
                    if (
                        !empty($request->start_date) && !empty($request->end_date)
                        && $request->start_date !== 'null' && $request->end_date !== 'null'
                    ) {
                        $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                        $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                        $log_book_records = $log_book_records->whereBetween('log_book.date', [$startDate, $endDate]);
                    }

                    // dd($log_book_records->get());


                    //keyword filter
                    if (isset($request->keyword) && $request->keyword != 'null') {
                        $log_book_records = $log_book_records->where('log_book.title', 'like', '%' . $request->keyword . '%');
                    }

                    $log_book_records = $log_book_records->get();

                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as $key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }
                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            // $log_book_records = DB::table('log_book')
            //     ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
            //     //->select('log_book.*', 'user.name as staff_name')
            //     // ->where('log_book.logType', 1)
            //     ->whereIn('log_book.id', $su_logs)
            //     ->whereDate('log_book.date', '=', $today)
            //     ->join('user', 'log_book.user_id', '=', 'user.id')
            //     ->join('category', 'log_book.category_id', '=', 'category.id')
            //     ->where('log_book.home_id', $home_id)
            //     ->orderBy('date', 'desc')->get();

            $startOfWeek = $today->copy()->startOfWeek();  // Monday
            $endOfWeek = $today->copy()->endOfWeek();      // Sunday
            $startOfMonth = $today->copy()->startOfMonth();
            $endOfMonth = $today->copy()->endOfMonth();

            $log_book_records = DB::table('log_book')
                ->select(
                    'log_book.dynamic_form_id',
                    DB::raw('MIN(log_book.created_at) as created_at'),
                    DB::raw('MIN(log_book.id) as id'),
                    DB::raw('MIN(log_book.date) as date'),
                    DB::raw('MIN(log_book.title) as title'),
                    DB::raw('MIN(log_book.image_name) as image_name'),
                    DB::raw('MIN(user.name) as staff_name'),
                    DB::raw('MIN(dynamic_form.service_user_id) as service_user_id'),
                    DB::raw('MIN(service_user.name) as child_name'),
                    DB::raw('MIN(log_book.is_late) as is_late'),
                    DB::raw('MIN(log_book.logType) as logType'),
                    DB::raw('MIN(category.name) as category_name'),
                    DB::raw('MIN(log_book.details) as details'),
                    DB::raw('MIN(category.color) as category_color'),
                    DB::raw('MIN(category.icon) as category_icon')
                )
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->join('dynamic_form', 'log_book.dynamic_form_id', '=', 'dynamic_form.id')
                ->join('service_user', 'service_user.id', '=', 'dynamic_form.service_user_id')
                ->whereIn('log_book.id', $su_logs)
                ->where('log_book.home_id', $home_id)
                ->where(function ($query) use ($today) {
                    $query->where(function ($q) use ($today) {
                        $q->where('log_book.logType', 1)
                            ->whereDate('log_book.date', '=', $today);
                    })
                        ->orWhere(function ($q) use ($today) {
                            $q->where('log_book.logType', 2)
                                ->whereDate('log_book.start_date', '<=', $today)
                                ->whereDate('log_book.end_date', '>=', $today);
                        })
                        ->orWhere(function ($q) use ($today) {
                            $q->where('log_book.logType', 3)
                                ->whereDate('log_book.start_date', '<=', $today)
                                ->whereDate('log_book.end_date', '>=', $today);
                        });
                })
                ->groupBy('log_book.dynamic_form_id', 'log_book.date')
                ->orderBy('log_book.date', 'desc')
                ->get();

            // echo "<pre>"; print_r($log_book_records);die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();

            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                //$key['comments']=$comments->count();
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));

                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                        // $key['late_time_text']=date('H:i', strtotime($key['created_at']));
                        //$key['late_date_text']=date('d-m-Y', strtotime($key['created_at']));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        }

        $dynamic_forms = DynamicFormBuilder::getFormList();

        return view('frontEnd.serviceUserManagement.daily_log', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'su_logs', 'categorys', 'service_users', 'staff_members', 'dynamic_forms'));
    }

    public function index2(Request $request)
    {
        //echo "string";
        //die();
        if (isset($_GET['key'])) {
            $service_user_id = $_GET['key'];
        } else {
            $service_user_id = "";
        }

        $data = $request->input();
        $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        //staff_member
        $staff_members = User::where('is_deleted', '0')
            ->where('home_id', Auth::user()->home_id)
            ->get();
        //$service_user_id ="";
        if ($service_user_id != '') {
            //print_r($data);
            // die();
            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')
                // ->where('su_log_book.user_id',$user_id)
                ->where('su_log_book.service_user_id', $service_user_id)->get()->toArray();

            //  echo "<pre>"; print_r($su_logs); die;
            if ($request->filter == '1') {


                if (!empty($data)) {
                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 1)
                        ->whereIn('log_book.id', $su_logs)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }

                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as &$key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 1)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                // ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();

            //  echo "<pre>"; print_r($log_book_records); die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        } else {
            $su_home_id = ServiceUser::value('home_id');
            $service_user_name = ServiceUser::value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')->get()->toArray();
            //echo"<pre>";
            //print_r($su_logs);
            //die;

            if ($request->filter == '1') {
                if (!empty($data)) {
                    //sourabh staff member and Child filter
                    if ($request->service_user == '' && $request->staff_member == '') {
                        $service_userss = ServiceUser::select('id')
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->get()->toArray();
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->whereIn('su_log_book.service_user_id', $service_userss)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member == '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    } else if ($request->service_user == '' && $request->staff_member != '') {

                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            ->where('su_log_book.user_id', $request->staff_member)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')->where('su_log_book.user_id', $request->staff_member)->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    }


                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 1)
                        ->whereIn('log_book.id', $su_logss)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    //print_r($log_book_records);
                    //die;
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    //sourabh
                    if (isset($request->keyword) && $request->keyword != 'null') {
                        $log_book_records = $log_book_records->where('log_book.details', 'like', '%' . $request->keyword . '%');
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as $key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                //->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 1)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();
            //print_r($log_book_records);
            //die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                //$key['comments']=$comments->count();
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                        // $key['late_time_text']=date('H:i', strtotime($key['created_at']));
                        //$key['late_date_text']=date('d-m-Y', strtotime($key['created_at']));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        }



        $dynamic_forms = DynamicFormBuilder::getFormList();

        return view('frontEnd.serviceUserManagement.daily_log2', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'su_logs', 'categorys', 'service_users', 'staff_members', 'dynamic_forms'));
    }

    public function index3(Request $request)
    {
        //echo "string";
        //die();
        if (isset($_GET['key'])) {
            $service_user_id = $_GET['key'];
        } else {
            $service_user_id = "";
        }

        $data = $request->input();
        $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        //staff_member
        $staff_members  =   User::where('is_deleted', '0')
            ->where('home_id', Auth::user()->home_id)
            ->get();
        //$service_user_id ="";
        if ($service_user_id != '') {
            //print_r($data);
            // die();
            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')
                // ->where('su_log_book.user_id',$user_id)
                ->where('su_log_book.service_user_id', $service_user_id)->get()->toArray();

            //  echo "<pre>"; print_r($su_logs); die;
            if ($request->filter == '1') {


                if (!empty($data)) {
                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 1)
                        ->whereIn('log_book.id', $su_logs)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }

                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as &$key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 1)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                // ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();

            //  echo "<pre>"; print_r($log_book_records); die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        } else {
            $su_home_id = ServiceUser::value('home_id');
            $service_user_name = ServiceUser::value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')->get()->toArray();
            //echo"<pre>";
            //print_r($su_logs);
            //die;

            if ($request->filter == '1') {
                if (!empty($data)) {
                    //sourabh staff member and Child filter
                    if ($request->service_user == '' && $request->staff_member == '') {
                        $service_userss = ServiceUser::select('id')
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->get()->toArray();
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->whereIn('su_log_book.service_user_id', $service_userss)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member == '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    } else if ($request->service_user == '' && $request->staff_member != '') {

                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            ->where('su_log_book.user_id', $request->staff_member)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')->where('su_log_book.user_id', $request->staff_member)->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    }


                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 1)
                        ->whereIn('log_book.id', $su_logss)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    //print_r($log_book_records);
                    //die;
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    //sourabh
                    if (isset($request->keyword) && $request->keyword != 'null') {
                        $log_book_records = $log_book_records->where('log_book.details', 'like', '%' . $request->keyword . '%');
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as $key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                //->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 1)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();
            //print_r($log_book_records);
            //die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                //$key['comments']=$comments->count();
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                        // $key['late_time_text']=date('H:i', strtotime($key['created_at']));
                        //$key['late_date_text']=date('d-m-Y', strtotime($key['created_at']));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        }



        $dynamic_forms = DynamicFormBuilder::getFormList();

        return view('frontEnd.serviceUserManagement.daily_log3', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'su_logs', 'categorys', 'service_users', 'staff_members', 'dynamic_forms'));
    }

    function daily_logs_filterData(Request $request)
    {
        return "1";
    }
    public function weekly_log(Request $request)
    {
        // dd($request);
        //echo "string";
        //die();
        if (isset($_GET['key'])) {
            $service_user_id = $_GET['key'];
        } else {
            $service_user_id = "";
        }


        $data = $request->input();
        $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        //staff_member
        $staff_members  =   User::where('is_deleted', '0')
            ->where('home_id', Auth::user()->home_id)
            ->get();
        //$service_user_id ="";
        if ($service_user_id != '') {
            //print_r($data);
            // die();
            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')
                // ->where('su_log_book.user_id',$user_id)
                ->where('su_log_book.service_user_id', $service_user_id)->get()->toArray();

            if ($request->filter == '1') {


                if (!empty($data)) {
                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 2)
                        ->whereIn('log_book.id', $su_logs)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }

                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as &$key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                ->where('log_book.logType', 2)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        } else {
            $su_home_id = ServiceUser::value('home_id');
            $service_user_name = ServiceUser::value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')->get()->toArray();
            //echo"<pre>";
            //print_r($su_logs);
            //die;

            if ($request->filter == '1') {
                if (!empty($data)) {
                    //sourabh staff member and Child filter
                    if ($request->service_user == '' && $request->staff_member == '') {
                        $service_userss = ServiceUser::select('id')
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->get()->toArray();
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->whereIn('su_log_book.service_user_id', $service_userss)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member == '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    } else if ($request->service_user == '' && $request->staff_member != '') {

                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            ->where('su_log_book.user_id', $request->staff_member)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')->where('su_log_book.user_id', $request->staff_member)->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    }


                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 2)
                        ->whereIn('log_book.id', $su_logss)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    //print_r($log_book_records);
                    //die;
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    //sourabh
                    if (isset($request->keyword) && $request->keyword != 'null') {
                        $log_book_records = $log_book_records->where('log_book.details', 'like', '%' . $request->keyword . '%');
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as $key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                //->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 2)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();
            //print_r($log_book_records);
            //die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                //$key['comments']=$comments->count();
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                        // $key['late_time_text']=date('H:i', strtotime($key['created_at']));
                        //$key['late_date_text']=date('d-m-Y', strtotime($key['created_at']));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        }




        return view('frontEnd.serviceUserManagement.weekly_log', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'su_logs', 'categorys', 'service_users', 'staff_members'));
    }
    public function monthly_log(Request $request)
    {
        //echo "string";
        //die();
        if (isset($_GET['key'])) {
            $service_user_id = $_GET['key'];
        } else {
            $service_user_id = "";
        }

        $data = $request->input();
        $home_id = Auth::user()->home_id;
        $user_id = Auth::user()->id;
        $today = date('Y-m-d 00:0:00');
        $service_users = ServiceUser::select('id', 'name')
            ->where('home_id', $home_id)
            ->where('is_deleted', '0')
            ->get();
        //staff_member
        $staff_members  =   User::where('is_deleted', '0')
            ->where('home_id', Auth::user()->home_id)
            ->get();
        //$service_user_id ="";
        if ($service_user_id != '') {
            //print_r($data);
            // die();
            $su_home_id = ServiceUser::where('id', $service_user_id)->value('home_id');
            $service_user_name = ServiceUser::where('id', $service_user_id)->value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')
                // ->where('su_log_book.user_id',$user_id)
                ->where('su_log_book.service_user_id', $service_user_id)->get()->toArray();

            if ($request->filter == '1') {


                if (!empty($data)) {
                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 3)
                        ->whereIn('log_book.id', $su_logs)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }

                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as &$key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                ->where('log_book.logType', 3)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        } else {
            $su_home_id = ServiceUser::value('home_id');
            $service_user_name = ServiceUser::value('name');
            // if($su_home_id != $home_id){
            //     return redirect()->back()->with("error",UNAUTHORIZE_ERR);
            // }

            $su_logs = ServiceUserLogBook::select('su_log_book.log_book_id')->get()->toArray();
            //echo"<pre>";
            //print_r($su_logs);
            //die;

            if ($request->filter == '1') {
                if (!empty($data)) {
                    //sourabh staff member and Child filter
                    if ($request->service_user == '' && $request->staff_member == '') {
                        $service_userss = ServiceUser::select('id')
                            ->where('home_id', $home_id)
                            ->where('is_deleted', '0')
                            ->get()->toArray();
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->whereIn('su_log_book.service_user_id', $service_userss)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member == '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            // ->where('su_log_book.user_id',$user_id)
                            ->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    } else if ($request->service_user == '' && $request->staff_member != '') {

                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')
                            ->where('su_log_book.user_id', $request->staff_member)->get()->toArray();
                    } else if ($request->service_user != '' && $request->staff_member != '') {
                        $su_logss = ServiceUserLogBook::select('su_log_book.log_book_id')->where('su_log_book.user_id', $request->staff_member)->where('su_log_book.service_user_id', $request->service_user)->get()->toArray();
                    }


                    $log_book_records = DB::table('log_book')
                        ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                        ->where('log_book.logType', 3)
                        ->whereIn('log_book.id', $su_logss)
                        ->join('user', 'log_book.user_id', '=', 'user.id')
                        ->join('category', 'log_book.category_id', '=', 'category.id')
                        ->orderBy('date', 'desc');
                    //print_r($log_book_records);
                    //die;
                    // $log_book_records = LogBook::select('log_book.*')
                    //                         ->orderBy('date','desc');

                    // Log::info("Logs.");
                    // Log::info($log_book_records);

                    if (isset($request->category_id) && $request->category_id != 'NaN') {
                        $log_book_records = $log_book_records->where('log_book.category_id', $request->category_id);
                        // Log::info("Category Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    if (isset($request->start_date) && $request->start_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '>=', $request->start_date);
                        // Log::info("Start Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    if (isset($request->end_date) && $request->end_date != 'null') {
                        $log_book_records = $log_book_records->whereDate('log_book.date', '<=', $request->end_date);
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }
                    //sourabh
                    if (isset($request->keyword) && $request->keyword != 'null') {
                        $log_book_records = $log_book_records->where('log_book.details', 'like', '%' . $request->keyword . '%');
                        // Log::info("End Date Logs.");
                        // Log::info($log_book_records->get()->toArray());
                    }


                    // $log_book_records = $log_book_records->get()->toArray();
                    $log_book_records = $log_book_records->get();
                    $log_book_records = collect($log_book_records)->map(function ($x) {
                        return (array) $x;
                    })->toArray();

                    foreach ($log_book_records as $key) {
                        $key['date'] = date("d-m-Y H:i", strtotime($key['date']));
                        $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                        $key = Arr::add($key, 'comments', $comments->count());
                        if ($key['is_late']) {
                            $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                            $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                            if ($given_date_without_time == $created_at_without_time) {
                                $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                                $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                            }
                        }
                    }

                    $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();

                    return compact('log_book_records', 'categorys');
                }
            }

            Log::info($su_logs);
            $today = date('Y-m-d');
            $log_book_records = DB::table('log_book')
                ->select('log_book.*', 'user.name as staff_name', 'category.color as category_color')
                //->select('log_book.*', 'user.name as staff_name')
                ->where('log_book.logType', 3)
                ->whereIn('log_book.id', $su_logs)
                ->whereDate('log_book.date', '=', $today)
                ->join('user', 'log_book.user_id', '=', 'user.id')
                ->join('category', 'log_book.category_id', '=', 'category.id')
                ->orderBy('date', 'desc')->get();
            //print_r($log_book_records);
            //die;

            $log_book_records = collect($log_book_records)->map(function ($x) {
                return (array) $x;
            })->toArray();
            // $log_book_records = LogBook::select('log_book.*')
            //                             ->whereIn('log_book.id',$su_logs)
            //                             ->whereDate('log_book.date', '=', $today)
            //                             ->orderBy('date','desc')->get()->toArray();

            foreach ($log_book_records as &$key) {
                $key['created_at'] = date("d-m-Y H:i", strtotime($key['created_at']));
                $comments = LogBookComment::where('log_book_id', $key['id'])->get();
                $key = Arr::add($key, 'comments', $comments->count());
                //$key['comments']=$comments->count();
                if ($key['is_late']) {
                    $given_date_without_time    = date('Y-m-d', strtotime($key['date']));
                    $created_at_without_time    = date('Y-m-d', strtotime($key['created_at']));
                    if ($given_date_without_time == $created_at_without_time) {
                        $key = Arr::add($key, 'late_time_text', date('H:i', strtotime($key['created_at'])));
                        $key = Arr::add($key, 'late_date_text', date('d-m-Y', strtotime($key['created_at'])));
                        // $key['late_time_text']=date('H:i', strtotime($key['created_at']));
                        //$key['late_date_text']=date('d-m-Y', strtotime($key['created_at']));
                    }
                }
            }

            $categorys = CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get()->toArray();
            /**
             * Removing Attendance Category
             */
            foreach ($categorys as $k => $val) {
                if ($val['name'] == "Attendance") {
                    unset($categorys[$k]);
                }
            }
        }

        return view('frontEnd.serviceUserManagement.monthly_log', compact('user_id', 'service_user_id', 'service_user_name', 'home_id', 'su_home_id', 'log_book_records', 'su_logs', 'categorys', 'service_users', 'staff_members'));
    }


    public function view_log_form_data($dynamic_form_id = null)
    {
        $result = DynamicForm::showFormLogWithValue($dynamic_form_id, false);
        return $result;
    }

    public function getDynamicFormDailyLog($id)
    {

        $data['log_book_records'] = DB::table('log_book')
            ->where('log_book.id', $id)
            ->first();

        $data['dynamicForm'] = DynamicForm::showFormLogWithValue($data['log_book_records']->dynamic_form_id, true);
        $data['pattern'] = DynamicFormBuilder::where('id', $data['dynamicForm']['form_builder_id'])->value('pattern');
        $data['pattern_data'] = DynamicForm::where('id', $data['log_book_records']->dynamic_form_id)->value('pattern_data');


        return response()->json($data);
    }
}
