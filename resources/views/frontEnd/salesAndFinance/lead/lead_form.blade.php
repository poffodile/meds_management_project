@extends('frontEnd.layouts.master')
@include('frontEnd.salesAndFinance.jobs.layout.header')
@section('title',' Add Leads')
<link rel="stylesheet" href="{{ url('public/css/salesFinance/custom_lead.css') }}" />

<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .error {
        color: #e10078;
        font-size: 0.9em;
    }

    .next-30-days {
        color: #1f88b5;
        cursor: pointer;
    }
</style>
<?php
if (isset($lead)) {
    $action   = route('lead.store');
    $task     = "Edit";
    $form_id  = 'edit_leads_form';
    $readonly = '';
} else {
    $action  = route('lead.store');
    $task    = "Add";
    $form_id = 'add_leads_form';
}
?>

<section class="wrapper">

    <div class=" panel">
        <header class="panel-heading px-5">
            <h4>New Lead</h4>
        </header>
        <div class="panel-body">
            <form class="customerForm" method="Post" action="{{ $action }}" id="add_leads_form">
                @csrf
                <div class="row">
                    <!-- Lead Details -->
                    <div class="col-md-4">
                        <div class="formDtail">
                            <h4 class="contTitle">Lead Details</h4>

                            <div class="form-group my-3">
                                <label for="" class="col-sm-3 control-label">Lead Ref.</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="lead_id" value="{{ isset($lead->id) ? $lead->id : '' }}">
                                    <input type="hidden" name="customer_id" value="{{ isset($lead->customer_id) ? $lead->customer_id : '' }}">
                                    <p class="form-control-static">{{ isset($lead->lead_ref) ? $lead->lead_ref : 'Auto Generate' }}</p>
                                </div>
                            </div>

                            <div class="form-group m-tb-10">
                                <label class="col-sm-3 control-label">Assign To</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="assign_to">
                                        <option value="0">-Not Assigned-</option>
                                        @foreach($users as $value)
                                        <option value="{{ $value->id }}" {{ isset($lead->assign_to) && $lead->assign_to == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group m-tb-10">
                                <label class="col-sm-3 control-label">Source</label>
                                <div class="col-sm-9">
                                    <select name="source" class="form-control">
                                        <option value="0">None</option>
                                        @foreach($sources as $value)
                                        <option value="{{ $value->id }}" {{ isset($lead->source) && $lead->source == $value->id ? 'selected' : '' }}>{{ $value->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group m-tb-10">
                                <label class="col-sm-3 control-label">Status</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="status">
                                        @foreach($status as $value)
                                        <option value="{{ $value->id }}" @if($value->id == 6) disabled @endif {{ isset($lead->status) && $lead->status == $value->id ? 'selected' : '' }}>
                                            {{ $value->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group m-tb-10">
                                <label class="col-sm-3 control-label">Preferred date to call</label>
                                <div class="col-sm-3">
                                    <input type="date" name="prefer_date" class="form-control" value="{{ $lead->prefer_date ?? '' }}">
                                </div>
                                <div class="col-sm-1 text-center">
                                    <label class="control-label">To</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="time" name="prefer_time" class="form-control" value="{{ $lead->prefer_time ?? '' }}">
                                </div>
                                <div class="col-sm-2">
                                    <label class="control-label" data-toggle="modal" data-target="#next30daysModel" style="cursor:pointer;">Next 30 days</label>
                                </div>
                            </div>

                            <!-- Modal -->
                            <div class="modal fade" id="next30daysModel" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Next 30 days</h4>
                                        </div>
                                        <div class="modal-body">
                                            <button type="button" class="btn btn-default" id="printButton">Print</button>
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th># Name</th>
                                                        <th>Company</th>
                                                        <th>Address</th>
                                                        <th>Time</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Data Fields -->
                    <div class="col-md-4">
                        <div class="formDtail">
                            <h4 class="contTitle">Data Fields</h4>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Full Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="name" placeholder="Full Name" value="{{ $lead->contact_name ?? '' }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Company Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="company_name" placeholder="Company Name" value="{{ $lead->name ?? '' }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Email Address <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="{{ $lead->email ?? '' }}" required>
                                    @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Telephone <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="telephone" class="form-control" value="{{ $lead->telephone ?? '' }}" placeholder="Telephone" required oninput="this.value = this.value.replace(/\s/g, '').replace(/[^0-9]/g, '')">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Mobile</label>
                                <div class="col-sm-9">
                                    <input type="text" name="mobile" class="form-control" value="{{ $lead->mobile ?? '' }}" placeholder="Mobile">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="col-md-4">
                        <div class="formDtail">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Website</label>
                                <div class="col-sm-9">
                                    <input type="text" name="website" class="form-control" value="{{ $lead->website ?? '' }}" placeholder="Website">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Address</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="address" rows="3" placeholder="Address">{{ $lead->address ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">City</label>
                                <div class="col-sm-9">
                                    <input type="text" name="city" class="form-control" value="{{ $lead->city ?? '' }}" placeholder="City">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">County</label>
                                <div class="col-sm-9">
                                    <input type="text" name="country" class="form-control" value="{{ $lead->country ?? '' }}" placeholder="County">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Postcode</label>
                                <div class="col-sm-6">
                                    <input type="text" name="postal_code" class="form-control" value="{{ $lead->postal_code ?? '' }}" placeholder="Postcode">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="newJobForm mt-4" id="hiddenDiv">
        <label class="upperlineTitle">Extra Information</label>
        <div class="row">
            <div class="col-sm-12">
                <div class="extraInformationTab">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link active" id="nav-Notes-tab" data-bs-toggle="tab" data-bs-target="#nav-Notes" type="button" role="tab" aria-controls="nav-Notes" aria-selected="true">Notes</button>
                            <button class="nav-link" id="nav-Tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-Tasks" type="button" role="tab" aria-controls="nav-Tasks" aria-selected="false">Tasks</button>
                            <button class="nav-link" id="nav-attachments-tab" data-bs-toggle="tab" data-bs-target="#nav-attachments" type="button" role="tab" aria-controls="nav-attachments" aria-selected="false">Attachments</button>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-Notes" role="tabpanel" aria-labelledby="nav-Notes-tab" tabindex="0">
                            <div class="tabheadingTitle">
                                <h3>Notes - </h3>
                            </div>
                            <div class="col-sm-12">
                                <div class="mb-3 row">
                                    <label for="inputCountry" class="col-sm-2 col-form-label">Type</label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput" id="notes_type" name="notes_type">
                                            @if(isset($notes_type))
                                            @foreach($notes_type as $value)
                                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="plusandText">
                                            <a href="#!" class="formicon" data-bs-toggle="modal" data-bs-target="#notesModel"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-sm-7 mt-3">
                                        <textarea class="form-control textareaInput" placeholder="Notes" name="notes" id="notes" rows="3"></textarea>
                                    </div>
                                    <div class="col-sm-3 mt-3">
                                        <div class="jobsection">
                                            <button type="button" id="saveLeadNotes" class="profileDrop">Save Notes</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="productDetailTable pt-3">
                                            <table class="table" id="containerA">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>By</th>
                                                        <th>Type</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($lead_notes_data))
                                                    @foreach($lead_notes_data as $value)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y H:i') }}</td>
                                                        <td>{{ \App\User::where('id', $lead->user_id)->value('name') }}</td>
                                                        <td>{{ $value->title }}</td>
                                                        <td>{{ $value->notes }}</td>
                                                    </tr>
                                                    @endforeach
                                                    @else
                                                    <tr>
                                                        <td colspan="4">no data </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Button trigger modal -->

                                <!-- Modal -->
                                <div class="modal fade" id="notesModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content add_Customer">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-5" id="staticBackdropLabel">History Type - Add</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form role="form" id="lead_notes_type_form">
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">History Type*</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput" name="title" id="title">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Status</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-control editInput selectOptions" id="status" name="status">
                                                                <option value="1">Active</option>
                                                                <option value="0">Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer customer_Form_Popup">
                                                <div class="pageTitleBtn p-0">
                                                    <button type="button" class="profileDrop" id="addNotesType">Save</button>
                                                    <!-- <button type="button" class="profileDrop">Save & Close</button> -->
                                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal End -->
                            </div>
                            <!-- ENd col-9 -->
                        </div>
                        <div class="tab-pane fade" id="nav-Tasks" role="tabpanel" aria-labelledby="nav-Tasks-tab" tabindex="0">
                            <div class="tabheadingTitle">
                                <h3>Tasks - </h3>
                                <a href="#" class="profileDrop ms-3 open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel"><i class="fa-solid fa-floppy-disk"></i> New Tasks</a>
                            </div>
                            <div class="col-sm-12">
                                <!-- Modal -->
                                <div class="modal fade" id="tasksModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content add_Customer">
                                            <div class="modal-header">
                                                <h5 class="modal-title text fs-5 " id="staticBackdropLabel">Add Task</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" class="customerForm" id="addTask">
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Leads Ref.*</label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" id="lead_task_id" name="lead_task_id">
                                                            <input type="text" class="form-control editInput" name="lead_ref" id="leadsRef" value="{{ (isset($lead->lead_ref)) ? $lead->lead_ref : '' }}" id="lead_ref" placeholder="Leads Ref." readonly>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Task User*</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-control editInput selectOptions" name="user_id" id="user_id">
                                                                @foreach($users as $value)
                                                                <option value="{{ $value->id }}" {{ isset($lead->assign_to) && $lead->assign_to  == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Tasks Type*</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-control editInput selectOptions" id="lead_task_type_id" name="lead_task_type_id">
                                                                @if(isset($leadTask))
                                                                @foreach($leadTask as $value)
                                                                <option value="{{ $value->id }}" {{ isset($lead->assign_to) && $lead->assign_to  == $value->id ? 'selected' : '' }}>{{ $value->title }}</option>
                                                                @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Date*</label>
                                                        <div class="col-sm-4">
                                                            <input type="date" class="form-control editInput current_date_only" id="create_date" name="create_date">
                                                        </div>
                                                        <!-- <div class="col-sm-1 text-center">
                                                                        <i class="fa fa-calendar-days"></i>
                                                                    </div> -->
                                                        <div class="col-sm-4">
                                                            <input type="time" class="form-control editInput" id="create_time" name="create_time">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Title*</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput" id="task_title" name="title" placeholder="Title ">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Contact Name</label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" id="hiddenTaskContact" value="{{ (isset($lead->contact_name)) ? $lead->contact_name : '' }}">
                                                            <input type="text" class="form-control editInput" id="canatact_name" name="contact_name" placeholder="Enter name">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Contact Phone</label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" id="hiddenTaskPhone" value="{{ (isset($lead->telephone)) ? $lead->telephone : '' }}">
                                                            <input type="text" class="form-control editInput" id="phone_num" name="contact_phone" placeholder="Enter email">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Notify?</label>
                                                        <div class="col-sm-3 d-flex">
                                                            <input type="checkbox" class="editInput" id="yeson">
                                                            <label for="notify" class="col-form-label ps-3">Yes, On</label>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="date" class="form-control editInput current_date_only" disabled id="notifyDate" name="notify_date">
                                                        </div>
                                                        <!-- <div class="col-sm-1 text-center">
                                                                        <i class="fa fa-calendar-days"></i>
                                                                    </div> -->
                                                        <div class="col-sm-3">
                                                            <input type="time" class="form-control editInput" disabled id="notifyTime" name="notify_time">
                                                        </div>
                                                        <div class="col-sm-12 row">
                                                            <div class="col-sm-3"></div>
                                                            <div class="col-sm-8">
                                                                <div id="optionsDiv">
                                                                    <label class="editInput"><input type="checkbox" value="1" id="notificationCheckbox" name="notification"> Notification</label>
                                                                    <label class="editInput"><input type="checkbox" value="1" id="emailCheckbox" name="email_notify"> Email</label>
                                                                    <label class="editInput"><input type="checkbox" value="1" id="smsCheckbox" name="sms_notify"> SMS</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Notes</label>
                                                        <div class="col-sm-9">
                                                            <textarea class="form-control textareaInput" name="notes" id="notes" rows="3" placeholder="Notes"></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer customer_Form_Popup">
                                                <div class="pageTitleBtn p-0">
                                                    <a href="#" class="profileDrop" id="saveAddTask"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                                                    <!-- <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save & Close </a> -->
                                                    <a href="#" class="profileDrop" data-bs-dismiss="modal"> Close</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="productDetailTable mt-3">
                                    <table class="table" id="taskTableData">
                                        <thead class="table-light">
                                            <tr>
                                                <th># </th>
                                                <th>Date </th>
                                                <th>User</th>
                                                <th>Tasks Type </th>
                                                <th>Title </th>
                                                <th>Contact Name </th>
                                                <th>Contact Phone </th>
                                                <th>Notify </th>
                                                <th>Notes </th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="10"><strong>Open Tasks</strong></td>
                                            </tr>
                                            @if(isset($lead_task_open))
                                            @if( !$lead_task_open->isEmpty() )
                                            @foreach($lead_task_open as $value)
                                            @php
                                            $create_date = \Carbon\Carbon::parse($value->create_date)->format('d/m/Y');
                                            $create_time = \Carbon\Carbon::parse($value->create_time)->format('H:i');
                                            $combinedDateTime = \Carbon\Carbon::createFromFormat('d/m/Y H:i', "$create_date $create_time");
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $combinedDateTime->format('d/m/Y H:i')  }}</td>
                                                <td>{{ $value->name }}</td>
                                                <td>{{ $value->task_type_title}}</td>
                                                <td>{{ $value->title}}</td>
                                                <td>{{ $value->contact_name ?  $value->contact_name : $lead->contact_name }}</td>
                                                <td>{{$lead->telephone}}</td>
                                                <td> @if( $value->notification === 1 || $value->email_notify === 1 || $value->sms_notify === 1)
                                                    Yes, on<br>
                                                    {{ \Carbon\Carbon::parse($value->notify_date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($value->notify_time)->format('h:i') }}
                                                    @else
                                                    <span>No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $value->notes}}</td>
                                                <td>
                                                    <div class="nav-item dropdown tableActionBtn">
                                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                            Action
                                                        </a>
                                                        <div class="dropdown-menu fade-up m-0">
                                                            <a href="javascript:void(0)" onclick="mark_as_complete_task('{{ $value->id }}', '{{$lead->id}}', '{{$value->lead_ref}}')" class="dropdown-item">Mark As Completed</a>
                                                            <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="{{ $value->id }}" data-user_id="{{ $value->user_id }}" data-title="{{ $value->title }}" data-contact_name="{{ $value->contact_name ? $value->contact_name : $lead->contact_name }}" data-contact_phone="{{ $value->contact_phone ? $value->contact_phone : $lead->telephone }}" data-task_type_id="{{ $value->lead_task_type_id }}" data-create_date="{{ $value->create_date }}" data-create_time="{{ $value->create_time}}" data-notify_date="{{ $value->notify_date }}" data-notify_time="{{ $value->notify_time }}" data-notes="{{ $value->notes }}" data-notification="{{ $value->notification }}" data-email_notify="{{ $value->email_notify }}" data-sms_notify="{{ $value->sms_notify }}">Edit Task</a>
                                                            <a href="{{ url('/leads/lead_task/delete',['task' => $value->id, 'lead_id' => $lead->id]) }}" class="dropdown-item">Delete Task</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td colspan="10" class="text-center"><strong>No task(s) found</strong></td>
                                            </tr>
                                            @endif

                                            <tr>
                                                <td colspan="10"><strong>Close Tasks</strong></td>
                                            </tr>
                                            @endif
                                            @if(isset($lead_task_open))
                                            @if( !$lead_task_close->isEmpty())
                                            @foreach($lead_task_close as $value)
                                            @php
                                            $create_date = \Carbon\Carbon::parse($value->create_date)->format('d/m/Y');
                                            $create_time = \Carbon\Carbon::parse($value->create_time)->format('H:i');
                                            $combinedDateTime = \Carbon\Carbon::createFromFormat('d/m/Y H:i', "$create_date $create_time");
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration}}</td>
                                                <td>{{$combinedDateTime->format('d/m/Y H:i') }}</td>
                                                <td>{{ $value->name }}</td>
                                                <td>{{ $value->task_type_title}}</td>
                                                <td>{{ $value->title}}</td>
                                                <td>{{ $value->contact_name ?  $value->contact_name : $lead->contact_name }}</td>
                                                <td>{{$lead->telephone}}</td>
                                                <td> @if( $value->notification === 1 || $value->email_notify === 1 || $value->sms_notify === 1)
                                                    Yes, on<br>
                                                    {{ \Carbon\Carbon::parse($value->notify_date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($value->notify_time)->format('h:i') }}
                                                    @endif
                                                </td>
                                                <td>{{ $value->notes}}</td>
                                                <td>
                                                    <div class="nav-item dropdown tableActionBtn">
                                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                            Action
                                                        </a>
                                                        <div class="dropdown-menu fade-up m-0">
                                                            <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="{{ $value->id }}" data-user_id="{{ $value->user_id }}" data-title="{{ $value->title }}" data-contact_name="{{ $value->contact_name ? $value->contact_name : $lead->contact_name }}" data-contact_phone="{{ $value->contact_phone ? $value->contact_phone : $lead->telephone }}" data-task_type_id="{{ $value->lead_task_type_id }}" data-create_date="{{ $value->create_date }}" data-create_time="{{ $value->create_time}}" data-notify_date="{{ $value->notify_date }}" data-notify_time="{{ $value->notify_time }}" data-notes="{{ $value->notes }}" data-notification="{{ $value->notification }}" data-email_notify="{{ $value->email_notify }}" data-sms_notify="{{ $value->sms_notify }}">Edit Task</a>
                                                            <a href="{{ url('/leads/lead_task/delete',['task' => $value->id, 'lead_id' => $lead->id]) }}" class="dropdown-item">Delete Task</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td colspan="10" class="text-center"><strong>No task(s) found</strong></td>
                                            </tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="nav-attachments" role="tabpanel" aria-labelledby="nav-attachments-tab" tabindex="0">
                            <div class="tabheadingTitle">
                                <h3>Attachments - </h3>
                                <a href="#" class="profileDrop ms-3 open-modal-attachment" data-bs-toggle="modal" data-bs-target="#attechmentModel"><i class="fa-solid fa-floppy-disk"></i> Attachments</a>
                            </div>
                            <div class="col-md-7">
                                <div class="jobsection d-flex">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <!-- Modal -->
                                <div class="modal fade" id="attechmentModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content add_Customer">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-5" id="staticBackdropLabel">Add Attachments</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" id="imageUploadForm" enctype="multipart/form-data" class="customerForm pt-0">
                                                    <div><span id="error-message" class="error"></span></div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Lead Ref.</label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" name="lead_id" id="lead_id" value="{{ (isset($lead->id)) ? $lead->id : '' }}">
                                                            <input type="hidden" id="lead_attachment_id" name="lead_attachment_id">
                                                            <input type="text" class="form-control-plaintext editInput" name="lead_ref" id="leadsRef" value="{{ (isset($lead->lead_ref)) ? $lead->lead_ref : '' }}" id="lead_ref" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Type</label>
                                                        <div class="col-sm-9">
                                                            <select class="form-control editInput selectOptions" name="attachment_type_id" id="attachment_type_id">
                                                                <option value="">Select</option>
                                                                @if(isset($attachment_type))
                                                                @foreach($attachment_type as $value)
                                                                <option value="{{ $value->id }}" {{ isset($lead->assign_to) && $lead->assign_to  == $value->id ? 'selected' : '' }}>{{ $value->title }}</option>
                                                                @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputCustomer" class="col-sm-3 col-form-label">File Name*</label>
                                                        <div class="col-sm-9">
                                                            <input type="file" class="editInput" multiple="false" id="file" name="file" accept="image/*" id="finput" onchange="upload()">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Title</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput" id="attachment_title" name="title" value="">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Description
                                                            (max 500 characters)</label>
                                                        <div class="col-sm-9">
                                                            <textarea class="form-control textareaInput" name="description" id="description" maxlength="500" rows="3" placeholder="Description"></textarea>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer customer_Form_Popup">
                                                <div class="pageTitleBtn p-0">
                                                    <a href="#" class="profileDrop" id="saveAttachmentType"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                                                    <a href="#" class="profileDrop" data-bs-dismiss="modal"> Close</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="productDetailTable mt-3">
                                    <table class="table" id="containerA">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"></th>
                                                <th>#</th>
                                                <th>Type</th>
                                                <th>Title</th>
                                                <th>Description</th>
                                                <th>File Name</th>
                                                <th>Mime Type / Size</th>
                                                <th>Created On</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(isset($lead_attachment))
                                            @foreach($lead_attachment as $value)
                                            <tr>
                                                <td>
                                                    <div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$value['id']}}"></div>
                                                </td>
                                                <td></td>
                                                <td>{{ $value['type'] }}</td>
                                                <td>{{ $value['title'] }}</td>
                                                <td>{{ $value['description'] }}</td>
                                                <td>{{ $value['filename'] }}</td>
                                                <td>{{ $value['mime_type'] }} / {{ $value['size'] }}</td>
                                                <td>{{ $value['created_at'] }}</td>
                                                <td><a href="{{ url('storage/app/public/lead_attachments/' . $value['filename']) }}" target="_blank"><i data-toggle="tooltip" data-original-title="View" class="fa fa-eye"></i></a> | <a href="{{ url('/leads/lead_attachment/delete', ['attachment_id' => $value['id'], 'lead_id' => $lead->id]) }}" class="delete"><i data-toggle="tooltip" class="fa fa-trash" data-original-title="Delete" aria-describedby="tooltip895132"></i></a></td>
                                            </tr>
                                            @endforeach
                                            @else
                                            <tr>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                                <td>...</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
            <div class="pageTitleBtn">
                <button type="submit" class="profileDrop reDesignBtn" id="submit_main_form"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                <!-- <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i></a> -->
            </div>
        </div>
    </div>
</section>

<?php
if (isset($lead)) {
    $lead_id =  $lead->id;
    $lead_contact = $lead->contact_name;
    $lead_telephone = $lead->telephone;
} else {
    $lead_id = '';
    $lead_contact = '';
    $lead_telephone = '';
}
?>
</section>
<script>
    var addNotesTypeURL = '{{ route("lead.ajax.saveLeadNoteType") }}';
    var saveLeadNotes = '{{ route("lead.ajax.saveLeadNotes") }}';
    const addLeadTaskUrl = '{{ route("lead.ajax.saveLeadTasks") }}';
    var saveLeadAttachmentUrl = '{{ route("lead.ajax.saveLeadAttachment") }}';
    var getLeadTaskDataURL = '{{ route("lead.ajax.getLeadTaskOnLeadId") }}';
    const get30DaysLead = '{{ route("lead.ajax.get30DaysLead") }}';
    const markAsComplete = '{{ route("lead.task_mark_as_completed", ["task" => ":task"]) }}';
    const lead_id = '{{ $lead_id}}';
    const lead_contact = '{{ $lead_contact }}';
    const lead_telephone = '{{ $lead_telephone }}';
    const baseMarkAsCompletedURL = "{{ url('/lead/task_mark_as_completed', ['task' => '__TASK_ID__','lead_id' => $lead_id ]) }}";
    const baseDeleteURL = "{{ url('/leads/lead_task/delete', ['task' => '__TASK_ID__', 'lead_id' => $lead_id]) }}";
</script>
<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {
            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'LeadAttachment';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (data) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                        }
                        // return false;
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                    }
                });
            }
        }

    });
    $('.delete_checkbox').on('click', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });
</script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script type="text/javascript" src="{{ url('public/js/salesFinance/customLeadForm.js') }}"></script>