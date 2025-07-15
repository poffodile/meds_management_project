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
                    <h3>Jobs Appointment Type</h3>
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
                    <a href="#!" data-bs-toggle="modal" data-bs-target="#customerPop" class="profileDrop">Add</a>
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
                                <th>Job Appointment Type </th>
                                <th>Document</th>
                                <th>Notify</th>
                                <th>Auto Authoristion</th>
                                <th>Default Duration </th>
                                <th>Status</th>
                                <th> </th>
                            </tr>
                        </thead>

                        <tbody id="result">
                            <?php foreach ($appointment_type as $key => $val) { ?>
                                <tr>
                                    <td><input type="checkbox" id="" class="delete_checkbox" value="{{$val->id}}"></td>
                                    <td>{{++$key}}</td>
                                    <td>{{$val->name}}</td>
                                    <td>No</td>
                                    <td>No</td>
                                    <td><?php echo ($val->auth == 1) ? "Yes" : "No"; ?></td>
                                    <td><?php echo $val->hours . ":" . $val->minute; ?></td>
                                    <td>
                                        <?php if ($val->status == 1) { ?>
                                            <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                        <?php } else { ?>
                                            <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown"> Action </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="javascript:void(0)" onclick="get_model_with_id({{$val->id}})" class="dropdown-item">Edit Details</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="javascript:void(0)" onclick="get_automation_model({{$val->id}})" class="dropdown-item">Set Automation</a>
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
                    <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content add_Customer">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="customerModalLabel">Job Appointment Type-Add</h5>
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
                                                <form id="form_data" class="customerForm pt-0">
                                                    <input type="hidden" name="id" id="id">
                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Job App. Type*</label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput"
                                                                id="name" name="name" value="">
                                                        </div>

                                                    </div>
                                                </div>

                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Default Duration</label>
                                                        <div class="col-sm-3">
                                                            <select name="hours" id="hours" class="hours form-control editInput selectOptions">
                                                                <option value="01">01</option>
                                                                <option value="02">02</option>
                                                                <option value="03">03</option>
                                                                <option value="04">04</option>
                                                                <option value="05">05+</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <select name="minutes" id="minutes" class="minutes form-control editInput selectOptions">
                                                                <?php for($i=0;$i<60;$i++){
                                                                    $time=str_pad($i, 2, '0', STR_PAD_LEFT);?>

                                                                    <option value="{{$time}}"><?php echo $time; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <label class="col-form-label">(Hours:Minutes)</label>
                                                        </div>
                                                        &emsp;
                                                    </div>
                                                </div>
                                                    <div class="mb-2 row">
                                                        <label class="col-sm-3 col-form-label">Auto Auth</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-group">
                                                            <input type="radio" name="radio" id="yes" checked > <label for="yes" class="col-form-label">Yes,autometically go for Authorisation when complete</label>
                                                            </div>
                                                            <div class="form-group">
                                                            <input type="radio" name="radio" id="no"> <label for="no" class="col-form-label">No go to Action Required when complete (Mobile only)</label>
                                                                <!-- <label class="form-check-label checkboxtext" for="inlineRadio1">Yes,visible to customer</label> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mb-2 row">
                                                    <label for="inputProject" class="col-sm-3 col-form-label">Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions" id="modal_status" name="status">
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
                                <button type="button" class="profileDrop" onclick="get_save_job_appointment_type()">Save</button>
                                <!-- <button type="button" class="profileDrop" onclick="save_job_typeClose()">Save & Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end here -->
                <!-- work flow Modal start here -->

                <div class="modal fade" id="workflowModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content add_Customer">
                            <div class="modal-header">
                                <h4 class="modal-title"> Set Automation </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="alert alert-success text-center success_message" style="display:none;height:50px">
                                        <p id="message_automation"></p>
                                    </div>
                                    <div class="col-md-10 col-lg-10 col-xl-10">
                                        <div class="formDtail">
                                            <form id="flowForm">
                                                @csrf
                                                <input type="hidden" name="id" id="id">
                                                <div class="custom-fieldset">
                                                    <p class="floatLeft redText marginTop8px marginBottom10"></p>
                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Document(s)</label>
                                                        <div class="col-sm-9">
                                                            <p id="message1" style="color:#dc3545;">You currently have no didgital document stored in SCITS. If ou would like to manage your documents in your system then please call us on +44(0) 151 653 1926</p>
                                                            <input type="text" class="form-control editInput" id="document" name="document" value="">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label class="col-sm-3 col-form-label">Notify?</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check form-check-inline">
                                                                <input type="radio" name="notify" id="notify_no" checked onchange="notify_show(0)">No &emsp;
                                                                <input type="radio" name="notify" id="notify_yes" onchange="notify_show(1)">Yes
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row hide_data" style="display:none">
                                                        <label class="col-sm-3 col-form-label">Notify When</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="on_complete" id="on_complete">On Complete &emsp;
                                                                <input type="checkbox" name="on_change" id="on_change">On Change (Declined, Follow On, Abandoned, No Access)
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row hide_data" style="display:none">
                                                        <label class="col-sm-3 col-form-label">Notify Who</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check">
                                                                <select class="form-select users" id="notify_who" name="notify_who[]" multiselect-search="false" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">
                                                                    <?php foreach ($users as $user) { ?>
                                                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row hide_data" style="display:none">
                                                        <label class="col-sm-3 col-form-label">Send As *</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="notification" id="notification">Notification (User Only) &emsp;
                                                                <input type="checkbox" name="sms" id="sms">SMS
                                                                <input type="checkbox" name="email" id="email">Email
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row hide_data" style="display:none">
                                                        <label class="col-sm-3 col-form-label">Notify Customer</label>
                                                        <div class="col-sm-9">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" name="notify_customer" id="notify_customer">On Complete
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="noti_button" style="margin-left:60%">
                                                    <input type="button" class="btn btn-primary" value="Save" onclick="save_automation()"></input>
                                                    <a href="javascript:void(0)" class="btn btn-primary" data-bs-dismiss="modal">Cancel</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div> 
                                <!-- End row -->
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
                get_save_job_appointment_type();
                $("#customerPop").modal('hide');
            }

        }

        function get_save_job_appointment_type() {
            var token = '<?php echo csrf_token(); ?>'
            var name = $("#name").val();
            var hours = $("#hours").val();
            var minute = $("#minutes").val();
            var auth;
            if ($('#yes').is(':checked')) {
                auth = 1;
            } else {
                auth = 0;
            }
            var status = $("#modal_status").val();
            var home_id = '<?php echo $home_id; ?>'
            var id = $("#id").val();
            var url="{{url('/job_type_appointment_save')}}";
            if(id !=''){
                url="{{url('/job_type_appointment_edit')}}";
            }
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
                    url: url,
                    data: {
                        id: id,
                        home_id: home_id,
                        name: name,
                        hours: hours,
                        minute: minute,
                        auth: auth,
                        status: status,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if ($.trim(data) == "done") {
                            $("#message").text(message);
                            $(".success_message").show();
                            setTimeout(function() {
                                $(".alert").hide();
                                window.location.reload();
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
                url: "{{url('/job_appointment_type_edit_form')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    // return false;
                    $("#id").val(data.id);
                    $("#name").val(data.name);
                    $("#hours").val(data.default_days);
                    $("#minutes").val(data.default_days);
                    $(".hours option").each(function() {
                        if ($(this).val() == data.hours) {
                            $(this).prop('selected', true);
                        }
                    });
                    $(".minutes option").each(function() {
                        if ($(this).val() == data.minute) {
                            $(this).prop('selected', true);
                        }
                    });
                    if (data.auth == 1) {
                        document.getElementById('yes').checked = true;
                        document.getElementById('no').checked = false;
                    } else {
                        document.getElementById('no').checked = true;
                        document.getElementById('yes').checked = false;
                    }

                    if (data.status == 1) {
                        $("#status_1").prop('selected', true);
                    } else {
                        $("#status_0").prop('selected', true);
                    }

                    $("#customerPop").modal('show');
                }
            });
        }

        function status_change(id, status) {
            var token = '<?php echo csrf_token(); ?>'
            var model = "Construction_job_appointment_type";
            $.ajax({
                type: "POST",
                url: "{{url('/jobAppointmentType_status_change')}}",
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
        function get_automation_model(id) {
            // alert(id);
            // $("#flowForm")[0].reset();
            // $("#job_type_id").val(id);
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('job_appointment_type_edit_form')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    // return false;
                    if (data.document != '') {
                        $('#document').css('display', 'none');
                    } else {
                        $('#message1').hide();
                    }
                    $("#id").val(data.id);
                    // $('#workflowNotificationModal').modal('hide');
                    // $('#result_work_flow').html(data);
                    // $('.multiselect-dropdown').hide();
                    // MultiselectDropdown();
                    $("#workflowModal").modal('show');
                }
            });

        }
    </script>
    <script>
        function notify_show(value) {
            if (value == 0) {
                $('.hide_data').hide();
            } else {
                $('.hide_data').show();
            }
        }

        function save_automation() {
            var token = '<?php echo csrf_token(); ?>'
            var document = $("#document").val();
            var id = $("#id").val();
            var notify;
            if ($('#notify_yes').is(':checked')) {
                notify = 1;
            } else {
                notify = 0;
            }
            var on_complete;
            if ($('#on_complete').is(':checked')) {
                on_complete = 1;
            } else {
                on_complete = 0;
            }
            var on_change;
            if ($('#on_change').is(':checked')) {
                on_change = 1;
            } else {
                on_change = 0;
            }

            var notify_who = $('.users').val();

            var notification;
            if ($('#notification').is(':checked')) {
                notification = 1;
            } else {
                notification = 0;
            }
            var sms;
            if ($('#sms').is(':checked')) {
                sms = 1;
            } else {
                sms = 0;
            }
            var email;
            if ($('#email').is(':checked')) {
                email = 1;
            } else {
                email = 0;
            }
            var notify_customer;
            if ($('#notify_customer').is(':checked')) {
                notify_customer = 1;
            } else {
                notify_customer = 0;
            }
            $.ajax({
                type: "POST",
                url: "{{url('/job_type_appointment_save')}}",
                // data: new FormData($("#flowForm")[0]),
                data: {
                    id: id,
                    document: document,
                    notify: notify,
                    on_complete: on_complete,
                    on_change: on_change,
                    notify_who: notify_who,
                    notification: notification,
                    sms: sms,
                    email: email,
                    notify_customer: notify_customer,
                    _token: token
                },
                // async: false,
                // contentType: false,
                // cache: false,
                // processData: false,
                success: function(data) {
                    console.log(data);
                    if ($.trim(data) == "done") {
                        $("#message_automation").text("Successfully Done");
                        $(".success_message").show();
                        setTimeout(function() {
                            $(".alert").hide();
                            window.location.reload();
                        }, 3000);
                        $("#form_data")[0].reset();
                    } else {
                        alert("Something went Wrong");
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
            if (ids.length == 0) {
                alert("Please check the checkbox for delete");
            } else {
                if (confirm("Are you sure to delete?")) {
                    // console.log(ids);
                    var token = '<?php echo csrf_token(); ?>'
                    var model = 'Construction_job_appointment_type';
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