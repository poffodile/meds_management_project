@include('frontEnd.salesAndFinance.jobs.layout.header')
</script><link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.css">
<style>
    <?php if($list_mode == 'ACTIVE'){?>
    #active {
        background-color:#474747;
    }
    .icon {
        color: white;
        background-color: #039b1c;
        border-radius: 15px;
        padding: 5px;
        font-size: 10px;
    }
    
    <?php }else{?>
    #inactive{
        background-color:#474747;
    }
    .icon {
        color: white;
        background-color: #474747;
        border-radius: 15px;
        font-size: 10px;
        padding: 5px;
    }
    <?php }?>
    .textbox {
    box-sizing: border-box;
    perspective: 500px;
    position: relative;
    text-align: left;
}
.image_style {
    cursor: pointer;
}
.textbox input {
    padding: 10px 14px;
    width: 100%;
}

.textbox input::placeholder {
    color: #ccc;
}

.textbox .autoComplete {
    left: 0;
    position: absolute;
    top: calc(100% + 5px);
    width: 100%;
}

.textbox .autoComplete .item {
    animation: showItem .3s ease forwards;
    background-color: #fff;
    box-shadow: 0 8px 8px -10px rgba(0, 0, 0, .4);
    box-sizing: border-box;
    color: #7C8487;
    cursor: pointer;
    display: block;
    font-size: .8rem;
    opacity: 0;
    outline: none;
    padding: 10px;
    text-decoration: none;
    transform-origin: top;
    /* transform: rotateX(-90deg); */
    transform: translateX(10px);
}

.textbox .autoComplete .item:hover,
.textbox .autoComplete .item:focus {
    background-color: #fafafa;
    color: #D1822B;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
}
.pagination li {
    margin: 0 5px;
    cursor: pointer;
    padding: 5px 10px;
    border: 1px solid #ccc;
}
.pagination .active {
    background-color: #007bff;
    color: white;
}
.pagination li:hover {
    background-color: #f0f0f0;
}
.contact-row:hover {
    cursor: pointer;
}
.selected-row td {
    background-color: #f0f8ff;
    /* border:2px solid red; */
}
    
@keyframes showItem {
    0% {
        opacity: 0;
        /* transform: rotateX(-90deg); */
        transform: translateX(10px);
    }

    100% {
        opacity: 1;
        /* transform: rotateX(0); */
        transform: translateX(0);
    }
}
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ced4da;
    font-size: 14px;
}

.select2-container .select2-selection--single .select2-selection__arrow {
    height: 100%;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    padding: 4px;
}
.select2-container .select2-selection--multiple{
    min-height:32px !important;
 }
/* 
 .CRMFullModel .modal-dialog.modal-xl {
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
    #CRMPop td .fa {
        color:#0877bd;
    }
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>All Customers</h3>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="{{ url('customer_add_edit') }}" class="profileDrop" >New Customer</a>
                    <a href="{{ url('customers?list_mode=ACTIVE') }}" class="profileDrop" id="active">Active Customer <span>({{$active_customer}})</span></a>
                    <a href="{{ url('customers?list_mode=INACTIVE') }}" class="profileDrop" id="inactive">Inactive Customer<span>({{$inactive_customer}})</span></a>
                    <a href="#!" class="profileDrop">Bulk Actions<span></span></a>
                    <a href="#!" class="profileDrop">Import</a><a href="#!">Click here to download import template</a>
                </div>
            </div>
        </div>

        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>

                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <a href="#" class="profileDrop">Mark As completed</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-success text-center" style="display:none">
                        <p>Status Change Successfully Done</p>
                    </div>

                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:60px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Address</th>
                                <th>Contact Name</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach($customer as $k=>$val){?>
                            <tr>
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                <td>{{++$k}}.</td>
                                <td>{{$val->name}}</td>
                                <td>{{$val->address}}</td>
                                <td>{{$val->contact_name}}</td>
                                <td>{{$val->email}}</td>
                                <td>{{$val->telephone}}</td>
                                <td><a href="javascript:void(0)" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa fa-check icon"></i></a></td>
                                <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="{{url('customer_add_edit?key=')}}{{$val->id}}" class="dropdown-item">Edit Details</a>
                                                <hr class="dropdown-divider">
                                                <a href="javasrcript:void(0)" onclick="get_modal(1,null)" class="dropdown-item">Record Expense</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="get_modal(2,{{$val->id}})" class="dropdown-item">CRM History</a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </td>
                            </tr>
                           <?php }?>
                        </tbody>
                    </table>

                </div> <!-- End off main Table -->
                <!-- Model start here -->

                    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Reject Confirmation</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form>
                                        <div class="mb-3">
                                            <label for="recipient-name" class="col-form-label">Lead Ref:</label>
                                            <input type="text" class="form-control" id="recipient-name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="recipient-name" class="col-form-label">Reject Type:</label>
                                            <input type="text" class="form-control" id="recipient-name">
                                        </div>
                                        <a href="#!" data-bs-toggle="modal" data-bs-target="#rejectModal2"><i class="fa-solid fa-square-plus fs-4"></i></a>
                                        <div class="mb-3">
                                            <label for="message-text" class="col-form-label">Reject Reason:</label>
                                            <textarea class="form-control" id="message-text"></textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary">Send message</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="rejectModal2" tabindex="1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel2">New message</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form>
                                        <div class="mb-3">
                                            <label for="recipient-name" class="col-form-label">Recipient:</label>
                                            <input type="text" class="form-control" id="recipient-name">
                                        </div>
                                        <div class="mb-3">
                                            <label for="message-text" class="col-form-label">Message:</label>
                                            <textarea class="form-control" id="message-text"></textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary">Send message</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- end here -->
                 <!--Expense Modal start here -->
                <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content add_Customer">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="customerModalLabel">Expense</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                    <p>The Expense has been saved Successfully</p>
                                    </div>
                                    
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="formDtail">
                                                <form id="form_data" class="customerForm">
                                                    @csrf
                                                    <input type="hidden" name="id" id="id">
                                                    <input type="hidden" name="home_id" id="home_id" value="{{Auth::user()->home_id}}">
                                                    <div class="row">
                                                        <div class="col-md-6 col-lg-6 col-xl-6">
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Expense Name <span class="radStar">*</span></label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="title" name="title" value="" placeholder="Expense Name">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Net Amount <span class="radStar">*</span></label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="amount" name="amount" value="" placeholder="Net Amount">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputProject"
                                                                        class="col-sm-4 col-form-label">Vat <span class="radStar">*</span></label>
                                                                    <div class="col-sm-8">
                                                                        <select class="form-control editInput selectOptions"
                                                                            id="vat" name="vat">
                                                                            <option value="0" selected>Custom VAT Amount</option>
                                                                            @foreach($rate as $rate_vale)
                                                                                <option value="{{$rate_vale->tax_rate}}">{{$rate_vale->name}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Vat Amount</label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="vat_amount" name="vat_amount" value="" onkeyup="calculate_vat()">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Gross Amount <span class="radStar">*</span></label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="gross_amount" name="gross_amount" value="" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Expense Date <span class="radStar">*</span></label>
                                                                    <div class="col-sm-8">
                                                                        <input type="date" class="form-control editInput"
                                                                            id="expense_date" name="expense_date" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Expense By</label>
                                                                    <div class="col-sm-8">
                                                                        <select class="form-control editInput selectOptions"
                                                                            id="user_id" name="user_id">
                                                                            @foreach($users as $user_val)
                                                                            <option value="{{ $user_val->id }}">{{ $user_val->name }}</option>

                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-4 col-form-label">Reference</label>
                                                                    <div class="col-sm-8">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="reference" name="reference" value="">
                                                                    </div>
                                                                </div>
                                                                
                                                        </div>
                                                        <div class="col-md-6 col-lg-6 col-xl-6">
                                                            <div class="mb-2 row">
                                                                <label for="inputProject"
                                                                    class="col-sm-3 col-form-label">Customer</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control editInput selectOptions"
                                                                        id="customer_id" name="customer_id" onchange="find_project(null,null)">
                                                                        <option selected disabled >--None--</option>
                                                                        @foreach($customer as $customer_val)
                                                                            <option value="{{$customer_val->id}}">{{$customer_val->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputProject"
                                                                    class="col-sm-3 col-form-label">Project</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control editInput selectOptions"
                                                                        id="project_id" name="project_id" disabled>
                                                                        <option >None</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Job</label>
                                                                <div class="col-sm-9">
                                                                    <div class="textbox completeIt">
                                                                        <input type="text" class="form-control editInput" id="job" autocomplete="off" autofocus name="job_display">
                                                                        <input type="hidden" id="selectedJobRef" name="job">
                                                                        <div class="icon"></div>
                                                                        <div class="autoComplete" id="jobList"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputProject"
                                                                    class="col-sm-3 col-form-label">Job Appointment</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control editInput selectOptions"
                                                                        id="job_appointment_id" name="job_appointment_id" disabled >
                                                                        <option >None</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Authorised</label>
                                                                <div class="col-sm-9">
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input " type="radio" name="authorisedradio" id="authorised1">
                                                                        <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline">
                                                                        <input class="form-check-input " type="radio" name="authorisedradio" id="authorised0" checked="">
                                                                        <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                                                    </div>
                                                                    <input type="hidden" id="authorised" name="authorised" value="0">
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Billable?</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input " type="radio" name="billableradio" id="billabl1" checked="">
                                                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input " type="radio" name="billableradio" id="billable0">
                                                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                                                        </div>
                                                                        <input type="hidden" id="billable" name="billable" value="0">
                                                                    </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Paid</label>
                                                                    <div class="col-sm-9">
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input " type="radio" name="paidradio" id="paid1">
                                                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-inline">
                                                                            <input class="form-check-input " type="radio" name="paidradio" id="paid0" checked="">
                                                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                                                        </div>
                                                                        <input type="hidden" id="paid" name="paid" value="0">
                                                                    </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-2 col-form-label">Notes</label>
                                                                <div class="col-sm-10">
                                                                    <textarea class="form-control textareaInput" name="notes" id="notes" rows="3" ></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-2 col-form-label">Attachments</label>
                                                                <div class="col-sm-10">
                                                                    <input type="file" class="editInput"
                                                                        id="attachments" name="attachments" value="">
                                                                    <p>(Max file size 25 MB)</p>
                                                                    <p id="fileSizeError" style="color: red; display: none;">File larger than 25 MB.</p>
                                                                    <p id="file_name"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div> <!-- End row -->
                                </div>
                                <div class="modal-footer customer_Form_Popup">

                                    <button type="button" class="profileDrop" id="save_data">Save</button>
                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- end here -->
                 <!-- ****************CRM History Modal ****************-->
<div class="modal fade CRMFullModel" id="CRMPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">CRM Dashboard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body crmModelCont pt-2">
                <!-- <div class="jobsection pb-2 hideandshow">
                    <button class="profileDrop" id="onclickbtnHideShowLeads">Hide/Show</button>
                </div>
                <div id="showDivContLeads">
                    <div class="newJobForm mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="">
                                    <h4 class="contTitle text-center">Contact Details</h4>
                                </div>
                                <div class="row pt-3">
                                    <label class="col-md-4"><strong>Full Name:</strong></label>
                                    <div class="col-md-8">
                                        <span id="calls_contact_name"></span>
                                    </div>
                                </div>
                                <div class="row pt-3">
                                    <label class="col-md-4"><strong>Email Address:</strong></label>
                                    <div class="col-md-8">
                                        <span id="calls_email"></span>
                                    </div>
                                </div>
                                <div class="row pt-3">
                                    <label class="col-md-4"><strong>Telephone:</strong></label>
                                    <div class="col-md-8">
                                        <span id="calls_telephone"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="">
                                    <h4 class="contTitle text-center">Lead Details</h4>
                                </div>
                                <div class="row pt-3">
                                    <label class="col-md-4"><strong>Lead Ref.:</strong></label>
                                    <div class="col-md-8">
                                        <span id="calls_lead_refs"></span>
                                        <input type="hidden" id="lead_id_CRM" name="">

                                    </div>
                                </div>
                                <div class="row pt-3">
                                    <label class="col-md-4"><strong>Lead Status:</strong></label>
                                    <div class="col-md-8">
                                        <span id="calls_status"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
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
                    <li class="nav-item" role="presentation">
                        <button class="nav-link " id="pills-contacts-tab" data-bs-toggle="pill" data-bs-target="#pills-contacts" type="button" role="tab" aria-controls="pills-contacts" aria-selected="false">Contacts</button>
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
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                            <tbody id="crm_customer_all_data">
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
                                        <ul class="pagination" id="pagination"></ul>
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
                                        <a href="javascript:void(0)" class="profileDrop p-2 crmNewBtn" id="openCallsModel"> New</a>
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                            <tbody id="customer_crmData">
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls-call"></div>
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
                                <div class="col-sm-5">
                                    <div class="jobsection  mt-3">
                                        <select name="" class="form-control editInput" id="">
                                            <option value="1">All</option>
                                            <option value="2">Customer Related</option>
                                            <option value="3">Quote Related</option>
                                            <option value="4">Job Related</option>
                                            <option value="5">Invoice Related</option>
                                                                                
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                                    <th>Customer Visible</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="crm_customer_email">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls-email"></div>
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
                                                    <input type="hidden" name="task_customer_id" id="task_customer_id" class="customer_id">
                                                    <input type="hidden" name="task_id" id="task_id" class="task_id">
                                                    @csrf
                                                    <div class="tab-content p-3 border bg-light" id="nav-tabContent">
                                                        <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <div class="mb-2 row">
                                                                        <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                                                        <div class="col-sm-9">
                                                                            <p class="customer_name"></p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task User <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-8">
                                                                            <input type="hidden" name="form_type" id="form_type" value="">
                                                                            <select class="form-control editInput" name="user_id" id="getUserList">
                                                                            @foreach($users as $value)
                                                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                                            @endforeach
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
                                                                                @foreach($task_type as $type)
                                                                                    <option value="{{$type->id}}">{{$type->title}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <a href="#!" class="formicon" id="openThirdModal"><i class="fa-solid fa-square-plus"></i></a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Start Date <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-4">
                                                                            <input type="date" class="form-control editInput" name="start_date" id="TaskStartDate" onchange="setMinEndDate()" min="">
                                                                        </div>
                                                                        <div class="col-sm-4">
                                                                            <input type="time" class="form-control editInput" name="start_time" id="TaskStartTime">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">End Date <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-4">
                                                                            <input type="date" class="form-control editInput" name="end_date" id="TaskEndDate" value="" min="" disabled>
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

                                                                    <!-- <div class="mb-3 row">
                                                                        <label for="related_to" class="col-sm-3 col-form-label">Related To</label>
                                                                        <div class="col-sm-9">
                                                                            <span class="editInput" id="related_To"></span>
                                                                        </div>
                                                                    </div> -->
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
                                                                    <!-- <div class="mb-3 row">
                                                                        <label for="related_to" class="col-sm-4 col-form-label">Related To</label>
                                                                        <div class="col-sm-8">
                                                                            <span class="editInput" id="relatedTo"></span>
                                                                        </div>
                                                                    </div> -->
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="mb-3 row">
                                                                        <label for="staticEmail" class="col-sm-4 col-form-label">Task Type <span class="radStar ">*</span></label>
                                                                        <div class="col-sm-6">
                                                                            <select class="form-control editInput" name="task_type_id" id="lead_task_types_timer">
                                                                                @foreach($task_type as $type1)
                                                                                    <option value="{{$type1->id}}">{{$type1->title}}</option>
                                                                                @endforeach
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
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                                </tr>
                                            </thead>
                                            <tbody id="crm_customer_task">
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls-task"></div>
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
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                            <tbody id="crm_customer_note">
                                                
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls-note"></div>
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
                                            <input type="text" class="form-control editInput search_data_email" onkeyup="searchDataWithEmail('Complaints')" placeholder="Keyword to search" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="crm_customer_complaint">
                                                
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="tab-pane fade" id="pills-contacts" role="tabpanel" aria-labelledby="pills-contacts-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Contacts</label>
                            <div class="row">
                                <div class="col-sm-1">
                                    <div class="jobsection mt-3">
                                        <a href="#" class="profileDrop p-2 crmNewBtn contact_add" id="openContactsModel"> New</a>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3  mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Keyword to search" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
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
                                                    <th>Note(s)</th>
                                                    <th>Customer Visible</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="crm_customer_contact">
                                                
                                            </tbody>
                                        </table>
                                        <div id="pagination-controls-contact"></div>
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
                    @csrf
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <button type="button" class="profileDrop search_contacts" id="search_contacts">Search Contacts</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Customer <span class="radStar ">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="call_customer_id" id="call_customer_id">
                                @foreach($customer as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Contact </label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="call_contact" id="call_contact" class="notes_contact">
                                
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <a href="#!" class="formicon contact_add" id="contact_add"><i class="fa-solid fa-square-plus"></i></a>
                        </div>

                    </div>
                    <div class="mb-2 row">
                        <!-- <input type="hidden" name="call_customer_id" id="call_customer_id" class="customer_id"> -->
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
                                
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <a href="javascript:void(0)" class="formicon" id="openCrmTypeModel"><i class="fa-solid fa-square-plus"></i></a>
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
                    <!-- <div class="mb-2 row">
                        <label for="calls_lead_ref" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="call_lead_ref"></span>
                            <input type="hidden" name="lead_ref" id="call_lead_ref_data">
                        </div>
                    </div> -->
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
                    @csrf
                     <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <button type="button" class="profileDrop search_contacts" id="search_contacts">Search Contacts</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Customer <span class="radStar ">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="notes_customer_id" id="notes_customer_id">
                                @foreach($customer as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Contact </label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="notes_contact" id="notes_contact" class="notes_contact">
                                
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <a href="#!" class="formicon contact_add" id="contact_add"><i class="fa-solid fa-square-plus"></i></a>
                        </div>

                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Type <span class="radStar ">*</span> </label>
                        <div class="col-sm-8">
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
                                    <input type="checkbox" name="notification" id="calls_notify_who1" value="1" checked> Notification (User Only)
                                </label>
                                <label for="calls_notify_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                </label>
                                <label for="calls_notify_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_notify_who3" value="1" checked> Email
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
                <form  id="crm_lead_complaint_form">
                    @csrf
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label"></label>
                        <div class="col-sm-9">
                            <div class="col-form-label">
                                <button type="button" class="profileDrop search_contacts" id="search_contacts1">Search Contacts</button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Customer <span class="radStar ">*</span></label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="complaint_customer_id" id="complaint_customer_id">
                                @foreach($customer as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Contact </label>
                        <div class="col-sm-8">
                            <select class="form-control editInput" name="comaplint_contact" id="comaplint_contact" class="notes_contact">
                                
                            </select>
                        </div>
                        <div class="col-sm-1">
                            <a href="#!" class="formicon contact_add" id="contact_add1"><i class="fa-solid fa-square-plus"></i></a>
                        </div>

                    </div>
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
                                    <input type="checkbox" name="notification" id="calls_complaint_who1" value="1" checked> Notification (User Only)
                                </label>
                                <label for="calls_complaint_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_complaint_who2" value="1"> SMS
                                </label>
                                <label for="calls_complaint_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_complaint_who3" value="1" checked> Email
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
                <form id="">
                    @csrf
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
                    @csrf
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
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
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
                <form action="" id="crm_customer_email_form" enctype='multipart/form-data'>
                    @csrf
                    <input type="hidden" name="email_customer_id" id="email_customer_id" class="customer_id">
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">To <span class="radStar ">*</span> </label>
                        <div class="col-sm-9">
                            <!-- <input type="text" class="form-control editInput" name="to" id="to" value=""> -->
                            <select name="to" class="form-control editInput" id="to">
                                <option value=""></option>
                                @foreach($customer_email_to as $email_val)
                                    <option value="{{$email_val['id']}}">{{$email_val['name']}} [{{$email_val['email']}}] --{{$email_val['type']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Cc </label>
                        <div class="col-sm-9">
                            <!-- <input type="text" class="form-control editInput" name="cc" id="cc"> -->
                            <select name="cc" class="form-control editInput" id="cc">
                                <option value=""></option>
                                @foreach($customer_email_to as $email_val1)
                                    <option value="{{$email_val1['id']}}">{{$email_val1['name']}} [{{$email_val1['email']}}] --{{$email_val1['type']}}</option>
                                @endforeach
                            </select>
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
                            <input type="file" class="editInput" name="image_attachment" id="image_attachment">
                        </div>
                    </div>
                    <!-- <div class="mb-2 row">
                        <label for="type_title" class="col-sm-3 col-form-label">Related To </label>
                        <div class="col-sm-9">
                            <span class="editInput" id="lead_ref_email"></span>
                            <input type="hidden" id="lead_id_email" name="lead_id">
                        </div>
                    </div> -->
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
                                    @foreach($customer as $value)
                                    @if($value->email)
                                    <option value="{{ $value->id }}">Me- {{ $value->email }}/No Mobile</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-3 col-form-label">Send As <span class="radStar ">*</span> </label>
                            <div class="col-sm-9">
                                <label for="calls_notify_who1" class="editInput">
                                    <input type="checkbox" name="notification" id="calls_notify_who1" value="1" checked> Notification (User Only)
                                </label>
                                <label for="calls_notify_who2" class="editInput">
                                    <input type="checkbox" name="sms" id="calls_notify_who2" value="1"> SMS
                                </label>
                                <label for="calls_notify_who3" class="editInput">
                                    <input type="checkbox" name="email" id="calls_notify_who3" value="1" checked> Email
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="mb-2 row">
                        <label for="inputCity" class="col-sm-3 col-form-label">Customer Visible? </label>
                        <div class="col-sm-9">
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault1" value="0" checked>
                            <label class="form-check-label editInput" for="flexRadioDefault1">No</label>
                            <input class="form-check-input" type="radio" name="customer_visible" id="flexRadioDefault2" value="1">
                            <label class="form-check-label editInput" for="flexRadioDefault2">Yes</label>
                        </div>
                    </div> -->
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
  <!--  Modal start here -->
  <div class="modal fade" id="task_type_modal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Task Type - Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                        <p id="message"></p>
                    </div>
                        <div class="col-md-6 col-lg-12 col-xl-12">
                            <div class="formDtail">
                                <form id="task_type_form_data" class="customerForm">
                                    <input type="hidden" name="id" id="id">
                                    <div class="mb-2 row">

                                        <label for="inputName" class="col-sm-3 col-form-label">Task Type <span class="radStar ">*</span></label>


                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput"
                                                id="name" name="title" value="">
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputProject"
                                            class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions"
                                                id="statusModal" name="status">
                                                <option value="1" >Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </div>
                <div class="modal-footer customer_Form_Popup">

                    <button type="button" class="profileDrop" id="save_task_type_data">Save</button>
                    <!-- <button type="button" class="profileDrop" id="save_dataClose">Save &
                        Close</button> -->
                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
                    <!-- end here -->
    
        
      <!-- Search Modal start here -->
      <div class="modal fade" id="search_contactsModal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">All Contacts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body crmModelCont pt-2">
                    <ul class="nav nav-pills mb-3 mt-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-search_customers-tab" data-bs-toggle="pill" data-bs-target="#pills-search_customers" type="button" role="tab" aria-controls="pills-search_customers" aria-selected="true">Customers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="pills-search_suppliers-tab" data-bs-toggle="pill" data-bs-target="#pills-search_suppliers" type="button" role="tab" aria-controls="pills-search_suppliers" aria-selected="false">Suppliers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link " id="pills-search_users-tab" data-bs-toggle="pill" data-bs-target="#pills-search_users" type="button" role="tab" aria-controls="pills-search_users" aria-selected="false">Users</button>
                        </li>
                        
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-search_customers" role="tabpanel" aria-labelledby="pills-search_customers-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Customer Contacts</label>
                            <div class="row">
                                    <label class="col-sm-1  mt-3 col-form-label">Search:</label>
                                <div class="col-sm-3">
                                   
                                    <div class="jobsection  mt-3">
                                        <select name="" class="form-control editInput" id="">
                                            <option value="1">All</option>
                                            <option value="2">Customer</option>
                                            <option value="3">Contacts</option>
                                            <option value="4">Customer Sites</option>
                                                                                
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3 mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-3">
                                    <div class="mb-3 mt-3">
                                        <button type="button" class="profileDrop" onclick="get_contactModal(1)" id="search_contacts">Add Contact</button>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="CRMFullHistoryData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Company</th>
                                                    <th>Contact Name</th>
                                                    <th>Full Name</th>
                                                    <th>Email Address</th>
                                                    <th>Telephone</th>
                                                    <th>Mobile</th>
                                                </tr>
                                            </thead>
                                            <tbody id="customer_contact_list">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
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
                                <div class="col-sm-12 d-flex justify-content-end mt-4">
                                    <button type="button" class="profileDrop me-2" onclick="insertSelectedContact()">Insert</button>
                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-search_suppliers" role="tabpanel" aria-labelledby="pills-search_suppliers-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Supplier Contacts</label>
                            <div class="row">
                                <label class="col-sm-1  mt-3 col-form-label">Search:</label>
                                <div class="col-sm-3">
                                    <div class="jobsection  mt-3">
                                        <select name="" class="form-control editInput" id="">
                                            <option value="1">All</option>
                                            <option value="2">Supplier</option>
                                            <option value="3">Contacts</option>
                                                                                
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3 mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-3">
                                    <div class="mb-3 mt-3">
                                        <button type="button" class="profileDrop" onclick="get_contactModal(2)" id="search_contacts">Add Contact</button>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="CRMFullHistoryData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Company</th>
                                                    <th>Contact Name</th>
                                                    <th>Full Name</th>
                                                    <th>Email Address</th>
                                                    <th>Telephone</th>
                                                    <th>Mobile</th>
                                                </tr>
                                            </thead>
                                            <tbody id="supplier_contact_list">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
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
                                <div class="col-sm-12 d-flex justify-content-end mt-4">
                                    <button type="button" class="profileDrop me-2" onclick="insertSelectedContact()">Insert</button>
                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pills-search_users" role="tabpanel" aria-labelledby="pills-search_users-tab" tabindex="0">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">User Contacts</label>
                            <div class="row">
                            <label class="col-sm-1  mt-3 col-form-label">Search:</label>
                                <div class="col-sm-3">
                                    
                                    <div class="jobsection  mt-3">
                                        <select name="" class="form-control editInput" id="">
                                            <option value="1">All</option>
                                            <option value="2">User</option>
                                            <option value="3">Contacts</option>
                                                                                
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-sm-3">
                                    <form class="searchForm" action="">
                                        <div class="input-group mb-3 mt-3">
                                            <input type="text" class="form-control editInput" placeholder="Your Email" name="email">
                                            <button type="button" class="input-group-text editInput">Search</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-sm-3">
                                    <div class="mb-3 mt-3">
                                        <button type="button" class="profileDrop" onclick="get_contactModal(3)" id="search_contacts">Add Contact</button>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="CRMFullHistoryData">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Company</th>
                                                    <th>Contact Name</th>
                                                    <th>Full Name</th>
                                                    <th>Email Address</th>
                                                    <th>Telephone</th>
                                                    <th>Mobile</th>
                                                </tr>
                                            </thead>
                                            <tbody id="user_contact_list">
                                                <tr>
                                                    <td></td>
                                                    <td></td>
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
                                <div class="col-sm-12 d-flex justify-content-end mt-4">
                                    <button type="button" class="profileDrop me-2" onclick="insertSelectedContact()">Insert</button>
                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                </div>
                
            </div>
        </div>
    </div>
      <!-- end here -->
       <!-- Contact Modal start here -->
    <div class="modal fade" id="contact_modal" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerModalLabel">Add Customer Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form id="contact_form">
                                            <input type="hidden" id="userType" value="1">
                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label CustomerLabel">Customer <span class="radStar">*</span></label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions" id="contact_customer_id">
                                                        <option selected disabled>Select Customer</option>
                                                        <?php foreach($customer as $customer_val){?>
                                                            <option value="{{$customer_val->id}}">{{$customer_val->name}}</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="mb-2 row">
                                                <label class="col-sm-3 col-form-label">Default Billing</label>
                                                <div class="col-sm-9">

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_yes">
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_no" checked>
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio2">No</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Contact
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_contact_name">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Job
                                                    Title (Position)</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions get_job_title_result"
                                                        id="contact_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $con_val_title){?>
                                                            <option value="{{$con_val_title->id}}">{{$con_val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="javascript:void(0)" class="formicon job_title_modal"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputEmail"
                                                    class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_email">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputTelephone"
                                                    class="col-sm-3 col-form-label">Telephone</label>
                                                    <div class="col-sm-2">
                                                    <select class="form-control editInput selectOptions">
                                                        @foreach($country_code as $code)
                                                            <option value="{{$code->id}}">+{{$code->code}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_phone">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputMobile"
                                                    class="col-sm-3 col-form-label">Mobile</label>
                                                    <div class="col-sm-2">
                                                        <select class="form-control editInput selectOptions">
                                                        @foreach($country_code as $country_code)
                                                            <option value="{{$country_code->id}}">+{{$country_code->code}}</option>
                                                        @endforeach
                                                        </select>
                                                    </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_mobile">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Fax</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_fax">
                                                </div>
                                            </div>
                                            
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="">
                                        <div class="mb-2 row">
                                            <label class="col-sm-3 col-form-label">Address Details</label>
                                            <div class="col-sm-9">

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="inlineRadioOptions" id="contact_default_address" onchange="default_address()">
                                                </div>
                                            </div>
                                        </div>
                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" id="contact_address" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">City</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_city">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCounty"
                                                    class="col-sm-3 col-form-label">County</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_country_input">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Pincode</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_pincode">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="contact_country_id">
                                                        <option selected disabled>Select Coutry</option>
                                                        <?php foreach($country as $countryval){?>
                                                        <option value="{{$countryval->id}}" class="contact_country_id">{{$countryval->name}} ({{$countryval->code}})</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div> <!-- End row -->

                        </div>
                        <div class="modal-footer customer_Form_Popup">

                            <button type="button" class="profileDrop" onclick="save_contact()">Save</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        <!-- end here -->
          <!-- Job title Modal start -->
     <div class="modal fade" id="job_modaltitle" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Job Title - Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="job_title_form">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Job Title <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="job_title_name" class="form-control editInput" id="job_title_name" value="" placeholder="Job Title">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="job_title_status" name="job_title_status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_job_title()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
     <!-- end here -->

            </div>
        </di>
    </div>
    </section>
    <!-- Moment js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script>
        function status_change(id, status){
            var token='<?php echo csrf_token();?>'
            var model="Customer";
            $.ajax({
                type: "POST",
                url: "{{url('/status_change')}}",
                data: {id:id,status:status,model:model,_token:token},
                success: function(data) {
                    // console.log(data);
                    if($.trim(data)==1){
                        // $('.alert').show().fadeOut(800);
                        $(".alert").show('slow' , 'linear').delay(2000).fadeOut(setTimeout(function() {
                        location.reload();
                    }, 3000));

                        
                    }
                    
                }
            });
        }
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
            var model='Customer';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    // console.log(data);
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
<script>
    function get_modal(modal,id){
        if(modal == 1){
            $("#form_data")[0].reset();
            $("#customerPop").modal('show');
        }else if(modal == 2){
          $(".customer_id").val(id);
            // $("#form_data")[0].reset();
            $("#CRMPop").modal('show');
            get_customer_details(id);
            get_all_crm_customer_call(id,pageUrl = '{{ url("get_all_crm_customer_call") }}');
            get_all_crm_customer_email(id,pageUrl = '{{ url("get_all_crm_customer_email") }}');
            get_all_crm_customer_task(id,pageUrl = '{{ url("get_all_crm_customer_task") }}');
            get_all_crm_customer_note(id,pageUrl = '{{ url("get_all_crm_customer_note") }}');
            get_all_crm_customer_complaint(id,pageUrl = '{{ url("get_all_crm_customer_complaint") }}');
            get_all_crm_customer_contacts(id,pageUrl = '{{ url("get_all_crm_customer_contacts") }}');
            full_history();
        }
    }
    function get_customer_details(id){
        var token='<?php echo csrf_token();?>'
            $.ajax({
                type: "POST",
                url: "{{url('/get_customer_details')}}",
                data: {id:id,_token:token},
                success: function(data) {
                    // console.log(data)
                    $('.customer_name').text(data.customer.name);
                    $("#notes_customer_id").val(data.customer.id);
                    $("#call_customer_id").val(data.customer.id);
                    $("#complaint_customer_id").val(data.customer.id);
                    // 
                    var selectHTML='';
                    $.each(data.contact, function(index, contact) {
                            selectHTML += '<option value="' + contact.id + '">' + contact.contact_name + '</option>';
                            $("#notes_contact").html(selectHTML);
                            $("#call_contact").html(selectHTML);
                            $("#comaplint_contact").html(selectHTML);
                        });
                },
                error: function(xhr, status, error) {
                   var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
    }
</script>
<script>
    const maxFileSize = 25 * 1024 * 1024;
    const fileInput = document.getElementById('attachments');
    const errorMessage = document.getElementById('fileSizeError');

    fileInput.addEventListener('change', function() {
        errorMessage.style.display = 'none';
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size;
            if (fileSize > maxFileSize) {
                errorMessage.style.display = 'block';

                fileInput.value = '';
            }
        }
    });
    $("#amount").on('keyup', function() {
        var amount = parseFloat($("#amount").val());
        if (!isNaN(amount)) {
            $("#gross_amount").prop('disabled', false);
            $("#vat_amount").val('0.00');
            $("#gross_amount").val(amount.toFixed(2));
        } else {
            $("#gross_amount").val('');
            $("#vat_amount").val('');
            $("#gross_amount").prop('disabled', true);
        }
    });
    $("#vat").on('change', function(){
        var vat=parseFloat($("#vat").val());
        var amount = parseFloat($("#amount").val());
        if(vat == 0){
            $("#vat_amount").val('0.00');
        }else{
            $("#vat_amount").val(vat);
        }
        var calculation=amount*vat/100;
        var gross_amount=amount+calculation;
        $("#gross_amount").val(gross_amount.toFixed(2));
    });
    function calculate_vat(){
        var vat_amount=parseFloat($("#vat_amount").val());
        // alert(vat_amount)
        var amount = parseFloat($("#amount").val());
        if (!isNaN(vat_amount)) {
            // console.log(1);
            $("#vat").val(0);
            var calculation=amount*vat_amount/100;
            var gross_amount=amount+calculation;
            $("#gross_amount").val(gross_amount.toFixed(2));
        }else{
            // console.log(2);
            $("#gross_amount").val(amount.toFixed(2));
        }
    }
    function find_project(customer_id,project_id){
        var customer_id;
        if(customer_id == '' || customer_id == null){
            customer_id=$("#customer_id").val();
        }else{
            customer_id=customer_id;
        }
        var token='<?php echo csrf_token();?>'
        const projectSelect = document.getElementById("project_id");
        $("#project_id").prop('disabled',false);
        $.ajax({
                type: "POST",
                url: "{{url('/find_project')}}",
                data: {customer_id:customer_id,_token:token},
                success: function(data) {
                    // console.log(data);
                    const projectArr=data.project;
                    projectArr.forEach((project) => {
                        const option = document.createElement("option");
                        option.value = project.id;
                        option.text = `${project.project_name}`;
                        projectSelect.appendChild(option);
                    });
                    
                    if (project_id != null) {
                        $('#project_id').val(project_id);
                    }
                }
            });
    }
    // function find_job(project_id){
    //     var job_input=$("#job").val();
    //     if(job_input.length>4){
    //         var token='<?php echo csrf_token();?>'
            
    //         $.ajax({
    //                 type: "POST",
    //                 url: "{{url('/find_job')}}",
    //                 data: {job_input:job_input,_token:token},
    //                 success: function(data) {
    //                     console.log(data);
    //                     $('#jobList').empty();
    //                     if (data.job_appoint.length > 0) {
    //                         $.each(data.job_appoint, function(index, job) {
    //                             $('#jobList').append('<option value="' + job.job_ref + '">'+ job.site_address +'</option>');
    //                         });
    //                     } else {
    //                         $('#jobList').append('<option value="">No job found</option>');
    //                     }
                        
    //                 }
    //             });
    //     }
        
    // }
    $("#save_data").on('click', function(){
        if ($('#authorised1').is(':checked')) {
            $("#authorised").val(1);
        }else {
            $("#authorised").val(0);
        }
        if ($('#billabl1').is(':checked')) {
            $("#billable").val(1);
        }else {
            $("#billable").val(0);
        }
        if ($('#paid1').is(':checked')) {
            $("#paid").val(1);
        }else {
            $("#paid").val(0);
        }
        var title=$("#title").val();
        var amount=$("#amount").val();
        var vat=$("#vat").val();
        var vat_amount=$("#vat_amount").val();
        var gross_amount=$("#gross_amount").val();
        var expense_date=$("#expense_date").val();
        
        if(title == ''){
            $("#title").css('border','1px solid red');
            return false;
        }else if(amount == ''){
            $("#title").css('border','');
            $("#amount").css('border','1px solid red');
            return false;
        }else if(vat == ''){
            $("#amount").css('border','');
            $("#vat").css('border','1px solid red');
            return false;
        }else if(vat_amount == ''){
            $("#amount").css('border','');
            $("#vat_amount").val('0.00');
        }else if(gross_amount == ''){
            $("#amount").css('border','');
            $("#vat").css('border','');
            $("#gross_amount").css('border','1px solid red');
            return false; 
        }else if(expense_date == ''){
            $("#amount").css('border','');
            $("#gross_amount").css('border','');
            $("#expense_date").css('border','1px solid red');
            return false; 
        }else {
            $.ajax({
            type: "POST",
            url: "{{url('expense_save')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
                success: function(data) {
                    // console.log(data);
                    if(data.vali_error){
                        alert(data.vali_error);
                        $("#email").css('border','1px solid red');
                        $(window).scrollTop($('#email').position().top);
                        return false;
                    }else{
                        $(window).scrollTop(0);
                        $("#email").css('border','');
                        $('.alert').show();
                        setTimeout(function() {
                            $('.alert').hide();
                            location.reload();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                   var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    });
</script>
<script>
   let job_input = document.getElementById('job');
let autoComplete = document.getElementById('jobList');
let hiddenJobInput = document.getElementById('selectedJobRef'); 

job_input.addEventListener('input', function() {
    let job_val = job_input.value;

    if (job_val.length > 4) {
        var token = '<?php echo csrf_token();?>';
        
        $.ajax({
            type: "POST",
            url: "{{url('/find_job')}}",
            data: { job_input: job_val, _token: token },
            success: function(data) {
                $('#jobList').empty(); 

                if (data.job_appoint.length > 0) {
                    data.job_appoint.forEach((job) => {
                        let item = document.createElement('a');
                        item.classList.add('item');
                        item.setAttribute('href', '#');
                        item.dataset.value = job.job_ref;
                        item.innerHTML = `${job.job_ref} - ${job.site_address}`;

                        
                        item.addEventListener('click', function(event) {
                            event.preventDefault();
                            job_input.value = `${job.job_ref}`;   
                            hiddenJobInput.value = job.job_ref; 

                            // console.log(hiddenJobInput.value); 
                            $('#jobList').empty();            
                            job_input.focus();
                            find_appointment(hiddenJobInput.value);
                        });

                        autoComplete.appendChild(item);
                    });
                } else {
                    let noJobItem = document.createElement('a');
                    noJobItem.classList.add('item');
                    noJobItem.setAttribute('href', '#');
                    noJobItem.innerHTML = "No job found";
                    autoComplete.appendChild(noJobItem);
                }
            }
        });
    }
});

</script>
<script>
    function find_appointment(selectedJobRef){
        var id=selectedJobRef.split('JOB-');
        // console.log(id);
        if(id.length>1){
            var job_id=id[1];
            var token='<?php echo csrf_token();?>'
            $.ajax({
                type: "POST",
                url: "{{url('/find_appointment')}}",
                data: {job_id:job_id,_token:token},
                success: function(data) {
                    // console.log(data);
                    if(data.length>0){
                        $('#job_appointment_id').prop('disabled',false);
                        var selectHTML = '';
                        $.each(data, function(index, appointment) {
                            selectHTML += '<option value="' + appointment.appointment_id + '">' + appointment.user_name + ':'+ appointment.start_date +' '+appointment.start_time+'-'+appointment.end_date+' '+appointment.end_time+','+appointment.status+ '</option>';
                        });

                        selectHTML += '</select>';
                        document.getElementById('job_appointment_id').innerHTML = selectHTML;
                        
                        // $('#job_appointment_id').select2();
                        // document.getElementById('job_appointment_id').select2();
                    }else{
                        $('#job_appointment_id').prop('disabled',true);
                    }
                },
                error: function(xhr, status, error) {
                   var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
</script>
<script>
    // Calls model open and close 
    const openCallsModel = document.getElementById('openCallsModel');
        const callsModel = document.getElementById('callsModal');
        var countries = document.getElementById("countries");
        openCallsModel.onclick = function() {
            getCountriesList(countries);
            getCRMTypeData();
            $('#callsModal').modal('show');
        }
        const openModalBtn = document.getElementById('openCrmTypeModel');
        const crmTypeModel = document.getElementById('crmTypeModel');
        openModalBtn.onclick = function() {
            $('#crmTypeModel').modal('show');
        }

        // Email model open and close
        const openNewEmail = document.getElementById('openNewEmail');
        const NewEmailModel = document.getElementById('NewEmailModel');
        openNewEmail.onclick = function() {
            $('#NewEmailModel').modal('show');
        }

        const mainCheckbox = document.getElementById('yeson');
        const optionsDiv = document.getElementById('optionsDiv');
        // Open the second modal without hiding the first one
        $('#openSecondModal').on('click', function() {
            optionsDiv.style.display = 'none';
            $('#secondModal').modal('show');
        });
        $('#openThirdModal').on('click', function() {
            $('#task_type_modal').modal('show');
        });
        $('#openThirdModal2').on('click', function() {
            $('#task_type_modal').modal('show');
        });
        $('.search_contacts').on('click', function(){
            GetCustomerWithContact();
            $("#search_contactsModal").modal('show');
        })

        mainCheckbox.addEventListener('change', function() {
            if (mainCheckbox.checked) {
                optionsDiv.style.display = 'block';
            } else {
                optionsDiv.style.display = 'none';
            }
        });

        // Notes model show and hide
        const openNotesModel = document.getElementById('openNotesModel');
        const NewNotesModel = document.getElementById('NewNotesModel');
        openNotesModel.onclick = function() {
            getCRMTypeData();
            $('#NewNotesModel').modal('show');
        }
        const openComplaintsModel = document.getElementById('openComplaintsModel');
        const compliantsModal = document.getElementById('compliantsModal');
        openComplaintsModel.onclick = function() {
            getCRMTypeData();
            $('#compliantsModal').modal('show');
        }
         // CRM Section Type ADD model in Complaints Js Start for model  show
         const openCrmTypeModelNotes = document.getElementById('openCrmTypeModelNotes');
        const crmTypeModelNotes = document.getElementById('crmTypeModelNotes');
        openCrmTypeModelNotes.onclick = function() {
            $('#crmTypeModelNotes').modal('show');
        }
        const openCrmTypeModelComplaints = document.getElementById('openCrmTypeModelComplaints');
        const crmTypeModelComplaint = document.getElementById('crmTypeModelComplaint');
        openCrmTypeModelComplaints.onclick = function() {
            $('#crmTypeModelComplaint').modal('show');
        }

        $(".job_title_modal").on('click', function(){
            $("#job_title_form")[0].reset();
            $("#job_modaltitle").modal('show');
        });
        $(".contact_add").on('click', function(){
            $('#contact_form')[0].reset();
            $("#contact_address").val('');
            $('#contact_modal').modal('show');
        });
        function get_contactModal(userType){
            $('#contact_form')[0].reset();
            $("#userType").val(userType);
            var url='';
            var CustomerLabel='';
            if(userType == 1){
                CustomerLabel='Customer <span class="radStar">*</span>';
                url='{{ url("getAllCustomerList") }}';
            }else if(userType == 2){    
                CustomerLabel='Supplier <span class="radStar">*</span>';
                url='{{ url("getAllSupplierList") }}';
            }else{
                CustomerLabel='User <span class="radStar">*</span>';
                url='{{ url("getAllUserList") }}';
            }
            $('.CustomerLabel').html(CustomerLabel);
            var token='<?php echo csrf_token();?>'
            $.ajax({
                    url: url,
                    method: 'POST',
                    data: {_token:token},
                    success: function(data) {
                        // console.log(data);
                         if(data.success){
                            var data = data.data;
                            var selectBody = $("#contact_customer_id"); 
                            selectBody.empty();
                            var contact_html='<option selected disabled>Please Select</option>';
                            if(userType == 1){
                                data.forEach(function(item) {
                                    contact_html+='<option value="'+ item.id +'">'+ item.name +'</option>';
                                });
                            }else if(userType == 2){    
                               data.forEach(function(item) {
                                contact_html+='<option value="'+ item.id +'">'+ item.name +'</option>';
                                });
                            }else{
                                data.forEach(function(item) {
                                    contact_html+='<option value="'+ item.id +'">'+ item.name +'</option>';
                                });
                            }
                            selectBody.html(contact_html);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            $("#contact_address").val('');
            $('#contact_modal').modal('show');
        }
        
        function getCRMTypeData() {
        $.ajax({
            url: '{{ route("lead.ajax.getCRMTypeData") }}',
            method: 'GET',
            success: function(response) {
                // console.log(response.Data);
                const selectElement = document.getElementById('calls_type');
                const lead_notes_crm = document.getElementById('lead_notes_crm');
                const lead_complaint_crm = document.getElementById('lead_complaint_crm');

                selectElement.innerHTML = '';
                lead_notes_crm.innerHTML = '';
                lead_complaint_crm.innerHTML = '';

                response.Data.forEach(index => {
                    const option = document.createElement('option');
                    option.value = index.id;
                    option.text = index.title;
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
    // on CRM model open set the value for models

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
            document.getElementById('saveCRMCallsModelData').addEventListener('click', function() {
                var calls_type=$("#calls_type").val();
                var editorData=document.getElementById('calls_notes').value = editor.getData();
                if(calls_type == '' || calls_type == null){
                    $('#calls_type').css('border','1px solid red');
                    return false;
                }else if(editorData == ''){
                    $('#calls_type').css('border','');
                    $('.ck.ck-reset.ck-editor.ck-rounded-corners').css('border','1px solid red');
                    return false;
                }
                var formData = $('#CRM_calls_form').serialize();

                $.ajax({
                    url: '{{ url("save_crm_customer_call") }}',
                    method: 'POST',
                    data: formData,
                    success: function(data) {
                        // console.log(data);
                        if(data.vali_error){
                        alert(data.vali_error);
                        $("#user_notifiy").css('border','1px solid red');
                        $('#user_notifiy').focus();
                        return false;
                    }else if(data.success){
                            const responseData = data.data[0]; 
                            get_all_crm_customer_call(responseData.customer_id,pageUrl = '{{ url("get_all_crm_customer_call") }}');
                            $('#callsModal').modal('hide');
                            full_history()
                        } else {
                            alert("Something went wrong");
                        }
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
                // console.log(document.getElementById('emailMessage').value);
                var formData = new FormData(document.getElementById('crm_customer_email_form'));
                $.ajax({
                    url: '{{ url("save_crm_customer_email") }}',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        // console.log(data);
                        if(data.vali_error){
                        alert(data.vali_error);
                        $("#user_notifiy").css('border','1px solid red');
                        $('#user_notifiy').focus();
                        return false;
                    }else if(data.success){
                            const responseData = data.data; 
                            get_all_crm_customer_email(responseData.customer_id,pageUrl = '{{ url("get_all_crm_customer_email") }}');
                            $('#NewEmailModel').modal('hide');
                        } else {
                            alert("Something went wrong");
                        }
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
                // console.log(document.getElementById('CRMNotes').value);
                // var formData = new FormData(document.getElementById('crm_lead_notes_form'));
                var formData = $('#crm_lead_notes_form').serialize();
                // console.log(formData);
                $.ajax({
                    url: '{{ url("save_crm_customer_notes") }}',
                    method: 'POST',
                    data: formData,
                    // processData: false,  
                    // contentType: false,  
                    success: function(response) {
                        // console.log(response);
                        if(response.vali_error){
                        alert(response.vali_error);
                        $("#user_notifiy").css('border','1px solid red');
                        $('#user_notifiy').focus();
                        return false;
                    }else if(response.success){
                            const responseData = response.data; 
                            get_all_crm_customer_note(responseData.customer_id,pageUrl = '{{ url("get_all_crm_customer_note") }}');
                            $('#NewNotesModel').modal('hide');
                        } else {
                            alert("Something went wrong");
                        }
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
                // console.log(document.getElementById('CRMComplaint').value);
                var formData = $('#crm_lead_complaint_form').serialize();
                // console.log(formData);
                $.ajax({
                    url: '{{ url("save_crm_customer_complaints") }}',
                    method: 'POST',
                    data: formData,
                    // processData: false,  
                    // contentType: false,  
                    success: function(response) {
                        // console.log(response);
                        
                        if(response.vali_error){
                            alert(response.vali_error);
                            return false;
                        }else if(response.success){
                            const responseData = response.data; 
                            get_all_crm_customer_complaint(responseData.customer_id,pageUrl = '{{ url("get_all_crm_customer_complaint") }}');
                            $("#notification_complaint_div").hide();
                            $('#compliantsModal').modal('hide');
                            $('#notify_complaint1').prop('checked',true);

                            editor.setData('');
                        } else {
                            alert("Something went wrong");
                        }
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

        $('#saveCRMLeadTaskWithTimer').on('click', function() {
            const formTypeInput = document.getElementById('form_type');
            if (document.getElementById('nav-home').classList.contains('active')) {
                formTypeInput.value = 'task_form';
            } else if (document.getElementById('nav-profile').classList.contains('active')) {
                formTypeInput.value = 'timer_form';
            }

            var formData = $('#crm_lead_task_form').serialize();
            $.ajax({
                url: '{{ url("save_crm_customer_task") }}',
                method: 'POST',
                data: formData,
                success: function(data) {
                    // console.log(data);
                    if (data.vali_error) {
                        alert(data.vali_error);
                        return false;
                    } else {
                        const responseData = data.data;
                        get_all_crm_customer_task(responseData.customer_id,pageUrl = '{{ url("get_all_crm_customer_task") }}');
                        $('#secondModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });
        $('#save_task_type_data').on('click', function(){
            var token='<?php echo csrf_token();?>'
            var title = $("#name").val();
            var status = $.trim($('#statusModal option:selected').val());
            var home_id = '<?php echo Auth::user()->home_id;?>';
            if(title == ''){
                $("#name").css('border','1px solid red');
                return false;
            }else{
                $("#name").css('border','');
                $.ajax({
                    type: "POST",
                    url: '{{ url("/save_task_type") }}',
                    data: {home_id: home_id, title: title, status: status, _token: token},
                    success: function(data) {
                        // console.log(data);
                        if(data.vali_error){
                            alert(data.vali_error);
                            return false;
                        }else if(data.data && data.data.original && data.data.original.error){
                            alert(data.data.original.error);
                            return false;
                        }else{
                            $("#task_type_modal").modal('hide');
                            var html='<option value="'+data.data.id+'">'+data.data.title+'</option>';
                            $("#lead_task_types").append(html);
                            
                        }
                    } 
                });
            }
        });
        // Ajax Call for saving CRM section Type for notes
        $('#saveCRMTypesNotes').on('click', function() {
            var title = $('#type_title_notes').val();
            var colourCode = $('#colour_code_notes').val();
            var crmSection = $('#crm_section_notes').val();
            var token='<?php echo csrf_token();?>'
            var formData = {
                title: title,
                colour_code: colourCode,
                crm_section: crmSection,
                _token:token
            };
            // console.log(formData);
            addCRMTypes(formData, 3);
        });
        $('#saveCRMTypesComplaint').on('click', function() {
            var title = $('#type_title_complaint').val();
            var colourCode = $('#colour_code_complaint').val();
            var crmSection = $('#crm_section_complaint').val();
            var token='<?php echo csrf_token();?>'
            var formData = {
                title: title,
                colour_code: colourCode,
                crm_section: crmSection,
                _token:token
            };
            // console.log(formData);
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
        function addCRMTypes(formData, type) {
            // alert(type)
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

</script>

<script>
    function get_all_crm_customer_call(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,_token:token
            },
            success: function(response) {
                // console.log(response.data);
                var data = response.data;
                var paginationCall = response.pagination;
                var tableBody = $("#customer_crmData"); 
                tableBody.empty();
                
                data.forEach(function(item) {
                    
                    var date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    
              
                    var visibilityCell = '';
                    if (item.customer_visibility === 0) {
                        visibilityCell = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
                    } else if (item.customer_visibility === 1) {
                        visibilityCell = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    var html = '<tr>' +
                        '<td>' + date + '</td>' +                       
                        '<td>' + '<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>' + '</td>' + 
                        '<td>' + (item.telephone || '-') + '</td>' +      
                        '<td>' + (item.type || '-') + '</td>' +         
                        '<td>' + (item.notes || '-') + '</td>' +          
                        '<td>' + visibilityCell + '</td>' +              
                        '<td>' +         
                            '<i class="fa fa-phone"></i> ' +
                            '<i class="fa fa-envelope"></i> ' +
                            '<i class="fa fa-list-ul"></i> ' +
                            '<i class="fa fa-file"></i> ' +
                            '<i class="fa fa-exclamation-triangle"></i>' +
                        '</td>' +
                    '</tr>';

                    // Append each row to the table body
                    // 
                    // full_history();
                    tableBody.append(html);
                });
                var paginationControlsClll = $("#pagination-controls-call");
                paginationControlsClll.empty();
                if (paginationCall.prev_page_url) {
                    paginationControlsClll.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_call(' + id + ', \'' + paginationCall.prev_page_url + '\')">Previous</button>');
                }
                if (paginationCall.next_page_url) {
                    paginationControlsClll.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_call(' + id + ', \'' + paginationCall.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_all_crm_customer_email(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                var data = response.data;
                var paginationEmail = response.pagination;
                var tableBody = $("#crm_customer_email"); 
                tableBody.empty();
                
                data.forEach(function(item) {
                    
                    var date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    
              
                    var visibilityCell = '';
                    if (item.customer_visibility === 0) {
                        visibilityCell = '<span class="grayCheck" onclick="customer_visibility('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    } else if (item.customer_visibility === 1) {
                        visibilityCell = '<span class="grencheck" onclick="customer_visibility('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    var html = '<tr>' +
                        '<td>' + date + '</td>' +                       
                        '<td>' + '<?php echo Auth::user()->name . "<br>" . Auth::user()->email; ?>' + '</td>' + 
                        '<td>' + item.name + '<br>Email:'+item.customer_email + '</td>' +      
                        '<td>' + (item.type || '-') + '</td>' +          
                        '<td>Eamil sent to ' + item.send_email + ' from '+ item.name +'</td>' +         
                        '<td id="visible_check'+item.id+'">' + visibilityCell + '</td>' +              
                        '<td>' +         
                            '<i class="fa fa-phone"></i> ' +
                            '<i class="fa fa-envelope"></i> ' +
                            '<i class="fa fa-list-ul"></i> ' +
                            '<i class="fa fa-file"></i> ' +
                            '<i class="fa fa-exclamation-triangle"></i>Sent' +
                        '</td>' +
                    '</tr>';

                    // Append each row to the table body
                    tableBody.append(html);
                });
                var paginationControlsEmail = $("#pagination-controls-email");
                paginationControlsEmail.empty();
                if (paginationEmail.prev_page_url) {
                    paginationControlsEmail.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_email(' + id + ', \'' + paginationEmail.prev_page_url + '\')">Previous</button>');
                }
                if (paginationEmail.next_page_url) {
                    paginationControlsEmail.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_email(' + id + ', \'' + paginationEmail.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_all_crm_customer_task(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                var data = response.data;
                var tableBody = $("#crm_customer_task"); 
                var paginationTask = response.pagination;
                tableBody.empty();
                
                data.forEach(function(item) {
                    const date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    const start_date = moment(item.start_date).format('DD/MM/YYYY HH:mm');
                    var html = '<tr data-task-id="' + item.id + '">' +
                                    '<td> </td>' +
                                    '<td>' + start_date + '</td>' +
                                    '<td><?php echo Auth::user()->name; ?></td>' +
                                    '<td>' + item.customer_name + '</td>' +  
                                    '<td>' + item.type + '</td>' +        
                                    '<td>' + item.title + '</td>' +
                                    '<td>' + item.notes + '</td>' +
                                    '<td>' + item.customer_name + '</td>' +
                                    '<td>' + date + '</td>' +
                                    '<td><?php echo Auth::user()->name; ?></td>' +
                                    '<td>' +
                                        '<img src="<?php echo url('public/frontEnd/jobs/images/pencil.png');?>" height="16px" alt="" class="modal_data_crm_task image_style" ' +
                                        'data-id="'+item.id+'" data-title="'+item.title+'" data-user_id="'+item.user_id+'" data-task_type_id="'+item.task_type_id+'" data-start_date="'+item.start_date+'" ' +
                                        'data-start_time="'+item.start_time+'" data-end_date="'+item.end_date+'" data-end_time="'+item.end_time+'" data-is_recurring="'+item.is_recurring+'" data-notify="'+item.notify+'" ' +
                                        'data-notification="'+item.notification+'" data-email="'+item.email+'" data-sms="'+item.sms+'" data-notify_date="'+item.notify_date+'" data-notify_time="'+item.notify_time+'" data-notes="'+item.notes+'">&emsp;' +
                                        '<img src="<?php echo url('public/frontEnd/jobs/images/delete.png');?>" alt="" class="crm_task_delete image_style" data-delete="'+item.id+'">' +
                                    '</td>' +
                                '</tr>';
                    tableBody.append(html);
                });
                var paginationControlsTask = $("#pagination-controls-task");
                paginationControlsTask.empty();
                if (paginationTask.prev_page_url) {
                    paginationControlsTask.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_task(' + id + ', \'' + paginationTask.prev_page_url + '\')">Previous</button>');
                }
                if (paginationTask.next_page_url) {
                    paginationControlsTask.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_task(' + id + ', \'' + paginationTask.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_all_crm_customer_note(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                var data = response.data;
                var paginationNotes = response.pagination;
                var tableBody = $("#crm_customer_note"); 
                tableBody.empty();
                
                data.forEach(function(item) {
                    
                    var date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    
              
                    var visibilityCell = '';
                    if (item.customer_visibility === 0) {
                        visibilityCell = '<span class="grayCheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    } else if (item.customer_visibility === 1) {
                        visibilityCell = '<span class="grencheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    var contact;
                    if(item.contact == ''){
                        contact=item.customer_name;
                    }else{
                        contact=item.contact;
                    }

                    var html = '<tr>' +
                        '<td>' + date + '</td>' +
                        '<td><?php echo Auth::user()->name . "<br>(" . Auth::user()->email .")"; ?></td>' +
                        '<td>' + contact + '</td>' +  
                        '<td>'+ item.type +'</td>' +        
                        '<td>' + item.notes + '</td>' +
                        '<td id="visible_note'+item.id+'">' + visibilityCell + '</td>' +
                        '<td><i class="fa fa-phone"></i> ' +
                        '<i class="fa fa-envelope"></i> ' +
                        '<i class="fa fa-list-ul"></i> ' +
                        '<i class="fa fa-file"></i> ' +
                        '<i class="fa fa-exclamation-triangle"></i>Sent</td>' +
                        '</tr>';

                    // Append each row to the table body
                    tableBody.append(html);
                });
                var paginationControlsNotes = $("#pagination-controls-note");
                paginationControlsNotes.empty();
                if (paginationNotes.prev_page_url) {
                    paginationControlsNotes.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_note(' + id + ', \'' + paginationNotes.prev_page_url + '\')">Previous</button>');
                }
                if (paginationNotes.next_page_url) {
                    paginationControlsNotes.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_note(' + id + ', \'' + paginationNotes.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_all_crm_customer_complaint(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                var data = response.data;
                var pagination = response.pagination;
                var tableBody = $("#crm_customer_complaint"); 
                tableBody.empty();
                
                data.forEach(function(item) {
                    var date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    
                    var visibilityCell = '';
                    if (item.customer_visibility === 0) {
                        visibilityCell = '<span class="grayCheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    } else if (item.customer_visibility === 1) {
                        visibilityCell = '<span class="grencheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    
                    var contact = item.contact === '' ? item.customer_name : item.customer_name + '<br><b>Contact:</b>' + item.contact;
                    
                    let notify = '';
                    if (item.notify == 1) {
                        notify += '<br><b>Notify:</b> <?php echo Auth::user()->name; ?><br><b>Send As:</b> ' 
                                + (item.sms == 1 ? "SMS," : "") 
                                + (item.notification == 1 ? " Notification," : "") 
                                + (item.email == 1 ? " Email" : "");
                    }

                    var html = '<tr>' +
                        '<td>' + date + '</td>' +
                        '<td><?php echo Auth::user()->name . "<br>(" . Auth::user()->email .")"; ?></td>' +
                        '<td>' + contact + '</td>' +  
                        '<td>' + item.type + '</td>' +        
                        '<td></td>' +        
                        '<td>' + item.notes + notify + '</td>' +
                        '<td id="visible_complaint' + item.id + '">' + visibilityCell + '</td>' +
                        '<td><i class="fa fa-phone"></i> ' +
                        '<i class="fa fa-envelope"></i> ' +
                        '<i class="fa fa-list-ul"></i> ' +
                        '<i class="fa fa-file"></i> ' +
                        '<i class="fa fa-exclamation-triangle"></i>Sent</td>' +
                        '</tr>';
                    
                    tableBody.append(html);
                });

                var paginationControls = $("#pagination-controls");
                paginationControls.empty();
                if (pagination.prev_page_url) {
                    paginationControls.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_complaint(' + id + ', \'' + pagination.prev_page_url + '\')">Previous</button>');
                }
                if (pagination.next_page_url) {
                    paginationControls.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_complaint(' + id + ', \'' + pagination.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_all_crm_customer_contacts(id,pageUrl){
        var token='<?php echo csrf_token();?>'
        var userTyep=1;
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                userTyep:userTyep,id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                var data = response.data;
                var paginationContact = response.pagination;
                var tableBody = $("#crm_customer_contact"); 
                tableBody.empty();
                
                data.forEach(function(item) {
                    
                    var date = moment(item.created_at).format('DD/MM/YYYY HH:mm');
                    
              
                    var visibilityCell = '';
                    if (item.customer_visibility === 0) {
                        visibilityCell = '<span class="grayCheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    } else if (item.customer_visibility === 1) {
                        visibilityCell = '<span class="grencheck" onclick="customer_visibility1('+item.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    var contact;
                    if(item.contact == ''){
                        contact=item.customer_name;
                    }else{
                        contact=item.contact;
                    }

                    var html = '<tr>' +
                        '<td>' + date + '</td>' +
                        '<td><?php echo Auth::user()->name . "<br>(" . Auth::user()->email .")"; ?></td>' +
                        '<td>' + contact + '</td>' +       
                        '<td></td>' +        
                        '<td></td>' +
                        '<td>' + visibilityCell + '</td>' +
                        '<td><i class="fa fa-phone"></i> ' +
                        '<i class="fa fa-envelope"></i> ' +
                        '<i class="fa fa-list-ul"></i> ' +
                        '<i class="fa fa-file"></i> ' +
                        '<i class="fa fa-exclamation-triangle"></i></td>' +
                        '</tr>';
                    tableBody.append(html);
                });
                var paginationControlsContact = $("#pagination-controls-contact");
                paginationControlsContact.empty();
                if (paginationContact.prev_page_url) {
                    paginationControlsContact.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_contacts(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                }
                if (paginationContact.next_page_url) {
                    paginationControlsContact.append('<button type="button" class="profileDrop" onclick="get_all_crm_customer_contacts(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>
<script>
    function customer_visibility(id){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            type: "POST",
            url: "{{url('/visibility_change')}}",
            data: {id:id,_token:token},
            success: function(data) {
                // console.log(data);
                // visible_check1
                var visibilityCell = '';
                if(data.data.customer_visibility == 0){
                    visibilityCell = '<span class="grayCheck" onclick="customer_visibility('+data.data.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                } else if (data.data.customer_visibility === 1) {
                    visibilityCell = '<span class="grencheck" onclick="customer_visibility('+data.data.id+')"><i class="fa-solid fa-circle-check"></i></span>';
                }
                $("#visible_check"+data.data.id).html(visibilityCell);
                
            }
        });
    }
</script>
<script>
    $(document).ready(function () {
    document.getElementById('weekly').style.display = 'none';
    document.getElementById('monthly').style.display = 'none';
    document.getElementById('task_end_date').style.display = 'none';

    $('#isRecurring').on('change', function () {
        if ($(this).is(':checked')) {
            $('#recurrence_div').show();
        } else {
            $('#recurrence_div').hide();
        }
    });

    $('input[name="task_end_repe_date"]').on('change', function () {

        // Hide both divs initially
        $('#repetitation').hide();
        $('#task_end_date').hide();

        var value = $(this).val();
        // Show the appropriate div based on the selected radio button
        if (value === '1') {
            $('#repetitation').show();
        } else if (value === '2') {
            $('#task_end_date').show();
        }
    });

    document.getElementById('task_frequency').addEventListener('change', function () {
        document.getElementById('daily').style.display = 'none';
        document.getElementById('weekly').style.display = 'none';
        document.getElementById('monthly').style.display = 'none';

        var selectedValue = this.value;
        // Show the appropriate div based on the selected option
        if (selectedValue == 1) {
            document.getElementById('daily').style.display = 'block';
        } else if (selectedValue == 2) {
            document.getElementById('weekly').style.display = 'block';
        } else if (selectedValue == 3) {
            document.getElementById('monthly').style.display = 'block';
        }
    });

     // start here js for time start and pause
        let timerInterval;
        let elapsedSeconds = 0;
        let isRunning = false;

        function toggleTimer() {
            var staticEmail=$("input[name=title_timer]").val();
            if(staticEmail == ''){
                alert("Please fill all required field");
                return false;
            }else{
                if (isRunning) {
                    // Pause the timer
                    clearInterval(timerInterval);
                    document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-play"></i> Start';
                } else {
                    // Start the timer with an interval of 100ms for faster updates
                    timerInterval = setInterval(function() {
                        elapsedSeconds++;
                        document.getElementById('timerDisplay').textContent = formatTime(elapsedSeconds);
                        document.getElementById('start_time').value = formatTime(elapsedSeconds);
                    }, 100); // Now the timer updates every 100 milliseconds
                    document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-stop"></i> Pause';
                }
                isRunning = !isRunning; // Toggle the running state
            }
            
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


});
</script>
<script>
    $(document).on('click', '.modal_data_crm_task', function() {
        $("#secondModal").modal('show');
        var taskId = $(this).data('id');
        var title = $(this).data('title');
        var userId = $(this).data('user_id');
        var taskTypeId = $(this).data('task_type_id');
        var startDate = $(this).data('start_date');
        var startTime = $(this).data('start_time');
        var endDate = $(this).data('end_date');
        var endTime = $(this).data('end_time');
        var isRecurring = $(this).data('is_recurring');
        var notify = $(this).data('notify');
        var notification = $(this).data('notification');
        var email = $(this).data('email');
        var sms = $(this).data('sms');
        var notifyDate = $(this).data('notify_date');
        var notifyTime = $(this).data('notify_time');
        var notes = $(this).data('notes');
        
        $('#task_id').val(taskId); 
        $('#getUserList').val(userId);
        $('#taskTitle').val(title);
        $('#lead_task_types').val(taskTypeId);
        $('#TaskStartDate').val(startDate);
        $('#TaskStartTime').val(startTime);
        $('#TaskEndDate').val(endDate);
        $('#TaskEndTime').val(endTime);
        $('#lead_task_types').val(taskTypeId);
        $('#lead_task_types').val(taskTypeId);
        $('#lead_task_types').val(taskTypeId);
        $('#notify_date').val(notifyDate);
        $('#notify_time').val(notifyTime);
        $('#TaskNotesText').val(notes);
        if(isRecurring == 1){
            $('#isRecurring').prop('checked',true);
        }else{
            $('#isRecurring').prop('checked',false);
        }
        if(notify == 1){
            $('#yeson').prop('checked',true);
        }else{
            $('#yeson').prop('checked',false);
        }
        
    });
</script>
<script>
    function save_job_title(){
        var token='<?php echo csrf_token();?>'
        var name=$("#job_title_name").val();
        var status=$("#job_title_status").val();
        var home_id='<?php echo Auth::user()->home_id; ?>'
        if(name == ''){
        $("#job_title_name").css('border','1px solid red');
        return false;
        }else{
            $("#job_title_name").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/save_job_title')}}",
                data: {name:name,status:status,home_id:home_id,_token:token},
                success: function(data) {
                    // console.log(data);
                    
                    $("#job_modaltitle").modal('hide');
                    $('.get_job_title_result').append(data);
                    // window.location.reload();
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
    
    function save_contact(){
        var token='<?php echo csrf_token();?>'
        var default_billing; 
        if ($('#contact_default_yes').is(':checked')) {
            default_billing=1;
        }else {
            default_billing=0;
        }
        var customer_id=$("#contact_customer_id").val();
        var contact_name=$("#contact_contact_name").val();
        var job_title_id=$("#contact_job_titile_id").val();
        var email=$("#contact_email").val();
        var telephone=$("#contact_phone").val();
        var mobile=$("#contact_mobile").val();
        var fax=$("#contact_fax").val();
        var address=$("#contact_address").val();
        var city=$("#contact_city").val();
        var country=$("#contact_country_input").val();
        var postcode=$("#contact_pincode").val();
        var country_id=$("#contact_country_id").val();
        var userType=$("#userType").val();
        if(customer_id == '' || customer_id == null){
            $("#contact_customer_id").css('border','1px solid red');
            return false;
        }else if(contact_name == ''){
            $("#contact_customer_id").css('border','');
            $('#contact_contact_name').css('border','1px solid red');
            return false;
        }else if(address == ''){
            $('#contact_contact_name').css('border','');
            $("#contact_address").css('border','1px solid red');
            return false;
        }else {
            $("#contact_customer_id").css('border','');
            $("#contact_contact_name").css('border','');
            $("#contact_address").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/contact_save')}}",
                data: {userType:userType,country_id:country_id,postcode:postcode,country:country,city:city,address:address,fax:fax,mobile:mobile,telephone:telephone,email:email,job_title_id:job_title_id,contact_name:contact_name,customer_id:customer_id,default_billing:default_billing,_token:token},
                success: function(data) {
                    // console.log(data);
                    $("#contact_modal").modal('hide');
                    $("#notes_contact").append(data);
                    $("#comaplint_contact").append(data);
                    get_all_crm_customer_contacts(customer_id,pageUrl = '{{ url("get_all_crm_customer_contacts") }}');   
                    GetCustomerWithContact();
                }
            });
        }
    }
    function default_address(){
        var check;
        if ($('#contact_default_address').is(':checked')) {
            check=1;
        }else {
            check=0;
        }
        var token='<?php echo csrf_token();?>'
        var customer_id=$("#contact_customer_id").val();
        if(customer_id == null || customer_id == ''){
            alert("please select Customer");
            $('#contact_default_address').prop('checked',false);
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{{url('/default_address')}}",
            data: {check:check,customer_id:customer_id,_token:token},
            success: function(data) {
                // console.log(data);
                if(check == 1){
                    $("#contact_address").val(data.details.address);
                    $("#contact_city").val(data.details.city);
                    $("#contact_country_input").val(data.details.country);
                    $("#contact_pincode").val(data.details.postal_code);
                }else{
                    $("#contact_address").val('');
                    $("#contact_city").val('');
                    $("#contact_country_input").val('');
                    $("#contact_pincode").val('');
                }
                $("#contact_country_code").html(data.reslut);
            }
        });
    }
    function GetCustomerWithContact(){
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('GetCustomerWithContact')}}",
            data: {
                _token: token
            },
            success: function(response) {
                console.log(response);
                // return false;
                if (response.data.length > 0) {
                var ContactData=response.data;
                // console.log(customerData);return false;
                var customer_contact = '';
                var supplier_contact = '';
                var user_contact = '';
                for (let i = 0; i < ContactData.length; i++) {
                    var customerData = ContactData[i].customers;
                    var user=ContactData[i].user;
                    if(ContactData[i].userType == 1){
                        customer_contact += '<tr onclick="select_row(this, ' + ContactData[i].id + ', \'' + ContactData[i].contact_name + '\')" class="contact-row"><td>CUSTOMER</td><td>' + (customerData.name ?? '') + '</td><td>' + (customerData.contact_name ?? '') + '</td><td>' + (ContactData[i].contact_name ?? "") + '</td><td>' + (ContactData[i].email ?? "") + '</td><td>' + (ContactData[i].telephone ?? "") + '</td><td>' + (ContactData[i].mobile ?? "") + '</td></tr>';
                    }else if(ContactData[i].userType == 2){
                        supplier_contact += '<tr onclick="select_row(this, ' + ContactData[i].id + ', \'' + ContactData[i].contact_name + '\')" class="contact-row"><td>Supplier</td><td>' + (customerData.name ?? '') + '</td><td>' + (customerData.contact_name ?? '') + '</td><td>' + (ContactData[i].contact_name ?? "") + '</td><td>' + (ContactData[i].email ?? "") + '</td><td>' + (ContactData[i].telephone ?? "") + '</td><td>' + (ContactData[i].mobile ?? "") + '</td></tr>';
                    }else if(ContactData[i].userType == 3){
                        user_contact += '<tr onclick="select_row(this, ' + ContactData[i].id + ', \'' + ContactData[i].contact_name + '\')" class="contact-row"><td>User</td><td>' + (user.name ?? '') + '</td><td>' + (user.contact_name ?? '') + '</td><td>' + (ContactData[i].contact_name ?? "") + '</td><td>' + (ContactData[i].email ?? "") + '</td><td>' + (ContactData[i].telephone ?? "") + '</td><td>' + (ContactData[i].mobile ?? "") + '</td></tr>';
                    }else{
                        alert("Something is wrong");
                        return false;
                    }
                    
                }
                document.getElementById('customer_contact_list').innerHTML = customer_contact;
                document.getElementById('supplier_contact_list').innerHTML = supplier_contact;
                document.getElementById('user_contact_list').innerHTML = user_contact;
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }
    function select_row(rowElement,id,name){
        $('.contact-row').removeClass('selected-row');
        $(rowElement).addClass('selected-row');
        // console.log(rowElement);
        selectedContactId = id;
        selectedContactName = name;
    }
    let selectedContactId = null;
    let selectedContactName = null;
    function insertSelectedContact(){
        if (selectedContactId && selectedContactName) {
        $('#search_contactsModal').modal('hide');
        // $('#notes_contact').append(new Option(selectedContactName, selectedContactId));
        // This code is used when we need to differentiate data among the Customer, Supplier, and User.↓
        // $('#notes_contact').html(new Option(selectedContactName, selectedContactId).attr('data-type', selectedContactType));
        $('#notes_contact').html(new Option(selectedContactName, selectedContactId));
        $('#comaplint_contact').html(new Option(selectedContactName, selectedContactId));
        
        // Clear selection if needed
        selectedContactId = null;
        selectedContactName = null;
        } else {
            alert("Please select a contact row first.");
        }
    }
    function full_history() {
    var tableBody = $("#crm_customer_all_data");
    const itemsPerPage = 10;
    let currentPage = 1;
    let data = [];

    // Fetch data from the server
    function fetchData() {
        const token = '{{ csrf_token() }}';

        $.ajax({
            type: "POST",
            url: "{{ url('GetFullHistory') }}",
            data: {
                _token: token
            },
            success: function(response) {
                // console.log(response);
                if (response.success && response.callHistoryData.length > 0) {
                    data = response.callHistoryData;
                    setupPagination();
                    displayData(currentPage);
                } else {
                    tableBody.empty().append('<tr><td colspan="7" class="text-center">No history available</td></tr>');
                    $("#pagination").empty();
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: ", error);
            }
        });
    }

    // Display data in the table
    function displayData(page) {
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const itemsToShow = data.slice(startIndex, endIndex);

        tableBody.empty();

        itemsToShow.forEach(function(item) {
            const date = moment(item.date).format('DD/MM/YYYY HH:mm');

            // Visibility cell based on customer visibility status
            var visibilityCell = '';
            if (item.customer_visibility == 0) {
                visibilityCell = '<span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span>';
            } else if (item.customer_visibility == 1) {
                visibilityCell = '<span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>';
            }

            let notes = '';
            try {
                const notesObj = JSON.parse(item.notes); // Parsing the JSON stored in 'notes'

                // If notes contains a message and send_as data
                const message = notesObj.message || '';
                const notify = notesObj.notify || 'Unknown';
                const sendAs = notesObj.send_as ? notesObj.send_as.join(', ') : 'Unknown';

                // Format the notes
                notes = `${message}<br><b>Notify:</b> ${notify}<br><b>Send As:</b> ${sendAs}`;
            } catch (e) {
                console.error("Error parsing notes: ", e);
                notes = item.notes || '-'; // If parsing fails, show the raw notes or default '-'
            }

            const html = '<tr>' +
                '<td>' + date + '</td>' +
                '<td>' + item.by + '</td>' +
                '<td>' + (item.contact || '-') + '</td>' +
                '<td>' + (item.type || '-') + '</td>' +
                '<td>' + notes + '</td>' +  // Display formatted notes
                '<td></td>' + // Empty column, customize as needed
                '<td>' + visibilityCell + '</td>' +
            '</tr>';

            tableBody.append(html);
        });
    }

    // Set up pagination
    function setupPagination() {
        const pageCount = Math.ceil(data.length / itemsPerPage);
        const pagination = $("#pagination");
        pagination.empty();

        for (let i = 1; i <= pageCount; i++) {
            const pageItem = $('<li>').text(i).addClass('page-item');
            if (i === currentPage) {
                pageItem.addClass('active');
            }
            pageItem.on('click', function() {
                currentPage = i;
                displayData(currentPage);
                updatePagination();
            });
            pagination.append(pageItem);
        }

        updatePagination();
    }

    // Update the pagination UI
    function updatePagination() {
        $("#pagination li").removeClass('active');
        $("#pagination li").eq(currentPage - 1).addClass('active');
    }

    fetchData(); // Call fetchData to load the data when the page loads
}



</script>
<!-- Searching Via Email data -->
<script>
function searchDataWithEmail(text){
    var search_data_email=$('.search_data_email').val();
    if(search_data_email.length > 3){
        if(text == 'Complaints'){
            alert(search_data_email)
        }
    }
}
</script>
<script>
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("TaskStartDate").setAttribute("min", today);
    document.getElementById("TaskEndDate").setAttribute("min", today);

    function setMinEndDate() {
        $('#TaskEndDate').removeAttr('disabled');
        const startDate = document.getElementById("TaskStartDate").value;
        document.getElementById("TaskEndDate").setAttribute("min", startDate);
    }
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/ckeditor5-build-classic/ckeditor.js"></script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')