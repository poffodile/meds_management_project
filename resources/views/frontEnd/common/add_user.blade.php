<?php
$department = App\Models\CompanyDepartment::getActiveCompanyDepartment();

$childSection = App\Models\ChildSection::where(['home_id' => Auth::user()->home_id, 'status' => 1])->whereNull('deleted_at')->get();
?>

<div class="modal fade leaveCommunStyle" id="addServiceUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-user-btn" data-dismiss="modal"
                    aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="ClientModalTitle">Add Client</h4>
            </div>

            <form method="post" action="{{ url('add-service-user') }}" enctype="multipart/form-data" id='add_service_user'>
                <input type="hidden" id="suClientId" name="suClientId">
                <div class="modal-body heightScrollModal">
                    <div class="row">
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label>Full Name *</label>
                            <input type="text" name="su_name" id="su_name" required placeholder="name" class="form-control">
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12">
                            <label>Username *</label>
                            <input type="text" name="su_user_name" id="su_user_name" required placeholder="username" class="form-control">
                        </div>
                        <div class="form-group col-md-4 col-sm-6 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Birth</label>

                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date"> <!-- dpYears -->
                                    <input name="date_of_birth" id="date_of_birth" type="text" value="" autocomplete="off" readonly="" size="16" class="form-control date-pick-su" required>
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="new-date-su" autocomplete="off" class="form-control date-btn2">
                                        <button class="btn allBtnUseColor" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Phone Number *</label>
                            <input type="text" name="phone_no" id="phone_no" required placeholder="phone number" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Hair and Eyes *</label>
                            <input type="text" name="hair_and_eyes" id="hair_and_eyes" required placeholder="hair and eyes"
                                class="form-control">
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Markings *</label>
                            <input type="text" name="markings" id="markings" required placeholder="markings"
                                class="form-control">
                        </div>

                        <div class="form-group col-md-6 col-sm-64 col-xs-12">
                            <label>Start Date</label>
                            <input type="text" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>End Date</label>
                            <input type="text" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Email *</label>
                            <input type="email" name="email" id="suEmail" required placeholder="email" class="form-control">
                        </div>

                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Department *</label>
                            <div class="select-style">
                                <select name="department" id="department" class="form-control" required>
                                    @foreach($department as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Course</label>
                            <div class="select-style">
                                <select name="childCourse" id="childCourse" class="form-control">
                                </select>
                            </div>
                            <input type="hidden" name="chiledCoursenumber" id="chiledCoursenumber">
                            <input type="hidden" name="childCourseLevel" id="childCourseLevel">
                            <input type="hidden" name="childCourseImage" id="childCourseImage">
                            <input type="hidden" name="childCourseDescription" id="childCourseDescription">
                        </div> -->

                        <!-- <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Weekly Rate</label>
                            <input type="text" name="weekly_rate" id="weekly_rate" placeholder="Weekly Rate" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-sm-4 col-xs-12">
                            <label>Subs</label>
                            <input type="text" name="subs" id="subs" placeholder="Subs" class="form-control">
                        </div>
                        <div class="form-group col-md-6 col-sm-4 col-xs-12">
                            <label>Extra</label>
                            <input type="text" name="extra" id="extra" placeholder="Extra" class="form-control">
                        </div> -->

                        <!-- <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Local Authority</label>
                            <input type="text" name="local_authority" id="local_authority" required placeholder="Local Authority"
                                class="form-control">
                        </div> -->
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Admission Number *</label>
                            <input type="text" name="admission_number" id="admission_number" required placeholder="admission number" class="form-control">
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Status</label>
                            <div class="select-style">
                                <select id="suStatus" name="suStatus" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Mobility</label>
                            <div class="select-style">
                                <select id="suMobility" name="suMobility" class="form-control">
                                    <option value="Independent">Independent</option>
                                    <option value="Requires Assistance">Requires Assistance</option>
                                    <option value="Wheelchair User">Wheelchair User</option>
                                    <option value="Bed Bound">Bed Bound</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4 col-sm-4 col-xs-12">
                            <label>Funding Type</label>
                            <div class="select-style">
                                <select id="suFundingType" name="suFundingType" class="form-control">
                                    <option value="Local Authority">Local Authority</option>
                                    <option value="Self Funded">Self Funded</option>
                                    <option value="NHS">NHS</option>
                                    <option value="Mixed">Mixed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <label>Section *</label>
                            <select class="form-control" name="section" id="section" required>
                                @foreach($childSection as $sectionVal)
                                <option value="{{$sectionVal->id}}">{{$sectionVal->section}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-sm-6 col-xs-12">
                            <?php
                            $su_ethnicity = App\Ethnicity::select('id', 'name')->where('is_deleted', '0')->get()->toArray();
                            ?>
                            <label>Ethnicity *</label>
                            <div class="select-style">
                                <select name="ethnicity_id" class="" id="ethnicity_id" required>
                                    <option value="0" disabled> Select Ethnicity </option>
                                    @foreach ($su_ethnicity as $key => $value)
                                    <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Short Description *</label>
                            <textarea name="short_description" id="short_description" required class="form-control" rows="3" cols="20" placeholder="Short Descriptiion"></textarea>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Address *</label>
                            <div class="row ">
                                <div class="col-lg-4">
                                    <input type="text" name="street" id="street" placeholder="Street" class="form-control" required>
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" name="city" id="city" placeholder="City" class="form-control" required>
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" name="postcode" id="postcode" placeholder="Postcode" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Care Needs (comma separated)</label>
                            <textarea name="care_needs" id="care_needs" class="form-control" rows="3" cols="20" placeholder="e.g., Personal care, Medication management, Meal preparation"></textarea>
                        </div>

                        <div class="col-md-12">
                            <div class="carer-form">
                                <div class="qualifications">
                                    <h4>Preferred Services (for continuity of care) *</h4>
                                    <div class="checkbox-grid su_usercheckbox-grid">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Medical Notes</label>
                            <textarea name="medical_notes" id="medical_notes" class="form-control" rows="3" cols="20" placeholder="Important medical information"></textarea>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Emergency Contact *</label>
                            <div class="row ">
                                <div class="col-lg-4">
                                    <input type="text" name="em_name" id="em_name" placeholder="Name" class="form-control" required>
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" name="em_phone" id="em_phone" placeholder="phone" class="form-control" required onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" name="relationship" id="relationship" placeholder="Relationship" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Height *</label>
                            <div class="row ">
                                <div class="col-lg-4">
                                    <select class="form-control" id="height_unit" name="height_unit" required>
                                        <option value="ft_in">Feet/in</option>
                                        <option value="cm">CM</option>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <select name="height_ft" id="height_dropdown" class="form-control" required>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <div id="inch_div">
                                        <select name="height_in" id="height_in_dropdown" class="form-control" required>
                                            <option value="">Select</option>
                                            @for ($i = 0; $i < 12; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Weight *</label>
                            <div class="row ">
                                <div class="col-lg-6">
                                    <select name="weight_unit" id="weight_unit" class="form-control" required>
                                        <option value="kg">Kilograms (kg)</option>
                                        <option value="lbs">Pounds (lbs)</option>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <select name="weight" id="weight_dropdown" class="form-control" required>
                                        <option value="">-- Select Weight --</option>
                                        <!-- Options will be filled dynamically by JS -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-0">
                            <div class="col-md-12 p-0">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-new thumbnail"
                                        style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px; font-size: 40px;
    color: #c7c4c4;">
                                        <!-- <img src="" alt="No Image" /> -->
                                        <i class="fa fa-upload"></i>
                                    </div>
                                    <div class="fileupload-preview fileupload-exists thumbnail"
                                        style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 20px;">
                                    </div>
                                    <div>
                                        <span class="btn btn-white btn-file">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select
                                                image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                            <input name="img_upload" type="file" class="default"
                                                id="img_upload" />
                                        </span>
                                        <!-- <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload"><i class="fa fa-trash"></i>Remove</a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <div class="overtime">
                                <label>
                                    <input type="checkbox" name="send_credentials" value="yes" id="sign-checkbox1" maxlength="255"> Send Credentials
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-0">
                    <button class="btn btn-default cancel-user-btn" data-dismiss="modal" type="button"> Cancel
                    </button>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn allBtnUseColor image_val" type="submit" id="clientFormSaveBtn"> Create Client </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
    $(document).ready(function() {
        var today = new Date;
        $('#new-date-su').datetimepicker({
            format: 'dd-mm-yyyy',
            endDate: today,
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            var newFormat = currdate.getDate() + "-" + (currdate.getMonth() + 1) + "-" + currdate
                .getFullYear();
            $('.date-pick-su').val(newFormat);
        });

        $('#new-date-su').on('click', function() {
            $('#new-date-su').datetimepicker('show');
        });

        $("#addServiceUserModal").scroll(function() {
            $('#new-date-su').datetimepicker('place');
            $('#start_date').datepicker('place');
            $('#end_date').datepicker('place');
        });

        $('#new-date-su').on('change', function() {
            $('#new-date-su').datetimepicker('hide');
        });

        $('#start_date').datepicker({
            format: 'dd-mm-yyyy'
        });
        $('#start_date').on('change', function() {
            $('#start_date').datepicker('hide');
        });

        $('#end_date').datepicker({
            format: 'dd-mm-yyyy'
        });

        $('#end_date').on('change', function() {
            $('#end_date').datepicker('hide');
        });
    });
</script>


<script>
    $(document).ready(function() {
        $("#img_upload").change(function() {
            var img_name = $(this).val();
            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();
                if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
                    input = document.getElementById('img_upload');
                    if (input.files[0].size > 2097152 || input.files[0].size < 10240) {
                        $(this).val('');
                        $("#img_upload").removeAttr("src");
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

<!-- <script>
    $(function() {
        $("#add_service_user").validate({
            rules: {

                email: {
                    required: true,
                    email: true
                },
                su_name: {
                    required: true,
                    regex: /^[a-zA-Z0-9'.\s]{1,40}$/
                },
                su_user_name: {
                    required: true,
                    regex: /^[a-zA-Z0-9'_#@.\s]{2,40}$/,
                    remote: "{{ url('/check-username-exists') }}"
                    //remote: "{{ url('user/check-su-username-exists') }}"
                },
                image: "required",
                date_of_birth: "required",
                phone_no: {
                    required: true,
                    regex: /^[0-9 +]{10,13}$/
                },
                section: "required",
                admission_number: "required",
                short_description: "required",
                height: "required",
                weight: "required",
                hair_and_eyes: "required",
                markings: "required",
            },
            messages: {
                su_name: {
                    required: "This field is required.",
                    regex: "Invalid Character",
                    remote: "Username already exists",
                },
                su_user_name: {
                    required: "This field is required.",
                    remote: "Username already exists",
                    regex: "Invalid Character"
                },
                // email: "This field is required.",
                email: {
                    required: "This field is required.",
                    regex: "Please enter a valid email address.",
                },
                image: "This field is required.",
                date_of_birth: "This field is required.",
                phone_no: {
                    required: "This field is required.",
                    regex: "Invalid Character"
                },
                section: "This field is required.",
                admission_number: "This field is required.",
                short_description: "This field is required.",
                height: "This field is required.",
                weight: "This field is required.",
                hair_and_eyes: "This field is required.",
                markings: "This field is required.",
            },
            submitHandler: function(form) {
                form.submit();
            }
        })
        return false;
    });
</script> -->

<script>
    $(document).ready(function() {
        $('.cancel-user-btn').click(function() {
            $('#add_service_user').find('input').val('');
            $('#add_service_user').find('textarea').val('');
            $('label.error').hide();
            $('#add_service_user').find('img').attr('src', '');

            var token = "{{ csrf_token() }}";
            $('input[name=\'_token\']').val(token);
        });
        $('#home_type').change(function() {
            var selectedType = $(this).val();
            if (selectedType === 'residential') {
                $('#residential_rooms').show().find('select').prop('disabled', false);
                $('#accommodation_rooms').hide().find('select').prop('disabled', true);
            } else if (selectedType === 'accommodation') {
                $('#accommodation_rooms').show().find('select').prop('disabled', false);
                $('#residential_rooms').hide().find('select').prop('disabled', true);
            } else {
                $('#residential_rooms, #accommodation_rooms').hide().find('select').prop('disabled',
                    true);
            }
        });

        // ✅ Trigger change once on page load to apply correct state
        $('#home_type').trigger('change');
    });
</script>
<script>
    const unitSelect = document.getElementById('height_unit');
    const dropdown = document.getElementById('height_dropdown');
    const inchDiv = document.getElementById('inch_div');

    function populateDropdown(unit) {
        dropdown.innerHTML = '<option value="">-- Select Height --</option>'; // reset

        if (unit === 'cm') {
            for (let i = 100; i <= 250; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.text = i + ' cm';
                //if (i == selectedValue) option.selected = true; // pre-select
                dropdown.appendChild(option);
            }
            inchDiv.style.display = "none";
        } else if (unit === 'ft_in') {
            for (let ft = 3; ft <= 8; ft++) {
                const option = document.createElement('option');
                option.value = ft;
                option.text = ft + ' ft';
                //if (ft == selectedValue) option.selected = true; // pre-select
                dropdown.appendChild(option);
            }
            inchDiv.style.display = "block";
        }
    }

    // Initial population
    populateDropdown(unitSelect.value);

    // Update when unit changes
    unitSelect.addEventListener('change', () => {
        populateDropdown(unitSelect.value);
    });



    // const weightInput = document.getElementById('weight_input');
    const weightUnit = document.getElementById('weight_unit');
    const weightDropdown = document.getElementById('weight_dropdown');

    // Pass saved value from Blade to JS

    function populateDropdownWeight(unit) {
        weightDropdown.innerHTML = '<option value="">-- Select Weight --</option>';
        let start, end, step;

        if (unit === 'kg') {
            start = 30;
            end = 200;
            step = 1;
        } else {
            start = 66;
            end = 440;
            step = 1;
        }

        for (let i = start; i <= end; i += step) {
            const option = document.createElement('option');
            option.value = i;
            option.text = `${i} ${unit}`;
            // if (i == selectedWeight) option.selected = true; // Pre-select saved value
            weightDropdown.appendChild(option);
        }
    }

    // Initialize dropdown with saved unit and weight
    populateDropdownWeight(weightUnit.value);

    // Update dropdown when unit changes
    weightUnit.addEventListener('change', () => {
        populateDropdownWeight(weightUnit.value);
    });
    $(document).on('change', '#childCourse', function() {
        let selected = $(this).find(':selected');
        $("#chiledCoursenumber").val(selected.data('coursenumber'));
        $("#childCourseLevel").val(selected.data('level'));
        $("#childCourseImage").val(selected.data('image'));
        $("#childCourseDescription").val(selected.data('description'));
    });
    $(document).on('change', '.course_qualifications', function() {

        let box = $(this).closest('.course-box');

        if ($(this).is(':checked')) {

            box.find('[data-name]').each(function() {
                $(this).attr('name', $(this).data('name'));
            });

            // box.find('.qual_upload')
            //     .prop('disabled', false)
            //     .attr('required', true);

        } else {

            box.find('[data-name]').each(function() {
                $(this).removeAttr('name');
            });

            // box.find('.qual_upload')
            //     .prop('disabled', true)
            //     .removeAttr('required')
            //     .val('');
        }
    });
</script>