@extends('frontEnd.layouts.master')
@section('title','Finance')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <!-- <header class="panel-heading tab-bg-dark-navy-blue cus-panel-heading">
                        <ul class="nav nav-tabs nav-justified ">
                            <li class="active">
                                <a data-toggle="tab" href="#overview"> Overview</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#manager_access_rights">Access Rights</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#my_profile_contacts" class="profile-contact-map"> Contacts</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#my_profile_info">Full Profile</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#profile_settings">Settings</a>
                            </li>
                        </ul>
                    </header> -->
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="overview" class="tab-pane active">
                                <div class="row tabs_finance">
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Day_book">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-pink">
                                                    <i class="fa fa-line-chart"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Vat Quater</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Fixed_Asset">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-green">
                                                    <i class="fa fa-window-maximize" aria-hidden="true"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Fixed Assets</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance">
                                        <a href="{{ url('/finance/council-tax') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row lightRed">
                                                        <i class="fa fa-university" aria-hidden="true"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Council Tax</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/petty-cash/expend-card') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row terques-bg">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Petty Cash</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/expenses') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row grayish_green">
                                                        <i class="fa fa-calculator"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Expenses</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Purchase_Order">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row lavender_pink">
                                                    <i class="fa fa-shopping-cart"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Purchase Order</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Invoice">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row purple-bg">
                                                    <i class="fa fa-file-text-o"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Invoice</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Items">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-blue">
                                                    <i class="fa fa-archive"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Items</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/finance/leave-tracker') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row bg-yellow">
                                                        <i class="fa fa-calculator"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Leave Tracker</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div> -->
                                    <!-- fa-hourglass-end  fa-life-ring  fa-hourglass-half  fa-hourglass-start  fa-clock-o  fa-tachometer  fa-sliders   -->

                                    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/petty-cash/child_register') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row Olive_gray">
                                                        <i class="fa fa-child"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Child Register</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div> -->
                                    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Quoting_System">
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
                                    </div> -->

                                    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="#!">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row bg-pink">
                                                        <i class="fa fa-cogs"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">Business Integrations</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div> -->
                                    <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                    <a href="#!">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row light_yellow">
                                                    <i class="fa fa-bullhorn"></i>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="wdgt-value">
                                                        <h4 class="count">Marketing</h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    </div> -->
                                        <!-- <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="#!">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row bg-yellow">
                                                        <i class="fa fa-bell"></i>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="wdgt-value">
                                                            <h4 class="count">EworksPay</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div> -->

                                </div>
                                <div class="tab_finance-content">
                                    <div id="Invoice" data-tab-finance-content>
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
                                                                    <h4>New Invoice</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice/Draft')}}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-floppy-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Draft Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice/Outstanding')}}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Outstanding Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice/Overdue')}}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-warning" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Overdue Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice/Paid')}}">
                                                                <div>
                                                                    <div class="icon icon6">
                                                                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Paid Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-search" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon8">
                                                                        <i class="fa fa-book" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Account Statements</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Reminders</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown1">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon10">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Recurring Invoices</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown1 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>New Recurring Invoice </span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Recurring Invoice</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown2">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon4">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Credit Notes</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown2 " style="display: none;">
                                                                <a href="{{url('new_credit_notes')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> New Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes/Approved')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Approved Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <!-- <a href="{{url('new_credit_notes')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Awaiting Approval Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('new_credit_notes')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Approval Credit Note</span>
                                                                    </div>
                                                                </a> -->
                                                                <a href="{{url('credit_notes/Paid')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Paid Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes/Cancelled')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>Cancelled Credit Note</span>

                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('/account_codes')}}">
                                                                <div>
                                                                    <div class="icon icon1">
                                                                        <i class="fa fa-calculator " aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Account Codes</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('/tax_rate')}}">
                                                                <div>
                                                                    <div class="icon icon2">
                                                                        <i class="fa fa-percent " aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Tax Rate</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Purchase_Order" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <!-- <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div>
                                                                    <div class="icon icon8">
                                                                        <i class="fa fa-tachometer" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Dashboard</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div> -->
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase_order')}}">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-cart-plus"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>New Purchase Order </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('draft_purchase_order')}}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-floppy-o"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Draft Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order/AwaitingApprivalPurchaseOrders') }}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <i class="fa fa-hourglass-half"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Awaiting Approval Purchase Orders</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order/Approved') }}">
                                                                <div>
                                                                    <div class="icon icon5">
                                                                        <i class="fa fa-check-circle"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Approved Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order/Rejected') }}">
                                                                <div>
                                                                    <div class="icon icon6">
                                                                        <i class="fa fa-times-circle"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Rejected Purchase Order </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order/Actioned') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-file-text"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Actioned Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order/Paid') }}">
                                                                <div>
                                                                    <div class="icon icon8">
                                                                        <i class="fa fa-thumbs-up"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Paid Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-orders-search')}}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-search" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Purchase Orders </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-order-invoices')}}">
                                                                <div>
                                                                    <div class="icon icon2">
                                                                        <i class="fa fa-file-text"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Invoices Received </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-order-statements')}}">
                                                                <div>
                                                                    <div class="icon icon1">
                                                                        <i class="fa fa-book"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Purchase Orders Statements</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown3">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon7">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Recurring Purchase Orders</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown3 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span>New Recurring Purchase Order</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Recurring Purchase Orders</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown4">
                                                                <a href="javascript:void(0)">
                                                                    <div>
                                                                        <div class="icon icon10">
                                                                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card_name">
                                                                        <h4>Credit Notes</h4>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown4 " style="display: none;">
                                                                <a href="{{url('new_credit_notes')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> New Credit Note </span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes/Approved')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Approval Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes/Paid')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Paid Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes/Cancelled')}}">
                                                                    <div>
                                                                        <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                                                                        <span> Cancelled Credit Note</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{url('departments')}}">
                                                                <div>
                                                                    <div class="icon icon4">
                                                                        <i class="fa fa-sitemap "></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Departments</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div id="Petty_Cash" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/petty-cash/expend-card') }}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-expand" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Expand Card</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-money"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Cash</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div id="Day_book" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/purchase/purchase-day-book') }}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-book" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Purchase Day Book</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{  url('sales/sales-day-book') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-book"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Sales Day Book</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('purchase/purchase-type') }}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-money"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Purchase Expenses Type</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Fixed_Asset" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('sales-finance/assets/asset-category') }}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-window-maximize" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Asset Catetgory</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/sales-finance/assets/depreciation-type') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Depreciation Type</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('sales-finance/assets/asset-register') }}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-bullseye" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Asset Register</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Council_Tax" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/petty-cash/expend-card') }}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-window-maximize" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Council Tax 1</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Council Tax 2</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-bullseye" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Council Tax 3</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Items" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/item/product_categories') }}">
                                                                <div>
                                                                    <div class="icon icon10">
                                                                        <i class="fa fa-th-large" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Product Categories</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/item/products') }}">
                                                                <div>
                                                                    <div class="icon icon7">
                                                                        <i class="fa fa-square" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Products</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/item/item-groups') }}">
                                                                <div>
                                                                    <div class="icon icon3">
                                                                        <i class="fa fa-th-large" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Product Groups</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 col-sm-6">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/item/catalogues') }}">
                                                                <div>
                                                                    <div class="icon icon9">
                                                                        <i class="fa fa-folder-open" aria-hidden="true"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Catalogues</h4>
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