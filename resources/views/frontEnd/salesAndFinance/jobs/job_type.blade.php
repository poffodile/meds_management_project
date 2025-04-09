@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
    .addError {
        border: 1px solid red;
    }
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Jobs Type</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <?php if (array_key_exists(315, $access_rights)) { ?>
                    <div class="jobsection">
                        <a href="#!" data-bs-toggle="modal" data-bs-target="#customerPop" class="profileDrop">Add</a>
                    </div>
                <?php } ?>
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
                                <th>Job Type </th>
                                <th>Customer Visible</th>
                                <th>Default Compltion Days</th>
                                <th>Default</th>
                                <th>Workflow </th>
                                <th>Status</th>
                                <th> </th>
                            </tr>
                        </thead>

                        <tbody id="result">
                            <?php foreach ($job_type as $key => $val) {
                                $workflow = App\Models\Work_flow::where(['home_id' => $home_id, 'job_type_id' => $val->id])->first(); ?>
                                <tr>
                                    <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                    <td>{{++$key}}</td>
                                    <td>{{$val->name}}</td>
                                    <td><?php echo ($val->customer_visible == 1) ? "Yes" : "No"; ?></td>
                                    <td>{{$val->default_days}}</td>
                                    <td><span class="grayCheck"><i class="fa-solid fa-circle-check"></i></span></td>
                                    <td><?php if (isset($workflow) && $workflow->id == $val->id) { ?>
                                            <span class="grayCheck"><i style="color:green" class="fa-solid fa-check"></i></span>
                                        <?php } else {
                                            echo "-";
                                        } ?>
                                    </td>
                                    <td>
                                        <?php if ($val->customer_visible == 1) { ?>
                                            <span class="grencheck"><i class="fa-solid fa-circle-check"></i></span>
                                            <?php } else {
                                            if ($val->status == 1) { ?>
                                                <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                            <?php } else { ?>
                                                <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>

                                        <?php }
                                        } ?>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                    Action
                                                </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="javascript:void(0)" onclick="get_model_with_id({{$val->id}})" class="dropdown-item">Edit Details</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="javascript:void(0)" onclick="get_flow_model({{$val->id}})" class="dropdown-item">Manage Workflow</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>

                </div> <!-- End off main Table -->
                <!-- Job type Modal start here -->
                <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content add_Customer">
                            <div class="modal-header">
                                <h5 class="modal-title" id="customerModalLabel">Job Type-Add</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                        <p id="message"></p>
                                    </div>
                                    <div class="col-12">
                                        <div class="formDtail">
                                            <form id="form_data" class="customerForm">
                                                <input type="hidden" name="id" id="id">
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Job Type <span class="radStar">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput"
                                                            id="name" name="name" value="">
                                                    </div>
                                                </div>


                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Number of days <span class="radStar">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput"
                                                            id="default_days" name="default_days" value="14" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>

                                                <div class="mb-2 row align-items-center">
                                                    <label class="col-sm-3 col-form-label">Customer Visible</label>
                                                    <div class="col-sm-9">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <input class="form-check-input mt-0" type="checkbox" id="customer_visible"
                                                                value="1" checked name="customer_visible">
                                                            <label class="form-check-label checkboxtext"
                                                                for="inlineRadio1">Yes,visible to customer</label>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="mb-2 row">
                                                    <label for="inputProject"
                                                        class="col-sm-3 col-form-label">Appointment Type</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions appointmentType"
                                                            id="appointment_id" name="appointment_id">
                                                            <option selected disabled>Select Appointment Type</option>
                                                            <?php foreach ($appointment_type as $type) { ?>
                                                                <option value="{{$type->id}}">{{$type->name}}</option>
                                                            <?php } ?>
                                                        </select>
                                                        <!-- (Planning) -->
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

                                <button type="button" class="profileDrop" onclick="get_save_job_type()">Save</button>
                                <!-- <button type="button" class="profileDrop" onclick="save_job_typeClose()">Save &
                                        Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end here -->
                <!-- work flow Modal start here -->

                <div class="modal fade" id="workflowModal" tabindex="-1" aria-labelledby="customerModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content add_Customer">
                            <div class="modal-header">
                                <h4 class="modal-title fs-5 text-white"> Workflow </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                        <p id="message"></p>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="formDtail">
                                            <form id="flowForm">
                                                @csrf
                                                <input type="hidden" name="home_id" id="home_id" value="{{$home_id}}">
                                                <input type="hidden" id="delete_ids_array" name="delete_ids_array" class="delete_ids_array">
                                                <div class="custom-fieldset">
                                                    <div class="custom-legend"><strong>New Job ></strong> Workflow</div>
                                                    <input type="hidden" id="job_type_id" name="job_type_id">
                                                    <p class="floatLeft redText marginTop8px marginBottom10">
                                                        Assigning a workflow to a job type will help your planners follow the correct stages of a Job. These stages can be adjusted at planning should they be required to go outside the normal flow if necessary.<br>
                                                        To change the workflow stages in this section once created and saved, will require you not to have any active jobs using this job type in the system – changing the overall workflow stages is deactivated until all jobs have been completed using this job type.<br>
                                                        Alternatively you can create a new job type with a new workflow.<br>
                                                        <br>Please call your account manager for more information
                                                    </p>
                                                    <a href="javascript:" class="profileDrop" style="float:right; margin-bottom: 20px;" onclick="show_hide()">Add Workflow</a>
                                                    <table class="table">
                                                        <thead class="header-row flowhead" style="display:none" id="flowhead">
                                                            <th width="3%"></th>
                                                            <th>Job Appointment Types</th>
                                                            <th width="70px">Notify</th>
                                                            <th>&nbsp;</th>
                                                        </thead>
                                                        <tbody id="result_work_flow">
                                                            <tr style="cursor: move; font-weight: bold; color: green;" id="wrokflow_msg">
                                                                <td class="workflowText" style="border-top: none;">
                                                                    <p>Please start adding workflow</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div> <!-- End row -->
                            </div>
                            <!-- <div class="noti_button" style="margin-left:60%">
                                    <input type="button" class="btn btn-primary" value="Save" onclick="save_work_flow()"></input>
                                    <a href="javascript:void(0)" class="btn btn-primary" onclick="document.getElementById('workflowModal').style.display='none'">Cancel</a>
                                </div> -->
                            <div class="modal-footer">
                                <button type="button" id="lead_reject" class="profileDrop" onclick="save_work_flow()">Save</button>
                                <button type="button" class="profileDrop" data-bs-dismiss="modal" onclick="document.getElementById('workflowModal').style.display='none'">Close</button>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end here -->
                <!-- Notification Rules model start here -->

                <div class="modal fade" id="workflowNotificationModal" tabindex="-1" aria-labelledby="customerModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content add_Customer">
                            <div class="modal-header">
                                <h4 class="modal-title"> Workflow Notification Rule </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                        <p id="message"></p>
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="formDtail">
                                            <form id="notification_form">
                                                @csrf
                                                <input type="hidden" id="job_type_id_noti" name="job_type_id_noti">
                                                <input type="hidden" id="row_id_noti" name="row_id_noti">
                                                <div class="form-group">
                                                    <label class="col-lg-3 control-label">Notify When <span class="radStar">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="checkbox" name="notify_when_on_complete" value="" id="notify_when_on_complete"> On Complete <br>
                                                        <input type="checkbox" name="notify_when_on_change" value="" id="notify_when_on_change"> On Change (Declined, Follow On, Abandoned, No Access)
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-lg-3 control-label">Notify Who? <span class="radStar">*</span></label>
                                                    <div class="col-lg-9">
                                                        <select class="form-select who_noti" name="notify_who[]" id="notify_who" multiselect-search="true" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">

                                                            <?php foreach ($customers as $cust) { ?>
                                                                <option value="{{$cust->id}}">{{$cust->name}}</option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-lg-3 control-label">Notify Customer</label>
                                                    <div class="col-lg-9">
                                                        <input type="checkbox" name="notify_customer_on_complete" id="notify_customer_on_complete" value="0"> On Complete
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-lg-3 control-label">Send As <span class="radStar">*</span></label>
                                                    <div class="col-lg-9">
                                                        <input type="checkbox" name="sendas" id="sendas" value="0"> Notification (User Only) <br>
                                                        <input type="checkbox" name="sms" id="sms" value="0"> SMS <br>
                                                        <input type="checkbox" name="emailsend" id="emailsend" value="0"> Email <br>
                                                    </div>
                                                </div>

                                        </div>
                                        <div class="noti_button">
                                            <a href="javascript:" class="btn btn-primary" onclick="get_save_notification()">Save</a>
                                            <a href="javascript:" class="btn btn-primary">Cancel</a>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div> <!-- End row -->
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
        $("#customer_visible").change(function() {
            if ($('#customer_visible').is(':checked')) {
                $("#customer_visible").val(1);
            } else {
                $("#customer_visible").val(0);
            }
        });

        function save_job_typeClose() {
            var name = $("#name").val();
            if (name == '') {
                $("#name").addClass('addError');
                return false;
            } else {
                get_save_job_type();
                $("#customerPop").modal('hide');
            }

        }

        function get_save_job_type() {
            var token = '<?php echo csrf_token(); ?>'
            var name = $("#name").val();
            var default_days = $("#default_days").val();
            var customer_visible = $("#customer_visible").val();
            var appointment_id = $("#appointment_id").val();
            var status = $("#modal_status").val();
            var home_id = '<?php echo $home_id; ?>'
            var id = $("#id").val();
            var message;
            if (id == '') {
                message = "Added Successfully Done";
            } else {
                message = "Editted Successfully Done";
            }
            if (name == '') {
                $("#name").addClass('addError');
                return false;
            } else {
                $.ajax({
                    type: "POST",
                    url: "{{url('/job_type_save')}}",
                    data: {
                        id: id,
                        home_id: home_id,
                        name: name,
                        default_days: default_days,
                        customer_visible: customer_visible,
                        appointment_id: appointment_id,
                        status: status,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if ($.trim(data.success) == "true") {
                            $("#message").text(message);
                            $(".success_message").show();
                            // $("#result").html(data.html_result);
                            setTimeout(function() {
                                $(".alert").hide();
                                location.reload();
                            }, 3000);
                            $("#form_data")[0].reset();
                        } else {
                            alert("Something went Wrong");
                        }
                    }
                });
            }
        }

        function get_model_with_id(id) {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('/job_type_edit_form')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    $("#id").val(data.id);
                    $("#name").val(data.name);
                    $("#default_days").val(data.default_days);
                    // $("#modal_status").
                    $("#modal_status").val(data.status);

                    if (data.customer_visible == 0) {
                        document.getElementById('customer_visible').checked = false;
                        document.getElementById('customer_visible').value = 0;
                    }
                    $(".appointmentType option").each(function() {
                        if ($(this).val() == data.appointment_id) {
                            $(this).prop('selected', true);
                        }
                    });
                    $("#customerPop").modal('show');
                }
            });
        }

        function status_change(id, status) {
            var token = '<?php echo csrf_token(); ?>'
            var model = "Job_type";
            $.ajax({
                type: "POST",
                url: "{{url('/status_change')}}",
                data: {
                    id: id,
                    status: status,
                    model: model,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    if ($.trim(data) == 1) {
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
        function get_flow_model(id) {
            // alert(id);
            $("#flowForm")[0].reset();
            $("#job_type_id").val(id);
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('workflow_list_job')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    $('#workflowNotificationModal').modal('hide');
                    $('#result_work_flow').html(data);
                    $('.multiselect-dropdown').hide();
                    MultiselectDropdown();
                }
            });
            $("#workflowModal").modal('show');
        }
        var rowCount = 0;

        function show_hide() {
            rowCount++;
            var job_type_id = $('#job_type_id').val();
            $('.flowhead').show();
            $('#wrokflow_msg').hide();
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('workflow_list_add')}}",
                data: {
                    job_type_id: job_type_id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    $('#workflowNotificationModal').modal('hide');
                    $('#result_work_flow').append(data);
                    $('.multiselect-dropdown').hide();
                    MultiselectDropdown();
                }
            });
            // var data='<tr><input type="hidden" value="'+rowCount+'" name="row_count"><td></td><td><select class="form-select" name="appointment_id[]" multiselect-search="false" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple"><option value="1">Install</option><option value="2">Cold Call</option><option value="3">Maintenance</option></select></td><td><a href="javascript:" class="text-primary" onclick="set_rules('+job_type_id+','+rowCount+')">Set Rule</a></td><td><a href="javascript:" class="text-danger delete-row">X</a></td></tr>';
            // $('#result_work_flow').append(data);
            // $('.multiselect-dropdown').hide();
            //     MultiselectDropdown();
        }
        var delete_ids_array = [];
        $('#result_work_flow').on('click', '.delete-row', function() {
            var row_count = $("#row_count").val();
            delete_ids_array.push(row_count);
            // console.log(delete_ids_array);
            $("#delete_ids_array").val(delete_ids_array);
            $(this).closest('tr').remove();
        });

        function set_rules(job_type_id, row_id) {
            $('#notification_form')[0].reset();
            $('#job_type_id_noti').val(job_type_id);
            $('#row_id_noti').val(row_id);
            $('#workflowNotificationModal').modal('show');
        }

        function get_save_notification() {
            var job_type_id_noti = $('#job_type_id_noti').val();
            var row_id_noti = $('#row_id_noti').val();
            // var who_noti=[];
            // $('.who_noti').each(function(){
            //     who_noti.push($(this).val());
            // });
            // who_noti=who_noti;
            // console.log(who_noti)
            if ($('#notify_when_on_complete').is(':checked')) {
                $("#notify_when_on_complete").val(1);
            }
            if ($('#notify_when_on_change').is(':checked')) {
                $("#notify_when_on_change").val(1);
            }
            if ($('#notify_customer_on_complete').is(':checked')) {
                $("#notify_customer_on_complete").val(1);
            }
            if ($('#sendas').is(':checked')) {
                $("#sendas").val(1);
            }
            if ($('#sms').is(':checked')) {
                $("#sms").val(1);
            }
            if ($('#emailsend').is(':checked')) {
                $("#emailsend").val(1);
            }
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('Workflow_notification_save')}}",
                data: new FormData($("#notification_form")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    console.log(data);
                    if ($.trim(data) == 1) {
                        $('#workflowNotificationModal').modal('hide');
                    } else {
                        alert("Something went wrong");
                    }
                }
            });
        }

        function save_work_flow() {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('/workflow_save_data')}}",
                data: new FormData($("#flowForm")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    console.log(data);
                    // location.reload();

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
            if (ids.length == 0) {
                alert("Please check the checkbox for delete");
            } else {
                if (confirm("Are you sure to delete?")) {
                    // console.log(ids);
                    var token = '<?php echo csrf_token(); ?>'
                    var model = 'Job_type';
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
</section>
@include('frontEnd.salesAndFinance.jobs.layout.footer')