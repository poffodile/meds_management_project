@extends('frontEnd.layouts.master')
@section('title','Dashboard')
@section('content')
<style type="text/css">
    .wdgt-value {
        padding: 24px 0;
    }

    .wdgt-value h1 {
        font-size: 21px;
    }

    .lightRed {
        background: #f38d8d;
    }

</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper p-t-80">
        <div class="container p-0">
            <div class="col-md-7 col-sm-7 col-xs-12 p-0">
            <!-- style="display: none" -->
             @if(in_array(1, $access_rights))
                <a href="{{ url('/service-user-management') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row bg-yellow"> <i class="fa fa-briefcase"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Child Management</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
            @endif
            @if(in_array(534, $access_rights))
                <a href="{{url('service/daily-logs')}}">
                <div class="col-md-6">
                    <div class="profile-nav alt">
                            <!-- #PoliProcModal -->
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row lavender_pink"> <i class="fa fa-book"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Daily Log</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        
                    </div>
                </div>
            </a>
            @endif
            <!-- @if(in_array(535, $access_rights)) -->
                <a href="{{url('/forms')}}">
                    <!-- data-target="#" data-toggle="modal" class="MainNavText"   -->
                    <!-- dynmicFormModal -->
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row lightRed"> <i class="fa fa-wpforms"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Forms</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
            <!-- @endif -->
            @if(in_array(67, $access_rights))
                <a href="{{ url('/system/calendar') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row label-daily"> <i class="fa fa-calendar"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Calendar</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
            @endif
                <?php if($accessRight === true) { ?>
                <a href="{{ url('/rota_management') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row grayish_green"> <i class="fa fa-group"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Rota Management</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a> <?php } ?>
                 <div class="col-md-6">
                    <div class="profile-nav alt">
                    <!-- #PoliProcModal  -->
                     @if(in_array(211, $access_rights))
                        <a data-target="#PoliProcModal" data-toggle="modal" class="MainNavText" >   
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row terques-bg"> <i class="fa fa-book"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Policies & Procedures</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </a>
                        @endif
                    </div>
                </div> 
                @if(in_array(159, $access_rights))
                <a href="{{ url('/staff-management') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row bg-jamni"> <i class="fa fa-group"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Staff Management</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
                @endif
                @if(in_array(3, $access_rights))
                <a href="{{ url('/system-management') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row bg-green"> <i class="fa fa-pencil "></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">System Management</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
                @endif
                <a href="{{ url('/view-reports') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row bg-pink"> <i class="fa fa-file-text-o"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">View Reports</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
                @if(in_array(172, $access_rights))
                <a href="{{ url('/general-admin') }}">
                     <div class="col-md-6">
                        <div class="profile-nav alt">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row bg-blue"> <i class="fa fa-cogs"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">General Admin</h1>
                                        <p></p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </a>
                @endif
                @if(in_array(324, $access_rights))
                <a href="{{ url('/sales') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <div class="panel text-center">
                                <div class="user-heading alt wdgt-row orange-bg"> <i class="fa fa-briefcase"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Sales</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endif
                <!-- <a href="{{ url('/petty-cash/child_register') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <div class="panel text-center">
                                <div class="user-heading alt wdgt-row lightBrown"> <i class="fa fa-child"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Child Register</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a> -->
                @if(in_array(342, $access_rights))
                <a href="{{ url('/finance') }}">
                    <div class="col-md-6">
                        <div class="profile-nav alt">
                            <div class="panel text-center">
                                <div class="user-heading alt wdgt-row alert-bg-green"> <i class="fa fa-money"></i></div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">Finance</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endif
            </div>

            @include('frontEnd.common.sidebar_dashboard')
            @include('frontEnd.policies_procedures')

        </div>
    </section>
</section> 

<!--main content end-->
@endsection