<?php

namespace App\Http\Controllers\backend\salesFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Lead;
// use App\Customer;
use App\Models\Customer;
use Illuminate\Support\Facades\Session;
use Validator;
use App\Models\LeadRejectType;
use App\Models\LeadRejectReason;
use App\Models\LeadStatus;
use App\Models\LeadTask;
use App\Models\AttachmentType;
use App\Models\LeadSource;
use App\Models\LeadTaskType;
use App\Models\LeadNoteType;
use App\Models\LeadNote;
use App\Models\LeadAttachment;
use App\Models\CRMSectionType;


class LeadController extends Controller
{
    
    public function index(Request $request){
        $page = "Leads";
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);
        $customers = Customer::getCustomerWithLeads($lastSegment, Session::get('scitsAdminSession')->home_id);
        $leadRejectTypes = LeadRejectType::getLeadRejectType();
        return view('backEnd/salesFinance/leads/leads', compact('page', 'customers', 'leadRejectTypes', 'lastSegment'));
    }
    public function create(){
        $page = "Leads";
        $users = User::getHomeUsers(Session::get('scitsAdminSession')->home_id);
        $status = LeadStatus::getLeadStatus();
        $sources = LeadSource::getLeadSources();
      
        return view('backEnd/salesFinance/leads/leads_form',compact('page','users', 'status', 'sources'));
    }

    public function store(Request $request){
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255',
        //     'telephone' => 'required|string|max:15',
        // ]);
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }
        try {
            $website = $request->input('website');
            if ($website && !preg_match('/^http/', $website)) {
                $website = 'http://' . $website;
            }   
            $customer = Customer::updateOrCreate(['id' => $request->customer_id],[
                'home_id' => Session::get('scitsAdminSession')->home_id,
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
                if(!isset($request->lead_ref)){
                    $lastLead = Lead::orderBy('id', 'desc')->first();
                    $nextId = $lastLead ? $lastLead->id + 1 : 1;
                    $lead_refid = 'LEAD-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
                } else {
                    $lead_refid = $request->lead_ref;
                }     

                // Create the lead using the customer ID
                $lead = Lead::updateOrCreate(['id' => $request->lead_id],[
                    'home_id' => Session::get('scitsAdminSession')->home_id,
                    'lead_ref' => $lead_refid,
                    'customer_id' => $customer_id,
                    'assign_to' => $request->input('assign_to'),
                    'source' => $request->input('source'),
                    'status' => $request->input('status'),
                    'prefer_date' => $request->input('prefer_date'),
                    'prefer_time' => $request->input('prefer_time'),
                ]);
                if ($lead->wasRecentlyCreated) {
                    $message =  'Lead created successfully.';
                    return redirect()->route('leads.edit', ['id' => $lead->id])->with('success', $message);
                } else {
                    $message =  'Lead updated successfully.';
                    return redirect()->route('leads.index')->with('success', $message);
                }
            } else {
                return redirect()->route('leads.index')->with('error', 'Failed to create customer.');
            }  
        } catch (\Exception $e) {
            \Log::error('Error saving lead: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to save lead. Please try again.']);
        }
    }

    public function edit($id){ 
        // dd();
        $page = 'Leads';
        $lead = Customer::getCustomerLeads($id);  
        $users = User::getHomeUsers(Session::get('scitsAdminSession')->home_id);
        $status = LeadStatus::getLeadStatus();
        $sources = LeadSource::getLeadSources();
        $notes_type = LeadNoteType::getLeadNoteTypeWithHomeId(Session::get('scitsAdminSession')->home_id);
        $leadTask = LeadTaskType::getLeadTaskType();
        $attachment_type = AttachmentType::getAttachmentType();
        $lead_notes_data = LeadNote::getLeadNoteFromleadNoteType($id); 
        $lead_task_open =  LeadTask::getLeadTaskTypeUser($lead->lead_ref, 0); 
        $lead_task_close =  LeadTask::getLeadTaskTypeUser($lead->lead_ref, 1); 
        $lead_attachment = LeadAttachment::getLeadAttachments($id);
        return view('backEnd/salesFinance/leads/leads_form', compact('lead', 'users', 'page','sources', 'status', 'notes_type', 'lead_notes_data', 'leadTask', 'lead_task_open', 'lead_task_close', 'attachment_type', 'lead_attachment'));   
    }


    // Lead Reject Type
    public function lead_reject_type(){
        $page = "Lead Reject Type";
        $lead_rejects = LeadRejectType::getAllLeadRjectType();
        return view('backEnd/salesFinance/leads/lead_reject_type', compact('lead_rejects', 'page'));   
    }

    public function saveLeadRejectType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]); 
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadRejectType::updateOrCreate(['id' => $request->lead_reject_id], array_merge($request->all(), ['home_id' => Session::get('scitsAdminSession')->home_id]));
        if(isset($request->lead_reject_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Reject Type added successfully!']);
        }
    }
    
    public function lead_reject_type_delete($id){
        if(LeadRejectType::deleteLeadRejectType($id) ){
            return redirect()->route('leads.lead_reject_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_reject_type')->with('error', "Record not found");
        }
    }

    public function saveLeadRejectReason(Request $request){
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
        if(isset($request->lead_reject_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Rejected added successfully!']);
        }
    }

    // Lead Status 
    public function lead_status(){
        $page = "Lead Status";
        $lead_status = LeadStatus::getAllLeadStatus();
        return view('backEnd/salesFinance/leads/lead_status', compact('page', 'lead_status'));
    }

    public function saveLeadStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadStatus::updateOrCreate(['id' => $request->lead_status_id], array_merge($request->all(), ['home_id' => Session::get('scitsAdminSession')->home_id]));
        if(isset($request->lead_status_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Status added successfully!']);
        }
    }

    public function lead_status_delete($id){ 
        if(LeadStatus::deleteLeadStatus($id) ){
            return redirect()->route('leads.lead_status')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_status')->with('error', "Record not found");
        }
    }

    // Lead Sources
    public function lead_sources(){
        $page = "Lead Sources";
        $lead_sources = LeadSource::getAllLeadSources();
        return view('backEnd/salesFinance/leads/lead_sources', compact('page', 'lead_sources'));
    }

    public function saveLeadSource(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()]);
        }
        LeadSource::updateOrCreate(['id' => $request->lead_source_id], array_merge($request->all(), ['home_id' =>  Session::get('scitsAdminSession')->home_id]));
        if(isset($request->lead_source_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Lead Source added successfully!']);
        }
    }

    public function lead_source_delete($id){
        if(LeadSource::deleteLeadSources($id) ){
            return redirect()->route('leads.lead_sources')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_sources')->with('error', "Record not found");
        } 
    }

    // Lead Task Type
    public function lead_task_type(){
        $page = "lead_task_type";
        $lead_task_type = LeadTaskType::getAllLeadTask();
        return view('backEnd/salesFinance/leads/lead_task_type', compact('page', 'lead_task_type'));
    }

    public function saveLeadTaskType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        LeadTaskType::updateOrCreate(['id' => $request->lead_task_type_id], array_merge($request->all(), ['home_id' =>  Session::get('scitsAdminSession')->home_id]));

        if(isset($request->lead_task_type_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Task Type added successfully!']);
        }
    }

    public function lead_task_type_delete($id){
        if(LeadTaskType::deleteLeadTaskType($id) ){
            return redirect()->route('leads.lead_task_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_task_type')->with('error', "Record not found");
        } 
    }

    // Lead Note Types start
    public function lead_notes_type(){
        $page = "lead_notes_type";
        $lead_notes_type = LeadNoteType::getAllLeadNoteType();
        return view('backEnd/salesFinance/leads/lead_notes_type', compact('page', 'lead_notes_type'));
    }

    public function saveLeadNotesType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        LeadNoteType::updateOrCreate(['id' => $request->lead_notes_type_id], array_merge($request->all(), ['home_id' =>  Session::get('scitsAdminSession')->home_id]));

        if(isset($request->lead_notes_type_id)){
            return response()->json(['message' => 'Record updated successfully!']);
        } else {
            return response()->json(['message' => 'Notes Type added successfully!']);
        }
    }

    public function lead_note_type_delete($id){
        if(LeadNoteType::deleteLeadNoteType($id) ){
            return redirect()->route('leads.lead_notes_type')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.lead_notes_type')->with('error', "Record not found");
        } 
    }
    // Lead Notes Type End

    public function convert_to_customer($id){
        if(Customer::converToCustomer($id) ){
            return redirect()->route('leads.index')->with('success', "Customer converted successfully");
        } else {
            return redirect()->route('leads.index')->with('error', "Record not found");
        } 
    }

    public function save_lead_notes(Request $request){
        $save = LeadNote::create(array_merge($request->all(), ['home_id' =>  Session::get('scitsAdminSession')->home_id]));
        if( $save ){
            return response()->json(['message' => 'Record added successfully!']);
        } else {
            return response()->json(['message' => "Error ! Notes doesn't save!"]);
        }
    }

    // Lead Tasks
    public function save_lead_tasks(Request $request){
        $validator = Validator::make($request->all(), [
            'lead_task_type_id' => 'required',
            'title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        LeadTask::updateOrCreate(['id' => $request->lead_task_id], $request->all());
        if(isset($request->lead_task_id)){
            return response()->json(['success' => true, 'message' => 'Record updated successfully!']);
        } else {
            return response()->json(['success' => false, 'message' => 'Task added successfully!']);
        }
    }

    public function lead_task_delete($taskId, $leadId){
        if(LeadTask::deleteLeadTask($taskId) ){
            return redirect()->route('leads.edit', ['id' => $leadId])->with('success', 'Lead Taks deleted successfully');
        } else {
            return redirect()->route('leads.edit', ['id' => $leadId])->with('fails', 'Error in Lead task deletion');
        } 
    }

    // Lead Attachments start
    public function saveLeadAttachment(Request $request){
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
            LeadAttachment::create(array_merge($request->all(), ['image' => $filePath,  'mime_type' => $mimeType, 'size_in_bytes' => $sizeInBytes, ]) );
            return response()->json(['success' => true, 'message' => 'File uploaded successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Error in file upload!']);
        }
    }

    public function lead_attachments_delete($attachment_id, $leadId){
        if(LeadAttachment::deleteLeadAttachment($attachment_id)){
            return redirect()->route('leads.edit', ['id' => $leadId])->with('success', 'Lead Atachments deleted successfully');
        } else {
            return redirect()->route('leads.edit', ['id' => $leadId])->with('fails', 'Error in Lead attachments deletion');
        } 
    }
    // Lead Attachments end

    public function task_list(){
        $page = "Leads";
        $lead_tasks = LeadTask::getLeadTasks(0);
        return view('backEnd/salesFinance/leads/lead_task', compact('page', 'lead_tasks'));
    }

    public function lead_task_list_delete($id){
        $data = LeadTask::deleteLeadTask($id);
        if($data){
            return redirect()->route('leads.list')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.list')->with('error', "Record not found");
        }
    }

    public function lead_mark_as_completed($task_id, $leadId){
        if( LeadTask::taskMarkAsCompleted($task_id)){
            return redirect()->route('leads.edit', ['id' => $leadId])->with('success', 'Task mark as completed');
        } else {
            return redirect()->route('leads.edit', ['id' => $leadId])->with('fails', 'Error in task complete');
        } 
    }

    public function lead_authorized_by_admin($lead_id){
        if (Lead::LeadAuthorizedAdmin($lead_id)){
            return redirect()->route('leads.index')->with('success', "Lead Authorized successfully");
        } else {
            return redirect()->route('leads.index')->with('error', "Error in Lead Authorizataion");
        }
    }

    // CRM Section Type
    public function CRM_section_type(){
        $page = "crm_section_type";
        $crm_sections = CRMSectionType::getCRMSectionTypes();
        return view('backEnd/salesFinance/leads/CRM_section_type', compact('page', 'crm_sections'));

    }

    public function saveCRMSectionType(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'crm_section' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        CRMSectionType::updateOrCreate(['id' => $request->section_type_id],  array_merge($request->all() , ['home_id' => Session::get('scitsAdminSession')->home_id]));
        if ( isset($request->section_type_id)) {
            return response()->json(['success' => true, 'message' => 'CRM Section updated successfully']);
        } else {
            return response()->json(['success' => true, 'message' => 'CRM Section Type added successfully.']);
        }
    }

    public function crm_section_type_delete($id){
        $data = CRMSectionType::deleteCRMSectionType($id);
        if($data){
            return redirect()->route('leads.crm_section')->with('success', "Record deleted successfully");
        } else {
            return redirect()->route('leads.crm_section')->with('error', "Record not found");
        }
    }

}
