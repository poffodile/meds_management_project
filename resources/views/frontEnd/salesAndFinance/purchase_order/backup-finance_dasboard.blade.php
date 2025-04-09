@extends('frontEnd.layouts.master')

@section('title','Finance')

@section('content')

<style>
    img {
        background-color: white;
        width: 68%;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 2px;
    }

    .left-border {
        border-right: 1px solid #ddd;
    }

    .re-generateQR {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-right: 11px;
        margin-bottom: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .re-generateQR span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .re-generateQR span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .re-generateQR:hover span {
        padding-right: 20px;
    }

    .re-generateQR:hover span:after {
        opacity: 1;
        right: 0;
    }

    .view-QR {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-bottom: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .view-QR span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .view-QR span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .view-QR:hover span {
        padding-right: 20px;
    }

    .view-QR:hover span:after {
        opacity: 1;
        right: 0;
    }

    #download {
        width: 68%;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    #download button {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-top: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    #download button span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    #download button span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    #download button:hover span {
        padding-right: 20px;
    }

    #download button:hover span:after {
        opacity: 1;
        right: 0;
    }

    .set-width {
        width: 68%;
    }

    #overview .panel-body {
        min-height: auto;
    }

    [data-tab-finance-content] {
        display: none;
    }

    .active[data-tab-finance-content] {
        display: block;
        border-top: 1px solid #ddd;
        padding: 20px;
    }

    .inner_cards .icon {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        height: 90px;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        box-shadow: 0px 0px 20px rgb(0 0 0 / 10%);
    }

    .inner_cards a {
        text-decoration: none;
    }

    .inner_cards .icon.icon1 {
        background-color: #9466b5;
    }

    .inner_cards .icon.icon2 {
        background-color: #99cce3;
    }

    .inner_cards .icon.icon3 {
        background-color: #1fb5ad;
    }

    .inner_cards .icon.icon4 {
        background-color: #789DBC;
    }

    .inner_cards .icon.icon5 {
        background-color: #9466b5;
    }

    .inner_cards .icon.icon6 {
        background-color: #89A8B2;
    }

    .inner_cards .icon.icon7 {
        background-color: #727D73;
    }

    .inner_cards .icon.icon8 {
        background-color: #3674B5;
    }

    .inner_cards .icon.icon9 {
        background-color: #FF8A8A;
    }

    .inner_cards .icon.icon10 {
        background-color: #B17F59;
    }


    .inner_cards .icon i {
        font-size: 45px;
    }

    .inner_cards .card_name {
        padding: 20px 15px;
        text-align: center;
        background-color: #fff;
        margin-bottom: 20px;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
        height: 100%;
        min-height: 79px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .inner_cards .card_name h4 {
        margin-bottom: 0px;
        margin-top: 0px;
        font-size: 15px;
        color: #444;
        line-height: normal;
    }

    .inner_cards {
        box-shadow: 0px 0px 20px rgb(0 0 0 / 10%);
        position: relative;
    }

    .show_dropdown {
        position: absolute;
        background-color: #fff;
        width: 95%;
        top: 85px;
        left: 27px;
        box-shadow: 0px 0px 20px rgb(0 0 0 / 10%);
        border-radius: 5px;
    }

    .show_dropdown a div {
        padding: 10px;
        display: flex;
        gap: 10px;
        align-items: baseline;
        border-bottom: 1px solid #ddd;
    }

    .show_dropdown a div:hover {
        color: #1f88b5;
    }

    .show_dropdown::before {
        content: "";
        border-bottom: 7px solid #fff;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        position: absolute;
        top: -7px;
        left: 20px;
        box-shadow: 0px 0px 20px rgb(0 0 0 / 10%)
    }
</style>

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
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Purchase_Order">
                                        <div class="profile-nav alt">
                                            <div class="panel text-center">
                                                <div class="user-heading alt wdgt-row bg-blue">
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
                                    <!-- fa-hourglass-end  fa-life-ring  fa-hourglass-half  fa-hourglass-start  fa-clock-o  fa-tachometer  fa-sliders   -->
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12 tab_finance" data-tab-finance-target="#Petty_Cash">
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
                                    </div>
                                    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-12">
                                        <a href="{{ url('/petty-cash/child_register') }}">
                                            <div class="profile-nav alt">
                                                <div class="panel text-center">
                                                    <div class="user-heading alt wdgt-row purple-bg">
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
                                    </div>
                                </div>
                                <div class="tab_finance-content">
                                    <div id="Invoice" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div class="icon icon1">
                                                                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Dashboard</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/add')}}">
                                                                <div class="icon icon2">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>New Invoice</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Draft')}}">
                                                                <div class="icon icon3">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Draft Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Outstanding')}}">
                                                                <div class="icon icon4">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Outstanding Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Overdue')}}">
                                                                <div class="icon icon5">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Overdue Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('invoices/invoice?key=Paid')}}">
                                                                <div class="icon icon6">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Paid Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div class="icon icon7">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Invoices</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div class="icon icon8">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Account Statements</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div class="icon icon9">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Reminders</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown1">
                                                                <div class="icon icon10">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Recurring Invoices</h4>
                                                                </div>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown1 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>New Recurring Invoice </span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Recurring Invoice</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown2">
                                                                <div class="icon icon10">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Credit Notes</h4>
                                                                </div>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown2 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> New Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>Draft Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Awaiting Approval Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>Approval Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>Paid Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>Cancelled Credit Note</span>

                                                                    </div>
                                                                </a>
                                                            </div>
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
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="#">
                                                                <div class="icon icon8">
                                                                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Dashboard</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase_order')}}">
                                                                <div class="icon icon9">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>New Purchase Order </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('draft_purchase_order')}}">
                                                                <div class="icon icon10">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Draft Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order?list_mode=AwaitingApprivalPurchaseOrders') }}">
                                                                <div class="icon icon4">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Awaiting Approval Purchase Orders</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order?list_mode=Approved') }}">
                                                                <div class="icon icon5">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Approved Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order?list_mode=Rejected') }}">
                                                                <div class="icon icon6">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Rejected Purchase Order </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order?list_mode=Actioned') }}">
                                                                <div class="icon icon7">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Actioned Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('draft_purchase_order?list_mode=Paid') }}">
                                                                <div class="icon icon8">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Paid Purchase Order</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-orders-search')}}">
                                                                <div class="icon icon3">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Search Purchase Orders </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-order-invoices')}}">
                                                                <div class="icon icon2">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Invoices Received </h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{url('purchase-order-statements')}}">
                                                                <div class="icon icon1">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Purchase Orders Statements</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown3">
                                                                <div class="icon icon7">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Recurring Purchase Orders</h4>
                                                                </div>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown3 " style="display: none;">
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span>New Recurring Purchase Order</span>
                                                                    </div>
                                                                </a>
                                                                <a href="#!">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Recurring Purchase Orders</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <div class="inner_card_dropdown" data-target="show_inner_card_dropdown4">
                                                                <div class="icon icon3">
                                                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Credit Notes</h4>
                                                                </div>
                                                            </div>
                                                            <div class="show_dropdown show_inner_card_dropdown4 " style="display: none;">
                                                                <a href="{{url('new_credit_notes')}}">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> New Credit Note </span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes?list_mode=Approved')}}">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Approval Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes?list_mode=Paid')}}">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Paid Credit Note</span>
                                                                    </div>
                                                                </a>
                                                                <a href="{{url('credit_notes?list_mode=Cancelled')}}">
                                                                    <div>
                                                                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                                                        <span> Cancelled Credit Note</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Petty_Cash" data-tab-finance-content>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="{{ url('/petty-cash/expend-card') }}">
                                                                <div class="icon icon10">
                                                                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                                                                </div>
                                                                <div class="card_name">
                                                                    <h4>Expand Card</h4>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="inner_cards">
                                                            <a href="">
                                                                <div class="icon icon7">
                                                                    <i class="fa fa-money"></i>
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