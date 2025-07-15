<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontEnd\salesFinance\LeadController as FrontendLeadController;
use App\Http\Controllers\frontEnd\salesFinance\QuoteController as FrontendQuoteController;
use App\Http\Controllers\frontEnd\salesFinance\CouncilTaxController;
use App\Http\Controllers\frontEnd\salesFinance\item\ProductCategoryController as FrontendProductCategoryController;
use App\Http\Controllers\frontEnd\salesFinance\CrmSectionController;
use App\Http\Controllers\frontEnd\salesFinance\SupplierController;
use App\Http\Controllers\frontEnd\salesFinance\DayBook\SalesController;
use App\Http\Controllers\frontEnd\salesFinance\DayBook\PurchaseController;
use App\Http\Controllers\frontEnd\salesFinance\GeneralSectionController;
use App\Http\Controllers\frontEnd\salesFinance\CustomerController;
use App\Http\Controllers\frontEnd\salesFinance\InvoiceController;
use App\Http\Controllers\frontEnd\salesFinance\Purchase_orderController;
use App\Http\Controllers\frontEnd\salesFinance\item\CataloguesController;
use App\Http\Controllers\frontEnd\salesFinance\item\ProductController;
use App\Http\Controllers\frontEnd\salesFinance\item\ProductGroupController;
use App\Http\Controllers\frontEnd\salesFinance\ExpenseController;
use App\Http\Controllers\frontEnd\salesFinance\JobController;
use App\Http\Controllers\frontEnd\salesFinance\CreditNotesController;
use App\Http\Controllers\frontEnd\PettyCashController;
use App\Http\Controllers\frontEnd\salesFinance\asset\AssetController;
use App\Http\Controllers\frontEnd\salesFinance\PreInvoiceController;
use App\Http\Controllers\Rota\StaffController;
use App\Http\Controllers\Rota\AnnualLeaveController;
use App\Http\Controllers\frontEnd\salesFinance\leave_tracker\LeaveTrackerController;

// Backend Controllers
use App\Http\Controllers\backEnd\superAdmin\HomeController;
use App\Http\Controllers\backEnd\salesfinance\LeadController as BackendLeadController;
use App\Http\Controllers\backEnd\CustomerController as BackendCustomerController;
use App\Http\Controllers\backEnd\salesfinance\GeneralController;
use App\Http\Controllers\backEnd\ManagersController;
use App\Http\Controllers\backEnd\salesfinance\ExpenseControllerAdmin;
use App\Http\Controllers\backEnd\salesfinance\InvoiceController as BackendInvoiceController;
use App\Http\Controllers\backEnd\salesfinance\Purchase_orderControllerAdmin;
use App\Http\Controllers\backEnd\salesfinance\CreditNotesControllerAdmin;
use App\Http\Controllers\backEnd\salesfinance\DayBook\PurchaseBackendController;
use App\Http\Controllers\backEnd\salesfinance\DayBook\SalesBackendController;
use App\Http\Controllers\backEnd\salesfinance\CouncilBackendController;
use App\Http\Controllers\backEnd\salesfinance\PettyCashBackendController;
use App\Http\Controllers\backEnd\rota\StaffWorkerController;
use App\Http\Controllers\backEnd\salesfinance\asset\AssetBackendController;
use App\Http\Controllers\backEnd\generalAdmin\HomeCostingController;
use App\Http\Controllers\backEnd\generalAdmin\DepartmentBackendController;
use App\Http\Controllers\backEnd\systemManage\PlanBuilderAdminController;
use App\Http\Controllers\backEnd\salesfinance\TimeSheetBackendController;


Route::get('clear', function () {
	Artisan::call('cache:clear');
	return "Cleared!";
});

//  QR code for company
Route::post('/qrcode', 'App\Http\Controllers\Android\AndroidApiController@QRCode');

Route::match(['get', 'post'], '/change-design-layout/{design_layout_id}',  'App\Http\Controllers\frontEnd\DashboardController@change_layout');
/*-------Api Routes-------*/
Route::group(['prefix' => 'api/service'], function () {
	Route::post('/contact-us', 'App\Http\Controllers\Api\ContactUsController@add_contact_us');
	Route::post('/login', 'App\Http\Controllers\Api\ServiceUser\UserController@login');
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
Route::group(['prefix' => 'api/staff'], function () {
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
});
/*Change Password*/
Route::post('api/change-password', 'App\Http\Controllers\Api\ServiceUser\UserController@change_password');
Route::post('api/forgot-password', 'App\Http\Controllers\Api\ServiceUser\UserController@forgot_password');
Route::get('/reset-password/{user_name}/{security_code}', 'App\Http\Controllers\Api\ServiceUser\UserController@show_forget_password_form');
Route::post('/reset-password/save', 'App\Http\Controllers\Api\ServiceUser\UserController@set_forget_password');
Route::post('api/remove-device', 'App\Http\Controllers\Api\DeviceController@remove_device');
/*------Api Routes End here-------*/

// Route::match(['get','post'], '/login', 'App\Http\Controllers\frontEnd\UserController@login');
Route::match(['get', 'post'], '/login', 'App\Http\Controllers\frontEnd\UserController@login')->middleware('PreventBack');

Route::get('/logout', 'App\Http\Controllers\frontEnd\UserController@logout');
Route::post('/forgot-password', 'App\Http\Controllers\frontEnd\ForgotPasswordController@send_forgot_pass_link_mail');
Route::match(['get', 'post'], '/check-email-exists', 'App\Http\Controllers\frontEnd\ForgotPasswordController@check_email_exists');

Route::get('/fb_close', 'App\Http\Controllers\Controller@fb_close');

//Only Use for Agent
Route::match(['get', 'post'], 'agent/welcome', 'App\Http\Controllers\frontEnd\AgentController@welcome_page');

Route::match(['get', 'post'], '/delete/calendar/event/{event_id}', 'App\Http\Controllers\frontEnd\SystemManagement\CalendarController@del_calender_event');
Route::match(['get', 'post'], '/select/social/work/send/mail/{srvc_usr_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@social_worker');
Route::match(['get', 'post'], '/send/mail/social/worker', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@send_mail_social_work');

// code given by ethan start
Route::get('/switch_home', 'App\Http\Controllers\frontEnd\UserController@switch_home');
Route::match(['get', 'post'], '/switch_home_submit', 'App\Http\Controllers\frontEnd\UserController@switch_home_submit');
// code given by ethan End


Route::group(['middleware' => ['checkUserAuth', 'lock']], function () {

	// Rota Management
	Route::get('/rota-dashboard', 'App\Http\Controllers\Rota\RotaController@index');
	Route::get('/rota-management', 'App\Http\Controllers\Rota\RotaController@rota_management_dashboard');
	Route::get('/rota', 'App\Http\Controllers\Rota\RotaController@create');
	Route::post('/add-rota-data', 'App\Http\Controllers\Rota\RotaController@store');
	Route::get('/rota-planner', 'App\Http\Controllers\Rota\RotaController@rota_calender_view');

	Route::post('/add-shift-data', 'App\Http\Controllers\Rota\RotaController@add_shift_data');
	Route::post('/get-all-users', 'App\Http\Controllers\Rota\RotaController@get_all_users');
	Route::post('/assign_rota_users', 'App\Http\Controllers\Rota\RotaController@assign_rota_users');
	Route::post('/update_rota_name', 'App\Http\Controllers\Rota\RotaController@update_rota_name');
	Route::post('/publish_rota_employee', 'App\Http\Controllers\Rota\RotaController@publish_rota_employee');
	Route::post('/unpublish_rota_employee', 'App\Http\Controllers\Rota\RotaController@unpublish_rota_employee');

	Route::get('/calendar', 'App\Http\Controllers\Rota\RotaController@calender_view');
	Route::get('/absence/type={id}', 'App\Http\Controllers\Rota\RotaController@annual_leave_view');
	Route::post('/get-all-users-search', 'App\Http\Controllers\Rota\RotaController@get_all_users_search');
	Route::get('/get-all-users-edit', 'App\Http\Controllers\Rota\RotaController@get_all_users_edit');
	Route::post('/delete_rota_employee', 'App\Http\Controllers\Rota\RotaController@delete_rota_employee');
	Route::get('/edit_rota/{id}', 'App\Http\Controllers\Rota\RotaController@edit_rota');
	Route::post('/publish_unpublish_rota', 'App\Http\Controllers\Rota\RotaController@publish_unpublish_rota');

	Route::post('/add-leave', 'App\Http\Controllers\Rota\RotaController@add_leave');
	Route::post('/date_validation_for_user', 'App\Http\Controllers\Rota\RotaController@date_validation_for_user');


	Route::get('/pending-request', 'App\Http\Controllers\Rota\RotaController@leave_pending');
	Route::post('/pending-request-data', 'App\Http\Controllers\Rota\RotaController@pending_request_data');
	Route::get('/get_all_leave', 'App\Http\Controllers\Rota\RotaController@get_all_leave');
	Route::get('/employee', 'App\Http\Controllers\Rota\RotaController@employee_view');
	Route::post('/get_rota_employee', 'App\Http\Controllers\Rota\RotaController@get_rota_employee');
	Route::post('/get_all_shift', 'App\Http\Controllers\Rota\RotaController@get_all_shift');
	Route::post('/edit_shift_data_get', 'App\Http\Controllers\Rota\RotaController@edit_shift_data_get');
	Route::post('/update-shift-data', 'App\Http\Controllers\Rota\RotaController@update_shift_data');
	Route::post('/approve_leave', 'App\Http\Controllers\Rota\RotaController@approve_leave');
	Route::post('/get_leave_record_for_1_week', 'App\Http\Controllers\Rota\RotaController@get_leave_record_for_1_week');

	Route::post('/get_record_of_rota', 'App\Http\Controllers\Rota\RotaController@get_record_of_rota');
	Route::get('/get_all_rota_data', 'App\Http\Controllers\Rota\RotaController@get_all_rota_data');
	Route::post('/delete-shift-data', 'App\Http\Controllers\Rota\RotaController@delete_shift_data');
	Route::get('/recruitment', 'App\Http\Controllers\Rota\RotaController@recruitment_index');
	Route::get('/jobs', 'App\Http\Controllers\Rota\RotaController@jobs_index');
	Route::get('/create-jobs', 'App\Http\Controllers\Rota\RotaController@create_jobs');
	Route::get('/permissions', 'App\Http\Controllers\Rota\RotaController@permission_index');

	Route::post('/check_users_add_in_shift', 'App\Http\Controllers\Rota\RotaController@check_users_add_in_shift');

	Route::get('/payroll', 'App\Http\Controllers\Rota\RotaController@payroll');
	Route::get('/information_checker', 'App\Http\Controllers\Rota\RotaController@information_checker');
	Route::get('/overtime', 'App\Http\Controllers\Rota\RotaController@overtime');
	Route::get('/payroll_glossary', 'App\Http\Controllers\Rota\RotaController@payroll_glossary');

	Route::match(['get', 'post'], '/', 'App\Http\Controllers\frontEnd\DashboardController@dashboard')->name('dashboard');
	Route::post('/add-incident-report', 'App\Http\Controllers\frontEnd\DashboardController@add_incident_report');

	// Ram 14/06/2024 path for jobs create
	Route::controller(JobController::class)->group(function () {
		Route::get('/jobs_list', 'job_list');
		Route::post('/job_save_all', 'job_save_all');
		// Route::post('/status_change','status_change');
		Route::post('/delete_function', 'delete_function');
		Route::post('/edit_job', 'edit_job');
		Route::post('/search_value', 'search_value');
		Route::post('/save_get_ajax', 'save_get_ajax');
		Route::get('/job_type', 'job_type');
		Route::post('/job_type_save', 'job_type_save');
		Route::post('/job_type_edit', 'job_type_save');
		Route::post('/job_type_edit_form', 'job_type_edit_form');
		Route::post('/workflow_save_data', 'workflow_save_data');
		Route::post('/Workflow_notification_save', 'Workflow_notification_save');
		Route::post('/workflow_list_job', 'workflow_list_job');
		Route::post('/workflow_list_add', 'workflow_list_add');
		Route::get('/sales-finance/dashboard', 'index');
		Route::get('/planner_day', 'planner_day');
		Route::get('/jobs_create', 'jobs_create');
		Route::get('/job_edit', 'jobs_create');
		Route::post('/job_add_edit_save', 'job_add_edit_save');
		Route::post('/get_customer_details_front', 'get_customer_details_front');
		Route::post('/result_product_calculation', 'result_product_calculation');
		Route::post('/save_job_product', 'save_job_product');
		Route::post('/get_save_appointment', 'get_save_appointment');
		Route::post('/new_appointment_add_section', 'new_appointment_add_section');
		Route::get('/job_appointment_type_list', 'job_appointment_type_list');
		Route::post('/job_type_appointment_save', 'job_type_appointment_save');
		Route::post('/job_type_appointment_edit', 'job_type_appointment_save');
		Route::post('/job_appointment_type_edit_form', 'job_appointment_type_edit_form');
		Route::get('/appointment_rejection_cat_list', 'appointment_rejection_cat_list');
		Route::post('/appointment_rejection_cat_save', 'appointment_rejection_cat_save');
		Route::post('/appointment_rejection_cat_edit', 'appointment_rejection_cat_save');
		Route::post('/job_appointment_rejection_edit_form', 'job_appointment_rejection_edit_form');
		Route::get('/job_titles', 'job_titles');
		Route::post('/save_job_title', 'save_job_title');
		Route::post('/edit_job_title', 'save_job_title');
		Route::post('/job_title_edit_form', 'job_title_edit_form');
		Route::post('/save_region', 'save_region');
		Route::post('/jobassign_productsDelete', 'jobassign_productsDelete');
		Route::post('/project_save', 'project_save');
		Route::post('/contact_save', 'contact_save');
		Route::post('/site_save', 'site_save');
		Route::post('/product_save', 'product_save');
		Route::post('/supplier_result', 'supplier_result');
		Route::post('/save_product_category', 'save_product_category');
		Route::post('/save_tax_rate', 'save_tax_rate');
		Route::post('/product_modal_list', 'product_modal_list');
	});

	Route::get('/get-appointment-type', 'App\Http\Controllers\frontEnd\salesFinance\JobController@getActiveJobAppointment')->name('job.ajax.jobAppointment');

	// end here
	// CRM Section Controller
	Route::get('/complaint_type', [CrmSectionController::class, 'complaint_type']);

	// Frontend Action Controller
	Route::post('/bulk_delete', 'App\Http\Controllers\ActionController@bulk_delete');
	Route::post('/status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/task_type_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/region_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/tag_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/payment_type_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/attachment_type_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/job_title_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/customer_type_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/appointmentrejectioncat_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/jobAppointmentType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/jobType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/QuoteRejectType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/QuoteSource_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/QuoteType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/LeadNoteType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/LeadRejectType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/LeadTaskType_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/LeadStatus_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/Department_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/account_code_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/tax_rate_status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/sales-finance/assets/asset-category/status_change', 'App\Http\Controllers\ActionController@status_change');
	Route::post('/sales-finance/assets/asset-depreciation/status_change', 'App\Http\Controllers\ActionController@status_change');

	// Supplier Section
	Route::controller(SupplierController::class)->group(function () {
		Route::get('/suppliers', 'index');
		Route::get('/supplier_add', 'supplier_add');
		Route::get('/supplier_edit', 'supplier_add');
		Route::post('/supplier_save', 'supplier_save');
		Route::get('/supplier', 'supplier_list');
		Route::post('/supplier_attachment_save', 'supplier_attachment_save');
		Route::post('/getAllSupplierAttachment', 'getAllSupplierAttachment');
		Route::post('/supplier_attachment_image_delete', 'supplier_attachment_image_delete');
		Route::post('/get_supplier_details', 'get_supplier_details');
		Route::post('/search_email_list', 'search_email_list');
		Route::post('/getsupplier_purchaseList', 'getsupplier_purchaseList');
	});
	Route::controller(ExpenseController::class)->group(function () {
		Route::match(['get', 'post'], '/expenses', 'expenses');
		Route::post('/find_project', 'find_project');
		Route::post('/find_job', 'find_job');
		Route::post('/find_appointment', 'find_appointment');
		Route::post('/expense_save', 'expense_save');
		Route::post('/expense_image_delete', 'expense_image_delete');
		Route::get('/reject_expense', 'reject_expense');
		Route::post('/searchCustomerName', 'searchCustomerName');
		Route::post('/searchExpenses', 'searchExpenses');
	});

	// Forontend Customer Controller
	Route::controller(CustomerController::class)->group(function () {
		Route::post('save_crm_customer_call', 'save_crm_customer_call');
		Route::post('get_all_crm_customer_call', 'get_all_crm_customer_call');
		Route::post('save_crm_customer_email', 'save_crm_customer_email');
		Route::post('get_all_crm_customer_email', 'get_all_crm_customer_email');
		Route::post('visibility_change', 'visibility_change');
		Route::post('save_crm_customer_task', 'save_crm_customer_task');
		Route::post('get_customer_details', 'get_customer_details');
		Route::post('get_all_crm_customer_task', 'get_all_crm_customer_task');
		Route::post('save_crm_customer_notes', 'save_crm_customer_notes');
		Route::post('get_all_crm_customer_note', 'get_all_crm_customer_note');
		Route::post('save_crm_customer_complaints', 'save_crm_customer_complaints');
		Route::post('get_all_crm_customer_complaint', 'get_all_crm_customer_complaint');
		Route::post('get_all_crm_customer_contacts', 'get_all_crm_customer_contacts');
		Route::post('getAllCustomerList', 'getAllCustomerList');
		Route::post('getAllSupplierList', 'getAllSupplierList');
		Route::post('getAllUserList', 'getAllUserList');
		Route::post('GetCustomerWithContact', 'GetCustomerWithContact');
		Route::post('GetFullHistory', 'GetFullHistory');
		Route::post('getAllSite', 'getAllSite');
		Route::post('getAllLogin', 'getAllLogin');
		Route::post('customer_type_edit_form', 'customer_type_edit_form');
		Route::post('edit_customer_type', 'save_customer_type');
		Route::post('save_customer_type', 'save_customer_type');
		Route::get('/customer_type', 'customer_type');
		Route::get('/customers', 'active_customer');
		Route::post('/delete_login', 'delete_login');
		Route::post('/save_login', 'save_login');
		Route::post('/delete_site', 'delete_site');
		Route::post('/save_site', 'save_site');
		Route::post('/delete_contact', 'delete_contact');
		Route::post('/save_contact', 'save_contact');
		Route::post('/default_address', 'default_address');
		Route::get('/add_currency', 'add_currency');
		Route::post('/customer_add_edit_save', 'customer_add_edit_save');
		Route::get('/customer_add_edit', 'customer_add_edit');

		Route::prefix('customers')->group(function () {
			Route::post('/addCustomer', 'SaveCustomerData')->name('customer.ajax.SaveCustomerData');
			Route::get('/getCustomerList', 'getCustomerList')->name('customer.ajax.getCustomerList');
			Route::post('/getCustomerDetails', 'getCustomerDetails')->name('customer.ajax.getCustomerDetails');
			Route::post('/SaveCustomerContactData', 'SaveCustomerContactData')->name('customer.ajax.SaveCustomerContactData');
			Route::get('/getCustomerJobTitle', 'getCustomerJobTitle')->name('customer.ajax.getCustomerJobTitle');
			Route::post('/saveJobTitle', 'saveJobTitle')->name('customer.ajax.saveJobTitle');
			Route::post('/saveCustomerSiteAddress', 'saveCustomerSiteAddress')->name('customer.ajax.saveCustomerSiteAddress');
			Route::post('/getCustomerBillingAddress', 'getCustomerBillingAddress')->name('customer.ajax.getCustomerBillingAddress');
			Route::post('/getCustomerBillingAddressData', 'getCustomerBillingAddressData')->name('customer.ajax.getCustomerBillingAddressData');
			Route::post('/getCustomerSiteAddress', 'getCustomerSiteAddress')->name('customer.ajax.getCustomerSiteAddress');
			Route::post('/getCustomerSiteDetails', 'getCustomerSiteDetails')->name('customer.ajax.getCustomerSiteDetails');
		});
	});

	// Frontend Controller for setting in General section 
	Route::controller(GeneralSectionController::class)->group(function () {
		Route::get('/attachments_types', 'attachments_types');
		Route::post('/save_attachment_type', 'save_attachment_type');
		Route::post('/edit_attachment_type', 'save_attachment_type');
		Route::get('/Payment_type', 'Payment_type');
		Route::post('/save_payment_type', 'save_payment_type');
		Route::post('/edit_payment_type', 'save_payment_type');
		Route::get('/regions', 'regions');
		Route::get('/task_types', 'task_types');
		Route::post('/save_task_type', 'save_task_type');
		Route::post('/edit_task_type', 'save_task_type');
		Route::post('/save_task_type_data', 'save_task_type_data')->name('General.ajax.save_task_type_data');
		Route::get('/getTaskTypeList', 'getTaskTypeList')->name('General.ajax.getTaskTypeList');
		Route::get('/tags', 'tags');
		Route::post('/save_tag', 'save_tag')->name('General.ajax.saveQuoteTag');
		Route::post('/edit_tag', 'save_tag');
		Route::get('/getTags', 'getTags')->name('General.ajax.getTags');
	});

	// Frontend Invoice Controller
	Route::controller(InvoiceController::class)->group(function () {
		Route::get('/account_codes', 'account_codes');
		Route::post('/save_account_code', 'save_account_code')->name('invoice.ajax.saveAccountCode');
		Route::post('/edit_account_code', 'save_account_code')->name('invoice.ajax.editAccountCode');
		Route::get('/tax_rate', 'tax_rate');
		Route::post('/save_tax_rate', 'save_tax_rate');
		Route::post('/edit_tax_rate', 'save_tax_rate');
		Route::get('/getAccountCode', 'getAccountCode')->name('Invoice.ajax.getAccountCode');
		Route::get('/getActiveAccountCode', 'getActiveAccountCode')->name('Invoice.ajax.getActiveAccountCode');
		Route::get('/getTaxRate', 'getActiveTaxRate')->name('invoice.ajax.getActiveTaxRate');
		Route::post('/getTaxRateOnTaxId', 'getTaxRateOnTaxId')->name('invoice.ajax.getTaxRateOnTaxId');

		Route::prefix('invoices')->group(function () {
			Route::get('/dashborad', 'dashboard');
			Route::get('/add', 'create');
			Route::post('/invoice_save', 'invoice_save');
			Route::get('/invoice/Draft', 'invoice');
			Route::get('/invoice/Outstanding', 'invoice');
			Route::get('/invoice/Overdue', 'invoice');
			Route::get('/invoice/Paid', 'invoice');
			Route::get('/preview', 'preview');
			Route::get('/print', 'preview');
			Route::get('/edit', 'create');
			Route::post('/getInvoiceProductDetail', 'getInvoiceProductDetail');
			Route::post('/invoice_productsDelete', 'invoice_productsDelete');
			Route::post('/invoice_attachmentSave', 'invoice_attachmentSave');
			Route::post('/getInvoiceAllAttachmens', 'getInvoiceAllAttachmens');
			Route::post('/customer_visibleUpdate', 'customer_visibleUpdate');
			Route::post('/mobile_user_visibleUpdate', 'mobile_user_visibleUpdate');
			Route::post('/delete_invoice_attachment', 'delete_invoice_attachment');
			Route::post('/save_reminder', 'save_reminder');
			Route::post('/delete_invoice_reminder', 'delete_invoice_reminder');
			Route::post('/new_task_save', 'new_task_save');
			Route::post('/getAllInvoiceNewTaskList', 'getAllInvoiceNewTaskList');
			Route::post('/completeNewTaskUrl', 'completeNewTaskUrl');
		});
	});


	Route::controller(Purchase_orderController::class)->group(function () {
		Route::get('/departments', 'departments');
		Route::post('/save_department', 'save_department');
		Route::post('/edit_department', 'save_department');
		Route::get('/purchase_order', 'purchase_order');
		Route::get('/purchase_order/duplicate', 'purchase_order');
		Route::post('/purchase_order_save', 'purchase_order_save');
		Route::get('/purchase_order_edit', 'purchase_order');
		Route::post('/purchase_order_attachment_save', 'purchase_order_attachment_save');
		Route::post('/getAllAttachmens', 'getAllAttachmens');
		Route::post('/delete_po_attachment', 'delete_po_attachment');
		Route::post('/vat_tax_details', 'vat_tax_details');
		Route::post('/getPurchaesOrderProductDetail', 'getPurchaesOrderProductDetail');
		Route::post('/purchase_productsDelete', 'purchase_productsDelete');
		Route::post('/purchase_order_new_task_save', 'purchase_order_new_task_save');
		Route::post('/getAllNewTaskList', 'getAllNewTaskList');
		Route::get('/draft_purchase_order', 'draft_purchase_order');
		Route::get('/draft_purchase_order/AwaitingApprivalPurchaseOrders', 'draft_purchase_order');
		Route::get('/draft_purchase_order/Approved', 'draft_purchase_order');
		Route::get('/draft_purchase_order/Rejected', 'draft_purchase_order');
		Route::get('/draft_purchase_order/Actioned', 'draft_purchase_order');
		Route::get('/draft_purchase_order/Paid', 'draft_purchase_order');
		Route::post('/searchPurchaseOrders', 'searchPurchaseOrders');
		Route::post('/searchDepartment', 'searchDepartment');
		Route::post('/searchTag', 'searchTag');
		Route::post('/searchSupplier', 'searchSupplier');
		Route::post('/searchCreatedBy', 'searchCreatedBy');
		Route::post('/searchProject', 'searchProject');
		Route::post('/searchPurchase_qoute_ref', 'searchPurchase_qoute_ref');
		Route::post('/searchPurchase_job_ref', 'searchPurchase_job_ref');
		Route::post('/purchase_order_approve', 'purchase_order_approve');
		Route::post('/purchase_order_record_delivered', 'purchase_order_record_delivered');
		Route::post('/savePurchaseOrderRecordPayment', 'savePurchaseOrderRecordPayment');
		Route::post('/purchaseOrderInviceRecieve', 'purchaseOrderInviceRecieve');
		Route::post('/purchaseOrderreject', 'purchaseOrderreject');
		Route::post('/save_reminder', 'save_reminder');
		Route::post('/purchaseOrderEmailSave', 'purchaseOrderEmailSave');
		Route::get('/preview', 'preview');
		Route::get('/purchase-orders-search', 'purchase_orders_search');
		Route::get('/purchase-order-statements', 'purchase_order_statements');
		Route::post('/searchPurchaseOrdersStatements', 'searchPurchaseOrdersStatements');
		Route::get('/purchase-order-invoices', 'purchase_order_invoices');
		Route::post('/searchPurchaseOrdersInvoice', 'searchPurchaseOrdersInvoice');
		Route::post('/getAllPurchaseInvoices', 'getAllPurchaseInvoices');
		Route::post('/getAllPaymentPaids', 'getAllPaymentPaids');
		Route::post('/paymentPaidDelete', 'paymentPaidDelete');
		Route::get('/preview-purchase-orders', 'preview_multiple_purchaseOrders');
		Route::post('/purchase_order_approveMultiple', 'purchase_order_approveMultiple');
		Route::post('/searchPurchase_ref', 'searchPurchase_ref');
		Route::post('/saveBulkInvoiceModal', 'saveBulkInvoiceModal');
		Route::post('/saveBulkRecordPaymentModal', 'saveBulkRecordPaymentModal');
		Route::post('/searchPurchaseOrdersStatementsOutstanding', 'searchPurchaseOrdersStatementsOutstanding');
		Route::get('/finance', 'finance_dashboard');
	});

	// forntend petty cash
	Route::controller(PettyCashController::class)->group(function () {
		Route::prefix('petty-cash/')->group(function () {
			Route::get('dashboard', 'index');
			Route::get('expend-card', 'expend_card');
			Route::get('petty_cash', 'petty_cash');
			Route::get('child_register', 'child_register');
			Route::get('expend_card_add', 'expend_card_add');
			Route::get('petty-cash-add', 'petty_cash_add');
			Route::get('child-register-add', 'child_register_add');
			Route::post('saveExpend', 'saveExpend');
			Route::post('editExpend', 'saveExpend');
			Route::post('saveCash', 'saveCash');
			Route::post('editCash', 'saveCash');
			Route::post('cash_filter', 'cash_filter');
			Route::post('expand_card_filter', 'expand_card_filter');
			Route::get('getAllExpendCash', 'getAllExpendCash');
			Route::post('cash_delete', 'cash_delete');
			Route::post('expend_delete', 'expend_delete');
		});
	});

	// frontend Pre-Invoice
	Route::controller(PreInvoiceController::class)->group(function () {
		Route::get('service/invoice/{service_user_id}', 'index');
		Route::post('save-pre-invoice', 'preinvoice_save');
		Route::get('service/invoice/preview/{service_user_id}', 'preview');
		Route::post('service/invoice/edit_PreInvoice', 'edit_PreInvoice');
	});

	// Staff for frontend
	Route::controller(StaffController::class)->group(function () {
		Route::get('rota/staff', 'index');
		Route::post('rota/staff-add', 'store');
		Route::delete('rota/staff-delete/{id}', 'destroy');
	});
	// Annual leave frontend
	Route::controller(AnnualLeaveController::class)->group(function () {
		route::prefix('rota')->group(function () {
			Route::get('annual-leave', 'index');
			// Route::post('get-user-data', 'getUserData');
		});
	});
	// Leave Tracker frontend
	Route::controller(LeaveTrackerController::class)->group(function () {
		Route::prefix('finance')->group(function () {
			Route::get('leave-tracker', 'leave_tracker');
			Route::get('leave-tracker-add', 'leave_tracker_add');
		});
	});

	Route::controller(CreditNotesController::class)->group(function () {
		Route::get('credit_notes/Approved', 'credit_notes');
		Route::get('credit_notes/Paid', 'credit_notes');
		Route::get('credit_notes/Cancelled', 'credit_notes');
		Route::get('new_credit_notes', 'new_credit_notes');
		Route::post('credit_notes_save', 'credit_notes_save');
		Route::get('credit_note_edit', 'new_credit_notes');
		Route::post('searchCreditNotes', 'searchCreditNotes');
		Route::post('getCreditProduct', 'getCreditProduct');
		Route::post('cancelCreditNote', 'cancelCreditNote');
		Route::post('crediNoteEmailSave', 'crediNoteEmailSave');
		Route::get('credit_preview', 'credit_preview');
		Route::post('getAllSupplierPurchaseOrder', 'getAllSupplierPurchaseOrder');
		Route::post('crediNoteAllocateSave', 'crediNoteAllocateSave');
	});
	Route::controller(AssetController::class)->group(function () {
		Route::prefix('sales-finance/assets/')->group(function () {
			Route::get('asset-category', 'asset_category');
			Route::get('depreciation-type', 'depreciation_type');
			Route::get('asset-register', 'asset_register');
			Route::post('asset-register-search', 'asset_register_search');
			Route::get('asset-regiser-add', 'asset_regiser_add');
			Route::post('asset-regiser-save', 'asset_regiser_save');
			Route::post('asset-category-save', 'asset_category_save');
			Route::post('asset-category-edit', 'asset_category_save');
			Route::post('depreciation-type-save', 'depreciation_type_save');
			Route::post('depreciation-type-edit', 'depreciation_type_save');
			Route::get('asset-register-edit', 'asset_regiser_add');
			Route::post('asset-register-delete', 'asset_register_delete');
			Route::post('asset-category-delete', 'asset_category_delete');
		});
	});


	// frontend Council Tax
	Route::controller(CouncilTaxController::class)->group(function () {
		Route::prefix('finance')->group(function () {
			Route::get('/council-tax', 'index')->name('finance.council-tax');
			Route::post('/save-council-tax', 'saveCouncilTaxData')->name('finance.saveCouncilTaxData');
			Route::post('/edit-council-tax', 'saveCouncilTaxData');
			Route::delete('/delete-council-tax/{id}', 'destroy')->name('finance.deleteCouncilTax');
		});
	});

	Route::controller(SalesController::class)->group(function () {
		Route::prefix('sales')->group(function () {
			Route::get('/sales-day-book', 'index')->name('sales.salesDayBook');
			Route::get('/sales-day-book/add', 'create')->name('sales.salesDayBookCreate');
			Route::post('/save-sales-day-book', 'store');
			Route::post('/edit-sales-day-book', 'store');
			Route::post('/sales-day-book/delete/{id}', 'deleteSalesDayBook')->name('salesDayBook.delete');
			Route::get('/sales-day-book/edit/{id}', 'editSalesDayBook');
			Route::get('/get-sales-day-book/data', 'getSalesDayBook');
		});
	});

	Route::controller(PurchaseController::class)->group(function () {
		Route::prefix('purchase')->group(function () {
			Route::get('purchase-type', 'purchase_type')->name('purchase.purchaseExpenses');
			Route::post('save-purchase-expenses', 'save_purchase_expenses')->name('purchase.purchaseExpensesSave');
			Route::post('edit-purchase-expenses', 'save_purchase_expenses');
			Route::get('purchase-day-book-reclaim-per', 'purchase_day_book_reclaim_per')->name('purchase.purchaseDayBookReclaimPer');
			Route::post('/purchase-expenses/delete/{id}', 'deletePurchaseExpenses')->name('deletePurchaseExpenses.delete');
			Route::get('reclaimPercantage', 'reclaimPercantage')->name('purchase.reclaimPercantage');
			Route::get('/purchase-day-book', 'index')->name('purchase.purchaseDayBook');
			Route::get('/purchase-day-book/add', 'create')->name('purchase.purchaseDayBookCreate');
			Route::post('/save-purchase-day-book', 'store');
			Route::get('/purchase-daybook/data', 'getPurchaseDayBook');
			Route::post('/purchase-day-book/delete/{id}', 'deletePurchaseDayBook')->name('purchaseDayBook.delete');
			Route::get('/purchase-day-book/edit/{id}', 'editPurchaseDayBook');
			Route::get('/getSupplierData', 'getSupplierData')->name('purchase.getSupplierData');
			Route::get('/getPurchaseExpense', 'getPurchaseExpense')->name('purchase.getPurchaseExpense');
		});
	});

	Route::controller(FrontendLeadController::class)->group(function () {

		Route::prefix('lead')->group(function () {
			Route::get('/myLeads', 'index')->name('lead.myleads');
			Route::get('/authorization', 'index')->name('lead.authorization');
			Route::get('/rejected', 'index')->name('lead.rejected');
			Route::get('/actioned', 'index')->name('lead.actioned');
			Route::get('/lead_task_delete/{id}', 'lead_task_list_delete');
			Route::get('/task_mark_as_completed/{task}', 'task_mark_as_completed')->name('lead.task_mark_as_completed');
			// Lead Task Type
			Route::get('/lead_task_type', 'lead_task_type')->name('leads.lead_task_type');
			Route::post('/saveLeadTaskType', 'saveLeadTaskType')->name('lead.ajax.saveLeadTaskType');
			Route::post('/editLeadTaskType', 'saveLeadTaskType')->name('lead.ajax.editLeadTaskType');
			Route::get('/lead_task_type/delete/{id}', 'lead_task_type_delete');
			Route::get('/lead_mark_as_completed/{task}/{lead}', 'lead_mark_as_completed');
			Route::get('/getLeadTaskType', 'getLeadTaskTypeData')->name('lead.ajax.getLeadTaskType');
			Route::post('/getLeadTaskOnLeadId', 'getLeadTaskOnLeadId')->name('lead.ajax.getLeadTaskOnLeadId');

			// Lead Notes Type
			Route::get('/lead_notes_type', 'lead_notes_type')->name('lead.lead_notes_type');
			Route::post('/saveLeadNotesType', 'saveLeadNotesType')->name('lead.ajax.saveLeadNoteType');
			Route::post('/editLeadNoteType', 'saveLeadNotesType')->name('lead.ajax.editLeadNoteType');
			Route::get('/lead_note_type/delete/{id}', 'lead_note_type_delete');

			// Lead Sources
			Route::get('/lead_sources', 'lead_sources')->name('leads.lead_sources');
			Route::post('/saveLeadSource', 'saveLeadSource')->name('lead.ajax.saveLeadSource');
			Route::post('/editLeadSource', 'saveLeadSource')->name('lead.ajax.editLeadSource');

			// Lead Status
			Route::get('/lead_status', 'lead_status')->name('lead.lead_status');
			Route::post('/saveLeadStatus', 'saveLeadStatus')->name('lead.ajax.saveLeadStatus');
			Route::post('/editLeadStatus', 'saveLeadStatus')->name('lead.ajax.editLeadStatus');

			// CRM Section Types
			Route::get('/CRM_section_types', 'CRM_section_type')->name('lead.crm_section');
			Route::post('/saveCRMSectionType', 'saveCRMSectionType')->name('lead.ajax.saveCRMSectionType');
			Route::post('/editCRMSectionType', 'saveCRMSectionType')->name('lead.ajax.editCRMSectionType');
			Route::get('/crm_section_type/delete/{id}', 'crm_section_type_delete');
			Route::get('/get_CRM_section_types', 'get_CRM_section_types')->name('lead.ajax.getCRMTypeData');

			Route::post('/getCRMTaskDataWeek', 'getCRMTaskDataWeek')->name('lead.ajax.getCRMTaskDataWeek');
			Route::post('/getCRMTaskDataOverdue', 'getCRMTaskDataOverdue')->name('lead.ajax.getCRMTaskDataOverdue');
			Route::post('/getCRMTaskDataComplete', 'getCRMTaskDataComplete')->name('lead.ajax.getCRMTaskDataComplete');
			Route::post('/getCRMTaskDataRecurring', 'getCRMTaskDataRecurring')->name('lead.ajax.getCRMTaskDataRecurring');
			Route::get('/getUserList', 'getUserList')->name('lead.ajax.getUserList');
			Route::post('/getLeadDataWithRecurrence', 'getLeadDataWithRecurrence')->name('lead.ajax.getLeadDataWithRecurrence');
			Route::get('/get30DaysLead', 'get30DaysLead')->name('lead.ajax.get30DaysLead');
			Route::post('/saveLeadConvertQuote', 'saveLeadConvertQuote')->name('lead.ajax.saveLeadConvertQuote');

			// Countries List
			Route::get('/getCountriesList', 'getCountriesList')->name('ajax.getCountriesList');

			// Lead CRM 
			Route::post('/saveCRMLeadData', 'saveCRMLeadData')->name('lead.ajax.saveCRMLeadData');
			Route::post('/getCRMCallsData', 'getCRMCallsData')->name('lead.ajax.getCRMCallsData');
			Route::post('/saveCRMLeadEmails', 'saveCRMLeadEmails')->name('lead.ajax.saveCRMLeadEmails');
			Route::post('/getCRMEmailsData', 'getCRMEmailsData')->name('lead.ajax.getCRMEmailsData');
			Route::post('/saveCRMLeadNotes', 'saveCRMLeadNotes')->name('lead.ajax.saveCRMLeadNotes');
			Route::post('/getCRMNotesData', 'getCRMNotesData')->name('lead.ajax.getCRMNotesData');
			Route::get('/searchUser', 'searchUser')->name('lead.ajax.searchUser');

			// Lead reject type or reasons
			Route::get('/lead_reject_types', 'lead_reject_type')->name('lead.lead_reject_types');
			Route::post('/saveLeadRejectTypes', 'saveLeadRejectType')->name('lead.ajax.saveLeadRejectTypes');
			Route::post('/editLeadRejectTypes', 'saveLeadRejectType')->name('lead.ajax.editLeadRejectTypes');
			Route::post('/saveLeadRejectReasons', 'saveLeadRejectReason')->name('lead.ajax.saveLeadRejectReasons');
		});

		Route::prefix('leads')->group(function () {
			Route::get('/leads', 'index')->name('lead.index');
			Route::get('/unassigned', 'index')->name('lead.unassigned');
			Route::get('/converted', 'index')->name('lead.converted');
			Route::get('/add', 'create');
			Route::post('/create', 'store')->name('lead.store');
			Route::get('/edit/{id}', 'edit')->name('lead.edit');
			Route::get('/authorization/{id}', 'sentToAuthorization')->name('lead.authorization');
			Route::get('/search', 'searchLead');
			Route::get('tasks', 'task_list')->name('lead.task_list');
		});

		//Leads 
		Route::get('sales', 'leads');

		// Lead Task
		Route::post('/saveLeadTasks', 'save_lead_tasks')->name('lead.ajax.saveLeadTasks');
		Route::get('/leads/lead_task/delete/{task}/{lead}', 'lead_task_delete');
		Route::post('/saveLeadNotes', 'save_lead_notes')->name('lead.ajax.saveLeadNotes');

		// Lead Attachment 
		Route::post('/saveLeadAttachment', 'saveLeadAttachment')->name('lead.ajax.saveLeadAttachment');
		Route::get('/leads/lead_attachment/delete/{attachment}/{lead}', 'lead_attachments_delete');

		// Route::get('/lead/lead_source/delete/{id}', 'lead_source_delete');
		// Route::get('/lead_status/delete/{id}', 'lead_status_delete');
		// Route::get('/lead/lead_reject_types/delete/{id}', 'lead_reject_type_delete');
	});

	Route::controller(FrontendQuoteController::class)->group(function () {

		Route::prefix('quote')->group(function () {
			Route::get('/dashboard', 'dashboard')->name('quote.dashboard');
			Route::get('/add', 'create')->name('quote.quotes');
			Route::get('/add-details', 'details')->name('quote.details');
			Route::get('/draft', 'index')->name('quote.draft');
			// Add Quote Types
			Route::get('/quote_type', 'quote_type')->name('quote.quote_type');
			Route::post('/saveQuoteType', 'saveQuoteType')->name('quote.ajax.saveQuoteType');
			Route::post('/editQuoteType', 'saveQuoteType')->name('quote.ajax.editQuoteType');
			Route::post('/deleteQuoteType', 'deleteQuoteType')->name('quote.ajax.deleteQuoteType');
			Route::get('/getQuoteTypes', 'getQuoteTypes')->name('quote.ajax.getQuoteTypes');
			// Add Quote Sources
			Route::get('/quote_sources', 'quote_sources')->name('quote.quote_sources');
			Route::post('/saveQuoteSources', 'saveQuoteSources')->name('quote.ajax.saveQuoteSources');
			Route::post('/editQuoteSources', 'saveQuoteSources')->name('quote.ajax.editQuoteSources');
			Route::post('/deleteQuoteSource', 'deleteQuoteSource')->name('quote.ajax.deleteQuoteSource');
			// Add Quote Reject Type
			Route::get('/quote_reject_types', 'quote_reject_type')->name('quote.quote_reject_type');
			Route::post('/saveQuoteRejectType', 'saveQuoteRejectType')->name('quote.ajax.saveQuoteRejectType');
			Route::post('/editQuoteRejectType', 'saveQuoteRejectType')->name('quote.ajax.editQuoteRejectType');
			Route::post('/deleteQuoteRejectType', 'deleteQuoteRejectType')->name('quote.ajax.deleteQuoteRejectType');
			// Add Quote
			Route::post('/saveCustomerType', 'saveCustomerType')->name('quote.ajax.saveCustomerType');
			Route::get('/getCustomerType', 'getCustomerType')->name('quote.ajax.getCustomerType');
			Route::post('/saveRegion', 'saveRegion')->name('quote.ajax.saveRegion');
			Route::post('/editRegion', 'saveRegion')->name('quote.ajax.editRegion');
			Route::get('/getRegions', 'getRegions')->name('quote.ajax.getRegions');
			
			Route::get('/getCurrencyData', 'getCurrencyData')->name('currency.ajax.getCurrencyData');
			Route::post('/saveQuoteData', 'store');
			Route::get('/getHomeUsers', 'getHomeUsers')->name('quote.ajax.getUsersData');

			Route::get('/edit/{id}', 'edit')->name('quote.edit');
			Route::post('/saveAttachmentData', 'saveAttachmentData')->name('quote.ajax.saveAttachmentData');
			Route::post('/getAttachmentData', 'getAttachmentData')->name('quote.ajax.getAttachmentData');
			Route::get('/getAttachmentList', 'getAttachmentList')->name('quote.ajax.getAttachmentList');
			Route::post('/saveQuoteAttachments', 'saveQuoteAttachments')->name('quote.ajax.saveQuoteAttachments');
			Route::post('/getAttachmentDataOnQuoteId', 'getAttachmentDataOnQuoteId')->name('quote.ajax.getAttachmentDataOnQuoteId');
			Route::post('/deleteAttachment', 'deleteAttachment')->name('quote.ajax.deleteAttachment');
			Route::post('/getQuoteProductList', 'getQuoteProductList')->name('quote.ajax.getQuoteProductList');
			Route::post('/save-quote-callback', 'storeCallBackData')->name('quote.callback.save');
			Route::get('/callBack', 'callBack');
			Route::post('/save-quote-task', 'saveQuoteTask')->name('quote.ajax.saveQuoteTask');
			Route::post('/getQuoteTaskList', 'getQuoteTaskList')->name('quote.ajax.getQuoteTaskList');
			Route::get('/accepted', 'index');
			Route::get('/actioned', 'index');
			Route::get('/search', 'searchQuote');
			Route::get('/rejected', 'index');
		});
		
		Route::get('/quote-details/edit/{id}', 'editQuoteDetails')->name('quote.editDetails');
		Route::get('quote-details/add_multi_attachment', 'add_multi_attachment')->name('quote.addMultiAttachment');
		
		
		Route::prefix('quotes')->group(function () {
			Route::patch('/statusChange', 'statusChange')->name('quote.ajax.statusChange');
			Route::get('/getActiveRejectType', 'getActiveRejectType')->name('quote.ajax.getActiveRejectType');
			Route::post('/saveQuoteRejectReasonsType', 'saveQuoteRejectReasonsType')->name('quote.ajax.saveQuoteRejectReasonsType');
			Route::post('/saveQuoteDeposite', 'saveQuoteDeposite')->name('quote.ajax.saveQuoteDeposite');
			Route::post('/getDepositeData', 'getDepositeData')->name('quote.ajax.getDepositeData');
			Route::post('/saveInvoiceDeposite', 'saveInvoiceDeposite')->name('quote.ajax.saveInvoiceDeposite');
			Route::post('/getQuoteInvoiceDeposit', 'getQuoteInvoiceDeposit')->name('quote.ajax.getQuoteInvoiceDeposit');
			Route::post('/searchQuoteData', 'searchQuoteData')->name('quote.ajax.searchQuoteData');
		});

	});

	Route::controller(CataloguesController::class)->group(function () {

		Route::get('/item/catalogues', 'index')->name('catalogues.index');
		Route::post('/item/catalogues_save', 'catalogues_save');
		Route::post('/item/catalogues_edit', 'catalogues_save');
		Route::post('item/ProductCataloguePriceList', 'ProductCataloguePriceList');
		Route::post('item/ProductCataloguePriceDelete', 'ProductCataloguePriceDelete');
	});

	Route::controller(FrontendProductCategoryController::class)->group(function () {
		Route::get('/item/product_categories', 'index')->name('item.index');
		Route::post('/item/add_product_category', 'saveProductCategoryData')->name('item.saveProductCategoryData');
		Route::post('/item/edit_product_category', 'saveProductCategoryData')->name('item.editProductCategoryData');
		Route::post('/item/change_product_category_status', 'changeProductCategoryStatus')->name('item.changeProductCategoryStatus');
		Route::post('/item/delete_product_category', 'deleteProductCategory')->name('item.delete_product_category');
		Route::get('/item/get_product_categories', 'getCategoriesList')->name('item.ajax.getCategoriesList');
	});

	Route::controller(ProductController::class)->prefix('item')->name('item.')->group(function () {
		// GET routes
		Route::get('/products', 'productlist')->name('products');
		Route::get('/products/active', 'productlist')->name('products.active');
		Route::get('/products/inactive', 'productlist')->name('products.inactive');
		Route::get('/getProductCounts', 'getProductCounts')->name('ajax.getProductCounts');

		// POST routes for product-related actions
		Route::post('/productcategorylist', 'productcategorylist')->name('productcategorylist');
		Route::post('/generateproductcode', 'generateproductcode')->name('generateproductcode');
		Route::post('/saveTaxrateData', 'saveTaxrateData')->name('saveTaxrateData');
		Route::post('/taxratelist', 'taxratelist')->name('taxratelist');
		Route::post('/account_code', 'account_code')->name('account_code');
		Route::post('/saveproductdata', 'saveproductdata')->name('saveproductdata');
		Route::post('/editproductdata', 'saveproductdata')->name('editproductdata');
		Route::post('/changeProductStatus', 'changeProductStatus')->name('changeProductStatus');
		Route::post('/deleteProduct', 'deleteProduct')->name('deleteProduct');
		Route::post('/getproductdata', 'getproductdata')->name('getproductdata');

		// POST routes for product image actions
		Route::post('/getproductimage', 'getproductimage')->name('getproductimage');
		Route::post('/saveproductimages', 'saveproductimages')->name('saveproductimages');
		Route::post('/deleteproductimage', 'deleteproductimage')->name('deleteproductimage');

		// Additional POST route
		Route::post('/getProductList', 'getProductList')->name('ajax.getProductList');

		Route::get('/searchProduct', 'searchProduct')->name('ajax.searchProduct');
		Route::post('/getProductFromId', 'getProductFromId')->name('ajax.getProductFromId');
	});


	Route::controller(ProductGroupController::class)->prefix('item')->name('item.')->group(function () {
		// GET routes
		Route::get('/item-groups', 'productGroupList');

		// post routes
		Route::post('/saveProductGroup', 'saveProductGroup')->name('ajax.saveProductGroup');
		Route::post('/editProductGroup', 'saveProductGroup')->name('ajax.editProductGroup');
		Route::post('/ProductGroupProductsList', 'ProductGroupProductsList');
		Route::post('/ProductGroupProductsdetails', 'ProductGroupProductsdetails');
	});


	// ------------- Personal Management - My profile ---------------------// 
	Route::get('/my-profile/{user_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\ProfileController@index');
	Route::post('/my-profile/edit', 'App\Http\Controllers\frontEnd\PersonalManagement\ProfileController@edit_profile_setting');

	Route::match(['get', 'post'], '/profile/change-password', 'App\Http\Controllers\frontEnd\PersonalManagement\ChangePasswordController@change_password');

	// Weekly money module
	Route::post('weekly-allowance/update', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoneyController@update_home_weekly_allowance');

	//----12 jun 2018----
	Route::post('shopping_budget/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoneyController@add_shopping_bugdet');

	//add petty cash for home 
	Route::post('/profile/petty_cash/add-cash', 'App\Http\Controllers\frontEnd\PersonalManagement\PettyCashController@add_petty_cash');
	Route::get('/profile/petty-cash/view/{petty_cash_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\PettyCashController@view');
	//Route::post('/profile/petty-cash/edit', 'App\Http\Controllers\frontEnd\PersonalManagement\PettyCashController@view');
	Route::match(['get', 'post'], '/profile/petty_cash/check-balance', 'App\Http\Controllers\frontEnd\PersonalManagement\PettyCashController@get_petty_balance');
	Route::match(['get', 'post'], '/profile/petty-cashes', 'App\Http\Controllers\frontEnd\PersonalManagement\PettyCashController@index');

	// location history
	Route::match(['get', 'post'], '/service/location-history/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@index');
	Route::post('/service/location-history/location/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@add_location');

	Route::match(['get', 'post'], '/service/location-history/location/edit/{location_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@edit_location');

	Route::get('/service/location-history/location/delete/{location_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@delete_location');
	Route::post('/service/location-history/restriction-type/change', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@change_location_restriction_type');
	//not
	/*Route::get('/service/location-history/notif/acnowldg/master/{location_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@acknowldg_loc_notif_master');
	Route::get('/service/location-history/notif/acnowldg/personal/{location_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LocationHistoryController@acknowldg_loc_notif_personal');*/


	//Sick Leave
	Route::match(['get', 'post'], '/my-profile/sick-leaves/view/{manager_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\SickLeaveController@index');
	//Route::match(['get', 'post'], '/my-profile/sick-leaves/delete/{sick_leave_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\SickLeaveController@delete');
	Route::match(['get', 'post'], '/my-profile/sick-leaves/view-record/{sick_leave_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\SickLeaveController@view_sick_record');

	//Annual Leave
	Route::match(['get', 'post'], '/my-profile/annual-leaves/view/{manager_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\AnnualLeaveController@index');
	Route::match(['get', 'post'], '/my-profile/annual-leaves/view-record/{annual_leave_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\AnnualLeaveController@view_annual_record');
	//Task Allocation
	Route::match(['get', 'post'], '/my-profile/task-allocation/view/{manager_id}', 'App\Http\Controllers\frontEnd\PersonalManagement\TaskAllocationController@index');

	//Time Sheet
	Route::match(['get', 'post'], '/my-profile/{id}/time-sheet', 'App\Http\Controllers\frontEnd\PersonalManagement\TimesheetController@index');
	Route::match(['get', 'post'], '/my-profile/time-sheet/add', 'App\Http\Controllers\frontEnd\PersonalManagement\TimesheetController@save');
	Route::match(['get', 'post'], '/my-profile/time-sheet/edit', 'App\Http\Controllers\frontEnd\PersonalManagement\TimesheetController@save');
	Route::delete('/my-profile/time-sheet/delete/{id}', 'App\Http\Controllers\frontEnd\PersonalManagement\TimesheetController@destroy');
	Route::post('/my-profile/time-sheet', 'App\Http\Controllers\frontEnd\PersonalManagement\TimesheetController@getData');

	// -------- Header ------------------------//
	//Dynamic forms
	//Route::match(['get','post'], '/system/plans/', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@index');

	//not
	Route::match(['get', 'post'], '/service/dynamic-forms', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@index');
	Route::post('/saveFormDotIoImage', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@saveFormDotIoImage')->name('saveFormDotIoImage');
	Route::post('/service/dynamic-form/save', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@save_form');
	Route::post('/service/dynamic-form/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@edit_form');
	Route::post('/service/dynamic-form/view/pattern', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@view_form_pattern');
	Route::post('/service/patterndataformio', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@patterndataformio');
	Route::post('/service/patterndataformiovaule', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@patterndataformiovalue');
	Route::get('/service/dynamic-form/view/data/{dynamic_form_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@view_form_data');
	Route::get('/service/dynamic-form/delete/{dynamic_form_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@delete_form');
	Route::post('/service/dynamic-form/edit-details', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@edit_details');
	Route::post('/service/dynamic-form/daily-log', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DynamicFormController@su_daily_log_add');
	// Route::match(['get','post'], '/system/plans/edit', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@edit');
	// Route::match(['get','post'], '/system/plans/delete/{plan_id}', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@delete');

	// -------- Service Management ------------------------//

	Route::match(['get', 'post'], '/service-user-management', 'App\Http\Controllers\frontEnd\ServiceUserManagementController@service_users');
	Route::match(['get', 'post'], '/service/user-profile/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@index');

	Route::get('/service/user/afc-status/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@get_afc_status');

	// status change of su_profile pic
	Route::match(['get', 'post'], '/service/user-profile/afc-status/update/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@update_afc_status');

	//notifications
	Route::match(['get', 'post'], '/service/notifications/', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@show_notifications');

	Route::match(['get', 'post'], '/system-management', 'App\Http\Controllers\frontEnd\SystemManagementController@system_management');

	Route::match(['get', 'post'], '/add-service-user', 'App\Http\Controllers\frontEnd\SystemManagementController@add_service_user');
	Route::match(['get', 'post'], '/add-staff-user', 'App\Http\Controllers\frontEnd\SystemManagementController@add_staff_user');
	Route::get('user/qualification/delete/{id}', 'App\Http\Controllers\frontEnd\SystemManagementController@delete_certificate');

	//Daily Record in ServiceUserManagement
	Route::match(['get', 'post'], '/service/daily-records/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@index');
	Route::match(['get', 'post'], '/service/daily-record/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@add');
	Route::match(['get', 'post'], '/service/daily-record/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@edit');
	Route::match(['get', 'post'], '/service/daily-record/delete/{su_daily_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@delete');
	Route::match(['get', 'post'], '/service/daily-record/calendar/add/{su_daily_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@add_to_calendar');
	/*Route::match(['get','post'], '/service/daily-record/status/{daily_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyRecordController@update_status');*/

	//Daily Logs in ServiceUserManagement
	Route::match(['get', 'post'], '/service/daily-logs', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyLogsController@index');
	Route::match(['get', 'post'], '/service/daily-logs2', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyLogsController@index2');
	Route::match(['get', 'post'], '/service/daily-logs3', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyLogsController@index3');
	Route::match(['get', 'post'], '/service/weekly-logs', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyLogsController@weekly_log');
	Route::match(['get', 'post'], '/service/monthly-logs', 'App\Http\Controllers\frontEnd\ServiceUserManagement\DailyLogsController@monthly_log');

	//Backend Logs Download
	Route::get('/service/logbook/download', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PDFLogsController@download');

	//health record
	Route::get('/service/health-records/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\HealthRecordController@index');
	Route::post('/service/health-record/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\HealthRecordController@add');
	Route::post('/service/health-record/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\HealthRecordController@edit');
	Route::get('/service/health-record/delete/{su_health_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\HealthRecordController@delete');

	//risks
	Route::post('/service/risk/status/change', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@change_risk_status');
	Route::get('/service/risks/{su_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@index');
	Route::get('/service/risk/view/{risk_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@view');
	Route::get('/service/risk/risksfilter', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@risksfilter');
	//risk RMP
	Route::post('/service/risk/rmp/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@add_rmp_risk');
	Route::get('/service/risk/rmp/view/{su_risk_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@view_rmp_risk');
	//edit only a single records info
	Route::post('/service/risk/rmp/edit/{su_rmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@edit_rmp_risk');

	Route::get('/service/risk/inc-rec/view/{su_risk_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@view_inc_rec_risk');
	//edit multiple records at a time - details, review etc. 

	//risk Incident Report
	Route::post('/service/risk/inc-rep/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@add_inc_rep');
	Route::post('/service/risk/inc-rep/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RiskController@edit_inc_rep');

	//File Manager
	Route::match(['get', 'post'], '/service/file-managers/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\FileController@index');
	Route::match(['get', 'post'], '/service/file-manager/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\FileController@add_files');
	Route::get('/service/file-manager/delete/{file_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\FileController@delete');
	Route::post('/service/file-manager/upload/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\FileController@add_file');
	Route::post('/service/file-manager/email', 'App\Http\Controllers\frontEnd\ServiceUserManagement\FileController@file_email');

	//care team serviceUserManagement
	Route::match(['get', 'post'], '/service/care_team/add/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@add_care_team');
	Route::match(['get', 'post'], '/service/care_team/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_care_team');
	Route::match(['get', 'post'], '/service/care_team/delete/{care_team_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@delete_care_team');

	//careHistory serviceUserManagement
	Route::match(['get', 'post'], '/service/care_history/add/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@add_care_history');
	Route::match(['get', 'post'], '/service/care_history/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_care_history');
	Route::match(['get', 'post'], '/service/care_history/delete/{care_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@delete_care_history');

	Route::match(['get', 'post'], '/service/care-history/delete-file/{su_care_history_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@delete_hist_file');

	//location info
	Route::post('/service/user/edit-location-details', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_location_info');
	Route::post('/service/user/edit-contact-details', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_contact_info');

	//contact_us
	Route::post('/service/user/contact-person/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_contact_person');
	Route::get('/service/user/contact-person/delete/{contact_us_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@delete_contact_person');

	//earning scheme	
	Route::match(['get', 'post'], '/service/earning-scheme/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@index');
	Route::match(['get', 'post'], '/service/earning-scheme/view_incentive/{earning_category_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@view_incentive');
	Route::match(['get', 'post'], '/service/earning-scheme/incentive/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@add_to_calendar');
	Route::post('/service/earning-scheme/star/remove/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@remove_star');

	Route::post('/service/earning/set-target', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@set_su_earning_target');

	//suspend incentive
	Route::post('/service/earning-scheme/incentive/suspend', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@incentive_suspend');
	Route::get('/service/earning-scheme/incentive/suspend/view/{suspended_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@view_suspension');
	/*Route::post('/service/earning-scheme/incentive/suspend/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@edit_suspension');*/
	Route::get('/service/earning-scheme/incentive/suspend/delete', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EarningSchemeController@remove_suspension');

	//Living Skill in ServiceUserManagement
	Route::match(['get', 'post'], '/service/living-skills/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LivingSkillController@index');
	Route::match(['get', 'post'], '/service/living-skill/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LivingSkillController@add');
	Route::match(['get', 'post'], '/service/living-skill/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LivingSkillController@edit');
	Route::match(['get', 'post'], '/service/living-skill/delete/{su_living_skill_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LivingSkillController@delete');
	Route::match(['get', 'post'], '/service/living-skill/calendar/add/{su_living_skill_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LivingSkillController@add_to_calendar');

	//Education Record in ServiceUserManagement
	Route::match(['get', 'post'], '/service/education-records/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EducationRecordController@index');
	Route::match(['get', 'post'], '/service/education-record/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EducationRecordController@add');
	Route::match(['get', 'post'], '/service/education-record/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EducationRecordController@edit');
	Route::match(['get', 'post'], '/service/education-record/delete/{su_edu_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EducationRecordController@delete');
	Route::match(['get', 'post'], '/service/education-record/calendar/add/{su_edu_record_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EducationRecordController@add_to_calendar');

	//MFC Records in ServiceUserManagement
	Route::match(['get', 'post'], '/service/mfc-records/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MFCController@index');
	Route::get('/service/mfc/view/{su_mfc_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MFCController@view_mfc_rcrd');
	Route::post('/service/mfc/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MFCController@add');
	Route::match(['get', 'post'], '/service/mfc/edit/{su_mfc_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MFCController@edit');
	Route::match(['get', 'post'], '/service/mfc/delete/{su_mfc_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MFCController@delete');

	//BMP_RMP in  Daily Record ServiceUserManagement
	Route::match(['get', 'post'], '/service/bmp-rmps/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@index');
	Route::post('/service/bmp-rmp/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@add');
	Route::post('/service/bmp-rmp/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@edit');
	Route::get('/service/bmp-rmp/delete/{bmp_rmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@delete');

	Route::match(['get', 'post'], '/service/bmp-rmp/view/{bmp_rmp_risk_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@view_detail');

	Route::post('/service/user/edit-details', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ProfileController@edit_detail_info');
	/*Route::match(['get','post'], '/service/daily-records-bmp-rmp/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpRmpController@service_users_list');*/

	//Body Map

	Route::match(['get', 'post'], '/service/body-map/{risk_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BodyMapController@index');
	Route::match(['get', 'post'], '/service/body-map/injury/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BodyMapController@addInjury');
	Route::match(['get', 'post'], '/service/body-map/injury/remove/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BodyMapController@removeInjury');

	//calender paths
	Route::match(['get', 'post'], '/service/calendar/{service_user_id}', 'App\Http\Controllers\frontEnd\CalendarController@index');
	Route::match(['get', 'post'], '/service/calendar/daily-records/{serv_usr_id}', 'App\Http\Controllers\frontEnd\CalendarController@daily_records');
	Route::match(['get', 'post'], '/service/calendar/health-records/{serv_usr_id}', 'App\Http\Controllers\frontEnd\CalendarController@health_records');
	Route::match(['get', 'post'], '/service/calendar/daily-records/delete/{daily_record_id}', 'App\Http\Controllers\frontEnd\CalendarController@delete_daily_record');

	//calendar drag & drop events
	Route::match(['get', 'post'], '/service/calendar/event/add', 'App\Http\Controllers\frontEnd\CalendarController@add_event');
	Route::match(['get', 'post'], '/service/calendar/event/move', 'App\Http\Controllers\frontEnd\CalendarController@move_event');


	//calendar entries
	Route::get('/service/calendar/entry/display-form/{plan_bulider_id}', 'App\Http\Controllers\frontEnd\CalendarEntryController@display_form');
	Route::post('/service/calendar/entry/add', 'App\Http\Controllers\frontEnd\CalendarEntryController@add');

	// calendar add notes
	Route::post('/service/calendar/note/add', 'App\Http\Controllers\frontEnd\CalendarEntryController@add_note');
	Route::post('/service/calendar/mandatory_leave/add', 'App\Http\Controllers\frontEnd\CalendarEntryController@add_mandatory_leave');

	// calendar view event details
	Route::match(['get', 'post'], '/service/calendar/event/view', 'App\Http\Controllers\frontEnd\CalendarEventController@index');
	Route::match(['get', 'post'], '/service/calendar/event/edit', 'App\Http\Controllers\frontEnd\CalendarEventController@edit');
	Route::match(['get', 'post'], '/service/calendar/event/remove/{calendar_id}', 'App\Http\Controllers\frontEnd\CalendarEventController@delete');

	//Weekly and Monthly Report
	Route::match(['get', 'post'], '/select/report', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@index');
	Route::match(['get', 'post'], '/monthly/report/detail/{log_book_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@monthly_report_detail');
	Route::match(['get', 'post'], '/edit/report/detail', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@edit_report_detail');
	Route::match(['get', 'post'], '/send/mail/careteam/{log_book_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\ReportController@send_mail_to_careteam');

	//EventChange Request
	Route::get('/service/event-requests/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EventRequestController@index');
	Route::get('/service/event-request/{req_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EventRequestController@view');
	Route::post('/service/event-request/update', 'App\Http\Controllers\frontEnd\ServiceUserManagement\EventRequestController@update');

	//placement plan - service usr mngment
	Route::match(['get', 'post'], '/service/placement-plans/{su_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@index');
	Route::match(['get', 'post'], '/service/placement-plan/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@add');
	Route::match(['get', 'post'], '/service/placement-plan/completed-targets/{su_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@completed_targets');
	Route::match(['get', 'post'], '/service/placement-plan/active-targets/{su_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@active_targets');
	Route::match(['get', 'post'], '/service/placement-plan/pending-targets/{su_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@pending_targets');
	Route::match(['get', 'post'], '/service/placement-plan/mark-complete/{target_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@mark_complete');
	Route::match(['get', 'post'], '/service/placement-plan/mark-active/{target_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@mark_active');

	Route::match(['get', 'post'], '/service/placement-plan/target/view/{act_tar_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@view_target');
	Route::post('/service/placement-plan/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@edit');

	Route::post('/service/placement-plan/add-qqa-review', 'App\Http\Controllers\frontEnd\ServiceUserManagement\PlacementPlanController@add_qqa_review');

	//RMP
	Route::match(['get', 'post'], '/service/rmp/view/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@index');
	Route::get('/service/rmp/view_rmp/{su_rmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@view_rmp');
	Route::post('/service/rmp/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@add_rmp');
	Route::match(['get', 'post'], '/service/rmp/edit_rmp/{su_rmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@edit_rmp');
	Route::get('/service/rmp/delete/{su_rmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@delete');
	Route::match(['get', 'post'], '/service/rmp/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\RmpController@edit');

	//BMP
	Route::match(['get', 'post'], '/service/bmp/view/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@index');
	Route::post('/service/bmp/add', 'fApp\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@add_bmp');
	Route::match(['get', 'post'], '/service/bmp/edit', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@edit');
	Route::get('/service/bmp/delete/{su_bmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@delete');
	Route::get('/service/bmp/view_bmp/{su_bmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@view_bmp');
	Route::post('/service/rmp/edit_bmp/{su_bmp_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\BmpController@edit_bmp');

	//IncidentReport
	Route::match(['get', 'post'], '/service/incident-report/views/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\IncidentController@index');
	Route::get('/service/incident-report/view_incident/{su_incident_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\IncidentController@view_incident');
	Route::post('/service/incident-report/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\IncidentController@add_incident');
	Route::post('/service/incident-report/edit_incident/{su_incident_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\IncidentController@edit_incident');
	Route::get('/service/incident-report/delete/{su_incident_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\IncidentController@delete');

	//ServiceUser LogBook
	Route::match(['get', 'post'], '/service/logsbook/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@index');
	Route::post('/system/logbook/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@add');
	Route::get('/forms', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@forms');
	Route::get('/service/logbook/view/{log_book_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@view');

	/**
	 * BaseUrl: 
	 */
	$LogBookCommentsController = 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookComments\LogBookCommentsController@';
	Route::get('/service/logbook/comments', $LogBookCommentsController . 'index');
	Route::post('/service/logbook/comments', $LogBookCommentsController . 'store');

	// Route::get('/service/logbook/view/{service_user_id}/{log_book_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@view');
	Route::get('/service/logbook/Calendar/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@add_to_calendar');

	//handover point
	Route::get('/staff-user-list', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@staffuserlist');
	Route::post('/handover/service/log', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@log_handover_to_staff_user');
	Route::match(['get', 'post'], '/handover/daily/log', 'App\Http\Controllers\frontEnd\HandoverController@index');
	Route::match(['get', 'post'], '/handover/daily/log/edit', 'App\Http\Controllers\frontEnd\HandoverController@handover_log_edit');

	//add to weekly
	Route::match(['get', 'post'], '/weekly/report/{log_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@weekly_report');
	Route::match(['get', 'post'], '/weekly/rprt/edit/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\LogBookController@weekly_report_edit');

	// su Moods listing
	Route::get('/service/moods/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoodController@index');
	Route::post('/service/mood/suggestion/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoodController@add');

	// my money requests of user
	Route::get('service/money-requests/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoneyController@index');
	Route::get('service/money-request/{money_request_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoneyController@view_detail');
	Route::post('service/money-request/update', 'App\Http\Controllers\frontEnd\ServiceUserManagement\MoneyController@update');

	Route::match(['get', 'post'], 'notif/response', 'App\Http\Controllers\frontEnd\ServiceUserManagementController@notif_response');


	// -------- System Management ------------------------//
	//-------------shalinder----------------------------------------------------------------------//
	Route::match(['get', 'post'], '/system/earning-scheme/tasks/{label_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@index');
	Route::match(['get', 'post'], '/system/earning-scheme/task/add/{label_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@add');
	Route::match(['get', 'post'], '/system/earning-scheme/task/edit/{label_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@edit');
	Route::match(['get', 'post'], '/system/earning-scheme/task/delete/{daily_record_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@delete');
	Route::match(['get', 'post'], '/system/earning-scheme/task/status/{daily_record_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@update_status');
	Route::match(['get', 'post'], '/system/earning-scheme/del-daily-records', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeTaskController@delete_daily_records');
	//---------------------------------------------------------------------------------------------//

	//Daily Records in SystemManagement
	Route::match(['get', 'post'], '/system/daily-records/', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@index');
	Route::match(['get', 'post'], '/system/daily-records/add', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@add');
	Route::match(['get', 'post'], '/system/daily-records/delete/{daily_record_id}', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@delete');
	Route::match(['get', 'post'], '/system/daily-records/status/{daily_record_id}', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@update_status');
	Route::match(['get', 'post'], '/system/daily-records/edit', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@edit');
	Route::match(['get', 'post'], '/system/del-daily_records', 'App\Http\Controllers\frontEnd\SystemManagement\DailyRecordController@delete_daily_records');
	// Daily Records in SystemManagement end

	//MFC in SystemManagement May19
	Route::match(['get', 'post'], '/system/mfc/', 'App\Http\Controllers\frontEnd\SystemManagement\MFCController@index');
	Route::match(['get', 'post'], '/system/mfc/add', 'App\Http\Controllers\frontEnd\SystemManagement\MFCController@add');
	Route::match(['get', 'post'], '/system/mfc/edit', 'App\Http\Controllers\frontEnd\SystemManagement\MFCController@edit');
	Route::match(['get', 'post'], '/system/mfc/delete/{mfc_id}', 'App\Http\Controllers\frontEnd\SystemManagement\MFCController@delete');
	Route::match(['get', 'post'], '/system/mfc/status/{mfc_id}', 'App\Http\Controllers\frontEnd\SystemManagement\MFCController@update_status');

	//Risk Controller in SystemManagement
	Route::match(['get', 'post'], '/system/risk/add', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@add');
	Route::match(['get', 'post'], '/system/risk/index', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@index');
	Route::match(['get', 'post'], '/system/risk/delete/{risk_id}', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@delete');
	Route::match(['get', 'post'], '/system/risk/status/{risk_id}', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@update_status');
	Route::match(['get', 'post'], '/system/risk/edit', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@edit');
	Route::match(['get', 'post'], '/system/del-risk', 'App\Http\Controllers\frontEnd\SystemManagement\RiskController@risk_delete');
	//Risk Controller in SystemManagement End

	//Earning Scheme in SystemManagement
	Route::match(['get', 'post'], '/system/earning/index', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@index');
	Route::match(['get', 'post'], '/system/earning/add', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@add');
	Route::match(['get', 'post'], '/system/earning/edit', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@edit');
	Route::match(['get', 'post'], '/system/earning/delete/{earn_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@delete');
	Route::match(['get', 'post'], '/system/earning/status/{earn_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@update_status');
	//Multidelete earning schemes (Not Use)
	Route::match(['get', 'post'], '/system/del-earn', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@earning_scheme_delete');
	Route::match(['get', 'post'], '/system/earning/add_incentive', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@add_incentive');
	Route::match(['get', 'post'], '/system/earning/delete_incentive/{incentive_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@delete_incentive');
	Route::match(['get', 'post'], '/system/earning/update_incentive_status/{incentive_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@update_incentive_status');

	//Incentive View in Earning Scheme
	Route::match(['get', 'post'], '/system/earning/view_incentive/{incentive_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@view_incentive');
	Route::match(['get', 'post'], '/system/earning/edit_incentive', 'App\Http\Controllers\frontEnd\SystemManagement\EarningSchemeController@edit_incentive');

	//Health Records in SystemManagement
	Route::match(['get', 'post'], '/system/health-records/', 'App\Http\Controllers\frontEnd\SystemManagement\HealthRecordController@index');
	/*Route::match(['get','post'], '/system/health-records/pagination', 'App\Http\Controllers\frontEnd\SystemManagement\HealthRecordController@pagination');*/

	//SupportTicket in SystemManagement
	Route::match(['get', 'post'], '/system/support-ticket', 'App\Http\Controllers\frontEnd\SystemManagement\SupportTicketController@index');
	Route::match(['get', 'post'], '/system/support-ticket/add', 'App\Http\Controllers\frontEnd\SystemManagement\SupportTicketController@add');
	Route::match(['get', 'post'], '/system/support-ticket/view/{ticket_id}', 'App\Http\Controllers\frontEnd\SystemManagement\SupportTicketController@view_ticket');
	Route::match(['get', 'post'], '/system/support-ticket/view-msg/add', 'App\Http\Controllers\frontEnd\SystemManagement\SupportTicketController@add_ticket_mesg');
	Route::match(['get', 'post'], '/system/support-ticket/ticket_status/{support_id}', 'App\Http\Controllers\frontEnd\SystemManagement\SupportTicketController@ticket_status');

	//Appointments / Plans in SystemManagement
	Route::match(['get', 'post'], '/system/plans/', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@index');
	Route::match(['get', 'post'], '/system/plans/add', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@add');
	Route::match(['get', 'post'], '/system/plans/view/{plan_builder_id}', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@view');
	Route::match(['get', 'post'], '/system/plans/edit', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@edit');
	Route::match(['get', 'post'], '/system/plans/delete/{plan_id}', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@delete');
	Route::match(['get', 'post'], '/system/del/plans', 'App\Http\Controllers\frontEnd\SystemManagement\PlanBuilderController@delete_plan');

	//Calendar in SystemManagement
	Route::match(['get', 'post'], '/system/calendar', 'App\Http\Controllers\frontEnd\SystemManagement\CalendarController@index');

	//calendar drag & drop events
	Route::match(['get', 'post'], '/system/calendar/event/add', 'App\Http\Controllers\frontEnd\SystemManagement\CalendarController@add_event');
	Route::match(['get', 'post'], '/system/calendar/event/move', 'App\Http\Controllers\frontEnd\SystemManagement\CalendarController@move_event');

	//calendar entries
	Route::get('/system/calendar/entry/display-form/{plan_bulider_id}', 'App\Http\Controllers\frontEnd\CalendarEntryController@display_form');
	Route::post('/system/calendar/entry/add', 'App\Http\Controllers\frontEnd\CalendarEntryController@add');

	// calendar add notes
	Route::post('/system/calendar/note/add', 'App\Http\Controllers\frontEnd\CalendarEntryController@add_note');

	// calendar view event details
	Route::match(['get', 'post'], '/system/calendar/event/view', 'App\Http\Controllers\frontEnd\CalendarEventController@index');
	Route::match(['get', 'post'], '/system/calendar/event/edit', 'App\Http\Controllers\frontEnd\CalendarEventController@edit');
	Route::match(['get', 'post'], '/system/calendar/event/remove/{calendar_id}', 'App\Http\Controllers\frontEnd\CalendarEventController@delete');
	Route::post('/system/calendar/select/member', 'App\Http\Controllers\frontEnd\SystemManagement\CalendarController@select_member');

	//Living Skills in SystemManagement
	Route::match(['get', 'post'], '/system/living-skills/', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@index');
	Route::match(['get', 'post'], '/system/living-skill/add', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@add');
	Route::match(['get', 'post'], '/system/living-skill/edit', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@edit');
	Route::match(['get', 'post'], '/system/living-skill/delete/{living_skill_id}', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@delete');
	Route::match(['get', 'post'], '/system/living-skill/status/{living_skill_id}', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@update_status');
	Route::match(['get', 'post'], '/system/del/living-skill/', 'App\Http\Controllers\frontEnd\SystemManagement\LivingSkillController@living_skill_delete');
	//Living Skills in SystemManagement end

	//Education Training in SystemManagement 
	Route::match(['get', 'post'], '/system/education-records/', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@index');
	Route::match(['get', 'post'], '/system/education-record/add', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@add');
	Route::match(['get', 'post'], '/system/education-record/edit', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@edit');
	Route::match(['get', 'post'], '/system/education-record/delete/{edu_rec_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@delete');
	Route::match(['get', 'post'], '/system/education-record/status/{edu_rec_id}', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@update_status');
	Route::match(['get', 'post'], '/system/del/education-record', 'App\Http\Controllers\frontEnd\SystemManagement\EducationRecordController@edu_record_delete');
	//Education Training in SystemManagement May15 end

	//if user is not autorized to anything then send request to admin
	Route::match('post', '/send-modify-request', 'App\Http\Controllers\frontEnd\DashboardController@send_modify_request');

	//Route::match(['post','get'], '/bug-report', 'Controller@bug_report');
	Route::match(['post', 'get'], '/bug-report', 'App\Http\Controllers\frontEnd\BugReportController@index');
	Route::match(['post', 'get'], '/bug-report/add', 'App\Http\Controllers\frontEnd\BugReportController@add');

	// -------- Staff Management ------------------------//

	Route::match(['get', 'post'], '/staff-management', 'App\Http\Controllers\frontEnd\StaffManagementController@staff_member');
	Route::match(['get', 'post'], '/staff/profile/{staff_id}', 'App\Http\Controllers\frontEnd\StaffManagement\ProfileController@index');
	Route::match(['get', 'post'], '/staff/member/edit-settings', 'App\Http\Controllers\frontEnd\StaffManagement\ProfileController@edit_staff_setting');

	/*Route::post('/staff/member/edit-profile', 'App\Http\Controllers\frontEnd\StaffManagement\ProfileController@edit_staff_detail_info');
    Route::post('/staff/member/edit-location', 'App\Http\Controllers\frontEnd\StaffManagement\ProfileController@edit_staff_location_info');
    Route::post('/staff/member/edit-contact', 'App\Http\Controllers\frontEnd\StaffManagement\ProfileController@edit_staff_contact_info');*/

	//TaskAllocation
	Route::match(['get', 'post'], '/staff/member/task-allocation/add', 'App\Http\Controllers\frontEnd\StaffManagement\TaskAllocationController@add');
	Route::match(['get', 'post'], '/staff/member/task-allocation/view/{staff_member_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TaskAllocationController@index');
	Route::match(['get', 'post'], '/staff/member/task-allocation/delete/{task_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TaskAllocationController@delete');
	Route::match(['get', 'post'], '/staff/member/task-allocation/edit', 'App\Http\Controllers\frontEnd\StaffManagement\TaskAllocationController@edit');
	Route::match(['get', 'post'], '/staff/member/task-allocation/status-update/{task_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TaskAllocationController@update_status');

	//Manage SickLeave
	Route::match(['get', 'post'], '/staff/member/sick-leave/view/{staff_member_id}', 'App\Http\Controllers\frontEnd\StaffManagement\SickLeaveController@index');
	Route::match(['get', 'post'], '/staff/member/sick-leave/view-record/{sick_leave_id}', 'App\Http\Controllers\frontEnd\StaffManagement\SickLeaveController@view_sick_record');
	Route::post('/staff/member/sick-leave/add', 'App\Http\Controllers\frontEnd\StaffManagement\SickLeaveController@add');
	Route::post('/staff/member/sick-leave/edit', 'App\Http\Controllers\frontEnd\StaffManagement\SickLeaveController@edit');
	Route::get('/staff/member/sick-leave/delete/{sick_leave_id}', 'App\Http\Controllers\frontEnd\StaffManagement\SickLeaveController@delete');

	//Manage AnnualLeave
	Route::match(['get', 'post'], '/staff/annual-leaves/{staff_member_id}', 'App\Http\Controllers\frontEnd\StaffManagement\AnnualLeaveController@index');
	Route::match(['get', 'post'], '/staff/annual-leave/view-annual/{annual_leave_id}', 'App\Http\Controllers\frontEnd\StaffManagement\AnnualLeaveController@view_annual_record');
	Route::post('/staff/annual-leave/add', 'App\Http\Controllers\frontEnd\StaffManagement\AnnualLeaveController@add');
	Route::post('/staff/annual-leave/edit', 'App\Http\Controllers\frontEnd\StaffManagement\AnnualLeaveController@edit');
	Route::get('/staff/annual-leave/delete/{annual_leave_id}', 'App\Http\Controllers\frontEnd\StaffManagement\AnnualLeaveController@delete');

	//Staff Rota
	Route::match(['get', 'post'], '/staff/rota/view', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@index');
	Route::post('/staff/rota/add-shift', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@add_shift');
	Route::get('/staff/rota/delete-shift/{rota_id}', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@delete');
	Route::post('/staff/rota/add-rota', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@add_rota');

	Route::get('/staff/rota/shift/view/{rota_id}', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@view_rota');
	Route::post('/staff/rota/shift/edit', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@edit_shift');

	Route::get('/staff/rota/print', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@print_rota');
	Route::get('/staff/rota/copy', 'App\Http\Controllers\frontEnd\StaffManagement\RotaController@copy_rota');

	/*------- staff Training ------- */
	Route::get('/staff/trainings', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@index');
	Route::post('/staff/training/add', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@add');
	Route::get('/staff/training/view/{id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@view');
	Route::get('/staff/training/completed/view/{id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@completed_training');
	Route::get('/staff/training/not-completed/view/{id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@not_completed_training');
	Route::get('/staff/training/active/view/{id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@active_training');
	Route::get('/staff/training/status/update/{training_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@status_update');
	//Route::get('/staff/training/status/update/{training_id}/{status}','App\Http\Controllers\frontEnd\StaffManagement\TrainingController@status_update');
	Route::post('/staff/training/staff/add', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@add_user_training');
	Route::get('/staff/training/view_fields/{traini_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@view_fields');
	Route::post('/staff/training/edit_fields', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@edit_fields');
	Route::get('/staff/training/delete/{training_id}', 'App\Http\Controllers\frontEnd\StaffManagement\TrainingController@delete');

	// -------- general admin ------------------------//
	Route::match(['get', 'post'], '/general-admin', 'App\Http\Controllers\frontEnd\GeneralAdminController@index');

	//LogBook
	Route::match(['get', 'post'], '/general/logsbook', 'App\Http\Controllers\frontEnd\GeneralAdmin\LogBookController@index');
	Route::match(['get', 'post'], '/general/logbook/add', 'App\Http\Controllers\frontEnd\GeneralAdmin\LogBookController@add');
	Route::get('/service-user-list', 'App\Http\Controllers\frontEnd\GeneralAdmin\LogBookController@serviceuserlist');
	Route::post('/service-user-add-log', 'App\Http\Controllers\frontEnd\GeneralAdmin\LogBookController@service_user_add_log');
	Route::match(['get', 'post'], '/general/logbook/calendar/add', 'App\Http\Controllers\frontEnd\GeneralAdmin\LogBookController@add_to_calendar');


	//PettyCash Report
	Route::match(['get', 'post'], '/general/petty-cashes', 'App\Http\Controllers\frontEnd\GeneralAdmin\PettyCashController@index');
	Route::get('/general/petty-cash/view/{petty_cash_id}', 'App\Http\Controllers\frontEnd\GeneralAdmin\PettyCashController@view');
	Route::post('/general/petty-cash/add', 'App\Http\Controllers\frontEnd\GeneralAdmin\PettyCashController@add');
	Route::post('/general/petty-cash/edit', 'App\Http\Controllers\frontEnd\GeneralAdmin\PettyCashController@edit');
	Route::match(['get', 'post'], '/general/petty_cash/check-balance', 'App\Http\Controllers\frontEnd\GeneralAdmin\PettyCashController@get_petty_balance');

	//Policies & Procedures
	Route::match(['get', 'post'], '/policies', 'App\Http\Controllers\frontEnd\PoliciesController@index');
	Route::match(['get', 'post'], '/policies/add-multiple', 'App\Http\Controllers\frontEnd\PoliciesController@add_multiple');
	Route::post('/policies/add-single', 'App\Http\Controllers\frontEnd\PoliciesController@add_single');
	Route::get('/policies/delete/{policy_id}', 'App\Http\Controllers\frontEnd\PoliciesController@delete'); //delete new added
	Route::get('/policy/delete/{policy_id}', 'App\Http\Controllers\frontEnd\PoliciesController@delete_policy'); //delete from logged
	Route::get('/policy/accept/{policy_id}', 'App\Http\Controllers\frontEnd\PoliciesController@accept_policy');
	Route::post('/policy/update', 'App\Http\Controllers\frontEnd\PoliciesController@update_policy');

	//AgendaMeetingController
	Route::match(['get', 'post'], '/staff/meetings', 'App\Http\Controllers\frontEnd\GeneralAdmin\AgendaMeetingController@index');
	Route::get('/staff/meeting/view/{meeting_id}', 'App\Http\Controllers\frontEnd\GeneralAdmin\AgendaMeetingController@view');
	Route::post('staff/meeting/add', 'App\Http\Controllers\frontEnd\GeneralAdmin\AgendaMeetingController@add');
	Route::post('staff/meeting/edit', 'App\Http\Controllers\frontEnd\GeneralAdmin\AgendaMeetingController@edit');
	Route::get('staff/meeting/delete/{meeting_id}', 'App\Http\Controllers\frontEnd\GeneralAdmin\AgendaMeetingController@delete');

	//------------- View Reports ---------------//
	Route::match(['get', 'post'], '/view-reports', 'App\Http\Controllers\frontEnd\ViewReportController@index');
	// 	Route::get('/users/{user_type_id}', 'App\Http\Controllers\frontEnd\ViewReportController@get_user');
	Route::get('/users', 'App\Http\Controllers\frontEnd\ViewReportController@get_user');
	Route::match(['get', 'post'], '/user/record', 'App\Http\Controllers\frontEnd\ViewReportController@record');
});

// System guide 
Route::get('/system-guide/faq/view/{guide_tag}', 'App\Http\Controllers\frontEnd\SystemGuideController@index');
Route::get('/system-guide/category/logged', 'App\Http\Controllers\frontEnd\SystemGuideController@category_logged');
Route::get('/system-guide/search/{ques}', 'App\Http\Controllers\frontEnd\SystemGuideController@searched_ques');

//Routes that does not need permissions - Auth and access

// Frontend unique SU username
// Route::match(['get','post'], 'user/check-su-username-exists', 'App\Http\Controllers\frontEnd\UserController@check_su_username_exists');
// Route::match(['get','post'], 'staff/check-staff-username-exists', 'App\Http\Controllers\frontEnd\UserController@check_staff_username_exists');

Route::match(['get', 'post'], '/check-username-exists', 'App\Http\Controllers\frontEnd\UserController@check_username_exists');
Route::match('get', '/set-password/{user_id}/{security_code}', 'App\Http\Controllers\frontEnd\UserController@show_set_password_form');
Route::match('post', 'users/set-password', 'App\Http\Controllers\frontEnd\UserController@set_password');
Route::get('get-homes/{company_name}', 'App\Http\Controllers\frontEnd\UserController@get_homes');

//lockscreen (not required to saved in db)
Route::get('lock', 'App\Http\Controllers\frontEnd\LockAccountController@lock');
Route::get('lockscreen', 'App\Http\Controllers\frontEnd\LockAccountController@lockscreen');
Route::post('lockscreen', 'App\Http\Controllers\frontEnd\LockAccountController@unlock');

//care center message module (not saved)
Route::get('/service/care-center/message-office/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\CareCenterController@office_messages');
Route::post('/service/care-center/message-office/add', 'App\Http\Controllers\frontEnd\ServiceUserManagement\CareCenterController@add_office_message');
Route::get('/service/care-center/message/need_assistance/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\CareCenterController@need_assistance_messages');
Route::match(['get', 'post'], '/service/care-center/req-callback/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\CareCenterController@request_callback');
Route::match(['get', 'post'], '/service/care-center/in-danger/{service_user_id}', 'App\Http\Controllers\frontEnd\ServiceUserManagement\CareCenterController@in_danger');


//sticky notification
//not
Route::get('/notification/ack/master/{notification_id}', 'App\Http\Controllers\frontEnd\StickyNotificationController@ack_master');
Route::get('/notification/ack/indiv/{notification_id}', 'App\Http\Controllers\frontEnd\StickyNotificationController@ack_individual');

/*-------In Danger -------*/

/*Route::get('/care-center/notification/update/{care_center_id}','App\Http\Controllers\frontEnd\ServiceUserCareCenterController@update_view_status');*/

//Cron jobs
//Route::get('cron/weekly-allowance/add','App\Http\Controllers\CronController@add_weekly_allowance');
Route::get('cron/every-day', 'App\Http\Controllers\CronController@every_day');
Route::get('cron/every-week', 'App\Http\Controllers\CronController@every_week');
Route::get('cron/every-minute', 'App\Http\Controllers\CronController@every_minute');
Route::get('user/payments', 'App\Http\Controllers\CronController@recurring_home_package_fee');


//Route::get('cron-jobs','CronController@index');


//________________BACKEND_ROUTES_START___________________________________________________//

Route::match(['get', 'post'], 'admin/login', 'App\Http\Controllers\backEnd\AdminController@login');
Route::match(['get', 'post'], 'admin/logout', 'App\Http\Controllers\backEnd\AdminController@logout');
Route::match(['get', 'post'], 'admin/check-email-exists', 'App\Http\Controllers\backEnd\ForgotPasswordController@check_admin_email_exists');
Route::match(['get', 'post'], 'admin/forgot-password', 'App\Http\Controllers\backEnd\ForgotPasswordController@send_forgot_pass_link_mail');

Route::get('admin/get-homes/{company_name}', 'App\Http\Controllers\backEnd\WelcomeController@get_homes');

//show admin set password page
Route::match('get', 'admin/set-password/{system_admin_id}/{security_code}', 'App\Http\Controllers\backEnd\superAdmin\AdminController@show_set_password_form_system_admin');

//save admin new password after set password page
Route::match(['get', 'post'], 'admin/system-admin/set-password', 'App\Http\Controllers\backEnd\superAdmin\AdminController@set_password_system_admin');
//paypal
Route::match(['get', 'post'], '/system-admin/home/payment/success/{system_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@success');

Route::post('/admin/getHomeList', [HomeController::class, 'getHomeList'])->name('admin.getHomeList');

// Route::post('/admin/getHomeList', 'App\Http\Controllers\backEnd\superAdmin\HomeController@getHomeList');
//paypal
Route::group(['prefix' => 'admin', 'middleware' => 'CheckAdminAuth'], function () {
	//download form  As PDF 
	Route::match(['get', 'post'], '/DownloadFormpdf/{id}', 'App\Http\Controllers\backEnd\superAdmin\UserController@DownloadFormpdf');

	Route::get('/', 'App\Http\Controllers\backEnd\AdminController@dashboard');
	// 	Route::get('/dashboard', 'App\Http\Controllers\backEnd\AdminController@dashboard');
	Route::match(['get', 'post'], '/dashboard', 'App\Http\Controllers\backEnd\AdminController@dashboard');


	//personal Mangement(profile)
	Route::get('/profile', 'App\Http\Controllers\backEnd\myProfile\ProfileController@profile');
	Route::match(['get', 'post'], '/profile/edit', 'App\Http\Controllers\backEnd\myProfile\ProfileController@edit');
	Route::match(['get', 'post'], '/profile/change-password', 'App\Http\Controllers\backEnd\myProfile\ProfileController@change_password');

	Route::match(['get', 'post'], '/welcome', 'App\Http\Controllers\backEnd\WelcomeController@welcome');
	Route::get('/welcome/get-homes/{company_name}', 'App\Http\Controllers\backEnd\WelcomeController@welcome_get_homes');
	// Route::match(['get','post'], '/welcome', 'App\Http\Controllers\backEnd\HomeController@welcome_admin');
	// Route::match(['get','post'], '/welcome/homes', 'App\Http\Controllers\backEnd\HomeController@welcome_super_admin');

	//backEnd Manager in SuperAdmin
	Route::match(['get', 'post'], '/company-managers', 'App\Http\Controllers\backEnd\superAdmin\companyManager\ManagerController@index');
	Route::match(['get', 'post'], '/company-manager/add', 'App\Http\Controllers\backEnd\superAdmin\companyManager\ManagerController@add');
	Route::match(['get', 'post'], '/company-manager/edit/{id}', 'App\Http\Controllers\backEnd\superAdmin\companyManager\ManagerController@edit');
	Route::match(['get', 'post'], '/company-manager/delete/{id}', 'App\Http\Controllers\backEnd\superAdmin\companyManager\ManagerController@delete');
	Route::match(['get', 'post'], '/company-manager/send-set-pass-link/{user_id}', 'App\Http\Controllers\backEnd\superAdmin\companyManager\ManagerController@send_user_set_pass_link_mail');
	Route::match(['get', 'post'], '/company-manager/check_username_unique', 'App\Http\Controllers\backEnd\UserController@check_username_exist');
	Route::post('/companyManager/change-status', [ManagerController::class, 'manager_change_status']);


	//backEnd SystemAdmin in SuperAdmin 
	Route::match(['get', 'post'], '/system-admins', 'App\Http\Controllers\backEnd\superAdmin\AdminController@system_admins');
	Route::match(['get', 'post'], '/system-admin/add', 'App\Http\Controllers\backEnd\superAdmin\AdminController@add');
	Route::match(['get', 'post'], '/system-admin/edit/{sa_id}', 'App\Http\Controllers\backEnd\superAdmin\AdminController@edit');
	Route::match(['get', 'post'], '/system-admin/delete/{sa_id}', 'App\Http\Controllers\backEnd\superAdmin\AdminController@delete');
	Route::match(['get', 'post'], '/system-admin/send-set-pass-link/{system_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\AdminController@send_system_admin_set_pass_link_mail');
	Route::match(['get', 'post'], '/system-admin/package/detail/{sa_id}', 'App\Http\Controllers\backEnd\superAdmin\AdminController@package_detail');

	//backEnd company charges in SuperAdmin
	Route::match(['get', 'post'], '/company-charges', 'App\Http\Controllers\backEnd\superAdmin\CompanyChargesController@index');
	Route::match(['get', 'post'], '/company-charge/edit/{package_id}', 'App\Http\Controllers\backEnd\superAdmin\CompanyChargesController@edit');
	Route::match(['get', 'post'], '/company-charge/validate-home-range', 'App\Http\Controllers\backEnd\superAdmin\CompanyChargesController@validate_home_range');
	Route::match(['get', 'post'], '/company-charge/validate-range-gap', 'App\Http\Controllers\backEnd\superAdmin\CompanyChargesController@validate_range_gap');

	//backEnd SystemAdmin in SuperAdmin Home
	Route::match(['get', 'post'], '/system-admin/homes/{system_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@index');
	Route::match(['get', 'post'], '/system-admin/homes/add/{system_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@add');
	Route::match(['get', 'post'], '/system-admin/home/edit/{home_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@edit');
	Route::match(['get', 'post'], '/system-admin/home/delete/{home_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@delete');
	Route::match(['get', 'post'], '/system-admin/home/undo-delete/{home_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeController@undo_delete');
	Route::match(['get', 'post'], '/system-admin/home/company-package-type', 'App\Http\Controllers\backEnd\superAdmin\HomeController@company_package_type');
	Route::match(['get', 'post'], '/system-admin/home/card-detail', 'App\Http\Controllers\backEnd\superAdmin\HomeController@card_detail_save');


	Route::match(['get', 'post'], '/users', 'App\Http\Controllers\backEnd\UserController@users');
	Route::match(['get', 'post'], '/users/add', 'App\Http\Controllers\backEnd\UserController@add');
	Route::match(['get', 'post'], '/users/edit/{id}', 'App\Http\Controllers\backEnd\UserController@edit');
	Route::match(['get', 'post'], '/users/delete/{id}', 'App\Http\Controllers\backEnd\UserController@delete');
	Route::get('/users/certificates/delete/{id}', 'App\Http\Controllers\backEnd\UserController@delete_certificates');
	Route::match(['get', 'post'], '/users/send-set-pass-link/{user_id}', 'App\Http\Controllers\backEnd\UserController@send_user_set_pass_link_mail');

	// Ram 04/07/2024 here paths for Job Manageent
	Route::match(['get', 'post'], 'jobs_list', 'App\Http\Controllers\backEnd\JobsController@jobs_list');
	Route::post('/job_status_change', 'App\Http\Controllers\backEnd\JobsController@job_status_change');
	Route::post('/job_delete', 'App\Http\Controllers\backEnd\JobsController@job_delete');
	Route::get('/job_add', 'App\Http\Controllers\backEnd\JobsController@job_add');
	Route::post('/job_save_data', 'App\Http\Controllers\backEnd\JobsController@job_save_data');
	Route::match(['get', 'post'], '/jobs_type_list', 'App\Http\Controllers\backEnd\JobsController@jobs_type_list');
	Route::post('/job_type_status_change', 'App\Http\Controllers\backEnd\JobsController@job_type_status_change');
	Route::post('/job_type_delete', 'App\Http\Controllers\backEnd\JobsController@job_type_delete');
	Route::get('/job_type_add', 'App\Http\Controllers\backEnd\JobsController@job_type_add');
	Route::post('/job_type_save_data', 'App\Http\Controllers\backEnd\JobsController@job_type_save_data');
	Route::match(['get', 'post'], '/work_flow_list', 'App\Http\Controllers\backEnd\JobsController@work_flow_list');
	Route::post('/wrok_flow_status_change', 'App\Http\Controllers\backEnd\JobsController@wrok_flow_status_change');
	Route::post('/wrok_flow_delete', 'App\Http\Controllers\backEnd\JobsController@wrok_flow_delete');
	Route::get('/work_flow_add', 'App\Http\Controllers\backEnd\JobsController@work_flow_add');
	Route::post('/workflow_save_data', 'App\Http\Controllers\backEnd\JobsController@workflow_save_data');
	Route::post('/Workflow_notification_save', 'App\Http\Controllers\backEnd\JobsController@Workflow_notification_save');
	Route::match(['get', 'post'], '/product_category', 'App\Http\Controllers\backEnd\JobsController@product_category');
	Route::post('/product_cat_status_change', 'App\Http\Controllers\backEnd\JobsController@product_cat_status_change');
	Route::post('/product_cat_delete', 'App\Http\Controllers\backEnd\JobsController@product_cat_delete');
	Route::get('/product_category_add', 'App\Http\Controllers\backEnd\JobsController@product_category_add');
	Route::post('/product_cat_save_data', 'App\Http\Controllers\backEnd\JobsController@product_cat_save_data');
	Route::match(['get', 'post'], '/product_list', 'App\Http\Controllers\backEnd\JobsController@product_list');
	Route::post('/product_status_change', 'App\Http\Controllers\backEnd\JobsController@product_status_change');
	Route::post('/product_delete', 'App\Http\Controllers\backEnd\JobsController@product_delete');
	Route::match(['get', 'post'], '/account_codes', 'App\Http\Controllers\backEnd\JobsController@account_codes');
	Route::get('/account_code_add', 'App\Http\Controllers\backEnd\JobsController@account_code_add');
	Route::post('/account_save_data', 'App\Http\Controllers\backEnd\JobsController@account_save_data');
	Route::post('/account_status_change', 'App\Http\Controllers\backEnd\JobsController@account_status_change');
	Route::post('/account_delete', 'App\Http\Controllers\backEnd\JobsController@account_delete');
	Route::match(['get', 'post'], '/tax_rate', 'App\Http\Controllers\backEnd\JobsController@tax_rate');
	Route::get('/tax_add', 'App\Http\Controllers\backEnd\JobsController@tax_add');
	Route::post('/tax_save_data', 'App\Http\Controllers\backEnd\JobsController@tax_save_data');
	Route::post('/tax_status_change', 'App\Http\Controllers\backEnd\JobsController@tax_status_change');
	Route::post('/tax_delete', 'App\Http\Controllers\backEnd\JobsController@tax_delete');
	Route::get('/product_add', 'App\Http\Controllers\backEnd\JobsController@product_add');
	Route::post('/product_save_data', 'App\Http\Controllers\backEnd\JobsController@product_save_data');
	Route::get('/catalogue', 'App\Http\Controllers\backEnd\JobsController@catalogue');
	Route::post('/save_catalogue', 'App\Http\Controllers\backEnd\JobsController@save_catalogue');
	Route::get('/getCategoryList', 'App\Http\Controllers\backEnd\JobsController@getCategoryList');
	Route::post('/getProduct_List', 'App\Http\Controllers\backEnd\JobsController@getProduct_List');
	Route::get('/getProductListCounts', 'App\Http\Controllers\backEnd\JobsController@getProductListCounts');
	Route::post('/getProductSelectId', 'App\Http\Controllers\backEnd\JobsController@getProductSelectId');
	Route::post('/ProductCataloguePriceList', 'App\Http\Controllers\backEnd\JobsController@ProductCataloguePriceList');
	Route::post('/ProductGroupProductsdetail', 'App\Http\Controllers\backEnd\JobsController@ProductGroupProductsdetail');
	Route::match(['get', 'post'], '/product_group', 'App\Http\Controllers\backEnd\JobsController@product_group');
	Route::post('/save_productGroup', 'App\Http\Controllers\backEnd\JobsController@save_productGroup');
	Route::post('/ProductGroupProductsList', 'App\Http\Controllers\backEnd\JobsController@ProductGroupProductsList');
	Route::post('/supplier_result', 'App\Http\Controllers\backEnd\JobsController@supplier_result');
	Route::match(['get', 'post'], '/customer_list', 'App\Http\Controllers\backEnd\JobsController@customer_list');
	Route::match(['get', 'post'], '/project_list', 'App\Http\Controllers\backEnd\JobsController@project_list');
	Route::get('/project_add', 'App\Http\Controllers\backEnd\JobsController@project_add');
	Route::post('/project_save_data', 'App\Http\Controllers\backEnd\JobsController@project_save_data');
	Route::post('/project_status_change', 'App\Http\Controllers\backEnd\JobsController@project_status_change');
	Route::post('/project_delete', 'App\Http\Controllers\backEnd\JobsController@project_delete');
	Route::post('/search_value', 'App\Http\Controllers\backEnd\JobsController@search_value');
	Route::post('/get_customer_details', 'App\Http\Controllers\backEnd\JobsController@get_customer_details');
	Route::post('/get_delete_jobproduct', 'App\Http\Controllers\backEnd\JobsController@get_delete_jobproduct');
	// Route::post('/search_value_front','App\Http\Controllers\backEnd\JobsController@search_value_front');
	// Job Recurring Start
	Route::match(['get', 'post'], '/job_recurring_list', 'App\Http\Controllers\backEnd\JobsController@job_recurring_list');

	// Backend Job Controller
	Route::match(['get', 'post'], '/job_title', 'App\Http\Controllers\backEnd\JobsController@job_title');
	Route::get('/job_title_add', 'App\Http\Controllers\backEnd\JobsController@job_title_add');
	Route::post('/job_title_save', 'App\Http\Controllers\backEnd\JobsController@job_title_save');
	Route::post('/job_title_status_change', 'App\Http\Controllers\backEnd\JobsController@job_title_status_change');
	Route::post('/job_title_delete', 'App\Http\Controllers\backEnd\JobsController@job_title_delete');
	Route::match(['get', 'post'], '/job_appointment_type', 'App\Http\Controllers\backEnd\JobsController@job_appointment_type');
	Route::get('/job_appointment_type_add', 'App\Http\Controllers\backEnd\JobsController@job_appointment_type_add');
	Route::post('/job_appointment_type_save', 'App\Http\Controllers\backEnd\JobsController@job_appointment_type_save');
	Route::post('/job_appointment_type_status_change', 'App\Http\Controllers\backEnd\JobsController@job_appointment_type_status_change');
	Route::post('/job_appointment_type_delete', 'App\Http\Controllers\backEnd\JobsController@job_appointment_type_delete');
	Route::match(['get', 'post'], '/job_rejection_categories', 'App\Http\Controllers\backEnd\JobsController@job_rejection_categories');
	Route::get('/job_rejection_category_add', 'App\Http\Controllers\backEnd\JobsController@job_rejection_category_add');
	Route::post('/job_rejection_category_save', 'App\Http\Controllers\backEnd\JobsController@job_rejection_category_save');
	Route::post('/job_rejection_category_status_change', 'App\Http\Controllers\backEnd\JobsController@job_rejection_category_status_change');
	Route::post('/job_rejection_category_delete', 'App\Http\Controllers\backEnd\JobsController@job_rejection_category_delete');

	// Backend CustomerController
	Route::controller(BackendCustomerController::class)->group(function () {
		Route::match(['get', 'post'], '/customers', 'customers');
		Route::get('customer_add', 'customer_add');
		Route::match(['get', 'post'], '/customer_type', 'customer_type');
		Route::get('/customer_type_add', 'customer_type_add');
		Route::post('/customer_type_save', 'customer_type_save');
		Route::post('/customer_type_status_change', 'customer_type_status_change');
		Route::post('/customer_type_delete', 'customer_type_delete');
		Route::post('/customer_save', 'customer_save');
		Route::post('/customer_contact_save', 'customer_contact_save');
		Route::post('/customer_site_save', 'customer_site_save');
		Route::post('/customer_login_save', 'customer_login_save');
		Route::post('/customer_status_change', 'customer_status_change');
		Route::post('/customer_delete', 'customer_delete');
		Route::post('/default_address', 'default_address');
		Route::post('/delete_contact', 'delete_contact');
		Route::post('/delete_site', 'delete_site');
		Route::post('/delete_login', 'delete_login');
		Route::get('/getCustomerList', 'getCustomerList');
	});

	//User TaskAllocation
	Route::match(['get', 'post'], '/user/task-allocations/{user_id}', 'App\Http\Controllers\backEnd\user\TaskAllocationController@index');
	Route::match(['get', 'post'], '/user/task-allocation/add/{user_id}', 'App\Http\Controllers\backEnd\user\TaskAllocationController@add');
	Route::match(['get', 'post'], '/user/task-allocation/edit/{u_task_alloc_id}', 'App\Http\Controllers\backEnd\user\TaskAllocationController@edit');
	Route::match(['get', 'post'], '/user/task-allocation/delete/{u_task_alloc_id}', 'App\Http\Controllers\backEnd\user\TaskAllocationController@delete');

	//User SickLeave
	Route::match(['get', 'post'], '/user/sick-leaves/{staff_member_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@index');
	Route::match(['get', 'post'], '/user/sick-leave/add/{staff_member_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@add');
	Route::match(['get', 'post'], '/user/sick-leave/edit/{u_sick_leave_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@edit');
	Route::get('/user/sick-leave/delete/{u_sick_leave_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@delete');
	Route::match(['get', 'post'], '/user/sick-leave/sanction/{u_sick_leave_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@sanction_leave');
	Route::match(['get', 'post'], '/user/sick-leave/user-list/{home_id}/{user_id}', 'App\Http\Controllers\backEnd\user\SickLeaveController@staff_user_list');
	Route::match(['get', 'post'], '/user/rota', 'App\Http\Controllers\backEnd\user\SickLeaveController@get_staff_rota');

	//User Annual Leave
	Route::match(['get', 'post'], '/user/annual-leaves/{staff_member_id}', 'App\Http\Controllers\backEnd\user\AnnualLeaveController@index');
	Route::match(['get', 'post'], '/user/annual-leave/add/{staff_member_id}', 'App\Http\Controllers\backEnd\user\AnnualLeaveController@add');
	Route::match(['get', 'post'], '/user/annual-leave/edit/{u_annual_leave_id}', 'App\Http\Controllers\backEnd\user\AnnualLeaveController@edit');
	Route::get('/user/annual-leave/delete/{u_sick_leave_id}', 'App\Http\Controllers\backEnd\user\AnnualLeaveController@delete');

	//backEnd ServiceUserController
	Route::match(['get', 'post'], '/service-users', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@index');
	Route::match(['get', 'post'], '/service-users/add', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@add');
	Route::match(['get', 'post'], '/service-users/edit/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@edit');
	Route::match(['get', 'post'], '/service-users/delete/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@delete');
	Route::get('/service-users/send-set-pass-link/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@send_set_pass_link_mail');

	//backEnd Childs Care History
	Route::match(['get', 'post'], '/service-users/care-history/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\CareHistoryController@index');
	Route::match(['get', 'post'], '/service-users/care-history/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\CareHistoryController@add');
	Route::match(['get', 'post'], '/service-users/care-history/edit/{care_id}', 'App\Http\Controllers\backEnd\serviceUser\CareHistoryController@edit');
	Route::match(['get', 'post'], '/service-users/care-history/delete/{care_id}', 'App\Http\Controllers\backEnd\serviceUser\CareHistoryController@delete');
	Route::match(['get', 'post'], '/service-users/care-history/delete-file/{su_care_history_id}', 'App\Http\Controllers\backEnd\serviceUser\CareHistoryController@delete_hist_file');

	//backEnd careTeam in serviceUser
	Route::match(['get', 'post'], '/service-user/careteam/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\CareTeamController@team_list');
	Route::match(['get', 'post'], '/service-user/careteam/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\CareTeamController@add');
	Route::match(['get', 'post'], '/service-user/careteam/delete/{team_member_id}', 'App\Http\Controllers\backEnd\serviceUser\CareTeamController@delete');
	Route::match(['get', 'post'], '/service-user/careteam/edit/{team_member_id}', 'App\Http\Controllers\backEnd\serviceUser\CareTeamController@edit');

	//backEnd serviceUser contacts
	Route::match(['get', 'post'], '/service-users/contacts/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ContactsController@team_list');
	Route::match(['get', 'post'], '/service-users/contacts/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ContactsController@add');
	Route::match(['get', 'post'], '/service-users/contacts/delete/{team_member_id}', 'App\Http\Controllers\backEnd\serviceUser\ContactsController@delete');
	Route::match(['get', 'post'], '/service-users/contacts/edit/{team_member_id}', 'App\Http\Controllers\backEnd\serviceUser\ContactsController@edit');

	//backEnd ServiceUser Moods
	Route::match(['get', 'post'], '/service-user/moods/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\MoodController@index');
	Route::match(['get', 'post'], '/service-user/mood/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\MoodController@add');
	Route::match(['get', 'post'], '/service-user/mood/edit/{su_mood_id}', 'App\Http\Controllers\backEnd\serviceUser\MoodController@edit');
	Route::match(['get', 'post'], '/service-user/mood/delete/{su_mood_id}', 'App\Http\Controllers\backEnd\serviceUser\MoodController@delete');

	//backEnd ServiceUser External Service
	Route::match(['get', 'post'], '/service-user/external-service/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ExternalServiceController@index');
	Route::match(['get', 'post'], '/service-user/external-service/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\ExternalServiceController@add');
	Route::match(['get', 'post'], '/service-user/external-service/edit/{ext_service_id}', 'App\Http\Controllers\backEnd\serviceUser\ExternalServiceController@edit');
	Route::match(['get', 'post'], '/service-user/external-service/delete/{ext_service_id}', 'App\Http\Controllers\backEnd\serviceUser\ExternalServiceController@delete');


	//backEnd ServiceUser DailyLog
	Route::match(['get', 'post'], '/service-user/logbooks/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\LogBookController@index');
	Route::get('/service-user/logbook/view/{log_book_id}', 'App\Http\Controllers\backEnd\serviceUser\LogBookController@view');
	Route::get('/service-user/logbook/download', 'App\Http\Controllers\backEnd\serviceUser\LogBookController@download');

	//backEnd ServiceUser Independent LivingSkills
	Route::match(['get', 'post'], '/service-user/living-skills/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\LivingSkillController@index');
	Route::get('/service-user/living-skill/view/{su_living_skill_id}', 'App\Http\Controllers\backEnd\serviceUser\LivingSkillController@view');

	//backEnd ServiceUser Calendar
	Route::match(['get', 'post'], '/service-user/calendar/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\CalendarController@index');
	Route::match(['get', 'post'], '/service-user/calendar/event/view', 'App\Http\Controllers\backEnd\serviceUser\CalendarController@event_detail');

	//backEnd ServiceUser RMP
	Route::match(['get', 'post'], '/service-user/rmps/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\RmpController@index');
	Route::match(['get', 'post'], '/service-user/rmp/view/{d_rmp_form_id}', 'App\Http\Controllers\backEnd\serviceUser\RmpController@view');

	//backEnd ServiceUser BMP
	Route::match(['get', 'post'], '/service-user/bmps/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\BmpController@index');
	Route::match(['get', 'post'], '/service-user/bmp/view/{d_bmp_form_id}', 'App\Http\Controllers\backEnd\serviceUser\BmpController@view');

	//backEnd ServiceUser Risk
	Route::match(['get', 'post'], '/service-user/risks/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\RiskController@index');
	Route::match(['get', 'post'], '/service-user/risk/view/{su_risk_id}', 'App\Http\Controllers\backEnd\serviceUser\RiskController@view');

	//backEnd ServiceUser Earning Scheme
	Route::match(['get', 'post'], '/service-user/earning-schemes/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@index');
	Route::match(['get', 'post'], '/service/earning-scheme/view_incentive/{earning_category_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@view_incentive');
	Route::match(['get', 'post'], '/service/earning-scheme/daily-records/{su_id}/{label_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@daily_record');
	Route::match(['get', 'post'], '/service/earning-scheme/daily-record/view/{daily_record_id}/{label_type}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@daily_record_view');
	Route::match(['get', 'post'], '/service/earning-scheme/living-skills/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@living_skill');
	Route::match(['get', 'post'], '/service/earning-scheme/living_skill/view/{daily_record_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@living_skill_view');
	Route::match(['get', 'post'], '/service/earning-scheme/education-records/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@education_record');
	Route::match(['get', 'post'], '/service/earning-scheme/education-record/view/{education_record_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@education_record_view');
	Route::match(['get', 'post'], '/service/earning-scheme/mfcs/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@mfc');
	Route::match(['get', 'post'], '/service/earning-scheme/mfc/view/{d_mfc_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@mfc_view');
	Route::match(['get', 'post'], '/service-user/earning-scheme-label/add/{service_user_id}/{earning_scheme_label_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@add_earning_scheme_label');
	Route::match(['get', 'post'], '/service-user/earning-scheme-label/delete/{service_user_id}/{earning_scheme_label_id}', 'App\Http\Controllers\backEnd\serviceUser\EarningSchemeController@delete_earning_scheme_label');

	//backEnd incidentReport in serviceUser service-users/incident-reports (not)
	Route::match(['get', 'post'], '/service-user/incident-reports/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\IncidentReportController@index');
	Route::match(['get', 'post'], '/service-user/incident/add/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\IncidentReportController@add');
	Route::match(['get', 'post'], '/service-user/incident/edit/{inc_rep_id}', 'App\Http\Controllers\backEnd\serviceUser\IncidentReportController@edit');
	Route::get('/service-user/incident/delete/{inc_rep_id}', 'App\Http\Controllers\backEnd\serviceUser\IncidentReportController@delete');

	//backEnd Agent
	Route::match(['get', 'post'], '/agents', 'App\Http\Controllers\backEnd\AgentController@agents');
	Route::match(['get', 'post'], '/agents/add', 'App\Http\Controllers\backEnd\AgentController@add');
	Route::match(['get', 'post'], '/agents/edit/{agent_id}', 'App\Http\Controllers\backEnd\AgentController@edit');
	Route::match(['get', 'post'], '/agents/delete/{agent_id}', 'App\Http\Controllers\backEnd\AgentController@delete');
	Route::match(['get', 'post'], '/agents/send-set-pass-link/{agent_id}', 'App\Http\Controllers\backEnd\AgentController@send_user_set_pass_link_mail');
	//agent access rights
	Route::get('agents/access-rights/{agent_id}', 'App\Http\Controllers\backEnd\AccessRightController@agent_index');
	Route::match(['get', 'post'], 'agents/access-right/update', 'App\Http\Controllers\backEnd\AccessRightController@agent_update');

	//backEnd DailyRecord
	Route::match(['get', 'post'], '/daily-record', 'App\Http\Controllers\backEnd\DailyRecordController@index');
	Route::match(['get', 'post'], '/daily-record/add', 'App\Http\Controllers\backEnd\DailyRecordController@add');
	Route::match(['get', 'post'], '/daily-record/edit/{id}', 'App\Http\Controllers\backEnd\DailyRecordController@edit');
	Route::match(['get', 'post'], '/daily-record/delete/{id}', 'App\Http\Controllers\backEnd\DailyRecordController@delete');

	//backEnd Daily Record Score
	Route::match(['get', 'post'], '/daily-record-scores', 'App\Http\Controllers\backEnd\DailyRecordScoreController@index');
	Route::match(['get', 'post'], '/daily-record-score/edit/{dr_score_id}', 'App\Http\Controllers\backEnd\DailyRecordScoreController@edit');

	//backEnd Risks
	Route::match(['get', 'post'], '/risk', 'App\Http\Controllers\backEnd\RiskController@risk');
	Route::match(['get', 'post'], '/risk/add', 'App\Http\Controllers\backEnd\RiskController@add');
	Route::match(['get', 'post'], '/risk/edit/{id}', 'App\Http\Controllers\backEnd\RiskController@edit');
	Route::match(['get', 'post'], '/risk/delete/{id}', 'App\Http\Controllers\backEnd\RiskController@delete');

	//backEnd EarningScheme 
	Route::match(['get', 'post'], '/earning-scheme', 'App\Http\Controllers\backEnd\EarningSchemeController@earning_scheme');
	Route::match(['get', 'post'], '/earning-scheme/add', 'App\Http\Controllers\backEnd\EarningSchemeController@add');
	Route::match(['get', 'post'], '/earning-scheme/edit/{id}', 'App\Http\Controllers\backEnd\EarningSchemeController@edit');
	Route::match(['get', 'post'], '/earning-scheme/delete/{id}', 'App\Http\Controllers\backEnd\EarningSchemeController@delete');

	//backEnd EarningScheme -- Incentive
	Route::match(['get', 'post'], '/earning-scheme/incentive/{id}', 'App\Http\Controllers\backEnd\IncentiveController@incentive');
	Route::match(['get', 'post'], '/earning-scheme/incentive/add/{id}', 'App\Http\Controllers\backEnd\IncentiveController@add');
	Route::match(['get', 'post'], '/earning-scheme/incentive/edit/{id}', 'App\Http\Controllers\backEnd\IncentiveController@edit');
	Route::match(['get', 'post'], '/earning-scheme/incentive/delete/{id}', 'App\Http\Controllers\backEnd\IncentiveController@delete');

	//backEnd Incentive
	Route::match(['get', 'post'], '/earning-scheme/incentive/{id}', 'App\Http\Controllers\backEnd\IncentiveController@incentive');

	//Earning scheme labels
	Route::any('earning-scheme-labels', 'App\Http\Controllers\backEnd\EarningSchemeLabelController@index');
	Route::any('/earning-scheme-label/add', 'App\Http\Controllers\backEnd\EarningSchemeLabelController@add');
	Route::any('/earning-scheme-label/edit/{label_id}', 'App\Http\Controllers\backEnd\EarningSchemeLabelController@edit');
	Route::any('/earning-scheme-label/delete/{label_id}', 'App\Http\Controllers\backEnd\EarningSchemeLabelController@delete');

	//backEnd Homelist
	Route::match(['get', 'post'], '/homelist', 'App\Http\Controllers\backEnd\homeManage\HomeController@index');
	Route::match(['get', 'post'], '/homelist/add', 'App\Http\Controllers\backEnd\homeManage\HomeController@add');
	Route::match(['get', 'post'], '/homelist/edit/{home_id}', 'App\Http\Controllers\backEnd\homeManage\HomeController@edit');
	Route::match(['get', 'post'], '/homelist/delete/{home_id}', 'App\Http\Controllers\backEnd\homeManage\HomeController@delete');
	Route::match(['get', 'post'], '/homelist/undo-delete/{home_id}', 'App\Http\Controllers\backEnd\homeManage\HomeController@undo_delete');
	Route::match(['get', 'post'], '/homelist/company-package-type', 'App\Http\Controllers\backEnd\homeManage\HomeController@company_package_type');
	Route::match(['get', 'post'], '/homelist/payment/success/{admin_id}', 'App\Http\Controllers\backEnd\homeManage\HomeController@success');

	//backend homelist-admins		
	Route::match(['get', 'post'], '/homelist/home-admin/{home_id}', 'App\Http\Controllers\backEnd\homeManage\AdminController@index');
	Route::match(['get', 'post'], '/homelist/home-admin/add/{home_id}', 'App\Http\Controllers\backEnd\homeManage\AdminController@add');
	Route::match(['get', 'post'], '/homelist/home-admin/edit/{home_admin_id}', 'App\Http\Controllers\backEnd\homeManage\AdminController@edit');
	Route::match(['get', 'post'], '/homelist/home-admin/delete/{home_admin_id}', 'App\Http\Controllers\backEnd\homeManage\AdminController@delete');
	Route::match(['get', 'post'], '/homelist/home-admin/send-set-pass-link/{home_admin_id}', 'App\Http\Controllers\backEnd\homeManage\AdminController@send_set_password_link_mail');

	//access rights
	Route::get('users/access-rights/{user_id}', 'App\Http\Controllers\backEnd\AccessRightController@index');
	Route::match(['get', 'post'], 'users/access-right/update', 'App\Http\Controllers\backEnd\AccessRightController@update');

	//support ticket
	Route::match(['get', 'post'], '/support-ticket', 'App\Http\Controllers\backEnd\SupportTicketController@index');
	Route::match(['get', 'post'], '/support-ticket/add/msg', 'App\Http\Controllers\backEnd\SupportTicketController@add_ticket_mesg');
	Route::match(['get', 'post'], '/support-ticket/view/{ticket_id}', 'App\Http\Controllers\backEnd\SupportTicketController@view_ticket');
	// Route::match(['get','post'], '/support-ticket/edit/{user_id}', 'App\Http\Controllers\backEnd\SupportTicketController@edit');
	Route::match(['get', 'post'], '/support-ticket/delete/{user_id}', 'App\Http\Controllers\backEnd\SupportTicketController@delete');

	//backEnd Placement Plan	
	Route::match(['get', 'post'], '/placement-plan', 'App\Http\Controllers\backEnd\PlacementPlanController@index');
	Route::match(['get', 'post'], '/placement-plan/add', 'App\Http\Controllers\backEnd\PlacementPlanController@add');
	Route::match(['get', 'post'], '/placement-plan/edit/{target_id}', 'App\Http\Controllers\backEnd\PlacementPlanController@edit');
	Route::match(['get', 'post'], '/placement-plan/delete/{target_id}', 'App\Http\Controllers\backEnd\PlacementPlanController@delete');

	//form-builder
	Route::match(['get', 'post'], '/form-builder', 'App\Http\Controllers\backEnd\systemManage\FormBuilderController@index');
	Route::match(['get', 'post'], '/form-builder/add', 'App\Http\Controllers\backEnd\systemManage\FormBuilderController@add');

	Route::match(['get', 'post'], '/form-builder/edit/{form_id}', 'App\Http\Controllers\backEnd\systemManage\FormBuilderController@edit');
	Route::match(['get', 'post'], '/form-builder/delete/{form_id}', 'App\Http\Controllers\backEnd\systemManage\FormBuilderController@delete');

	/*//form-builder
	Route::match(['get','post'], '/form-builder', 'App\Http\Controllers\backEnd\FormBuilderController@index');	
	Route::match(['get','post'], '/form-builder/add', 'App\Http\Controllers\backEnd\FormBuilderController@add');	
	Route::match(['get','post'], '/form-builder/view/{form_id}', 'App\Http\Controllers\backEnd\FormBuilderController@view');	
	Route::match(['get','post'], '/form-builder/edit/{form_id}', 'App\Http\Controllers\backEnd\FormBuilderController@edit');	
	Route::match(['get','post'], '/form-builder/delete/{form_id}', 'App\Http\Controllers\backEnd\FormBuilderController@delete');*/

	// labels
	Route::get('/labels', 'App\Http\Controllers\backEnd\HomeLabelController@index');
	Route::get('/label/view/{label_tag}', 'App\Http\Controllers\backEnd\HomeLabelController@view');
	Route::post('/label/edit', 'App\Http\Controllers\backEnd\HomeLabelController@edit');

	// categories
	Route::get('/categories', 'App\Http\Controllers\backEnd\HomeCategoriesController@index');
	Route::get('/categories/view/{category_tag}', 'App\Http\Controllers\backEnd\HomeCategoriesController@view');
	Route::post('/categories/edit', 'App\Http\Controllers\backEnd\HomeCategoriesController@edit');
	Route::match(['get', 'post'], '/categories/add', 'App\Http\Controllers\backEnd\HomeCategoriesController@add');
	// Route::get('/categories/add', 'App\Http\Controllers\backEnd\HomeCategoriesController@add');	

	// Backend unique username for user,Child,agent & admin
	Route::match(['get', 'post'], '/users/check_username_unique', 'App\Http\Controllers\backEnd\UserController@check_username_exist');
	Route::match(['get', 'post'], '/service-users/check_username_exists', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@check_username_exist');

	Route::match(['get', 'post'], '/service-users/check-serviceuser-email-exists', 'App\Http\Controllers\backEnd\serviceUser\ServiceUserController@check_serviceuser_email_exists');

	Route::match(['get', 'post'], '/system-admin/check_user_username_exists', 'App\Http\Controllers\backEnd\superAdmin\AdminController@check_username_exist');
	Route::match(['get', 'post'], '/system-admin/check_user_company_exists', 'App\Http\Controllers\backEnd\superAdmin\AdminController@check_company_exist');

	Route::match(['get', 'post'], '/agents/check_username_unique', 'App\Http\Controllers\backEnd\AgentController@check_username_exist');

	// migrations 
	Route::match(['get', 'post'], '/service-users/migrations/{service_user_id}', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@index');
	Route::get('/service-users/migration/send-request/{service_user_id}', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@show_form');
	Route::post('/service-users/migration/send-request', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@save_request');

	Route::get('/service-users/migration/view/{su_migration_id}', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@view');
	Route::get('/service-users/migration/cancel-request/{su_migration_id}', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@cancel_request');
	//get homes by id
	Route::get('/migration/get-company/{admin_id}', 'App\Http\Controllers\backEnd\serviceUser\MigrationController@get_homes');

	//backEnd ModifyRequest
	Route::match(['get', 'post'], '/modification-requests', 'App\Http\Controllers\backEnd\ModificationRequestController@index');
	Route::match(['get', 'post'], '/modification-request/edit/{request_id}', 'App\Http\Controllers\backEnd\ModificationRequestController@edit');
	Route::match(['get', 'post'], '/modification-request/delete/{request_id}', 'App\Http\Controllers\backEnd\ModificationRequestController@delete');

	//backEnd LivingSkills 
	Route::match(['get', 'post'], '/living-skill', 'App\Http\Controllers\backEnd\LivingSkillController@index');
	Route::match(['get', 'post'], '/living-skill/add', 'App\Http\Controllers\backEnd\LivingSkillController@add');
	Route::match(['get', 'post'], '/living-skill/edit/{skill_id}', 'App\Http\Controllers\backEnd\LivingSkillController@edit');
	Route::match(['get', 'post'],  '/living-skill/delete/{skill_id}', 'App\Http\Controllers\backEnd\LivingSkillController@delete');

	//backEnd MFC 
	Route::match(['get', 'post'], '/mfc-records', 'App\Http\Controllers\backEnd\MFCController@index');
	Route::match(['get', 'post'], '/mfc/add', 'App\Http\Controllers\backEnd\MFCController@add');
	Route::match(['get', 'post'], '/mfc/edit/{mfc_id}', 'App\Http\Controllers\backEnd\MFCController@edit');
	Route::get('/mfc/delete/{mfc_id}', 'App\Http\Controllers\backEnd\MFCController@delete');

	//backEnd Education Training
	Route::match(['get', 'post'], '/education-trainings', 'App\Http\Controllers\backEnd\EducationTrainingController@index');
	Route::match(['get', 'post'], '/education-training/add', 'App\Http\Controllers\backEnd\EducationTrainingController@add');
	Route::match(['get', 'post'], '/education-training/edit/{edu_tr_id}', 'App\Http\Controllers\backEnd\EducationTrainingController@edit');
	Route::match(['get', 'post'], '/education-training/delete/{edu_tr_id}', 'App\Http\Controllers\backEnd\EducationTrainingController@delete');

	//backEnd CareTeam-JobTitle
	Route::match(['get', 'post'], '/care-team-job-titles', 'App\Http\Controllers\backEnd\homeManage\CareTeamJobTitleController@index');
	Route::match(['get', 'post'], '/care-team-job-title/add', 'App\Http\Controllers\backEnd\homeManage\CareTeamJobTitleController@add');
	Route::match(['get', 'post'], '/care-team-job-title/edit/{job_title_id}', 'App\Http\Controllers\backEnd\homeManage\CareTeamJobTitleController@edit');
	Route::match(['get', 'post'], '/care-team-job-title/delete/{job_title_id}', 'App\Http\Controllers\backEnd\homeManage\CareTeamJobTitleController@delete');

	//backEnd Moods
	Route::match(['get', 'post'], '/moods', 'App\Http\Controllers\backEnd\homeManage\MoodController@index');
	Route::match(['get', 'post'], '/mood/add', 'App\Http\Controllers\backEnd\homeManage\MoodController@add');
	Route::match(['get', 'post'], '/mood/edit/{mood_id}', 'App\Http\Controllers\backEnd\homeManage\MoodController@edit');
	Route::match(['get', 'post'], '/mood/delete/{mood_id}', 'App\Http\Controllers\backEnd\homeManage\MoodController@delete');

	//Access levels
	Route::get('/home/access-levels', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@index');
	Route::match(['get', 'post'], '/home/access-level/add', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@add');
	Route::match(['get', 'post'], '/home/access-level/edit/{access_level_id}', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@edit');
	Route::match(['get', 'post'], '/home/access-level/delete/{access_level_id}', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@delete');
	Route::get('/home/access-level/rights/view/{access_level_id}', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@view_rights');
	Route::post('/home/access-level/rights/update', 'App\Http\Controllers\backEnd\homeManage\AccessLevelController@update_rights');

	//Staff Rota
	// Route::get('/home/rota-shift', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@index');
	// Route::match(['get', 'post'], '/home/rota-shift/view/{shift_id}', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@view');
	// Route::match(['get', 'post'], '/home/rota-shift/edit', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@edit');

	//Staff Rota
	Route::match(['get', 'post'], '/home/rota-shift', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@index');
	//Route::match(['get', 'post'], '/home/rota-shift/view/{shift_id}', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@view');
	Route::match(['get', 'post'], '/home/rota-shift/add', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@add');
	//(not saved)
	Route::match(['get', 'post'], '/home/rota-shift/edit/{shift_id}', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@edit');
	Route::match(['get', 'post'], '/home/rota-shift/delete/{shift_id}', 'App\Http\Controllers\backEnd\homeManage\RotaShiftController@delete');

	//backEnd ContactUs
	// Route::match(['get', 'post'], '/contact-us', 'App\Http\Controllers\backEnd\ContactUsController@index');
	// Route::match(['get', 'post'], '/contact/reply/{contact_id}', 'App\Http\Controllers\backEnd\ContactUsController@reply');
	// Route::match(['get', 'post'], '/contact/delete/{contact_id}', 'App\Http\Controllers\backEnd\ContactUsController@delete');

	//backEnd System Guide Category
	Route::match(['get', 'post'], '/system-guide-category', 'App\Http\Controllers\backEnd\systemGuide\SystemGuideCategoryController@index');
	Route::match(['get', 'post'], '/system-guide/view/{sg_ctgry_id}', 'App\Http\Controllers\backEnd\systemGuide\SystemGuideController@index');

	//backEnd System Guide 
	Route::match(['get', 'post'], '/system-guide/add/{sg_ctgry_id}', 'App\Http\Controllers\backEnd\systemGuide\SystemGuideController@add');
	Route::match(['get', 'post'], '/system-guide/edit/{sys_guide_id}', 'App\Http\Controllers\backEnd\systemGuide\SystemGuideController@edit');
	Route::match(['get', 'post'], '/system-guide/delete/{sys_guide_id}', 'App\Http\Controllers\backEnd\systemGuide\SystemGuideController@delete');

	// backEnd managers
	Route::match(['get', 'post'], '/managers', 'App\Http\Controllers\backEnd\ManagersController@index');
	Route::match(['get', 'post'], '/managers/add', 'App\Http\Controllers\backEnd\ManagersController@add');
	Route::match(['get', 'post'], '/managers/edit/{manager_id}', 'App\Http\Controllers\backEnd\ManagersController@edit');
	Route::match(['get', 'post'], '/managers/delete/{manager_id}', 'App\Http\Controllers\backEnd\ManagersController@delete');
	Route::match(['get', 'post'], '/manager/change-status', 'App\Http\Controllers\backEnd\ManagersController@change_status');
	Route::match(['get', 'post'], '/manager/check-email-exists', 'App\Http\Controllers\backEnd\ManagersController@check_email_exists');
	Route::match(['get', 'post'], '/manager/check-contact-no-exists', 'App\Http\Controllers\backEnd\ManagersController@check_contact_no_exists');
	Route::post('/manager/change-status', [ManagersController::class, 'manager_change_status']);
	Route::get('/managers/send-set-pass-link/{user_id}', [ManagersController::class, 'send_user_set_pass_link_mail']);

	//backEnd Child Dynamic Forms
	Route::match(['get', 'post'], '/service-user/dynamic-forms/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\DynamicFormController@index');
	Route::match(['get', 'post'], '/service-user/dynamic-forms/view/{d_form_id}', 'App\Http\Controllers\backEnd\serviceUser\DynamicFormController@view');
	Route::post('/service-user/dynamic-form/edit', 'App\Http\Controllers\backEnd\serviceUser\DynamicFormController@edit');
	Route::get('/service-user/dynamic-form/delete/{d_form_id}', 'App\Http\Controllers\backEnd\serviceUser\DynamicFormController@delete');

	//backEnd Child File Manager
	Route::match(['get', 'post'], '/service-user/file-managers/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\FileManagerController@index');
	Route::match(['get', 'post'], '/service-user/file-manager/add/{service_user_id}', 'App\Http\Controllers\backEnd\serviceUser\FileManagerController@add');
	Route::get('/service-user/file-manager/delete/{file_id}', 'App\Http\Controllers\backEnd\serviceUser\FileManagerController@delete');

	//backEnd Child My Money History
	Route::match(['get', 'post'], '/service-user/my-money/history/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\MyMoneyHistoryController@index');

	//backEnd Child My Money Request
	Route::match(['get', 'post'], '/service-user/my-money/request/{su_id}', 'App\Http\Controllers\backEnd\serviceUser\MyMoneyRequestController@index');
	Route::match(['get', 'post'], '/service-user/my-money/request-view/{money_request_id}', 'App\Http\Controllers\backEnd\serviceUser\MyMoneyRequestController@view');

	//backEnd Policies&Procedure
	Route::match(['get', 'post'], '/home/policies', 'App\Http\Controllers\backEnd\homeManage\PoliciesController@index');
	Route::match(['get', 'post'], '/home/policies/add', 'App\Http\Controllers\backEnd\homeManage\PoliciesController@add');
	Route::get('/home/policies/delete/{policy_id}', 'App\Http\Controllers\backEnd\homeManage\PoliciesController@delete');
	Route::match(['get', 'post'], '/home/policies/staff/accepted/{policy_id}', 'App\Http\Controllers\backEnd\homeManage\PoliciesController@policy_accepted_staff');
	Route::match(['get', 'post'], '/home/policies/staff/to-agree/{policy_id}', 'App\Http\Controllers\backEnd\homeManage\PoliciesController@to_agree_policy');

	//------ backEnd General Admin ---------- //
	//Agenda Meetings
	Route::match(['get', 'post'], '/general-admin/agenda/meetings', 'App\Http\Controllers\backEnd\generalAdmin\AgendaMeetingController@index');
	Route::match(['get', 'post'], '/general-admin/agenda/meeting-view/{meeting_id}', 'App\Http\Controllers\backEnd\generalAdmin\AgendaMeetingController@view');
	//Petty Cash
	Route::match(['get', 'post'], '/general-admin/petty/cash', 'App\Http\Controllers\backEnd\generalAdmin\PettyCashController@index');
	Route::match(['get', 'post'], '/general-admin/petty/cash-view/{petty_id}', 'App\Http\Controllers\backEnd\generalAdmin\PettyCashController@view');
	//Log Book
	Route::match(['get', 'post'], '/general-admin/log/book', 'App\Http\Controllers\backEnd\generalAdmin\LogBookController@index');
	Route::match(['get', 'post'], '/general-admin/log/book-view/{log_book_id}', 'App\Http\Controllers\backEnd\generalAdmin\LogBookController@view');
	//Weekly Allowance 
	Route::match(['get', 'post'], '/general-admin/allowance/weekly', 'App\Http\Controllers\backEnd\generalAdmin\WeeklyAllowanceController@index');
	//Staff Training
	Route::match(['get', 'post'], '/general-admin/staff/training', 'App\Http\Controllers\backEnd\generalAdmin\StaffTrainingController@index');
	Route::match(['get', 'post'], '/general-admin/staff/training-view/{training_id}', 'App\Http\Controllers\backEnd\generalAdmin\StaffTrainingController@view');

	Route::controller(DepartmentBackendController::class)->group(function () {
		Route::prefix('/general-admin/department')->group(function () {
			Route::get('/', 'index')->name('generalAdmin.department.index');
			Route::get('/add', 'create');
			Route::post('/save', 'store');
			Route::post('/change-status/{id}', 'changeStatus');
			Route::get('/delete/{id}', 'destroy');
			Route::get('/edit/{id}', 'edit');
		});
	});


	// Backend Route for construction customers
	Route::prefix('/sales-finance/customers')->group(function () {
		Route::get('/', 'App\Http\Controllers\backEnd\salesfinance\CustomerController@index');
		Route::get('/add', 'App\Http\Controllers\backEnd\salesfinance\CustomerController@create')->name('customers.create');
		Route::get('/create', 'App\Http\Controllers\backEnd\salesfinance\CustomerController@store')->name('customers.store');
	});

	Route::controller(BackendLeadController::class)->group(function () {

		Route::prefix('sales-finance/leads')->group(function () {
			// Admin leads
			Route::get('/', 'index')->name('leads.index');
			Route::get('/add', 'create')->name('leads.create');
			Route::post('/create', 'store')->name('leads.store');
			Route::get('/edit/{id}', 'edit')->name('leads.edit');
			Route::get('/unassigned', 'index')->name('leads.unassigned');
			Route::get('/actioned', 'index')->name('leads.actioned');
			Route::get('/rejected', 'index')->name('leads.rejected');
			Route::get('/authorization', 'index')->name('leads.authorization');
			Route::get('/convert_to_customer/{id}', 'convert_to_customer')->name('leads.convertCustomer');
			Route::get('/converted', 'index')->name('leads.converted');
			Route::post('/saveLeadNotes', 'save_lead_notes')->name('leads.ajax.saveLeadNotes');
			Route::get('/tasks', 'task_list')->name('leads.list');
			Route::get('/lead_task_delete/{id}', 'lead_task_list_delete');
			Route::get('/authorized/{id}', 'lead_authorized_by_admin');

			// Lead Task 
			Route::post('/saveLeadTasks', 'save_lead_tasks')->name('leads.ajax.saveLeadTasks');
			Route::get('/lead_task/delete/{task}/{lead}', 'lead_task_delete');

			// Lead Status
			Route::get('/lead_status', 'lead_status')->name('leads.lead_status');
			Route::post('/saveLeadStatus', 'saveLeadStatus')->name('leads.ajax.saveLeadStatus');
			Route::get('/lead_status/delete/{id}', 'lead_status_delete');

			// Lead Sources
			Route::get('/lead_sources', 'lead_sources')->name('leads.lead_sources');
			Route::post('/saveLeadSource', 'saveLeadSource')->name('leads.ajax.saveLeadSource');
			Route::get('/lead_source/delete/{id}', 'lead_source_delete');

			// Lead Task Type
			Route::get('/lead_task_type', 'lead_task_type')->name('leads.lead_task_type');
			Route::post('/saveLeadTaskType', 'saveLeadTaskType')->name('leads.ajax.saveLeadTaskType');
			Route::get('/lead_task_type/delete/{id}', 'lead_task_type_delete');
			Route::get('/lead_mark_as_completed/{task}/{lead}', 'lead_mark_as_completed');

			// Lead Notes Type
			Route::get('/lead_notes_type', 'lead_notes_type')->name('leads.lead_notes_type');
			Route::post('/saveLeadNotesType', 'saveLeadNotesType')->name('leads.ajax.saveLeadNoteType');
			Route::get('/lead_note_type/delete/{id}', 'lead_note_type_delete');

			// Lead reject type or resons
			Route::get('/lead_reject_type', 'lead_reject_type')->name('leads.lead_reject_type');
			Route::post('/saveLeadRejectType', 'saveLeadRejectType')->name('leads.ajax.saveLeadRejectType');
			Route::get('/lead_reject_type/delete/{id}', 'lead_reject_type_delete');
			Route::post('/saveLeadRejectReason', 'saveLeadRejectReason')->name('leads.ajax.saveLeadRejectReason');

			// Lead Attachment 
			Route::post('/saveLeadAttachment', 'saveLeadAttachment')->name('leads.ajax.saveLeadAttachment');
			Route::get('/lead_attachments/delete/{attachment}/{lead}', 'lead_attachments_delete');

			// CRM Section Types
			Route::get('/CRM_section_types', 'CRM_section_type')->name('leads.crm_section');
			Route::post('/saveCRMSectionType', 'saveCRMSectionType')->name('leads.ajax.saveCRMSectionType');
			Route::get('/crm_section_type/delete/{id}', 'crm_section_type_delete');
		});
	});

	// Backend Controller for setting in General section
	Route::controller(GeneralController::class)->group(function () {

		Route::prefix('general')->group(function () {
			Route::get('/attachment_types', 'attachment_types_index')->name('attachment_types.view');
			Route::post('/saveAttachmentType', 'saveAttachmentType')->name('general.ajax.saveAttachmentType');
			Route::get('/attachment_type/delete/{id}', 'delete_attachment_type');
			Route::get('/payment_types', 'payment_types');
			Route::post('/savePaymentType', 'SavePaymentType');
			Route::get('/payment_type/delete', 'payment_type_delete');
			Route::get('/regins', 'regins');
			Route::post('/saveRegion', 'saveRegion');
			Route::get('/region/delete', 'region_delete');
			Route::get('/task_types', 'task_types');
			Route::post('saveTaskType', 'saveTaskType');

			Route::get('task_type/delete', 'task_type_delete');
			Route::get('/tags', 'tags');
			Route::post('/saveTag', 'saveTag');
			Route::get('/tags/delete', 'tags_delete');
		});
	});

	//Backend Controller for General Section E
	Route::controller(ExpenseControllerAdmin::class)->group(function () {
		Route::prefix('sales-finance/expense')->group(function () {
			Route::match(['get', 'post'], '/', 'index');
			Route::post('find_project', 'find_project');
			Route::post('find_job', 'find_job');
			Route::post('find_appointment', 'find_appointment');
			Route::post('expense_save', 'expense_save');
			Route::post('expense_edit', 'expense_save');
			Route::post('expense_image_delete', 'expense_image_delete');
			Route::post('expense_delete', 'expense_delete');
			Route::post('expense_reject', 'expense_reject');
		});
	});
	// Route::match(['get', 'post'], '/job_recurring_list', 'App\Http\Controllers\backEnd\JobsController@job_recurring_list');
	// Backend Purchase Order Controller
	Route::controller(Purchase_orderControllerAdmin::class)->group(function () {
		Route::prefix('sales-finance/purchase-order')->group(function () {
			Route::get('purchase_orders', 'purchase_orders');
			Route::get('purchase_order_add', 'purchase_order_add');
		});
	});
	// Credit Notes Backend side
	Route::controller(CreditNotesControllerAdmin::class)->group(function () {
		Route::prefix('sales-finance/credit-notes')->group(function () {
			Route::get('/credit_notes_form', 'credit_notes_form');
		});
	});
	// end
	// Backend Staff worker 
	Route::controller(StaffWorkerController::class)->group(function () {
		Route::prefix('rota')->group(function () {
			Route::get('/staff-worker', 'index')->name('backend.staff_worker');
			Route::get('/staff-worker-add', 'create')->name('backend.staff_worker.add');
			Route::post('/save-staff-worker-data', 'store');
			Route::delete('/staff-delete/{id}', 'destroy')->name('staff.delete');
			Route::get('/edit-staff-worker/{id}', 'edit')->name('backend.staff_worker.edit');
		});
	});

	// Backend DayBook Purchse Controller
	Route::controller(PurchaseBackendController::class)->group((function () {
		Route::prefix('sales-finance/purchase')->group(function () {
			Route::get('/purchase-day-book', 'index')->name('backend.purchase_day_book.index');
			Route::get('/purchase-day-book-edit/{id}', 'purchaseDayBookEdit')->name('backend.purchase_day_book.edit');
			Route::post('/purchase-day-book-delete/{id}', 'purchaseDayBookDelete')->name('backend.purchase_day_book.delete');
			Route::get('/purchase-day-book-add', 'create')->name('backend.purchase_day_book.create');
			Route::post('/purchase-day-book-save', 'store')->name('backend.purchase_day_book.store');
			Route::get('/purchase-type', 'purchase_type')->name('backend.purchase_expenses_type');
			Route::get('/purchase-type-add', 'purchase_type_add')->name('backend.purchase_expenses_type_add');
			Route::get('/save-purchase-expenses-type', 'save_purchase_expenses_type');
			Route::post('/change-status/{id}', 'changeStatus');
			Route::get('/purchase-daybook/data', 'getPurchaseDayBook');
			Route::get('/purchase-expenses-type-delete/{id}', 'deletePurchaseExpensesType');
			Route::get('/purchase-expenses-type-edit/{id}', 'editPurchaseExpensesType');
			Route::get('/getSupplierData', 'getSupplierData')->name('purchase.getSupplierData');
			Route::get('/getPurchaseExpense', 'getPurchaseExpense')->name('purchase.getPurchaseExpense');
			Route::get('purchase-day-book-reclaim-per', 'purchase_day_book_reclaim_per')->name('backend.purchase.purchaseDayBookReclaimPer');
			Route::get('reclaimPercantage', 'reclaimPercantage')->name('purchase.reclaimPercantage');
		});
	}));

	Route::controller(BackendInvoiceController::class)->group((function () {
		Route::prefix('sales-finance/sales')->group(function () {
			Route::get('/getTaxRate', 'getActiveTaxRate');
		});
	}));


	Route::controller(TimeSheetBackendController::class)->group((function () {
		Route::prefix('/sales-finance/time-sheet')->group(function () {
			Route::get('/', 'index')->name('backEnd.salesFinance.time_sheet');
			Route::get('/add', 'create');
			Route::post('/save', 'store');
			Route::post('/get-data', 'getData');
			Route::delete('/delete/{id}', 'destroy');
			Route::get('/edit/{id}', 'edit');
		});
	}));

	// Backend DayBook Sales Controller
	Route::controller(SalesBackendController::class)->group((function () {
		Route::prefix('sales-finance/sales')->group(function () {
			Route::get('/sales-day-book', 'index')->name('backend.sales_day_book.index');
			Route::get('/sales-day-book-add', 'create');
			Route::post('/save-sales-day-book', 'store');
			Route::post('/sales-day-book-delete/{id}', 'salesDayBookDelete')->name('backend.sales_day_book.delete');
			Route::get('/daybook/data', 'getSalesDayBook');
			Route::get('/sales-day-book-edit/{id}', 'salesDayBookEdit')->name('backend.sales_day_book.edit');
		});
	}));
	// Backend Council tax Controller
	Route::controller(CouncilBackendController::class)->group((function () {
		Route::prefix('finance')->group(function () {
			Route::get('/council-tax', 'index')->name('backend.council_tax.index');
			Route::get('/council-tax-edit/{id}', 'councilTaxEdit')->name('backend.council_tax.edit');
			Route::get('/council-tax-add', 'create');
			Route::post('/save-council-tax', 'store');
			Route::get('/council-tax-delete/{id}', 'councilTaxDelete')->name('backend.council_tax.delete');
			Route::get('/council-tax-edit/{id}', 'councilTaxEdit')->name('backend.council_tax.edit');
		});
	}));
	// Backend Petty Cash
	Route::controller(PettyCashBackendController::class)->group(function () {
		Route::prefix('sales-finance')->group(function () {
			Route::get('expend-card', 'expend_card');
			Route::get('getAllExpendCard', 'getAllExpendCardData');
			Route::post('expend-card/saveExpend', 'saveExpend');
			Route::post('expend-card/editExpend', 'saveExpend');
			Route::post('expend-card/expend_delete', 'expend_delete');
			Route::post('expand_card_filter', 'expand_card_filter');
			Route::get('petty-cash', 'cash');
			Route::post('petty-cash/saveCash', 'saveCash');
			Route::post('petty-cash/editCash', 'saveCash');
			Route::post('petty-cash/cash_delete', 'cash_delete');
			Route::post('petty-cash/cash_filter', 'cash_filter');
		});
	});
	// Backend Fixed Assets
	Route::controller(AssetBackendController::class)->group(function () {
		Route::prefix('sales-finance/assets/')->group(function () {
			Route::get('asset-category', 'asset_category');
			Route::post('asset-category-save', 'asset_category_save');
			Route::post('asset-category-edit', 'asset_category_save');
			Route::post('asset-category-status-change', 'asset_category_status_change');
			Route::post('asset-category-delete', 'asset_category_delete');
			Route::get('depreciation-type', 'depreciation_type');
			Route::post('depreciation-type-save', 'depreciation_type_save');
			Route::post('depreciation-type-edit', 'depreciation_type_save');
			Route::post('depreciation-status-change', 'depreciation_status_change');
			Route::get('asset-register', 'asset_register');
			Route::post('asset-register-search', 'asset_register_search');
			Route::get('asset-register-add', 'asset_regiser_add');
			Route::post('asset-register-save', 'asset_regiser_save');
			Route::get('asset-register-edit', 'asset_regiser_add');
			Route::post('asset-register-delete', 'asset_register_delete');
		});
	});
	// HomeCostingController code 
	Route::controller(HomeCostingController::class)->group(function () {
		Route::prefix('general-admin')->group(function () {
			Route::get('/home-costing', 'index');
			Route::get('/home-costing/add', 'add');
		});
	});
	// end here
	// PlanBuilderAdminController Code

	Route::controller(PlanBuilderAdminController::class)->group(function () {
		Route::prefix('appointment')->group(function () {
			Route::match(['get', 'post'], 'plans', 'index');
			Route::get('plans/add', 'plan_add');
			Route::post('plans/store', 'store');
			Route::get('plans/edit/{id}', 'edit');
			Route::get('plans/view/{id}', 'view');
			Route::get('plans/delete/{id}', 'delete');
		});
	});
	// end here
});

//super admin path
Route::group(['prefix' => 'super-admin', 'middleware' => 'CheckAdminAuth'], function () {

	//Child migration
	Route::get('/migrations', 'App\Http\Controllers\backEnd\superAdmin\MigrationController@index');
	Route::get('/migration/view/{migration_id}', 'App\Http\Controllers\backEnd\superAdmin\MigrationController@view');
	Route::post('/migration/update', 'App\Http\Controllers\backEnd\superAdmin\MigrationController@update');

	//super user admin
	Route::match(['get', 'post'], '/user/add', 'App\Http\Controllers\backEnd\superAdmin\UserController@add');
	Route::match(['get', 'post'], '/user/edit/{user_id}', 'App\Http\Controllers\backEnd\superAdmin\UserController@edit');
	Route::match(['get', 'post'], '/user/delete/{user_id}', 'App\Http\Controllers\backEnd\superAdmin\UserController@delete');
	Route::match(['get', 'post'], '/users', 'App\Http\Controllers\backEnd\superAdmin\UserController@index');

	Route::match(['get', 'post'], '/user/send-set-pass-link/{user_id}', 'App\Http\Controllers\backEnd\superAdmin\UserController@send_set_password_link_mail');

	Route::match('get', 'user/set-password/{super_admin_id}/{security_code}', 'App\Http\Controllers\backEnd\superAdmin\UserController@show_set_password_form_super_admin');

	Route::match(['get', 'post'], 'user/super-admin/set-password', 'App\Http\Controllers\backEnd\superAdmin\UserController@set_password_super_admin');

	//home-admin
	Route::match(['get', 'post'], '/home-admin/{home_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeAdminController@index');
	Route::match(['get', 'post'], '/home-admin/add/{home_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeAdminController@add');
	Route::match(['get', 'post'], '/home-admin/edit/{home_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeAdminController@edit');
	Route::match(['get', 'post'], '/home-admin/delete/{home_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeAdminController@delete');

	//FileManager Categories
	Route::match(['get', 'post'], '/filemanager-categories', 'App\Http\Controllers\backEnd\superAdmin\FileManagerCategoryController@index');
	Route::match(['get', 'post'], '/filemanager-category/add', 'App\Http\Controllers\backEnd\superAdmin\FileManagerCategoryController@add');
	Route::match(['get', 'post'], '/filemanager-category/edit/{category_id}', 'App\Http\Controllers\backEnd\superAdmin\FileManagerCategoryController@edit');
	Route::match(['get', 'post'], '/filemanager-category/delete/{category_id}', 'App\Http\Controllers\backEnd\superAdmin\FileManagerCategoryController@delete');

	Route::match(['get', 'post'], '/home-admin/send-set-pass-link/{home_admin_id}', 'App\Http\Controllers\backEnd\superAdmin\HomeAdminController@send_set_password_link_mail');

	//SocailApp
	Route::match(['get', 'post'], '/social-apps', 'App\Http\Controllers\backEnd\superAdmin\SocialAppController@index');
	Route::match(['get', 'post'], '/social-app/add', 'App\Http\Controllers\backEnd\superAdmin\SocialAppController@add');
	Route::match(['get', 'post'], '/social-app/edit/{social_app_id}', 'App\Http\Controllers\backEnd\superAdmin\SocialAppController@edit');
	Route::get('/social-app/delete/{social_app_id}', 'App\Http\Controllers\backEnd\superAdmin\SocialAppController@delete');

	//Ethnicity
	Route::match(['get', 'post'], '/ethnicities', 'App\Http\Controllers\backEnd\superAdmin\EthnicityController@index');
	Route::match(['get', 'post'], '/ethnicity/add', 'App\Http\Controllers\backEnd\superAdmin\EthnicityController@add');
	Route::match(['get', 'post'], '/ethnicity/edit/{ethnicity_id}', 'App\Http\Controllers\backEnd\superAdmin\EthnicityController@edit');
	Route::match(['get', 'post'], '/ethnicity/delete/{ethnicity_id}', 'App\Http\Controllers\backEnd\superAdmin\EthnicityController@delete');
});
