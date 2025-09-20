<!-- Add Log Book Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Add Daily Log</h4>
            </div>
            <div class="modal-body">
                @include('frontEnd.common.popup_alert_messages')
                <div class="row">

                    <form id="su-log-book-form" enctype="multipart/form-data">
                        @csrf
                        <div class="add-new-box risk-tabs custm-tabs">
                            {{-- <input type="hidden" name="dynamic_form_log_book_id" id="dynamic_form_log_book_id"> --}}
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Child: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class='su_name' <?php if (isset($_GET['key'])) {
                                            echo 'disabled';
                                        } ?> />
                                        <!-- <option value="{{ $service_user_id }}">{{ $service_user_name }}</option> -->
                                        @foreach ($service_users as $val)
                                            <option <?php if (isset($_GET['key'])) {
                                                if ($_GET['key'] == $val->id) {
                                                    echo 'Selected';
                                                }
                                            } ?> value="{{ $val->id }}">{{ $val->name }}
                                            </option>
                                        @endforeach

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Title: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <input type="text" class="form-control" placeholder="" name="log_title" />
                                    </div>
                                    <p class="help-block"> Enter the Title of Log and add details below.</p>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Category: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="category" class='su_name' required>
                                            <option disabled selected value> -- Select an option -- </option>
                                            @foreach ($categorys as $key)
                                                <option value="{{ $key['id'] }}">{{ $key['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 datepicker-sttng date-sttng">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Date: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                                        class="input-group date"> <!--  dpYears  -->
                                        <input name="log_date" id="daily_log_date" value="{{ date('d-m-Y H:i') }}"
                                            type="text" readonly="" size="16"
                                            class="form-control daily-log-book-datetime">
                                        <span class="input-group-btn add-on datetime-picker2">
                                            <input type="text" value="" name=""
                                                id="log-book-datetimepicker" autocomplete="off"
                                                class="form-control date-btn2">
                                            <button class="btn btn-primary" type="button"><span
                                                    class="glyphicon glyphicon-calendar"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Details: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <textarea name="log_detail" class="form-control detail-info-txt log-detail" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- new image -->
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Image: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <input type="file" name="log_image"
                                            class="form-control detail-info-txt log-image">
                                    </div>
                                </div>
                            </div>
                            <div id="image-preview" style="margin-top:10px; display:none;">
                                <img src="" alt="Preview"
                                    style="max-width:200px; border:1px solid #ddd; padding:5px;">
                            </div>
                            <!-- new image -->
                            <input type="hidden" class="dynamic_form_log_select">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select"
                                            id="">
                                            <option value="0"> Select Form </option>
                                            <?php
                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('daily_log');
                                            foreach ($dynamic_forms as $value) {
                                                $location_ids_arr = explode(',', $value['location_ids']);
                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                            <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }}
                                            </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <p class="help-block"> Choose a form type to fill. </p>
                                </div>
                            </div>
                            {{-- <input type="hidden" id="log_dynamic_form_id" name="log_dynamic_form_id"> --}}
                            {{-- <input type="hidden" id="formDataLogs" name="formDataLogs"> --}}
                            <div class="dynamic-form-fields"></div>



                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default cancel-log" type="button" data-dismiss="modal"
                                    aria-hidden="true"> Cancel </button>
                                {{-- <input type="hidden" name="id" value=""> --}}
                                @if (isset($_GET['key']))
                                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                @endif
                                <input type="hidden" name="location_id" value="9">
                                {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
                                <button class="btn btn-warning submit-log hide-field" type="button"> Submit </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Log Book Modal -->

<!-- Edit Log Book Modal -->
<div class="modal fade" id="editLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

                <h4 class="modal-title">Edit Daily Log</h4>
            </div>
            <div class="modal-body">
                @include('frontEnd.common.popup_alert_messages')
                <div class="row">

                    <form id="su-log-book-form-edit" enctype="multipart/form-data">
                        @csrf
                        <div class="add-new-box risk-tabs custm-tabs">
                            <input type="hidden" name="dynamic_form_log_book_id" id="dynamic_form_log_book_id">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Child: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class='su_name' <?php if (isset($_GET['key'])) {
                                            echo 'disabled';
                                        } ?> />
                                        <!-- <option value="{{ $service_user_id }}">{{ $service_user_name }}</option> -->
                                        @foreach ($service_users as $val)
                                            <option <?php if (isset($_GET['key'])) {
                                                if ($_GET['key'] == $val->id) {
                                                    echo 'Selected';
                                                }
                                            } ?> value="{{ $val->id }}">
                                                {{ $val->name }}
                                            </option>
                                        @endforeach

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Title: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <input type="text" class="form-control" placeholder=""
                                            name="log_title" />
                                    </div>
                                    <p class="help-block"> Enter the Title of Log and add details below.</p>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"><!-- add-rcrd -->
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Category: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">
                                        <select name="category" class='su_name' required id="edit_category">
                                            <option disabled selected value> -- Select an option -- </option>
                                            @foreach ($categorys as $key)
                                                <option value="{{ $key['id'] }}">{{ $key['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 datepicker-sttng date-sttng">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Date: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                                        class="input-group date"> <!--  dpYears  -->
                                        <input name="log_date" id="daily_log_date_edit" value=""
                                            type="text" readonly="" size="16"
                                            class="form-control daily-log-book-datetime">
                                        <span class="input-group-btn add-on datetime-picker2">
                                            <input type="text" value="" name=""
                                                id="log-book-datetimepicker" autocomplete="off"
                                                class="form-control date-btn2">
                                            <button class="btn btn-primary" type="button"><span
                                                    class="glyphicon glyphicon-calendar"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Details: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <textarea name="log_detail" class="form-control detail-info-txt log-detail" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- new image -->
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Image: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-bi">
                                        <input type="file" name="log_image"
                                            class="form-control detail-info-txt log-image">
                                    </div>
                                </div>
                            </div>
                            <div id="image-preview" style="margin-top:10px; display:none;">
                                <img src="" alt="Preview"
                                    style="max-width:200px; border:1px solid #ddd; padding:5px;">
                            </div>
                            <!-- new image -->
                            <input type="hidden" name="dynamic_form_log_select" class="dynamic_form_log_select" id="dynamic_form_log_select">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                                <div class="col-md-9 col-sm-10 col-xs-12">
                                    <div class="select-style">

                                        <select name="dynamic_form_builder_id" class="dynamic_form_select"
                                            id="dynamic_form_builder_log">
                                            <option value="0"> Select Form </option>
                                            <?php
                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('daily_log');
                                            foreach ($dynamic_forms as $value) {
                                                $location_ids_arr = explode(',', $value['location_ids']);
                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                            <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }}
                                            </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <p class="help-block"> Choose a form type to fill. </p>
                                </div>
                            </div>
                            <input type="hidden" id="log_dynamic_form_id" name="log_dynamic_form_id">
                            <input type="hidden" id="formDataLogs" name="formDataLogs">
                            <div class="dynamic-form-log-fields"></div>

                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default cancel-log" type="button" data-dismiss="modal"
                                    aria-hidden="true"> Cancel </button>
                                {{-- <input type="hidden" name="id" value=""> --}}
                                @if (isset($_GET['key']))
                                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                @endif
                                <input type="hidden" name="location_id" value="9">
                                {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
                                <button class="btn btn-warning submit-log-edit hide-field" type="button"> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Edit Log Book Modal -->

<script>
    $(document).ready(function() {

        $('#addLogModal').on('shown.bs.modal', function(e) {
            let currentDateTime = moment().format('DD-MM-YYYY HH:mm');
            $('#daily_log_date').val(currentDateTime);
        });

     
        var today = new Date;
        $('#log-book-datetimepicker').datetimepicker({
            format: 'dd-mm-yyyy',
            endDate: today,
            // minView : 2

        }).on("change.dp", function(e) {
            var currentdate = $(this).data("datetimepicker").getDate();
            var newFormat = ("0" + currentdate.getDate()).slice(-2) + "-" + ("0" + (currentdate
                    .getMonth() + 1)).slice(-2) + "-" +
                currentdate.getFullYear() + " " + ("0" + currentdate.getHours()).slice(-2) + ":" + (
                    "0" + currentdate.getMinutes()).slice(-2);

            $('.daily-log-book-datetime').val(newFormat);
        });

        $('#log-book-datetimepicker').on('click', function() {
            $('#log-book-datetimepicker').datetimepicker('show');
        });

        $("#logBookModal").scroll(function() {
            $('#log-book-datetimepicker').datetimepicker('place')
        });

        $('#log-book-datetimepicker').on('change', function() {
            $('#log-book-datetimepicker').datetimepicker('hide');
        });

        $('.dynamic_form_select').on('change', function() {
            var form_select = $(this);
            var model_id = form_select.closest('.modal').attr('id');

            var form_builder_id = form_select.val();
            var service_user_id = $('#' + model_id + ' .su_n_id').val();

            var form_title = $('#' + model_id + ' .dynamic_form_select option:selected').text();

            if (form_builder_id > 0) {

                // $('.loader').show();
                // $('body').addClass('body-overflow');

                $.ajax({
                    type: 'post',
                    //url : "{{ url('/service/dynamic-form/view/pattern') }}"+'/'+form_builder_id+'/'+su_id,
                    url: "{{ url('/service/dynamic-form/view/pattern_log') }}",
                    data: {
                        'form_builder_id': form_builder_id,
                        'service_user_id': service_user_id
                    },
                    dataType: "json",
                    success: function(resp) {
                        console.log(resp);

                        if (isAuthenticated(resp) == false) {
                            return false;
                        }

                        var response = resp['response'];
                        if (response == true) {

                            var pattern = resp['pattern'];
                            $('#' + model_id + ' .dynamic-form-log-fields').html(pattern);
                            $('#' + model_id + ' .dynamic_form_h3').html(form_title +
                                ' Details');

                            $('.dpYears').datepicker({
                                //format: 'dd/mm/yyyy',
                            }).on('changeDate', function(e) {
                                $(this).datepicker('hide');
                            });

                            //alert(1);
                            $('.send_to').selectize({
                                delimiter: ',',
                                persist: false,
                                create: function(input) {
                                    return {
                                        value: input,
                                        text: input
                                    }
                                }
                            });
                        }

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        let formE2 = document.getElementById("editLogModal");
                        let mode = formE2.getAttribute("data-mode");

                        loaddataontableLog()
                      
                     
                    }
                });
            } else {
                $('.dynamic-form-log-fields').hide();
            }
        });

    });

    $('input[name="log_image"]').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview img').attr('src', e.target.result);
                $('#image-preview').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#image-preview').hide();
        }
    });

    let loaddataontableLog = () => {
        let formid = $("#formid").val();
        let home_id = $("#home_id").val();
        var token = "<?= csrf_token() ?>";
        // alert(token);
        var settings = {
            "url": "{{ url('/service/patterndataformio') }}",
            "method": "POST",
            "data": {
                patterndata: formid,
                home_id: home_id,
                _token: token
            },
            //dataType: "json",
        };
        $.ajax(settings).done(function(response) {
            // alert("sdio");
            if (isAuthenticated(response) == false) {
                return false;
            }
            console.log("loaddataontableLog", response);
            Formio.createForm(document.getElementById('formiotestForm'), {
                components: JSON.parse(response)
            });
        });
    }
</script>

<!-- Add Log to ServiceUser LogBook -->
<script>
    $('.cancel-log').click(function() {
        $('select[name=\'category\']').val('');
        $('input[name=\'log_title\']').val('');
        $('#daily_log_date').val('');
        $('textarea[name=\'log_detail\']').val('');
    });

    $('.submit-log').click(function() {

        var category = $('select[name=\'category\']').val();
        var log_title = $('input[name=\'log_title\']').val();
        var log_date = $('input[name=\'log_date\']').val();
        var log_image = $('input[name=\'log_image\']').val();
        var log_detail = $('.log-detail').val();
        var token = $('input[name=\'_token\']').val();

        var formdata = $('#su-log-book-form').serialize();
        console.log("formdata1", formdata);
        console.log("formdata2", new FormData($("#su-log-book-form")[0]));

        var error = 0;

        if (category == null) {
            $('select[name=\'category\']').addClass('red_border');
            error = 1;
        } else {
            $('select[name=\'category\']').removeClass('red_border');
        }

        if (log_date == '') {
            $('input[name=\'log_date\']').addClass('red_border');
            error = 1;
        } else {
            $('input[name=\'log_date\']').removeClass('red_border');
        }

        if (log_title == '') {

            $('input[name=\'log_title\']').addClass('red_border');
            error = 1;
        } else {

            $('input[name=\'log_title\']').removeClass('red_border');
        }

        if (log_detail == '') {
            $('textarea[name=\'log_detail\']').addClass('red_border');
            error = 1;
        } else {
            $('textarea[name=\'log_detail\']').removeClass('red_border');
        }

        // if(log_image == ''){ 
        //     $('input[name=\'log_image\']').addClass('red_border');
        //     error = 1;
        // }else{ 
        //     $('input[name=\'log_image\']').removeClass('red_border');
        // }

        if (error == 1) {
            return false;
        }

        // var formData = $('#su-log-book-form').serialize();
        var formData = new FormData($('#su-log-book-form')[0]);
        var fileInput = $('input[name="log_image"]')[0].files[0];
        if (fileInput) {
            formData.append('log_image', fileInput);
        }
        console.log(formData);
        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({
            type: 'post',
            url: "{{ url('/service/logbook/add') }}",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            // dataType : 'json',

            success: function(resp) {
                // alert(resp);
                console.log("resp", resp)
                if (isAuthenticated(resp) == false) {
                    return false;
                }

                if (resp == false) {
                    $('span.popup_error_txt').text('Error Occured', 'Try after sometime');
                    $('.popup_error').show();
                    setTimeout(function() {
                        $(".popup_error").fadeOut()
                    }, 5000);
                } else {
                    if (resp == 3) {
                        $('.loader').show();
                        $('body').addClass('body-overflow');
                        $('span.popup_success_txt').text('Daily log eddied successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut();
                            $('.loader').hide();
                            $('body').removeClass('body-overflow');
                            location.reload();
                        }, 2000);
                    } else {
                        $('select[name=\'category\']').val('');
                        $('input[name=\'log_title\']').val('');
                        $('input[name=\'log_date\']').val('');
                        $('textarea[name=\'log_detail\']').val('');

                        //show success message
                        $('span.popup_success_txt').text('Daily log added successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut();
                            $('.loader').hide();
                            $('body').removeClass('body-overflow');
                            location.reload();
                        }, 2000);
                    }

                }

                return false;
            }
        });
        return false;
    });



    $('.submit-log-edit').click(function() {

        var category = $('#edit_category').val();
        var log_title = $('input[name=\'log_title\']').val();
        var log_date = $('input[name=\'log_date\']').val();
        var log_image = $('input[name=\'log_image\']').val();
        var log_detail = $('.log-detail').val();
        var token = $('input[name=\'_token\']').val();


        var error = 0;

        if (category == null) {
            $('#edit_category').addClass('red_border');
            error = 1;
        } else {
            $('#edit_category').removeClass('red_border');
        }

        if (log_date == '') {
            $('input[name=\'log_date\']').addClass('red_border');
            error = 1;
        } else {
            $('input[name=\'log_date\']').removeClass('red_border');
        }

        if (log_title == '') {

            $('input[name=\'log_title\']').addClass('red_border');
            error = 1;
        } else {

            $('input[name=\'log_title\']').removeClass('red_border');
        }

        if (log_detail == '') {
            $('textarea[name=\'log_detail\']').addClass('red_border');
            error = 1;
        } else {
            $('textarea[name=\'log_detail\']').removeClass('red_border');
        }

        // if(log_image == ''){ 
        //     $('input[name=\'log_image\']').addClass('red_border');
        //     error = 1;
        // }else{ 
        //     $('input[name=\'log_image\']').removeClass('red_border');
        // }

        if (error == 1) {
            return false;
        }

        var formdata = $('#su-log-book-form-edit').serialize();
        console.log("formdata1", formdata);
        console.log("formdata2", new FormData($("#su-log-book-form-edit")[0]));


        // var formData = $('#su-log-book-form').serialize();
        var formData = new FormData($('#su-log-book-form-edit')[0]);
        var fileInput = $('input[name="log_image"]')[0].files[0];
        if (fileInput) {
            formData.append('log_image', fileInput);
        }
        console.log(formData);
        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({
            type: 'post',
            url: "{{ url('/service/logbook/add') }}",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            // dataType : 'json',

            success: function(resp) {
                // alert(resp);
                console.log("resp", resp)
                if (isAuthenticated(resp) == false) {
                    return false;
                }

                if (resp == false) {
                    $('span.popup_error_txt').text('Error Occured', 'Try after sometime');
                    $('.popup_error').show();
                    setTimeout(function() {
                        $(".popup_error").fadeOut()
                    }, 5000);
                } else {
                    if (resp == 3) {
                        $('.loader').show();
                        $('body').addClass('body-overflow');
                        $('span.popup_success_txt').text('Daily log eddied successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut();
                            $('.loader').hide();
                            $('body').removeClass('body-overflow');
                            location.reload();
                        }, 2000);
                    } else {
                        $('select[name=\'category\']').val('');
                        $('input[name=\'log_title\']').val('');
                        $('input[name=\'log_date\']').val('');
                        $('textarea[name=\'log_detail\']').val('');

                        //show success message
                        $('span.popup_success_txt').text('Daily log added successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut();
                            $('.loader').hide();
                            $('body').removeClass('body-overflow');
                            location.reload();
                        }, 2000);
                    }

                }

                return false;
            }
        });
        return false;
    });
</script>

@include('frontEnd.serviceUserManagement.elements.handover_to_staff')
