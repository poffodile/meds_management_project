<!-- <script src="http://localhost/scits/public/frontEnd/js/jquery.js"></script> -->
<!--   <script src="http://localhost/scits/public/frontEnd/js/jquery.validate.js"></script> -->
<!-- add staff model-->
<div class="modal fade leaveCommunStyle" id="addStaffModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Staff</h4>
            </div>
            <form method="post" action="{{ url('add-staff-user') }}" enctype="multipart/form-data" id='add_staff'>
                <div class="modal-body heightScrollModal">
                    <div class="row">
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Name</label>
                            <input type="text" name="staff_name" placeholder="name" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Username</label>
                            <input type="text" id="staff_user_name" name="staff_user_name" placeholder="username" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Phone Number</label>
                            <input type="text" name="staff_phone_no" required placeholder="phone number" class="form-control" maxlength="15">
                        </div>
                        <div class="form-group col-md-16 col-sm-6 col-xs-12">
                            <label>Email</label>
                            <input type="email" name="staff_email" placeholder="email" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Job Title</label>
                            <input type="text" name="job_title" placeholder="job title" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Department</label>
                            <select class="form-control" name="department" id="">
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

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <div class="overtime">
                                <label>
                                    <input type="checkbox" name="available_for_overtime" value="1"> Available for Overtime
                                </label>
                                <div class="extraHours">
                                    <label>Max extra hours per week</label>
                                    <input type="number" name="max_extra_hours" class="form-control">
                                </div>
                            </div>
                        </div>

                        {{-- <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <h4>Qualifications</h4>
                            <div class="checkbox-grid">
                                @foreach ($courses as $course)
                                    <label><input type="checkbox" name="qualifications[]" value="{{ $course['course_id'] }}"> {{ $course['title'] }}</label>
                                @endforeach
                            </div>
                        </div> --}}

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" cols="20" placeholder="Short Description" maxlength="1000"></textarea>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Payroll</label>
                            <input type="text" name="payroll" placeholder="payroll" class="form-control" maxlength="255">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Holiday Entitlement</label>
                            <input type="text" name="holiday_entitlement" placeholder="holiday entitlement" class="form-control" maxlength="255">
                        </div>

                        <div class="form-group col-md-16 col-sm-6 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Joining</label>
                            <!-- <input name="date_of_birth" required class="form-control default-date-picker" type="text" value="" />
                            <span class="input-group-btn add-on">
                                <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                            </span> -->

                            <!-- <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date datetime-picker">
                                <input name="date_of_birth" type="text" readonly value="" size="16" class="form-control">
                                <span class="input-group-btn add-on">
                                    <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div> -->
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!-- dpYears -->
                                    <input name="date_of_joining" type="text" value="" readonly="" size="16" class="form-control joining-date">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="joining-date" class="form-control date-btn2">
                                        <button class="btn allBtnUseColor" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Leaving</label>
                            <!-- <input name="date_of_birth" required class="form-control default-date-picker" type="text" value="" />
                            <span class="input-group-btn add-on">
                                <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                            </span> -->

                            <!-- <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date datetime-picker">
                                <input name="date_of_birth" type="text" readonly value="" size="16" class="form-control">
                                <span class="input-group-btn add-on">
                                    <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                </span>
                            </div> -->
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!-- dpYears -->
                                    <input name="date_of_leaving" type="text" value="" readonly="" size="16" class="form-control leaving-date">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="leaving-date" class="form-control date-btn2 ">
                                        <button class="btn allBtnUseColor" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-0">
                            <div class="col-md-12 p-0">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
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
                                                    <!-- btn btn-white btn-file -->
                                                    <!-- <i class="fa fa-upload"></i> Upload -->
                                                    <input type="file" name="qualifications[{{ $course['course_id'] }}][cert]" class="default qual_upload">
                                                </span>
                                            </div>

                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>



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
                            <input type="date" name="dbs_expiry_date" class="form-control">
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
                    <button class="btn allBtnUseColor validation_staff" type="submit"> Submit </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- STAFF JOINING DATE SCRIPT --><!-- STAFF JOINING DATE SCRIPT --><!-- STAFF JOINING DATE SCRIPT -->
<script>
    $(document).ready(function() {

        // today  = new Date; 
        $('#joining-date').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            var newFormat = currdate.getDate() + "-" + (currdate.getMonth() + 1) + "-" + currdate.getFullYear();
            $('.joining-date').val(newFormat);
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

<!-- STAFF LEAVING DATE SCRIPT --><!-- STAFF LEAVING DATE SCRIPT --><!-- STAFF LEAVING DATE SCRIPT -->
<script>
    $(document).ready(function() {
        // today  = new Date; 
        $('#leaving-date').datetimepicker({
            format: 'dd-mm-yyyy',
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            var newFormat = currdate.getDate() + "-" + (currdate.getMonth() + 1) + "-" + currdate.getFullYear();
            $('.leaving-date').val(newFormat);
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
                    remote: "{{ url('/check-username-exists') }}",
                    regex: /^[a-zA-Z0-9'_#@.\s]{2,40}$/
                },
                staff_phone_no: {
                    required: true,
                    regex: /^[0-9 +]{10,13}$/
                },
                image: "required",
                description: "required",
                job_title: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                },
                payroll: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                },
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
                payroll: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                holiday_entitlement: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                image: "This field is required.",
                description: "This field is required.",
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
                                                           <input name="qualifiaction_cert[]" class="default qual_upload" type="file">
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
    document.querySelectorAll('.overtime').forEach(wrapper => {

        const checkbox = wrapper.querySelector('input[type="checkbox"]');
        const extraHours = wrapper.querySelector('.extraHours');

        checkbox.addEventListener('change', function() {
            extraHours.style.display = this.checked ? 'block' : 'none';
        });

    });
</script>
