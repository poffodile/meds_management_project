@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Staff Timesheet')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')



<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Overtime</h4>
                    </header>
                    <div class="panel-body">
                        <div class="absenceHistory">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="OvertimeheadingOrBtn">  
                                        <a href="#!" type="button" class="btn btn-warning">Log overtime</a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel">
                                        <header class="panel-heading"> Time off in lieu (TOIL) </header>
                                        <div class="panel-body">
                                            <div class="overTimeBox">
                                                <div class="row">                                                
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd m-t-20">
                                                            <label>TOIL logged</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m</strong>
                                                                    <span>No approved claims</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd borderLeftRight m-t-20">
                                                            <label>TOIL taken</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m </strong>
                                                                    <span>No TOIL absences</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd m-t-20">
                                                            <label>TOIL balance</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m</strong>
                                                                    <span>Available to take</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 m-t-20 text-center">
                                                        <a href="#!" type="button" class="btn btn-warning">Use TOIL</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="panel">
                                        <header class="panel-heading"> Payable </header>
                                        <div class="panel-body">
                                            <div class="overTimeBox">
                                                <div class="row">                                                
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd m-t-20">
                                                            <label>Overtime logged</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m</strong>
                                                                    <span>No approved claims</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd borderLeftRight m-t-20">
                                                            <label>Paid</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m </strong>
                                                                    <span>Payment scheduled or paid</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="absenceAdd m-t-20">
                                                            <label>Pending payment</label>
                                                            <div class="timeHrsMinuts m-t-20 m-b-20">
                                                                <div class="timelist">
                                                                    <strong>0h 0m</strong>
                                                                    <span>Approved awaiting payment</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- <div class="col-md-12 m-t-20 text-center">
                                                        <a href="#!" type="button" class="btn btn-warning">Use TOIL</a>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="absenceAccordion">
                                        <div class="panel-group" id="accordion">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">
                                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Current & future (0)</a>
                                                    </h4>
                                                </div>
                                                <div id="collapseOne" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                Shank fatback pastrami turkey ham hock. Pastrami biltong ham ball tip meatloaf drumstick bacon jowl kielbasa.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h4 class="panel-title">
                                                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">History (0)</a>
                                                    </h4>
                                                </div>
                                                <div id="collapseTwo" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                Shank fatback pastrami turkey ham hock. Pastrami pork. Turducken cow salami venison, biltong ham ball tip meatloaf drumstick bacon jowl kielbasa.
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection