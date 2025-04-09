<div class="modal fade" id="addServiceUserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-user-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add User</h4>
            </div>

            <form method="post" action="{{ url('add-service-user') }}" enctype="multipart/form-data" id='add_service_user'>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Name</label>
                            <input type="text" name="su_name" required placeholder="name" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Username</label>
                            <input type="text" name="su_user_name" required placeholder="username" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Child Type</label>
                            <div class="select-style">
                                <select name="child_type" id="home_type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="residential">Residential</option>
                                    <option value="accommodation">Supported Accomodation</option>
                                    <option value="leavers">Leavers</option>
                                </select>
                            </div>
                        </div>
                        <!-- Residential rooms -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12" id="residential_rooms" style="display: none;">
                            <label>Residential Rooms Type</label>
                            <div class="select-style">
                                <select name="room_type" class="form-control">
                                    <option value="">Select Type </option>
                                    <option value="1">1 Bed Placement </option>
                                    <option value="2">2 Bed Placement </option>
                                    <option value="3">3 Bed Placement </option>
                                    <option value="4">4 Bed Placement </option>
                                </select>
                            </div>
                        </div>
                        <!-- Supported Accommodation rooms -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12" id="accommodation_rooms" style="display: none;">
                            <label>Supported Accomodation Rooms Type</label>
                            <div class="select-style">
                                <select  name="room_type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="1">Group Living</option>
                                    <option value="2">Seperate Flats </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Weekly Rate</label>
                            <input type="text" name="weekly_rate" placeholder="Weekly Rate" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Subs</label>
                            <input type="text" name="subs" placeholder="Subs" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Extra</label>
                            <input type="text" name="extra" placeholder="Extra" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Start Date</label>
                            <input type="text" name="start_date" id="start_date" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>End Date</label>
                            <input type="text" name="end_date" id="end_date" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Local Authority</label>
                            <input type="text" name="local_authority" required placeholder="Local Authority" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Phone Number</label>
                            <input type="text" name="phone_no" required placeholder="phone number" class="form-control">
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 datepicker-sttng date-sttng">
                            <label>Date of Birth</label>
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
                                    <input name="date_of_birth" type="text" value="" autocomplete="off" readonly="" size="16" class="form-control date-pick-su">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <input type="text" value="" name="" id="new-date-su" autocomplete="off" class="form-control date-btn2">
                                        <button class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Section</label>
                            <input type="text" name="section" required placeholder="section" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Admission Number</label>
                            <input type="text" name="admission_number" required placeholder="admission number" class="form-control">
                        </div>
                        <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>Admission Number</label>
                                <input type="text" name="admission_number" required placeholder="admission number" class="form-control">
                            </div> -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <?php
                            $su_ethnicity = App\Ethnicity::select('id', 'name')->where('is_deleted', '0')->get()->toArray();
                            ?>
                            <label>Ethnicity</label>
                            <div class="select-style">
                                <select name="ethnicity_id" class="">
                                    <option value="0"> Select Ethnicity </option>
                                    @foreach($su_ethnicity as $key => $value)
                                    <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Short Description</label>
                            <textarea name="short_description" required class="form-control" rows="3" cols="20" placeholder="Short Descriptiion"></textarea>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Height</label>
                            <input type="text" name="height" required placeholder="height" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Weight</label>
                            <input type="text" name="weight" required placeholder="weight" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Hair and Eyes</label>
                            <input type="text" name="hair_and_eyes" required placeholder="hair and eyes" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Markings</label>
                            <input type="text" name="markings" required placeholder="markings" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Email</label>
                            <input type="email" name="email" required placeholder="email" class="form-control">
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 m-0">
                            <div class="col-md-12 p-0">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                        <img src="" alt="No Image" />
                                    </div>
                                    <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 20px;"></div>
                                    <div>
                                        <span class="btn btn-white btn-file">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                            <input name="image" type="file" class="default" id="img_upload" />
                                        </span>
                                        <!-- <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload"><i class="fa fa-trash"></i>Remove</a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="send_credentials" value="yes" id="sign-checkbox1" maxlength="255"> Send Credentials
                                </label>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer m-t-0">
                    <button class="btn btn-default cancel-user-btn" data-dismiss="modal" type="button"> Cancel </button>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button class="btn btn-warning image_val" type="submit"> Submit </button>
                </div>
        </div>
        </form>
    </div>
</div>
</div>

<script>
    $(document).ready(function() {

        today = new Date;
        $('#new-date-su').datetimepicker({
            format: 'dd-mm-yyyy',
            endDate: today,
            minView: 2
        }).on("change.dp", function(e) {
            var currdate = $(this).data("datetimepicker").getDate();
            var newFormat = currdate.getDate() + "-" + (currdate.getMonth() + 1) + "-" + currdate.getFullYear();
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
                phone_no:{ 
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
                $('#residential_rooms, #accommodation_rooms').hide().find('select').prop('disabled', true);
            }
        });

        // ✅ Trigger change once on page load to apply correct state
        $('#home_type').trigger('change');
    });
</script>