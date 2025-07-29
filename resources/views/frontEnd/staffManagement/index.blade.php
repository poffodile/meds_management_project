@extends('frontEnd.layouts.master')
@section('title','Staff Management')
@section('content')

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <input type="hidden" name="staff_id" class="selected_staff_id" value="">
                    <?php $auth_user_id = Auth::user()->id;
                            // echo $auth_user_id; die;
                        foreach($staff as $staff) {
                            $user_image = $staff->image;

                            if($auth_user_id == $staff->id) {
                                $redirect_url = url('my-profile/'.$auth_user_id);
                                $check        = 'self_check';
                            } else {
                                $redirect_url = url('/staff/profile/'.$staff->id);
                                $check        = 'self_not_check';
                            }
                            if(empty($user_image)){
                                $user_image = 'default_user.jpg';
                            } 
                    ?>
                    <div class="col-md-6" >
                        <!--widget start-->
                        <aside class="profile-nav alt">
                            <section class="panel">
                                <div class="user-heading cususr-head alt gray-bg">
                                    <a href="{{ $redirect_url }}" class="" id="{{ $staff->id }}">
                                    <img alt="user image" src="{{ userProfileImagePath.'/'.$user_image }}" class="">
                                    </a>
                                    <h1><a href="{{ $redirect_url }}" class="name-clr">{{ $staff->name }}</a></h1>
                                    <p> {{ $staff->job_title }} </p>
                                </div>
                                <ul class="nav nav-pills nav-stacked">
                                    <li><a href="{{ $redirect_url }}"> <i class="fa fa-pencil"></i> Personal Details <!-- <span class="badge label-warning pull-right r-activity">04</span> --></a></li>
                                  <!--   <li><a href="javascript:;"> <i class="fa fa-star-half-o"></i> Contact Details <span class="badge label-warning pull-right r-activity">02</span></a></li> -->
                                @if($check == 'self_not_check')
                                    <li><a href="#annualLeaveModal" data-toggle="modal" class="staff-set-btn" staff_id="{{ $staff->id }}"> <i class="fa fa-files-o"></i> Annual Leave <!-- <span class="badge label-success pull-right r-activity">00</span> --></a></li>
                                    
                                    <li><a href="#sickLeaveModal" data-toggle="modal" class="staff-set-btn" staff_id="{{ $staff->id }}"> <i class="fa fa-bed"></i> Sick Records <!-- <span class="badge label-warning pull-right r-activity">02</span> --></a></li>

                                    <li><a href="javascript:;"  class="staff-set-btn task-allocation-list" staff_id="{{ $staff->id }}"> <i class="fa fa-calendar"></i> Task Allocation <!-- <span class="badge label-success pull-right r-activity">00</span> --></a></li>
                                @endif
                                @if($check == 'self_check')
                                    <li><a href="{{ $redirect_url }}" class="staff-set-btn"> <i class="fa fa-files-o"></i> Annual Leave <!-- <span class="badge label-success pull-right r-activity">00</span> --></a></li>
                                    
                                    <li><a href="{{ $redirect_url }}" class="staff-set-btn"> <i class="fa fa-bed"></i> Sick Records <!-- <span class="badge label-warning pull-right r-activity">02</span> --></a></li>

                                    <li><a href="{{ $redirect_url }}"  class="staff-set-btn"> <i class="fa fa-calendar"></i> Task Allocation <!-- <span class="badge label-success pull-right r-activity">00</span> --></a></li>
                                @endif
                                    <!-- <li><a href="{{ url('/staff/rota/view') }}"> <i class="fa fa-sliders"></i> Manage Rota </a></li>
                                      -->
                                    <li><a href="{{ url('/rota-dashboard') }}"> <i class="fa fa-sliders"></i> Manage Rota <!-- <span class="badge label-warning pull-right r-activity">02</span> --></a></li>
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

<?php $staff_id = isset($staff_id) ? $staff_id : ''; ?>

@include('frontEnd.staffManagement.elements.annual_leaves')
@include('frontEnd.staffManagement.elements.sick_leaves')
@include('frontEnd.staffManagement.elements.task_allocation')


<script >
    autosize($("textarea"));
    $('.staff-set-btn').click(function(){
        //saving the current selected Child id in a temporary location
        var staff_id = $(this).attr('staff_id');
        $('.selected_staff_id').val(staff_id);
        $('.add-new-btn').click();
    });
</script>


@endsection