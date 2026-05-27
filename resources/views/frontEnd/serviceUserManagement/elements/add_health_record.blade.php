<?php
$home_id = Auth::user()->home_id;
$service_users = App\ServiceUser::where('home_id', $home_id)->get()->toArray();
$dynamic_forms = App\DynamicFormBuilder::getFormList();
$service_user_id = isset($service_user_id) ? $service_user_id : 0;
?>
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- dynmic Form Modal -->
<div class="modal fade" id="dynmicFormModalhealthrecord" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Health Record</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Add new Details -->
                    <div class="add-new-box risk-tabs custm-tabs">
                        <form method="post" action="" id="TopForms">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Child: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class="su_n_id">
                                            <!-- <option selected disabled> Select Child </option> -->
                                            @foreach ($service_users as $value)
                                                <option value="{{ $value['id'] }}"
                                                    {{ $service_user_id == $value['id'] ? 'selected' : '' }}>
                                                    {{ ucfirst($value['name']) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Add: </label>
                                <div class="col-md-11 col-sm-12 col-xs-12">
                                    <div class="select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select">
                                            <option value="0"> Select Form </option>
                                            <?php
                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('top_profile_btn');
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
                                    <p class="help-block"> Choose a user and the type of form you want to fill. </p>
                                </div>
                            </div>
                            <!-- option for save in daily log -->
                            <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Log Type </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="logtype" class="su_n_id" >
                                            <option value="0"> Select </option>
                                            <option value="1"> Daily Log </option>
                                            <option value="2"> Health Log </option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                            <input type="hidden" name="logtype" value="2" />
                            <!-- option for save in daily log -->

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>
                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"> </div>

                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="uploadPopImg mt-0 hideImageDiv" id=""><img class="my-2 imagePreview" src="" width="100px"></div>
                            </div> -->

                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                    Cancel </button>
                                <button class="btn btn-warning sbt-dyn-form-btn-health" type="submit"> Confirm
                                </button>
                                <!-- sbt-bmp-btn  -->
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- dynmic Form Modal End -->

<!-- View/Edit dynamic form -->
<div class="modal fade" id="DynFormViewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a class="close edit_dyn_form" href="">
                    <i class="fa fa-pencil" title="Edit Form"></i>
                </a>
                <a class="close mdl-back-btn previous_modal_btn" pre_modal="" href="" data-toggle="modal"
                    data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-arrow-left" title="View Previous Modal"></i>
                </a>
                <h4 class="modal-title">View Details</h4>
            </div>
            <div class="modal-body">
                <form method="" id="dynFormFormData">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">User: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <div class="select-style">
                                    <select name="service_user_id" class="su_id" disabled="">
                                        <option value="0"> N/A Child </option>
                                        @foreach ($service_users as $value)
                                            <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Form: </label>
                            <div class="col-md-11 col-sm-12 col-xs-12">
                                <div class="select-style">
                                    <select name="dynamic_form_builder_ids" class="dynamic_form_select"
                                        disabled="">
                                        <option value="0"> Select Form </option>

                                        <?php foreach ($dynamic_forms as $value) {
                                            $location_ids_arr = explode(',', $value['location_ids']); ?>
                                        <option value="{{ $value['id'] }}"> {{ $value['title'] }} </option>
                                        <?php  } ?>
                                    </select>
                                </div>
                                <!-- <p class="help-block"> Choose a user and the type of form you want to fill. </p> -->
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="below-divider"></div>
                        </div>

                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')

                        <!-- Add new Details -->
                        <div class="risk-tabs">
                            <!-- dynamic form fields will be shown here -->
                            <div class="dynamic-form-fields"> </div>
                        </div>
                    </div>
                    <div class="modal-footer m-t-0">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="dynamic_form_id" class="dynamic_form_id" value="">
                        <input type="hidden" name="formdata" id="setformdata" value="">
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                            Cancel </button>
                        <!-- <button class="btn btn-warning sbt_edit_bmp_btn" id="vw-sbt-bmp-plan" type="button"> Continue </button> -->
                        <button class="btn btn-warning e-sbt-dyn-form-btn" disabled="" id=""
                            type="button" data-dismiss="modal" aria-hidden="true"> Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- View/Edit dynamic form End -->


<!-- Su Daily Log Book Modal -->
<div class="modal fade" id="suDailyLogBook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#dynmicFormModal"
                    class="close" style="padding-right:6px"> <i class="fa fa-arrow-left"></i></a>
                <h4 class="modal-title"> Add Record To Child's Daily Log</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="add-new-box risk-tabs custm-tabs">
                        <form method="post" action="" id="">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Child: </label>
                                <div class="col-md-6 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <select name="s_user_id" class="select-field form-control" required
                                            id="records_list" style="width:100%;">
                                            <option value="0"> Select Child </option>
                                            @foreach ($service_users as $value)
                                                <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default" type="button" data-dismiss="modal"
                                    aria-hidden="true"> Cancel </button>
                                <input type="hidden" name="dyn_form_id" value="" id="dyn_form_id">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-warning sbt-su-dyn-frm-log" type="submit"> Submit </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Su Daily Log Book Modal End -->

<!-- dynamic form script start -->
<script>
    $(document).ready(function() {
        $('.dynamic_form_select').on('change', function() {

            var form_select = $(this);
            var model_id = form_select.closest('.modal').attr('id');

            var form_builder_id = form_select.val();
            var service_user_id = $('#' + model_id + ' .su_n_id').val();

            var form_title = $('#' + model_id + ' .dynamic_form_select option:selected').text();
            //alert(model_id);
            //alert(form_title);

            if (form_builder_id > 0) {

                $('.loader').show();
                $('body').addClass('body-overflow');

                $.ajax({
                    type: 'post',
                    //url : "{{ url('/service/dynamic-form/view/pattern') }}"+'/'+form_builder_id+'/'+su_id,
                    url: "{{ url('/service/dynamic-form/view/pattern') }}",
                    data: {
                        'form_builder_id': form_builder_id,
                        'service_user_id': service_user_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: "json",
                    success: function(resp) {

                        if (isAuthenticated(resp) == false) {
                            return false;
                        }

                        var response = resp['response'];
                        if (response == true) {

                            var pattern = resp['pattern'];
                            $('#' + model_id + ' .dynamic-form-fields').html(pattern);
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

                            // setTimeout(function () {
                            //     autosize($("textarea"));
                            // },200);

                            // $('#alert-datetimepicker').datetimepicker({
                            //     format: 'dd-mm-yyyy',
                            //     // endDate: today,
                            //     // minView : 2

                            // }).on("change.dp",function (e) {
                            //     var currentdate = $(this).data("datetimepicker").getDate();
                            //     var newFormat = currentdate.getDate()+"-" +(currentdate.getMonth() + 1)+"-"+currentdate.getFullYear()+" "+currentdate.getHours()+":"+currentdate.getMinutes();
                            //     $('.alert-datetime').val(newFormat);
                            // });
                            // $('#alert-datetimepicker').on('change', function(){
                            //     $('#alert-datetimepicker').datetimepicker('hide');
                            // });

                            //form_select.parent().removeClass('red_border');           
                        }

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                        loaddataontable()
                    }
                });
            } else {
                //$('.dynamic-form-fields').
                //$('.entry-default-fields').hide();
            }
        });

        $('.sbt-dyn-form-btn-health').click(function() {
            // alert("dfdsf");
            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            // alert(form_id); //return false;
            var service_user = $('#' + model_id + ' .su_n_id');
            var service_user_id = service_user.val().trim();
            // alert(service_user_id); 
            // alert(service_user.val().trim()); 
            var form_builder = $('#' + model_id + ' .dynamic_form_select');
            // alert(form_builder); return false;
            var form_builder_id = form_builder.val().trim();
            // alert(form_builder_id); return false;
            // var static_title    = $('#'+model_id+' .static_title');

            // var static_title_vl = static_title.val();
            // alert(static_title_vl); return false;

            // if(static_title_vl == undefined){
            //     return false;
            // } 

            // var static_title_vl = static_title_vl.trim();
            // alert(static_title_vl); return false;
            var err = 0;

            // if(service_user_id == 0) { 
            //     service_user.parent().addClass('red_border');
            //     err = 1;
            // }else{
            //     service_user.parent().removeClass('red_border');
            // }

            if (form_builder_id == 0) {
                form_builder.parent().addClass('red_border');
                err = 1;
            } else {
                form_builder.parent().removeClass('red_border');
            }

            // if(static_title_vl == '') { 
            //     static_title.addClass('red_border');
            //     err = 1;
            // } else{
            //     static_title.removeClass('red_border');
            // }

            if (err == 1) {
                return false;
            }

            var formdata = $('#' + form_id).serialize();
            console.log("###############")
            console.log(formdata)
            //alert(formdata); return false;
            //$('.loader').show();
            // $('body').addClass('body-overflow');
            //console.log(formdata);
            //alert(formdata);
            $.ajax({
                type: 'post',
                url: "{{ url('/service/health-record/add') }}",
                data: formdata,
                //dataType: 'json',
                success: function(resp) {
                    console.log("resp");
                    console.log(resp);
                    if (isAuthenticated(resp) == "false") {
                        return false;
                    }

                    if (resp == "true") {
                        //console.log("true");
                        $('#' + form_id + ' span.popup_success_txt').text(
                            'Record has been Added Successfully');
                        $('#' + form_id + ' .popup_success').show();
                        setTimeout(function() {
                            $('#' + form_id + ' .popup_success').fadeOut()
                            location.reload();
                        }, 3000);

                        $('#' + model_id + ' .dynamic_form_select').val('0');
                        $('#' + model_id + ' .dynamic-form-fields').html('');

                        //for mfc case only                    
                        /*$(".js-example-placeholder-single-mfc").select2({
                          dropdownParent: $('#mfcModal'),
                          placeholder: "Select Description"
                        });*/

                    } else {
                        //console.log("false");
                        //show error message
                        $('#' + form_id + '  span.popup_error_txt').text(
                            "{{ COMMON_ERROR }}");
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
            var previous_model_id = $(this).closest('.modal').find('.previous_modal_btn').attr(
                'pre_modal');
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
            //alert(formdata); 
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

                        $('#' + model_id).modal('hide');
                        $('#' + previous_model_id).modal('show');

                        $('#' + previous_model_id + ' span.popup_success_txt').text(
                            'Record has been Edited Successfully');
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
                        $('#' + previous_model_id + '  span.popup_error_txt').text(
                            "{{ COMMON_ERROR }}");
                        $('#' + previous_model_id + ' .popup_error').show();
                        setTimeout(function() {
                            $('#' + previous_model_id + ' .popup_error').fadeOut()
                        }, 5000);
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
    var seteditvalueeditable = true;
    $(document).ready(function() {
        // $('.send_to').selectize({
        //     maxItems: null,
        //     valueField: 'id',
        //     labelField: 'title',
        //     searchField: 'title',
        //     options: [
        //         {id: 1, title: 'Spectrometer', url: 'http://en.wikipedia.org/wiki/Spectrometers'},
        //         {id: 2, title: 'Star Chart', url: 'http://en.wikipedia.org/wiki/Star_chart'},
        //         {id: 3, title: 'Electrical Tape', url: 'http://en.wikipedia.org/wiki/Electrical_tape'}
        //     ],
        //     create: false
        // });
        /* $('.send_to').selectize({
             delimiter: ',',
             persist: false,
             create: function(input) {
                 return {
                     value: input,
                     text: input
                 }
             }
         });*/

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
                        $(view_modal + ' .mdl-back-btn').attr('pre_modal',
                            previous_model_id);

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
                        $('#' + form_id + '  span.popup_error_txt').text(
                            "{{ COMMON_ERROR }}");
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
            //attr('pre_modal');
            return false;
        });
    });
</script>

<script>
    //logged btn click view bmp title
    $(document).ready(function() {
        $(document).on('click', '.logged-dyn-btn', function() {

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-forms') }}",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.view-dyn-record').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
                    } else {
                        $('.view-dyn-record').html(resp);
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
        //$(document).on('click','.bmp_paginate .pagination li', function(){
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
        // $(document).on('click', '#dynmicFormModal .search-dyn-btn', function() {
        //     update_search_list()
        //     return false;
        // });

        // function update_search_list() {
        //     var search_input = $('input[name=\'search_dyn_record\']');
        //     var search = search_input.val();

        //     search = jQuery.trim(search);
        //     search = search.replace(/[&\/\\#,+()$~%.'":*?<>^@{}]/g, '');

        //     if (search == '') {
        //         search_input.addClass('red_border');
        //         return false;
        //     } else {
        //         search_input.removeClass('red_border');
        //     }

        //     var formdata = $('#searched-dyn-records-form').serialize();

        //     $('.loader').show();
        //     $('body').addClass('body-overflow');

        //     $.ajax({
        //         type: 'post',
        //         url: "{{ url('/service/dynamic-forms') }}" + '?search=' + search,
        //         data: formdata,
        //         success: function(resp) {
        //             if (isAuthenticated(resp) == false) {
        //                 return false;
        //             }
        //             if (resp == '') {
        //                 $('#searched-dyn-records-form .searched-record').html('No Records found.');
        //             } else {
        //                 $('#searched-dyn-records-form .searched-record').html(resp);
        //             }
        //             $('.loader').hide();
        //             $('body').removeClass('body-overflow');
        //         }
        //     });
        //     return false;
        // }
    });
</script>


<script>
    $(document).ready(function() {
        $(document).on('click', '.dyn_form_daily_log', function() {

            var dyn_form_id = $(this).attr('dyn_form_id');

            $('#dynmicFormModal').modal('hide');
            $('#dyn_form_id').val(dyn_form_id);
            $('#suDailyLogBook').modal('show');
        });


        $('.sbt-su-dyn-frm-log').click(function() {

            var dyn_form_id = $('input[name=\'dyn_form_id\']').val();
            var s_user_id = $('select[name=\'s_user_id\']').val();
            var token = $('input[name=\'_token\']').val();

            error = 0;
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
                    '_token': token
                },
                //dataType : 'json',
                success: function(res) {
                    //console.log(res);
                    if (isAuthenticated(res) == false) {
                        return false;
                    }
                    // alert(resp); return false;
                    if (res == '0') {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                    } else if (res == '1') {
                        $('span.popup_success_txt').text(
                            'Record has been added to Child dailylog successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $('.popup_success').fadeOut()
                        }, 5000);
                        $('.dyn-logged-btn').click();

                    } else {
                        $('span.popup_error_txt').text(
                            'Record is already added to YP log book');
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



<!-- <script>
    //making editable click on edit of listing
    $(document).ready(function() {
        $(document).on('click', '.edit_dyn_details', function() {
            var dyn_form_id = $(this).attr('id');
            $('.edit_dyn_details_' + dyn_form_id).removeAttr('disabled');
            $('.edit_dyn_id_' + dyn_form_id).removeAttr('disabled');

            $(this).closest('.cog-panel').find('.input-plusbox').toggle();
            return false;
        });
    });
</script> -->


<!-- dynamic form end -->

