@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
    .addError {
        border:1px solid red;
    }
</style>
    <section class="main_section_page px-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 col-lg-4 col-xl-4 ">
                    <div class="pageTitle">
                        <h3>Job Titles (Positions)</h3>
                    </div>
                </div>
                <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                    <div class="pageTitleBtn">
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                
                    <div class="jobsection">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#customerPop" class="profileDrop">Add</a>
                    </div>
                    
                </div>
                <div class="col-md-4 col-lg-4 col-xl-4 ">
                </div>
            </div>
            <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
            <p id="status_meesage"></p>
        </div>
            <di class="row">
                <div class="col-lg-12">
                    <div class="maimTable">
                        <div class="printExpt">
                            <div class="prntExpbtn">
                            <a href="#!">Print</a>
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
                        <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                    <th>#</th>
                                    <th>Job Title </th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                                               
                            <tbody id="result">
                            <?php foreach($job_title as $key=>$val){?>
                                <tr>
                                    <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                    <td>{{++$key}}</td>
                                    <td>{{$val->name}}</td>
                                    <td>
                                        <?php if($val->status == 1){?>
                                            <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                            <?php } else {?>
                                            <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                            
                                        <?php }?>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                    Action
                                                </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="javascript:void(0)" onclick="get_model_with_id({{$val->id}})" class="dropdown-item">Edit Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php }?>

                            </tbody>
                        </table>

                    </div>   <!-- End off main Table -->
                    <!-- Job type Modal start here -->
                    <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-md">
                            <div class="modal-content add_Customer">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="customerModalLabel">Job Title-Add</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                        <p id="message"></p>
                                    </div>
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="formDtail">
                                                <form id="form_data" class="customerForm">
                                                    <input type="hidden" name="id" id="id">
                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Job Title <span class="radStar ">*</span></label>
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
                                                                id="modal_status" name="status">
                                                                <option value="1" id="status_1">Active</option>
                                                                <option value="0" id="status_0">Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div> <!-- End row -->
                                </div>
                                <div class="modal-footer customer_Form_Popup">

                                    <button type="button" class="profileDrop" onclick="get_save_job_title()">Save</button>
                                    <button type="button" class="profileDrop" onclick="save_job_titleClose()">Save &
                                        Close</button>
                                    <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end here -->
                    
                </div>
            </di>
        </div>
        <script src="{{url('public/backEnd/js/multiselect.js')}}"></script>
        <script>
            
            function save_job_titleClose(){
                var name=$("#name").val();
                if(name == ''){
                    $("#name").addClass('addError');
                    return false;
                } else {
                    get_save_job_title();
                    $("#customerPop").modal('hide');
                }
                
            }
            function get_save_job_title(){
                var token='<?php echo csrf_token();?>'
                var name=$("#name").val();
                var status=$("#modal_status").val();
                var home_id='<?php echo $home_id;?>'
                var id=$("#id").val();
                var message;
                var url;
                if(id == ''){
                    url="{{url('/save_job_title')}}";
                    message= "Added Successfully Done";
                } else {
                    url="{{url('/edit_job_title')}}";
                    message= "Editted Successfully Done";
                }
                if(name == ''){
                    $("#name").addClass('addError');
                    return false;
                }else {
                        $.ajax({
                        type: "POST",
                        url: url,
                        data: {id:id,home_id:home_id,name:name,status:status,_token:token},
                        success: function(data) {
                            console.log(data);
                            $("#message").text(message);
                            $(".success_message").show();
                            setTimeout(function() {
                                $(".alert").hide();
                                location.reload();
                            }, 3000);
                            $("#form_data")[0].reset();
                            
                        },
                        error: function(xhr, status, error) {
                            var errorMessage = xhr.status + ': ' + xhr.statusText;
                            alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                        }
                    });
                }
            }
            function get_model_with_id(id){
                var token='<?php echo csrf_token();?>'
                $.ajax({
                        type: "POST",
                        url: "{{url('/job_title_edit_form')}}",
                        data: {id:id,_token:token},
                        success: function(data) {
                            console.log(data);
                            $("#id").val(data.id);
                            $("#name").val(data.name);
                            $("#customerPop").modal('show');
                            $("#modal_status").val(data.status);
                        }
                    });
            }
            function status_change(id, status){
            var token='<?php echo csrf_token();?>'
            var model="job_title";
            $.ajax({
                type: "POST",
                url: "{{url('/job_title_status_change')}}",
                data: {id:id,status:status,model:model,_token:token},
                success: function(data) {
                    console.log(data);
                    if($.trim(data)==1){
                        $("#status_meesage").text("status Changed Successfully Done");
                        $("#msg").show();
                        setTimeout(function() {
                            location.reload();
                        }, 3000);

                        
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
            var model='Job_title';
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
    </section>
    @include('frontEnd.salesAndFinance.jobs.layout.footer')