@extends('frontEnd.layouts.master')
@section('title','Child Management')
@section('content')


<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->

        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <input type="hidden" name="service_user_id" class="selected_su_id" value="">
                    <?php 
                        foreach($patients as $patient) {
                            $user_image = $patient->image;
                            $afc_status = App\ServiceUser::get_afc_status($patient->id);
                            if($afc_status == 1) {  
                                $profile_colors = 'profile_active';
                            } else {
                                $profile_colors = 'profile_inactive';
                            }
                            if(empty($user_image)){
                                $user_image = 'default_user.jpg';
                            } 
                        $notifi_count_personal = App\ServiceUserManagement::personalDetailNotifyCount($patient->id);
                        
                        if($notifi_count_personal == 0) {
                            $notifi_count_personal_clr = 'label-success';
                        } else if($notifi_count_personal <= 10) {
                            $notifi_count_personal_clr = 'label-warning';
                        } else {
                            $notifi_count_personal_clr = 'label-danger';
                        }

                        // $notifi_count_hr = App\Notification::getNotificationCount($patient->id,1);
                        //$notifi_count_dr = App\Notification::getNotificationCount($patient->id,2);
                        //$notifi_count_es = App\Notification::getNotificationCount($patient->id,3);
                        //$notifi_count_pp = App\Notification::getNotificationCount($patient->id,4);
                        
                        $notifi_pp = App\ServiceUserManagement::placementNotifyCount($patient->id);
                        // echo "<pre>"; print_r($notifi_pp); die;
                        $ern_schme_count  = App\ServiceUserManagement::EarningNotifyCount($patient->id);
                        $afc_status_count = App\ServiceUserManagement::AFCNotifyCount($patient->id);

                        $health_record_count = App\ServiceUserManagement::healthRecordCount($patient->id);

                        /*$risk_notif = DB::table('su_risk')
                                ->select('su_risk.id as su_risk_id','su_rmp.id as su_rmp_id', 'su_incident_report.id as su_incident_record_id','risk.description as risk_name')
                                ->where('su_risk.service_user_id',$patient->id)
                                ->join('risk', 'su_risk.risk_id','=', 'risk.id')                                            
                                ->leftJoin('su_rmp', 'su_risk.id', '=', 'su_rmp.su_risk_id')
                                ->leftJoin('su_incident_report', 'su_risk.id', '=', 'su_incident_report.su_risk_id')
                                //->orderBy('su_risk.id','desc')
                                ->count();*/
                        $risk_notif = App\ServiceUserRisk::riskNotifCount($patient->id);
                        // echo $risk_notif; die;
                        // if($risk_notif == 0) {
                        //     $notif_color = 'label-success';
                        // } else if($risk_notif <= 10) {
                        //     $notif_color = 'label-warning';
                        // } else {
                        //     $notif_color = 'label-danger';
                        // }

                        $risk_status = App\Risk::overallRiskStatus($patient->id);
                        if($risk_status == 1){
                            // $color = 'orange-clr';
                            // $risk_status = 'Historic';
                            $notif_color = 'label-warning';
                        } else if($risk_status == 2){
                            // $color = 'red-clr';
                            // $risk_status = 'High';
                            $notif_color = 'label-danger';
                        } else{
                            // $color = 'darkgreen-clr';
                            // $risk_status = 'No';
                            $notif_color = 'label-success';
                        }
  
                    ?>
                    <div class="col-md-6" >
                        <!--widget start-->
                        <aside class="profile-nav alt">
                            <section class="panel">
                                <div class="user-heading cususr-head alt gray-bg">
                                    <a href="{{ url('/service/user-profile/'.$patient->id) }}" su_id="{{ $patient->id }}" class="profile_click {{ $profile_colors }} su-set-btn"><!-- sum_profile_click -->
                                    <img alt="user image" src="{{ serviceUserProfileImagePath.'/'.$user_image }}" class="">
                                    </a>
                                    <h1><a href="{{ url('/service/user-profile/'.$patient->id) }}" class="name-clr">{{ $patient->name }}</a></h1>
                                    <p>Section {{ $patient->section }}</p>
                                </div>
                                <?php //echo '<pre>'; print_r($labels); die; ?>
                                <ul class="nav nav-pills nav-stacked">
                                    <li ><a href="{{ url('/select/report?key=' . base64_encode($patient->id)) }}"> <i class="fa fa-tachometer"></i> Dashboard <span class="badge label-success pull-right {{ $notifi_count_personal_clr }}">{{ (strlen($notifi_count_personal) < 2) ? '0':'' }}{{ $notifi_count_personal }}</span></a></li>
                                    
                                    <li ><a href="{{ url('/service/user-profile/'.$patient->id) }}"> <i class="fa fa-pencil"></i> Personal Details <span class="badge label-success pull-right {{ $notifi_count_personal_clr }}">{{ (strlen($notifi_count_personal) < 2) ? '0':'' }}{{ $notifi_count_personal }}</span></a></li>

                                    <li><a href="{{ url('/service/earning-scheme/'.$patient->id) }}"> <i class="{{ $labels['earning_scheme']['icon'] }}"></i> {{$labels['earning_scheme']['label']}} <span class="badge pull-right r-activity {{ $ern_schme_count['color'] }}">{{ (strlen($ern_schme_count['count']) < 2) ? '0':'' }}{{ $ern_schme_count['count'] }}</span></a></li>
                                    <!-- daily-record-list -->
                                    <!-- <li><a href="{{ url('/service/user-profile/'.$patient->id) }}" su_id="{{ $patient->id }}" class=" su-set-btn"> <i class="{{ $labels['mfc']['icon'] }}"></i>{{ $labels['mfc']['label'] }} <span class="badge pull-right r-activity {{ $afc_status_count['color'] }}">{{ (strlen($afc_status_count['count']) < 2) ? '0':'' }}{{ $afc_status_count['count'] }}</span></a></li> -->
                                    <li><a href="{{ url('/service/missing-care-form-records/'.$patient->id) }}" su_id="{{ $patient->id }}" class=" su-set-btn"> <i class="{{ $labels['mfc']['icon'] }}"></i>{{ $labels['mfc']['label'] }} <span class="badge pull-right r-activity {{ $afc_status_count['color'] }}">{{ (strlen($afc_status_count['count']) < 2) ? '0':'' }}{{ $afc_status_count['count'] }}</span></a></li>

                                    <!-- <li><a href="javascript:;" su_id="$patient->id" class="daily-record-list su-set-btn"> <i class="fa fa-calendar"></i>$labels['daily_record']['label'] <span class="badge pull-right r-activity  $notifi_count_dr['color'] "> (strlen($notifi_count_dr['count']) < 2) ? '0':''  $notifi_count_dr['count'] </span></a></li> -->

                                    <li><a href="{{ url('/service/health-records/'.$patient->id) }}"> <i class="{{ $labels['health_record']['icon'] }}"></i>{{$labels['health_record']['label']}} <span class="badge pull-right r-activity {{ $health_record_count['color'] }}">{{ (strlen($health_record_count['count']) < 2) ? '0':'' }}{{ $health_record_count['count'] }}</span></a></li>

                                    <li><a href="{{ url('/service/placement-plans/'.$patient->id) }}"> <i class="{{$labels['placement_plan']['icon']}}"></i> {{ $labels['placement_plan']['label'] }} <span class="badge pull-right r-activity {{ $notifi_pp['color'] }}">{{ (strlen($notifi_pp['count']) < 2) ? '0':'' }}{{ $notifi_pp['count'] }}</span></a></li>

                                    <!-- <li><a href="{{ url('/service/user-profile/'.$patient->id) }}"> <i class="fa fa-scissors"></i> Risks <span class="badge {{ $notif_color }} pull-right r-activity">{{ (strlen($risk_notif) < 2) ? '0' : '' }}{{ $risk_notif }}</span></a></li> -->
                                    <li><a href="{{ url('/service/risks/'.$patient->id) }}"> <i class="fa fa-scissors"></i> Risks <span class="badge {{ $notif_color }} pull-right r-activity">{{ (strlen($risk_notif) < 2) ? '0' : '' }}{{ $risk_notif }}</span></a></li>
                                </ul>
                            </section>
                        </aside>
                        <!--widget end-->
                    </div>
                    <?php } ?>
                </div>
                <div class="row"></div>
                <div class="row"></div>
                <div class="row"></div>
            </div>
            <div class="col-md-4">
                <div class="feed-box text-center">
                </div>
                <div class="profile-nav alt">
                </div>
                <section class="panel">
                    @include('frontEnd.common.notification_bar')
                </section>
            </div>
        </div>
        <!--mini statistics start--><!--mini statistics end-->
        <div class="row"></div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
</section>
<?php $service_user_id = isset($service_user_id) ? $service_user_id : ''; ?>

    <!-- include('frontEnd.common.incident_report') -->

    @include('frontEnd.serviceUserManagement.elements.daily_record')  
    @include('frontEnd.serviceUserManagement.elements.health_record')
    @include('frontEnd.serviceUserManagement.elements.wear_record')

<script>
    // profile status change
    $(document).ready(function(){

        // $('.sum_profile_click').click(function(){
        //     var su_id = $(this).attr('id');
        //     window.location.href = "{{ url('/service/user-profile') }}"+'/'+su_id;
        // });

        // $('.sum_profile_click').mousedown(function(e) {
        //     switch (event.which) {
        //         case 1: 
        //         alert('Left Mouse button pressed.');
        //         break;
        //         case 2:
        //         alert('Middle Mouse button pressed.');
        //         break;
        //         case 3: 
        //         alert('Right Mouse button pressed.');
        //         break;
        //         default:
        //         alert('You have a strange Mouse!');
        //     }
        //     if(e.button == 2) {
        //         alert('Right Mouse button pressed.');
        //         e.preventDefault();
        //         return false;
        //     } else {
        //          alert('Left Mouse button pressed.');
        //     }

        // });

        //photo right click functionality
        // $(function () {
        //     $('.sum_profile_click').bind('contextmenu', function (e) {
        //         var service_user_id = $(this).attr('id');
        //         $(this).addClass('profile_active_status');
        //         $('.loader').show();
        //         $('body').addClass('body-overflow');

        //     $.ajax({
        //         type   : 'get',
        //         url    : "{{ url('/service/user-profile/afc-status/update/') }}"+'/'+service_user_id,
        //         success:function(resp){ 
        //             if(isAuthenticated(resp) == false){
        //                 return false;
        //             } 
        //             if(resp == 'true') {                       
        //                 if($('.profile_active_status').hasClass('profile_active')) {
        //                     $('.profile_active_status').removeClass('profile_active');
        //                     $('.profile_active_status').addClass('profile_inactive');
        //                 } else {
        //                     $('.profile_active_status').removeClass('profile_inactive')
        //                      $('.profile_active_status').addClass('profile_active');
        //                 }
        //                 //show success message
        //                 $('.ajax-alert-suc').find('.msg').text('MFC/AFC status has been changed successfully.');
        //                 $('.ajax-alert-suc').show();
        //                 setTimeout(function(){$(".ajax-alert-suc").fadeOut()}, 5000);
        //             } else if(resp == 'AUTH_ERR') {
        //                 $('.ajax-alert-err').find('.msg').text("{{ UNAUTHORIZE_ERR }}");
        //                 $('.ajax-alert-err').show();
        //                 setTimeout(function(){$(".ajax-alert-err").fadeOut()}, 5000);
        //             } else { 
        //                 $('.ajax-alert-err').find('.msg').text('Some Error Occured. Status can not be updated.');
        //                 $('.ajax-alert-err').show();
        //                 setTimeout(function(){$(".ajax-alert-err").fadeOut()}, 5000);
        //             } 
        //             $('.profile_active_status').removeClass('profile_active_status');
        //             $('.loader').hide();
        //             $('body').removeClass('body-overflow');
        //         }
        //     });
        //     return false;
        //     e.preventDefault();
        //     });
        // });
    });
</script>
<script >
    //show popups of links
    $('.su-set-btn').click(function(){
        //saving the current selected Child id in a temporary location
        var su_id = $(this).attr('su_id');
        //alert(su_id);
        $('.selected_su_id').val(su_id);
       
        //updating calendar url acc. to su id
        var calndr_url = "{{ url('/service/calendar') }}"+'/'+su_id;
        $('.su-botm-calndr').attr('href',calndr_url);
        $('.add-new-btn').click();

    });

    // photo right click functionality  
    //$(function () {
        $('.profile_click').bind('contextmenu', function (e) {
            var su_id = $(this).attr('su_id');
            $('.selected_su_id').val(su_id);
            e.preventDefault();
        });
    //});
</script>

<!-- <script>
    $(document).ready(function(){
        // console.log('asd');
        $('.settings').on('click',function(){
            console.log('asd');
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

@endsection