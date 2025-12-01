@extends('frontEnd.layouts.master')
@section('title','System Management')
@section('content')
<!-- <script src="{{ url('public/frontEnd/js/jquery.js') }}"></script> -->
<!--main content start-->
<section id="main-content">
    <section class="wrapper p-t-80">
        <div class="container p-0">
            
            <div class="col-md-7 col-sm-7 col-xs-12">
                <!--progress bar start-->
                <section class="panel">
                    <header class="panel-heading">
                        System management
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="panel-body cus-panelbody">
                        <div class="col-md-6 col-sm-6 col-xs-6 add_user">
                            <div class="sys-mngmnt-box" >
                                <!-- data-toggle="modal" data-target="#addServiceUserModal" -->
                                <div class="sys-mngmnticon"> <i class="fa fa-user"></i> </div>
                                <p>Add Child</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 add_staff">
                            <div class="sys-mngmnt-box" >
                                <!-- data-toggle="modal" data-target="#addStaffModal" -->
                                <div class="sys-mngmnticon"> <i class="fa fa-users"></i> </div>
                                <p>Add Staff</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 risk-record">
                            <div class="sys-mngmnt-box">
                                <div class="sys-mngmnticon"> <i class="fa fa-scissors"></i> </div>
                                <p>Risks</p>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6 col-xs-6 earning-scheme">
                            <div class="sys-mngmnt-box">
                                <div class="sys-mngmnticon"> <i class="{{ $labels['earning_scheme']['icon'] }}"></i> </div>
                                <p>{{ $labels['earning_scheme']['label'] }}</p>
                            </div>
                        </div>
                        @if(!empty($earning_scheme_label))
                            @foreach($earning_scheme_label as $key => $label)
                                @if($label['label_type'] != 'M')
                                    <!-- @if($label['label_type'] == 'E')
                                        <div class="col-md-6 col-sm-6 col-xs-6 education-record">
                                    @elseif($label['label_type'] == 'G')
                                        <div class="col-md-6 col-sm-6 col-xs-6 daily-record">
                                    @elseif($label['label_type'] == 'I')
                                        <div class="col-md-6 col-sm-6 col-xs-6 living-skill">
                                    @endif-->
                                    <div class="col-md-6 col-sm-6 col-xs-6 label-record">
                                        <div class="sys-mngmnt-box">
                                            <div class="sys-mngmnticon"> <i class="{{ $label['icon'] }}"></i> </div>
                                            <input type="hidden" name="label_id" class="label_id" value="{{ $label['id'] }}">
                                            <input type="hidden" name="label_type" class="label_type" value="{{ $label['label_type'] }}">
                                             <!-- data-toggle="modal" href="#dailyrecordModal" -->
                                            <p>{{ $label['name'] }}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                        
                        <!-- <div class="col-md-6 col-sm-6 col-xs-6 mfc" >
                            <div class="sys-mngmnt-box ">
                                <div class="sys-mngmnticon"> <i class="{{ $labels['mfc']['icon'] }}"></i> </div>
                                <p>{{ $labels['mfc']['label'] }}</p>
                            </div>
                        </div> -->
                        <div class="col-md-6 col-sm-6 col-xs-6 health_record">
                            <div class="sys-mngmnt-box">
                                <div class="sys-mngmnticon"> <i class="{{ $labels['health_record']['icon'] }}"></i> </div>
                                <p>{{ $labels['health_record']['label'] }}</p>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6 col-xs-6 appointments">
                            <div class="sys-mngmnt-box">
                                <div class="sys-mngmnticon"> <i class="fa fa-map-marker"></i> </div>
                                <p>Appointments / Plans</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 support_ticket"> <!-- support_ticket -->
                            <div class="sys-mngmnt-box">
                                <div class="sys-mngmnticon"> <i class="fa fa-ticket"></i> </div>
                                <p>Support Ticket</p>
                            </div>
                        </div>

                    </div>
                </section>
                <!--progress bar end-->
            </div>

            @include('frontEnd.common.sidebar_dashboard')

        </div>
    </section>
</section>
<!--main content end-->
<!-- <script src="{{ url('public/frontEnd/js/jquery-1.8.3.min.js') }}"></script> -->

@include('frontEnd.common.add_user')
@include('frontEnd.systemManagement.elements.add_staff')
@include('frontEnd.systemManagement.elements.daily_record')
@include('frontEnd.systemManagement.elements.risk')
@include('frontEnd.systemManagement.elements.earning_scheme')
@include('frontEnd.systemManagement.elements.earn_schm_incentive')

@include('frontEnd.systemManagement.elements.living_skill')
@include('frontEnd.systemManagement.elements.education_record')
<!-- include('frontEnd.systemManagement.elements.mfc') -->

@include('frontEnd.systemManagement.elements.icon_list')
@include('frontEnd.systemManagement.elements.health_record')
@include('frontEnd.systemManagement.elements.support_ticket')
@include('frontEnd.systemManagement.elements.appointments')

<script>
    $(document).ready(function(){
        
        $(".add_staff").click(function(){
            autosize($("textarea"));
            $('#addStaffModal').modal('show');
        });

        $(".add_user").click(function(){
            autosize($("textarea"));
            $('#addServiceUserModal').modal('show');
        });

    });
</script>

<!-- <script>
    //cog icon on click event - options show
    $(document).ready(function(){ 
        $(document).on('click','.settings', function(){
            $(this).find('.pop-notifbox').toggleClass('active');
            $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
        });
        $(document).on('click',function(e){
            e.stopPropagation();
            var $trigger = $(".settings");
            if($trigger !== e.target && !$trigger.has(e.target).length){
                $('.pop-notifbox').removeClass('active');
            }
        });
    });
</script> -->
               
@endsection        