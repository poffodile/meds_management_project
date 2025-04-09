<style>
    button.choices__button {
        display: none;
    }
</style>
<?php
$home_id = Auth::user()->home_id;
$service_users = App\ServiceUser::where('home_id', $home_id)->where('is_deleted', 0)->get()->toArray();
$dynamic_forms = App\DynamicFormBuilder::getFormList();
$service_user_id = (isset($service_user_id)) ? $service_user_id : 0;
?>

<style>
    .pagination>li>a,
    .pagination>li>span {
        padding: 5px 6px;
    }
</style>

<!-- dynmic Form Modal -->
<div class="modal fade" id="dynmicFormModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Forms </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 serch-btns text-right">
                        <button class="btn label-default add-new-btn active" type="button"> Add New </button>
                        <button class="btn label-default logged-btn dyn-logged-btn active logged-dyn-btn" type="button"> Logged Plans </button>
                        <button class="btn label-default search-btn active" type="button"> Search </button><!--adg-->
                    </div>
                    <!-- Add new Details -->
                    <div class="add-new-box risk-tabs custm-tabs">
                        <form method="post" action="" id="TopForm">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7">User: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class="su_n_id">
                                            <!--<option value=""> Child </option>-->
                                            @foreach($service_users as $value)
                                            <option value="{{ $value['id'] }}" {{ ($service_user_id==$value['id'])
                                                ? 'selected' : '' }}>{{ ucfirst($value['name']) }}</option>
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
                            <!-- option for save in daily log -->
                            <!--<div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">-->
                            <!--    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Log Type </label>-->
                            <!--    <div class="col-md-11 col-sm-11 col-xs-12">-->
                            <!--        <div class="select-style">-->
                            <!--            <select name="logtype" class="su_n_id" >-->
                            <!--                <option value="0"> Select </option>-->
                            <!--                <option value="1"> Daily Log </option>-->
                            <!--                <option value="2"> Health Log </option>                         -->
                            <!--            </select>-->
                            <!--        </div>-->
                            <!--    </div>-->
                            <!--</div>-->

                            <!-- option for save in daily log -->



                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>
                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"></div>

                            <!-- <div class="col-md-12 col-sm-12 col-xs-12 cog-panel ">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Send To</label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                        <select class="send_to demo-default form-control" multiple="" placeholder="Select a person...">
                                            <option value="">Select a person...</option>
                                            <option value="4">Thomas Edison</option>
                                            <option value="1">Nikola</option>
                                            <option value="3">Nikola Tesla</option>
                                            <option value="5">Arnold Schwarzenegger</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->

                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <!-- <input type="hidden" name="plan_detail" value=""> -->
                                <!--  <input type="hidden" name="service_user_id" value="{{ $service_user_id }}"> -->
                                <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                    Cancel </button>
                                <button class="btn btn-warning sbt-dyn-form-btn" type="submit"> Confirm </button>
                                <!-- sbt-bmp-btn  -->
                            </div>
                        </form>
                    </div>

                    <!-- logged plans -->
                    <div class="logged-box risk-tabs custm-tabs">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Logged Records </h3>
                        </div>
                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')
                        <div class="modal-space modal-pading view-dyn-record">
                            <!-- record shown using Ajax -->
                        </div>
                        <div class="modal-footer m-t-0 recent-task-sec">
                            <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            <!-- <button class="btn btn-warning sbt-edit-bmp-record" type="button"> Confirm</button> -->
                        </div>
                    </div>

                    <!-- Search Box -->
                    <div class="search-box risk-tabs custm-tabs">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h3 class="m-t-0 m-b-20 clr-blue fnt-20">Search</h3>
                        </div>
                        <!--<div class="col-md-12 col-sm-12 col-xs-12 p-0 srch-field">-->
                        <!--    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 cus-lbl text-right"> Title: </label>-->
                        <!--    <div class="col-md-11 col-sm-11 col-xs-12 m-b-15 title">-->
                        <!--        <div class="select-style">-->
                        <!--            <select name="search_dyn_record" id="search_type">-->
                        <!--                <option value="">Select</option>-->
                        <!--                <option value='1'> Title </option>-->
                        <!--                <option value='2'> Date </option>-->
                        <!--            </select>-->
                        <!--        </div>-->
                        <!--         <input type="text" name="search_dyn_record" class="form-control" maxlength="255"> -->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="col-md-12 col-sm-12 col-xs-12 p-0 search_title">-->
                        <!--    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 cus-lbl"> Title: </label>-->
                        <!--    <div class="col-md-11 col-sm-11 col-xs-12 m-b-15 title">-->
                        <!--        <input type="text" name="search_dyn_title" class="form-control" maxlength="255">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd search_date">
                            <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Date: </label>
                            <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15">

                                <input name="search_dyn_date" type="date" size="45" class="form-control">
                                <!-- <span class="input-group-btn add-on">
                                        <button class="btn clndr btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                    </span> -->

                            </div>
                        </div>
                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')
                        <form id="searched-dyn-records-form" method="post">
                            <div class="modal-space modal-pading searched-record text-center">
                                <!--searched Record List using ajax -->
                            </div>
                        </form>
                        <div class="modal-footer m-t-0 recent-task-sec">
                            <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                Cancel </button>
                            <button class="btn btn-warning search-dyn-btn" type="button"> Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- dynmic Form Modal End -->

<!-- View/Edit dynamic form -->
<div class="modal fade" id="DynFormViewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a class="close edit_dyn_form" href="">
                    <i class="fa fa-pencil" title="Edit Form"></i>
                </a>
                <a class="close mdl-back-btn previous_modal_btn" pre_modal="" href="" data-toggle="modal" data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-arrow-left" title="View Previous Modal"></i>
                </a>
                <h4 class="modal-title">View Details</h4>
            </div>
            <div class="modal-body">
                <form method="" id="dynFormFormData">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7">User: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <div class="select-style">
                                    <select name="service_user_id" class="su_id" disabled="">
                                        <option value="0"> N/A Child </option>
                                        @foreach($service_users as $value)
                                        <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Form: </label>
                            <div class="col-md-11 col-sm-11 col-xs-12">
                                <div class="select-style">
                                    <select name="dynamic_form_builder_id" class="dynamic_form_select" disabled="">
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
                        <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel
                        </button>
                        <!-- <button class="btn btn-warning sbt_edit_bmp_btn" id="vw-sbt-bmp-plan" type="button"> Continue </button> -->
                        <button class="btn btn-warning e-sbt-dyn-form-btn" disabled="" id="" type="button" data-dismiss="modal" aria-hidden="true"> Save </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- View/Edit dynamic form End -->


<!-- Su Daily Log Book Modal -->
<div class="modal fade" id="suDailyLogBook" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#dynmicFormModal" class="close" style="padding-right:6px"> <i class="fa fa-arrow-left"></i></a>
                <h4 class="modal-title"><span class="logtitle"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="add-new-box risk-tabs custm-tabs">
                        <form method="post" action="" id="">
                            {{-- sourabh --}}
                            <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Category: </label>
                                <div class="col-md-6 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <select name="s_category_id" class="select-field form-control" required id="category_list" style="width:100%;">
                                            <option value="0"> Select Category </option>
                                            @php
                                            $category = App\CategoryFrontEnd::select('category.*')->orderBy('name', 'asc')->get();
                                            @endphp
                                            @foreach($category as $category_value)
                                            <option value="{{ $category_value->id }}">{{ $category_value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                            {{-- sourabh --}}
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-4 col-sm-2 col-xs-12 p-t-7 text-right"> Select Child:
                                </label>
                                <div class="col-md-6 col-sm-10 col-xs-12">
                                    <div class="select-bi" style="width:100%;float:left;">
                                        <select name="s_user_id" class="select-field form-control" required id="records_list" style="width:100%;">
                                            <option value="0"> Select Child </option>
                                            @foreach($service_users as $value)
                                            <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group modal-footer m-t-0 modal-bttm">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <input type="hidden" name="dyn_form_id" value="" id="dyn_form_id">
                                <input type="hidden" name="logtype" value="" id="logtype">
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

            if (form_builder_id > 0) {

                // $('.loader').show();
                // $('body').addClass('body-overflow');

                $.ajax({
                    type: 'post',
                    //url : "{{ url('/service/dynamic-form/view/pattern') }}"+'/'+form_builder_id+'/'+su_id,
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

                        // if (resp['imageName'] !== null && resp['imageName'] !== undefined) {
                        //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');
                        //     const imagePreviews = document.getElementsByClassName('imagePreview');

                        //     // Show or hide the image preview for each form
                        //     for (let i = 0; i < hideImageDivs.length; i++) {
                        //         hideImageDivs[i].style.display = "block";
                        //     }

                        //     // Set the src attribute for each image preview
                        //     for (let i = 0; i < imagePreviews.length; i++) {
                        //         imagePreviews[i].src = resp['image'];
                        //     }
                        // } else {
                        //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');

                        //     for (let i = 0; i < hideImageDivs.length; i++) {
                        //         hideImageDivs[i].style.display = "none";
                        //     }
                        // }

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

        $('.sbt-dyn-form-btn').click(function() {
            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            // alert(form_id); //return false;
            var service_user = $('#' + model_id + ' .su_n_id');
            // alert(service_user);
            var form_builder = $('#' + model_id + ' .dynamic_form_select');
            // alert(form_builder); //return false;
            var static_title = $('#' + model_id + ' .static_title');

            // var static_title_vl = static_title.val();
            // if (static_title_vl == undefined) {
            //     return false;
            // }

            var service_user_id = service_user.val().trim();
            // alert(service_user_id);
            var form_builder_id = form_builder.val().trim();
            // alert(form_builder_id);
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

            // if (static_title_vl == '') {
            //     static_title.addClass('red_border');
            //     err = 1;
            // } else {
            //     static_title.removeClass('red_border');
            // }

            if (err == 1) {
                return false;
            }

            var formdata = $('#' + form_id).serialize();
            // alert(formdata); // return false;
            //$('.loader').show();
            // $('body').addClass('body-overflow');
            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-form/save') }}",
                data: formdata,
                //dataType: 'json',
                success: function(resp) {
                    console.log(resp);
                    // alert("ldkjflk");
                    if (isAuthenticated(resp) == "false") {
                        return false;
                    }

                    if (resp == "true") {

                        // const hideImageDivs = document.getElementsByClassName('hideImageDiv');

                        // for (let i = 0; i < hideImageDivs.length; i++) {
                        //     hideImageDivs[i].style.display = "none";
                        // }

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

                        //for mfc case only
                        /*$(".js-example-placeholder-single-mfc").select2({
                          dropdownParent: $('#mfcModal'),
                          placeholder: "Select Description"
                        });*/

                    } else {
                        //console.log("false");
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

        $(document).on('click', '.dyn-form-view-data', function()
        {

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

                    //  if (resp['imageName'] !== null && resp['imageName'] !== undefined) {
                    //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');
                    //     const imagePreviews = document.getElementsByClassName('imagePreview');

                    //     // Show or hide the image preview for each form
                    //     for (let i = 0; i < hideImageDivs.length; i++) {
                    //         hideImageDivs[i].style.display = "block";
                    //     }

                    //     // Set the src attribute for each image preview
                    //     for (let i = 0; i < imagePreviews.length; i++) {
                    //         imagePreviews[i].src = resp['image'];
                    //     }
                    // } else {
                    //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');

                    //     for (let i = 0; i < hideImageDivs.length; i++) {
                    //         hideImageDivs[i].style.display = "none";
                    //     }
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






        $(document).on('click', '.dyn-form-filler', function()
        {
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

                    //  if (resp['imageName'] !== null && resp['imageName'] !== undefined) {
                    //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');
                    //     const imagePreviews = document.getElementsByClassName('imagePreview');

                    //     // Show or hide the image preview for each form
                    //     for (let i = 0; i < hideImageDivs.length; i++) {
                    //         hideImageDivs[i].style.display = "block";
                    //     }

                    //     // Set the src attribute for each image preview
                    //     for (let i = 0; i < imagePreviews.length; i++) {
                    //         imagePreviews[i].src = resp['image'];
                    //     }
                    // } else {
                    //     const hideImageDivs = document.getElementsByClassName('hideImageDiv');

                    //     for (let i = 0; i < hideImageDivs.length; i++) {
                    //         hideImageDivs[i].style.display = "none";
                    //     }
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

        function dyn_form_filler(){

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
                        $('.view-dyn-record').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
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
            if(logtype==1){
                $('.logtitle').text("Add Record To Child's Daily Log");
            }else if(logtype==2){
                $('.logtitle').text("Add Record To Child's Weekly Log");
            }else if(logtype==3){
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
            if(logtype==1){
                var logtext = "daily";
            }else if(logtype==2){
                var logtext = "weekly";
            }else if(logtype==3){
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
                    //console.log(res);
                    if (isAuthenticated(res) == false) {
                        return false;
                    }
                    // alert(resp); return false;
                    if (res == '0') {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                    } else if (res == '1') {
                        $('span.popup_success_txt').text('Record has been added to Child '+ logtext +' log successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $('.popup_success').fadeOut()
                        }, 5000);

                        if(logtype==1){
                            window.location.href = "{{ url('/service/daily-logs?key=') }}" + s_user_id;   
                        }else if(logtype==2){
                            window.location.href = "{{ url('/service/weekly-logs?key=') }}" + s_user_id;   
                        }else if(logtype==3){
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
                printWindow.document.write('<link href="{{ url('public/backEnd/css/amarjeet.css')}}" rel="stylesheet" type="text/css" >');
                printWindow.document.write('<link href="{{ url('public/backEnd/css/pdfstyle.css')}}" rel="stylesheet" type="text/css" >');
                printWindow.document.write('</head><body > <div class="masterprintmainarea">');
                printWindow.document.write('<div class="header">');
                printWindow.document.write('<img src="{{url('/public/images/scits.png')}}" style="float:right;height:80px;">');
                printWindow.document.write('</div>');
                // printWindow.document.write(divContents);
                printWindow.document.write('</div>');
                printWindow.document.write('<div class="footer">');
                printWindow.document.write('<div class="footer-section-area">');
                printWindow.document.write('© {{ date('Y') }} Omega Care Group (SCITS). All Rights Reserved | www.socialcareitsolutions.co.uk ');
                printWindow.document.write('</div>');
                printWindow.document.write('</div>');
                printWindow.document.write('</div> </body></html>');
        });
    })
</script>






<script>
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
</script>

<!-- <script>
    //making editable click on edit of listing
    $(document).ready(function(){
        $(document).on('click','.edit_dyn_details', function(){
            var dyn_form_id = $(this).attr('id');
            $('.edit_dyn_details_'+dyn_form_id).removeAttr('disabled');
            $('.edit_dyn_id_'+dyn_form_id).removeAttr('disabled');

            $(this).closest('.cog-panel').find('.input-plusbox').toggle();
            return false;
        });
    });
</script> -->
<!-- <script>
    //saving all editable record
    $(document).ready(function(){
        $(document).on('click','.sbt-edit-dyn-record', function(){
            var enabled = 0;
            $('.view-dyn-record .edit_rcrd').each(function(index) {
                var is_disable = $(this).attr('disabled');
                if(is_disable == undefined) {
                    enabled = 1;
                }
            });
            if(enabled == 0) {
                return false;
            }
            var formdata =  $('#edit-bmp-form').serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type : 'post',
                url  : "{{ url('service/dynamic-form/edit-details')  }}",
                data : formdata,
                success : function(resp) {
                    if(isAuthenticated(resp) == false){
                        return false;
                    }
                    $('.view-dyn-record').html(resp);
                    $('span.popup_success_txt').text('Updated Successsfully');
                    $('.popup_success').show();
                    setTimeout(function(){$(".popup_success").fadeOut()}, 5000);

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script> -->

<!-- dynamic form end -->

<!-- <script>
    $(document).ready(function(){
        $('.dynamic_form_select').on('change', function() {

            var form_select = $(this);
            var model_id    = form_select.closest('.modal').attr('id');

            var form_id     = form_select.val();
            var form_title  = $('.dynamic_form_select option:selected').text();

            if(form_id > 0){

                $('.loader').show();
                $('body').addClass('body-overflow');

                $.ajax({
                    type:'get',
                    url : "{{ url('/service/dynamic-form/view/pattern') }}"+'/'+form_id,
                    dataType: "json",
                    success:function(resp){

                        if(isAuthenticated(resp) == false){
                            return false;
                        }

                        var response = resp['response'];
                        if(response == true){

                            var pattern = resp['pattern'];
                            $('#'+model_id+' .dynamic-form-fields').html(pattern);
                            $('#'+model_id+' .dynamic_form_h3').html(form_title+' Details');

                            $('.dpYears').datepicker({
                                //format: 'dd/mm/yyyy',
                            }).on('changeDate', function(e){
                                $(this).datepicker('hide');
                            });

                            //form_select.parent().removeClass('red_border');
                        }

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                    }
                });

            } else{
                //$('.dynamic-form-fields').
                //$('.entry-default-fields').hide();
            }
        });
    });
</script> -->
<!-- <script>
$(document).ready(function(){
    $('.sbt-dyn-form-btn').click(function(){

        var model_id        = $(this).closest('.modal').attr('id');
        var service_user    = $('#'+model_id+' .su_n_id');
        var form_builder    = $('#'+model_id+' .dynamic_form_select');
        var service_user_id = service_user.val().trim();
        var form_builder_id = form_builder.val().trim();
        var err = 0;

        if(service_user_id == 0) {
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
        }

        if(err == 1){
            return false;
        }else{
            return true;
        }

    });
});
</script>   -->
<script>
    let loaddataontable = () => {
        let formid = $("#formid").val();
        let home_id = $("#home_id").val();
        var token = "<?= csrf_token() ?>";
        //alert(token);
        var settings = {
            "url": "{{url('/service/patterndataformio')}}",
            "method": "POST",
            "data": {
                patterndata: formid,
                home_id: home_id,
                _token: token
            },
            //dataType: "json",
        };
        $.ajax(settings).done(function(response) {
            if (isAuthenticated(response) == false) {
                return false;
            }
            //console.log(response);
            Formio.createForm(document.getElementById('formiotest'), {
                components: JSON.parse(response)
            });
        });


        // console.log(formid);
        // console.log(home_id);

        // console.log(pattendata);
        //console.log($('#getdatamodel').val());

    }
    let viewdatawithvalueFormio = () => {
        // console.log($('#dynamic_form_idformio').val());
        let dynamic_form_idformio = $("#dynamic_form_idformio").val();
        var token = "<?= csrf_token() ?>";
        var settings = {
            "url": "{{url('/service/patterndataformiovaule')}}",
            "method": "POST",
            "data": {
                dynamic_form_idformio: dynamic_form_idformio,
                _token: token
            },
            //dataType: "json",
        };
        $.ajax(settings).done(function(response) {
            // console.log(response[0].pattern);
            if (isAuthenticated(response) == false) {
                return false;
            }

            Formio.createForm(document.getElementById('formioView'), {
                components: JSON.parse(response[0].pattern)
            }, {
                readOnly: seteditvalueeditable
            }).then(function(form) {
                form.submission = {
                    data: JSON.parse(response[0].pattern_data)
                }
                // form.getComponent('email').setValue('rksonkar356@gmail.com');
            });

        });


    }
    // $(document).ready(function() {
    //     $('.search_title, .search_date').hide(); // hide both elements on page load

    //     $('#search_type').on('change', function() {
    //         var searchType = document.getElementById('search_type').value;
    //         $('.search_title, .search_date').hide(); // hide both elements first
    //         switch (searchType) {
    //             case '1':
    //                 $('.search_title').show();
    //                 break;
    //             case '2':
    //                 $('.search_date').show();
    //                 break;
    //         }
    //     });
    // });
</script>