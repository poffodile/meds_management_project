@extends('frontEnd.layouts.master')
@section('title', 'Forms')
@section('content')

    <link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

 

    <section id="container">
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="pull-right">
                        <div class="filter_buttons"
                            style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                            <a data-toggle="modal" href="#dynmicFormModal" class="btn btn-primary  col-6"
                                id='add_new_log'>Add New</a>
                            <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6"
                                style="background-color:#d9534f;color:white;">PDF Export</a>
                        </div>
                    </div>
                </div>
                <!-- page start-->
                <div class="Select_staff">
                    <div class="Select_staff_inner">
                        <a class="back_opt col-3" onclick="history.back()">
                            <i class="fa fa-angle-left"></i>
                        </a>
                    </div>
                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <select class="form-control" name="staff_member" id="staff_member">
                            <option value="">Select Staff Member</option>
                            @foreach ($staff_members as $val)
                                <option value="{{ $val->id }}" <?php if (isset(Auth::user()->id)) {
                                    if (Auth::user()->id == $val->id) {
                                        echo 'Selected';
                                    }
                                } ?>>{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="Select_staff_inner">
                        <select class="form-control" name="service_user" id="service_user" <?php if (isset($_GET['key'])) {
                            echo 'disabled';
                        } ?>>
                            <option value="">Select Child</option>
                            @foreach ($service_users as $val)
                                <option <?php if (isset($_GET['key'])) {
                                    if ($_GET['key'] == $val->id) {
                                        echo 'Selected';
                                    }
                                } ?> value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <div class="datepicker-sttng date-sttng">
                            <label style="display: none;"> Date: </label>
                            <div>
                                @php
                                    $today = \Carbon\Carbon::now()->format('d-m-Y');
                                    $oneMonthAgo = \Carbon\Carbon::now()->subMonth()->format('d-m-Y');
                                @endphp
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                                    class="input-group date">
                                    <input id="date_range_input" style="cursor: pointer;" name="daterange"
                                        value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" 
                                        {{-- value="{{ $oneMonthAgo }} - {{ $today }}"  --}}
                                        type="text" readonly=""
                                        size="16" class="form-control log-book-datetime">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <button onclick="showDate()" class="btn btn-primary" type="button"><span
                                                class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <input type="text" class="form-control" id="keyword" onKeyPress="myFunctionkey()"
                            onKeyUp="myFunctionkey()" name="keyword" placeholder="Keyword">
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div class="timeline">
                            <article class="timeline-item alt">
                                <div class="text-right">
                                    <div class="time-show first">
                                        <a href="#" class="btn btn-primary" id="today">Today</a>
                                    </div>
                                </div>
                            </article>
                            <div class="timeline-messages">
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            13 Jan 2013
                                        </div>
                                        <div class="second bg-terques ">
                                            <p>Join as Product Asst. Manager</p>
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                
                                                    <input type="checkbox" class="dd-input">

                                                        <ul class="dropdown-menu dd-menu">
                                                            <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                            <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                            <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                            <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                            <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                        </ul>                                                 

                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    

                                                </label>                                                 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-red">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Completed Provition period and Appointed as a permanent Employee</p>
                                        </div>
                                        <div class="first">
                                            10 Feb 2012
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            2 January 2011
                                        </div>
                                        <div class="second bg-purple">
                                            <p>Selected Employee of the Month</p>
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Got Promotion and become area manager of California</p>
                                        </div>
                                        <div class="first">
                                            4 March 2010
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            3 April 2009
                                        </div>
                                        <div class="second bg-yellow">
                                            <p>Selected the Best Employee of the Year 2013 and was awarded</p>
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-terques">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p> Got Promotion and become Product Manager and was transper from Branch to Head Office. Lorem ipsum dolor sit amet</p>
                                        </div>
                                        <div class="first">
                                            23 May 2008
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            14 June 2007
                                        </div>
                                        <div class="second bg-blue">
                                            <p>Height Sales scored and break all of the previous sales record ever in the company. Awarded</p>
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="dropdown timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dropdown-menu dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->

                        </div>
                        <article class="timeline-item alt">
                            <div class="text-right">
                                <div class="time-show first">
                                    <a href="#" class="btn btn-primary" id="today">08/07/2025</a>
                                </div>
                            </div>
                        </article>
                        </div>
                    </div>
                </div>
                <!-- page end-->
            </section>
        </section>
        <!--main content end-->
    </section>




@endsection
