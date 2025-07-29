@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')

<!-- Annual Leave -->
<section id="main-content">
    <div class="wrapper">
        @if($last_leave->leave_type == 1)
        <div class="panel mb-5">
            <div class="row working-time-pattern annual-leave">
                <div class="col-md-12">
                    <header class="panel-heading">
                        <h4 class="head">Annual leave added for {{ $username }}</h4>
                    </header>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="p-4">
                                <p class="mb-3">Thanks {{ $username }},</p>
                                <p class="mb-5">You have added this annual leave. You will be off from <strong>{{ \Carbon\Carbon::parse($last_leave->start_date)->format('D j M') }}</strong> until <strong>{{ \Carbon\Carbon::parse($last_leave->end_date)->format('D j M') }}</strong> and <strong> {{ date("g:i", strtotime($last_leave->late_by))   }} hrs </strong> will be deducted from your entitlement.</p>
                                <p class="mb-1"> <strong> Description </strong></p>
                                <p class="mb-2">{{ $last_leave->notes }}</p>
                                <div class="mt-5">
                                     <a href="{{ url('/rota-dashboard') }}" class="dash-btn">Back to dashboard</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- <div class="col-md-12">
                             <button type="button" class="absance-btn">Add absence</button>
                            <a href="{{ url('/rota-dashboard') }}" class="dash-btn">Back to dashboard</a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- Working Pattern form end here -->
        <!-- Working Pattern form start from here -->
        <!-- sickness  -->
        @if($last_leave->leave_type == 2)
        <div class="panel mb-5">
            <div class="row working-time-pattern annual-leave">
                <div class="col-md-12">
                    <div class="heading">
                        <h3 class="head">Sickness added for {{ $username }}</h3>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="p-4">
                        <div class="col-md-12">
                            <p class="mb-3">Thanks {{ $username }},</p>
                            <p class="mb-5">You have added this Sickness. You will be off from <strong>{{ \Carbon\Carbon::parse($last_leave->start_date)->format('D j M') }}</strong> until <strong>{{ \Carbon\Carbon::parse($last_leave->end_date)->format('D j M') }}.</strong></p>
                            <p class="mb-1"> <strong> Description </strong></p>
                            <p class="mb-2">{{ $last_leave->notes }}</p>
                            <div class="mt-5">
                                <a href="{{ url('/rota') }}" class="dash-btn">Back to dashboard</a>
                            </div>
                        </div>

                        <!-- <div class="col-md-12">
                            <button type="button" class="absance-btn">Add absence</button>                            
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- Working Pattern form end here -->
        <!-- Working Pattern form start from here -->
        <!-- Lateness -->
        @if($last_leave->leave_type == 3)
        <div class="panel mb-5">
            <div class="row working-time-pattern annual-leave">
                <div class="col-md-12">
                    <div class="heading">
                        <h3 class="head">Add lateness added for {{ $username }}</h3>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="p-4">
                        <div class="col-md-12">
                            <p class="mb-3">Thanks {{ $username }},</p>
                            <p class="mb-5">You have added this lateness. You were late by <strong>{{ $last_leave->late_by }}2 hrs</strong> on <strong>{{ \Carbon\Carbon::parse($last_leave->start_date)->format('D j M') }}.</strong></p>
                            <p class="mb-1"> <strong> Description </strong></p>
                            <p class="mb-2">{{ $last_leave->notes }}</p>
                            <div class="mt-5">
                                 <a href="{{ url('/rota') }}" class="dash-btn">Back to dashboard</a>
                            </div>
                        </div>

                        <!-- <div class="col-md-12">
                           <button type="button" class="absance-btn">Add absence</button>                           
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Working Pattern form end here -->
        <!-- Lateness -->
        @if($last_leave->leave_type == 4)
        <div class="panel mb-5">
            <div class="row working-time-pattern annual-leave">
                <div class="col-md-12">
                    <div class="heading">
                        <h3 class="head">Add other absence added for {{ $username }}</h3>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="p-4">
                        <div class="col-md-12">
                            <p class="mb-3">Thanks {{ $username }},</p>
                            <p class="mb-5">You have added this other absence. You were late by <strong>{{ $last_leave->late_by }}2 hrs</strong> on <strong>{{ \Carbon\Carbon::parse($last_leave->start_date)->format('D j M') }}.</strong></p>
                            <p class="mb-1"> <strong> Description </strong></p>
                            <p class="mb-2">{{ $last_leave->notes }}</p>
                             <div class="mt-5">
                                <a href="{{ url('/rota') }}" class="dash-btn">Back to dashboard</a>
                             </div>                            
                        </div>

                        <!-- <div class="col-md-12">
                           <button type="button" class="absance-btn">Add absence</button>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</section>
