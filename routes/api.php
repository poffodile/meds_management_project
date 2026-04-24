<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Android\AndroidApiController;
use App\Http\Controllers\Api\Schedule\ScheduleController;
use App\Http\Controllers\Api\EducationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('/user-login', 'App\Http\Controllers\Android\AndroidApiController@user_login');
Route::get('/get-leave-list', 'App\Http\Controllers\Android\AndroidApiController@get_leave_list');
Route::post('/add-user-leave', 'App\Http\Controllers\Android\AndroidApiController@add_user_leave');
Route::post('/get-user-leave', 'App\Http\Controllers\Android\AndroidApiController@get_user_leave');
Route::post('/add-login-activity', 'App\Http\Controllers\Android\AndroidApiController@add_login_activity');
Route::post('/check-out-activity', 'App\Http\Controllers\Android\AndroidApiController@check_out_activity');
Route::post('/check-out-activity/update-reason', 'App\Http\Controllers\Android\AndroidApiController@check_out_activity_update_reason');

Route::post('/get-user-activity', 'App\Http\Controllers\Android\AndroidApiController@get_user_activity');
// Route::post('/qrcode', 'Android\AndroidApiController@QRCode');
Route::post('/get-company-data', 'App\Http\Controllers\Android\AndroidApiController@get_company_data');
Route::post('/get-home-data', 'App\Http\Controllers\Android\AndroidApiController@get_home_data');
Route::get('/courses-list', 'App\Http\Controllers\Api\Staff\UserController@cources_list');

// Ram 19/08/2025
Route::post('get-homes', [AndroidApiController::class, 'get_homes']);

Route::group(['prefix' => '/service'], function () {
	Route::post('/login', 'App\Http\Controllers\Api\ServiceUser\UserController@login');
	Route::post('/contact-us', 'App\Http\Controllers\Api\ContactUsController@add_contact_us');
	Route::get('/personal-detail/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\UserController@personal_details');

	/*-------NoteController--------*/
	Route::get('/notes/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\NoteController@index');
	Route::post('/note/add', 'App\Http\Controllers\Api\ServiceUser\NoteController@add');
	Route::post('/note/edit', 'App\Http\Controllers\Api\ServiceUser\NoteController@edit');

	/*-------UserController---------*/
	Route::get('/targets/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\TargetController@index');
	/*DailyTask Controller*/
	Route::get('/daily-tasks/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@daily_tasks');
	Route::get('/living-skill/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@living_skill');
	Route::get('/education-records/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@education_records');
	Route::get('/earning/daily-tasks/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@earning_daily_tasks');
	Route::get('/earning/living-skill/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@earning_living_skill');
	Route::get('/earning/education-records/{su_id}', 'App\Http\Controllers\Api\ServiceUser\DailyTasksController@earning_education_records');

	/*-------EarningSchemeController-------*/
	//view earning categories
	Route::get('/earning-scheme-categories/{su_id}', 'App\Http\Controllers\Api\ServiceUser\EarningSchemeController@categories');
	//view incentives of a earning category
	Route::get('/earning-scheme-details/{earning_scheme_id}', 'App\Http\Controllers\Api\ServiceUser\EarningSchemeController@earning_incentives');
	//earning history of su
	Route::get('/earning-schemes/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\EarningSchemeController@earning_history');
	//booked incentives of a user
	Route::get('/earning/user-incentives/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\EarningSchemeController@user_incentives');

	Route::post('/earning/incentive/add', 'App\Http\Controllers\Api\ServiceUser\EarningSchemeController@add_to_calendar');

	/*-------MoodController-------*/
	Route::get('/moods/{su_id}', 'App\Http\Controllers\Api\ServiceUser\MoodController@moods');
	Route::post('/mood/add', 'App\Http\Controllers\Api\ServiceUser\MoodController@add_mood');
	Route::get('/mood/user/{id}', 'App\Http\Controllers\Api\ServiceUser\MoodController@listing_mood');

	/*-------MoneyController-------*/
	Route::get('/money/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\MoneyController@index');
	Route::post('money/request/add', 'App\Http\Controllers\Api\ServiceUser\MoneyController@add_money_request');
	Route::get('money/history/{su_id}', 'App\Http\Controllers\Api\ServiceUser\MoneyController@history');
	Route::get('money/request/view/{money_request_id}', 'App\Http\Controllers\Api\ServiceUser\MoneyController@request_detail');

	/*-------LabelController-------*/
	Route::match(['get', 'post'], 'labels/{service_user_id}', 'App\Http\Controllers\Api\LabelController@label');

	/*-------CareTeamController-------*/
	Route::match(['get', 'post'], '/care-team/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareTeamController@care_team');
	Route::match(['get', 'post'], '/care-team/view/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareTeamController@care_team_view');

	/*-----AppointmentController-------*/
	Route::get('/appointments/{su_id}', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@appointments');
	Route::get('/appointment/forms/{su_id}', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@appointment_forms_list');
	Route::get('/appointment/form/{form_id}', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@view_add_appointment_form');
	Route::post('/appointment/save', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@save_appointment');
	Route::get('/appointment/view/{su_calendar_event_id}', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@view_appointment_detail');
	Route::get('/staff/members/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\AppointmentController@staff_members');


	/*-------CareCenterController-------*/
	Route::get('care-center/staff-list/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareCenterController@staff_list');
	// Route::post('/care-center/in-danger','Api\ServiceUser\CareCenterController@add_danger');
	Route::get('/care-center/social-worker/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareCenterController@social_worker_list');
	Route::get('/care-center/external-service-list/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareCenterController@external_service_list');

	Route::post('/care-center/in-danger', 'App\Http\Controllers\Api\ServiceUser\CareCenter\DangerController@add');

	//Request callback
	Route::post('/care-center/request-callback', 'App\Http\Controllers\Api\ServiceUser\CareCenter\RequestCallBackController@add');

	//Need assistance
	Route::get('/care-center/need-assistance/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareCenter\NeedAssistanceController@index');
	Route::post('/care-center/need-assistance/send-message', 'App\Http\Controllers\Api\ServiceUser\CareCenter\NeedAssistanceController@send_message');

	//Message office
	Route::get('/care-center/message-office/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CareCenter\MessageOfficeController@index');
	Route::post('/care-center/message-office/send-message', 'App\Http\Controllers\Api\ServiceUser\CareCenter\MessageOfficeController@send_message');

	//Make complaint
	Route::post('/care-center/complaint/add', 'App\Http\Controllers\Api\ServiceUser\CareCenter\ComplaintController@add');

	//yp Calendar 
	Route::get('/calendar/{service_user_id}', 'App\Http\Controllers\Api\ServiceUser\CalendarController@index');
	Route::match(['get', 'post'], '/calendar/event/view', 'App\Http\Controllers\Api\ServiceUser\CalendarEventController@index');
	Route::match(['get', 'post'], '/calendar/change-event-req/{su_id}', 'App\Http\Controllers\Api\ServiceUser\ChangeEventRequestController@index');
	Route::match(['get', 'post'], '/calendar/change-event-req/add/{su_id}', 'App\Http\Controllers\Api\ServiceUser\ChangeEventRequestController@add');

	//yp Location tracking
	Route::match(['get', 'post'], '/location/add', 'App\Http\Controllers\Api\ServiceUser\LocationController@add');
	Route::match(['get', 'post'], '/location/add-missing', 'App\Http\Controllers\Api\ServiceUser\LocationController@add_missing_locations');
	Route::match(['get', 'post'], '/location/alert/{su_location_history_id}', 'App\Http\Controllers\Api\ServiceUser\LocationController@notify_location_alert_node');
	Route::match(['get', 'post'], '/logout/location', 'App\Http\Controllers\Api\ServiceUser\LocationController@lat_long_update_logout_tym');
	//save device id
	Route::post('/device/add', 'App\Http\Controllers\Api\DeviceController@add_su_device');
});


Route::post('/forget-password', 'App\Http\Controllers\Api\Staff\UserController@forget_password');

Route::group(['prefix' => '/staff'], function () {


	Route::get('/service-users/{staff_id}', 'App\Http\Controllers\Api\Staff\ServiceUserController@listing_service_user');
	Route::get('/daily-tasks/{staff_id}', 'App\Http\Controllers\Api\Staff\TaskAllocationController@index');
	Route::get('/money-requests/{staff_id}', 'App\Http\Controllers\Api\Staff\MoneyRequestController@index');
	Route::post('/money-request/update', 'App\Http\Controllers\Api\Staff\MoneyRequestController@update_request');
	Route::get('/trainings/{staff_id}', 'App\Http\Controllers\Api\Staff\TrainingController@index');
	Route::post('/mood/add-suggestion', 'App\Http\Controllers\Api\Staff\MoodController@give_suggestion');
	Route::post('/message-office/add-message', 'App\Http\Controllers\Api\Staff\MessageOfficeController@add_message');
	Route::get('/care-center/in-danger/{staff_id}', 'App\Http\Controllers\Api\Staff\CareCenterController@in_danger_requests');
	Route::get('/care-center/request-callbacks/{staff_id}', 'App\Http\Controllers\Api\Staff\CareCenterController@request_callbacks');
	Route::post('/device/add', 'App\Http\Controllers\Api\DeviceController@add_user_device');
	Route::post('/behavior/add', 'App\Http\Controllers\Api\Staff\BehaviorController@addBehavior');

	// Staff Register
	Route::post('/add-staff', 'App\Http\Controllers\Api\Staff\UserController@addStaffUser');
	Route::post('/add-staff-qualification', 'App\Http\Controllers\Api\Staff\UserController@addStaffQualification');
	Route::post('/set-password', 'App\Http\Controllers\Api\Staff\UserController@setPassword');

	// staff document
	Route::post('/document-list', 'App\Http\Controllers\Api\Staff\StaffDocumentController@document_list');
	Route::post('/save-document', 'App\Http\Controllers\Api\Staff\StaffDocumentController@saveDouments');
	Route::post('/personal-details', 'App\Http\Controllers\Api\Staff\ServiceUserController@staffDetails');
	Route::post('/wishUser', 'App\Http\Controllers\Api\Staff\StaffDocumentController@wishUser');

	// Staff Notes
	Route::get('/staff-notes/{staff_id}', 'App\Http\Controllers\Api\Staff\StaffNoteController@staff_notes');
	Route::post('/staff-note/add', 'App\Http\Controllers\Api\Staff\StaffNoteController@add_staff_note');
	Route::delete('/staff-notes/{note_id}', 'App\Http\Controllers\Api\Staff\StaffNoteController@deleteNote');

	Route::post('/dashboard', 'App\Http\Controllers\Api\Staff\UserController@dashboard');
	Route::post('/payroll', 'App\Http\Controllers\Api\Staff\UserController@payroll');
	Route::get('/staff-payslip/{staff_id}/{week_key}', 'App\Http\Controllers\Api\Staff\UserController@staffPayslip');
	Route::get('/staff-payslip-download/{staff_id}/{week_key}', 'App\Http\Controllers\Api\Staff\UserController@downloadPayslip')->name('api.staff.payslip.download');
	Route::post('/schedule-shifts', [ScheduleController::class, 'schedule_shifts']);

	Route::post('/schedule-shifts-details', [ScheduleController::class, 'schedule_shifts_details']);
	Route::post('/schedule-shifts/update-status', [ScheduleController::class, 'schedule_shifts_update_status']);
	Route::post('/unassigned-shifts', [ScheduleController::class, 'get_unassigned_shifts']);
	Route::post('/assign-shift', [ScheduleController::class, 'assignShift']);

	// Education Module - Staff Side
	Route::match(['get', 'post'], '/education/assigned-children/{staff_id?}', [EducationApiController::class, 'getAssignedChildren']);
	Route::match(['get', 'post'], '/education/profile/{service_user_id?}', [EducationApiController::class, 'getEducationProfile']);
	Route::post('/education/task/add', [EducationApiController::class, 'addTask']);
	Route::post('/education/attendance/add', [EducationApiController::class, 'addAttendance']);
	Route::post('/education/note/add', [EducationApiController::class, 'addNote']);
	Route::post('/education/resource/add', [EducationApiController::class, 'addResource']);
});

Route::group(['prefix' => '/child'], function() {
    // Education Module - Child Side
    Route::match(['get', 'post'], '/education/tasks/{service_user_id?}', [EducationApiController::class, 'getChildTasks']);
    Route::post('/education/task/complete/{task_id}', [EducationApiController::class, 'completeTask']);
});
