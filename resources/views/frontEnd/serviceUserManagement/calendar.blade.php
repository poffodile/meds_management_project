@if(!isset($page_title))
$page_title = '';
@endif
@extends('frontEnd.layouts.master')
@section('title','Service Calendar '.$page_title)
@section('content')
<style type="text/css">
    .event_label_close.alert-danger {
        font-size: 25px;
        margin-top: -5px;
    }

    .calenderScroll {
        height: 500px;
        overflow: auto;
    }

    .cus-calendar .external-event {
        position: relative;
    }

    .external-event .fa {
        color: #fff;
        /* background-color: #fff; */
        /* height: 19px; */
        /* width: 19px; */
        text-align: center;
        /* line-height: 1.4; */
        border-radius: 100%;
        cursor: pointer;
        position: absolute;
        right: 8px;
        top: 25%;
    }

    .label-mandatory {
        background-color: #7f2222b0;
    }

    .maroonList {
        background: #7f2222b0 !important;
    }

    .calenderScroll {
        height: 500px;
        overflow: unset !important;
    }
</style>
<?php //echo 'm'; die; 
?>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <section class="panel cus-calendar">
            <header class="panel-heading">
                {{ $page_title }}
                <!-- All young person's Event Calendar -->
                <!-- <span class="tools pull-right">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-cog"></a>
                <a href="javascript:;" class="fa fa-times"></a>
                </span> -->
            </header>
            <div class="panel-body">
                <!-- page start-->
                <div class="row">
                    <aside class="col-lg-9">
                        <div class="outer-calendar">
                            <div id="calendar" class="has-toolbar"></div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-health"></span> {{ $labels['health_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-incentive"></span> {{ $labels['earning_scheme']['label'] }} </li>
                                        <li><span class="bars-pointer label-mandatory"></span> Mandatory Leave </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-daily"></span> {{ $labels['daily_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-event"></span> Appointment Plan </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-living"></span> {{ $labels['living_skill']['label'] }}</li>
                                        <li><span class="bars-pointer label-note"></span> Note</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                        <li><span class="bars-pointer label-education"></span> {{ $labels['education_record']['label'] }} </li>
                                        <li><span class="bars-pointer label-log-book"></span> Log Book </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-4 col-sm-3 col-xs-12 p-0">
                                <div class="top-stats-panel">
                                    <ul class="bars" type="none">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </aside>
                    <aside class="col-lg-3">
                        <div class="calenderScroll">
                            <h4 class="drg-event-title"> Events need booking in </h4>


                            <div id='external-events'>

                                <div class="scroller_hr1 cal_evnt_scroller">
                                    <?php
                                    $pre_exist = 'N';
                                    if (!empty($health_records)) {
                                        foreach ($health_records as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class='external-event label label-health' event_id="{{ $value['health_record_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='health_record'>{{ $value['title'] }}
                                                    <span class="fa fa-close pull-right su_del_hel_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>

                                <!-- old -->
                                
                                <!-- pp -->
                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>
                                    <div class="scroller_note1 cal_evnt_scroller m-b-5">
                                        <?php if (!empty($mandatory_leave_records)) { 
                                            // echo '<pre>'; print_r($calender_notes);  die;
                                            foreach ($mandatory_leave_records as $key => $value) {
                                                if (empty($value['calendar_id'])) {

                                        ?>
                                                    <div class='external-event label label-note maroonList' event_id="{{ $value['mandatory_leaves_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='calender_notes'>{{ $value['title'] }}
                                                        <span class="fa fa-close pull-right su_del_cal_rec dele"></span>
                                                    </div>
                                        <?php  }
                                            }
                                        } ?>
                                    </div>

                                    <div class="bordr-div"></div>
                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>
                                <div class="scroller_dr1 cal_evnt_scroller">
                                    <?php if (!empty($daily_records)) {
                                        foreach ($daily_records as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class='external-event label label-daily' event_id="{{ $value['su_daily_record_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='daily_record'>{{ $value['description'] }}
                                                    <span class="fa fa-close pull-right su_del_daily_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>

                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>
                                <div class="scroller_ls1 cal_evnt_scroller">
                                    <?php if (!empty($living_skills)) {
                                        foreach ($living_skills as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class='external-event label label-living' event_id="{{ $value['su_living_skill_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='living_skill'>{{ $value['description'] }}
                                                    <span class="fa fa-close pull-right su_del_skill_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>
                                <div class="scroller_edu1 cal_evnt_scroller">
                                    <?php if (!empty($education_records)) {
                                        foreach ($education_records as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class='external-event label label-education' event_id="{{ $value['su_education_record_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='education_record'>{{ $value['description'] }}
                                                    <span class="fa fa-close pull-right su_del_edu_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>
                                <div class="scroller_ern1 cal_evnt_scroller">
                                    <?php if (!empty($su_incentives)) {
                                        foreach ($su_incentives as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class="external-event label label-incentive" event_id="{{ $value['su_ern_inc_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='incentive_record'>{{ $value['name'] }}
                                                    <span class="fa fa-close pull-right su_del_incentive_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                                <?php if ($pre_exist == 'Y') { ?>
                                    <div class="bordr-div"></div>
                                <?php }
                                $pre_exist = 'N'; ?>

                                <div class="scroller_cal_evnt1 cal_evnt_scroller m-b-5">
                                    <?php if (!empty($event_records)) {
                                        foreach ($event_records as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                $pre_exist = 'Y';
                                    ?>
                                                <div class='external-event label label-event' event_id="{{ $value['su_calendar_event_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='event_record'>{{ $value['title'] }}
                                                    <span class="fa fa-close pull-right su_del_event_rec dele"></span>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                </div>
                                <!--  <div class='external-event label label-primary'>My Event </div>
                            <div class='external-event label label-success'>My Event 2</div>
                            <div class='external-event label label-info'>My Event 3</div>
                            <div class='external-event label label-inverse'>My Event 4</div>
                            <div class='external-event label label-warning'>My Event 5</div>
                            <div class='external-event label label-danger'>My Event 6</div>
                            <div class='external-event label label-default'>My Event 7</div>
                            <div class='external-event label label-primary'>My Event 8</div>
                            <div class='external-event label label-info'>My Event 9</div>
                            <div class='external-event label label-success'>My Event 10</div> -->

                            </div>

                          
                            <div class="form-group col-md-8 col-sm-8 col-xs-8 p-0" data-toggle="modal" href="#calndraddEntryModal">
                                <div class="input-group popovr">
                                    <div class='add-event label label-event'> Add Event </div>
                                    <span class="input-group-addon cus-inpt-grp-addon color-green">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="bordr-div"></div>
                            <div id="external-events">
                                <div class="scroller_note1 cal_evnt_scroller m-b-5">
                                    <?php if (!empty($calender_notes)) { // echo '<pre>'; print_r($calender_notes); die;
                                        foreach ($calender_notes as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                                // if(strlen($value['title']) > 42){
                                                //     $title = substr($value['title'],0,42).'...';
                                                // } else{
                                                //     $title = substr($value['title'],0,42);       
                                                // }
                                                // $title = str_replace("\r\n",' ', $title);

                                    ?>
                                                <div class='external-event label label-note' event_id="{{ $value['id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='calender_notes'>{{ $value['note_title'] }}
                                                    <span class="fa fa-close pull-right su_del_cal_rec dele"></span>
                                                </div>
                                    <?php  }
                                        }
                                    } ?>
                                </div>
                            </div>
                            <div class="form-group col-md-8 col-sm-8 col-xs-8 p-0" data-toggle="modal" href="#calndraddNoteModal">
                                <div class="input-group popovr">
                                    <div class='add-event label label-note'> Add Notes </div>
                                    <span class="input-group-addon cus-inpt-grp-addon color-green">
                                        <i class="fa fa-plus"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="bordr-div"></div>
                            <div id="external-events">
                                <div class="cal_evnt_scroller">
                                    <?php if (!empty($su_log_books)) {
                                        foreach ($su_log_books as $key => $value) {
                                            if (empty($value['calendar_id'])) {
                                    ?>
                                                <div class="external-event label label-log-book event_label" event_id="{{ $value['log_book_id'] }}" event_type="{{ $value['event_type'] }}" recrd_type='log_book'>
                                                    <span class="fa fa-close pull-right su_del_log_book_rec dele"></span>
                                                    <!--<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <button type="button" class="event_label_close alert-danger">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>-->
                                                    {{ $value['log_title'] }}
                                                </div>

                                    <?php }
                                        }
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
                <!-- page end-->
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
@include('frontEnd.serviceUserManagement.elements.calender_add_entry')
@include('frontEnd.serviceUserManagement.elements.calender_add_notes')
@include('frontEnd.serviceUserManagement.elements.calender_event_detail')
<!-- Scirpts to enable drag and drop in the touch devices -->
<script type="text/javascript" src="{{asset('/public/frontEnd/js/jquery.ui.touch-punch.js')}}"></script>
<!-- Scirpts to detect mobile devices -->
<script type="text/javascript" src="{{asset('/public/frontEnd/js/detectmobilebrowser.js')}}"></script>
<script>
    autosize($("textarea"));
    //scroller 
    /*event_count = $('.scroller_hr .external-event').length;
    if(event_count > 4){
        $('.scroller_hr').slimScroll({height:'140px',alwaysVisible: true});            
    }
    event_count = $('.scroller_dr .external-event').length;
    if(event_count > 4){
        $('.scroller_dr').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_ls .external-event').length;
    if(event_count > 4){
        $('.scroller_ls').slimScroll({height:'140px'});            
    }
     event_count = $('.scroller_edu .external-event').length;
    if(event_count > 4){
        $('.scroller_edu').slimScroll({height:'140px'});            
    }
   
    event_count = $('.scroller_ern .external-event').length;
    if(event_count > 4){
        $('.scroller_ern').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_cal_evnt .external-event').length;
    if(event_count > 4){
        $('.scroller_cal_evnt').slimScroll({height:'140px'});            
    }
    event_count = $('.scroller_note .external-event').length;
    if(event_count > 4){
        $('.scroller_note').slimScroll({height:'140px'});            
    }*/
</script>
<script>
    /* initialize the external events
     -----------------------------------------------------------------*/
    $('#external-events div.external-event').each(function() {
        // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
        // it doesn't need to have a start or end
        var eventObject = {
            title: $.trim($(this).text()), // use the element's text as the event title
            color: $(this).css("background-color"),
            event_id: $(this).attr('event_id'),
            event_type: $(this).attr('event_type'),
            //calendar_id: 0
        };
        //alert(eventObject.id); //return false;
        // store the Event Object in the DOM element so we can get to it later
        var object = $(this).data('eventObject', eventObject);
        //alert(object); return false;
        // make the event draggable using jQuery UI
        $(this).draggable({
            /*containment: "window",*/
            revert: true,
            /*scroll: false,*/
            zIndex: 999999,
            helper: 'clone',
            revertDuration: 0,
            /*helper: "clone",
            revert: true,      // will cause the event to go back to its
            revertDuration: 0  //  original position after the drag*/
        });
    });
    /* initialize the calendar
     -----------------------------------------------------------------*/
    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,basicWeek,basicDay'
        },
        editable: true,
        droppable: true, // this allows things to be dropped onto the calendar !!!
        drop: function(date, allDay) { // this function is called when something is dropped
            $('.loader').show();
            // retrieve the dropped element's stored Event Object
            var originalEventObject = $(this).data('eventObject');
            var selected_event = $(this);
            //console.log(originalEventObject);
            var event_type = originalEventObject.event_type;
            var event_id = originalEventObject.event_id;
            var service_user_id = "{{ $service_user_id }}";
            var token = "{{ csrf_token() }}";
            var event_year = date.getFullYear();
            var event_month = date.getMonth() + 1;
            var event_day = date.getDate();
            var event_date = event_year + '-' + event_month + '-' + event_day;

            $.ajax({
                type: 'post',
                //url  : "{{ url('/service/calendar/add-event') }}",
                url: "{{ url('/service/calendar/event/add') }}",
                data: {
                    'service_user_id': service_user_id,
                    'event_type': event_type,
                    'event_id': event_id,
                    'event_date': event_date,
                    '_token': token
                },
                dataType: 'json',
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    var calendar_id = resp['calendar_id'];
                    if (response == true) {

                        // we need to copy it, so that multiple events don't have a reference to the same object
                        var copiedEventObject = $.extend({}, originalEventObject);
                        // assign it the date that was reported
                        copiedEventObject.start = date;
                        copiedEventObject.allDay = allDay;
                        copiedEventObject.calendar_id = calendar_id;

                        // render the event on the calendar
                        // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                        $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
                        // is the "remove after drop" checkbox checked?
                        //if ($('#drop-remove').is(':checked')) {
                        // if so, remove the element from the "Draggable Events" list
                        selected_event.remove();
                        //}
                        $('.loader').hide();
                    } else {
                        $('.loader').hide();
                        alert('Event could not be added. Please try again after some time.');
                    }
                }
            });
            return false;
        },
        eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc) {

            //alert(event.title + " was dropped on " + event.start.format());
            //alert(event.title + " was dropped on " + event.start);
            if (confirm("Are you sure about this change?")) {

                $('.loader').show();
                var event_calendar_id = event.calendar_id;

                var token = "{{ csrf_token() }}";

                var event_year = event.start.getFullYear();
                var event_month = event.start.getMonth() + 1;
                var event_day = event.start.getDate();
                var event_date = event_year + '-' + event_month + '-' + event_day;
                $.ajax({
                    type: 'post',
                    url: "{{ url('/service/calendar/event/move') }}",
                    data: {
                        'event_calendar_id': event_calendar_id,
                        '_token': token,
                        'event_date': event_date
                    },
                    success: function(resp) {
                        if (isAuthenticated(resp) == false) {
                            return false;
                        }
                        if (resp == 'false') {
                            revertFunc();
                            $('.ajax-alert-err').show();
                            $('.ajax-alert-err .msg').text("{{ COMMON_ERROR }}");
                            setTimeout(function() {
                                $(".notification-box").fadeOut()
                            }, 5000);
                        }
                        $('.loader').hide();
                    }
                });
            } else {
                revertFunc();
            }
        },
        events: [

            <?php
            if (!empty($health_records)) {
                foreach ($health_records as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['health_record_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#1fb5ad',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },

            <?php }
                }
            } ?>
            <?php
            if (!empty($daily_records)) {
                foreach ($daily_records as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['su_daily_record_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["description"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                            color: '#99CBE2',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
            <?php
            if (!empty($living_skills)) {
                foreach ($living_skills as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['su_living_skill_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["description"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                            color: '#A9D86E',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
            <?php
            if (!empty($education_records)) {
                foreach ($education_records as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['su_education_record_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["description"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day}}'),
                            color: '#344860',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
            <?php
            if (!empty($su_incentives)) {
                foreach ($su_incentives as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['su_ern_inc_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["name"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#9566B6',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
            <?php
            if (!empty($event_records)) {
                foreach ($event_records as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['su_calendar_event_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#3398CC',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
            <?php
            if (!empty($calender_notes)) { //echo '<pre>'; print_r($calender_notes); die;
                foreach ($calender_notes as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        // if(strlen($value['title']) > 42){
                        //     $event_title = substr($value['title'],0,42).'...';
                        // } else{
                        //     $event_title = substr($value['title'],0,42);       
                        // }
                        //$pattern     = '/[^a-zA-Z0-9]/u';
                        // $event_title = preg_replace($pattern, ' ', (string) $event_title);
                        $event_id    = $value['id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["note_title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#999999',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php  }
                }
            } ?>

            <?php
                if (!empty($mandatory_leave_records)) { //echo '<pre>'; print_r($calender_notes); die;
                    foreach ($mandatory_leave_records as $key => $value) {
                        if (!empty($value['calendar_id'])) {
                            $event_day   = date('d', strtotime($value['event_date']));
                            $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                            $event_year  = date('Y', strtotime($value['event_date']));
                            $event_id    = $value['mandatory_leaves_id'];
                            $event_type  = $value['event_type'];
                            $calendar_id = $value['calendar_id'];
                ?> {
                                title: '{{ $value["title"] }}',
                                start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                                color: '#7f2222b0',
                                event_id: '{{ $event_id }}',
                                event_type: '{{ $event_type }}',
                                calendar_id: '{{ $calendar_id }}'
                            },
                <?php  }
                    }
            } ?>


            <?php
            if (!empty($su_log_books)) {
                foreach ($su_log_books as $key => $value) {
                    if (!empty($value['calendar_id'])) {
                        $event_day   = date('d', strtotime($value['event_date']));
                        $event_mon   = date('m', strtotime($value['event_date'])) - 1;
                        $event_year  = date('Y', strtotime($value['event_date']));
                        $event_id    = $value['log_book_id'];
                        $event_type  = $value['event_type'];
                        $calendar_id = $value['calendar_id'];
            ?> {
                            title: '{{ $value["log_title"] }}',
                            start: new Date('{{ $event_year }}', '{{ $event_mon }}', '{{ $event_day }}'),
                            color: '#0275d8',
                            event_id: '{{ $event_id }}',
                            event_type: '{{ $event_type }}',
                            calendar_id: '{{ $calendar_id }}'
                        },
            <?php }
                }
            } ?>
        ],
        eventClick: function(event) {
            //alert(1);
            //console.log(event);
            var event_calendar_id = event.calendar_id;
            var event_type = event.event_type;
            var event_id = event.event_id;
            var remove_url = "{{ url('/service/calendar/event/remove') }}" + '/' + event_calendar_id;
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/service/calendar/event/view') }}",
                data: {
                    'event_type': event_type,
                    'event_id': event_id,
                    'calendar_id': event_calendar_id
                },
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    $('.calendar-event-details').html(resp);
                    $('.remove-cal-event').attr('href', remove_url);
                    $('#calndrViewEvent').modal('show');
                    setTimeout(function() {
                        autosize($("textarea"));
                    }, 200);
                    $('.edit_cal_event').removeClass('active');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
            /*if (event.url) {
                window.open(event.url);
                return false;
            }*/

        },
        eventAfterRender: function(event, element, view) {
            var isiPad = /ipad/i.test(navigator.userAgent.toLowerCase());
            if (jQuery.browser.mobile || isiPad) {
                element.draggable();
                $('.fc-view tbody').draggable();
            }
        }

    });
</script>
<!--
Note: here in calender month is one number in minus
 -->
<script type="text/javascript">
    // $(document).delegate('.event_label_close', 'click',function(){
    //     var ths = $(this);
    //     // return confirm("Do you want to delete it ?");
    //     var daily_record_id = $(this).parent('.event_label').attr('event_id');
    //     // console.log(daily_record_id);
    //     $.ajax({
    //         type:'get',
    //         url:"{{ url('/service/calendar/daily-records/delete')}}"+"/"+daily_record_id,
    //         success:function(resp){
    //             if(isAuthenticated(resp) == false){
    //                 return false;
    //             }

    //             // console.log(resp);
    //             if(resp == '1'){
    //                 // console.log('ok');
    //                 $(ths).closest('.event_label').remove();
    //             }
    //         }
    //     });
    // });
</script>
<script type="text/javascript">
    $(document).on('click', '.su_del_hel_rec, .su_del_daily_rec, .su_del_skill_rec, .su_del_edu_rec, .su_del_incentive_rec, .su_del_event_rec, .su_del_cal_rec, .su_del_log_book_rec', function() {
        var ths = $(this);
        var length = $(ths).parent().length;
        // console.log(length);
        var record_type = $(this).parent().attr('recrd_type');
        // console.log(record_type);
        var event_id = $(this).parent().attr('event_id');
        // console.log(event_id); 
        var _token = "{{ csrf_token()}}";
        $.ajax({
            type: 'post',
            url: "{{ url('/service/calendar/daily-records/delete') }}" + '/' + event_id,
            data: {
                'record_type': record_type,
                '_token': _token
            },
            success: function(resp) {
                // console.log(resp);
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == '1') {
                    var leng = Number(length) - 1;
                    // console.log(l);
                    if (leng == '0') {
                        $(ths).closest('.cal_evnt_scroller').next('.bordr-div').remove();
                    }
                    $(ths).parent().remove();
                }
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {

        $(".dele").on("click", function() {
            return confirm("Do you want to delete it ?");
        });
    });
</script>
@endsection