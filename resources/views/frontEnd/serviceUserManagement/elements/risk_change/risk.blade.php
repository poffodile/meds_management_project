<?php //show su profile risk tiles
foreach ($risks as $risk) {
    // $status = App\Risk::checkRiskStatus($service_user_id, $risk->id);
    // // dd($status);
    // $color_class = 'bg-darkgreen';
    // $risk_id = $status->id ?? '';
    // if (!empty($status->status)) {

    //     if ($status->status == 1) {
    //         $color_class = 'orange-bg';
    //     } elseif ($status->status == 2) {
    //         $color_class = 'bg-red';
    //     }
    // } else {
    //     $status = 0;
    // }

     $color_class = match ($risk->user_status) {
        1 => 'orange-bg',
        2 => 'bg-red',
        default => 'bg-darkgreen',
    };

?>
    <style>
        .input-date {
            display: inline-block;
            height: 34px;
            width: 167px;
        }

        .input-area {
            display: inline-block;
            height: 34px;
            width: 167px;
            margin-left: 5px;
        }

        .input-area:focus {
            border: 1px solid #57c8f1;
        }

        .select-control {
            display: inline-block;
            height: 34px;
            width: 167px;
            border: 1px solid #33333336;
            border-radius: 4px;
            margin-left: -5px;
        }

        .select-control:focus {
            border: 1px solid #57c8f1;
        }

        .riskfilter {
            margin-bottom: 25px;
            margin-right: 20px;
        }
    </style>
    <div class="col-md-3 col-sm-4 col-xs-12">
        <div class="profile-nav alt">
            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                <div class="user-heading alt wdgt-row {{ $color_class }} risk-clr">
                    <i class="{{ $risk->icon }}"></i>
                </div>

                <div class="panel-body">
                    <div class="wdgt-value">
                        <h4 class="count risk-desc">{{ $risk->description }}</h4>
                        <p></p>
                    </div>
                </div>
            </section>
            <ul class="m-0 p-0 overviw-dropdown risk_change_btns" type="none">
                <li><a href="#" class="risk_change_btn " risk-id="{{ $risk->risk_id }}" status="2"> <i class="<?php echo ($risk->user_status == '2') ? 'fa fa-check-circle' : 'fa fa-times-circle'; ?>"></i> Live Risk </a> </li>
                <li><a href="#" class="risk_change_btn" risk-id="{{ $risk->risk_id }}" status="1"> <i class="<?php echo ($risk->user_status == '1') ? 'fa fa-check-circle' : 'fa fa-times-circle'; ?>"></i> Historic Risk </a> </li>
                <li><a href="#" class="risk_change_btn" risk-id="{{ $risk->risk_id }}" status="0"> <i class="<?php echo ($risk->user_status == '0') ? 'fa fa-check-circle' : 'fa fa-times-circle'; ?>"></i> No Risk </a> </li>
                <!-- <li><a href="#" class="risk_change_btn" risk-id="{{ $risk->risk_id }}" status=""><i class="fa fa-times-circle"></i> View Log </a> </li>-->
                <li><a href="{{url('service/risks/'.$service_user_id)}}" class="risk_change_btns" risk-id="{{ $risk->risk_id }}" status=""><i class="fa fa-eye risk-view"></i> View Log </a> </li>

            </ul>
        </div>
    </div>

<?php  } ?>

<!-- risk 3 tab modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="riskDesc">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title risk-desc-modal">Risk</h4>
            </div>
            <div class="modal-body p-b-0">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 serch-btns text-right">
                        <!-- <button class="btn label-default risk-add-btn active" type="button"> Add New </button> -->
                        <!-- <button class="btn label-default risk-logged-btn risk-view-btn" type="button"> Logged Risks </button>
                        <button class="btn label-default risk-search-btn active" type="button"> Search </button> -->
                    </div>

                    <div class="risk-add-box risk-tabs">
                        <form method="post" action="" id="change_risk_status_form">
                            <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">User: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class="su_n_id" disabled="">
                                            <option value="0"> Select Child </option>
                                            @foreach($service_users as $value)
                                                <option value="{{ $value['id'] }}" {{ ($service_user_id == $value['id']) ? 'selected' : '' }}>{{ ucfirst($value['name']) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div> -->

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Risk: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                    <input type="text" disabled="" class="form-control trans" name="risk_name" maxlength="255" />
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Change to: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                    <div class="select-style">
                                        <select class="form-control new_risk_status" name="new_risk_status">
                                            <option value="">Select Risk </option>
                                            <option value="0">No Risk </option>
                                            <option value="1">Historic </option>
                                            <option value="2">Live Risk </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Add: </label>
                                <div class="col-md-11 col-sm-12 col-xs-12 p-l-20">
                                    <div class="form-control select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select">
                                            <option value="0"> Select Form </option>

                                            <?php

                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('risk_change');
                                            foreach ($dynamic_forms as $value) {

                                                $location_ids_arr = explode(',', $value['location_ids']);

                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                                    <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }} </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <p class="help-block"> Choose a form you want to fill. </p>
                                </div>
                            </div>
                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div> -->
                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"> </div>

                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="uploadPopImg mt-0 hideImageDiv"><img id="" class="my-2 imagePreview" src="" width="100px"></div>
                            </div> -->
                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <!-- <input type="hidden" name="plan_detail" value=""> -->
                                <input type="hidden" name="risk_id" value="">
                                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <button class="btn btn-warning change_risk_submit_btn" type="submit"> Confirm </button>
                                <!-- sbt-bmp-btn  -->
                            </div>
                        </form>
                    </div>
                    <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                       
                            <form id="change_risk_status_form">
                                @include('frontEnd.common.popup_alert_messages')
                                <div class="modal-space">
                                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Risk: </label>
                                            <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                <div class="input-group popovr">
                                                    <input type="text" disabled="" class="form-control trans" name="risk_name" maxlength="255"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Change to: </label>
                                            <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                <select class="form-control new_risk_status" name="new_risk_status">
                                                    <option value="">Select Risk </option>
                                                    <option value="0">No Risk </option>
                                                    <option value="1">Historic </option>
                                                    <option value="2">Live Risk </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6  col-lg-6 col-xs-12">
                                        <h3 class="m-t-0 m-b-20 m-l-30 clr-blue risk_name fnt-20" >Incident Explanation</h3>
                                    </div>
                                    <div class="dynamic-risk-fields">
                                        {!! $form_pattern['risk'] !!}
                                    </div>
                                </div>
                                <div class="modal-footer m-t-0 recent-task-sec p-b-0">
                                    <input type="hidden" name="service_user_id" value="{{ $service_user_id }}" />
                                    <input type="hidden" name="su_risk_id" value="" />
                                    <input type="hidden" name="risk_id" value="" />
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                    <button class="btn btn-default risk-modal-close" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                    <button class="btn btn-warning change_risk_submit_btn" type="button"> Confirm </button>
                                </div>
                            </form>
                        </div>
                    </div> -->

                    <!-- logged plans -->
                    <div class="risk-logged-box risk-tabs">
                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')
                        <!-- sourabh -->
                        <div class="row">
                            <form class="riskfilter">

                                <div class="form-group datepicker-sttng date-sttng">
                                    <div class="col-md-4 col-sm-10 col-xs-12 col-lg-5">
                                        <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                            <input id="date_range_input" style="cursor: pointer;" name="daterange" value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text" value="" readonly="" size="16" class="form-control log-book-datetime">
                                            <span class="input-group-btn add-on datetime-picker2">
                                                <button onclick="showDate()" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- <input type="date" id="birthday" class="input-date" name="birthday"> -->
                                <select name="select_riskcategory" id="select_riskcategory" class="select-control">
                                    <option value="all">All </option>
                                    <option value="0">No Risk </option>
                                    <option value="1">Historic </option>
                                    <option value="2">Live Risk </option>
                                </select>
                                <input type="text" id="keyword" class="input-area" onKeyPress="myFunctionkey()" onKeyUp="myFunctionkey()" placeholder="keyword">
                            </form>
                        </div>
                        <!-- sourabh -->
                        <div class="logged-plan-shown logged-risk-list">
                            <!-- logged risk list be shoen here using ajax -->


                        </div>
                    </div>

                    <div class="risk-search-box risk-tabs">
                        <form method="post" id="plan_search_form">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Risk Title: </label>
                                <div class="col-md-10 col-sm-10 col-xs-12 p-l-0 m-b-15">
                                    <input type="text" name="search_risk_keyword" class="form-control" maxlength="255">
                                </div>
                                <div class="searched-risks p-t-10 text-center">
                                    <!--searched Risk List using ajax -->
                                </div>
                            </div>
                            <div class="modal-space"></div>
                            <div class="modal-footer m-t-0 recent-task-sec">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <button class="btn btn-warning search_su_risk_btn" type="button"> Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- end risk 3 tab modal -->

<!-- risk view modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="riskViewModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                     <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#riskDesc" class="close p-r-10" > <i class="fa fa-arrow-left"></i></a> -->
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <a class="close view-logged" href="" data-toggle="modal" data-dismiss="modal" data-target="" style="font-size:18px; padding-right:8px;">
                    <i class="fa fa-arrow-left" title=""></i>
                </a>

                <h4 class="modal-title risk-desc-modal">Risk Details - View</h4>
            </div>
            <div class="modal-body p-b-0">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">

                        <form id="change_risk_status_form1">

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Risk: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                    <input type="text" disabled="" class="form-control trans" name="v-risk_name" maxlength="255" />
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Changed to: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                    <div class="select-style">
                                        <select class="form-control new_risk_status trans" name="v-risk_new_status" disabled="">
                                            <option value="">Select Risk </option>
                                            <option value="0">No Risk </option>
                                            <option value="1">Historic </option>
                                            <option value="2">Live Risk </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Form: </label>
                                <div class="col-md-11 col-sm-12 col-xs-12 p-l-20">
                                    <div class="select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select form-control" disabled="">
                                            <option value="0"> Select Form </option>
                                            <?php

                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('risk_change');
                                            foreach ($dynamic_forms as $value) {

                                                $location_ids_arr = explode(',', $value['location_ids']);

                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                                    <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }} </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="icon-div-icons pull-right p-r-15">
                                    <button class="btn btn-risk group-ico view-risk-rmp w-75" su_rmp_id="" name="View RMP"><i class=""></i>RMP</button>
                                    <button class="btn btn-risk group-ico ve-risk-inc-rep w-75" su_risk_id="" name="View Incident Report"><i class=""></i>Report</button>
                                    <a href="" title="Body Map" class="btn btn-risk group-ico body-map"><i class="fa fa-male"></i></a>
                                </div>
                            </div>


                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>
                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"> </div>

                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="uploadPopImg mt-0 hideImageDiv"><img id="" class="my-2 imagePreview" src="" width="100px"></div>
                            </div> -->

                            <!-- @include('frontEnd.common.popup_alert_messages')
                                    <div class="modal-space">
                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Risk Detail: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" disabled="" class="form-control trans" name="v-risk_name" maxlength="255"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 p-l-0"> Changed to: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" class="form-control trans" name="v-risk_new_status_txt" disabled="" maxlength="255" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 p-l-0"> Date: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" class="form-control trans" name="v-risk_date" disabled="" value="" maxlength="10" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-lg-6  col-xs-12">
                                            <h3 class="m-t-0 m-b-20 m-l-30 clr-blue risk_name fnt-20" >Incident Explanation</h3>
                                        </div>
                             
                                        <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                            <div class="icon-div-icons pull-right p-r-15">
                                                <button class="btn btn-risk group-ico view-risk-rmp w-75" su_rmp_id="" ><i class=""></i>RMP</button>
                                                <button class="btn btn-risk group-ico ve-risk-inc-rep w-75"><i class=""></i>IR</button>
                                            </div>
                                        </div>

                                        <div class="v-dynamic-risk-fields">
                                        </div>
                                    </div> -->

                            <div class="modal-footer m-t-0 recent-task-sec p-b-0">
                                <!-- <input type="hidden" name="service_user_id" value="{{ $service_user_id }}" />
                                        <input type="hidden" name="risk_id" value="" />
                                        <input type="hidden" name="new_status" value="" />
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">-->

                                <input type="hidden" name="su_risk_id" value="">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button><!--  -->
                                <button class="btn btn-warning" data-toggle="modal" data-target="#riskDesc" type="button" data-dismiss="modal"> Confirm </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- end risk view modal -->
@include('frontEnd.serviceUserManagement.elements.risk_change.risk_rmp')
@include('frontEnd.serviceUserManagement.elements.risk_change.risk_incident_report')
<script>
    $(function() {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            }
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });
    });

    function showDate() {

        $('#date_range_input').click();
    }
</script>

<script>
    $(document).ready(function() {
        $('.risk_change_btn').click(function() {

            if ($(this).children('i').hasClass('fa fa-check-circle')) {
                return false;
            }


            var risk_name = $(this).closest('.risk_change_btns').siblings('.panel').find('.risk-desc').text();
            var risk_id = $(this).attr('risk-id');
            var status = $(this).attr('status');

            //var risk_status_txt = $(this).text();
            $('.risk_change_btn').removeClass('active_change_risk');
            $(this).addClass('active_change_risk');
            if ($(this).children('i').hasClass('risk-view')) {
                $(this).removeClass('active_change_risk');

            }
            //$('.risk-desc-modal').text('Risk - '+risk_name);
            $('input[name=\'risk_name\']').val(risk_name);
            //$('input[name=\'risk_new_status_txt\']').val(risk_status_txt);

            $('#change_risk_status_form input[name=\'risk_id\']').val(risk_id);
            $('#change_risk_status_form select[name=\'new_risk_status\']').val(status);
            $('#change_risk_status_form .risk_detail_textarea').val('');

            // $('.dynamic_form_select').val('0');
            //reset dynamic-form-fields
            $('.dynamic-form-fields').find('input').val('');
            $('.dynamic-form-fields').find('textarea').val('');
            $('.dynamic-form-fields').find('select').val('');

            $('#riskDesc').modal('show');


            $('#riskDesc .risk-add-btn').click();

            return false;
        });
    });
</script>

<script>
    $(document).ready(function() { //save changed rifk description
        $('.change_risk_submit_btn').click(function() {

            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            //var service_user    = $('#'+model_id+' .su_n_id');
            var form_builder = $('#' + model_id + ' .dynamic_form_select');
            var static_title = $('#' + model_id + ' .static_title');
            var new_risk_slctbx = $('#' + model_id + ' .new_risk_status');

            var static_title_vl = static_title.val();
            if (static_title_vl == undefined) {
                return false;
            }

            //var service_user_id = service_user.val().trim();
            var form_builder_id = form_builder.val().trim();
            var static_title_vl = static_title_vl.trim();
            var new_risk_status = new_risk_slctbx.val().trim();
            var err = 0;

            /*if(service_user_id == 0) { 
                service_user.parent().addClass('red_border');
                err = 1;
            }else{
                service_user.parent().removeClass('red_border');
            }*/

            if (form_builder_id == 0) {
                form_builder.parent().addClass('red_border');
                err = 1;
            } else {
                form_builder.parent().removeClass('red_border');
            }

            if (static_title_vl == '') {
                static_title.addClass('red_border');
                err = 1;
            } else {
                static_title.removeClass('red_border');
            }

            if (new_risk_status == '') {
                new_risk_slctbx.addClass('red_border');
                err = 1;
            } else {
                new_risk_slctbx.removeClass('red_border');
            }

            if (err == 1) {
                return false;
            }

            var formdata = $('#' + form_id).serialize();
            var service_user_id = "{{ $service_user_id }}";
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/risk/status/change') }}",
                data: formdata,
                dataType: 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    var response = resp['response'];

                    if (response == 'true') {

                        var status = resp['status'];
                        var overall_risk_status = resp['overall_risk_status'];
                        var su_risk_id = resp['su_risk_id'];

                        if (status == '1') { //historic
                            //changing icon bg color
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').addClass('orange-bg');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('bg-red');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('bg-darkgreen');
                            //changing tick mark with selected option
                            $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class', 'fa fa-times-circle');
                            $('.active_change_risk').children('i').attr('class', 'fa fa-check-circle');

                            //view log btn functionality
                            // $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class','fa fa-eye');
                            // $('.active_change_risk').children('i').attr('class','fa fa-eye risk-view');

                        } else if (status == '2') { //live risk
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').addClass('bg-red');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('orange-bg');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('bg-darkgreen');
                            //changing tick mark with selected option
                            $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class', 'fa fa-times-circle');
                            $('.active_change_risk').children('i').attr('class', 'fa fa-check-circle');

                            //view log btn functionality
                            // $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class','fa fa-eye');
                            // $('.active_change_risk').children('i').attr('class','fa fa-eye risk-view');


                        } else { //no risk
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').addClass('bg-darkgreen');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('bg-red');
                            $('.active_change_risk').closest('.risk_change_btns').siblings('.panel').find('.risk-clr').removeClass('orange-bg');
                            //changing tick mark with selected option
                            $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class', 'fa fa-times-circle');
                            $('.active_change_risk').children('i').attr('class', 'fa fa-check-circle');

                            //view log btn functionality
                            // $('.active_change_risk').closest('.risk_change_btns').find('i').attr('class','fa fa-eye');
                            // $('.active_change_risk').children('i').attr('class','fa fa-eye risk-view');
                        }

                        if (overall_risk_status == '1') {
                            $('#su_risk_status').attr('class', 'orange-clr');
                            $('#su_risk_status').text('Historic');
                        } else if (overall_risk_status == '2') {
                            $('#su_risk_status').attr('class', 'red-clr');
                            $('#su_risk_status').text('High');
                        } else if (overall_risk_status == '0') {
                            $('#su_risk_status').attr('class', 'darkgreen-clr');
                            $('#su_risk_status').text('No');
                        } else {}

                        //show success message
                        $('span.popup_success_txt').text('Risk Details Added Successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut(function() {
                                window.location.href = "{{url('/service/risks/')}}" + '/' + service_user_id
                            })
                        }, 3000);

                        $('.dynamic-risk-fields').find('input').val('');
                        $('.dynamic-risk-fields').find('textarea').val('');
                        $('.dynamic-risk-fields').find('select').val('');

                        //show next modal i.e. rmp modal after successfull data saved

                        $('#riskDesc').modal('hide');

                        $('.dynamic-rmp-fields').find('input').val('');
                        $('.dynamic-rmp-fields').find('textarea').val('');
                        $('input[name=\'rmp_title\']').val('');
                        $('input[name=\'su_risk_id\']').val(su_risk_id);

                        $('#rmpAddModal').modal('show');

                        //return false;
                    } else if (response == 'AUTH_ERR') {

                        $('span.popup_error_txt').text('{{ UNAUTHORIZE_ERR }}');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);

                    } else {

                        $('span.popup_error_txt').text('Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                        //alert('Some Error Occured. Risk can not be updated.')
                    }
                    $('.risk_change_btns').hide();
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
        $('#riskDesc').on('scroll', function() {
            $('.dpYears').datepicker('place')
        });
    });
</script>
<script>
    $(document).ready(function() {

        $('.risk-view-btn').click(function() {

            $('.loader').show();
            $('body').addClass('body-overflow');

            var service_user_id = "{{ $service_user_id }}";
            $.ajax({
                type: 'get',
                url: "{{ url('/service/risks') }}" + '/' + service_user_id,
                //dataType : "json",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.logged-risk-list').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
                    } else {
                        $('.logged-risk-list').html(resp);
                    }

                    // $('.logged-risk-list').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                    //alert(resp);
                }
            });
        });

        // Return back from view record (logged risks)
        $('#riskViewModal .view-logged').click(function() {

            $('#riskDesc').modal('show');
            $('.risk-view-btn').click();
        });

        // View Risk from logged Records
        $(document).on('click', '.view-risk', function() {
            var risk_id = $(this).attr('su_risk_id');
            view_risk(risk_id);
        });

        <?php
        if (!empty($noti_data)) {
            if ($noti_data['event_type'] == 'RISK') {
        ?>
                var risk_id = "{{ $noti_data['event_id'] }}";
                view_risk(risk_id);
                $('#riskViewModal').modal('show');
        <?php }
        } ?>
        // View Risk from logged Records
        /*$(document).on('click','.view-risk', function(){
            var risk_id = $(this).attr('su_risk_id');
            view_risk(risk_id);
        });*/


        function view_risk(risk_id) {
            // $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/risk/view') }}" + '/' + risk_id,
                dataType: "json",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    console.log(response);
                    if (response == true) {

                        var su_risk_id = resp['sur_id'];
                        var su_rmp_id = resp['su_rmp_id'];
                        var risk_txt = resp['risk_txt'];
                        var risk_status = resp['risk_status'];
                        //var risk_status_txt = resp['risk_status_txt'];
                        var risk_dyn_form = resp['risk_form'];
                        var risk_date = resp['created_at'];

                        var form_builder_id = resp['form_builder_id'];
                        var dynamic_form_id = resp['dynamic_form_id'];
                        var incident_report_id = resp['incident_report_id'];
                        var sel_injury_parts = resp['sel_injury_parts'];
                        // console.log(sel_injury_parts);

                        var modal = ('#riskViewModal');

                        $(modal + ' .dynamic-form-fields').html(risk_dyn_form);
                        $(modal + ' .dynamic_form_select').val(form_builder_id);

                        $('input[name=\'su_risk_id\']').val(su_risk_id);
                        $(modal + ' input[name=\'v-risk_name\']').val(risk_txt);
                        $(modal + ' .new_risk_status').val(risk_status);
                        //alert(risk_status);
                        //settings plan buttons
                        $('.view-risk-rmp').attr('su_rmp_id', su_rmp_id);
                        $('.ve-risk-inc-rep').attr('su_risk_id', su_risk_id);
                        //june 23
                        // var body_url = "{{ url('/service/body-map') }}"+'/'+su_risk_id;
                        // $('.body-map').attr('href',body_url);
                        $('.body-map').attr('href', '#bodyMapModal');
                        $('.body-map').attr('data-toggle', 'modal');
                        $('.body-map').attr('data-dismiss', 'modal');
                        // $('input[name=sel_injury_parts]').attr('sel_injury_parts',sel_injury_parts);
                        var obj = JSON.parse(sel_injury_parts);
                        var len = obj.length;
                        for (var i = 0; i < len; i++) {

                            var sel_body_map_id = obj[i].sel_body_map_id;
                            $('#' + sel_body_map_id).attr('class', 'active');
                        }
                        $('input[name=su_rsk_id]').val(su_risk_id);

                        //$('input[name=\'v-risk_new_status_txt\']').val(risk_status_txt);
                        //$('input[name=\'v-risk_date\']').val(risk_date);
                        //$('.v-dynamic-risk-fields').html(risk_dyn_form);
                        $('.v-rmp-btn').attr('su_rmp_id', su_rmp_id);

                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);

                    } else if (response == 'AUTH_ERR') {
                        $('#riskViewModal').modal('hide');
                        $('.ajax-alert-err').find('.msg').text('{{ UNAUTHORIZE_ERR }}');
                        $('.ajax-alert-err').show();
                        setTimeout(function() {
                            $(".ajax-alert-err").fadeOut()
                        }, 5000);
                        //return false;

                    } else {

                        $('span.popup_error_txt').text('Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
        }

        /*$(document).on('click','.view-risk', function(){

           var risk_id = $(this).attr('su_risk_id');

           $('.loader').show();
           $('body').addClass('body-overflow');

           $.ajax({
                type : 'get', 
                url  : "{{ url('/service/risk/view') }}"+'/'+risk_id,
                dataType : "json",
                success:function(resp){
                    if(isAuthenticated(resp) == false){
                        return false;
                    }

                    var response = resp['response'];
                    if(response == true){
                        
                        var su_risk_id      = resp['sur_id'];
                        var su_rmp_id       = resp['su_rmp_id'];
                        var risk_txt        = resp['risk_txt'];
                        var risk_status_txt = resp['risk_status_txt'];
                        var risk_dyn_form   = resp['risk_form'];
                        var risk_date       = resp['created_at'];

                        $('input[name=\'su_risk_id\']').val(su_risk_id);
                        $('input[name=\'v-risk_name\']').val(risk_txt);
                        $('input[name=\'v-risk_new_status_txt\']').val(risk_status_txt);
                        $('input[name=\'v-risk_date\']').val(risk_date);
                        $('.v-dynamic-risk-fields').html(risk_dyn_form);
                        $('.v-rmp-btn').attr('su_rmp_id',su_rmp_id);

                    }  else if(response == 'AUTH_ERR'){

                        $('.ajax-alert-err').find('.msg').text('{{ UNAUTHORIZE_ERR }}');
                        $('.ajax-alert-err').show();
                        setTimeout(function(){$(".ajax-alert-err").fadeOut()}, 5000);
    
                    } else{
    
                        $('span.popup_error_txt').text('Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function(){$(".popup_error").fadeOut()}, 5000);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });     
        });*/

        // view Risk RMP button Script May12
        $(document).on('click', '.view-risk-rmp', function() {

            // var su_risk_id = $('.view-risk').attr('su_risk_id');
            var su_risk_id = $('input[name=\'su_risk_id\']').val();

            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/service/risk/rmp/view') }}" + '/' + su_risk_id,
                dataType: 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == true) {

                        /*var su_risk_id = resp['surmp_su_risk_id'];
                        var service_user_id = resp['service_user_id'];
                        alert(su_risk_id); return false;*/
                        var su_rmp_id = resp['su_rmp_id'];
                        //var rmp_title    = resp['rmp_title'];
                        var rmp_form = resp['rmp_risk_form'];
                        //var rmp_sent_to  = resp['rmp_sent_to'];
                        var form_builder_id = resp['form_builder_id'];
                        var rmp_modal = '#rmpViewModal';

                        // $('input[name=\'su_risk_id\']').val(su_risk_id);
                        // $('input[name=\'service_user_id\']').val(service_user_id);
                        $(rmp_modal + ' input[name=   \'su_rmp_id\']').val(su_rmp_id);
                        $(rmp_modal + ' .dynamic_form_select').val(form_builder_id);
                        $(rmp_modal + ' .dynamic-form-fields').html(rmp_form);
                        //$('input[name=\'edit_rmp_title\']').val(rmp_title);
                        //$('select[name=\'edit_sent_to\']').val(rmp_sent_to);
                        //$('.edit-dynamic-rmp-fields').html(rmp_form);
                        $('#riskViewModal').modal('hide');

                        $(rmp_modal).modal('show');

                        $('.dpYears').datepicker({
                            //format: 'dd/mm/yyyy',
                        }).on('changeDate', function(e) {
                            $(this).datepicker('hide');
                        });
                        $(rmp_modal).on('scroll', function() {
                            $('.dpYears').datepicker('place')
                        });

                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);

                    } else {
                        /*$('span.popup_error_txt').text('RMP Not Occured.');
                        $('.popup_error').show();
                        setTimeout(function(){$(".popup_error").fadeOut()}, 5000);*/
                        $('#riskViewModal').modal('hide');

                        /*$('.dynamic-rmp-fields').find('input').val('');
                        $('.dynamic-rmp-fields').find('textarea').val('');
                        $('input[name=\'rmp_title\']').val('');*/
                        // $('input[name=\'su_risk_id\']').val(su_risk_id);

                        $('#rmpAddModal').modal('show');
                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        $(document).on('click', '.ve-risk-inc-rep', function() {

            var su_risk_id = $(this).attr('su_risk_id');
            //var su_risk_id = $('input[name=\'su_risk_id\']').val();
            //alert(su_risk_id); return false;

            //intialize incident report model
            var inc_modal = '#veIncdntRprtModal';
            $(inc_modal + ' .dynamic_form_select').val('0');
            $(inc_modal + ' .dynamic-form-fields').html('');
            $(inc_modal + ' input[name=\'su_risk_id\']').val(su_risk_id);

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/risk/inc-rec/view') }}" + '/' + su_risk_id,
                dataType: 'json',
                success: function(resp) {
                    //alert(resp); return false;

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];

                    if (response == true) {

                        /*var su_risk_id = resp['surmp_su_risk_id'];
                        var service_user_id = resp['service_user_id'];
                        alert(su_risk_id); return false;*/
                        //var su_ir_id = resp['su_ir_id'];
                        // var inc_rec_title = resp['inc_rec_title'];
                        // var inc_rec_date = resp['inc_rec_date'];

                        var inc_rec_form = resp['inc_rec_form'];
                        var form_builder_id = resp['form_builder_id'];

                        //$(inc_modal+' input[name=\'su_rmp_id\']').val(su_rmp_id);
                        $(inc_modal + ' .dynamic_form_select').val(form_builder_id);
                        $(inc_modal + ' .dynamic-form-fields').html(inc_rec_form);
                        //$(inc_modal+' input[name=\'su_ir_id\']').val(su_ir_id);

                        // $('input[name=\'su_risk_id\']').val(su_risk_id);
                        // $('input[name=\'service_user_id\']').val(service_user_id);
                        // $('input[name=\'su_ir_id\']').val(su_ir_id);
                        // $('input[name=\'edit_inc_rec_title\']').val(inc_rec_title);
                        // $('input[name=\'edit_inc_rec_date\']').val(inc_rec_date);
                        // $('.edit-dynamic-inc-rec-fields').html(inc_rec_form);
                        $('#riskViewModal').modal('hide');
                        $(inc_modal).modal('show');

                        $('.dpYears').datepicker({
                            //format: 'dd/mm/yyyy',
                        }).on('changeDate', function(e) {
                            $(this).datepicker('hide');
                        });
                        $('#veIncdntRprtModal').on('scroll', function() {
                            $('.dpYears').datepicker({
                                orientation: "auto top",
                            });
                        });

                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);

                    } else {

                        $('#riskViewModal').modal('hide');
                        $(inc_modal).modal('show');
                        $(inc_modal + ' .dynamic_form_select').attr('disabled', false);
                        //$(inc_modal+' input[name=\'su_risk_id\']').val(su_risk_id);

                        $(inc_modal + ' span.popup_error_txt').text('Incident Report is Not Added. You can add this by selecting form');
                        $(inc_modal + ' .popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);

                        /*$('span.popup_error_txt').text('Incident Report Not Added');
                        $('.popup_error').show();
                        setTimeout(function(){$(".popup_error").fadeOut()}, 5000);*/

                        $('#riskViewModal').modal('hide');
                        $(inc_modal).modal('show');

                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
        // return false;

        // Edit Logged RMP-Risk Report
        $('.smt_edit_rmp_btn').on('click', function() {

            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            var static_title = $('#' + model_id + ' .static_title');
            var su_rmp_id = $('#' + model_id + ' input[name=\'su_rmp_id\']').val();

            var static_title_vl = static_title.val();
            if (static_title_vl == undefined) {
                return false;
            }

            err = 0;
            if (static_title_vl == '') {
                static_title.addClass('red_border');
                err = 1;
            } else {
                static_title.removeClass('red_border');
            }

            if (err == 1) {
                return false;
            }

            /*var name    = $(modal+' input[name=\'name\']').val();
            var rmp_title = $('input[name=\'edit_rmp_title\']').val();
            
            error = 0;
            rmp_title = jQuery.trim(rmp_title);
            if(rmp_title == '' || rmp_title == null) {
                $('input[name=\'edit_rmp_title\']').addClass('red_border');
                error = 1;
            } else {
                 $('input[name=\'edit_rmp_title\']').removeClass('red_border');
            }
            if(error == 1) {
                return false;
            }*/

            //var formdata = $('#edit_rmp_risk_form').serialize();
            var formdata = $('#' + form_id).serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/risk/rmp/edit') }}" + '/' + su_rmp_id,
                data: formdata,
                dataType: 'json',
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == '1') {

                        $(model_id + ' .dynamic-form-fields').html('');
                        $(model_id).modal('hide');

                        // $('.dynamic-rmp-fields').find('input').val('');
                        // $('.dynamic-rmp-fields').find('textarea').val('');
                        // $('input[name=\'edit_rmp_title\']').val('');
                        // $('#incdntRprtModal').modal('show');
                        //show success message
                        $('span.popup_success_txt').text('RMP Details Edited Successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                    } else {
                        $('span.popup_error_txt').text('{{ COMMON_ERROR }}');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        $('.edit_smt_inc_rec_btn').on('click', function() {

            var model_id = $(this).closest('.modal').attr('id');
            var form_id = $(this).closest('form').attr('id');
            var static_title = $('#' + model_id + ' .static_title');
            //var su_ir_id        = $('#'+model_id+' input[name=\'su_ir_id\']').val();

            var static_title_vl = static_title.val();
            if (static_title_vl == undefined) {
                return false;
            }

            err = 0;
            if (static_title_vl == '') {
                static_title.addClass('red_border');
                err = 1;
            } else {
                static_title.removeClass('red_border');
            }

            if (err == 1) {
                return false;
            }

            /*var inc_rec_title = $('input[name=\'edit_inc_rec_title\']').val();
            var inc_rec_date = $('input[name=\'edit_inc_rec_date\']').val();
            var su_ir_id = $('input[name=\'su_ir_id\']').val();
            error = 0;
            inc_rec_title = jQuery.trim(inc_rec_title);
            if(inc_rec_title == '' || inc_rec_title == null) {
                $('input[name=\'edit_inc_rec_title\']').addClass('red_border');
                error = 1;
            } else {
                 $('input[name=\'edit_inc_rec_title\']').removeClass('red_border');
            }
            if(inc_rec_date == '' || inc_rec_date == null) {
                $('input[name=\'edit_inc_rec_date\']').parent().addClass('red_border');
                error = 1;
            } else {
                 $('input[name=\'edit_inc_rec_date\']').parent().removeClass('red_border');
            }
            if(error == 1) {
                return false;
            }
            var formdata = $('#edit_inc_rep_form').serialize(); */
            var formdata = $('#' + form_id).serialize();
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/risk/inc-rep/edit') }}",
                data: formdata,
                dataType: 'json',
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == '1') {

                        $(model_id + ' .dynamic-form-fields').html('');
                        $(model_id).modal('hide');

                        // $('.edit-dynamic-inc-rec-fields').find('input').val('');
                        // $('.edit-dynamic-inc-rec-fields').find('textarea').val('');
                        // $('input[name=\'edit_inc_rec_title\']').val('');
                        // $('input[name=\'edit_inc_rec_date\']').val('');
                        // $('#veIncdntRprtModal').modal('hide');

                        //show success message
                        $('span.popup_success_txt').text('Incident Report Edited Successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                    } else {
                        $('span.popup_error_txt').text('{{ COMMON_ERROR }}');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });

        return false;

    });
</script>

<script>
    //pagination 
    $(document).on('click', '.risk-pagination li', function() {
        var new_url = $(this).children('a').attr('href');
        if (new_url == undefined) {
            return false;
        }

        $('.loader').show();
        $('body').addClass('body-overflow');

        $.ajax({
            type: 'get',
            url: new_url,
            success: function(resp) {
                if (isAuthenticated(resp) == false) {
                    return false;
                }

                $('.logged-risk-list').html(resp);
                //$('#healthmodal').modal('show');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;
    });
</script>

<script>
    //click search btn
    $(document).ready(function() {

        $('input[name=\'search_risk_keyword\']').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                $('.search_su_risk_btn').click();
                return false;
                //$('.search_files_btn').click();        
            }
        });

        $(document).on('click', '.search_su_risk_btn', function() {

            var search_input = $('input[name=\'search_risk_keyword\']');
            var search = search_input.val();
            search = jQuery.trim(search);

            if (search == '' || search == '0') {
                search_input.addClass('red_border');
                return false;
            } else {
                search_input.removeClass('red_border');
            }

            var service_user_id = "{{ $service_user_id }}";

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/risks') }}" + '/' + service_user_id + '?search=' + search,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    //alert(resp);
                    if (resp == '') {
                        $('.searched-risks').html('No Risks found.');
                    } else {
                        $('.searched-risks').html(resp);
                    }
                    $('input[name=\'search\']').val('');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>

<script>
    /*-------Sweetalert ---------*/
    //swal("Good job!", "You clicked the button!", "success");

    /*---------Three tabs click option----------*/
    $('.risk-logged-box').hide();
    $('.risk-search-box').hide();
    $('.risk-logged-btn').removeClass('active');
    $('.risk-search-btn').removeClass('active');

    $('.risk-add-btn').on('click', function() {
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.risk-add-box').show();
        $(this).closest('.modal-body').find('.risk-add-box').siblings('.risk-tabs').hide();
    });
    $('.risk-logged-btn').on('click', function() {
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.risk-logged-box').show();
        $(this).closest('.modal-body').find('.risk-logged-box').siblings('.risk-tabs').hide();
    });
    $('.risk-search-btn').on('click', function() {
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.risk-search-box').show();
        $(this).closest('.modal-body').find('.risk-search-box').siblings('.risk-tabs').hide();
    });
</script>
<script>
    $('#select_riskcategory').on('change', function() {
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        let category_id = $("#select_riskcategory").val();
        var service_user_id = "{{ $service_user_id }}";
        $.ajax({
            type: 'get',

            url: "{{ url('/service/risks') }}" + '/' + service_user_id + '?start_date=' + start_date.format('YYYY-MM-DD') + '&end_date=' + end_date.format('YYYY-MM-DD') + '&category_id=' + category_id + '&filter=1&keyword=' + keyword,

            success: function(resp) {
                //console.log(resp)
                //alert(resp)
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                //alert(resp);
                if (resp == '') {
                    $('.logged-risk-list').html('No Risks found.');
                } else {
                    $('.logged-risk-list').html(resp);
                }
                // $('input[name=\'search\']').val('');
                // $('.loader').hide();
                // $('body').removeClass('body-overflow');
            }
        });
    })
</script>
<script>
    // $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
    //          $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + 
    //          picker.endDate.format('MM/DD/YYYY'));
    //          alert(1)
    //     });

    //     $('input[name="daterange"]').on('cancel.daterangepicker', function(ev, picker) {
    //         $(this).val(''); 
    //         alert(2)
    //     });

    //     $('input[name="daterange"]').on('show.daterangepicker', function(ev, picker) {
    //         setTimeout(function(){
    //             alert("You have opened datepicker");
    //         }, 0);
    //     });
    $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        let start_date = picker.startDate.format('YYYY-MM-DD');
        let end_date = picker.endDate.format('YYYY-MM-DD');
        let keyword = $('#keyword').val();
        let category_id = $("#select_riskcategory").val();
        var service_user_id = "{{ $service_user_id }}";
        //alert(start_date)
        //alert(start_date.format('YYYY-MM-DD'))
        //alert("hdsgf")
        $.ajax({
            type: 'get',
            url: "{{ url('/service/risks') }}" + '/' + service_user_id + '?start_date=' + start_date + '&end_date=' + end_date + '&category_id=' + category_id + '&filter=1&keyword=' + keyword,
            success: function(resp) {
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                //alert(resp);
                if (resp == '') {
                    $('.logged-risk-list').html('No Risks found.');
                } else {
                    $('.logged-risk-list').html(resp);
                }

            }
        });
    });
</script>
<script>
    function myFunctionkey() {
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        let category_id = $("#select_riskcategory").val();
        var service_user_id = "{{ $service_user_id }}";

        $.ajax({
            type: 'get',

            url: "{{ url('/service/risks') }}" + '/' + service_user_id + '?start_date=' + start_date.format('YYYY-MM-DD') + '&end_date=' + end_date.format('YYYY-MM-DD') + '&category_id=' + category_id + '&filter=1&keyword=' + keyword,

            success: function(resp) {

                if (isAuthenticated(resp) == false) {
                    return false;
                }

                if (resp == '') {
                    $('.logged-risk-list').html('No Risks found.');
                } else {
                    $('.logged-risk-list').html(resp);
                }

            }
        });
    }
</script>