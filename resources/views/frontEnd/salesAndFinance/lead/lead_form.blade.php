@include('frontEnd.salesAndFinance.jobs.layout.header')
@section('title',' Add Leads')
<link rel="stylesheet" href="{{ url('public/css/salesFinance/custom_lead.css') }}" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .error {
        color: red;
        font-size: 0.9em;
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

<section class="main_section_page px-3 pt-0">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>New Lead</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <form class="customerForm" method="Post" action="{{ $action }}" id="add_leads_form">
                    @csrf
                    <div class="newJobForm card">
                        <div class="row">
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Lead Details</h4>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Lead Ref.</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" name="lead_id" id="lead_id" value="{{ (isset($lead->id)) ? $lead->id : '' }}">
                                            <input type="hidden" name="customer_id" value="{{ (isset($lead->customer_id)) ? $lead->customer_id : '' }}">
                                            <input type="text" name="lead_ref" class="form-control-plaintext editInput" id="" placeholder="Auto Generate" value="{{ (isset($lead->lead_ref)) ? $lead->lead_ref : '' }}" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Assign To</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" name="assign_to" id="assign_to">
                                                <option value="0">-Not Assigned-</option>
                                                @foreach($users as $value)
                                                <option value="{{ $value->id }}" {{ isset($lead->assign_to) && $lead->assign_to  == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div><!-- End off Customer -->
                                    <div class="mb-3 row">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Source</label>
                                        <div class="col-sm-9">
                                            <select name="source" id="inputCustomer" class="form-control editInput selectOptions">
                                                <option value="0">None</option>
                                                @foreach($sources as $value)
                                                <option value="{{ $value->id }}" {{ isset($lead->source) && $lead->source == $value->id ? 'selected' : '' }}>{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputContact" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="inputCustomer" name="status">
                                                @foreach($status as $value)
                                                <option value="{{ $value->id }}" @if($value->id == 6) disabled @endif {{ isset($lead->status) && $lead->status == $value->id ? 'selected' : '' }} >{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Preferred date to call</label>
                                        <div class="col-sm-3 pe-0">
                                            <input type="date" name="prefer_date" class="form-control editInput" value="{{ (isset($lead->prefer_date)) ? $lead->prefer_date : '' }}" id="">
                                        </div>
                                        <div class="col-sm-1 text-center"><label class="col-form-label">To</label></div>
                                        <div class="col-sm-3 ps-0">
                                            <input type="time" name="prefer_time" class="form-control editInput" value="{{ (isset($lead->prefer_time)) ? $lead->prefer_time : '' }}" id="">
                                        </div>
                                        <div class="col-sm-2 p-0">
                                            <label for="inputContact" class="col-form-label">Next 30 days</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Data Fields</h4>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Full Name *</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" name="name" id="inputName" placeholder="Full Name" value="{{ (isset($lead->contact_name)) ? $lead->contact_name : '' }}" required>
                                            <span id="fullNameError" class="error"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Company Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" name="company_name" id="inputcompany" placeholder="Company Name" value="{{ (isset($lead->name)) ? $lead->name : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Email Address *</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="email" class="form-control editInput" id="inputEmail" placeholder="Email Address" value="{{ (isset($lead->email)) ? $lead->email : '' }}" required>
                                            <span id="emailError" class="error"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone *</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="telephone" class="form-control editInput" id="inputTelephone" placeholder="Telephone " value="{{ (isset($lead->telephone)) ? $lead->telephone : '' }}" required>
                                            <span id="phoneError" class="error"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="mobile" class="form-control editInput" id="inputMobile" placeholder="Mobile" value="{{ (isset($lead->mobile)) ? $lead->mobile : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle p-2"></h4>
                                    <div class="mb-3 row">
                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Website</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="website" class="form-control editInput" id="inputJobRef" value="{{ (isset($lead->website)) ? $lead->website : '' }}" placeholder="Website">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address" id="inputAddress" rows="3" placeholder="Address">{{ (isset($lead->address)) ? $lead->address : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" name="city" class="form-control editInput textareaInput" id="inputCustomer" placeholder="City" value="{{ (isset($lead->city)) ? $lead->city : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County </label>
                                        <div class="col-sm-9">
                                            <input type="text" name="country" class="form-control editInput textareaInput" id="inputPurchase" placeholder="County" value="{{ (isset($lead->country)) ? $lead->country : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode </label>
                                        <div class="col-sm-6">
                                            <input type="text" name="postal_code" class="form-control editInput textareaInput" id="inputPurchase" placeholder="Postcode" value="{{ (isset($lead->postal_code)) ? $lead->postal_code : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End  off newJobForm -->
                </form>
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
                                                                    <th>Data</th>
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
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">History Type - Add</h1>
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
                                                        <div class="modal-footer">
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
                                        </div><!-- ENd col-9 -->
                                    </div>
                                    <div class="tab-pane fade" id="nav-Tasks" role="tabpanel" aria-labelledby="nav-Tasks-tab" tabindex="0">
                                        <div class="tabheadingTitle">
                                            <h3>Tasks - </h3>
                                            <a href="#" class="profileDrop ms-3 open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel"><i class="fa-solid fa-floppy-disk"></i> New Tasks</a>
                                        </div>
                                        <div class="col-sm-12">
                                            <!-- Modal -->
                                            <div class="modal fade" id="tasksModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Task</h1>
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
                                                                        <input type="date" class="form-control editInput" id="create_date" name="create_date">
                                                                    </div>
                                                                    <div class="col-sm-1 text-center">
                                                                        <i class="fa fa-calendar-days"></i>
                                                                    </div>
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
                                                                        <input type="text" class="form-control editInput" id="canatact_name" name="canatact_name" value="{{ (isset($lead->contact_name)) ? $lead->contact_name : '' }}" placeholder="Enter name">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Contact Phone</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="phone_num" name="phone_num" value="{{ (isset($lead->telephone)) ? $lead->telephone : '' }}" placeholder="Enter email">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Notify?</label>
                                                                    <div class="col-sm-2 d-flex">
                                                                        <input type="checkbox" class="editInput" id="yeson">
                                                                        <label for="notify" class="col-form-label ps-3">Yes, On</label>
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <input type="date" class="form-control editInput" id="notifyDate" name="notify_date">
                                                                    </div>
                                                                    <div class="col-sm-1 text-center">
                                                                        <i class="fa fa-calendar-days"></i>
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <input type="time" class="form-control editInput" id="notifyTime" name="notify_time">
                                                                    </div>
                                                                    <div id="optionsDiv">
                                                                        <label class="editInput"><input type="checkbox" value="1" id="notificationCheckbox" name="notification"> Notification</label>
                                                                        <label class="editInput"><input type="checkbox" value="1" id="emailCheckbox" name="email_notify"> Email</label>
                                                                        <label class="editInput"><input type="checkbox" value="1" id="smsCheckbox" name="sms_notify"> SMS</label>
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
                                                        <div class="modal-footer">
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
                                                            <td colspan="10"><strong>Open tasks</strong></td>
                                                        </tr>
                                                        @if(isset($lead_task_open))
                                                        @if( !$lead_task_open->isEmpty() )
                                                        @foreach($lead_task_open as $value)

                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y m:i')}}</td>
                                                            <td>{{ $value->name }}</td>
                                                            <td>{{ $value->task_type_title}}</td>
                                                            <td>{{ $value->title}}</td>
                                                            <td>{{$lead->contact_name}}</td>
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
                                                                        <a href="{{ url('/lead/task_mark_as_completed', ['task' => $value->id, 'lead_id' => $lead->id]) }}" class="dropdown-item">Mark As Completed</a>
                                                                        <hr class="dropdown-divider">
                                                                        <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="{{ $value->id }}" data-user_id="{{ $value->user_id }}" data-title="{{ $value->title }}" data-task_type_id="{{ $value->lead_task_type_id }}" data-create_date="{{ $value->create_date }}" data-create_time="{{ $value->create_time}}" data-notify_date="{{ $value->notify_date }}" data-notify_time="{{ $value->notify_time }}" data-notes="{{ $value->notes }}" data-notification="{{ $value->notification }}" data-email_notify="{{ $value->email_notify }}" data-sms_notify="{{ $value->sms_notify }}">Edit Task</a>
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
                                                            <td colspan="10"><strong>Close tasks</strong></td>
                                                        </tr>
                                                        @endif
                                                        @if(isset($lead_task_open))
                                                        @if( !$lead_task_close->isEmpty())
                                                        @foreach($lead_task_close as $value)
                                                        <tr>
                                                            <td>{{ $loop->iteration}}</td>
                                                            <td>{{ \Carbon\Carbon::parse($value->created_at)->format('d/m/Y m:i')}}</td>
                                                            <td>{{ $value->name }}</td>
                                                            <td>{{ $value->task_type_title}}</td>
                                                            <td>{{ $value->title}}</td>
                                                            <td>{{$lead->contact_name}}</td>
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
                                                                        <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="{{ $value->id }}" data-user_id="{{ $value->user_id }}" data-title="{{ $value->title }}" data-task_type_id="{{ $value->lead_task_type_id }}" data-create_date="{{ $value->create_date }}" data-create_time="{{ $value->create_time}}" data-notify_date="{{ $value->notify_date }}" data-notify_time="{{ $value->notify_time }}" data-notes="{{ $value->notes }}" data-notification="{{ $value->notification }}" data-email_notify="{{ $value->email_notify }}" data-sms_notify="{{ $value->sms_notify }}">Edit Task</a>
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
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Attachments</h1>
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
                                                        <div class="modal-footer">
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
                                                            <td><div class="text-center"><input type="checkbox" id="" class="delete_checkbox" value="{{$value['id']}}"></div></td>
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
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="pageTitleBtn">
                    <button type="submit" class="profileDrop reDesignBtn" id="submit_main_form"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i></a>
                </div>
            </div>
        </div>
        </form>
    </div>
</section>
<script>
    var addNotesTypeURL = '{{ route("lead.ajax.saveLeadNoteType") }}';
    var saveLeadNotes = '{{ route("lead.ajax.saveLeadNotes") }}';
    const addLeadTaskUrl = '{{ route("lead.ajax.saveLeadTasks") }}';
    var saveLeadAttachmentUrl = '{{ route("lead.ajax.saveLeadAttachment") }}';
    var getLeadTaskDataURL = '{{ route("lead.ajax.getLeadTaskOnLeadId") }}';
</script>
<script>
   $("#deleteSelectedRows").on('click', function() {
    let ids = [];
    
    $('.delete_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    if(ids.length == 0){
        alert("Please check the checkbox for delete");
    }else{
        if(confirm("Are you sure to delete?")){
            // console.log(ids);
            var token='<?php echo csrf_token();?>'
            var model='LeadAttachment';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    console.log(data);
                    if(data){
                        location.reload();
                    }else{
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

<script>
    function setleadTaskTableData(data, tableBody, table) {

        tableBody.innerHTML = '';

        if (data.length === 0) {
            // Handle the case where there is no data
            const errorRow = document.createElement('tr');
            const errorCell = document.createElement('td');
            errorCell.colSpan = 8; // Adjust this based on the number of columns in your table
            errorCell.classList.add('red_sorryText');
            errorCell.textContent = 'Sorry, no records to show ';
            errorCell.style.textAlign = 'center'; // Optional: Center the text
            errorRow.appendChild(errorCell);
            tableBody.appendChild(errorRow);
            return; // Exit the function
        }

        let countValue = 1;

        data.forEach(item => {

            // Create a new row
            const row = document.createElement('tr');

            const count = document.createElement('td');
            count.textContent = countValue;
            row.appendChild(count);

            const created_on = document.createElement('td');
            created_on.textContent = moment(item.created_at).format('DD/MM/YYYY HH:mm');
            row.appendChild(created_on);

            const name = document.createElement('td');
            name.innerHTML = item.name;
            row.appendChild(name);

            const task_type_title = document.createElement('td');
            task_type_title.innerHTML = item.task_type_title;
            row.appendChild(task_type_title);

            const title = document.createElement('td');
            title.textContent = item.title;
            row.appendChild(title);

            const contact_name = document.createElement('td');
            contact_name.innerHTML = <?php echo  $lead->contact_name; ?>;
            row.appendChild(contact_name);

            const telephone = document.createElement('td');
            telephone.innerHTML = <?php echo $lead->telephone; ?>;
            row.appendChild(telephone);

            if (item.notification === 1 || item.sms === 1 || item.email === 1) {

                const notifyDate = item.notify_date; // Example: '2025-01-24'
                const notifyTime = item.notify_time; // Example: '03:40'

                // Combine date and time
                const dateTime = `${notifyDate} ${notifyTime}`;
                const formattedDateTime = moment(dateTime, 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY HH:mm');

                const text = document.createElement('td');
                text.innerHTML = `Yes, On  <br> ${formattedDateTime}`;
                row.appendChild(text);
            } else {
                const text = document.createElement('td');
                text.innerHTML = "No";
                row.appendChild(text);
            }


            const notes = document.createElement('td');
            notes.innerHTML = item.notes;
            row.appendChild(notes);

            const baseMarkAsCompletedURL = "{{ url('/lead/task_mark_as_completed', ['task' => '__TASK_ID__', 'lead_id' => $lead->id]) }}";
            const baseDeleteURL = "{{ url('/leads/lead_task/delete', ['task' => '__TASK_ID__', 'lead_id' => $lead->id]) }}";

            const idCell = document.createElement('td');
            idCell.innerHTML = `<div class="nav-item dropdown tableActionBtn">
                    <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">Action</a>
                    <div class="dropdown-menu fade-up m-0">
                        <a href="${baseMarkAsCompletedURL.replace('__TASK_ID__', item.id)}" class="dropdown-item">Mark As Completed</a>
                        <hr class="dropdown-divider">
                        <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="${item.id}" data-user_id="${item.user_id}" data-title="${item.title}" data-task_type_id="${item.lead_task_type_id}" data-create_date="${item.create_date}" data-create_time="${item.create_time}" data-notify_date="${item.notify_date}" data-notify_time="${item.notify_time}" data-notes="${item.notes}" data-notification="${item.notification}" data-email_notify="${item.email_notify}" data-sms_notify="${item.sms_notify}">Edit Task</a>
                        <a href="${baseDeleteURL.replace('__TASK_ID__', item.id)}" class="dropdown-item">Delete Task</a>
                    </div>
                </div>`;
            row.appendChild(idCell);

            // Append the row to the table body
            tableBody.appendChild(row);
            countValue++;
        });

    }
</script>