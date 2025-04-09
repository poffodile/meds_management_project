@include('frontEnd.salesAndFinance.jobs.layout.header')

<style>
    table.tablechange tbody td {
    font-size: 12px;
    white-space: nowrap;
}
.image_delete {
    cursor: pointer;
}
.textbox {
    box-sizing: border-box;
    perspective: 500px;
    position: relative;
    text-align: left;
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
 .parent-container {
    position: absolute;
    background: #fff;
    width:190px;
}
#productList li:hover{
    cursor: pointer;
}
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Expense</h3>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    
                    <a href="#" class="profileDrop" onclick="modal_show()">Add</a>
                    <a href="{{url('expenses?key=authorised&value=0')}}" class="profileDrop bgcolor" id="bgcolor1">Unauthorised ({{$unauthorisedCount}})</a>
                    <a href="{{url('expenses?key=authorised&value=1')}}" class="profileDrop bgcolor" id="bgcolor2">Authorised ({{$authorisedCount}})</a>
                    <a href="{{url('expenses?key=reject&value=1')}}" class="profileDrop bgcolor" id="bgcolor3">Rejected ({{$rejectCount}})</a>
                    <a href="{{url('expenses?key=paid&value=1')}}" class="profileDrop bgcolor" id="bgcolor4">Paid ({{$paidCount}})</a>
                    <a href="{{url('expenses')}}" class="profileDrop bgcolor" id="bgcolor5">All ({{$expenseCount}})</a>
                    <!-- <a href="#" class="profileDrop" id="impExpClickbtnPopup">Import/Export</a> -->
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
                        <a href="javascript:void(0)" onclick="hideShowDiv()" class="hidebtn">Hide Search Filter</a>
                    </div>

                </div>
                <div class="searchJobForm" id="divTohide">
                    <form id="search_dataForm" class="p-4">
                        <div class="row justify-content-center">
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Expense By:</label>
                                    <div class="col-md-8">
                                        <select class="form-control editInput selectOptions" id="expenseBy">
                                            <option selected disabled></option>
                                            <option value="{{Auth::user()->id}}">{{Auth::user()->name}}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Expense Date:</label>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control editInput" id="start_date" >
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" class="form-control editInput" id="end_date">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Customer:</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput" id="customer_name" placeholder="Type Customer Name">
                                        <input type="hidden" id="selectedId" name="selectedId">
                                        <div class="parent-container"></div>
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput" id="keywords" keywords="" placeholder="Keywords to seacrh">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label text-end">Billable:</label>
                                    <div class="col-md-8">
                                        <select class="form-control editInput selectOptions" id="billable_search">
                                            <option selected disabled>--Any--</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="pageTitleBtn justify-content-center">
                                    <a href="javascript:void(0)" onclick="searchBtn()" class="profileDrop px-3">Search </a>
                                    <a href="javascript:void(0)" onclick="clearBtn()" class="profileDrop px-3">Clear</a>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>



            </div> <!-- End off main Table -->
        </div>
            <div class="col-lg-12">
                <div class="maimTable mt-2 table_responsive">
                
                    
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection d-flex">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <!-- <div class="pageTitleBtn p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Bulk Action </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" class="dropdown-item col-form-label">Set Accont
                                                    Codes</a>
                                                <a href="#" class="dropdown-item col-form-label">Set Tax
                                                    Rats</a>
                                                <a href="#" class="dropdown-item col-form-label">Fix duplicate
                                                    product codes</a>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                            <!-- <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <a href="#" class="profileDrop"> <i class="material-symbols-outlined">
                                            settings </i></a>
                                </div>
                            </div> -->
                        </div>
                    </div>
                    @if(session('message'))
                    <div class="alert alert-success text-center success_message mt-3 m-auto" style="height:50px; width:50%">
                        <p>{{ session('message') }}</p>
                    </div>
                    @endif

                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Date</th>
                                <th>Expense By</th>
                                <th>Expense Name</th>
                                <th>Reference</th>
                                <th>Job Ref</th>
                                <th>Site</th>
                                <th>Notes</th>
                                <th>Net Amount</th>
                                <th>Vat Amount</th>
                                <th>Gross Amount</th>
                                <th>Billing</th>
                                <th>Authorised</th>
                                <th>Rejected</th>
                                <th>Paid</th>
                                <th>Attachments</th>
                                <th>Created On</th>
                                <th></th>

                            </tr>
                        </thead>

                        <tbody id="expense_data">
                        <?php 
                            $net_amount=0;
                            $vat_amount=0;
                            $gross_amount=0;
                                foreach($expense as $key=>$val){
                                    $net_amount=$net_amount+$val->amount;
                                    $vat_amount=$vat_amount+$val->vat_amount;
                                    $gross_amount=$gross_amount+$val->gross_amount;
                                    $user = App\User::find($val->user_id)->name;
                                    $job = App\Models\Job::find($val->job_id);
                                    if(isset($job)){
                                        if($job->site_id == 'default' || $job->site_id == ''){
                                            $site = App\Models\Constructor_customer_site::where('customer_id',$job->customer_id)->orderBy('id','DESC')->first(); 
                                        }else{
                                            $site = App\Models\Constructor_customer_site::find($job->site_id);
                                        }
                                    }
                        ?>
                            <tr>
                                <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                <td>{{ ++$key }}</td>
                                <td><?php echo  date('d-m-Y',strtotime($val->expense_date));?></td>
                                <td disabled>{{ $user }}</td>
                                <td>{{ $val->title }}</td>
                                <td>{{ $val->reference }}</td>
                                <td>{{ $val->job ?? "-" }}</td>
                                <td>{{$site->site_name ?? ""}}</td>
                                <td>{{$val->notes}}</td>
                                <td>£{{$val->amount}}</td>
                                <td>£{{$val->vat_amount}}</td>
                                <td>£{{$val->gross_amount}}</td>
                                <td><?php echo($val->billable ==1)?"Yes":"No";?></td>
                                <td><?php echo($val->authorised ==1)?"Yes":"No";?></td>
                                <td><?php echo($val->reject ==1)?"Yes":"No";?></td>
                                <td><?php echo($val->paid ==1)?"Yes":"No";?></td>
                                <td> 
                                    @if($val->attachments != '')
                                    <a href="{{ url('public/images/expense/' . $val->attachments) }}" target="_blank" style="text-decoration:none">
                                        View
                                    </a>
                                    @endif
                                </td>
                                <td>{{$val->created_at}}</td>
                                <td>
                                    <div class="pageTitleBtn p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" class="dropdown-item col-form-label fetch_data" data-bs-toggle="modal" data-bs-target="#customerPop" data-id="{{$val->id}}" data-title="{{$val->title}}" data-amount="{{$val->amount}}" data-vat="{{$val->vat}}" data-vat_amount="{{$val->vat_amount}}" data-gross_amount="{{$val->gross_amount}}" data-expense_date="{{$val->expense_date}}" data-user_id="{{$val->user_id}}" data-reference="{{$val->reference}}" data-customer_id="{{$val->customer_id}}" data-job="{{$val->job}}" data-project_id="{{$val->project_id}}" data-job_appointment_id="{{$val->job_appointment_id}}" data-authorised="{{$val->authorised}}" data-billable="{{$val->billable}}" data-paid="{{$val->paid}}" data-notes="{{$val->notes}}" data-attachments="{{$val->attachments}}">Edit</a>
                                                <hr class="dropdown-divider">
                                                <a onclick="return confirm('Are you sure to reject it?')" href="{{url('/reject_expense?key=')}}{{base64_encode($val->id)}}" class="dropdown-item col-form-label">Reject</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php }?>
                        </tbody>
                        @if(count($expense)>0)
                            <tr class="calcualtionShowHide">
                                <th colspan="2"></th>
                                <th colspan="16">Page Sub Total:</th>
                            </tr>
                            <tr class="calcualtionShowHide">
                                <td colspan="9"></td>
                                
                                <td id="Tablenet_amount">£<?php echo number_format($net_amount,2, '.', '');?></td>
                                <td id="Tablevat_amount">£<?php echo number_format($vat_amount,2, '.', '');?></td>
                                <td id="Tablegross_amount" colspan="8">£<?php echo number_format($gross_amount,2, '.', '');?></td>
                            </tr>
                        @endif
                    </table>
                    <!-- Modal start here -->
                    <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content add_Customer">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="customerModalLabel">Expense</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    
                                    <div class="modal-body">
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                        <div class="mt-1 mb-0 text-center" id="message_save"></div>
                                    </div>
                                        <div class="row">
                                            <div class="col-md-12 col-lg-12 col-xl-12">
                                                <div class="formDtail">
                                                    <form id="form_data" class="customerForm">
                                                        @csrf
                                                        <input type="hidden" name="id" id="id">
                                                        <input type="hidden" name="home_id" id="home_id" value="{{$home_id}}">
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
                                                                                <option value="{{ $user_val->id }}" @if($user_val->id == $user_id) selected @endif>{{ $user_val->name }}</option>

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
                                                                        <p class="editInput">(Max file size 25 MB)</p>
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
                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>
  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
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
            console.log(1);
            $("#vat").val(0);
            var calculation=amount*vat_amount/100;
            var gross_amount=amount+calculation;
            $("#gross_amount").val(gross_amount.toFixed(2));
        }else{
            console.log(2);
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
                    console.log(data);
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
                success: function(response) {
                    console.log(response);
                    if(response.vali_error){
                        alert(response.vali_error);
                        $("#email").css('border','1px solid red');
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_save').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_save').removeClass('success-message').text('').hide();
                            location.reload();
                        }, 3000);
                    }else if(response.success === false){
                        $('#message_save').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                    }else{
                        alert("Something went wrong");
                        return false;
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
            var model='Expense';
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
 <script>
    // $(".fetch_data").on('click', function(){
    $(document).on('click', '.fetch_data', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var amount = $(this).data('amount');
        var vat = $(this).data('vat');
        var vat_amount = $(this).data('vat_amount');
        var gross_amount = $(this).data('gross_amount');
        var expense_date = $(this).data('expense_date');
        var user_id = $(this).data('user_id');
        var reference = $(this).data('reference');
        var customer_id = $(this).data('customer_id');
        var job = $(this).data('job');
        var job_appointment_id = $(this).data('job_appointment_id');
        var authorised = $(this).data('authorised');
        var billable = $(this).data('billable');
        var paid = $(this).data('paid');
        var notes = $(this).data('notes');
        var attachments = $(this).data('attachments');
        var project_id = $(this).data('project_id');

        $("#gross_amount").prop('disabled',false);
        $("#project_id").prop('disabled',false);
        $("#job_appointment_id").prop('disabled',false);

        find_project(customer_id,project_id);
        find_appointment(job);

        $('#id').val(id);
        $('#title').val(title);
        $('#amount').val(amount);
        $('#vat').val(vat);
        $('#vat_amount').val(vat_amount);
        $('#gross_amount').val(gross_amount);
        $('#expense_date').val(expense_date);
        $('#user_id').val(user_id);
        $('#reference').val(reference);
        $('#customer_id').val(customer_id);
        $('#job').val(job);
        $('#authorised').val(authorised);
        $('#authorised'+authorised).prop('checked',true);
        $('#billable').val(billable);
        $('#billable'+billable).prop('checked',true);
        $('#paid').val(paid);
        $('#paid'+paid).prop('checked',true);
        $('#notes').val(notes);
        // 
        // $('#attachments').val(attachments);
        if(attachments){
            var imgSrc = "{{url('public/frontEnd/jobs/images/delete.png')}}";
            var text = '&emsp;<img src="' + imgSrc + '" alt="" class="image_delete" data-delete="' + id + '">';
            $("#file_name").html(attachments + text);
        }else{
            $("#file_name").html('');
        }
        
    });
 </script>
 <script>
    function modal_show(){
        $("#form_data")[0].reset();
        $("#customerPop").modal('show');
    }
    $(document).on('click', '.image_delete', function() {
        var id=$(this).data('delete');
        if(confirm("Are you sure to delete?")){
            var token='<?php echo csrf_token();?>'
            $.ajax({
                type: "POST",
                url: "{{url('/expense_image_delete')}}",
                data: {id:id,_token:token},
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

                            console.log(hiddenJobInput.value);  
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
        console.log(id);
        if(id.length>1){
            var job_id=id[1];
            var token='<?php echo csrf_token();?>'
            $.ajax({
                type: "POST",
                url: "{{url('/find_appointment')}}",
                data: {job_id:job_id,_token:token},
                success: function(data) {
                    console.log(data);
                    if(data.length>0){
                        $('#job_appointment_id').prop('disabled',false);
                        var selectHTML = '';
                        $.each(data, function(index, appointment) {
                            selectHTML += '<option value="' + appointment.appointment_id + '">' + appointment.user_name + ':'+ appointment.start_date +' '+appointment.start_time+'-'+appointment.end_date+' '+appointment.end_time+','+appointment.status+ '</option>';
                        });

                        selectHTML += '</select>';
                        document.getElementById('job_appointment_id').innerHTML = selectHTML;
                        
                        $('#job_appointment_id').select2();
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
    $(document).ready(function(){
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        const key = params.get('key');
        const value = params.get('value');
        console.log('Key:', key);
        console.log('Value:', value);
        $('.bgcolor').css("background-color","");
        if (key === 'authorised' && value == 0) {
            $("#bgcolor1").css("background-color", "#494949");
        } else if (key === 'authorised' && value == 1) {
            $("#bgcolor2").css("background-color", "#494949");
        } else if (key === 'reject' && value == 1) {
            $("#bgcolor3").css("background-color", "#494949");
        } else if (key === 'paid' && value == 1) {
            $("#bgcolor4").css("background-color", "#494949");
        } else {
            $("#bgcolor5").css("background-color", "#494949");
        }
        setTimeout(function() {
            $('.alert').hide();
        }, 3000);
    })
</script>
<script>
    $("#expenseBy").on('change', function(){
        var expenseBy = $(this).find('option:selected');
        if (expenseBy.val()) {
            $(this).prop('disabled', true);
        }
    });
    function clearBtn(){
        $("#search_dataForm")[0].reset();
    }
    function searchBtn(){
        var expenseBy=$("#expenseBy").val();
        var customer_name=$("#customer_name").val();
        var selectedId=$("#selectedId").val();
        var billable=$("#billable_search").val();
        var start_date=$("#start_date").val();
        var end_date=$("#end_date").val();
        var keywords=$("#keywords").val();
        const Httpurl = new URL(window.location.href);
        const params = new URLSearchParams(Httpurl.search);
        const key = params.get('key');
        const value = params.get('value');
        let isEmpty = true;
        $("#search_dataForm").find("input, select, textarea").each(function() {
            if ($(this).val() && $(this).val().trim() !== "") {
                isEmpty = false;
                return false;
            }
        });

        if (isEmpty) {
            alert("Please fill in at least one field before searching.");
            return false;
        }
        if(start_date != '' && end_date == ''){
            alert("Please choose both date");
            return false;
        }
        if(start_date == '' && end_date != ''){
            alert("Please choose both date");
            return false;
        }
        
        $.ajax({
            url: "{{ url('searchExpenses') }}",
            method: 'post',
            data: {
                expenseBy: expenseBy,customer_name:customer_name,selectedId:selectedId,billable:billable,start_date:start_date,end_date:end_date,keywords:keywords,key:key,value:value,_token: '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
                // return false;
                var table = $('#exampleOne').DataTable();
                table.destroy();
                if(response.data.length>0){
                    $("#expense_data").html(response.data);
                    $("#Tablenet_amount").text("£"+response.net_amount);
                    $("#Tablevat_amount").text("£"+response.vat_amount);
                    $("#Tablegross_amount").text("£"+response.gross_amount);
                }else{
                    $("#expense_data").html(response.data);
                    $(".calcualtionShowHide").hide();
                }
                // $('#exampleOne').DataTable();
                $('#exampleOne').DataTable({
                    order: [[1, 'asc']],
                    language: {
                        paginate: {
                            previous: "Previous",
                            next: "Next"
                        },
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        infoEmpty: "No entries available",
                        emptyTable: '<span style="color: #e10078; font-weight: bold;">Sorry, there are no items available</span>',
                        infoFiltered: "(filtered from _MAX_ total entries)",
                        lengthMenu: "Show _MENU_ entries",
                        search: "Search:",
                        zeroRecords: "No matching records found"
                    },
                    paging: true
                });
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        }); 
    }
</script>
<script>
    $(document).ready(function() {
        $('#customer_name').on('keyup', function() {
            let search_query = $(this).val();
            const divList = document.querySelector('.parent-container');

            if (search_query === '') {
                divList.innerHTML = '';
            }
            if (search_query.length > 2) {
                $.ajax({
                    url: "{{ url('searchCustomerName') }}",
                    method: 'post',
                    data: {
                        search_query: search_query,_token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log(response);
                        // return false;
                        divList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'container';

                      
                        const ul = document.createElement('ul');
                        ul.id = "productList";
                        if(response.data.length >0){
                            response.data.forEach(item => {
                                const li = document.createElement('li'); 
                                li.textContent = item.name; 
                                li.id = item.id;
                                li.name = item.name;
                                li.className = "editInput";
                                ul.appendChild(li); 
                                const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            divList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                divList.innerHTML = '';
                                document.getElementById('customer_name').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedId = event.target.id;
                                    const selectedName = event.target.name;
                                    console.log('Selected Customer ID:', selectedId);
                                    console.log('Selected Customer Name:', selectedName);
                                    $("#customer_name").val(selectedName);
                                    $("#selectedId").val(selectedId);
                                    // getCustomerData(selectedId,selectedName);
                                }
                            });
                        }else{
                            const Errorli = document.createElement('li'); 
                            Errorli.textContent = 'Sorry Data Not found'; 
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli); 
                            div.appendChild(ul);
                            divList.appendChild(div);
                            setTimeout(function() {
                                divList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                divList.innerHTML = '';
                $('#results').empty();
            }
        });

    });
    $("#end_date").change(function () {
      var startDate = document.getElementById("start_date").value;
      var endDate = document.getElementById("end_date").value;

      if ((Date.parse(startDate) >= Date.parse(endDate))) {
          alert("End date should be greater than Start date");
          document.getElementById("end_date").value = "";
      }
  });
  $("#start_date").change(function () {
      var startDate = document.getElementById("start_date").value;
      var endDate = document.getElementById("end_date").value;

      if ((Date.parse(endDate) <= Date.parse(startDate))) {
          alert("Start date should be less than End date");
          document.getElementById("start_date").value = "";
      }
  });
</script>

@include('frontEnd.salesAndFinance.jobs.layout.footer')
