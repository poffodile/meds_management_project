@extends('backEnd.layouts.master')

@section('title','Expense')

@section('content')

<?php
    $page_url = url('admin/sales-finance/expense');
?>


<style type="text/css">
 .position-center label {
    font-size: 20px;
    font-weight: 500;
}   
</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                         <div class="row"> 
                          <div class="col-lg-12">  
                          
                            <div class="">
                                <div class="btn-group mr-3">
                                    <a href="javascript:void(0)" onclick="modal_show()">
                                    <!-- {{ url('admin/sales-finance/expense_add') }} -->
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add <i class="fa fa-plus"></i>
                                        </button>
                                    </a>    
                                </div>
                                <div class="btn-group mr-3">
                                    <a href="{{ url('admin/sales-finance/expense?key=authorised&value=0') }}">
                                        <button id="bgcolor1" class="btn btn-primary bgcolor">
                                        Unauthorised ({{$unauthorisedCount}})
                                        </button>
                                    </a>    
                                </div>
                                <div class="btn-group mr-3">
                                    <a href="{{ url('admin/sales-finance/expense?key=authorised&value=1') }}">
                                        <button id="bgcolor2" class="btn btn-primary bgcolor">
                                        Authorised ({{$authorisedCount}}) 
                                        </button>
                                    </a>    
                                </div>
                                <div class="btn-group mr-3">
                                    <a href="{{ url('admin/sales-finance/expense?key=reject&value=1') }}">
                                        <button id="bgcolor3" class="btn btn-primary bgcolor">
                                        Rejected ({{$rejectCount}}) 
                                        </button>
                                    </a>    
                                </div>
                                <div class="btn-group mr-3">
                                    <a href="{{ url('admin/sales-finance/expense?key=paid&value=1') }}">
                                        <button id="bgcolor4" class="btn btn-primary bgcolor">
                                        Paid ({{$paidCount}}) 
                                        </button>
                                    </a>    
                                </div>
                                <div class="btn-group mr-3">
                                    <a href="{{ url('admin/sales-finance/expense') }}">
                                        <button id="bgcolor5" class="btn btn-primary bgcolor">
                                        All ({{$expenseCount}}) 
                                        </button>
                                    </a>    
                                </div>
                                
                                @include('backEnd.common.alert_messages')
                            </div>
                           </div>
                           <div class="col-lg-6">
                            <!-- <div class="cog-btn-main-area">
                             <a class="btn btn-primary" href="#" data-toggle="dropdown">
                                    <i class="fa fa-cog fa-fw"></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    
                                </ul>   
                            </div> -->
                           </div>
                          </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ $page_url }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='post' action="{{ $page_url }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="dataTables_filter" id="editable-sample_filter">
                                            <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" ></label>
                                            <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                    <tr>
                                        <th>Expense By</th>
                                        <th>Expense Name</th>
                                        <th>Reference</th>
                                        <th>Attachments</th>
                                        <th>Reject</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if(count($expense) == 0){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="6">No Job found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 

                                        else
                                        {
                                            foreach($expense as $key => $value) 
                                            {  
                                                $user = App\User::find($value->user_id)->name;
                                                ?>

                                        <tr >
                                            <td class="user_name">{{$user}}</td>
                                            <td class="transform-none" style="text-transform: none;">{{ $value->title }}</td>
                                            <td>{{$value->reference}}</td>
                                            <td>
                                                @if($value->attachments != '')
                                                <a href="{{ url('public/images/expense/' . $value->attachments) }}" target="_blank" style="text-decoration:none">
                                                    View
                                                </a>
                                                @endif
                                            </td>
                                            <td>
                                                @if($value->reject == 1)
                                                    <a href="javascript:" onclick="reject('{{base64_encode($value->id)}}',0)" class="btn btn-success">No</a>
                                                @else
                                                <a href="javascript:" class="btn btn-danger" onclick="reject('{{base64_encode($value->id)}}',1)">Yes</a>
                                                @endif
                                            </td>
                                            <td class="action-icn">
                                            <!-- {{ url('admin/sales-finance/expense_add?key=') }}{{base64_encode($value->id)}} -->
                                                <a href="javascript:void(0)" class="edit fetch_data" data-bs-toggle="modal" data-bs-target="#customerPop" data-id="{{$value->id}}" data-title="{{$value->title}}" data-amount="{{$value->amount}}" data-vat="{{$value->vat}}" data-vat_amount="{{$value->vat_amount}}" data-gross_amount="{{$value->gross_amount}}" data-expense_date="{{$value->expense_date}}" data-user_id="{{$value->user_id}}" data-reference="{{$value->reference}}" data-customer_id="{{$value->customer_id}}" data-job="{{$value->job}}" data-project_id="{{$value->project_id}}" data-job_appointment_id="{{$value->job_appointment_id}}" data-authorised="{{$value->authorised}}" data-billable="{{$value->billable}}" data-paid="{{$value->paid}}" data-notes="{{$value->notes}}" data-attachments="{{$value->attachments}}"><span style= "font-size: 13px; color: #000;"><span style= "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a> &nbsp &nbsp &nbsp 

                                                <a href="javascript:void(0)" onclick="delete_job('{{base64_encode($value->id)}}')" class="text-danger"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                             
                                            </td>
                                            
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                           
                        </div>
                    </div>
                    <!-- Modal start here -->
                

<div class="modal fade popupcloseBtn in" id="customerPop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle" id="exampleModalLabel">Add Payment Type</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pdbotm">
	            <div class="alert alert-success text-center success_message" style="display:none;height:50px">
	            	<p>The Expense has been saved Successfully</p>
	            </div>
                <form id="form_data" class="customerForm">
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="home_id" id="home_id" value="{{$home_id}}">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Expense Name <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control"
                                            id="title" name="title" value="" placeholder="Expense Name">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Net Amount <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control"
                                            id="amount" name="amount" value="" placeholder="Net Amount">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputProject"
                                        class="col-sm-4 control-label">Vat <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <select class="form-control"
                                            id="vat" name="vat">
                                            <option value="0" selected>Custom VAT Amount</option>
                                            @foreach($rate as $rate_vale)
                                                <option value="{{$rate_vale->tax_rate}}">{{$rate_vale->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Vat Amount</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control "
                                            id="vat_amount" name="vat_amount" value="" onkeyup="calculate_vat()">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Gross Amount <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control "
                                            id="gross_amount" name="gross_amount" value="" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Expense Date <span class="radStar">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control "
                                            id="expense_date" name="expense_date" value="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Expense By</label>
                                    <div class="col-sm-8">
                                        <select class="form-control  "
                                            id="user_id" name="user_id">
                                            @foreach($users as $user_val)
                                            <option value="{{ $user_val->id }}" @if($user_val->id == $user_id) selected @endif>{{ $user_val->name }}</option>

                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputName" class="col-sm-4 control-label">Reference</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control "
                                            id="reference" name="reference" value="">
                                    </div>
                                </div>
                                
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="form-group row">
                                <label for="inputProject"
                                    class="col-sm-4 control-label">Customer</label>
                                <div class="col-sm-8">
                                    <select class="form-control  "
                                        id="customer_id" name="customer_id" onchange="find_project(null,null)">
                                        <option selected disabled >--None--</option>
                                        @foreach($customer as $customer_val)
                                            <option value="{{$customer_val->id}}">{{$customer_val->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputProject"
                                    class="col-sm-4 control-label">Project</label>
                                <div class="col-sm-8">
                                    <select class="form-control  "
                                        id="project_id" name="project_id" disabled>
                                        <option >None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Job</label>
                                <div class="col-sm-8">
                                    <div class="textbox completeIt">
                                        <input type="text" class="form-control " id="job" autocomplete="off" autofocus name="job_display">
                                        <input type="hidden" id="selectedJobRef" name="job">
                                        <div class="icon"></div>
                                        <div class="autoComplete" id="jobList"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputProject"
                                    class="col-sm-4 control-label">Job Appointment</label>
                                <div class="col-sm-8">
                                    <select class="form-control  "
                                        id="job_appointment_id" name="job_appointment_id" disabled >
                                        <option >None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Authorised</label>
                                <div class="col-sm-8">

                                    <label class="radio-inline">
                                        <input type="radio"  name="authorisedradio" id="authorised1">Yes
                                    </label>
                                    <label class="radio-inline">
                                            <input type="radio" name="authorisedradio" id="authorised0" checked="">No
                                    </label>


                                    <!-- <div class="form-check form-check-inline">
                                        <input class="form-check-input " type="radio" name="authorisedradio" id="authorised1">
                                        <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input " type="radio" name="authorisedradio" id="authorised0" checked="">
                                        <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                    </div> -->
                                    <input type="hidden" id="authorised" name="authorised" value="0">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Billable?</label>
                                    <div class="col-sm-8">

                                        <label class="radio-inline">
                                            <input type="radio"  name="billableradio" id="billabl1" checked="">Yes
                                        </label>
                                        <label class="radio-inline">
                                              <input type="radio" name="billableradio" id="billable0">No
                                        </label>


                                        <!-- <div class="form-check form-check-inline">
                                            <input class="form-check-input " type="radio" name="billableradio" id="billabl1" checked="">
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input " type="radio" name="billableradio" id="billable0">
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div> -->
                                        <input type="hidden" id="billable" name="billable" value="0">
                                    </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Paid</label>
                                    <div class="col-sm-8">
                                        <label class="radio-inline">
                                            <input type="radio" name="optradio" id="paid1" >Yes
                                        </label>
                                        <label class="radio-inline">
                                              <input type="radio" name="optradio" id="paid0" checked="">No
                                        </label>


                                        <!-- <div class="form-check form-check-inline">
                                            <input class="form-check-input " type="radio" name="paidradio" id="paid1">
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input " type="radio" name="paidradio" id="paid0" checked="">
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div> -->
                                        <input type="hidden" id="paid" name="paid" value="0">
                                    </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Notes</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="notes" id="notes" rows="3" ></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Attachments</label>
                                <div class="col-sm-10">
                                    <input type="file" class=""
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="save_data" class="btn btn-primary" >Add</button>
            </div>
        </div>
    </div>
</div>
                <!-- end here -->
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
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
                url: "{{url('admin/sales-finance/expense/find_project')}}",
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
            url: "{{url('admin/sales-finance/expense/expense_save')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
                success: function(data) {
                    console.log(data);
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
            url: "{{url('admin/sales-finance/expense/find_job')}}",
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
                url: "{{url('admin/sales-finance/expense/find_appointment')}}",
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
    $('document').ready(function(){
        $('.send-set-pass-link-btn').click(function(){

            var send_btn = $(this);
            var user_id = send_btn.attr('id');
        
            $('.loader').show();

            $.ajax({
                type:'get',
                url : '{{ url('admin/users/send-set-pass-link') }}'+'/'+user_id,
                success:function(resp){
                    console.log(resp);
                    // return false;
                    if(resp == true){
                        
                        var usr = send_btn.closest('tr').find('.user_name').text();
                        alert('Email sent to '+usr+' successfully');
                    
                    } else{
                        alert('{{ COMMON_ERROR }}');
                    }
                    $('.loader').hide();
                }
            });
            return false;
        });
    });
</script>
<script>
    function reject(id,reject){
        var id=id;
        var reject=reject;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/sales-finance/expense/expense_reject')}}",
                data:{id:id,reject:reject,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
                }
            }); 
    }
    function delete_job(id){
       if(confirm("Do you want to delete it ?")){
        var id=id;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/sales-finance/expense/expense_delete')}}",
                data:{id:id,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
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
        
    })
</script>
<script>
    $(".fetch_data").on('click', function(){
        $("#customerPop").modal('show');
        $('#save_data').text("Update");
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
        }
        
    });
 </script>
<script>
    function modal_show(){
        $("#file_name").html('');
        $('#save_data').text("Add");
        $("#form_data")[0].reset();
        $("#customerPop").modal('show');
    }
    $(document).on('click', '.image_delete', function() {
        var id=$(this).data('delete');
        if(confirm("Are you sure to delete?")){
            var token='<?php echo csrf_token();?>'
            $.ajax({
                type: "POST",
                url: "{{url('admin/sales-finance/expense/expense_image_delete')}}",
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
@endsection


