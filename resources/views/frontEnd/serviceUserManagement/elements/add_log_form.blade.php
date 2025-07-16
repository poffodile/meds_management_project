<!-- Log Book Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Forms</h4>
            </div>
            <div class="modal-body">
                @include('frontEnd.common.popup_alert_messages')
                <div class="row">

                    <form id="su-log-book-form">
                        @csrf
                        <div class="add-new-box risk-tabs custm-tabs">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0"> <!-- add-rcrd -->
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> User: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class="su_n_id" <?php if (isset($_GET['key'])) {
                                                                                            echo "disabled";
                                                                                        } ?> />
                                        @foreach($service_users as $val)
                                        <option <?php if (isset($_GET['key'])) {
                                                    if ($_GET['key'] == $val->id) {
                                                        echo "Selected";
                                                    }
                                                } ?> value="{{$val->id}}">{{$val->name}}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select">
                                            <option value="0"> Select Form </option>

                                            <?php

                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('top_profile_btn');
                                            foreach ($dynamic_forms as $value) {
                                                $location_ids_arr = explode(',', $value['location_ids']);
                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                                    <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }} </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <p class="help-block"> Choose a user and the type of form you want to fill. </p>
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>

                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"></div>

                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <button class="btn btn-warning sbt-dyn-form-btn" type="submit"> Confirm </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('#addLogModal').on('shown.bs.modal', function(e) {
            let currentDateTime = moment().format('DD-MM-YYYY HH:mm');
            $('#daily_log_date').val(currentDateTime);
        });

        var today = new Date;
        $('#log-book-datetimepicker').datetimepicker({
            format: 'dd-mm-yyyy',
            // endDate: today,
            // minView : 2

        }).on("change.dp", function(e) {
            var currentdate = $(this).data("datetimepicker").getDate();
            var newFormat = ("0" + currentdate.getDate()).slice(-2) + "-" + ("0" + (currentdate.getMonth() + 1)).slice(-2) + "-" +
                currentdate.getFullYear() + " " + ("0" + currentdate.getHours()).slice(-2) + ":" + ("0" + currentdate.getMinutes()).slice(-2);

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
    });
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

        var error = 0;

        if (category == null) {
            $('select[name=\'category\']').addClass('red_border');
            error = 1;
        } else {
            $('select[name=\'category\']').removeClass('red_border');
        }

        if (error == 1) {
            return false;
        }

        //alert(error);
        $('.loader').show();
        $('body').addClass('body-overflow');
        $.ajax({
            type: 'post',
            url: "{{ url('/service/logbook/add') }}",
            data: new FormData($("#su-log-book-form")[0]),
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            //dataType : 'json',

            success: function(resp) {
                alert(resp);
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

                    $('select[name=\'category\']').val('');
                    $('input[name=\'log_title\']').val('');
                    $('input[name=\'log_date\']').val('');
                    $('textarea[name=\'log_detail\']').val('');

                    //show success message
                    $('span.popup_success_txt').text('Daily log Added Successsfully');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut();
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                        location.reload();
                    }, 2000);
                }

                return false;
            }
        });
        return false;
    });
</script>

<script>
    $(document).ready(function() {
        $('.dynamic_form_select').on('change', function() {
            var form_select = $(this);
            var model_id = form_select.closest('.modal').attr('id');
            var form_builder_id = form_select.val();
            var service_user_id = $('#' + model_id + ' .su_n_id').val();
            var form_title = $('#' + model_id + ' .dynamic_form_select option:selected').text();

            if (form_builder_id > 0) {

                $.ajax({
                    type: 'post',
                    url: "{{ url('/service/dynamic-form/view/pattern') }}",
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
                            $('#' + model_id + ' .dynamic-form-fields').html(pattern);
                            $('#' + model_id + ' .dynamic_form_h3').html(form_title + ' Details');

                            $('.dpYears').datepicker({
                                //format: 'dd/mm/yyyy',
                            }).on('changeDate', function(e) {
                                $(this).datepicker('hide');
                            });

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
                        loaddataontable()
                    }
                });
            } else {}
        });

        $('.sbt-dyn-form-btn').click(function() {
            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            var service_user = $('#' + model_id + ' .su_n_id');
            var form_builder = $('#' + model_id + ' .dynamic_form_select');
            var static_title = $('#' + model_id + ' .static_title');
            var service_user_id = service_user.val().trim();
            var form_builder_id = form_builder.val().trim();
            var err = 0;

            if (form_builder_id == 0) {
                form_builder.parent().addClass('red_border');
                err = 1;
            } else {
                form_builder.parent().removeClass('red_border');
            }

            if (err == 1) {
                return false;
            }

            var formdata = $('#' + form_id).serialize();
            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-form/save') }}",
                data: formdata,
                success: function(resp) {
                    console.log(resp);
                    if (isAuthenticated(resp) == "false") {
                        return false;
                    }

                    if (resp == "true") {

                        console.log("true");
                        $('#' + form_id + ' span.popup_success_txt').text('Record has been Added Successfully');
                        $('#' + form_id + ' .popup_success').show();
                        setTimeout(function() {
                            $('#' + form_id + ' .popup_success').fadeOut(function() {
                                location.reload()
                            })
                        }, 5000);

                        $('#' + model_id + ' .dynamic_form_select').val('0');
                        $('#' + model_id + ' .dynamic-form-fields').html('');

                    } else {
                        //show error message
                        $('#' + form_id + '  span.popup_error_txt').text("{{ COMMON_ERROR }}");
                        $('#' + form_id + ' .popup_error').show();
                        setTimeout(function() {
                            $('#' + form_id + ' .popup_error').fadeOut()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        $('.e-sbt-dyn-form-btn').click(function() {

            var model_id = $(this).closest('.modal').attr('id');
            var previous_model_id = $(this).closest('.modal').find('.previous_modal_btn').attr('pre_modal');
            var logged_box = $('#' + previous_model_id).find('.logged-box');
            var form_id = $(this).closest('form').attr('id');

            //var service_user    = $('#'+model_id+' .su_n_id');
            //var form_builder    = $('#'+model_id+' .dynamic_form_select');
            //var service_user_id = service_user.val().trim();
            //var form_builder_id = form_builder.val().trim();
            //var err = 0;

            /*if(service_user_id == 0) {
                service_user.parent().addClass('red_border');
                err = 1;
            }else{
                service_user.parent().removeClass('red_border');
            }

            if(form_builder_id == 0) {
                form_builder.parent().addClass('red_border');
                err = 1;
            } else{
                form_builder.parent().removeClass('red_border');
            }*/

            /*if(err == 1){
                return false;
            }*/

            var formdata = $('#' + form_id).serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-form/edit') }}",
                data: formdata,
                dataType: 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    if (resp == true) {

                        //  const hideImageDivs = document.getElementsByClassName('hideImageDiv');

                        // for (let i = 0; i < hideImageDivs.length; i++) {
                        //     hideImageDivs[i].style.display = "none";
                        // }

                        $('#' + model_id).modal('hide');
                        $('#' + previous_model_id).modal('show');

                        $('#' + previous_model_id + ' span.popup_success_txt').text('Record has been Edited Successfully');
                        $('#' + previous_model_id + ' .popup_success').show();
                        setTimeout(function() {
                            $('#' + previous_model_id + ' .popup_success').fadeOut()
                        }, 5000);

                        $('#' + previous_model_id + ' .dyn-logged-btn').click();

                        //$('#'+previous_model_id+' .custm-tabs'

                        // $('#'+model_id+' .dynamic_form_select').val('0');
                        // $('#'+model_id+' .dynamic-form-fields').html('');

                        //for mfc case only
                        /*$(".js-example-placeholder-single-mfc").select2({
                          dropdownParent: $('#mfcModal'),
                          placeholder: "Select Description"
                        });*/
                        $('.e-sbt-dyn-form-btn').attr('disabled', true);
                    } else {
                        //show error message
                        $('#' + previous_model_id + '  span.popup_error_txt').text("{{ COMMON_ERROR }}");
                        $('#' + previous_model_id + ' .popup_error').show();
                        setTimeout(function() {
                            $('#' + previous_model_id + ' .popup_error').fadeOut()
                            location.reload()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });

    var seteditvalueeditable = true;

    $(document).ready(function() {


        $(document).on('click', '.dyn-form-view-data', function() {

            var previous_model_id = $(this).closest('.modal').attr('id');
            var dynamic_form_id = $(this).attr('id');
            var form_id = $(this).closest('form').attr('id');

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-form/view/data') }}" + '/' + dynamic_form_id,
                dataType: 'json',
                success: function(resp) {

                    // if (isAuthenticated(resp) == false) {
                    //     return false;
                    // }


                    var response = resp['response'];
                    var form_builder_id = resp['form_builder_id'];
                    var form_title = resp['form_title'];
                    var service_user_id = resp['service_user_id'];
                    var form_data = resp['form_data'];
                    var form_alert = resp['form_alert'];

                    if (response == true) {

                        $('#' + previous_model_id).modal('hide');
                        var view_modal = '#DynFormViewModal';

                        $(view_modal).modal('show');
                        $(view_modal + ' .mdl-back-btn').attr('pre_modal', previous_model_id);

                        $(view_modal + ' .dynamic_form_select').val(form_builder_id);
                        if (service_user_id != null) {
                            $(view_modal + ' .su_id').val(service_user_id);
                        } else {
                            $(view_modal + ' .su_id').val(0);
                        }
                        $(view_modal + ' .dynamic_form_id').val(dynamic_form_id);
                        $(view_modal + ' .dynamic-form-fields').html(form_data);

                        // setTimeout(function () {
                        //     autosize($("textarea"));
                        // },200);

                        /*$('.send_to').selectize({
                            maxItems: null,
                            valueField: 'id',
                            labelField: 'title',
                            searchField: 'title',
                            options: [
                                {id: 1, title: 'Spectrometer', url: 'http://en.wikipedia.org/wiki/Spectrometers'},
                                {id: 2, title: 'Star Chart', url: 'http://en.wikipedia.org/wiki/Star_chart'},
                                {id: 3, title: 'Electrical Tape', url: 'http://en.wikipedia.org/wiki/Electrical_tape'}
                            ],
                            create: false
                        });*/

                    } else {
                        //show error message
                        $('#' + form_id + '  span.popup_error_txt').text("{{ COMMON_ERROR }}");
                        $('#' + form_id + ' .popup_error').show();
                        setTimeout(function() {
                            $('#' + form_id + ' .popup_error').fadeOut()
                        }, 5000);
                    }
                    viewdatawithvalueFormio();
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        $(document).on('click', '.dyn-form-filler', function() {
            var previous_model_id = $(this).closest('.modal').attr('id');
            var dynamic_form_id = $(this).attr('id');
            var form_id = $(this).closest('form').attr('id');

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-form/view/data') }}" + '/' + dynamic_form_id,
                dataType: 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    var response = resp['response'];
                    var form_builder_id = resp['form_builder_id'];
                    var form_title = resp['form_title'];
                    var service_user_id = resp['service_user_id'];
                    var form_data = resp['form_data'];
                    var form_alert = resp['form_alert'];

                    if (response == true) {

                        $('#' + previous_model_id).modal('hide');
                        var view_modal = '#DynFormViewModal';

                        $(view_modal).modal('show');
                        $(view_modal + ' .mdl-back-btn').attr('pre_modal', previous_model_id);

                        $(view_modal + ' .dynamic_form_select').val(form_builder_id);
                        if (service_user_id != null) {
                            $(view_modal + ' .su_id').val(service_user_id);
                        } else {
                            $(view_modal + ' .su_id').val(0);
                        }
                        $(view_modal + ' .dynamic_form_id').val(dynamic_form_id);
                        $(view_modal + ' .dynamic-form-fields').html(form_data);

                    } else {
                        //show error message
                        $('#' + form_id + '  span.popup_error_txt').text("{{ COMMON_ERROR }}");
                        $('#' + form_id + ' .popup_error').show();
                        setTimeout(function() {
                            $('#' + form_id + ' .popup_error').fadeOut()
                        }, 5000);
                    }
                    viewdatawithvalueFormio();
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        function dyn_form_filler() {

        }

        $(document).on('click', '#DynFormViewModal .previous_modal_btn', function() {
            var previous_modal_id = $(this).attr('pre_modal');
            $('#' + previous_modal_id).modal('show');
        });

        $(document).on('click', '#DynFormViewModal .edit_dyn_form', function() {

            var modal_id = 'DynFormViewModal';
            $('#' + modal_id + ' .dynamic-form-fields input').attr('disabled', false);
            $('#' + modal_id + ' .dynamic-form-fields textarea').attr('disabled', false);
            $('#' + modal_id + ' .dynamic-form-fields select').attr('disabled', false);
            $('#' + modal_id + ' .e-sbt-dyn-form-btn').attr('disabled', false);

            $('#' + modal_id + ' span.popup_success_txt').text('Data is made editable');
            $('#' + modal_id + ' .popup_success').show();
            setTimeout(function() {
                $('#' + modal_id + ' .popup_success').fadeOut()
            }, 5000);

            $('.dpYears').datepicker({
                //format: 'dd/mm/yyyy',
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
            });
            $('#' + modal_id).on('scroll', function() {
                $('.dpYears').datepicker('place')
            });
            seteditvalueeditable = false;
            viewdatawithvalueFormio();
            //attr('pre_modal');
            return false;
        });
    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.dyn_form_del_btn', function() {

            if (!confirm('{{ DEL_CONFIRM }}')) {
                return false;
            }

            var this_record = $(this);
            var dyn_form_id = this_record.attr('id');

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-form/delete') }}" + '/' + dyn_form_id,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == 1) {
                        this_record.closest('.rows').remove();

                        //show success delete message
                        $('span.popup_success_txt').text("{{ DEL_RECORD }}");
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);
                    } else {
                        //show delete message error
                        $('span.popup_error_txt').text('{{ COMMON_ERROR }}');
                        $('.popup_error').show();
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });

            return false;
        });
    });
</script>



<script>
    //pagination of bmp
    $(document).ready(function() {
        $(document).on('click', '#dynmicFormModal .pagination li', function() {

            var page_no = $(this).children('a').text();
            if (page_no == '') {
                return false;
            }
            if (isNaN(page_no)) {
                var new_url = $(this).children('a').attr('href');
                page_no = new_url[new_url.length - 1];
            }
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-forms') }}" + "?page=" + page_no,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.view-dyn-record').html(resp);

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;

        });
    });
</script>

<script>
    $(document).ready(function() {

        //when enter press on search box
        $('input[name=\'search_dyn_record\']').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                return false;
            }
        });

        //when bmp search confirm button is clicked
        $(document).on('click', '#dynmicFormModal .search-dyn-btn', function() {
            update_search_list()
            return false;
        });

        function update_search_list() {
            // alert("data aaya");
            // var searchType = document.getElementById('search_type').value;
            var searchType = 2;
            var search_input = (searchType == 1) ? $('input[name="search_dyn_title"]') : $('input[name="search_dyn_date"]');
            var search = search_input.val();
            // console.log(searchType);
            // console.log(search_input);

            // var search_input = $('input[name=\'search_dyn_record\']');
            // var search = search_input.val();

            search = jQuery.trim(search);
            search = search.replace(/[&\/\\#,+()$~%.'":*?<>^@{}]/g, '');

            if (search == '') {
                search_input.addClass('red_border');
                return false;
            } else {
                search_input.removeClass('red_border');
            }

            var formdata = $('#searched-dyn-records-form').serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-forms') }}" + '?search=' + search + '&searchType=' + searchType,
                data: formdata,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('#searched-dyn-records-form .searched-record').html('No Records found.');
                    } else {
                        $('#searched-dyn-records-form .searched-record').html(resp);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        }
    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.dyn_form_daily_log', function() {

            var dyn_form_id = $(this).attr('dyn_form_id');
            var logtype = $(this).attr('logtype');
            $('#logtype').val(logtype);
            //alert(logtype);
            if (logtype == 1) {
                $('.logtitle').text("Add Record To Child's Daily Log");
            } else if (logtype == 2) {
                $('.logtitle').text("Add Record To Child's Weekly Log");
            } else if (logtype == 3) {
                $('.logtitle').text("Add Record To Child's Monthly Log");
            }
            $('#dynmicFormModal').modal('hide');
            $('#dyn_form_id').val(dyn_form_id);
            $('#suDailyLogBook').modal('show');
        });


        $('.sbt-su-dyn-frm-log').click(function() {
            alert("save");
            var dyn_form_id = $('input[name=\'dyn_form_id\']').val();
            var s_user_id = $('select[name=\'s_user_id\']').val();
            var s_category_id = $('select[name=\'s_category_id\']').val();
            var logtype = $('input[name=\'logtype\']').val();
            var token = $('input[name=\'_token\']').val();
            if (logtype == 1) {
                var logtext = "daily";
            } else if (logtype == 2) {
                var logtext = "weekly";
            } else if (logtype == 3) {
                var logtext = "monthly";
            }

            error = 0;
            if (s_category_id == 0) {
                $('select[name=\'s_category_id\']').parent().addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'s_category_id\']').parent().removeClass('red_border');
            }
            if (s_user_id == 0) {
                $('select[name=\'s_user_id\']').parent().addClass('red_border');
                error = 1;
            } else {
                $('select[name=\'s_user_id\']').parent().removeClass('red_border');
            }

            if (error == 1) {
                return false;
            }

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-form/daily-log') }}",
                data: {
                    'dyn_form_id': dyn_form_id,
                    's_user_id': s_user_id,
                    's_category_id': s_category_id,
                    'logtype': logtype,
                    '_token': token
                },
                //dataType : 'json',
                success: function(res) {
                    console.log(res);
                    if (isAuthenticated(res) == false) {
                        return false;
                    }
                    // alert(resp); return false;
                    if (res == '0') {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                    } else if (res == '1') {
                        $('span.popup_success_txt').text('Record has been added to Child ' + logtext + ' log successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $('.popup_success').fadeOut()
                        }, 5000);

                        if (logtype == 1) {
                            window.location.href = "{{ url('/service/daily-logs?key=') }}" + s_user_id;
                        } else if (logtype == 2) {
                            window.location.href = "{{ url('/service/weekly-logs?key=') }}" + s_user_id;
                        } else if (logtype == 3) {
                            window.location.href = "{{ url('/service/monthly-logs?key=') }}" + s_user_id;
                        }


                        $('.dyn-logged-btn').click();

                    } else {
                        $('span.popup_error_txt').text('Record is already added to YP log book');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $('.popup_error').fadeOut()
                        }, 5000);
                        // $('#service-user-add-log').find('select').val('');
                    }
                    $('#suDailyLogBook').modal('hide');
                    $('#dynmicFormModal').modal('show');

                    $('.loader').hide();
                    $('body').addClass('body-overflow');
                }
            });
            return false;
        });

    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.dyn_form_new_tab', function() {

            var printWindow = window.open('', '', 'height=600,width=600');
            printWindow.document.write('<html><head><title>Print DIV Content</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.form.io/formiojs/formio.full.min.css">');
            printWindow.document.write('<link href="{{ url('
                public / backEnd / css / amarjeet.css ')}}" rel="stylesheet" type="text/css" >');
            printWindow.document.write('<link href="{{ url('
                public / backEnd / css / pdfstyle.css ')}}" rel="stylesheet" type="text/css" >');
            printWindow.document.write('</head><body > <div class="masterprintmainarea">');
            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="{{url(' / public / images / scits.png ')}}" style="float:right;height:80px;">');
            printWindow.document.write('</div>');
            // printWindow.document.write(divContents);
            printWindow.document.write('</div>');
            printWindow.document.write('<div class="footer">');
            printWindow.document.write('<div class="footer-section-area">');
            printWindow.document.write('© {{ date('
                Y ') }} Omega Care Group (SCITS). All Rights Reserved | www.socialcareitsolutions.co.uk ');
            printWindow.document.write('</div>');
            printWindow.document.write('</div>');
            printWindow.document.write('</div> </body></html>');
        });
    })
</script>


@include('frontEnd.serviceUserManagement.elements.handover_to_staff')