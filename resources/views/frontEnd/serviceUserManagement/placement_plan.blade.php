@extends('frontEnd.layouts.master')
@section('title',$placement_plan_label)
@section('content')

<style>
    .ScrollStyle::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    /* Track */
    .ScrollStyle::-webkit-scrollbar-track {
        box-shadow: inset 0 0 5px grey;
        border-radius: 5px;
    }

    /* Handle */
    .ScrollStyle::-webkit-scrollbar-thumb {
        background: #1f88b5;
        border-radius: 10px;
    }

    /* Handle on hover */
    .ScrollStyle::-webkit-scrollbar-thumb:hover {
        background: #1f88b5;
    }

    .small-box.ScrollStyle {
        display: flex;
    }

    .ScrollStyle {
        overflow-x: auto;
        white-space: nowrap;
        overflow-y: hidden;
    }

    .settingFlex {
        display: flex;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="col-md-12 col-sm-12 col-xs-12 p-0">
            <!--notification start-->
            <section class="panel">
                <header class="panel-heading">

                    {{ $placement_plan_label }}
                    <!-- <span class="tools pull-right">
                        <a href="javascript:;" class="fa fa-chevron-down"></a>
                        <a href="javascript:;" class="fa fa-cog"></a>
                        <a href="javascript:;" class="fa fa-times"></a>
                    </span> -->
                </header>
                <div class="panel-body">
                    <div class="row">

                        <!-- left part -->
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Targets </h3>
                            </div>
                            <form method="post" action="{{ url('/service/placement-plan/add') }}" id="placement_plan">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 cus-label">
                                    <label class="col-md-2 col-sm-2 p-t-7 p-0"> Add Task: </label>
                                    <div class="col-md-5 col-sm-7 col-xs-12 p-0">
                                        <input type="text" name="task" required class="form-control" maxlength="255">
                                    </div>
                                    <div class="col-md-5 col-sm-3 col-xs-12 cus-label p-0">
                                        <label class="col-md-4 col-sm-6 col-xs-12 p-0 r-tl text-right"> Date: </label>
                                        <div class="col-md-8 col-sm-6 col-xs-12 p-r-0 r-p-0">
                                            <input name="date" required class="form-control datetime-picker trans" type="text" value="" autocomplete="off" maxlength="10" readonly="" />
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-2 col-sm-1 p-t-7"> </label>
                                    <div class="col-md-10 col-sm-11 col-xs-12 p-0">
                                        <div class="input-group popovr fll-wdth">
                                            <input type="text" class="form-control" name="description" required placeholder="" />
                                            <span class="input-group-addon cus-inpt-grp-addon">
                                                <button type="submit" class="btn group-ico-placement">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <p class="help-block"> Enter the description and instruction relevant to task.</p>
                                    </div>
                                </div>
                            </form>

                            <!-- Divider -->
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0">
                                <div class="below-divider"></div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <h3 class="color-green"> Active Targets </h3>
                            </div>

                            <form id='active_targets'>
                                <div class="active-target-list" style="border:0px red solid; /*height:500px*/">

                                    <?php
                                    foreach ($active_targets as $key => $value) {

                                        $current_month = date('m');
                                        $target_month  = date('m', strtotime($value->date));
                                        if ($target_month == $current_month) {
                                            $clr_class = 'orange-bg';
                                        } else {
                                            $clr_class = 'bg-darkgreen';
                                        }
                                    ?>

                                        <div class="form-group col-md-12 col-sm-12 col-xs-12 cog-panel p-0 delete-row">
                                            <div class="col-md-9 col-sm-9 col-xs-12">
                                                <input type="text" class="form-control trans" value="{{ $value->task }}" disabled="" maxlength="255">
                                            </div>
                                            <div class="date-settin">
                                                <div class="input-group popovr">
                                                    <span> Date:&nbsp; </span>

                                                    <label class="btn pp-txt-clr {{ $clr_class }}"> <?php echo date('d M', strtotime($value->date)); ?> </label>
                                                    <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                                        <i class="fa fa-cog"></i>
                                                        <div class="pop-notifbox">
                                                            <ul type="none" class="pop-notification" target_id="{{ $value->id }}" target_task="{{ $value->task }}">

                                                                <li class="view_active_target_btn active-targets"><a href="#"> <span> <i class="fa fa-eye "></i> </span> View/Edit </a> </li>
                                                                <li> <a href="{{ url('/service/placement-plan/mark-complete/'.$value->id) }}" target_id="{{ $value->id }}"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>

                                                                <li class="view_qqa_review_btn" qqa="{{ $value->qqa_review }}"> <a href="#"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> QQA Review </a> </li>
                                                                <!--        <li class="view_qqa_review_btn" qqa="" > <a href="#"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> QQA Review </a> </li> -->

                                                            </ul>
                                                        </div>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    <?php } ?>
                                    <div class="col-md-12 col-sm-12 col-xs-12 clearfix active-target-list-link">
                                        {{ $active_targets->links() }}
                                    </div>
                                </div>
                            </form>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider mrgn-rdce"></div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-calendar">
                                    <a href="{{ url('/service/calendar/'.$service_user_id) }}"><i class="fa fa-calendar"></i></a>
                                    <p>View all current active targets, completed targets and notes</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right part -->
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Completed </h3>
                            </div>
                            <div class="completed-target-list">
                                <div class="completed-box">
                                    <?php foreach ($completed_targets as $key => $value) { ?>
                                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                            {{$value->task}} <span class="color-green m-l-15"><i class="fa fa-check"></i></span>
                                        </div>
                                    <?php }  ?>
                                </div>

                                <div class="col-md-12 col-sm-12 col-xs-12 clearfix completed-target-list-link">
                                    {{ $completed_targets->links() }}
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <h3 class="m-t-0 m-b-30 color-red fnt-20"> Not achieved </h3>
                            </div>

                            <form id="pending_targets">
                                <div class="pending-target-list">
                                    <div class="small-box ScrollStyle">
                                        <?php
                                        foreach ($pending_targets as $key => $value) { ?>

                                            <div class="col-md-4 col-sm-12 col-xs-12 cog-panel p-0 ">
                                                <div class="form-group col-md-12 col-sm-12 col-xs-12 settingFlex">
                                                    <div>
                                                        {{  \Carbon\Carbon::parse($value->created_at)->format('d-m-Y H:i:s') }}

                                                    </div>
                                                    <div>
                                                        <span class="m-l-15 clr-blue settings setting-sze">
                                                            <i class="fa fa-cog"></i>
                                                            <div class="pop-notifbox">
                                                                <ul type="none" class="pop-notification" target_id="{{ $value->id }}" target_task="{{ $value->task }}">
                                                                    <li class="view_active_target_btn"><a href="#"> <span> <i class="fa fa-pencil "></i> </span> Mark Active </a> </li>
                                                                    <li> <a href="{{ url('/service/placement-plan/mark-complete/'.$value->id) }}"> <span class="color-green"> <i class="fa fa-check"></i> </span> Mark complete </a> </li>
                                                                    <li class="view_qqa_review_btn" qqa="{{ $value->qqa_review }}"> <a href="#"> <span class="color-red"> <i class="fa fa-exclamation-circle"></i> </span> QQA Review </a> </li>
                                                                </ul>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-xs-12 clearfix pending-target-list-link">
                                        {{ $pending_targets->links() }}
                                    </div>
                                </div>
                            </form>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
            <!--notification end-->

            <!-- <div class="col-md-12 col-sm-12 col-xs-12 clearfix">
        </div> -->
        </div>
    </section>
</section>
@include('frontEnd.serviceUserManagement.elements.placement_targets')
<!-- <div class="modal fade" id="qqa_reviewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">  

                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                    <div class="form-group p-0 col-md-12 col-sm-12 col-xs-12 add-rcrd">
                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 location-info-label">Current Location</label>
                        <div class="col-md-9 col-sm-9 col-xs-12 r-p-0">
                            <div class="input-group popovr">
                                <textarea name="current_location" required class="form-control edit_current_location" rows="5" ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div> -->

<script>
    $(document).ready(function() {

       var today = new Date;
        $('.datetime-picker').datetimepicker({
            format: 'dd-mm-yyyy',
            startDate: today,
            minView: 2
        });

    });
</script>

<!-- <script>
    $(document).ready(function(){
        $(document).on('click','.settings',function(){ 
            $(this).find('.pop-notifbox').toggleClass('active');
            $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
        });
        $(window).on('click',function(e){
            e.stopPropagation();
            var $trigger = $(".settings");
            if($trigger !== e.target && !$trigger.has(e.target).length){
                $('.pop-notifbox').removeClass('active');
            }
        });
    });
</script> -->

<script>
    $(document).ready(function() {
        //pagination of completed targets
        $(document).on('click', '.completed-target-list-link .pagination li', function() {

            var page_url = $(this).children('a').attr('href');
            var service_user_id = "{{ $service_user_id }}";

            if (page_url == undefined) {
                return false;
            }

            var page_url_array = page_url.split('=');
            page_no = page_url_array.pop();

            var page_url = "{{ url('/service/placement-plan/completed-targets') }}" + '/' + service_user_id + '?page=' + page_no;
            // alert(page_url); return false;
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: page_url,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.completed-target-list').html(resp);
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
        //pagination of pending targets
        $(document).on('click', '.pending-target-list-link .pagination li', function() {

            var page_url = $(this).children('a').attr('href');
            var service_user_id = "{{ $service_user_id }}";

            if (page_url == undefined) {
                return false;
            }
            var page_url_array = page_url.split('=');
            page_no = page_url_array.pop();

            var page_url = "{{ url('/service/placement-plan/pending-targets') }}" + '/' + service_user_id + '?page=' + page_no;
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: page_url,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.pending-target-list').html(resp);
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
        //pagination of active targets
        $(document).on('click', '.active-target-list-link .pagination li', function() {

            var page_url = $(this).children('a').attr('href');
            var service_user_id = "{{ $service_user_id }}";
            if (page_url == undefined) {
                return false;
            }
            var page_url_array = page_url.split('=');
            page_no = page_url_array.pop();

            var page_url = "{{ url('/service/placement-plan/active-targets') }}" + '/' + service_user_id + '?page=' + page_no;
            // alert(page_url);return false;
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: page_url,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.active-target-list').html(resp);
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

        $('.mark_complete').on('click', function() {

            var target_id = $(this).attr('target_id');

            $(this).addClass('active_record');
            var token = "{{ csrf_token() }}";
            var service_user_id = "{{ $service_user_id }}";

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/placement-plan/mark-complete') }}" + '/' + target_id,
                data: {
                    'target_id': target_id,
                    'service_user_id': service_user_id,
                    '_token': token
                },
                dataType: 'json',
                success: function(resp) {
                    // alert(resp); return false;
                    //console.log(resp); 

                    var response = resp['response'];
                    //console.log(response); 

                    if (response == 'true') { //alert('y'); return false;
                        var completed_targets = resp['completed_targets'];
                        var active_targets = resp['active_targets'];

                        $('.active_record').closest('.delete-row').remove();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        //show update message
                        $('span.popup_success_txt').text(' Target updated Successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                    } else {

                        //show update message error
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                    }
                }
            });
        });
    });
</script>
@endsection