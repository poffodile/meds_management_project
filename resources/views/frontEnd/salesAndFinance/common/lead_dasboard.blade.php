@extends('frontEnd.layouts.master')

@section('title','Sales')

@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="overview" class="tab-pane active">
                                <div class="row tabs_finance">
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#lead">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-pink">
                                                    <i class="fa fa-file-text"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count"> Lead</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Quoting_System">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-green">
                                                    <i class="fa fa-file-text"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Quoting System</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab_finance-content">
                                    <div id="lead" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/leads/add') }}">
                                                                <div>
                                                                    <div class="icon icon1">
                                                                        <i class="fa fa-plus-square" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>New Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/leads/leads') }}">
                                                                <div>
                                                                    <div class="icon icon2">
                                                                        <i class="fa fa-users" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>All Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/lead/myLeads') }}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-user-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>My Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/leads/unassigned') }}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <i class="fa fa-question-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Unassigned Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/lead/actioned') }}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-check-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Actioned Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/lead/rejected') }}">
                                                                <div>
                                                                    <div class="icon icon6">
                                                                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Rejected Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/lead/authorization') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-shield" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Authorization</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/leads/converted') }}">
                                                                <div>
                                                                    <div class="icon icon8">
                                                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Converted Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('leads/search') }}">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-search" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Lead</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/leads/tasks') }}">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-tasks" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Lead Task</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Quoting_System" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon1">
                                                                        <i class="fa fa-tachometer" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Dashboard</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/add')}}">
                                                                <div>
                                                                    <div class="icon icon2">
                                                                        <i class="fa fa-plus-square" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>New Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Draft')}}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Draft Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Outstanding')}}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Actioned Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Overdue')}}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Call Back Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Paid')}}">
                                                                <div>
                                                                    <div class="icon icon6">
                                                                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Accepted Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Converted Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon8">
                                                                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Quote Reminders</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-search" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Quote</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown5">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon10">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Appointment</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown5 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Sales Appointment </span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown6">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon4">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Recurring Quote</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown6 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> New Recurring Quote</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Recurring Quote</span>
                                                                    </div>
                                                                </a>
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
        <!-- page end-->
    </div>
</section>

<script>
    const tabs = document.querySelectorAll('[data-tab-finance-target]');
    const tabContents = document.querySelectorAll('[data-tab-finance-content]');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = document.querySelector(tab.dataset.tabFinanceTarget);

            if (tab.classList.contains('active')) {
                tab.classList.remove('active');
                target.classList.remove('active');
            } else {
                tabContents.forEach(tabContent => tabContent.classList.remove('active'));
                tabs.forEach(tab => tab.classList.remove('active'));

                tab.classList.add('active');
                target.classList.add('active');
            }
        });
    });

    // dropdown show hide
    $(document).ready(function() {
        $(".inner_card_dropdown").click(function(event) {
            event.stopPropagation(); // Prevent click from propagating to document
            var target = $(this).data("target");
            $("." + target).toggle();
        });

        // Click anywhere on the document to close dropdown
        $(document).click(function(event) {
            if (!$(event.target).closest(".inner_card_dropdown, .show_dropdown").length) {
                $(".show_dropdown").hide();
            }
        });
    });
</script>

@endsection