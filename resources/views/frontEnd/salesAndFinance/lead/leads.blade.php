@include('frontEnd.salesAndFinance.jobs.layout.header')
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.css">
<script src="https://cdn.ckeditor.com/ckeditor5/ckeditor.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* .CRMFullModel .modal-dialog.modal-xl {
        --bs-modal-width: 1600px;
    } */

    .overdue {
        display: flex;
        justify-content: end;
    }

    .overdue label {
        line-height: 22px;
        font-size: 13px;
        font-weight: 600;
    }

    .yeloColrbox {
        width: 20px;
        height: 20px;
        display: block;
        background-color: #FFCC66;
        margin-right: 4px;
    }

    .popup2 {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 99999;
        transition: opacity 0.3s ease;
    }

    .col-form-label p {
        margin: 0;
        line-height: 15px;
    }

    #showDivContLeads.show {
        height: 0;
    }

    #showDivContLeads {
        height: 200px;
        transition: .7s;
        overflow: hidden;
    }

    .btnActive {
        background-color: #494949;
    }

    .days_check div {
        display: flex;
        justify-content: space-between;
    }

    .days_check div label {
        padding: 0;
    }

    .days_check .form-check-input {
        margin-top: 2px;
    }
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>
                        @switch($lastSegment)
                        @case("leads") Leads @break
                        @case("myLeads") My Leads @break
                        @case("unassigned") Unassigned Leads @break
                        @case("rejected") Rejected Leads @break
                        @case("authorization") Authorization Leads @break
                        @case("converted") Converted Leads @break
                        @default {{-- No output if none of the cases match --}}
                        @endswitch
                    </h3>
                </div>
            </div>
        </div>

        @include('frontEnd.salesAndFinance.lead.lead_buttons')

        <div class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!" id="exportCsv">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>
                    </div>
                    <!-- Ram 15/10/2024 here code for bulk delete -->
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <!-- <a href="#" class="profileDrop">Mark As completed</a> -->
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end here -->
                    <div class="table-responsive" style="min-height: 350px;">
                        <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <!-- Ram bulk delete -->
                                    <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                    <th>#</th>
                                    <th>Lead Ref.</th>
                                    <th>Full Name</th>
                                    <th>Company Name</th>
                                    <th>Email Address</th>
                                    <th>Telephone</th>
                                    <th>Mobile</th>
                                    <th>Assigned User</th>
                                    <th>Status</th>
                                    <th>Website</th>
                                    <th>Address</th>
                                    <th>City </th>
                                    <th>County </th>
                                    <th>Postcode</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($customers as $customer)
                                @php
                                $authorizationText = '';
                                if ($customer->status_id == 7) {
                                if ($customer->authorization_status === 1) {
                                $authorizationText = 'Waiting for Authorization';
                                } elseif ($customer->authorization_status === 2) {
                                $authorizationText = 'Authorized';
                                } else {
                                $authorizationText = 'none';
                                }
                                }
                                @endphp
                                <tr>
                                    <!-- Ram bulk delete -->
                                    <td><input type="checkbox" id="" class="delete_checkbox" value="{{$customer->id}}"></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $customer->lead_ref }}</td>
                                    <td>{{ $customer->contact_name }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->telephone }}</td>
                                    <td>{{ $customer->mobile }}</td>
                                    <td>{{ App\User::getLeadAssignUserName($customer->assign_to) }}</td>
                                    <td>{{ $customer->status_id == 7 ? $authorizationText : $customer->status }}</td>
                                    <td>{{ $customer->website }}</td>
                                    <td>{{ $customer->address }}</td>
                                    <td>{{ $customer->city }}</td>
                                    <td>{{ $customer->country }}</td>
                                    <td>{{ $customer->postal_code }}</td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                    Action
                                                </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="{{ url('/leads/edit').'/'.$customer->id }}" class="dropdown-item">Edit Details</a>
                                                    <a href="#" class="dropdown-item">Send SMS</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="#" class="dropdown-item set_value_on_CRM_model" data-user-id="{{ $customer->id }}" data-ref="{{ $customer->lead_ref }}" data-contact-name="{{ $customer->contact_name }}" data-email="{{ $customer->email }}" data-name="{{ $customer->name }}" data-status="{{ $customer->status }}" data-telephone="{{ $customer->telephone }}" class="dropdown-item">CRM History</a>
                                                    <a href="#" class="dropdown-item open-modal" data-lead_ref="{{ $customer->lead_ref }}" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</a>
                                                    <a href="{{ url('/leads/authorization').'/'.$customer->id }}" class="dropdown-item">Send for Authorization</a>
                                                    <a href="javaScript:void(0)" onclick="openSentQuoteModal('{{ $customer->lead_ref }}', '{{ $customer->id}}', '{{ $customer->customer_id }}' ,'quote')" class="dropdown-item">Send to Quote</a>
                                                    <a href="#" class="dropdown-item">Send to Job</a>
                                                    <a href="#" class="dropdown-item" onclick="openSentQuoteModal('{{ $customer->lead_ref }}', '{{ $customer->id}}', '{{ $customer->customer_id }}' ,'customer')">Convert to Customer Only</a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- **************** Reject Modal Start ****************-->
                                        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Reject Confirmation</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form id="lead_reject_reason_form">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label for="recipient-name" class="col-form-label">Lead Ref:</label>
                                                                <input type="text" name="lead_ref" class="form-control editInput" id="lead_ref" placeholder="Auto Generate" value="">
                                                                <!-- <input type="text" class="form-control" id="recipient-name"> -->
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="recipient-name" class="col-form-label">Reject Type:</label>
                                                                <div class="row">
                                                                    <div class="col-10">
                                                                        <select name="reject_type_id" class="form-control editInput" id="">
                                                                            @foreach($leadRejectTypes as $value)
                                                                            <option value="{{ $value->id }}">{{ $value->title}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-2 d-flex align-items-center">
                                                                        <a href="#!"  data-bs-toggle="modal" data-bs-target="#rejectModal2">
                                                                            <i class="fa-solid fa-square-plus fs-4"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="message-text" class="col-form-label">Reject Reason:</label>
                                                                <textarea name="reject_reason" class="form-control editInput" id=""></textarea>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                                                        <button type="button" class="profileDrop" id="lead_reject_reason">Confirm Reject</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="rejectModal2" tabindex="1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="exampleModalLabel2"> Add Lead Reject Type </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form id="lead_reject_type_form_edit">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label for="recipient-name" class="col-form-label">Lead Reject Type :</label>
                                                                <input type="text" class="form-control editInput" name="title" id="recipient-name">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="message-text" class="col-form-label">Status:</label>
                                                                <select name="status" class="form-control editInput" id="">
                                                                    <option value="1">Active</option>
                                                                    <option value="0">InActive</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                                                        <button type="button" id="lead_reject" class="profileDrop">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--  **************** Reject Model End *****************  -->
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div> <!-- End off main Table -->
            </div>
            </di>
        </div>
</section>
<!-- ****************CRM History Modal ****************-->
<div class="modal fade CRMFullModel" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">CRM Dashboard - <span id="calls_lead_ref"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body crmModelCont pt-2">
                <div class="jobsection pb-2 hideandshow">
                    <button class="profileDrop" id="onclickbtnHideShowLeads">Hide/Show</button>
                </div>
                <div id="showDivContLeads">
                    <div class="newJobForm mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="">
                                    <h4 class="contTitle text-center">Contact Details</h4>
                                </div>
                                <div class="row pt-2">
                                    <label class="col-md-4 col-form-label">Full Name:</label>
                                    <div class="col-md-8 small_content">
                                        <span id="calls_contact_name"></span>
                                    </div>
                                </div>
                                <div class="row pt-2">
                                    <label class="col-md-4 col-form-label">Email Address:</label>
                                    <div class="col-md-8 small_content">
                                        <span id="calls_email"></span>
                                    </div>
                                </div>
                                <div class="row pt-2">
                                    <label class="col-md-4 col-form-label">Telephone:</label>
                                    <div class="col-md-8 small_content">
                                        <span id="calls_telephone"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="">
                                    <h4 class="contTitle text-center">Lead Details</h4>
                                </div>
                                <div class="row pt-2">
                                    <label class="col-md-4 col-form-label">Lead Ref.:</label>
                                    <div class="col-md-8 small_content">
                                        <span id="calls_lead_refs"></span>
                                        <input type="hidden" id="lead_id_CRM" name="">
                                    </div>
                                </div>
                                <div class="row pt-2">
                                    <label class="col-md-4 col-form-label">Lead Status:</label>
                                    <div class="col-md-8 small_content">
                                        <span id="calls_status"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-fullHistory-tab" data-bs-toggle="pill" data-bs-target="#pills-fullHistory" type="button" role="tab" aria-controls="pills-fullHistory" aria-selected="true">Full History</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-Calls-tab" data-bs-toggle="pill" data-bs-target="#pills-Calls" type="button" role="tab" aria-controls="pills-Calls" aria-selected="false">Calls</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-emails-tab" data-bs-toggle="pill" data-bs-target="#pills-emails" type="button" role="tab" aria-controls="pills-emails" aria-selected="false">Emails</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-tasks-tab" data-bs-toggle="pill" data-bs-target="#pills-tasks" type="button" role="tab" aria-controls="pills-tasks" aria-selected="false">Tasks</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-notes-tab" data-bs-toggle="pill" data-bs-target="#pills-notes" type="button" role="tab" aria-controls="pills-notes" aria-selected="false">Notes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-complaints-tab" data-bs-toggle="pill" data-bs-target="#pills-complaints" type="button" role="tab" aria-controls="pills-complaints" aria-selected="false">Complaints</button>
                    </li>
                </ul>
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-fullHistory" role="tabpanel" aria-labelledby="pills-fullHistory-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Full History</label>
                            <div class="row">
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3 mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="CRMFullHistoryData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>By</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Note(s)</th>
                                                    <th>Status</th>
                                                    <th>Customer Visible</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td> </td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-Calls" role="tabpanel" aria-labelledby="pills-Calls-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Calls History</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection  mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn" id="openCallsModel"> New</a>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="crmCallData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>By</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Note(s)</th>
                                                    <th>Customer Visible</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-emails" role="tabpanel" aria-labelledby="pills-emails-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Emails History</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection  mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn" id="openNewEmail"> New</a>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="crmEmailData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>By</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Note(s)</th>
                                                    <th>Status</th>
                                                    <th>Customer Visible</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-tasks" role="tabpanel" aria-labelledby="pills-tasks-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Tasks</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection  mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn open-modal" data-target="bd-example-modal-lg" id="openSecondModal"> New</a>
                                    </div>
                                </div>
                                <!-- Second Modal -->
                                <div class="modal fade bd-example-modal-lg" id="secondModal" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg mediamSizePopup">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="secondModalLabel">New Task</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- tab -->
                                                <nav>
                                                    <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                                                        <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Task</button>
                                                        <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Timer</button>
                                                    </div>
                                                </nav>
                                                <form id="crm_lead_task_form">
                                                    <div class="tab-content p-3 border bg-light" id="nav-tabContent">
                                                        <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task User <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-8">
                                                                            <input type="hidden" name="form_type" id="form_type" value="">
                                                                            <input type="hidden" name="lead_id" id="crm_lead_id_task">
                                                                            <input type="hidden" id="crm_lead_task_id" name="crm_lead_task_id">
                                                                            <select class="form-control editInput" name="user_id" id="getUserList">
                                                                                <option value="">Select</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Title <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control editInput" id="taskTitle" name="title" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task Type <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-6">
                                                                            <select class="form-control editInput" name="task_type_id" id="lead_task_types">
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <a href="#!" class="formicon" id="openThirdModal"><i class="fa-solid fa-square-plus"></i></a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Start Date <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-4">
                                                                            <input type="date" class="form-control editInput" name="start_date" id="TaskStartDate">
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <input type="time" class="form-control editInput" name="start_time" id="TaskStartTime">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">End Date <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-4">
                                                                            <input type="date" class="form-control editInput" name="end_date" id="TaskEndDate" value="">
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <input type="time" class="form-control editInput" name="end_time" id="TaskEndTime" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-3 col-form-label">Notify ? </label>
                                                                        <div class="col-sm-9">
                                                                            <div class="row">
                                                                                <div class="col-sm-3 pe-0">
                                                                                    <div class="form-check form-check-inline me-0">
                                                                                        <input class="form-check-input" type="checkbox" name="notify" id="yeson" value="1" required>
                                                                                        <label class="col-form-label" for="yeson">Yes, On</label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-sm-9">
                                                                                    <div class="row">
                                                                                        <div class="col-sm-6">
                                                                                            <input type="date" class="form-control editInput" id="notify_date" name="task_date">
                                                                                        </div>
                                                                                        <div class="col-sm-6">
                                                                                            <input type="time" class="form-control editInput" id="notify_time" name="task_time">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-3"></div>
                                                                        <div class="col-sm-9 mt-3">
                                                                            <div id="optionsDiv">
                                                                                <label class="editInput"><input type="checkbox" value="1" id="" name="notification"> Notification</label>
                                                                                <label class="editInput"><input type="checkbox" value="1" id="" name="email"> Email</label>
                                                                                <label class="editInput"><input type="checkbox" value="1" id="" name="sms"> SMS</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3 row">
                                                                        <label for="related_to" class="col-sm-3 col-form-label">Related To</label>
                                                                        <div class="col-sm-9">
                                                                            <span class="editInput" id="related_To"></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-3 col-form-label">Notes</label>
                                                                        <div class="col-sm-9">
                                                                            <textarea name="notes" class="form-control textareaInput" rows="5" id="TaskNotesText"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3 row">
                                                                    <label class="col-sm-2 col-form-label pe-0">Is Reccurring Task ?</label>
                                                                    <div class="col-sm-10">
                                                                        <input type="checkbox" value="1" name="is_recurring" class="form-check-input" id="isRecurring">
                                                                    </div>
                                                                    <div class="row" id="recurrence_div" style="display:none;">
                                                                        <div class="col-sm-6">
                                                                            <div class="newJobForm mt-4">
                                                                                <label class="upperlineTitle">Recurrence Pattern</label>
                                                                                <div class="Priority row">
                                                                                    <label class="col-sm-4 col-form-label">Create Task</label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="row">
                                                                                            <div class="col-sm-4">
                                                                                                <select class="form-control editInput selectOptions" name="create_task" id="">
                                                                                                    <option>0</option>
                                                                                                </select>
                                                                                            </div>
                                                                                            <label class="col-sm-8 col-form-label">Days before</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="col-sm-12">
                                                                                        <div class="form-check form-check-inline">
                                                                                            <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio1" value="1" checked>
                                                                                            <label class="col-form-label" for="inlineRadio1">End after</label>
                                                                                        </div>
                                                                                        <div class="form-check form-check-inline">
                                                                                            <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio2" value="2">
                                                                                            <label class="col-form-label" for="inlineRadio2">End By</label>
                                                                                        </div>
                                                                                        <div class="form-check form-check-inline">
                                                                                            <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio3" value="3">
                                                                                            <label class="col-form-label" for="inlineRadio3">No End Date</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="Priority row">
                                                                                    <div class="row" id="repetitation">
                                                                                        <label class="col-sm-4 col-form-label">No. of Repetitaion</label>
                                                                                        <div class="col-sm-8">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-8">
                                                                                                    <input type="text" class="form-control editInput" name="repetitation">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row" id="task_end_date">
                                                                                        <label class="col-sm-4 col-form-label">Task End Date</label>
                                                                                        <div class="col-sm-8">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-8">
                                                                                                    <input type="date" class="form-control editInput" name="task_end_date">
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <div class="newJobForm mt-4">
                                                                                <label class="upperlineTitle">Range of Recurrence</label>
                                                                                <div class="Priority row">
                                                                                    <label class="col-sm-4 col-form-label">Task Frequency</label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="row">
                                                                                            <div class="col-sm-6">
                                                                                                <select class="form-control editInput selectOptions" name="task_frequency" id="task_frequency">
                                                                                                    <option value="1">Daily</option>
                                                                                                    <option value="2">Weekly</option>
                                                                                                    <option value="3">Monthly</option>
                                                                                                    <!-- <option value="4">Yearly</option> -->
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div id="daily">
                                                                                    <div class="row py-1">
                                                                                        <div class="col-sm-3">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="daily" id="" value="" checked>
                                                                                                <label class="col-form-label" for="">Every</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-2 px-0 text-center">
                                                                                                    <input class="form-control editInput" type="text" name="daily_days" id="" value="">
                                                                                                </div>
                                                                                                <label class="col-sm-8 col-form-label">Days</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-sm-6">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="daily" id="" value="">
                                                                                                <label class="col-form-label" for="inlineRadioEvery">Every Weekday </label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div id="weekly">
                                                                                    <div class="row py-1">
                                                                                        <div class="col-sm-3">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="weekly" id="" value="" checked>
                                                                                                <label class="col-form-label" for="">Every</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-2 px-0">
                                                                                                    <input class="form-control editInput" type="text" name="weekly_days" id="" value="">
                                                                                                </div>
                                                                                                <label class="col-sm-10 col-form-label">Weeks</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-sm-3">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="weekly" id="" value="">
                                                                                                <label class="col-form-label" for="">Every</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-2 px-0">
                                                                                                    <input class="form-control editInput" type="text" name="weekly_weekday" id="" value="">
                                                                                                </div>
                                                                                                <label class="col-sm-10 col-form-label" for="inlineRadioEvery">Weeks on</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-11 px-0 days_check">
                                                                                                    @foreach($weeks as $weekDays)
                                                                                                    <div>
                                                                                                        <label class="col-form-label" for="{{ $weekDays->name }}">{{ $weekDays->name }}</label>
                                                                                                        <input class="form-check-input" type="checkbox" name="weekly_weeks_{{ $weekDays->iteration }}" id="{{ $weekDays->name }}" value="{{ $weekDays->id }}">
                                                                                                    </div>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div id="monthly">
                                                                                    <div class="row py-1">
                                                                                        <div class="col-sm-2">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="monthly" id="" value="day" checked>
                                                                                                <label class="col-form-label" for="">Day</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-4 px-0">
                                                                                                    <select class="form-control editInput selectOptions" name="monthly_days" id="">
                                                                                                        @for($i=1; $i<=30; $i++)
                                                                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                                                                            @endfor
                                                                                                    </select>
                                                                                                </div>
                                                                                                <label class="col-sm-8 col-form-label">off every</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-6">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-2 px-0">
                                                                                                    <input class="form-control editInput" type="text" name="monthly_month" id="" value="">
                                                                                                </div>
                                                                                                <label class="col-sm-10 col-form-label">Months</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col-sm-2">
                                                                                            <div class="form-check form-check-inline">
                                                                                                <input class="form-check-input" type="radio" name="monthly" id="" value="">
                                                                                                <label class="col-form-label" for="">Every</label>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-3">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-11 px-0">
                                                                                                    <select class="form-control editInput selectOptions" name="every_month_day" id="">
                                                                                                        <option value="1">First</option>
                                                                                                        <option value="2">Second</option>
                                                                                                        <option value="3">Third</option>
                                                                                                        <option value="4">Fourth</option>
                                                                                                        <option value="5">Last</option>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-3">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-11 px-0">
                                                                                                    <select class="form-control editInput selectOptions" id="" name="every_monthly_month">
                                                                                                        <option value="">Day</option>
                                                                                                        <option value="">WeekDay</option>
                                                                                                        <option value="">Weekend Day</option>
                                                                                                        @foreach($weeks as $value)
                                                                                                        <option value="{{ $value->id }}">{{ $value->name}}</option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <div class="row">
                                                                                                <div class="col-sm-4 px-0">
                                                                                                    <label class="col-form-label" for="">of Every</label>
                                                                                                </div>
                                                                                                <div class="col-sm-2 px-0">
                                                                                                    <input class="form-control editInput" type="text" name="every_month_of_month" id="" value="">
                                                                                                </div>
                                                                                                <div class="col-sm-4">
                                                                                                    <label class="col-form-label" for="">Months</label>
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
                                                        </div>
                                                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task User <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-8">
                                                                            <select class="form-control editInput" name="user_id_timer" id="">
                                                                                @foreach($users as $value)
                                                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Title <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-8">
                                                                            <input type="text" class="form-control editInput" name="title_timer" id="staticEmail">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Timer</label>
                                                                        <div class="col-sm-8">
                                                                            <button class="profileDrop" id="toggleTimerBtn"><i class="fa fa-play"></i> Start</button>
                                                                            <span id="timerDisplay">00:00:00</span>
                                                                            <input type="hidden" name="start_time_timer" id="start_time">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="related_to" class="col-sm-4 col-form-label">Related To</label>
                                                                        <div class="col-sm-8">
                                                                            <span class="editInput" id="relatedTo"></span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task Type <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-6">
                                                                            <select class="form-control editInput" name="task_type_id" id="lead_task_types_timer">
                                                                                <option value="">Select</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <a href="#!" class="formicon" id="openThirdModal2"><i class="fa-solid fa-square-plus"></i></a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Notes</label>
                                                                        <div class="col-sm-8">
                                                                            <textarea rows="5" name="notes_timer" class="form-control textareaInput" id=""></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                                <!-- tab -->
                                                <div class="pageTitleBtn">
                                                    <a href="#" class="profileDrop p-2 crmNewBtn" id="saveCRMLeadTaskWithTimer"> Save</a>
                                                    <!-- <a href="#" class="profileDrop p-2 crmNewBtn" > Close</a> -->
                                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Third Modal -->
                                <div class="modal fade" id="thirdModal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content ">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="thirdModalLabel">Add Task Type</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" id="lead_task_type_form">
                                                    <div class="mb-3 row">
                                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Task Type <span class="radStar ">*</span></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" name="title" class="form-control editInput" id="inputJobRef" value="" placeholder="Task Type">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 row">
                                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                                                        <div class="col-sm-9">
                                                            <select id="status" name="status" class="form-control editInput">
                                                                <option value="1">Active</option>
                                                                <option value="0">Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="pageTitleBtn">
                                                        <a href="#" class="profileDrop p-2 crmNewBtn" id="saveTaskType"> Save</a>
                                                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Keywords to search" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-2">
                                    <div class="overdue mt-3 ms-3" id="notShowOnComplete">
                                        <span class="yeloColrbox"></span><label>Overdue</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pageTitleBtn">
                                        <a href="#" onclick="ShowTaskOnDate(this, 'all');" class="profileDrop p-2 crmNewBtn btnActive">All</a>
                                        <a href="#" onclick="ShowTaskOnDate(this, 'today');" id="today" class="profileDrop p-2 crmNewBtn">Today</a>
                                        <a href="#" onclick="ShowTaskOnDate(this, 'week');" class="profileDrop p-2 crmNewBtn">This Week</a>
                                        <a href="#" onclick="ShowTaskOnDate(this, 'overdue');" class="profileDrop p-2 crmNewBtn">Overdue</a>
                                        <a href="#" onclick="ShowTaskOnDate(this, 'complete');" class="profileDrop p-2 crmNewBtn">Completed</a>
                                        <a href="#" onclick="ShowTaskOnDate(this, 'recurring');" class="profileDrop p-2 crmNewBtn">Recurring</a>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="CRMLeadTaskTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th></th>
                                                    <th>Date</th>
                                                    <th>User</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Title</th>
                                                    <th>Note(s)</th>
                                                    <th>Related To</th>
                                                    <th>Created On </th>
                                                    <th>Created By </th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-notes" role="tabpanel" aria-labelledby="pills-notes-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Notes</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection  mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn" id="openNotesModel"> New</a>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="crmLeadNotesTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>By</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Note(s)</th>
                                                    <th>Customer Visible</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pills-complaints" role="tabpanel" aria-labelledby="pills-complaints-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Complaints History</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn" id="openComplaintsModel"> New</a>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Keyword to search" name="email">
                                            <button type="button" class="input-group-text sarchBtn">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="crmLeadComplaintsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>By</th>
                                                    <th>Contact</th>
                                                    <th>Type</th>
                                                    <th>Source Ref</th>
                                                    <th>Note(s)</th>
                                                    <th>Customer Visible</th>
                                                    <th>Actioned</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                    <td>.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End off model body  -->
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- ****************End CRM History Modal ****************-->


<!-- Calls Modal -->
<div class="modal fade" id="callsModal" tabindex="-1" aria-labelledby="callsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Calls</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" id="closeCallsModels1"></button>
            </div>
            <div class="modal-body">
                <form action="" class="customerForm" id="CRM_calls_form">
                    <div class="mb-2 row">
                        <input type="hidden" name="crm_lead_calls_id" id="crm_lead_calls_id">
                        <label for="" class="col-sm-3 col-form-label">Direction </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="direction" id="direction_radio1" value="0" checked>
                            <label class="form-check-label editInput" for="direction_radio1"> Call Out </label>
                            <input class="form-check-input" type="radio" name="direction" id="direction_radio2" value="1">
                            <label class="form-check-label editInput" for="direction_radio2"> Call In </label>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="" class="col-sm-3 col-form-label">Telephone </label>
                        <div class="col-sm-2">
                            <select class="form-control editInput selectOptions" required="" name="country_code" id="countries">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control editInput" id="" name="telephone" placeholder="Telephone">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="calls_type" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-7">
                            <select name="crm_type_id" class="form-control editInput" id="calls_type">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <a href="#!" class="formicon" id="openCrmTypeModel"><i class="fa-solid fa-square-plus"></i></a>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="calls_notes" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <div id="editor">
                                </div>
                                <textarea name="content" id="calls_notes" style="display: none;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="calls_lead_ref" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="call_lead_ref"></span>
                            <input type="hidden" name="lead_ref" id="call_lead_ref_data">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="" class="col-sm-3 col-form-label">Notify? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="notify_radio" id="notify_radio1" value="0" checked>
                            <label class="form-check-label editInput" for=""> No </label>
                            <input class="form-check-input" type="radio" name="notify_radio" id="notify_radio2" value="1">
                            <label class="form-check-label editInput" for=""> Yes </label>
                        </div>
                    </div>
                    <div class="notification_div">
                        <div class="mb-2 row">
                            <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                    <option value=""></option>
                                    @foreach($users as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <label for="calls_notify_who1" class="editInput"><input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only) </label>
                                <label for="calls_notify_who2" class="editInput"><input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS </label>
                                <label for="calls_notify_who3" class="editInput"><input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault1" value="0" checked>
                            <label class="form-check-label editInput" for="flexRadioDefault1">No</label>
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault2" value="1">
                            <label class="form-check-label editInput" for="flexRadioDefault2">Yes</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" id="saveCRMCallsModelData">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closeCallsModels2">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- end Calls Modal -->

<!-- CRM Types Modal Start -->
<div class="modal fade" id="crmTypeModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add - CRM Section Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCrmModalBtn" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_section_type_form">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control editInput" name="title" id="type_title" value="">
                            <input type="hidden" class="form-control editInput" name="crm_section" id="" value="1">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="colour_code" class="col-sm-3 col-form-label">Colour Code </label>
                        <div class="col-sm-9">
                            <input type="color" class="form-control editInput" name="colour_code" id="colour_code" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" id="closeCrmModalBtn">Close</button>
                <button type="button" class="profileDrop" id="saveCRMTypes">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- CRM Types Modal End -->

<!-- CRM Add Email Modal Start -->
<div class="modal fade" id="NewEmailModel" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="emailModalLabel">Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCrmModalBtn" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_lead_email_form" enctype='multipart/form-data'>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">To <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <input type="hidden" class="form-control editInput" name="crm_lead_email_id" id="">
                            <input type="text" class="form-control editInput" name="to" id="" value="">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Cc </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control editInput" name="cc" id="">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Subject <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control editInput" name="subject" id="">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Message <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <div id="emailEditor">
                                </div>
                                <textarea name="message" id="emailMessage" style="display: none;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Attachment </label>
                        <div class="col-sm-9">
                            <input type="file" class="editInput" name="attachment" id="image">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="lead_ref_email"></span>
                            <input type="hidden" id="lead_id_email" name="lead_id">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="" class="col-sm-3 col-form-label">Notify? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="notify" id="notify_email1" value="0" checked>
                            <label class="form-check-label editInput" for="notify_email1"> No </label>
                            <input class="form-check-input" type="radio" name="notify" id="notify_email2" value="1">
                            <label class="form-check-label editInput" for="notify_email2"> Yes </label>
                        </div>
                    </div>
                    <div id="notification_email_div">
                        <div class="mb-2 row">
                            <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <select name="notify_user" class="form-control editInput" id="user_notifiy">
                                    <option value=""></option>
                                    @foreach($users as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <label for="calls_notify_who1" class="editInput">
                                    <input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only)
                                </label>
                                <label for="calls_notify_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                </label>
                                <label for="calls_notify_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault1" value="0" checked>
                            <label class="form-check-label editInput" for="flexRadioDefault1">No</label>
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault2" value="1">
                            <label class="form-check-label editInput" for="flexRadioDefault2">Yes</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="">Close</button>
                <button type="button" class="profileDrop" id="saveCRMLeadEmails">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- CRM Add Email Modal End -->

<!-- CRM Add Notes Modal Start -->
<div class="modal fade" id="NewNotesModel" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="notesModalLabel">Notes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCrmNotesBtn" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_lead_notes_form">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control editInput" name="crm_lead_notes_id" id="">
                            <select class="form-control editInput" name="crm_section_type_id" id="lead_notes_crm"></select>
                        </div>
                        <div class="col-sm-1">
                            <a href="#!" class="formicon" id="openCrmTypeModelNotes"><i class="fa-solid fa-square-plus"></i></a>
                        </div>

                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <div id="NotesEditor">
                                </div>
                                <textarea name="notes" id="CRMNotes" style="display: none;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="lead_ref_notes"></span>
                            <input type="hidden" id="lead_id_notes" name="lead_id">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="" class="col-sm-3 col-form-label">Notify? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="notify" id="notify_notes1" value="0" checked>
                            <label class="form-check-label editInput" for="notify_notes1"> No </label>
                            <input class="form-check-input" type="radio" name="notify" id="notify_notes2" value="1">
                            <label class="form-check-label editInput" for="notify_notes2"> Yes </label>
                        </div>
                    </div>
                    <div id="notification_notes_div">
                        <div class="mb-2 row">
                            <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <select name="user_id" class="form-control editInput" id="user_notifiy">
                                    <option value=""></option>
                                    @foreach($users as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <label for="calls_notify_who1" class="editInput">
                                    <input type="checkbox" name="notification" id="calls_notify_who1" value="1"> Notification (User Only)
                                </label>
                                <label for="calls_notify_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                </label>
                                <label for="calls_notify_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_notify_who3" value="1"> Email
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="customer_visibility" id="flexRadioDefault1" value="0" checked>
                            <label class="form-check-label editInput" for="flexRadioDefault1">No</label>
                            <input class="form-check-input" type="radio" name="customer_visibility" id="flexRadioDefault2" value="1">
                            <label class="form-check-label editInput" for="flexRadioDefault2">Yes</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="">Close</button>
                <button type="button" class="profileDrop" id="saveCRMLeadNotes">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- CRM Types Modal Notes Start -->
<div class="modal fade" id="crmTypeModelNotes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add - CRM Section Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_section_type_notes_form">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control editInput" name="title" id="type_title_notes">
                            <input type="hidden" class="form-control editInput" name="crm_section" id="crm_section_notes" value="3">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="colour_code" class="col-sm-3 col-form-label">Colour Code </label>
                        <div class="col-sm-9">
                            <input type="color" class="form-control editInput" name="colour_code" id="colour_code_notes" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closeCrmModalBtn">Close</button>
                <button type="button" class="profileDrop" id="saveCRMTypesNotes">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- CRM Types Modal Notes End -->

<!-- CRM Add Notes Modal End -->

<!-- CRM Add compliants Modal Start -->
<div class="modal fade" id="compliantsModal" tabindex="-1" role="dialog" aria-labelledby="compliantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="compliantsModalLabel">Complaint</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeCrmComplaintBtn" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_lead_complaint_form">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-8">
                            <input type="hidden" class="form-control editInput" name="crm_lead_complaint_id" id="">
                            <select class="form-control editInput" name="crm_section_type_id" id="lead_complaint_crm">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <a href="#!" class="formicon" id="openCrmTypeModelComplaints"><i class="fa-solid fa-square-plus"></i></a>
                        </div>

                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Notes <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <div id="complaintEditor">
                                </div>
                                <textarea name="compliant" id="CRMComplaint" style="display: none;"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="lead_ref_complaint"></span>
                            <input type="hidden" id="lead_id_complaint" name="lead_id">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="" class="col-sm-3 col-form-label">Notify? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="notify" id="notify_complaint1" value="0" checked>
                            <label class="form-check-label editInput" for="notify_complaint1"> No </label>
                            <input class="form-check-input" type="radio" name="notify" id="notify_complaint2" value="1">
                            <label class="form-check-label editInput" for="notify_complaint2"> Yes </label>
                        </div>
                    </div>
                    <div id="notification_complaint_div">
                        <div class="mb-2 row">
                            <label for="user_notifiy" class="col-sm-3 col-form-label">Notify Who? <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <select name="user_id" class="form-control editInput" id="user_notifiy">
                                    <option value=""></option>
                                    @foreach($users as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <label for="calls_complaint_who1" class="editInput">
                                    <input type="checkbox" name="notification" id="calls_complaint_who1" value="1"> Notification (User Only)
                                </label>
                                <label for="calls_complaint_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_complaint_who2" value="1"> SMS
                                </label>
                                <label for="calls_complaint_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_complaint_who3" value="1"> Email
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closeCrmComplaintBtn">Close</button>
                <button type="button" class="profileDrop" id="saveCRMLeadComplaint">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- CRM Types Modal Complaint Start -->
<div class="modal fade" id="crmTypeModelComplaint" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add - CRM Section Types</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="crm_section_type_form">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control editInput" name="title" id="type_title_complaint" value="">
                            <input type="hidden" class="form-control editInput" name="crm_section_complaint" id="crm_section_complaint" value="4">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="colour_code" class="col-sm-3 col-form-label">Colour Code </label>
                        <div class="col-sm-9">
                            <input type="color" class="form-control editInput" name="colour_code" id="colour_code_complaint" value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" id="closeCrmModalBtn">Close</button>
                <button type="button" class="profileDrop" id="saveCRMTypesComplaint">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- CRM Types Modal Complaint End -->
<!-- CRM Add compliants Modal End -->



<div class="modal fade" id="sentToQuoteModal" tabindex="1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title fs-5" id="exampleModalLabel2"> Notify Leads </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="notify_lead_quote_form">
                    @csrf
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label">
                            <h6><strong>Would you like to notify anyone that this lead <span id="sentQuote"></span> has been converted?</strong></h6>
                        </label>
                    </div>
                    <div class="mb-3 row">
                        <input type="hidden" name="lead_id" id="notify_lead_id">
                        <input type="hidden" name="type" id="notification_type">
                        <input type="hidden" name="customer_id" id="converted_customer">
                        <label class="col-sm-2 col-form-label"> <strong>Notify</strong></label>
                        <div class="col-sm-10">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="notify" id="inlineRadio1" onclick="toggleDiv(true)" value="1">
                                <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="notify" id="inlineRadio2" onclick="toggleDiv(false)" value="0" checked="">
                                <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                            </div>
                        </div>
                    </div>
                    <div id="toggleDiv">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label"> <strong>Notify Who ?</strong></label>
                            <div class="col-sm-10">
                                <select class="form-control editInput" name="notifiy_user_id" id="notifiy_user">

                                </select>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label"> <strong>Send As <span class="radStar">*</span></strong></label>
                            <div class="col-sm-10">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="notifocation" id="checkalrt1" value="1">
                                    <label class="form-check-label checkboxtext" for="checkalrt1">Notification</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="sms" id="checkalrt2" value="1">
                                    <label class="form-check-label checkboxtext" for="checkalrt2">SMS</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="email" id="checkalrt3" value="1">
                                    <label class="form-check-label checkboxtext" for="checkalrt3">Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                <button type="button" id="save_lead_convert_quote" class="profileDrop">Save</button>
            </div>
        </div>
    </div>
</div>



<!-- Moment js CDN -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script>
    function SetCSSForButtonInTaskModel(element) {
        var buttons = document.querySelectorAll('.crmNewBtn');
        buttons.forEach(function(btn) {
            btn.classList.remove('btnActive');
        });

        // Add 'btnActive' to the clicked button
        element.classList.add('btnActive');
    }

    // Function to populate the table
    function populateTable(data, lead_ref) {

        // Get the table body element
        const tableBody = document.querySelector('#CRMLeadTaskTable tbody');
        tableBody.innerHTML = '';

        data.forEach(item => {
            // Create a new row
            const row = document.createElement('tr');

            const listCell = document.createElement('td');
            listCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="summary"><i class="fa fa-list-ul"></i></a>`;
            row.appendChild(listCell);

            const created_at = moment(item.created_at).format('DD/MM/YYYY HH:mm');
            const date = moment(item.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
            const time = moment(item.start_time, 'HH:mm:ss').format('HH:mm');

            // Create cells and append them to the row
            const dateCell = document.createElement('td');
            dateCell.textContent = date + " " + time;
            row.appendChild(dateCell);

            const nameCell = document.createElement('td');
            nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
            row.appendChild(nameCell);

            const phoneCell = document.createElement('td');
            phoneCell.textContent = item.telephone;
            row.appendChild(phoneCell);

            const lead_task_title = document.createElement('td');
            lead_task_title.textContent = item.lead_task_title;
            row.appendChild(lead_task_title);

            const typeCell = document.createElement('td');
            typeCell.textContent = item.title;
            row.appendChild(typeCell);

            const notesCell = document.createElement('td');
            notesCell.innerHTML = item.notes;
            row.appendChild(notesCell);

            const related = document.createElement('td');
            related.innerHTML = lead_ref;
            row.appendChild(related);

            const create_time = document.createElement('td');
            create_time.innerHTML = created_at;
            row.appendChild(create_time);

            const created_by = document.createElement('td');
            created_by.innerHTML = '<?php echo Auth::user()->name; ?>';
            row.appendChild(created_by);

            const visibilityCell = document.createElement('td');
            if (item.customer_visibility == 0) {
                visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
            } else if (item.customer_visibility == 1) {
                visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
            }
            row.appendChild(visibilityCell);

            const idCell = document.createElement('td');
            idCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="edit"><i class="fa fa-edit"></i></a>  <i class="fa fa-times"></i>`;
            row.appendChild(idCell);

            // Append the row to the table body
            tableBody.appendChild(row);
        });
    }

    function ShowTaskOnDate(element, value) {

        var notShowOnComplete = document.getElementById('notShowOnComplete');
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;

        if (value == "all") {
            notShowOnComplete.style.display = "flex";

            SetCSSForButtonInTaskModel(element);
            getTasksDataAjaxCall();
        } else if (value == "today") {
            notShowOnComplete.style.display = "flex";
            SetCSSForButtonInTaskModel(element);
            $.ajax({
                url: '{{ route("lead.ajax.getCRMTaskDataToday") }}',
                method: 'POST',
                data: {
                    lead_id: lead_id
                },
                success: function(response) {
                    console.log(response.data);

                    // Call the function to populate the table with the data array
                    populateTable(response.data, lead_ref);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });

        } else if (value == "week") {
            notShowOnComplete.style.display = "flex";

            SetCSSForButtonInTaskModel(element);
            $.ajax({
                url: '{{ route("lead.ajax.getCRMTaskDataWeek") }}',
                method: 'POST',
                data: {
                    lead_id: lead_id
                },
                success: function(response) {
                    console.log(response.data);

                    // Call the function to populate the table with the data array
                    populateTable(response.data, lead_ref);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else if (value == "overdue") {
            notShowOnComplete.style.display = "flex";

            SetCSSForButtonInTaskModel(element);
            $.ajax({
                url: '{{ route("lead.ajax.getCRMTaskDataOverdue") }}',
                method: 'POST',
                data: {
                    lead_id: lead_id
                },
                success: function(response) {
                    console.log(response.data);

                    // Call the function to populate the table with the data array
                    populateTable(response.data, lead_ref);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else if (value == "complete") {
            notShowOnComplete.style.display = "none";

            SetCSSForButtonInTaskModel(element);
            $.ajax({
                url: '{{ route("lead.ajax.getCRMTaskDataComplete") }}',
                method: 'POST',
                data: {
                    lead_id: lead_id
                },
                success: function(response) {
                    console.log(response.data);

                    // Call the function to populate the table with the data array
                    populateTable(response.data, lead_ref);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else if (value == "recurring") {
            notShowOnComplete.style.display = "flex";

            SetCSSForButtonInTaskModel(element);
            $.ajax({
                url: '{{ route("lead.ajax.getCRMTaskDataRecurring") }}',
                method: 'POST',
                data: {
                    lead_id: lead_id
                },
                success: function(response) {
                    console.log(response.data);

                    // Call the function to populate the table with the data array
                    populateTable(response.data, lead_ref);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    }

    function getComplaintDataAjax() {
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        $.ajax({
            url: '{{ route("lead.ajax.getCRMComplaintData") }}',
            method: 'POST',
            data: {
                lead_id: lead_id
            },
            success: function(response) {
                console.log(response.data);

                // Get the table body element
                const tableBody = document.querySelector('#crmLeadComplaintsTable tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
                        row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const leadRef = document.createElement('td');
                        leadRef.textContent = lead_ref;
                        row.appendChild(leadRef);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = 'Call Logged from ' + lead_ref + '<br> <strong>Notes: </strong> ' + item.notes;
                        row.appendChild(notesCell);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visibility == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visibility == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        const idCell = document.createElement('td');
                        idCell.innerHTML = '<i class="fa fa-phone"></i>' + " " + '<i class="fa fa-envelope"></i>' + " " + '<i class="fa fa-list-ul"></i>' + " " + '<i class="fa fa-file"></i>' + " " + '<i class="fa fa-exclamation-triangle"></i>';
                        row.appendChild(idCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);

                    });
                }

                // Call the function to populate the table with the data array
                populateTable(response.data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getCallDataAjax() {
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        var lead_id = document.getElementById('lead_id_CRM').value;
        console.log("lead_ref ", lead_id);
        $.ajax({
            url: '{{ route("lead.ajax.getCRMCallsData") }}',
            method: 'POST',
            data: {
                lead_ref: lead_id
            },
            success: function(response) {
                console.log(response.data);

                // Get the table body element
                const tableBody = document.querySelector('#crmCallData tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
                        row.appendChild(nameCell);

                        // const nameCell = document.createElement('td');
                        // nameCell.textContent = item.user_id;
                        // row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = 'Call Logged from ' + lead_ref + '<br> <strong>Notes: </strong> ' + item.notes;

                        row.appendChild(notesCell);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visibility == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visibility == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        const idCell = document.createElement('td');
                        idCell.innerHTML = '<i class="fa fa-phone"></i>' + " " + '<i class="fa fa-envelope"></i>' + " " + '<i class="fa fa-list-ul"></i>' + " " + '<i class="fa fa-file"></i>' + " " + '<i class="fa fa-exclamation-triangle"></i>';
                        row.appendChild(idCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    });
                }

                // Call the function to populate the table with the data array
                populateTable(response.data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getEmailDataAjaxCall() {
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        $.ajax({
            url: '{{ route("lead.ajax.getCRMEmailsData") }}',
            method: 'POST',
            data: {
                lead_id: lead_id
            },
            success: function(response) {
                console.log(response.data);

                // Get the table body element
                const tableBody = document.querySelector('#crmEmailData tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
                        row.appendChild(nameCell);

                        // const nameCell = document.createElement('td');
                        // nameCell.textContent = item.user_id;
                        // row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = 'Call Logged from ' + lead_ref + '<br> <strong>Notes: </strong> ' + item.message;

                        row.appendChild(notesCell);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visible == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visible == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        const idCell = document.createElement('td');
                        idCell.innerHTML = '<i class="fa fa-phone"></i>' + " " + '<i class="fa fa-envelope"></i>' + " " + '<i class="fa fa-list-ul"></i>' + " " + '<i class="fa fa-file"></i>' + " " + '<i class="fa fa-exclamation-triangle"></i>';
                        row.appendChild(idCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    });
                }

                // Call the function to populate the table with the data array
                populateTable(response.data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getNotesDataAjax() {
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        $.ajax({
            url: '{{ route("lead.ajax.getCRMNotesData") }}',
            method: 'POST',
            data: {
                lead_id: lead_id
            },
            success: function(response) {
                console.log(response.data);

                // Get the table body element
                const tableBody = document.querySelector('#crmLeadNotesTable tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
                        row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = 'Call Logged from ' + lead_ref + '<br> <strong>Notes: </strong> ' + item.notes;

                        row.appendChild(notesCell);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visibility == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visibility == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        const idCell = document.createElement('td');
                        idCell.innerHTML = '<i class="fa fa-phone"></i>' + " " + '<i class="fa fa-envelope"></i>' + " " + '<i class="fa fa-list-ul"></i>' + " " + '<i class="fa fa-file"></i>' + " " + '<i class="fa fa-exclamation-triangle"></i>';
                        row.appendChild(idCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    });
                }

                // Call the function to populate the table with the data array
                populateTable(response.data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getTasksDataAjaxCall() {
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        $.ajax({
            url: '{{ route("lead.ajax.getCRMTasksData") }}',
            method: 'POST',
            data: {
                lead_id: lead_id
            },
            success: function(response) {
                console.log(response.data);

                // Get the table body element
                const tableBody = document.querySelector('#CRMLeadTaskTable tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const listCell = document.createElement('td');
                        listCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="summary"><i class="fa fa-list-ul" title="Call"></i></a>`;
                        row.appendChild(listCell);

                        const created_at = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                        const date = moment(item.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
                        const time = moment(item.start_time, 'HH:mm:ss').format('HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date + " " + time;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = item.userName;
                        row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const lead_task_title = document.createElement('td');
                        lead_task_title.textContent = item.lead_task_title;
                        row.appendChild(lead_task_title);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = item.notes;
                        row.appendChild(notesCell);

                        const related = document.createElement('td');
                        related.innerHTML = lead_ref;
                        row.appendChild(related);

                        const create_time = document.createElement('td');
                        create_time.innerHTML = created_at;
                        row.appendChild(create_time);

                        const created_by = document.createElement('td');
                        created_by.innerHTML = '<?php echo Auth::user()->name; ?>';
                        row.appendChild(created_by);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visibility == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visibility == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        const idCell = document.createElement('td');
                        idCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="edit"><i class="fa fa-edit"></i></a> <i class="fa fa-times"></i>`;
                        row.appendChild(idCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    });
                }

                // Call the function to populate the table with the data array
                populateTable(response.data);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getFullHistoryDataAjax() {
        var lead_id = document.getElementById('lead_id_CRM').value;
        var lead_ref = document.getElementById('calls_lead_refs').textContent;
        $.ajax({
            url: '{{ route("lead.ajax.getCRMAllData") }}',
            method: 'POST',
            data: {
                lead_id: lead_id
            },
            success: function(response) {
                console.log("History response", response.data);
                console.log("length", (response.data).length);

                // // Get the table body element
                const tableBody = document.querySelector('#CRMFullHistoryData tbody');
                tableBody.innerHTML = '';

                // Function to populate the table
                function populateTable(data) {
                    console.log('Inside table function ', data);
                    data.forEach(item => {
                        // Create a new row
                        const row = document.createElement('tr');

                        const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');

                        // Create cells and append them to the row
                        const dateCell = document.createElement('td');
                        dateCell.textContent = date;
                        row.appendChild(dateCell);

                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = "<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>";
                        row.appendChild(nameCell);

                        const phoneCell = document.createElement('td');
                        phoneCell.textContent = item.telephone;
                        row.appendChild(phoneCell);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = item.title;
                        row.appendChild(typeCell);

                        const notesCell = document.createElement('td');
                        notesCell.innerHTML = 'Call Logged from ' + lead_ref + '<br> <strong>Notes: </strong> ' + item.notes;
                        row.appendChild(notesCell);

                        const status = document.createElement('td');
                        status.innerHTML = 'status';
                        row.appendChild(status);

                        const visibilityCell = document.createElement('td');
                        if (item.customer_visibility == 0) {
                            visibilityCell.innerHTML = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                        } else if (item.customer_visibility == 1) {
                            visibilityCell.innerHTML = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                        }
                        row.appendChild(visibilityCell);

                        // Append the row to the table body
                        tableBody.appendChild(row);
                    });
                }

                // Call the function to populate the table with the data array
                for (i = 0; i <= (response.data).length; i++) {
                    console.log(response.data[i]);
                    populateTable(response.data[i]);
                }

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getLeadTaskType() {
        $.ajax({
            url: '{{ route("lead.ajax.getLeadTaskType") }}',
            method: 'GET',
            success: function(response) {
                console.log(response.data);
                const selectElement = document.getElementById('lead_task_types');
                const selectElementTimer = document.getElementById('lead_task_types_timer');

                selectElement.innerHTML = '';
                selectElementTimer.innerHTML = '';

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    selectElement.appendChild(option);
                });

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    selectElementTimer.appendChild(option);
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getUserList() {

        $.ajax({
            url: '{{ route("lead.ajax.getUserList") }}',
            method: 'GET',
            success: function(response) {
                console.log(response.data);
                const selectElement = document.getElementById('getUserList');

                selectElement.innerHTML = '';

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    selectElement.appendChild(option);
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getCRMTypeData() {
        $.ajax({
            url: '{{ route("lead.ajax.getCRMTypeData") }}',
            method: 'GET',
            success: function(response) {
                console.log(response.Data);
                const selectElement = document.getElementById('calls_type');
                const lead_notes_crm = document.getElementById('lead_notes_crm');
                const lead_complaint_crm = document.getElementById('lead_complaint_crm');

                selectElement.innerHTML = '';
                lead_notes_crm.innerHTML = '';
                lead_complaint_crm.innerHTML = '';

                response.Data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    selectElement.appendChild(option);
                });

                response.Data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    lead_notes_crm.appendChild(option);
                });

                response.Data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    lead_complaint_crm.appendChild(option);
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function openModal(type, id) {
        console.log(id);
        getLeadTaskType();
        getUserList();
        if (id != null) {

            $.ajax({
                url: '{{ route("lead.ajax.getLeadDataWithRecurrence") }}',
                method: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    document.getElementById('taskTitle').value = response.data[0]['title'];
                    if (response.data[0]['is_recurring'] === true) {
                        document.getElementById('isRecurring').checked = true;
                    }
                    document.getElementById('TaskNotesText').innerHTML = response.data[0]['notes'];
                    document.getElementById('TaskStartDate').value = response.data[0]['start_date'];
                    document.getElementById('TaskStartTime').value = response.data[0]['start_time'];
                    document.getElementById('TaskEndDate').value = response.data[0]['end_date'];
                    document.getElementById('TaskEndTime').value = response.data[0]['end_time'];
                    document.getElementById('notify_date').value = response.data[0]['task_date'];
                    document.getElementById('notify_time').value = response.data[0]['task_time'];


                    if (response.data[0]['notify'] === true) {
                        document.getElementById('notify').checked = true;
                    }

                    if (response.data[0]['notification'] === true) {
                        document.getElementById('notification').checked = true;
                    }

                    if (response.data[0]['sms'] === true) {
                        document.getElementById('sms').checked = true;
                    }

                    if (response.data[0]['email'] === true) {
                        document.getElementById('email').checked = true;
                    }


                    var user_id = response.data[0]['user_id'];
                    const getUserList = document.getElementById('getUserList');
                    Array.from(getUserList.options).forEach(function(option) {
                        if (option.value == user_id) {
                            option.selected = true; // Set the option as selected
                        }
                    });


                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        if (type == "summary") {
            $('#secondModalLabel').text('Summary');
            $('#saveCRMLeadTaskWithTimer').hide();
        } else if (type == "add") {
            $('#saveCRMLeadTaskWithTimer').show();
            $('#secondModalLabel').text('New Task');
        } else if (type == "edit") {
            $('#saveCRMLeadTaskWithTimer').show();
            $('#secondModalLabel').text('Edit Task');
        }
        $('#secondModal').modal('show');
    }

    $(document).ready(function() {

        document.getElementById('inlineRadio1').checked = true;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // start here js for time start and pause
        let timerInterval;
        let elapsedSeconds = 0;
        let isRunning = false;

        function toggleTimer() {
            if (isRunning) {
                // Pause the timer
                clearInterval(timerInterval);
                document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-play"></i> Start';
            } else {
                // Start the timer
                timerInterval = setInterval(function() {
                    elapsedSeconds++;
                    document.getElementById('timerDisplay').textContent = formatTime(elapsedSeconds);
                    document.getElementById('start_time').value = formatTime(elapsedSeconds);
                }, 1000);
                document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-stop"></i> Pause';
            }
            isRunning = !isRunning; // Toggle the running state
        }

        function formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
        }

        function pad(number) {
            return number < 10 ? '0' + number : number;
        }

        document.getElementById('toggleTimerBtn').addEventListener('click', toggleTimer);
        // End js for time start and end

        // set the value in Full history table data js Start here
        document.getElementById('pills-fullHistory-tab').addEventListener('click', function() {
            getFullHistoryDataAjax();
        });
        // set the value in Full history table data js End here

        // set the value in calls history table data js Start here
        document.getElementById('pills-Calls-tab').addEventListener('click', function() {
            getCallDataAjax();
        });
        // set the value in calls history table data js End here

        // set the value in Emails history table data js Start here
        document.getElementById('pills-emails-tab').addEventListener('click', function() {
            getEmailDataAjaxCall();
        });
        // set the value in Emails history table data js End here

        // set the value in Tasks table data js Start here
        document.getElementById('pills-tasks-tab').addEventListener('click', function() {
            getTasksDataAjaxCall();
        });
        // set the value in Tasks table data js End here

        // set the value in CRM Lead Notes table data js Start here
        document.getElementById('pills-notes-tab').addEventListener('click', function() {
            getNotesDataAjax();
        });
        // set the value in CRM Lead Notes table data js End here

        // set the value in CRM Lead Notes table data js Start here
        document.getElementById('pills-complaints-tab').addEventListener('click', function() {
            getComplaintDataAjax();
        });
        // set the value in CRM Lead Notes table data js End here

        // on CRM model open set the value for models
        document.querySelectorAll('.set_value_on_CRM_model').forEach(function(anchor) {
            anchor.addEventListener('click', function(event) {
                event.preventDefault();
                // Get the user ID from the data-user-id attribute of the anchor tag
                var leadId = anchor.getAttribute('data-user-id');
                var leadRef = anchor.getAttribute('data-ref');
                // alert(document.getElementById('calls_status').textContent);
                document.getElementById('calls_contact_name').textContent = anchor.getAttribute('data-contact-name');
                document.getElementById('calls_lead_ref').textContent = anchor.getAttribute('data-ref');
                document.getElementById('calls_lead_refs').textContent = leadRef;
                document.getElementById('calls_status').textContent = anchor.getAttribute('data-status');
                document.getElementById('calls_telephone').textContent = anchor.getAttribute('data-telephone');
                document.getElementById('calls_email').textContent = anchor.getAttribute('data-email');
                document.getElementById('related_To').textContent = leadRef;
                document.getElementById('relatedTo').textContent = leadRef;
                document.getElementById('call_lead_ref').textContent = leadRef;
                document.getElementById('call_lead_ref_data').value = leadId;
                document.getElementById('lead_ref_email').textContent = leadRef;
                document.getElementById('lead_ref_notes').textContent = leadRef;
                document.getElementById('lead_id_notes').value = leadId;
                document.getElementById('lead_id_email').value = leadId;
                document.getElementById('lead_id_CRM').value = leadId;
                document.getElementById('lead_ref_complaint').textContent = leadRef;
                document.getElementById('lead_id_complaint').value = leadId;
                document.getElementById('crm_lead_id_task').value = leadId;

                getFullHistoryDataAjax();
                // Open CRM modal
                $('#customerPop').modal('show');
            });
        });
        // on CRM model open set the value for models

        function addCRMTypes(formData, type) {
            $.ajax({
                url: '{{ route("lead.ajax.saveCRMSectionType") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    if (type === 3) {
                        $('#crmTypeModelNotes').modal('hide');
                    } else if (type === 4) {
                        $('#crmTypeModelComplaint').modal('hide');
                    }
                    getCRMTypeData();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Ajax Call for saving CRM section Type for notes
        $('#saveCRMTypesNotes').on('click', function() {
            var title = $('#type_title_notes').val();
            var colourCode = $('#colour_code_notes').val();
            var crmSection = $('#crm_section_notes').val();

            var formData = {
                title: title,
                colour_code: colourCode,
                crm_section: crmSection
            };
            console.log(formData);
            addCRMTypes(formData, 3);
        });

        $('#saveCRMTypesComplaint').on('click', function() {
            var title = $('#type_title_complaint').val();
            var colourCode = $('#colour_code_complaint').val();
            var crmSection = $('#crm_section_complaint').val();

            var formData = {
                title: title,
                colour_code: colourCode,
                crm_section: crmSection
            };
            console.log(formData);
            addCRMTypes(formData, 4);
        });

        // Ajax Call for saving CRM section Type
        $('#saveCRMTypes').on('click', function() {
            var formData = $('#crm_section_type_form').serialize();
            $.ajax({
                url: '{{ route("lead.ajax.saveCRMSectionType") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#crmTypeModel').modal('hide');
                    getCRMTypeData();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax for Add Task Type
        $('#saveTaskType').on('click', function() {
            var formData = $('#lead_task_type_form').serialize();
            $.ajax({
                url: '{{ route("lead.ajax.saveLeadTaskType") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#thirdModal').modal('hide');
                    getLeadTaskType();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax Call for adding lead Reject Type
        $('#lead_reject').on('click', function() {
            var formData = $('#lead_reject_type_form_edit').serialize();
            $.ajax({
                url: '{{ route("lead.ajax.saveLeadRejectTypes") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#secondModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax Call for adding Lead Reject Reasons
        $('#lead_reject_reason').on('click', function() {
            var formData = $('#lead_reject_reason_form').serialize();
            $.ajax({
                url: '{{ route("lead.ajax.saveLeadRejectReasons") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#secondModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax Call for adding CRM Lead Task and timer
        $('#saveCRMLeadTaskWithTimer').on('click', function() {
            const formTypeInput = document.getElementById('form_type');
            if (document.getElementById('nav-home').classList.contains('active')) {
                formTypeInput.value = 'task_form';
            } else if (document.getElementById('nav-profile').classList.contains('active')) {
                formTypeInput.value = 'timer_form';
            }
            var formData = $('#crm_lead_task_form').serialize();
            $.ajax({
                url: '{{ route("lead.ajax.saveCRMLeadTaskAndTimer") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#secondModal').modal('hide');
                    // location.reload();
                    getTasksDataAjaxCall();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // $('#getUserList').on('click', function() {
        //     getUserList();
        // });


        // Using event delegation to listen for clicks on the dynamically created .openAddNewTaskModel button
        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.openAddNewTaskModel')) {
                e.preventDefault();

                const button = e.target.closest('.openAddNewTaskModel');
                const taskId = button.getAttribute('data-id');
                const tasktype = button.getAttribute('data-type');
                console.log(taskId);
                if (tasktype == "edit") {
                    openModal("edit", taskId);
                } else {
                    openModal("summary", taskId);
                }
            }
        });

        // Calls model open and close 
        const openCallsModel = document.getElementById('openCallsModel');
        const callsModel = document.getElementById('callsModal');
        var countries = document.getElementById("countries");
        openCallsModel.onclick = function() {
            getCountriesList(countries);
            getCRMTypeData();
            $('#callsModal').modal('show');
        }
        // calls model open and close

        // Email model open and close
        const openNewEmail = document.getElementById('openNewEmail');
        const NewEmailModel = document.getElementById('NewEmailModel');
        openNewEmail.onclick = function() {
            $('#NewEmailModel').modal('show');
        }
        // Email model open and close

        // Notes model show and hide
        const openNotesModel = document.getElementById('openNotesModel');
        const NewNotesModel = document.getElementById('NewNotesModel');
        openNotesModel.onclick = function() {
            getCRMTypeData();
            $('#NewNotesModel').modal('show');
        }
        // Notes model show

        // CRM Section Type Js Start for model show
        const openModalBtn = document.getElementById('openCrmTypeModel');
        const crmTypeModel = document.getElementById('crmTypeModel');
        openModalBtn.onclick = function() {
            $('#crmTypeModel').modal('show');
        }
        // CRM Section Type Js End for model show

        // CRM Complaint Js Start for model show
        const openComplaintsModel = document.getElementById('openComplaintsModel');
        const compliantsModal = document.getElementById('compliantsModal');
        openComplaintsModel.onclick = function() {
            getCRMTypeData();
            $('#compliantsModal').modal('show');
        }
        // CRM Complaint Js End for model show

        // CRM Section Type ADD model in Complaints Js Start for model  show
        const openCrmTypeModelNotes = document.getElementById('openCrmTypeModelNotes');
        const crmTypeModelNotes = document.getElementById('crmTypeModelNotes');
        openCrmTypeModelNotes.onclick = function() {
            $('#crmTypeModelNotes').modal('show');
        }
        // CRM Section Type ADD model in Complaints Js End for model  show

        // // CRM Section Type ADD model in notes Js Start for model  show
        const openCrmTypeModelComplaints = document.getElementById('openCrmTypeModelComplaints');
        const crmTypeModelComplaint = document.getElementById('crmTypeModelComplaint');
        openCrmTypeModelComplaints.onclick = function() {
            $('#crmTypeModelComplaint').modal('show');
        }
        // // CRM Section Type ADD model in notes Js End for model  show

        const mainCheckbox = document.getElementById('yeson');
        const optionsDiv = document.getElementById('optionsDiv');
        // Open the second modal without hiding the first one
        $('#openSecondModal').on('click', function() {
            optionsDiv.style.display = 'none';
            openModal("add", id = null);
            // $('#secondModal').modal('show');
        });

        // Open the third modal without hiding the first and second ones
        $('#openThirdModal').on('click', function() {
            $('#thirdModal').modal('show');

        });
        $('#openThirdModal2').on('click', function() {
            $('#thirdModal').modal('show');
        });

        mainCheckbox.addEventListener('change', function() {
            if (mainCheckbox.checked) {
                optionsDiv.style.display = 'block';
            } else {
                optionsDiv.style.display = 'none';
            }
        });

        $('.open-modal').on('click', function() {
            var lead_ref = $(this).data('lead_ref');
            $('#lead_ref').val(lead_ref);
        });



    });

    // CRM JS on leads
    document.getElementById('onclickbtnHideShowLeads').addEventListener('click', function() {
        var element = document.getElementById('showDivContLeads');
        element.classList.toggle('show');
    });
    //End  CRM JS on leads

    // notification Div hide show on radio button for emails js End

    const openPopupButton2 = document.getElementById('openPopupButton2');
    const popup2 = document.getElementById('popup2');
    const closePopup2 = document.getElementById('closePopup');

    const openPopupButton = document.getElementById('openPopupButton');
    const popup = document.getElementById('popup');
    const closePopup = document.getElementById('closePopup');


    // Add event listeners to the radio buttons


    openPopupButton2.addEventListener('click', () => {
        popup2.style.display = 'block';
        setTimeout(() => {
            popup2.style.opacity = '1';
        }, 50); // Delay added for transition effect
    });

    closePopup2.addEventListener('click', () => {
        popup2.style.opacity = '0';
        setTimeout(() => {
            popup2.style.display = 'none';
        }, 300); // Ensure the popup is hidden after the transition ends
    });

    openPopupButton.addEventListener('click', () => {
        popup.style.display = 'block';
        setTimeout(() => {
            popup.style.opacity = '1';
        }, 50); // Delay added for transition effect
    });

    closePopup.addEventListener('click', () => {
        popup.style.opacity = '0';
        setTimeout(() => {
            popup.style.display = 'none';
        }, 300); // Ensure the popup is hidden after the transition ends
    });
</script>

<!-- Script For adding CK editor start -->
<script type="importmap">
    {
        "imports": {
            "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.js",
            "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/43.0.0/"
        }
    }
</script>
<!-- calls CK editor Js -->
<script type="module">
    import {
        ClassicEditor,
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Font,
        Underline,
        Alignment
    } from 'ckeditor5';

    ClassicEditor
        .create(document.querySelector('#editor'), {
            plugins: [Essentials, Paragraph, Bold, Italic, Underline, Font, Alignment],
            toolbar: [
                'undo', 'redo', '|', 'bold', 'italic', 'underline', 'alignment', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'
            ]
        })
        .then(editor => {
            window.editor = editor;
            // Add a click event listener to the save button
            document.getElementById('saveCRMCallsModelData').addEventListener('click', function() {
                // Get the CKEditor content
                document.getElementById('calls_notes').value = editor.getData();
                console.log(document.getElementById('calls_notes').value);
                var formData = $('#CRM_calls_form').serialize();

                $.ajax({
                    url: '{{ route("lead.ajax.saveCRMLeadData") }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        alert(response.message);
                        $('#callsModal').modal('hide');
                        getCallDataAjax();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });
        })
        .catch(error => {
            console.error(error);
        });
</script>
<!-- Script For adding CK editor End -->

<script src="https://cdn.ckeditor.com/ckeditor5/ckeditor5-build-classic/ckeditor.js"></script>
<!-- Email CK Editor start -->
<script type="module">
    import {
        ClassicEditor,
        Essentials,
        Paragraph,
        Bold,
        Italic,
        Font,
        Underline,
        Alignment,
        Plugin,
        ButtonView
    } from 'ckeditor5';

    class PreviewPlugin extends Plugin {
        static get pluginName() {
            return 'PreviewPlugin';
        }

        init() {
            const editor = this.editor;

            editor.ui.componentFactory.add('preview', locale => {
                const view = new ButtonView(locale);

                view.set({
                    label: 'Preview',
                    withText: true,
                    tooltip: 'Preview Content'
                });

                view.on('execute', () => {
                    const editorData = editor.getData();

                    // Open a new window to display the formatted content
                    const previewWindow = window.open('', 'Preview', 'width=800,height=600');
                    previewWindow.document.write(`
                    <html>
                        <head>
                            <title>Preview</title>
                            <style>
                                body { font-family: Arial, sans-serif; padding: 20px; }
                                .ck-content { margin: 0; }
                            </style>
                        </head>
                        <body>${editorData}</body>
                    </html>
                `);
                    previewWindow.document.close();
                });

                return view;
            });
        }
    }

    ClassicEditor
        .create(document.querySelector('#emailEditor'), {
            plugins: [Essentials, Paragraph, Bold, Italic, Underline, Font, Alignment, PreviewPlugin],
            toolbar: [
                'undo', 'redo', '|', 'bold', 'italic', 'underline', 'alignment', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|', 'preview'
            ]
        })
        .then(editor => {
            window.editor = editor;
            // var editorData = editor.getData();
            // Add a click event listener to the save button
            document.getElementById('saveCRMLeadEmails').addEventListener('click', function() {

                // Get the CKEditor content
                document.getElementById('emailMessage').value = editor.getData();
                console.log(document.getElementById('emailMessage').value);
                var formData = new FormData(document.getElementById('crm_lead_email_form'));

                console.log(formData);
                $.ajax({
                    url: '{{ route("lead.ajax.saveCRMLeadEmails") }}',
                    method: 'POST',
                    data: formData,
                    processData: false, // Important: Don't process the data
                    contentType: false,
                    success: function(response) {
                        alert(response.message);
                        $('#NewEmailModel').modal('hide');
                        getEmailDataAjaxCall();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

        })
        .catch(error => {
            console.error(error);
        });

    ClassicEditor
        .create(document.querySelector('#NotesEditor'), {
            plugins: [Essentials, Paragraph, Bold, Italic, Underline, Font, Alignment, PreviewPlugin],
            toolbar: [
                'undo', 'redo', '|', 'bold', 'italic', 'underline', 'alignment', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|', 'preview'
            ]
        })
        .then(editor => {
            window.editor = editor;
            var editorData = editor.getData();
            // Add a click event listener to the save button
            document.getElementById('saveCRMLeadNotes').addEventListener('click', function() {
                // Get the CKEditor content
                document.getElementById('CRMNotes').value = editor.getData();
                console.log(document.getElementById('CRMNotes').value);
                // var formData = new FormData(document.getElementById('crm_lead_notes_form'));
                var formData = $('#crm_lead_notes_form').serialize();
                console.log(formData);
                $.ajax({
                    url: '{{ route("lead.ajax.saveCRMLeadNotes") }}',
                    method: 'POST',
                    data: formData,
                    // processData: false,  
                    // contentType: false,  
                    success: function(response) {
                        alert(response.message);
                        $('#NewNotesModel').modal('hide');
                        getNotesDataAjax();
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });

            });

        })
        .catch(error => {
            console.error(error);
        });



    ClassicEditor
        .create(document.querySelector('#complaintEditor'), {
            plugins: [Essentials, Paragraph, Bold, Italic, Underline, Font, Alignment, PreviewPlugin],
            toolbar: [
                'undo', 'redo', '|', 'bold', 'italic', 'underline', 'alignment', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|', 'preview'
            ]
        })
        .then(editor => {
            window.editor = editor;
            var editorData = editor.getData();
            // Add a click event listener to the save button
            document.getElementById('saveCRMLeadComplaint').addEventListener('click', function() {
                // Get the CKEditor content
                document.getElementById('CRMComplaint').value = editor.getData();
                console.log(document.getElementById('CRMComplaint').value);
                var formData = $('#crm_lead_complaint_form').serialize();
                console.log(formData);
                $.ajax({
                    url: '{{ route("lead.ajax.saveCRMLeadComplaint") }}',
                    method: 'POST',
                    data: formData,
                    // processData: false,  
                    // contentType: false,  
                    success: function(response) {
                        alert(response.message);
                        getComplaintDataAjax();
                        $('#compliantsModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });

            });

        })
        .catch(error => {
            console.error(error);
        });
</script>
<!-- Email CK Editor End -->
<!-- Ram 15/10/2024 code for bulk delete -->
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
                var model = 'Lead';
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
                            // location.reload();
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

    function openSentQuoteModal(lead_ref, lead_id, customer_id, type) {
        document.getElementById('sentQuote').textContent = lead_ref;
        document.getElementById('notify_lead_id').value = lead_id;
        document.getElementById('notification_type').value = type;
        document.getElementById('converted_customer').value = customer_id;
        $('#sentToQuoteModal').modal('show');
    }


    $('.delete_checkbox').on('click', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });

    const getUserListNotify = "{{ route('lead.ajax.getUserList') }}";
    const saveLeadConvertQuote = '{{ route("lead.ajax.saveLeadConvertQuote") }}';
</script>
<script type="module">
    document.getElementById('exportCsv').addEventListener('click', function() {
        const table = document.getElementById('exampleOne'); // Get the table
        const selectedColumns = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]; // Specify which columns to export (e.g., 0 for Name, 2 for Phone)
        let csvContent = '';

        const now = new Date();
        const formattedDateTime = now.toISOString().replace(/[-T:]/g, '_').split('.')[0];
        const filename = `list_export_${formattedDateTime}.csv`;
        // Extract headers (only selected columns)
        const headers = Array.from(table.querySelectorAll('thead th'))
            .filter((_, index) => selectedColumns.includes(index)) // Filter headers by selected columns
            .map(th => th.textContent.trim())
            .join(',');
        csvContent += headers + '\n';

        // Extract rows (only selected columns)
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        rows.forEach(row => {
            const cells = Array.from(row.querySelectorAll('td'))
                .filter((_, index) => selectedColumns.includes(index)) // Filter cells by selected columns
                .map(td => td.textContent.trim());
            csvContent += cells.join(',') + '\n';
        });

        // Create and download CSV file
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>
<!-- end here -->
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script type="text/javascript" src="{{ url('public/js/salesFinance/leads/customLeads.js') }}"></script>