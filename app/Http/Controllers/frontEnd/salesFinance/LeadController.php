<?php

namespace App\Http\Controllers\frontEnd\salesFinance;


use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Lead;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\LeadRejectType;
use App\Models\LeadRejectReason;
use App\Models\AttachmentType;
use App\Models\LeadAttachment;
use App\Models\LeadStatus;
use App\Models\LeadTask;
use App\Models\LeadSource;
use App\Models\LeadTaskType;
use App\Models\LeadNoteType;
use App\Models\LeadNote;
use App\Models\CRMSectionType;
use App\Models\Country;
use App\Models\CRMLeadCalls;
use App\Models\CRMLeadEmail;
use App\Models\CRMLeadNotes;
use App\Models\CRMLeadComplaint;
use App\Models\CRMLeadTask;
use App\Models\CRMSection;
use App\Models\CRMLeadTaskReccurence;
use App\Models\Week;

class LeadController extends Controller
{
    public function leads(){
        // echo 1;die;lead_dasboard
        return view('frontEnd.salesAndFinance.common.lead_dasboard');
    }
    public function index(Request $request)
    {
        $data['page'] = "leads";
        $path = $request->path();
        $segments = explode('/', $path);
        $data['lastSegment'] = end($segments);
        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['leadTask'] = LeadTaskType::getLeadTaskType();
        $data['customers'] = Customer::getCustomerWithLeads(end($segments), Auth::user()->home_id);
        // dd($data['customers']);
        $data['leadRejectTypes'] = LeadRejectType::getLeadRejectType();
        $data['weeks'] = Week::getWeeklist();
        $data['allLead'] = Lead::getAllLeadCount(Auth::user()->home_id);
        $data['myLeads'] = Lead::getLeadByUser(Auth::user()->id, Auth::user()->home_id);
        $data['unAssignLead'] = Lead::getUnassignedCount(Auth::user()->home_id);
        $data['actionedLead'] =   Lead::getActionedLead(Auth::user()->home_id);
        $data['rejectLead'] = Lead::getRejectedCount(Auth::user()->home_id);
        $data['authorizedLead']     = Lead::getAuthorizationCount(Auth::user()->home_id);
        $data['convertedLead'] = Lead::getConvertedCustomersCount(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.lead.leads', $data);
    }
    public function create()
    {
        $page = "leads";
        $users = User::getHomeUsers(Auth::user()->home_id);
        $status = LeadStatus::getLeadStatus();
        $sources = LeadSource::getLeadSources(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.lead.lead_form', compact('page', 'users', 'status', 'sources'));
    }
    public function store(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($request->customer_id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        try {

            $website = $request->input('website');
            if ($website && !preg_match('/^http/', $website)) {
                $website = 'http://' . $website;
            }

            $customer = Customer::updateOrCreate(['id' => $request->customer_id], [
                'home_id' => Auth::user()->home_id,
                'name' => $request->company_name,
                'contact_name' => $request->name,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'mobile' => $request->mobile,
                'website' => $website,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'postal_code' => $request->postal_code,
            ]);
            $customer_id = $customer->id;

            if ($customer && $customer->id) {
                if (!isset($request->lead_ref)) {
                    $lastLead = Lead::orderBy('id', 'desc')->first();
                    $nextId = $lastLead ? $lastLead->id + 1 : 1;
                    $lead_refid = 'LEAD-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                } else {
                    $lead_refid = $request->lead_ref;
                }

                if ($request->input('prefer_date')  !== null) {
                    $prefer_date = Carbon::createFromFormat('d/m/Y', $request->input('prefer_date'))->format('Y-m-d');
                } else {
                    $prefer_date = $request->input('prefer_date');
                }

                // Create the lead using the customer ID
                $lead = Lead::updateOrCreate(['id' => $request->lead_id], [
                    'home_id' => Auth::user()->home_id,
                    'user_id' => Auth::user()->id,
                    'lead_ref' => $lead_refid,
                    'customer_id' => $customer_id,
                    'assign_to' => $request->input('assign_to'),
                    'source' => $request->input('source'),
                    'status' => $request->input('status'),
                    'prefer_date' => $prefer_date,
                    'prefer_time' => $request->input('prefer_time'),
                ]);

                if ($lead->wasRecentlyCreated) {
                    $message =  'Lead created successfully.';
                    return redirect()->route('lead.edit', ['id' => $lead->id])->with('success', $message);
                } else {
                    $message =  'Lead updated successfully.';
                    return redirect()->route('lead.index')->with('success', $message);
                }
            } else {
                // Handle the error case where the customer was not created successfully
                return redirect()->route('lead.index')->with('error', 'Failed to create customer.');
            }
        } catch (\Exception $e) {
            Log::error('Error saving lead: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to save lead. Please try again.']);
        }
    }

    public function edit($id)
    {
        $page = 'leads';
        $lead = Customer::getCustomerLeads($id);
        // dd($lead);
        $users = User::getHomeUsers(Auth::user()->home_id);
        $status = LeadStatus::getLeadStatus();
        $sources = LeadSource::getLeadSources();
        $notes_type = LeadNoteType::getLeadNoteTypeWithHomeId(Auth::user()->home_id);
        $leadTask = LeadTaskType::getLeadTaskType();
        $attachment_type = AttachmentType::getAttachmentType();
        $lead_notes_data = LeadNote::getLeadNoteFromleadNoteType($id);
        $lead_task_open =  LeadTask::getLeadTaskTypeUser($lead->lead_ref, 0);
        $lead_task_close =  LeadTask::getLeadTaskTypeUser($lead->lead_ref, 1);
        // dd($lead_task_open);
        $lead_attachment = LeadAttachment::getLeadAttachments($id);
        // dd($lead);
        return view('frontEnd.salesAndFinance.lead.lead_form', compact('lead', 'users', 'page', 'sources', 'status', 'notes_type', 'lead_notes_data', 'leadTask', 'lead_task_open', 'lead_task_close', 'attachment_type', 'lead_attachment'));
    }

    public function getUserList()
    {
        $users = User::getHomeUsers(Auth::user()->home_id);
        if ($users) {
            return response()->json(['success' => true, 'data' => $users]);
        } else {
            return response()->json(['success' => false, 'Data' => 'No Data']);
        }
    }

    public function getLeadDataWithRecurrence(Request $request)
    {

        $data = CRMLeadTask::getLeadDataWithRecurrence($request->id);
        $arrayRecord = [];
        foreach ($data as $value) {
            $record = [
                'id' => $value->id,
                'user_id' => $value->user_id,
                'title' => $value->title,
                'task_type_id' => $value->task_type_id,
                'start_date' => $value->start_date,
                'start_time' => $value->start_time,
                'end_date' => $value->end_date,
                'end_time' => $value->end_time,
                'is_recurring' => $value->is_recurring,
                'notify' => $value->notify,
                'notification' => $value->notify,
                'email' => $value->email,
                'task_date' => $value->task_date,
                'task_time' => $value->task_time,
                'notes' => $value->notes,
                'is_completed' => $value->is_completed,
                'created_at' => $value->created_at,
            ];
            if ($value->is_recurring == 1) {
                $recurrence = CRMLeadTaskReccurence::getRecurrenceDataFromTaskType($value->id);
                //    print_r($recurrence); 
                //    die;
                foreach ($recurrence as $recData) {
                    $record['task_end_repe_date'] = $recData->task_end_repe_date;
                    $record['no_of_repetitations'] = $recData->no_of_repetitations;
                    $record['task_end_date'] = $recData->task_end_date;
                    $record['task_frequency'] = $recData->task_frequency;
                    $record['daily_days'] = $recData->daily_days;
                    $record['daily_weekday'] = $recData->daily_weekday;
                    $record['weekly_days'] = $recData->weekly_days;
                    $record['weekly_weekday'] = $recData->weekly_weekday;
                    $record['weekly_weeks'] = $recData->weekly_weeks;
                    $record['monthly_days'] = $recData->monthly_days;
                    $record['monthly_month'] = $recData->monthly_month;
                    $record['every_month_day'] = $recData->every_month_day;
                    $record['every_monthly_month'] = $recData->every_monthly_month;
                    $record['every_month_of_month'] = $recData->every_month_of_month;
                    $record['recurrence_created_at'] = $recData->created_at;
                }
            }
            array_push($arrayRecord, $record);
        }
        if ($arrayRecord) {
            return response()->json(['success' => true, 'data' => $arrayRecord]);
        } else {
            return response()->json(['success' => false, 'Data' => 'No Data']);
        }
    }

    // Lead Note Types start
    public function lead_notes_type()
    {
        $page = "lead_notes_type";
        $lead_notes_type = LeadNoteType::getAllLeadNoteType();
        return view('frontEnd.salesAndFinance.lead.lead_note_type', compact('page', 'lead_notes_type'));
    }

    public function saveLeadNotesType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        LeadNoteType::updateOrCreate(['id' => $request->lead_notes_type_id], array_merge($request->all(), ['home_id' =>  Auth::user()->home_id]));

        if (isset($request->lead_notes_type_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Notes Type added successfully!']);
        }
    }

    public function lead_note_type_delete($id)
    {
        if (LeadNoteType::deleteLeadNoteType($id)) {
            return redirect()->route('leads.lead_notes_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_notes_type')->with('error', "Record not found");
        }
    }
    // Lead Notes Type End


    public function save_lead_notes(Request $request)
    {
        $save = LeadNote::create(array_merge($request->all(), ['home_id' =>  Auth::user()->home_id]));
        if ($save) {
            return response()->json(['success' => true, 'message' => 'Record added successfully!']);
        } else {
            return response()->json(['success' => false, 'message' => "Error ! Notes doesn't save!"]);
        }
    }

    // Lead Tasks
    public function save_lead_tasks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_task_type_id' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $create_date = Carbon::createFromFormat('d/m/Y', $request->create_date)->format('Y-m-d');
        if (isset($request->notify_date)) {
            $notify_date = Carbon::createFromFormat('d/m/Y', $request->notify_date)->format('Y-m-d');
        } else {
            $notify_date = $request->notify_date;
        }

        $lead = LeadTask::updateOrCreate(['id' => $request->lead_task_id], array_merge($request->all(), ['create_date' => $create_date, 'notify_date' => $notify_date]));

        if (isset($request->lead_task_id)) {
            return response()->json(['success' => true, 'message' => 'Record updated successfully!', 'data' => $lead->lead_ref]);
        } else {
            return response()->json(['success' => false, 'message' => 'Task added successfully!', 'data' => $lead->lead_ref]);
        }
    }
    public function lead_task_delete($taskId, $leadId)
    {
        if (LeadTask::deleteLeadTask($taskId)) {
            return redirect()->route('lead.edit', ['id' => $leadId])->with('success', 'Lead Task deleted successfully');
        } else {
            return redirect()->route('lead.edit', ['id' => $leadId])->with('fails', 'Error in Lead task deletion');
        }
    }

    // Lead Attachments start
    public function saveLeadAttachment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'file' => 'required|file|mimes:jpg,jpeg,png,gif|max:25600',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->file('file')->isValid()) {
            $file = $request->file('file');
            $mimeType = $file->getMimeType();
            $sizeInBytes = $file->getSize(); // Size in bytes
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('lead_attachments', $fileName, 'public');
            LeadAttachment::create(array_merge($request->all(), ['image' => $filePath,  'mime_type' => $mimeType, 'size_in_bytes' => $sizeInBytes,]));
            return response()->json(['success' => true, 'message' => 'File uploaded successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error in file upload!']);
        }
    }

    public function lead_attachments_delete($attachment_id, $leadId)
    {
        if (LeadAttachment::deleteLeadAttachment($attachment_id)) {
            return redirect()->route('lead.edit', ['id' => $leadId])->with('success', 'Lead Atachments deleted successfully');
        } else {
            return redirect()->route('lead.edit', ['id' => $leadId])->with('fails', 'Error in Lead attachments deletion');
        }
    }
    // Lead Attachments end

    public function task_list()
    {
        $data['page'] = "Leads";
        $data['lead_tasks'] = LeadTask::getLeadTasks(0);
        $data['allLead'] = Lead::getAllLeadCount(Auth::user()->home_id);
        $data['myLeads'] = Lead::getLeadByUser(Auth::user()->id, Auth::user()->home_id);
        $data['unAssignLead'] = Lead::getUnassignedCount(Auth::user()->home_id);
        $data['actionedLead'] =   Lead::getActionedLead(Auth::user()->home_id);
        $data['rejectLead'] = Lead::getRejectedCount(Auth::user()->home_id);
        $data['authorizedLead'] = Lead::getAuthorizationCount(Auth::user()->home_id);
        $data['convertedLead'] = Lead::getConvertedCustomersCount(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.lead.lead_task', $data);
    }

    public function lead_task_list_delete($id)
    {
        if (LeadTask::deleteLeadTask($id)) {
            return redirect()->route('lead.task_list')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('lead.task_list')->with('error', "Record not found");
        }
    }
    public function task_mark_as_completed($task_id)
    {
        $data = LeadTask::taskMarkAsCompleted($task_id);

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function sentToAuthorization($leadId)
    {
        if (Lead::leadForAdminAuthorization($leadId)) {
            return redirect()->route('lead.index')->with('success', 'Lead Sent for authorization');
        } else {
            return redirect()->route('lead.index')->with('error', 'Error in sending for authorization');
        }
    }

    // Lead Sources
    public function lead_sources()
    {
        $page = "Lead";
        $lead_sources = LeadSource::getAllLeadSources();
        return view('frontEnd.salesAndFinance.lead.lead_sources', compact('page', 'lead_sources'));
    }

    public function saveLeadSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadSource::updateOrCreate(['id' => $request->lead_source_id], array_merge($request->all(), ['home_id' =>  Auth::user()->home_id]));
        if (isset($request->lead_source_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Source added successfully!']);
        }
    }

    // public function lead_source_delete($id){
    //     if(LeadSource::deleteLeadSources($id) ){
    //         return redirect()->route('leads.lead_sources')->with('success', "Record deleted successfully");
    //     } else {
    //         return redirect()->route('leads.lead_sources')->with('error', "Record not found");
    //     } 
    // }

    // Lead Status 
    public function lead_status()
    {
        $page = "Lead Status";
        $lead_status = LeadStatus::getAllLeadStatus();
        return view('frontEnd.salesAndFinance.lead.lead_status', compact('page', 'lead_status'));
    }

    public function saveLeadStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadStatus::updateOrCreate(['id' => $request->lead_status_id], array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->lead_status_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Status added successfully!']);
        }
    }

    // public function lead_status_delete($id){ 
    //     if(LeadStatus::deleteLeadStatus($id) ){
    //         return redirect()->route('lead.lead_status')->with('success', "Record deleted successfully");
    //     } else {
    //         return redirect()->route('lead.lead_status')->with('error', "Record not found");
    //     }
    // }

    // Lead Task Type
    public function lead_task_type()
    {
        $page = "lead_task_type";
        $lead_task_type = LeadTaskType::getAllLeadTask();
        return view('frontEnd.salesAndFinance.lead.lead_task_type', compact('page', 'lead_task_type'));
    }

    public function saveLeadTaskType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        LeadTaskType::updateOrCreate(['id' => $request->lead_task_type_id], array_merge($request->all(), ['home_id' =>  Auth::user()->home_id]));

        if (isset($request->lead_task_type_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Task Type added successfully!']);
        }
    }

    public function lead_task_type_delete($id)
    {
        if (LeadTaskType::deleteLeadTaskType($id)) {
            return redirect()->route('leads.lead_task_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_task_type')->with('error', "Record not found");
        }
    }

    // Lead Reject Type
    public function lead_reject_type()
    {
        $page = "Lead Reject Type";
        $lead_rejects = LeadRejectType::getAllLeadRjectType();
        return view('frontEnd.salesAndFinance.lead.lead_reject_type', compact('lead_rejects', 'page'));
    }

    public function saveLeadRejectType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadRejectType::updateOrCreate(['id' => $request->lead_reject_id], array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->lead_reject_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Reject Type added successfully!']);
        }
    }

    public function lead_reject_type_delete($id)
    {
        if (LeadRejectType::deleteLeadRejectType($id)) {
            return redirect()->route('leads.lead_reject_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_reject_type')->with('error', "Record not found");
        }
    }

    public function saveLeadRejectReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_ref' => 'required',
            'reject_type_id' => 'required',
            'reject_reason' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        Lead::where('lead_ref', $request->lead_ref)->update(['status' => 6]);
        LeadRejectReason::updateOrCreate(['id' => $request->lead_reject_id], $request->all());
        if (isset($request->lead_reject_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Rejected added successfully!']);
        }
    }

    // CRM Section Type
    public function CRM_section_type()
    {
        $page = "crm_section_type";
        $crm_sections = CRMSectionType::getCRMSectionTypes();
        $crmSec = CRMSection::getCRMSectionData();
        return view('frontEnd.salesAndFinance.lead.CRM_section_type', compact('page', 'crm_sections', 'crmSec'));
    }

    public function saveCRMSectionType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'crm_section' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        CRMSectionType::updateOrCreate(['id' => $request->section_type_id],  array_merge($request->all(), ['home_id' => Auth::user()->home_id]));
        if (isset($request->section_type_id)) {
            return response()->json(['success' => true, 'message' => 'CRM Section updated successfully']);
        } else {
            return response()->json(['success' => true, 'message' => 'CRM Section Type added successfully.']);
        }
    }

    public function crm_section_type_delete($id)
    {
        $url = str_replace(url('/'), '', url()->previous());
        $data = CRMSectionType::deleteCRMSectionType($id);
        if ($url == '/complaint_type' && $data) {
            return redirect('/complaint_type')->with('success', "Record deleted successfully");
        } else {
            if ($data) {
                return redirect()->route('lead.crm_section')->with('success', "Record deleted successfully");
            } else {
                return redirect()->route('lead.crm_section')->with('error', "Record not found");
            }
        }
    }

    public function get_CRM_section_types()
    {
        $data = CRMSectionType::getCRMTypeFromHomeId(Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'Data' => $data]);
        } else {
            return response()->json(['success' => false, 'Data' => 'No Data']);
        }
    }

    public function getCountriesList()
    {
        $data = Country::getCountriesNameCode();
        if ($data) {
            return response()->json(['success' => true, 'Data' => $data]);
        } else {
            return response()->json(['success' => false, 'Data' => 'No Data']);
        }
    }

    public function saveCRMLeadData(Request $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'lead_ref' => 'required',
            'crm_type_id' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->notify_radio == 1) {
            $validator = Validator::make($request->all(), [
                'notify_user' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }


        $attributes = ['id' => $request->crm_lead_calls_id]; // Assuming each user has their own notification settings

        if ($request->telephone) {
            $phone = "+" . $request->country_code . "-" . $request->telephone;
        } else {
            $phone = $request->telephone;
        }


        // Data to update or create
        $values = [
            'home_id' => Auth::user()->home_id,
            'lead_id' => $request->lead_ref,
            'direction' => $request->direction,
            'telephone' => $phone,
            'crm_type_id' => $request->crm_type_id,
            'notes' => $request->content,
            'notify' => $request->notify_radio,
            'user_id' => $request->notify_user,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visibility' => $request->customer_visible
        ];

        // Update the record if it exists, otherwise create a new one
        CRMLeadCalls::updateOrCreate($attributes, $values);

        if (isset($request->crm_lead_calls_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'CRM Lead Calls added successfully!']);
        }
    }

    public function getCRMCallsData(Request $request)
    {
        $data = CRMLeadCalls::getCRMLeadCallsData($request->lead_ref, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function saveCRMLeadEmails(Request $request)
    {

        // dd($request->hasFile('attachment'));
        $validator = Validator::make($request->all(), [
            'to' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->notify_email1 == 1) {
            $validator = Validator::make($request->all(), [
                'notify_user' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }

        // Handle the image upload
        if ($request->hasFile('attachment')) {
            // Get the file
            $image = $request->file('attachment');

            // Store the image
            $path = $image->store('CRMLeadEmail', 'public'); // Store the file in 'public/images'
        } else {
            $path = null;
        }

        $attributes = ['id' => $request->crm_lead_email_id]; // Assuming each user has their own notification settings

        // Data to update or create
        $values = [
            'home_id' => Auth::user()->home_id,
            'lead_id' => $request->lead_id,
            'to' => $request->to,
            'cc' => $request->cc,
            'subject' => $request->subject,
            'message' => $request->message,
            'notify' => $request->notify,
            'attachment' => $path,
            'user_id' => $request->notify_user,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visible' => $request->customer_visible
        ];

        // Update the record if it exists, otherwise create a new one
        CRMLeadEmail::updateOrCreate($attributes, $values);

        if (isset($request->crm_lead_calls_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'CRM Lead Emails added successfully!']);
        }
    }

    public function getCRMEmailsData(Request $request)
    {
        $data = CRMLeadEmail::getCRMLeadEmailsData($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function saveCRMLeadNotes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'crm_section_type_id' => 'required',
            'notes' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->notify == 1) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }

        $attributes = ['id' => $request->crm_lead_notes_id]; // Assuming each user has their own notification settings

        // Data to update or create
        $values = [
            'home_id' => Auth::user()->home_id,
            'lead_id' => $request->lead_id,
            'crm_section_type_id' => $request->crm_section_type_id,
            'notes' => $request->notes,
            'notify' => $request->notify,
            'user_id' => $request->user_id,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'customer_visibility' => $request->customer_visibility
        ];

        // Update the record if it exists, otherwise create a new one
        CRMLeadNotes::updateOrCreate($attributes, $values);

        if (isset($request->crm_lead_notes_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'CRM Lead Notes added successfully!']);
        }
    }

    public function getCRMNotesData(Request $request)
    {
        $data = CRMLeadNotes::getCRMLeadNotesData($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getLeadTaskTypeData()
    {
        $data = LeadTaskType::getLeadTaskType();
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function saveCRMLeadComplaint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'crm_section_type_id' => 'required',
            'compliant' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($request->notify == 1) {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }

        $attributes = ['id' => $request->crm_lead_complaint_id]; // Assuming each user has their own notification settings

        // Data to update or create
        $values = [
            'home_id' => Auth::user()->home_id,
            'lead_id' => $request->lead_id,
            'crm_section_type_id' => $request->crm_section_type_id,
            'notes' => $request->compliant,
            'notify' => $request->notify,
            'user_id' => $request->user_id,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email
        ];

        // Update the record if it exists, otherwise create a new one
        CRMLeadComplaint::updateOrCreate($attributes, $values);

        if (isset($request->crm_lead_notes_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'CRM Lead Complaint added successfully!']);
        }
    }

    public function getCRMComplaintData(Request $request)
    {
        $data = CRMLeadComplaint::getCRMLeadComplaintData($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function saveCRMLeadTaskAndTimer(Request $request)
    {
        // dd($request);

        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'task_type_id' => 'required'
            // 'user_id' => 'required'
        ]);

        if ($request->task == "task_form") {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'end_date' => 'required',
                'end_time' => 'required',
                'user_id' => 'required'
            ]);
        } elseif ($request->timer == "timer_form") {
            $validator = Validator::make($request->all(), [
                'title_timer' => 'required',
                'user_id_timer' => 'required'
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->notify == 1) {
            $notification = $request->has('notification') ? 1 : 0;
            $sms = $request->has('sms') ? 1 : 0;
            $email = $request->has('email') ? 1 : 0;
        }

        if (!isset($notification) || !isset($sms) || !isset($email)) {
            $notification = $sms = $email = null;
        }

        // Data to update or create
        $values = [
            'home_id' => Auth::user()->home_id,
            'lead_id' => $request->lead_id,
            'user_id' => $request->user_id ?? $request->user_id_timer,
            'title' => $request->title ?? $request->title_timer,
            'task_type_id' => $request->task_type_id ?? $request->task_type_id_time,
            'start_date' => $request->start_date ?? Carbon::now()->toDateString(),
            'start_time' => $request->start_time ?? Carbon::now()->toTimeString(),
            'end_date' => $request->end_date,
            'end_time' => $request->end_time,
            'is_recurring' => $request->is_recurring ?? false,
            'notify' => $request->notify,
            'notification' => $notification,
            'sms' => $sms,
            'email' => $email,
            'task_date' => $request->task_date,
            'task_time' => $request->task_time,
            'notes' => $request->notes
        ];

        // Update the record if it exists, otherwise create a new one
        $CRMleadTask = CRMLeadTask::updateOrCreate(['id' => $request->crm_lead_task_id], $values);

        $data = CRMLeadTaskReccurence::create(array_merge($request->all(), ['crm_lead_task_id' =>  $CRMleadTask->id]));

        Log::info('User logged in', ['user_id' => $data]);

        if (isset($request->crm_lead_notes_id)) {
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'CRM Lead Task added successfully!']);
        }
    }

    public function getCRMTasksData(Request $request)
    {
        $data = CRMLeadTask::getCRMLeadTaskData($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMTaskDataToday(Request $request)
    {
        $data = CRMLeadTask::getCRMTaskDataToday($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMTaskDataWeek(Request $request)
    {
        $data = CRMLeadTask::getCRMTaskDataWeek($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMTaskDataOverdue(Request $request)
    {
        $data = CRMLeadTask::getCRMTaskDataOverdue($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMTaskDataComplete(Request $request)
    {
        $data = CRMLeadTask::getCRMTaskDataComplete($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMTaskDataRecurring(Request $request)
    {
        $data = CRMLeadTask::getCRMTaskDataRecurring($request->lead_id, Auth::user()->home_id);
        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function getCRMAllData(Request $request)
    {
        $data['0'] = CRMLeadTask::getCRMLeadTaskData($request->lead_id, Auth::user()->home_id);
        $data['1'] = CRMLeadComplaint::getCRMLeadComplaintData($request->lead_id, Auth::user()->home_id);
        $data['2'] = CRMLeadNotes::getCRMLeadNotesData($request->lead_id, Auth::user()->home_id);
        $data['3'] = CRMLeadEmail::getCRMLeadEmailsData($request->lead_id, Auth::user()->home_id);
        $data['4'] = CRMLeadCalls::getCRMLeadCallsData($request->lead_ref, Auth::user()->home_id);

        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    public function searchLead()
    {

        $data['page'] = "leads";
        $data['allLead'] = Lead::getAllLeadCount(Auth::user()->home_id);
        $data['myLeads'] = Lead::getLeadByUser(Auth::user()->id, Auth::user()->home_id);
        $data['unAssignLead'] = Lead::getUnassignedCount(Auth::user()->home_id);
        $data['actionedLead'] =   Lead::getActionedLead(Auth::user()->home_id);
        $data['rejectLead'] = Lead::getRejectedCount(Auth::user()->home_id);
        $data['authorizedLead'] = Lead::getAuthorizationCount(Auth::user()->home_id);
        $data['convertedLead'] = Customer::getConvertedCustomersCount(Auth::user()->home_id);
        $data['statuses'] = LeadStatus::getLeadStatus();
        $data['users'] = User::getHomeUsers(Auth::user()->home_id);
        $data['sources'] = LeadSource::getLeadSources(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.lead.search_leads', $data);
    }

    public function getLeadTaskOnLeadId(Request $request)
    {
        // dd($request);
        $close =  LeadTask::getLeadTaskTypeUser($request->lead_ref, 1);
        $open =  LeadTask::getLeadTaskTypeUser($request->lead_ref, 0);
        if ($open) {
            return response()->json(['success' => true, 'open' => $open, 'close' => $close]);
        } else {
            return response()->json(['success' => false, 'data' => 'No Data']);
        }
    }

    // public function searchUser(Request $request){
    //     $query = $request->get('query');

    //     // Fetch data from database (replace `YourModel` with your actual model)
    //     $results = Product::where('product_name', 'LIKE', "%{$query}%")->select('id', 'product_name')->get();

    //     // Return the results as JSON or a rendered view
    //     return response()->json($results); // For JSON response
    // }


    public function get30DaysLead()
    {
        $result = Lead::join('customers', 'customers.id', '=', 'leads.customer_id')
            ->where('leads.created_at', '>=', Carbon::now()->subDays(30))->where('leads.home_id', Auth::user()->home_id)
            // ->select('customers.name', 'customers.contact_name', 'customers.address')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d'); // Group by the date only
            });

        // dd($data);

        $data = $result->map(function ($group, $date) {
            return [
                'date' => $date,
                'count' => $group->count(),
                'records' => $group, // Include the original records if needed
            ];
        });

        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data'
        ]);
    }

    public function saveLeadConvertQuote(Request $request)
    {
        if ($request->type == "quote") {
            $data = Lead::saveLeadConvertQuote($request->all(), Auth::user()->home_id);

            return response()->json([
                'success' => (bool) $data,
                'data' => $data ? "lead converted to quote successfully" : 'Failed to convert lead to quote'
            ]);
        } elseif ($request->type == "customer") {
            $leadUpdated = Lead::where('id', $request->lead_id)->update(['converted_to' => "customer"]);
            $customerUpdated = Customer::where('id', $request->customer_id)->update(["is_converted" => 1]);

            if ($leadUpdated && $customerUpdated) {
                return response()->json([
                    'success' => true,
                    'data' => "Lead converted to customer successfully."
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'data' => "Failed to convert lead to customer."
                ], 400);
            }
        }
    }
}

// converted does not show the correct data
// after converting page reload
// ater convert the thata data remove




