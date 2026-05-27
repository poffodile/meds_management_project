<style>
    .add-on {
        float: inherit;
        padding: 0;
    }
</style>

<!-- add staff model-->
<div class="modal fade leaveCommunStyle backdropModal" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="modalTitle">Add Staff</h4>
            </div>
            <form method="post" action="{{ url('add-staff-user') }}" enctype="multipart/form-data" id='add_staff'>
                <div class="modal-body heightScrollModal">
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Name <span class="radStar">*</span></label>
                            <input type="hidden" name="staff_id" id="staff_id" value="">
                            <input type="text" name="staff_name" id="staff_name" placeholder="name" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Username <span class="radStar">*</span></label>
                            <input type="text" id="staff_user_name" name="staff_user_name" placeholder="username" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Phone Number <span class="radStar">*</span></label>
                            <input type="text" name="staff_phone_no" id="staff_phone_no" required placeholder="phone number" class="form-control" maxlength="15">
                        </div>
                        <div class="form-group col-md-16 col-sm-6 col-xs-12">
                            <label>Email <span class="radStar">*</span></label>
                            <input type="email" name="staff_email" id="staff_email" placeholder="email" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Job Title</label>
                            <input type="text" name="job_title" placeholder="job title" id="job_title" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Department</label>
                            <select class="form-control" name="department" id="department">
                                @foreach ($department as $dept)
                                <option value="{{ $dept['id'] }}">{{ $dept['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Employment Type</label>
                            <select name="employment_type" class="form-control" id="employment_type">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Status</label>
                            <select name="status" class="form-control" id="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                                <option value="2">On Leave</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Access Level</label>
                            <select name="access_level" class="form-control" id="access_level">
                                @foreach ($access_levels as $level)
                                <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Hourly Rate</label>
                            <input type="text" name="hourly_rate" id="hourly_rate" required class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <div class="overtime">
                                <label>
                                    <input type="hidden" name="available_for_overtime" value="0">
                                    <input type="checkbox" name="available_for_overtime" id="available_for_overtime" value="1"> Available for Overtime
                                </label>
                                <div class="extraHours">
                                    <label>Max extra hours per week</label>
                                    <input type="number" name="max_extra_hours" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Personal information</label>
                            <textarea name="personal_info" id="personal_info" class="form-control" rows="5" cols="20" placeholder="Personal Information" maxlength="1000"></textarea>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Bank Information</label>
                            <textarea name="banking_info" id="bank_info" class="form-control" rows="5" cols="20" placeholder="Bank Information" maxlength="1000"></textarea>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Additional Qualification Information</label>
                            <textarea name="qualification_info" id="qualification_info" class="form-control" rows="5" cols="20" placeholder="General Qualification Info (Optional)" maxlength="1000"></textarea>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" cols="20" placeholder="Short Description" maxlength="1000"></textarea>
                        </div>

                        <!-- <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Payroll</label>
                            <input type="text" name="payroll" id="payroll" placeholder="payroll" class="form-control" maxlength="255">
                        </div> -->

                        <div class="form-group col-md-16 col-sm-6 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Joining <span class="radStar">*</span></label>
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!-- dpYears -->
                                    <input name="date_of_joining" id="date_of_joining" type="text" value="" readonly="" size="16" class="form-control joining-date">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="joining-date" class="form-control date-btn2">
                                        <button class="btn allBtnUseColor" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Leaving</label>
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!-- dpYears -->
                                    <input name="date_of_leaving" id="date_of_leaving" type="text" value="" readonly="" size="16" class="form-control leaving-date">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="leaving-date" class="form-control date-btn2 ">
                                        <button class="btn allBtnUseColor" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Holiday Entitlement</label>
                            <input type="text" name="holiday_entitlement" placeholder="holiday entitlement" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Current Location <span class="radStar">*</span></label>
                            <input type="text" name="current_location" placeholder="current location" class="form-control" maxlength="255">
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-0">
                            <div class="col-md-12 p-0">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-new thumbnail" id="profile-picture" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px; font-size: 40px; color: #c7c4c4;">
                                        <img src="" alt="No Image" id="profile-picture-img" class="" />
                                    </div>
                                    <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px; font-size: 40px; color: #c7c4c4;">
                                        <!-- <img src="" alt="No Image" class="temp_img" /> -->
                                        <i class="fa fa-upload"></i>
                                    </div>
                                    <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;"></div>
                                    <div class="btn-file">
                                        <span class="btn btn-white ">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                        </span>
                                        <input name="image" type="file" class="default" id="img_upload1" maxlength="255">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-0">
                            <label>Qualification Information</label>

                            <div class="qualification p-0">
                                <div class="input_fields_wrap checkandUpload">

                                    @foreach ($courses as $key => $course)
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox"
                                                name="qualifications[{{ $course['course_id'] }}][course_id]"
                                                value="{{ $course['course_id'] }}">

                                            {{ $course['title'] }}

                                            <input type="hidden"
                                                name="qualifications[{{ $course['course_id'] }}][name]"
                                                value="{{ $course['title'] }}">
                                        </label>

                                        <!-- certificate -->
                                        <div class="btn-file p-0">
                                            <span class="">
                                                <input type="file" name="qualifications[{{ $course['course_id'] }}][cert]" class="default qual_upload" accept="application/pdf,.pdf">
                                            </span>
                                            <div class="qual-preview" id="qual-preview-{{ $course['course_id'] }}" style="margin-top:8px;"></div>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 m-t-10">
                            <label>Address</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="street" id="street" placeholder="Street">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="city" id="city" placeholder="City">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="postcode" id="postcode" placeholder="Postcode">
                                </div>
                            </div>
                        </div> -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-t-10">
                            <label>Emergency Contact</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="emergency_contact[name]" placeholder="Name">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="emergency_contact[phone_no]" placeholder="Phone">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="emergency_contact[relationship]" placeholder="Relationship">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12 m-0">
                            <label>DBS Certificate Number</label>
                            <input type="text" name="dbs_certificate_number" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12 m-0">
                            <label>DBS Expiry Date</label>
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div class="input-group date">
                                    <input type="text"
                                        name="dbs_expiry_date"
                                        class="form-control dbs-expiry-date"
                                        readonly
                                        size="16">

                                    <span class="input-group-btn add-on">
                                        <input type="text"
                                            id="dbs-expiry-picker"
                                            class="form-control date-btn2">
                                        <button class="btn allBtnUseColor" type="button">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </button>
                                    </span>
                                </div>
                            </div>

                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-t-10">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="send_credentials" value="yes" id="sign-checkbox1" maxlength="255"> Send Credentials
                                </label>
                            </div>
                        </div>
                        <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="line_manager" value="yes" id="sign-checkbox2" maxlength="255"> Assign Staff as Line Manager
                                </label>
                            </div>
                        </div> -->

                    </div>
                </div>
                <div class="modal-footer m-t-0">
                    <button class="btn btn-default cancel-btn" data-dismiss="modal" type="button"> Cancel </button>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="existing_image" id="existing_image" value="">
                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                    <button class="btn allBtnUseColor validation_staff" type="submit"> Submit </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- STAFF JOINING DATE SCRIPT -->
<script>
    $(document).ready(function() {
        // today  = new Date; 
        $('#joining-date').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            if (!currdate) return;
            var newFormat = String(currdate.getDate()).padStart(2, '0') + "-" + String(currdate.getMonth() + 1).padStart(2, '0') + "-" + currdate.getFullYear();
            $('.joining-date').val(newFormat);
            // keep the picker UI in sync
            $('#joining-date').datetimepicker('update', newFormat);

            // Constraint: Leaving date must be after joining date
            // $('#leaving-date').datetimepicker('setStartDate', currdate);

            // If existing leaving date is invalid (before new joining date), clear it
            var leavingDate = $('#leaving-date').data("datetimepicker").getDate();
            if (leavingDate && leavingDate < currdate) {
                $('.leaving-date').val('');
                $('#leaving-date').datetimepicker('update', '');
            }
        });

        $('#joining-date').on('click', function() {
            $('#joining-date').datetimepicker('show');
        });

        $("#addStaffModal").scroll(function() {
            $('#joining-date').datetimepicker('place')
        });

        $('#joining-date').on('change', function() {
            $('#joining-date').datetimepicker('hide');
        });
    });
</script>

<!-- STAFF LEAVING DATE SCRIPT -->
<script>
    $(document).ready(function() {
        // today  = new Date; 
        $('#leaving-date').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            if (!currdate) return;
            var newFormat = String(currdate.getDate()).padStart(2, '0') + "-" + String(currdate.getMonth() + 1).padStart(2, '0') + "-" + currdate.getFullYear();
            $('.leaving-date').val(newFormat);
            // keep the picker UI in sync
            $('#leaving-date').datetimepicker('update', newFormat);
        });

        $('#leaving-date').on('click', function() {
            $('#leaving-date').datetimepicker('show');
        });

        $("#addStaffModal").scroll(function() {
            $('#leaving-date').datetimepicker('place')
        });

        $('#leaving-date').on('change', function() {
            $('#leaving-date').datetimepicker('hide');
        });
    });
</script>

<!-- DBS EXPIRY DATE SCRIPT -->
<script>
    $(document).ready(function() {

        $('#dbs-expiry-picker').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2,
            autoclose: true
        }).on('change.dp', function() {

            var currdate = $(this).data("datetimepicker").getDate();

            if (!currdate) return;

            var newFormat =
                String(currdate.getDate()).padStart(2, '0') + "-" +
                String(currdate.getMonth() + 1).padStart(2, '0') + "-" +
                currdate.getFullYear();

            $('.dbs-expiry-date').val(newFormat);
        });

        $('#dbs-expiry-picker').on('click', function() {
            $(this).datetimepicker('show');
        });

        $("#addStaffModal").scroll(function() {
            $('#dbs-expiry-picker').datetimepicker('place');
        });

        $('#dbs-expiry-picker').on('change', function() {
            $(this).datetimepicker('hide');
        });

    });
</script>

<script>
    $(document).ready(function() {
        $("#img_upload1").change(function() {
            var img_name = $(this).val();
            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();
                if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
                    input = document.getElementById('img_upload1');
                    if (input.files[0].size > 2097152 || input.files[0].size < 10240) {
                        $(this).val('');
                        $("#img_upload1").removeAttr("src");
                        alert("image size should be at least 10KB and upto 2MB");
                        return false;
                    }
                } else {

                    $(this).val('');
                    alert('Please select an image .jpg, .png, .gif file format type.');
                }
            }
            return true;
        });
    });
</script>

<script>
    $(function() {
        $("#add_staff").validate({
            rules: {
                staff_email: {
                    required: true,
                    email: true
                },
                staff_name: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                },
                staff_user_name: {
                    required: true,
                    regex: /^[a-zA-Z0-9'_#@.\s]{2,40}$/,
                    remote: {
                        url: "{{ url('/check-username-exists') }}",
                        type: "post",
                        data: {
                            username: function() {
                                return $("#staff_user_name").val();
                            },
                            staff_id: function() {
                                return $("#staff_id").val(); // 👈 important
                            },
                            _token: "{{ csrf_token() }}"
                        }
                    }
                },
                staff_phone_no: {
                    required: true,
                    regex: /^[0-9 +]{10,13}$/
                },
                hourly_rate: "required",
                // image: "required",
                description: "required",
                job_title: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                },
                // payroll: {
                //     required: true,
                //     regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                // },
                holiday_entitlement: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                }
            },
            messages: {
                staff_name: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                staff_user_name: {
                    required: "This field is required.",
                    //usernameCheck:"this username is already in use.",
                    remote: "Username already exists",
                    regex: "Invalid Character"
                },
                staff_phone_no: {
                    required: "This field is required.",
                    regex: "This field must contain 10 to 13 digits"
                },
                staff_email: {
                    required: "This field is required.",
                    regex: ""
                },
                job_title: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                // payroll: {
                //     required: "This field is required.",
                //     regex: "Invalid Character"
                // },
                holiday_entitlement: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                // image: "This field is required.",
                description: "This field is required.",
                hourly_rate: "This field is required",
            },
            submitHandler: function(form) {
                form.submit();
            }
        })
        return false;
    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.cancel-btn', function() {
            $('#add_staff').find('input').val('');
            $('#add_staff').find('textarea').val('');
            $('#add_staff').find('img').attr('src', '');
            $('label.error').hide();
            $("#sign-checkbox1").attr('checked', false);
            $("#sign-checkbox2").attr('checked', false);

            var token = "{{ csrf_token() }}";
            $('input[name=\'_token\']').val(token);
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {

        var max_fields = 10; //maximum input boxes allowed
        var wrapper = $(".input_fields_wrap"); //Fields wrapper
        var add_button = $(".add_field"); //Add button ID

        var x = 1; //initlal text box count
        $(add_button).unbind().click(function(e) { //on add input button click
            e.preventDefault();
            if (x < max_fields) { //max input box allowed
                x++; //text box increment
                //alert(x);
                $(wrapper).append(`<div class="appended-whole-div">
                                        <div class="multi-upload  row">
                                            <div class="col-md-8 form-group">
                                                <input type="text" name="qualification[]" class="form-control" />
                                            </div>
                                            <div class="col-md-4 form-group">
                                                <div class="input-group">
                                                        <span class="btn btn-white btn-file">
                                                           <span class="fileupload-new"><i class="fa fa-upload"></i> Upload
                                                           </span>
                                                           <input name="qualifiaction_cert[]" class="default qual_upload" type="file"  accept="application/pdf,.pdf">
                                                        </span>
                                                        <span class="input-group-addon remove-addon"><a href="#" class="remove_field btn btn-danger">Remove</a>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`); //add input box
            }
        });

        $(wrapper).on("click", ".remove_field", function(e) { //user click on remove text
            e.preventDefault();
            $(this).closest('.appended-whole-div').remove();
            x--;
            //$(this).parent();

        })
    });
</script>

<script>
    $(document).ready(function() {
        // $(".qual_upload").change(function()
        $(document).on('change', '.qual_upload', function() {
            var img_name = $(this).val();
            // alert(img_name);
            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();
                if (ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'pdf' || ext == 'doc' || ext == 'docx') {
                    input = document.getElementsByClassName('qual_upload');
                    // if(input.files[0].size > 2097152 || input.files[0].size <  10240)
                    // {
                    //   $(this).val('');
                    //   $(".qual_upload").removeAttr("src");
                    //   alert("file size should be at least 10KB and upto 2MB");
                    //   return false;
                    // }
                } else {
                    $(this).val('');
                    alert('Please select an image .jpg, jpeg, .png, .pdf. .doc, .docx, .gif file format type.');
                }
            }
            return true;
        });
    });
</script>

<script>
    function initOvertimeToggle() {
        document.querySelectorAll('.overtime').forEach(wrapper => {

            const checkbox = wrapper.querySelector('input[type="checkbox"]');
            const extraHours = wrapper.querySelector('.extraHours');

            if (!checkbox || !extraHours) return;

            extraHours.style.display = checkbox.checked ? 'block' : 'none';

            checkbox.onchange = function() {
                extraHours.style.display = this.checked ? 'block' : 'none';
            };
        });
    }

    // page load
    document.addEventListener('DOMContentLoaded', initOvertimeToggle);

    // modal open (EDIT) - bind to the actual modal id
    $('#addStaffModal').on('shown.bs.modal', initOvertimeToggle);
</script>

<script>
    $(document).on('click', '.openStaffModal', function() {
        const $btn = $(this);
        const mode = $btn.attr('data-mode') || $btn.data('mode');

        // Reset form and qualification checkboxes/dynamic fields
        $('#add_staff')[0].reset();
        $('input[name^="qualifications"]').prop('checked', false);
        $('.appended-whole-div').remove();
        // clear any existing qualification previews left from previous edit
        $('.qual-preview').empty();
        // clear any qualification file inputs
        $('.qual_upload').val('');
        // reset existing image/remove flags and profile preview
        $('#existing_image').val('');
        $('#remove_image').val('0');
        $('#profile-picture-img').attr('src', '');
        if (document.getElementById('profile-picture')) document.getElementById('profile-picture').style.display = 'none';

        // remove any existing hidden method input
        $('#formMethod').remove();

        if (mode === 'add') {
            let accessLevelId = $('#access_level').val();
            fetchHourlyRate(accessLevelId);
            $('#modalTitle').text('Add Staff');
            $('#add_staff').attr('action', "{{ url('add-staff-user') }}");
            $('#staff_id').val('');
            document.getElementById('profile-picture').style.display = 'none';
        } else {
            $('#modalTitle').text('Edit Staff');
            const staffId = $btn.attr('data-id') || $btn.data('id');
            // set form action at runtime so staffId is appended correctly
            $('#add_staff').attr('action', '{{ url("roster/carer-update") }}/' + staffId);

            // qualifications may be passed as JSON in data-qualifications
            let qualifications = [];
            const qualAttr = $btn.attr('data-qualifications');
            if (qualAttr) {
                try {
                    qualifications = JSON.parse(qualAttr);
                } catch (e) {
                    qualifications = $btn.data('qualifications') || [];
                }
            } else {
                qualifications = $btn.data('qualifications') || [];
            }


            if (Array.isArray(qualifications)) {
                qualifications.forEach(q => {
                    // Match qualification by course_id
                    const courseId = q.course_id || q.courseId || q.id;
                    if (!courseId) return; // Skip if no course_id

                    const selector = 'input[name="qualifications[' + courseId + '][course_id]"]';
                    const checkboxEl = $(selector);

                    // Only check if the checkbox exists (course is available in form)
                    if (checkboxEl.length > 0) {
                        checkboxEl.prop('checked', true);

                        // If a certificate path exists, render preview
                        let img = q.image || q.cert || q.filename || q.file || q.path || null;
                        const previewEl = $('#qual-preview-' + courseId);

                        if (img && previewEl.length) {
                            let url = String(img);

                            // BASE URL (Laravel public folder)
                            // const BASE_URL = window.location.origin + '/socialcareitsolutions/public/images/userQualification/';
                            const BASE_URL = "{{ asset('public/images/userQualification') }}/";

                            // normalize path
                            if (url.startsWith('http')) {
                                // already full URL → do nothing
                            } else if (url.startsWith('public/')) {
                                url = BASE_URL + url.replace('public/', '');
                            } else if (url.startsWith('/')) {
                                url = BASE_URL + url.replace('/', '');
                            } else {
                                url = BASE_URL + url;
                            }

                            const lower = url.toLowerCase();
                            if (lower.endsWith('.pdf')) {
                                previewEl.html('<a href="' + url + '" target="_blank" class="btn btn-default">View PDF</a>');
                            } else {
                                previewEl.html('<a href="' + url + '" target="_blank">View file</a>');
                            }
                        }
                    }
                });
            }


            // add hidden _method input for PUT
            $('#add_staff').append('<input type="hidden" name="_method" id="formMethod" value="PUT">');

            $('#staff_id').val(staffId);
            $('#staff_name').val($btn.attr('data-name') || $btn.data('name'));
            $('#staff_user_name').val($btn.attr('data-username') || $btn.data('username'));
            $('#staff_phone_no').val($btn.attr('data-phone') || $btn.data('phone'));
            $('#staff_email').val($btn.attr('data-email') || $btn.data('email'));
            $('#job_title').val($btn.attr('data-job-title') || $btn.data('jobTitle') || $btn.data('job-title'));
            $('input[name="current_location"]').val($btn.attr('data-current-location') || $btn.data('current-location') || $btn.attr('data-current_location') || $btn.data('current_location'));
            $('#department').val($btn.attr('data-department') || $btn.data('department')).trigger('change');
            $('#employment_type').val($btn.attr('data-employment-type') || $btn.data('employmentType') || $btn.data('employment-type')).trigger('change');
            $('#status').val($btn.attr('data-status') || $btn.data('status')).trigger('change');

            const overtimeRaw =
                $btn.attr('data-overtime-availability') ??
                $btn.data('overtimeAvailability') ??
                $btn.data('overtime-availability');

            const maxHoursRaw =
                $btn.attr('data-max-extra-hours') ??
                $btn.attr('data-max_extra_hours') ??
                $btn.data('maxExtraHours') ??
                $btn.data('max-extra-hours');

            // Normalize values
            const overtime = Number(overtimeRaw) === 1;
            const maxHours = Number(maxHoursRaw);

            // Final condition
            const shouldCheck = overtime || maxHours > 0;

            $('input[name="available_for_overtime"]').prop('checked', shouldCheck);

            // ensure toggle runs after setting the checkbox
            initOvertimeToggle();

            let accessLevel = $btn.data('access-level');
            if (accessLevel !== undefined && accessLevel !== null && accessLevel !== '') {
                $('#access_level').val(accessLevel);
            }

            let hourlyRate = $btn.data('hourly-rate'); // preferred jQuery way

            if (hourlyRate !== undefined && hourlyRate !== null && hourlyRate !== '') {
                $('#hourly_rate').val(hourlyRate);
            } else {
                // Trigger change to fetch hourly rate via AJAX
                $('#access_level').trigger('change');
            }


            // set the max extra hours value
            $('input[name="max_extra_hours"]').val(maxHours);

            // trigger change so the extraHours container shows when we've programmatically checked the box
            $('input[name="available_for_overtime"]').trigger('change');
            $('#description').val($btn.attr('data-description') || $btn.data('description'));
            // $('input[name="payroll"]').val($btn.attr('data-payroll') || $btn.data('payroll'));
            $('input[name="holiday_entitlement"]').val($btn.attr('holiday-entitlement') || $btn.data('holiday_entitlement') || $btn.data('holidayEntitlement'));

            $('#personal_info').val($btn.attr('data-personal-info') || $btn.data('personal-info') || '');
            $('#bank_info').val($btn.attr('data-banking-info') || $btn.data('banking-info') || '');
            $('#qualification_info').val($btn.attr('data-qualification-info') || $btn.data('qualification-info') || '');

            // Populate emergency contact fields (support multiple data-attribute naming conventions)
            $('input[name="emergency_contact[name]"]').val(
                $btn.attr('data-emergency_contact_name') || $btn.data('emergency_contact_name') || $btn.data('emergencyContactName') || ''
            );
            $('input[name="emergency_contact[phone_no]"]').val(
                $btn.attr('data-emergency_contact_phone') || $btn.data('emergency_contact_phone') || $btn.data('emergencyContactPhone') || ''
            );
            $('input[name="emergency_contact[relationship]"]').val(
                $btn.attr('data-emergency_contact_relationship') || $btn.data('emergency_contact_relationship') || $btn.data('emergencyContactRelationship') || ''
            );
            let profile_img = $btn.attr('data-image') || $btn.data('image') || '';
            const BASE_URL = window.location.origin + '/socialcareitsolutions/public/images/userProfileImages/';
            document.getElementById('profile-picture').style.display = 'block';
            // populate hidden existing_image field so backend can decide whether to keep it
            $('#existing_image').val(profile_img ? profile_img : '');
            $('#remove_image').val('0');
            $('#profile-picture-img').attr('src', profile_img ? (String(profile_img).startsWith('http') ? profile_img : BASE_URL + profile_img) : '');

            function formatDateToDDMMYYYY(dateStr) {
                if (!dateStr) return '';
                dateStr = String(dateStr).trim();

                // already dd-mm-yyyy
                if (dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                    return dateStr;
                }

                // YYYY-MM-DD or YYYY-MM-DDTHH... → dd-mm-yyyy
                var iso = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (iso) {
                    return iso[3] + '-' + iso[2] + '-' + iso[1];
                }

                // common slash format DD/MM/YYYY or D/M/YYYY
                var slash = dateStr.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (slash) {
                    var dd = String(slash[1]).padStart(2, '0');
                    var mm = String(slash[2]).padStart(2, '0');
                    return dd + '-' + mm + '-' + slash[3];
                }

                // weird format like "29T00:00:00.000000Z-12-2025" or parts joined with '-'
                var parts = dateStr.split('-');
                if (parts.length >= 3) {
                    var year = parts[parts.length - 1];
                    var month = parts[parts.length - 2];
                    var dayPart = parts.slice(0, parts.length - 2).join('-');
                    var day = (dayPart.split('T')[0] || '').replace(/[^0-9]/g, '');
                    if (/^\d{1,2}$/.test(day) && /^\d{1,2}$/.test(month) && /^\d{4}$/.test(year)) {
                        return String(day).padStart(2, '0') + '-' + String(month).padStart(2, '0') + '-' + year;
                    }
                }

                // fallback: try Date parse
                var d = new Date(dateStr);
                if (!isNaN(d)) {
                    var dd = String(d.getDate()).padStart(2, '0');
                    var mm = String(d.getMonth() + 1).padStart(2, '0');
                    var yyyy = d.getFullYear();
                    return dd + '-' + mm + '-' + yyyy;
                }

                return '';
            }

            // prefer the stored $btn reference — $(this) inside this scope may not be the button
            var joiningDate = $btn.attr('data-date-of-joining') || $btn.data('date-of-joining') || $btn.data('dateOfJoining') || $btn.data('dateOfJoining') || '';
            var leavingDate = $btn.attr('data-date-of-leaving') || $btn.data('date-of-leaving') || $btn.data('dateOfLeaving') || $btn.data('dateOfLeaving') || '';

            // Convert format
            joiningDate = formatDateToDDMMYYYY(joiningDate);

            // Set values in readonly inputs
            $('#date_of_joining').val(joiningDate);

            if (leavingDate && leavingDate !== 'null' && leavingDate !== '0000-00-00 00:00:00' && leavingDate !== '1970-01-01') {
                leavingDate = formatDateToDDMMYYYY(leavingDate);
                $('#date_of_leaving').val(leavingDate);
            } else {
                $('#date_of_leaving').val('');
            }

            // Use datepicker/datetimepicker update APIs so the pickers format dates consistently
            function setPickerDate(pickerSelector, readonlySelector, dateStr) {
                // If no date provided, clear both pickers
                if (!dateStr) {
                    try {
                        $(pickerSelector).datetimepicker('update', '');
                    } catch (e) {}
                    try {
                        $(readonlySelector).datepicker('update', '');
                    } catch (e) {}
                    return;
                }

                // dateStr expected as dd-mm-yyyy (from formatDateToDDMMYYYY)
                var parts = String(dateStr).split('-');
                var dt = null;
                if (parts.length === 3) {
                    var dd = Number(parts[0]);
                    var mm = Number(parts[1]);
                    var yyyy = Number(parts[2]);
                    dt = new Date(yyyy, mm - 1, dd);
                } else {
                    // try Date parse fallback
                    var parsed = new Date(dateStr);
                    if (!isNaN(parsed)) dt = parsed;
                }

                // Use pickers' update with a Date object so they render using their own `dd-mm-yyyy` config
                if (dt) {
                    try {
                        $(pickerSelector).datetimepicker('update', dt);
                    } catch (e) {}
                    try {
                        $(readonlySelector).datepicker('update', dt);
                    } catch (e) {}
                } else {
                    // final fallback: clear
                    try {
                        $(pickerSelector).datetimepicker('update', '');
                    } catch (e) {}
                    try {
                        $(readonlySelector).datepicker('update', '');
                    } catch (e) {}
                }
            }

            // Update both picker inputs via their APIs (ensures configured format is used)
            setPickerDate('#joining-date', '#date_of_joining', joiningDate);
            setPickerDate('#leaving-date', '#date_of_leaving', leavingDate);

            // Enforce constraints on edit load
            var currentJoining = $('#joining-date').data("datetimepicker").getDate();
            if (currentJoining) {
                $('#leaving-date').datetimepicker('setStartDate', currentJoining);
            } else {
                $('#leaving-date').datetimepicker('setStartDate', null);
            }

            function parseDBSDate(dateStr) {
                if (!dateStr) return null;
                dateStr = String(dateStr).trim();

                // use same formatting helpers as above — try to extract dd,mm,yyyy
                // handle ISO-like prefixes
                var iso = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (iso) return new Date(iso[1], iso[2] - 1, iso[3]);

                // handle weird hyphenated form where day part contains T
                var parts = dateStr.split('-');
                if (parts.length >= 3) {
                    var year = parts[parts.length - 1];
                    var month = parts[parts.length - 2];
                    var dayPart = parts.slice(0, parts.length - 2).join('-');
                    var day = (dayPart.split('T')[0] || '').replace(/[^0-9]/g, '');
                    if (/^\d{1,2}$/.test(day) && /^\d{1,2}$/.test(month) && /^\d{4}$/.test(year)) {
                        return new Date(Number(year), Number(month) - 1, Number(day));
                    }
                }

                // try Date parse fallback
                var d = new Date(dateStr);
                if (!isNaN(d)) return d;

                return null;
            }
            var dbsExpiryDateStr = $btn.attr('data-dbs_expiry_date') || $btn.data('dbs_expiry_date') || $btn.data('dbsExpiryDate') || $btn.data('dbs-expiry-date') || '';
            var dbsExpiryDate = parseDBSDate(dbsExpiryDateStr);
            if (dbsExpiryDate) {
                var day = String(dbsExpiryDate.getDate()).padStart(2, '0');
                var month = String(dbsExpiryDate.getMonth() + 1).padStart(2, '0');
                var year = dbsExpiryDate.getFullYear();
                var formattedDBSDate = day + '-' + month + '-' + year;

                // Set values in readonly input
                $('.dbs-expiry-date').val(formattedDBSDate);
                // Update bootstrap datepicker
                $('.dbs-expiry-date').datepicker('update', formattedDBSDate);
            } else {
                $('.dbs-expiry-date').val('');
            }

            $('input[name="dbs_certificate_number"]').val($btn.attr('data-dbs_certificate_number') || $btn.data('dbs_certificate_number') || $btn.data('dbsCertificateNumber'));

        }

        $('#addStaffModal').modal('show');
    });
</script>

<script>
    $(document).ready(function() {
        // 🔁 On access level change
        $('#access_level').on('change', function() {
            fetchHourlyRate($(this).val());
        });
    });

    $(document).ready(function() {
        $('.backdropModal').modal({
            backdrop: false,
            keyboard: true,
            show: false
        });
    });

    function fetchHourlyRate(accessLevelId) {
        if (!accessLevelId) {
            $('#hourly_rate').val('');
            return;
        }

        $.ajax({
            url: "{{ url('/roster/carer/get-hourly-rate') }}",
            type: "POST",
            data: {
                access_level_id: accessLevelId,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.hourly_rate !== undefined) {
                    $('#hourly_rate').val(response.hourly_rate);
                } else {
                    $('#hourly_rate').val('');
                }
            },
            error: function() {
                $('#hourly_rate').val('');
            }
        });
    }
</script>