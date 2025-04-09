@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
    table.tablechange tbody td {
    font-size: 12px;
    white-space: nowrap;
}
.image_delete {
    cursor: pointer;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4 ">
            <div class="pageTitle">
                <h3>Products</h3>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
            <div class="jobsection">
                <a href="#" class="profileDrop" onclick="modal_show()">Add</a>
                <a href="#" class="profileDrop">Unauthorised (0)</a>
                <a href="#" class="profileDrop">Authorised (0)</a>
                <a href="#" class="profileDrop">Rejected (0)</a>
                <a href="#" class="profileDrop">Paid (0)</a>
                <a href="#" class="profileDrop">All (0)</a>
                <!-- <a href="#" class="profileDrop" id="impExpClickbtnPopup">Import/Export</a> -->
            </div>
        </div>
    </div>
    <di class="row">
        <div class="col-lg-12">
            <div class="maimTable mt-2 table_responsive">
                <div class="printExpt">
                    <div class="prntExpbtn">
                        <a href="#!">Print</a>
                        <a href="#!">Export</a>
                    </div>
                    <div class="searchFilter">
                        <a href="#!">Show Search Filter</a>
                    </div>

                </div>
                <!-- <div class="markendDelete">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="jobsection d-flex">
                                <a href="#" class="profileDrop">Delete</a>
                                <div class="pageTitleBtn p-0">
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
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="pageTitleBtn p-0">
                                <a href="#" class="profileDrop"> <i class="material-symbols-outlined">
                                        settings </i></a>
                            </div>
                        </div>
                    </div>
                </div> -->
                <div class="markendDelete">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="jobsection">
                                        <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                      
                                    </div>
                                </div>
                                
                            </div>
                        </div>

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

                    <tbody>
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
                            <td>{{ $val->expense_date }}</td>
                            <td>{{ $user }}</td>
                            <td>{{ $val->title }}</td>
                            <td>{{ $val->reference }}</td>
                            <td>{{ $job->job_ref ?? "-" }}</td>
                            <td>{{$site->site_name ?? ""}}</td>
                            <td>{{$val->notes}}</td>
                            <td>£{{$val->amount}}</td>
                            <td>£{{$val->vat_amount}}</td>
                            <td>£{{$val->gross_amount}}</td>
                            <td><?php echo($val->billable ==1)?"Yes":"No";?></td>
                            <td><?php echo($val->authorised ==1)?"Yes":"No";?></td>
                            <td><?php echo($val->reject ==1)?"Yes":"No";?></td>
                            <td><?php echo($val->paid ==1)?"Yes":"No";?></td>
                            <td><?php echo($val->attachments!='')?"<a href='#!' style='text-decoration:none'>View</a>":"";?></td>
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
                                            <a href="#" class="dropdown-item col-form-label">Reject</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php }?>
                    </tbody>
                    <tr>
                        <th colspan="2"></th>
                        <th colspan="16">Page Sub Total:</th>
                    </tr>
                        <tr>
                            <td colspan="9"></td>
                            
                            <td>£<?php echo number_format($net_amount,2, '.', '');?></td>
                            <td>£<?php echo number_format($vat_amount,2, '.', '');?></td>
                            <td colspan="8">£<?php echo number_format($gross_amount,2, '.', '');?></td>
                        </tr>
                </table>
                <!-- Modal start here -->
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
                                                    <input type="hidden" name="home_id" id="home_id" value="{{$home_id}}">
                                                    <div class="row">
                                                        <div class="col-md-6 col-lg-6 col-xl-6">
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Expense Name <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="title" name="title" value="" placeholder="Expense Name">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Net Amount <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="amount" name="amount" value="" placeholder="Net Amount">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputProject"
                                                                        class="col-sm-3 col-form-label">Vat <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
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
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Vat Amount</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="vat_amount" name="vat_amount" value="" onkeyup="calculate_vat()">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Gross Amount <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput"
                                                                            id="gross_amount" name="gross_amount" value="" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Expense Date <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="date" class="form-control editInput"
                                                                            id="expense_date" name="expense_date" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Expense By</label>
                                                                    <div class="col-sm-9">
                                                                        <select class="form-control editInput selectOptions"
                                                                            id="user_id" name="user_id">
                                                                            @foreach($users as $user_val)
                                                                            <option value="{{ $user_val->id }}" @if($user_val->id == $user_id) selected @endif>{{ $user_val->name }}</option>

                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Reference</label>
                                                                    <div class="col-sm-9">
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
                                                                        id="project_id" name="project_id" disabled onchange="find_appointment(null)">
                                                                        <option >None</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Job</label>
                                                                <div class="col-sm-9">
                                                                    <input type="text" class="form-control editInput"
                                                                        id="job" name="job" value="">
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputProject"
                                                                    class="col-sm-3 col-form-label">Job Appointment</label>
                                                                <div class="col-sm-9">
                                                                    <select class="form-control editInput selectOptions"
                                                                        id="job_appointment_id" name="job_appointment_id" disabled>
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
                                                                <label for="inputName" class="col-sm-3 col-form-label">Notes</label>
                                                                <div class="col-sm-9">
                                                                    <textarea class="form-control textareaInput" name="notes" id="notes" rows="3" ></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2 row">
                                                                <label for="inputName" class="col-sm-3 col-form-label">Attachments</label>
                                                                <div class="col-sm-9">
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
            </div> <!-- End off main Table -->
        </div>
    </di>
</div>
</section>
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
    function find_appointment(project_id){
        var id;
        if(project_id =='' || project_id == null){
            id=$("#project_id").val();
        }else{
            id=project_id;
        }
        
        var token='<?php echo csrf_token();?>'
        const job_appointmentSelect = document.getElementById("job_appointment_id");
        $("#job_appointment_id").prop('disabled',false);
        $.ajax({
                type: "POST",
                url: "{{url('/find_appointment')}}",
                data: {id:id,_token:token},
                success: function(data) {
                    console.log(data);
                    const job_appointArr=data.job_appoint;
                    job_appointArr.forEach((appoint) => {
                        const option = document.createElement("option");
                        option.value = appoint.id;
                        option.text = `${appoint.job_ref}`;
                        job_appointmentSelect.appendChild(option);
                    });
                    if(project_id != null){
                        $('#job_appointment_id').val(project_id);
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
            url: "{{url('expense_save')}}",
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
    $(".fetch_data").on('click', function(){
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
        find_appointment(project_id);

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
        var imgSrc = "{{url('public/frontEnd/jobs/images/delete.png')}}";
        var text = '&emsp;<img src="' + imgSrc + '" alt="" class="image_delete" data-delete="' + id + '">';
        $("#file_name").html(attachments + text);
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
@include('frontEnd.salesAndFinance.jobs.layout.footer')
