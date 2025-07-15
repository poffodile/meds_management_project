@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Invoices')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')
<style>
    .currency {
        padding: 2px 3px 2px 5px;
        line-height: 17px;
        text-shadow: 0 1px 0 #ffffff;
        border: 1px solid #ccc;
        background-color: #efefef;
        margin-right: 5px;
    }

    .calendar_icon {
        color: #e10078;
        display: flex;
        align-items: center;
    }

    .disabled-tab {
        pointer-events: none;
        opacity: 0.5;
    }

    .productDetailTable table.table thead tr th,
    .productDetailTable table.table tbody tr td,
    .productDetailTable table.table tfoot tr td {
        font-size: 12px;
        line-height: 22px;
    }

    /* .totlepayment {
    width: 300px;
    margin-left: 46%;
    text-align: end;
} */
    .image_style {
        cursor: pointer;
    }

    .unclicked {
        pointer-events: none;
    }

    .productDetailTable table thead tr {
        white-space: nowrap;
        vertical-align: middle;
    }

    .image_delete_payment_paid {
        cursor: pointer;
    }

    .input_style table tbody textarea {
        resize: none;
        overflow: hidden;
        min-height: 50px;
    }

    #product_result tr td {
        vertical-align: baseline;
    }
</style>
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        @if((isset($key) && $key !='') && (isset($duplicate) && $duplicate ==''))
                        <h4 class="header_text">{{$purchase_orders->purchase_order_ref}}</h4>
                        @else
                        <h4 class="header_text">New Purchase Order</h4>
                        @endif
                    </header>
                    <div class="panel-body">
                        <div class="col-md-12">
                            <form class="customerForm" id="all_data">
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Supplier Details</h4>
                                        @csrf
                                        <input type="hidden" id="id" name="id" value="<?php if((isset($purchase_orders) && $purchase_orders != '') && (isset($duplicate) && $duplicate == '')) {echo $purchase_orders->id; } ?>">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Supplier <span class="radStar">*</span></label>
                                                        <div class="row">
                                                            <div class="col-sm-10">
                                                                <select class="form-control editInput selectOptions PurchaseOrdercheckError" id="purchase_supplier_id" name="supplier_id" onchange="get_supplier_details()">
                                                                    <option selected disabled>Select Supplier</option>
                                                                    <?php foreach ($suppliers as $suppVal) { ?>
                                                                        <option value="{{$suppVal->id}}" <?php if (isset($purchase_orders) && $suppVal->id == $purchase_orders->supplier_id) {echo 'selected';} ?>>{{$suppVal->name}}</option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-sm-1">
                                                                <a href="javascript:void(0)" class="formicon" data-toggle="modal" data-target="#supplierPop">
                                                                    <i class="fa fa-plus-square"></i></a>
                                                            </div>
                                                            <div class="col-sm-1" id="clock" style="display:none">
                                                                <a href="#!" class="formicon"><i class="fa fa-clock"></i></a>
                                                            </div>
                                                        </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Contact</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions" id="purchase_contact_id" name="contact_id" <?php if (!isset($purchase_orders) && $purchase_orders == '') {echo 'disabled'; } ?>>
                                                                <option selected disabled>Select Supplier First</option>
                                                                @foreach($additional_contact as $addContact)
                                                                <option value="{{$addContact->id}}" <?php if (isset($purchase_orders) && $purchase_orders->contact_id == $addContact->id) { echo 'selected'; } ?>>{{$addContact->contact_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(1)">
                                                                <i class="fa fa-plus-square"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Name <span class="radStar">*</span></label>
                                                    <input type="text" class="form-control editInput textareaInput PurchaseOrdercheckError" name="name" id="purchase_name" value="<?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->name; } ?>" placeholder="Enter Your Full Name">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Address <span class="radStar">*</span></label>
                                                    <textarea class="form-control textareaInput PurchaseOrdercheckError" id="purchase_address" name="address" rows="2" placeholder="Enter Your Address"><?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->address; } ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">City</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_city" name="city" value="<?php if (isset($purchase_orders) && $purchase_orders != '') { echo $purchase_orders->city; } ?>" placeholder="Enter Your City">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">County</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_county" name="county" value="<?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->county; } ?>" placeholder="Enter Your County">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Postcode</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_postcode" name="postcode" value="<?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->postcode;} ?>" placeholder="Enter Your Postcode">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3 field">
                                                    <label class="col-form-label mb-2">Telephone</label>
                                                    <div class="row">
                                                        <div class="col-sm-3 pe-0">
                                                            <select class="form-control editInput selectOptions" id="purchase_telephone_code" name="telephone_code">
                                                                @foreach($country as $Codeval)
                                                                <option value="{{$Codeval->id}}" <?php if (isset($purchase_orders) && $purchase_orders->telephone_code == $Codeval->id) {echo 'selcted'; } else if ($Codeval->id == 230) {echo 'selected'; } ?>>+{{$Codeval->code}} - {{$Codeval->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_telephone" name="telephone" value="<?php if (isset($purchase_orders) && $purchase_orders != '') { echo $purchase_orders->telephone; } ?>" placeholder="Enter Your Telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                            <span style="color:red;display:none" id="CheckpurchaseTelephoneErr">Please enter 10 digit number</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3 field">
                                                    <label class="col-form-label mb-2">Mobile</label>
                                                    <div class="row">
                                                        <div class="col-sm-3 pe-0">
                                                            <select class="form-control editInput selectOptions" id="purchase_mobile_code" name="mobile_code">
                                                                @foreach($country as $Codeval)
                                                                <option value="{{$Codeval->id}}" <?php if (isset($purchase_orders) && $purchase_orders->mobile_code == $Codeval->id) {echo 'selcted'; } else if ($Codeval->id == 230) { echo 'selected';} ?>>+{{$Codeval->code}} - {{$Codeval->name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_mobile" name="mobile" value="<?php if (isset($purchase_orders) && $purchase_orders != '') { echo $purchase_orders->mobile;} ?>" placeholder="Enter Your Mobile No." onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                            <span style="color:red;display:none" id="CheckpurchaseMobileErr">Please enter 10 digit number</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Email</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_email" name="email" value="<?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->email;} ?>" placeholder="Enter Your Email" onchange="purchase_check_email()"><span style="color:red" id="purchaseemailErr"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">  
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Customer / Delivery Details</h4>
                                        <!-- <form class="customerForm"> -->
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Customer</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions" <?php if (!isset($key) && $key == '') { echo 'disabled'; } ?> id="purchase_customer_id" name="customer_id" onchange="get_customer_details()">
                                                                <option selected disabled>Select Customer</option>
                                                                <?php foreach ($customers as $cust) { ?>
                                                                    <option value="{{$cust->id}}" <?php if (isset($purchase_orders) && $purchase_orders->customer_id == $cust->id) {echo 'selected'; } ?>>{{$cust->name}}</option>
                                                                <?php } ?>
                                                            </select>
                                                            <!-- <input type="text"  id="staticEmail"> -->
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(2)"><i
                                                                    class="fa fa-plus-square"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Project</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions" id="purchase_project_id" name="project_id" <?php if (!isset($purchase_orders) && $purchase_orders == '') {echo 'disabled';} ?>>
                                                                <option selected disabled></option>
                                                                @foreach($projects as $project)
                                                                <option value="{{$project->id}}" <?php if (isset($purchase_orders) && $purchase_orders->project_id == $project->id) {echo 'selected';} ?>>{{$project->project_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(3)"><i
                                                                    class="fa fa-plus-square"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Site</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions get_site_result" onchange="siteDetail()" id="purchase_site_id" name="site_id" <?php if (!isset($purchase_orders) && $purchase_orders == '') {echo 'disabled'; } ?>>
                                                                <option selected disabled value="">None</option>
                                                                <option <?php if (isset($purchase_orders) && $purchase_orders->site_id == 0) {
                                                                            echo 'selected';
                                                                        } ?> value="0">Same as customer</option>
                                                                @foreach($site as $siteVal)
                                                                <option value="{{$siteVal->id}}" <?php if (isset($purchase_orders) && $purchase_orders->site_id == $siteVal->id) { echo 'selected'; } ?>>{{$siteVal->site_name}}</option>
                                                                @endforeach
                                                            </select>
                                                            <!-- <input type="text"  id="staticEmail"> -->
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(4)"><i class="fa fa-plus-square"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Name <span class="radStar">*</span></label>
                                                    <input type="text" name="user_name" id="purchase_user_name" class="form-control editInput textareaInput PurchaseOrdercheckError" value="<?php if (isset($purchase_orders) && $purchase_orders->user_name != '') {echo $purchase_orders->user_name;} else {echo Auth::user()->name; } ?>" placeholder="Enter Your Name">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Company</label>
                                                    <input type="text" name="company_name" id="purchase_company_name" class="form-control editInput textareaInput" value="<?php if (isset($purchase_orders) && $purchase_orders->company_name != '') {echo $purchase_orders->company_name;} else {echo $company_name;} ?>" placeholder="Enter Comapny Name">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Address <span class="radStar">*</span></label>
                                                    <textarea class="form-control textareaInput PurchaseOrdercheckError" id="purchase_user_address" name="user_address" rows="2" placeholder="Enter Your Address"><?php if (isset($purchase_orders) && $purchase_orders->user_address != '') { echo $purchase_orders->user_address;} else {echo Auth::user()->current_location; } ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">City</label>
                                                    <input type="text" class="form-control  editInput textareaInput" id="purchase_user_city" name="user_city" value="<?php if (isset($purchase_orders) && $purchase_orders->user_city != '') { echo $purchase_orders->user_city;} ?>" placeholder="Enter Your City">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">County</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_user_county" name="user_county" value="<?php if (isset($purchase_orders) && $purchase_orders->user_county != '') { echo $purchase_orders->user_county; } ?>" placeholder="Enter Your County">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label">Postcode</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_user_post_code" name="user_post_code" value="<?php if (isset($purchase_orders) && $purchase_orders->user_post_code != '') {echo $purchase_orders->user_post_code;} ?>" placeholder="Enter Your Postcode">
                                                </div>
                                                <div class="mb-3 field">
                                                    <label class="col-form-label mb-2">Telephone</label>
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <select class="form-control editInput selectOptions" id="purchase_user_telephone_code" name="user_telephone_code">
                                                                @foreach($country as $Codeval)
                                                                <option value="{{$Codeval->id}}" <?php if (isset($purchase_orders) && $purchase_orders->user_telephone_code == $Codeval->id) {echo 'selected'; } ?>>+{{$Codeval->code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_user_telephone" name="user_telephone" value="<?php if (isset($purchase_orders) && $purchase_orders->user_telephone != '') { echo $purchase_orders->user_telephone; } else {echo Auth::user()->phone_no; } ?>" placeholder="Enter Your Telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                            <span style="color:red;display:none" id="CheckpurchaseUserTelephoneErr">Please enter 10 digit number</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3 field">
                                                    <label class="col-form-label mb-2">Mobile</label>
                                                    <div class="row">
                                                        <div class="col-sm-3">
                                                            <select class="form-control editInput selectOptions" id="purchase_user_mobile_code" name="user_mobile_code">
                                                                @foreach($country as $Codeval)
                                                                <option value="{{$Codeval->id}}" <?php if (isset($purchase_orders) && $purchase_orders->user_telephone_code == $Codeval->id) { echo 'selected'; } ?>>+{{$Codeval->code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_user_mobile" name="user_mobile" value="<?php if (isset($purchase_orders) && $purchase_orders->user_mobile != '') { echo $purchase_orders->user_mobile; } else { echo Auth::user()->mobile; } ?>" placeholder="Enter Your Mobile No." onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                            <span style="color:red;display:none" id="CheckpurchaseUserMobileErr">Please enter 10 digit number</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Expected Delivery On</label>
                                                    <?php
                                                    $expectedDeliveryDate = isset($purchase_orders) && !empty($purchase_orders->expected_deleveryDate)
                                                        ? $purchase_orders->expected_deleveryDate
                                                        : date('Y-m-d', strtotime('+1 day'));
                                                    ?>
                                                    <input type="date" class="form-control editInput textareaInput" id="purchase_expected_deleveryDate" name="expected_deleveryDate" value="<?php echo $expectedDeliveryDate; ?>">
                                                    <!-- <div class="col-sm-2 calendar_icon">
                                                        <i class="fa fa-calendar-alt"></i>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Purchase Order Details</h4>
                                        <!-- <form class="customerForm"> -->
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Purchase Order Ref.</label>
                                                    <input type="text" class="form-control editInput" id="inputJobRef" value="<?php if (isset($purchase_orders) && $purchase_orders->purchase_order_ref != '') { echo $purchase_orders->purchase_order_ref; } else { echo 'Auto generate'; } ?>" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Department</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions" id="purchase_department_id" name="department_id">
                                                                <option selected disabled>Please Select</option>
                                                                <?php foreach ($department as $dept) { ?>
                                                                    <option value="{{$dept->id}}" <?php if (isset($purchase_orders) && $purchase_orders->department_id == $dept->id) { echo 'selected'; } ?>>{{$dept->title}}</option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(5)"><i
                                                                    class="fa fa-plus-square"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Purchase Date <span class="radStar">*</span></label>
                                                    <input type="date" class="form-control editInput PurchaseOrdercheckError" id="purchase_purchase_date" name="purchase_date" value="">
                                                    <!-- <div class="col-sm-2 calendar_icon">
                                                        <i class="fa fa-calendar-alt"></i>
                                                    </div> -->
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Reference</label>
                                                    <input type="text" class="form-control editInput textareaInput" id="purchase_reference" name="reference" placeholder="Reference(if any)" value="<?php if (isset($purchase_orders) && $purchase_orders->reference != '') { echo $purchase_orders->reference; } ?>">
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Quote Ref</label>
                                                    <div class="row">
                                                        <div class="col-sm-10 position-relative">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_qoute_ref" name="purchase_qoute_ref" placeholder="Quote, if any" value="<?php if (isset($purchase_orders) && $purchase_orders->qoute_ref != '') { echo $purchase_orders->qoute_ref; } ?>">
                                                            <input type="hidden" id="selectedPurchaseQuotRefId" name="qoute_ref">
                                                            <div class="search-container purchase_qoute_ref-container"></div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="formicon" onclick="show_searchModal(1)">
                                                                <i class="fa fa-search"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Job Ref</label>
                                                    <div class="row">
                                                        <div class="col-sm-10 position-relative">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_job_ref" name="purchase_job_ref" placeholder="Job Ref, if any" value="<?php if (isset($purchase_orders) && $purchase_orders->job_ref != '') { echo $purchase_orders->job_ref; } ?>">
                                                            <input type="hidden" id="selectedPurchaseJobRefId" name="job_ref">
                                                            <div class="search-container purchase_job_ref-container"></div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="formicon" onclick="show_searchModal(2)">
                                                                <i class="fa fa-search"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Invoice Ref</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control editInput textareaInput" id="purchase_invoice_ref" name="invoice_ref" placeholder="Invoice Ref, if any" value="<?php if (isset($purchase_orders) && $purchase_orders->invoice_ref != '') { echo $purchase_orders->invoice_ref; } ?>">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <div class="formicon" onclick="show_searchModal(3)">
                                                                <i class="fa fa-search"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Payment Terms</label>
                                                    <div class="row">
                                                        <div class="col-sm-8">
                                                            <select class="form-control editInput selectOptions" id="purchase_payment_terms" name="payment_terms" onchange="updateDueDate()">
                                                                </option>
                                                                <?php for ($i = 0; $i <= 90; $i++) { ?>
                                                                    <option value="{{$i}}" <?php if (isset($purchase_orders) && $purchase_orders->payment_terms == $i) { echo 'selected'; } ?>>{{$i}}</option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label class="form-check-label checkboxtext" for="checkalrt">
                                                                days</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Payment Due Date</label>
                                                    <input type="date" class="form-control editInput" id="purchase_payment_due_date" name="payment_due_date" value="">
                                                    <!-- <div class="col-sm-2 calendar_icon">
                                                        <i class="fa fa-calendar-alt"></i>
                                                    </div> -->
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Status</label>
                                                    <select class="form-control editInput selectOptions" id="purchase_status" name="status">
                                                        <option value="1" <?php if (isset($purchase_orders) && $purchase_orders->status == 1) {echo 'selected'; } else { echo 'selected'; } ?>>Draft</option>
                                                        <option value="2" <?php if (isset($purchase_orders) && $purchase_orders->status == 2) { echo 'selected'; } ?>>Awaiting Approval</option>
                                                        <option value="3" <?php if (isset($purchase_orders) && $purchase_orders->status == 3) { echo 'selected'; } ?>>Approved</option>
                                                        <option value="4" <?php if (isset($purchase_orders) && $purchase_orders->status == 4) { echo 'selected'; } ?>>Actioned</option>
                                                        <option value="5" disabled>Paid</option>
                                                        <option value="6" <?php if (isset($purchase_orders) && $purchase_orders->status == 6) {  echo 'selected'; } ?>>Cancelled</option>
                                                        <option value="7" <?php if (isset($purchase_orders) && $purchase_orders->status == 7) { echo 'selected'; } ?>>Invoice Received</option>
                                                        <option value="8" disabled>Rejected</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="col-form-label mb-2">Tags</label>
                                                    <div class="row">
                                                        <div class="col-sm-10">
                                                            <select class="form-control editInput selectOptions" id="purchase_tag_id" name="tag_id">
                                                                <option selected disabled>None</option>
                                                                @foreach($tag as $tagval)
                                                                <option value="{{$tagval->id}}" <?php if (isset($purchase_orders) && $purchase_orders->tag_id == $tagval->id) { echo 'selected'; } ?>>{{$tagval->title}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <a href="javascript:void(0)" class="formicon">
                                                            <i class="fa fa-plus-square" onclick="get_modal(6)"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3 mt-4">
                                                    <label class="col-form-label mb-2">
                                                        <a href="javascript:void(0)" onclick="openReminderModal(<?php if (isset($purchase_orders) && $purchase_orders != '') {echo $purchase_orders->id; } ?>)" class="btn btn-primary"> <i class="fa fa-clock-o"></i> Set Reminder </a>
                                                    </label>
                                                </div>
                                        <!-- </form> -->
                                            </div>
                                            <div class="col-md-12 col-lg-12 col-sm-12 mt-4">
                                                <div class="setRiminderTable" style="display:none">
                                                    <div class="table-responsive productDetailTable ">
                                                        <table class="table border-top border-bottom" id="">
                                                            <thead>
                                                                <tr>
                                                                    <th>Title </th>
                                                                    <th>Date </th>
                                                                    <th>Time</th>
                                                                    <th>Status </th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="reminder_data">
                                                                @foreach($reminder_data as $reminderVal)
                                                                <tr>
                                                                    <td>{{$reminderVal->title}}</td>
                                                                    <td>{{$reminderVal->reminder_date}}</td>
                                                                    <td>{{$reminderVal->reminder_time}}</td>
                                                                    @if($reminderVal->status == 1)
                                                                    <td><span class="iconColrGreen">Sent</span></td>
                                                                    <td>

                                                                        <a href="javascript:void(0)" data-id="{{$reminderVal->id}}" data-title="{{$reminderVal->title}}" data-user_id="{{$reminderVal->user_id}}" data-reminder_date="{{$reminderVal->reminder_date}}" data-reminder_time="{{$reminderVal->reminder_time}}" data-notification="{{$reminderVal->notification}}" data-sms="{{$reminderVal->sms}}" data-email="{{$reminderVal->email}}" data-notes="{{$reminderVal->notes}}" data-icon="eye" class="fecth_data"><i class="material-symbols-outlined">
                                                                                visibility
                                                                            </i></a>
                                                                        <a href="#!" class="iconColrGreen"><i class="material-symbols-outlined">
                                                                                check_circle
                                                                            </i></a>
                                                                    </td>
                                                                    @else
                                                                    <td><span class="iconColrRad">Pending</span></td>
                                                                    <td>
                                                                        <a href="javascript:void(0)" class="iconColrGreen fecth_data" data-id="{{$reminderVal->id}}" data-title="{{$reminderVal->title}}" data-user_id="{{$reminderVal->user_id}}" data-reminder_date="{{$reminderVal->reminder_date}}" data-reminder_time="{{$reminderVal->reminder_time}}" data-notification="{{$reminderVal->notification}}" data-sms="{{$reminderVal->sms}}" data-email="{{$reminderVal->email}}" data-notes="{{$reminderVal->notes}}" data-icon="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a> | 
                                                                        <a href="javascript:void(0)" class="iconColrRad"><i class="fa fa-times" aria-hidden="true"></i></a>
                                                                    </td>
                                                                    @endif
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Product Details</h4>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="mb-3 row d-flex align-items-center">
                                                    <label for="inputCountry" class="col-sm-2 col-form-label">Select product</label>
                                                    <div class="col-sm-3 position-relative">
                                                        <input type="text" class="form-control editInput textareaInput" id="search-product" placeholder="Type to add product">
                                                        <div class="parent-container"></div>
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <div class="plusandText">
                                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(7)"><i class="fa fa-plus-square"></i>
                                                            </a>
                                                            <span class="afterPlusText"> (Type to view product or <a href="Javascript:void(0)" onclick="openProductmodal();"class="taxt_blue">Click here</a> to view all assets)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- <div class="col-sm-3">
                                                <div class="pageTitleBtn p-0">
                                                    <a href="#" class="btn btn-default2">Add Title</a>
                                                    <a href="#" class="btn btn-default2">Show Variations</a>
                                                    <a href="#" class="btn btn-default2 bg-secondary">Export</a>
                                                </div>
                                            </div> -->
                                            <div class="col-sm-12">
                                                <div class="productDetailTable table-responsive input_style">
                                                    <table class="table border-top border-bottom" id="result">
                                                        <thead>
                                                            <tr>
                                                                <th>Job </th>
                                                                <th>Product </th>
                                                                <th>Code</th>
                                                                <th class="col-2">Description </th>
                                                                <th>
                                                                    <div class="tableplusBTN">
                                                                        <span>Account Code </span>
                                                                        <span class="plusandText ps-3">
                                                                            <a href="javascript:void(0)" class="formicon p-0" onclick="openAccountCodeModal(null)"> <i class="fa fa-plus-square"></i> </a>
                                                                        </span>
                                                                    </div>
                                                                </th>
                                                                <th>QTY</th>
                                                                <th>Price</th>
                                                                <th>
                                                                    <div class="tableplusBTN">
                                                                        <span>Price VAT(%) </span>
                                                                        <span class="plusandText ps-3">
                                                                            <a href="javascript:void(0)" class="formicon p-0" onclick="get_modal(9)"> <i class="fa fa-plus-square"></i> </a>
                                                                        </span>
                                                                    </div>
                                                                </th>
                                                                <th>VAT </th>
                                                                <th>Amount</th>
                                                                <th>Delivered QTY</th>
                                                                <th>Quantity Available</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="product_result">

                                                        </tbody>
                                                        <tfoot class="insrt_product_and_detail product_Det" id="product_calculation" style="display:none">
                                                            <tr>
                                                                <td class="border_tran" colspan="5"></td>
                                                                <td colspan="3">Sub Total (exc. VAT)</td>
                                                                <td id="exact_vat"></td>
                                                                <td class="border_tran" colspan="3"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="border_tran" colspan="5"></td>
                                                                <td colspan="3">VAT</td>
                                                                <td id="vat"></td>
                                                                <td class="border_tran" colspan="3"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="border_tran" colspan="5"></td>
                                                                <td colspan="3"><strong>Total(inc.VAT)</strong></td>
                                                                <td><strong id="total_vat"></strong></td>
                                                                <td class="border_tran" colspan="3"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="border_tran" colspan="5"></td>
                                                                <td colspan="3"><strong>Paid</strong></td>
                                                                <td><strong id="paid_amount">-£0.00</strong></td>
                                                                <td class="border_tran" colspan="3"></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="border_tran" colspan="5"></td>
                                                                <td colspan="3"><strong>Outstanding (inc.VAT)</strong></td>
                                                                <td><strong id="outstanding_vat"></strong></td>
                                                                <td class="border_tran" colspan="3"></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                    <div id="pagination-controls-Produc-details"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Notes</h4>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="">
                                                    <h4 class="contTitle text-left mb-0">Supplier Notes</h4>
                                                    <div class="mt-3">
                                                        <textarea cols="40" rows="5" id="purchase_supplier_notes" name="supplier_notes"><?php if (isset($purchase_orders) && $purchase_orders->supplier_notes != '') { echo $purchase_orders->supplier_notes; } ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="">
                                                    <h4 class="contTitle text-left mb-0">Delivery Notes</h4>
                                                    <div class="mt-3">
                                                        <textarea cols="40" rows="5" id="purchase_delivery_notes" name="delivery_notes"><?php if (isset($purchase_orders) && $purchase_orders->delivery_notes != '') { echo $purchase_orders->delivery_notes; } ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="">
                                                    <h4 class="contTitle text-left mb-0">Internal Notes</h4>
                                                    <div class="mt-3">
                                                        <textarea cols="40" rows="5" id="purchase_internal_notes" name="internal_notes"><?php if (isset($purchase_orders) && $purchase_orders->internal_notes != '') {  echo $purchase_orders->internal_notes; } ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <label class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Attachments</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="py-4">
                                                    <div class="jobsection">
                                                        <input type="file" id="purchase_attachment" name="attachment" class="btn btn-default2">Upload Attachments
                                                        <span><?php if (isset($purchase_orders) && $purchase_orders->file_original_name != '') { echo $purchase_orders->file_original_name; } ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                <div id="SupplierInvoiceList" style="display:none">
                                    <div class="row separate_section">
                                        <div class="col-lg-12">
                                            <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Supplier Invoices</h4>
                                            <div class="row">
                                                @if((isset($key) && $key !='') && (isset($duplicate) && $duplicate ==''))
                                                <div class="col-sm-12">
                                                    <div class="table-responsive productDetailTable ">
                                                        <table class="table border-top border-bottom" id="supplier_invoice_table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Recorded Date</th>
                                                                    <th>Recorded By</th>
                                                                    <th>Invoice Ref</th>
                                                                    <th>Invoice Date</th>
                                                                    <th>Due Date</th>
                                                                    <th>Description</th>
                                                                    <th>Attachment</th>
                                                                    <th>Paid</th>
                                                                    <th>Net Amount</th>
                                                                    <th>VAT Amount</th>
                                                                    <th>Amount</th>
                                                                    <th>Paid</th>
                                                                    <th>Outstanding</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="supplierInvoices_result"></tbody>
                                                        </table>
                                                        <div id="pagination-controls-Invoices"></div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="PaymentsPaid" style="display:none">
                                    <div class="row separate_section">
                                        <div class="col-lg-12">
                                            <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Payments Paid</h4>
                                            <div class="row">
                                                @if((isset($key) && $key !='') && (isset($duplicate) && $duplicate ==''))
                                                <div class="col-sm-12">
                                                    <div class="table-responsive productDetailTable ">
                                                        <table class="table border-top border-bottom" id="">
                                                            <thead>
                                                                <tr>
                                                                    <th>Recorded Date</th>
                                                                    <th>Recorded By</th>
                                                                    <th>Payment Date</th>
                                                                    <th>Payment Type</th>
                                                                    <th>Invoice</th>
                                                                    <th>Reference</th>
                                                                    <th>Description</th>
                                                                    <th>Type</th>
                                                                    <th>Amount</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="payment_paid_result"></tbody>
                                                        </table>
                                                        <div id="pagination-controls-Payment_paid"></div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Attachments</h4>
                                        <div class="row">
                                            <div class="col-sm-12 mb-3 mt-2">
                                                <div class="jobsection">
                                                    <a href="javascript:void(0)" class="btn btn-primary" onclick="get_modal(10)">New Attachment</a>
                                                    @if((isset($key) && $key !='') && (isset($duplicate) && $duplicate ==''))
                                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-primary" style="display:none">Delete Attachment(s)</a>
                                                    @endif
                                                </div>
                                            </div>
                                            @if((isset($key) && $key !='') && (isset($duplicate) && $duplicate ==''))
                                            <div class="col-sm-12">
                                                <div class="productDetailTable input_style">
                                                    <table class="table border-top border-bottom">
                                                        <thead>
                                                            <tr>
                                                                <th style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"></label></th>
                                                                <th>Type</th>
                                                                <th class="col-2">Title</th>
                                                                <th>Description</th>
                                                                <th>Section</th>
                                                                <th>File Name</th>
                                                                <th>Mime Type / Size</th>
                                                                <th>Created On</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="attachments_result"></tbody>
                                                    </table>
                                                    <div id="pagination-controls-Attachments"></div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row separate_section">
                                    <div class="col-lg-12">
                                        <h4 class="m-t-0 m-b-20 clr-blue fnt-20 text-center">Tasks</h4>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="d-flex justify-content-between">
                                                    <div class="jobsection">
                                                        <a href="javascript:void(0)" class="btn btn-default2 bgColour" id="task_active_inactive" @if(isset($key) || $key !='' ) style="background-color:#57c8f1" @endif onclick="bgColorChange(1)">Tasks</a>
                                                        <a href="javascript:void(0)" class="btn btn-default2 bgColour" id="recurring_active_inactive" onclick="bgColorChange(2)">Recurring Tasks</a>
                                                    </div>
                                                    <div class="jobsection">
                                                        <a href="javascript:void(0)" class="btn btn-default2 @if(!isset($key) || $key == '' || isset($duplicate) && $duplicate) disabled-tab @endif" @if(!isset($key) || $key=='' || isset($duplicate) && $duplicate) disabled @else onclick="get_modal(11)" @endif>New Task</a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-12" id="taskHideShow">
                                                <div class="productDetailTable">
                                                    <table class="table border-top border-bottom">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Ref</th>
                                                                <th>User</th>
                                                                <th>Type</th>
                                                                <th>Title</th>
                                                                <th>Notes</th>
                                                                <th>Created On</th>
                                                                <th>Executed</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="newtask_result"></tbody>
                                                    </table>
                                                    <div id="pagination-controls-New-task"></div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12" id="recurringHideShow" style="display:none">
                                                <div class="productDetailTable">
                                                    <table class="table border-top border-bottom">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Ref</th>
                                                                <th>User</th>
                                                                <th>Type</th>
                                                                <th>Title</th>
                                                                <th>Notes</th>
                                                                <th>Created On</th>
                                                                <th>Executed</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id=""></tbody>
                                                    </table>
                                                    <div id="pagination-controls-recurring"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="pageTitleBtn">
                                <div class="mt-1 mb-0 text-center" id="message_save"></div>
                                    <a href="javascript:void(0)" class="btn btn-warning" onclick="save_all_data()"><i class="fa fa-floppy-o"></i> Save</a>
                                    <a href="{{url('draft_purchase_order')}}" class="btn btn-default2 ms-3"><i class="fa fa-arrow-left"></i> Back</a>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="col-md-4 col-lg-4 col-xl-4 ">
                                <div class="pageTitle">
                                    <h3 class="header_text">New Jobs</h3>
                                </div>
                            </div>
                            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" class="btn btn-default2" onclick="save_all_data()"><i class="fa fa-floppy-o"></i> Save</a>
                                    <a href="{{url('draft_purchase_order')}}" class="btn btn-default2"><i class="fa fa-arrow-left"></i> Back</a>
                                    <div class="jobsection p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle btn btn-default2" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </a>
                                            <div class="dropdown-menu fade-up m-0 d-none">
                                                <a href="http://localhost/socialcareitsolution/job_edit?key=MQ==" class="dropdown-item col-form-label">Edit</a>
                                                <hr class="dropdown-divider">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Models Start Here -->
<!-- Record Payment Modal start here -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_recordPaymentModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="recordPaymentForm" class="customerForm pt-0">
                                <input type="hidden" name="po_id" id="recordPayment_po_id">
                                <!-- <input type="hidden" name="product_id" id="recordPayment_ppurchaseProduct_id"> -->
                                <input type="hidden" name="supplier_id" id="recordPayment_ppurchaseSupplier_id">
                                <input type="hidden" name="record_type" value="1">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Purchase Order</label>
                                            <div class="col-sm-9">
                                                <p id="purchaseOrderRecordDate"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Supplier</label>
                                            <div class="col-sm-9">
                                                <p id="record_supplierName"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Purchase Order Total</label>
                                            <div class="col-sm-9">
                                                <p id="record_TotalAmount"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Outstanding Amount</label>
                                            <div class="col-sm-9">
                                                <p id="record_OutstandingAmount"></p>
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Amount Paid <span class="radStar">*</span></label>
                                            <div class="col-sm-1">
                                                <div class="tag_box">
                                                    <span>£</span>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control editInput textareaInput" id="record_AmountPaid" name="record_amount_paid">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Payment Date <span class="radStar ">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="date" id="record_PaymentDate" name="record_payment_date" class="form-control editInput">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputProject" class="col-sm-3 col-form-label">Payment Type <span class="radStar ">*</span></label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions" id="record_PaymentType" name="record_payment_type">
                                                    @foreach($paymentTypeList as $type)
                                                    <option value="{{$type->id}}">{{$type->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <a href="javascript:void(0)" class="formicon" onclick="openPaymentTypeModal()"><i class="fa fa-plus-square"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Reference</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput textareaInput" placeholder="Reference (if any)" id="record_Reference" name="record_reference">
                                            </div>
                                        </div>
                                        <div class="mb-2 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Description</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control textareaInput CustomercheckError" id="record_Description" name="record_description" rows="10" maxlength="500"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default2" id="saverecordPaymentModal" onclick="saverecordPaymentModal()">Save</button>
                <button type="button" class="btn btn-default2" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- end here -->

@include('components.add-supplier-modal')
@include('components.add-customer-modal')
@include('components.contact-modal')
@include('components.add-site-modal')
@include('components.job-title-model')
@include('components.customer-type-modal')
@include('components.region-model')
@include('components.add-project-modal')
@include('components.department-model')
@include('components.product-list')
@include('components.account-code')
@include('frontEnd.salesAndFinance.item.common.addproductmodal')

<x-tag-modal
    modalId="TagModal"
    modalTitle="Add Tag"
    formId="add_tag_form"
    inputId="tag_title"
    statusId="tag_status"
    saveButtonId="saveTag"
    placeholderText="Tag" />

<x-add-attachment-modal
    purchaseModalId="purchase_model"
    purchaseformId="purchase_Attachmentform"
    refTitle="Purchase"
    modalTitle="Add Attachment"
    typeId="purchase_typeId"
    inputTitle="purchase_title"
    selectfileName="purchase_file"
    inputDescription="purchase_description"
    saveButtonId="savePurchaseAttachment"
    saveButtonUrl="{{url('/purchase_order_attachment_save')}}"
    hiddenForeignId="po_id" />

<x-new-task-modal
    modalId="NewTaskModal"
    modalTitle="New Task"
    formId="newTaskform"
    taskCustomerId="task_supplier_id"
    taskId="task_id"
    foriegnId="task_po_id"
    userId="task_user_id"
    taskTitle="taskTitle"
    taskTypeId="taskTypeId"
    taskStartDate="taskStartDate"
    taskStartTime="taskStartTime"
    taskEndDate="taskEndDate"
    taskEndTime="taskEndTime"
    notifyDate="notify_date"
    notifyTime="notify_time"
    taskNotesText="taskNotesText"
    modalLabelTitle="modal_label_title"
    saveNewTaskUrl="{{url('/purchase_order_new_task_save')}}"
    completeNewTaskUrl="{{url('/purchase_order_new_task_complete')}}"
    saveButtonId="saveNewTask" />

<x-vat-tax-rate
    modalId="VatTaxRateModal"
    modalTitle="Add Tax Rate"
    formId="vattaxrateform"
    id="vattaxrate_id"
    name="vat_tax_name"
    taxRate="vat_tax_rate"
    taxCode="vat_tax_code"
    expDate="vat_tax_expdate"
    status="vat_tax_satatus"
    saveButtonId="saveVatTaxRate" />

<x-add-reminder
    reminderModalId="ReminderModal"
    modalTitle="PO Reminder"
    reminderformId="reminderform"
    reminderId="reminder_id"
    reminderDate="reminder_date"
    reminderTime="reminder_time"
    reminderUser="reminder_user"
    hiddenForeignId="reminder_po_id"
    reminderNotification="reminder_notification"
    reminderEmail="reminder_email"
    reminderSms="reminder_sms"
    reminderTitle="reminder_title"
    reminderNotes="reminder_notes"
    reminderSaveUrl="{{url('/save_reminder')}}"
    saveButtonId="saveReminder" />
<x-purchase-search-modal
    searchModalId="refrence_purchase_seacrh_modal"
    modalTitle="refrence_purchase_seacrh_modalTitle"
    searchformId="refrence_purchase_seacrh_modalId" />

<!-- End here -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

<script>
    //Text Editer

    var editor_config = {
        toolbar: [{
                name: 'basicstyles',
                items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
            },
            {
                name: 'format',
                items: ['Format']
            },
            {
                name: 'paragraph',
                items: ['Indent', 'Outdent', '-', 'BulletedList', 'NumberedList']
            },
            {
                name: 'link',
                items: ['Link', 'Unlink']
            },
            {
                name: 'undo',
                items: ['Undo', 'Redo']
            }
        ],
    };

    CKEDITOR.replace('purchase_supplier_notes', editor_config);
    CKEDITOR.replace('purchase_delivery_notes', editor_config);
    CKEDITOR.replace('purchase_internal_notes', editor_config);
    //Text Editer
</script>
<script>
    function getAllSupplier(data) {
        $("#purchase_supplier_id").append('<option value="' + data.id + '">' + data.name + '</option>');
    }

    function GetAllContact(contact_data) {
        $("#purchase_contact_id").append('<option value="' + contact_data.data.id + '">' + contact_data.data.contact_name + '</option>');
    }

    function getAllCusomer(customer_data) {
        $("#purchase_customer_id").append('<option value="' + customer_data.id + '">' + customer_data.name + '</option>');
    }

    function getAllCustomerType(customer_type_data) {
        $("#customer_type_id").append(customer_type_data);
    }

    function getAllproject(project_data) {
        $("#purchase_project_id").append(project_data);
    }

    function getAllsite(site_data) {
        $("#purchase_site_id").append(site_data);
    }

    function getAlldepartment(department_data) {
        $("#purchase_department_id").append('<option value="' + department_data.data.id + '">' + department_data.data.title + '</option>');
    }

    function get_supplier_details() {
        var supplier_id = $("#purchase_supplier_id").val();
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('get_supplier_details')}}",
            data: {
                supplier_id: supplier_id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                const data = response.data;
                $('#purchase_contact_id').removeAttr('disabled');
                var contactSelect = document.getElementById("purchase_contact_id");
                $("#purchase_name").val(data.contact_name);
                $("#purchase_address").val(data.address);
                $("#purchase_city").val(data.city);
                $("#purchase_county").val(data.county);
                $("#purchase_postcode").val(data.postcode);
                $("#purchase_telephone").val(data.telephone);
                $("#purchase_mobile").val(data.mobile);
                $("#purchase_email").val(data.email);

                $.ajax({
                    url: '{{ route("ajax.getCountriesList") }}',
                    method: 'GET',
                    success: function(response1) {
                        // console.log(response1.Data);
                        const selectElement = $("#purchase_telephone_code")[0];
                        const selectElement1 = $("#purchase_mobile_code")[0];
                        selectElement.innerHTML = '';
                        selectElement1.innerHTML = '';
                        const defaultOptionTelephone = document.createElement("option");
                        const defaultOptionMobile = document.createElement("option");
                        // defaultOptionTelephone.value = "0";
                        defaultOptionTelephone.text = "Please Select";
                        defaultOptionTelephone.disabled = true;
                        defaultOptionTelephone.selected = true;
                        selectElement.appendChild(defaultOptionTelephone);

                        // defaultOptionMobile.value = "0";
                        defaultOptionMobile.text = "Please Select";
                        defaultOptionMobile.disabled = true;
                        defaultOptionMobile.selected = true;
                        selectElement1.appendChild(defaultOptionMobile);

                        response1.Data.forEach(user => {
                            const option1 = document.createElement('option');
                            option1.value = user.id;
                            option1.text = user.name + " " + "(+" + user.code + ")";
                            selectElement.appendChild(option1);
                        });
                        response1.Data.forEach(user1 => {
                            const option1 = document.createElement('option');
                            option1.value = user1.id;
                            option1.text = user1.name + " " + "(+" + user1.code + ")";
                            selectElement1.appendChild(option1);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
                $("#contact_customer_id").val(response.data.id);
                $("#contact_customer_name").text(response.data.name);
                $("#task_supplier_id").val(response.data.id);
                $(".customer_name").text(response.data.name);
                const all_contact = response.data.contacts;
                contactSelect.innerHTML = '';
                const defaultOption = document.createElement("option");
                defaultOption.value = "0";
                defaultOption.text = "Default";
                defaultOption.disabled = false;
                defaultOption.selected = false;
                contactSelect.appendChild(defaultOption);
                if (all_contact && all_contact.length > 0) {
                    all_contact.forEach((cont) => {
                        const option = document.createElement("option");
                        option.value = cont.id;
                        option.text = `${cont.contact_name}`;
                        contactSelect.appendChild(option);
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }
    var purchase_user_name = '';
    var purchase_company_name = '';
    var purchase_user_address = '';
    var purchase_user_city = '';
    var purchase_user_county = '';
    var purchase_user_post_code = '';
    var purchase_user_telephone_code;
    var user_telephone = '';
    var purchase_user_mobile_code;
    var purchase_user_mobile = '';

    function get_customer_details() {
        var customer_id = $("#purchase_customer_id").val();
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('get_customer_details_front')}}",
            data: {
                customer_id: customer_id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                // return false;
                $('#purchase_project_id').removeAttr('disabled');
                $('#purchase_site_id').removeAttr('disabled');
                if (data.customers && data.customers.length > 0) {
                    var customerData = data.customers[0];
                    var project = '<option value="0" selected disabled>None</option>';
                    if (customerData.customer_project && Array.isArray(customerData.customer_project)) {
                        for (let i = 0; i < customerData.customer_project.length; i++) {
                            project += '<option value="' + customerData.customer_project[i].id + '">' + customerData.customer_project[i].project_name + '</option>';
                        }
                    }
                    document.getElementById('purchase_project_id').innerHTML = project;

                    var site = '<option value="0">Same as customer</option>';
                    if (customerData.sites && Array.isArray(customerData.sites)) {
                        for (let i = 0; i < customerData.sites.length; i++) {
                            site += '<option value="' + customerData.sites[i].id + '">' + customerData.sites[i].site_name + '</option>';
                        }
                    }
                    document.getElementById('purchase_site_id').innerHTML = site;
                    $("#project_customer_name").text(customerData.name);
                    $("#site_customer_name").text(customerData.name);
                    purchase_user_name = customerData.name;
                    purchase_company_name = customerData.contact_name;
                    purchase_user_address = customerData.address;
                    purchase_user_city = customerData.city;
                    purchase_user_county = customerData.country;
                    purchase_user_post_code = customerData.postal_code;
                    purchase_user_telephone_code = customerData.telephone_country_code ?? 230;
                    user_telephone = customerData.telephone;
                    purchase_user_mobile_code = customerData.mobile_country_code ?? 230;
                    purchase_user_mobile = customerData.mobile;
                    $("#purchase_user_name").val(purchase_user_name);
                    $("#purchase_company_name").val(purchase_company_name);
                    $("#purchase_user_address").val(purchase_user_address);
                    $("#purchase_user_city").val(purchase_user_city);
                    $("#purchase_user_county").val(purchase_user_county);
                    $("#purchase_user_post_code").val(purchase_user_post_code);
                    $("#purchase_user_telephone_code").val(purchase_user_telephone_code);
                    $("#user_telephone").val(user_telephone);
                    $("#purchase_user_mobile_code").val(purchase_user_mobile_code);
                    $("#purchase_user_mobile").val(purchase_user_mobile);

                    $('#purchase_project_id').removeAttr('disabled');
                    $('#purchase_site_id').removeAttr('disabled');
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }

    function siteDetail() {
        var id = $("#purchase_site_id").val();
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerSiteDetails") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                // console.log(response.data);
                // return false;
                if (id == 0) {
                    $("#purchase_user_name").val(purchase_user_name);
                    $("#purchase_company_name").val(purchase_company_name);
                    $("#purchase_user_address").val(purchase_user_address);
                    $("#purchase_user_city").val(purchase_user_city);
                    $("#purchase_user_county").val(purchase_user_county);
                    $("#purchase_user_post_code").val(purchase_user_post_code);
                    $("#purchase_user_telephone_code").val(purchase_user_telephone_code);
                    $("#user_telephone").val(user_telephone);
                    $("#purchase_user_mobile_code").val(purchase_user_mobile_code);
                    $("#purchase_user_mobile").val(purchase_user_mobile);
                } else {
                    $("#purchase_user_name").val(response.data[0].contact_name);
                    $("#purchase_company_name").val(response.data[0].company_name);
                    $("#purchase_user_address").val(response.data[0].address);
                    $("#purchase_user_city").val(response.data[0].city);
                    $("#purchase_user_county").val(response.data[0].country);
                    $("#purchase_user_post_code").val(response.data[0].post_code);
                    $("#purchase_user_telephone_code").val(response.data[0].telephone_country_code ?? 230);
                    $("#user_telephone").val(response.data[0].telephone);
                    $("#purchase_user_mobile_code").val(response.data[0].mobile_country_code ?? 230);
                    $("#purchase_user_mobile").val(response.data[0].mobile);
                }

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>
<script>
    function get_modal(modal) {
        // alert(modal)
        var purchase_ref = '<?php if ((isset($purchase_orders) && $purchase_orders != '') && (isset($duplicate) && $duplicate == '')) {
                                echo $purchase_orders->purchase_order_ref;
                            } ?>'
        var po_id = '<?php if ((isset($purchase_orders) && $purchase_orders != '') && (isset($duplicate) && $duplicate == '')) {
                            echo $purchase_orders->id;
                        } ?>'
        var supplier_select_check = $("#purchase_supplier_id").val();
        var modal_array = [1, 7];
        var customer_id = $("#purchase_customer_id").val();
        if (supplier_select_check == null && modal_array.includes(modal)) {
            alert("Please select Supplier");
            return false;
        } else {
            if (modal == 1) {
                get_supplier_details();
                $("#contact_form")[0].reset();
                $('#contactModalLabel').text("Add Supplier Contact");
                $('#contactLabel').text("Supplier");
                $('#userType').val(2);
                $("#contact_billing_radio").hide();
                $("#contact_modal").modal('show');

            } else if (modal == 2) {
                $("#AddCustomerModal")[0].reset();
                $("#job_title_plusIcon").hide();
                $("#customerPop").modal('show');
            } else if (modal == 3) {
                if (customer_id == '' || customer_id == null) {
                    $("#HideShowFieldText").hide();
                    $("#HideShowFieldSelect").show();
                } else {
                    $("#HideShowFieldText").show();
                    $("#HideShowFieldSelect").hide();
                }
                $("#project_form")[0].reset();
                $("#project_modal").modal('show');
            } else if (modal == 4) {
                if (customer_id == '' || customer_id == null) {
                    alert("Please select Customer");
                    return false;
                } else {
                    $("#site_form")[0].reset();
                    $("#site_modal").modal('show');
                }
            } else if (modal == 5) {
                $("#department_form_data")[0].reset();
                $("#departmentPop").modal('show');
            } else if (modal == 6) {
                $("#add_tag_form")[0].reset();
                $("#TagModal").modal('show');
            } else if (modal == 7) {
                itemsAddProductModal(1);
            } else if (modal == 10) {
                if (po_id == '') {
                    if (confirm("Purchase order details should be saved before attaching any files. Do you want to save the purchase order now?")) {
                        save_all_data();
                    }
                } else {
                    $("#purchase_Attachmentform")[0].reset();
                    $("#Purchase_ref").val(purchase_ref);
                    $("#po_id").val(po_id);
                    $("#purchase_model").modal('show');
                }
            } else if (modal == 11) {
                $("#newTaskform")[0].reset();
                get_supplier_details();
                $("#modal_label_title").text('Supplier');
                $("#related_To").text(purchase_ref);
                $("#task_po_id").val(po_id);
                $("#NewTaskModal").modal('show');

            } else if (modal == 9) {
                $("#vattaxrateform")[0].reset();
                $("#VatTaxRateModal").modal('show');
            }
        }

    }

    function open_customer_type_modal() {
        $('#cutomer_type_modal').modal('show');
    }

    function openProductmodal() {
        var supplier_select_check = $("#purchase_supplier_id").val();
        if (supplier_select_check == null) {
            alert("Please select supplier first");
            return false;
        } else {
            openProductListModal();
        }
    }
</script>
<script>
    function purchase_check_email() {
        var email = $('#purchase_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1) {
            $('#purchaseemailErr').text("Please enter correct email address");
            return false;
        } else {
            $('#purchaseemailErr').text("");
        }
    }

    function save_all_data() {
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
        var emailErr = $("#purchaseemailErr").text();
        $('.PurchaseOrdercheckError').each(function() {
            if ($(this).val() === '' || $(this).val() == null) {
                $(this).css('border', '1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border', '');
            }
        });
        var purchase_telephone = $("#purchase_telephone").val();
        var purchase_mobile = $("#purchase_mobile").val();
        var purchase_user_telephone = $("#purchase_user_telephone").val();
        var purchase_user_mobile = $("#purchase_user_mobile").val();
        if (purchase_telephone != '' && purchase_telephone.length != 10) {
            $("#CheckpurchaseTelephoneErr").show();
            return false;
        } else if (purchase_mobile != '' && purchase_mobile.length != 10) {
            $("#CheckpurchaseTelephoneErr").hide();
            $("#CheckpurchaseMobileErr").show();
            return false;
        } else if (purchase_user_telephone != '' && purchase_user_telephone.length != 10) {
            $("#CheckpurchaseTelephoneErr").hide();
            $("#CheckpurchaseMobileErr").hide();
            $("#CheckpurchaseUserTelephoneErr").show();
            return false;
        } else if (purchase_user_mobile != '' && purchase_user_mobile.length != 10) {
            $("#CheckpurchaseTelephoneErr").hide();
            $("#CheckpurchaseMobileErr").hide();
            $("#CheckpurchaseUserTelephoneErr").hide();
            $("#CheckpurchaseUserMobileErr").show();
            return false;
        } else if (emailErr.length > 0) {
            $("#CheckpurchaseTelephoneErr").hide();
            $("#CheckpurchaseMobileErr").hide();
            $("#CheckpurchaseUserTelephoneErr").hide();
            $("#CheckpurchaseUserMobileErr").hide();
            return false;
        } else {
            $("#CheckpurchaseTelephoneErr").hide();
            $("#CheckpurchaseMobileErr").hide();
            $("#CheckpurchaseUserTelephoneErr").hide();
            $("#CheckpurchaseUserMobileErr").hide();
            $.ajax({
                type: "POST",
                url: "{{url('/purchase_order_save')}}",
                data: new FormData($("#all_data")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else if (response.success === true) {
                        $(window).scrollTop(0);
                        $('#message_save').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_save').removeClass('success-message').text('').hide();
                            // var id = parseInt(response.data.id, 10) || 0;
                            // var encodedId = btoa(unescape(encodeURIComponent(id)));
                            // location.href = '<?php echo url('purchase_order_edit'); ?>?key=' + encodedId;
                            location.href = '<?php echo url('draft_purchase_order'); ?>'
                        }, 3000);
                    } else if (response.success === false) {
                        $('#message_save').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
    }

    $("#saveTag").on('click', function() {
        var title = $("#tag_title").val().trim();
        var status = $.trim($('#tag_status option:selected').val());

        if (title.includes(',')) {
            alert("Comma not allowed in the tag, please use _ or - instead");
            return false;
        } else if (title == '') {
            $("#tag_title").css('border', '1px solid red');
            return false;
        } else {
            $("#tag_title").css('border', '');
            $.ajax({
                type: "POST",
                url: "{{url('/save_tag')}}",
                data: new FormData($("#add_tag_form")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    // console.log(response);
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else if (response.data && response.data.original && response.data.original.error) {
                        alert(response.data.original.error);
                        return false;
                    } else if (response.success === true) {
                        // $(window).scrollTop(0);
                        // $('#message_save').text(response.message).show();
                        // setTimeout(function() {
                        //     $('#message_save').text('').hide();
                        // }, 3000);
                        $("#TagModal").modal('hide');
                        $("#purchase_tag_id").append('<option value="' + response.data.id + '">' + response.data.title + '</option>')

                    } else {
                        alert("Something went wrong! Please try later");
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
    });
</script>
<script>
    function getProductData(selectedId) {
        selectProduct(selectedId);
        // $.ajax({
        //     url: '{{ route("item.ajax.getProductFromId") }}',
        //     method: 'Post',
        //     data: {
        //         id: selectedId
        //     },
        //     success: function(response) {
        //         console.log(response);
        //         productGroupTable(response.data, 'result');
        //     },
        //     error: function(xhr, status, error) {
        //         console.error(error);
        //     }
        // });
    }
    var GrandPrice = 0;
    var totalAmount = 0;

    function selectProduct(id) {
        var token = '<?php echo csrf_token(); ?>'
        var key = 'order';
        $.ajax({
            type: "POST",
            url: "{{url('result_product_calculation')}}",
            data: {
                id: id,
                key: key,
                _token: token
            },
            success: function(data) {
                // console.log(data);return false;
                const tableBody = document.querySelector(`#result tbody`);

                if (data.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id = 'EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center';

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                    const row = document.createElement('tr');
                    // job dropdown
                    const dropdownJob = document.createElement('td');

                    const selectDropdownJob = document.createElement('select');
                    selectDropdownJob.name = 'job_id[]';
                    selectDropdownJob.className = 'form_control';

                    const defaultOptionJob = document.createElement('option');
                    defaultOptionJob.value = '';
                    defaultOptionJob.text = '-Not Selected-';
                    selectDropdownJob.appendChild(defaultOptionJob);

                    const optionsJob = data.job;
                    optionsJob.forEach(optionJob => {
                        const optJob = document.createElement('option');
                        optJob.value = optionJob.id;
                        optJob.textContent = optionJob.name;
                        selectDropdownJob.appendChild(optJob);
                    });

                    dropdownJob.appendChild(selectDropdownJob);

                    row.appendChild(dropdownJob);
                    // end
                    const nameCell = document.createElement('td');
                    nameCell.innerHTML = data.product_detail.product_name;
                    row.appendChild(nameCell);

                    const codeCell = document.createElement('td');
                    // codeCell.textContent = data.product_detail.product_code;
                    const inputCode = document.createElement('input');
                    inputCode.className = 'product_code form_control';
                    inputCode.name = 'product_code[]';
                    inputCode.value = '';
                    codeCell.appendChild(inputCode);
                    row.appendChild(codeCell);

                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.className = 'product_id';
                    hiddenInput.name = 'product_id[]';
                    hiddenInput.value = data.product_detail.id;
                    row.appendChild(hiddenInput);

                    const descriptionCell = document.createElement('td');
                    const inputDescription = document.createElement('textarea');
                    inputDescription.className = 'description form_control';
                    inputDescription.setAttribute('rows', '1');
                    inputDescription.name = 'description[]';
                    // inputDescription.value = data.product_detail.description;
                    inputDescription.value = '';
                    inputDescription.addEventListener('input', function() {
                        auto_grow(this);
                    });
                    descriptionCell.appendChild(inputDescription);
                    row.appendChild(descriptionCell);

                    const dropdownAccountCode = document.createElement('td');
                    const selectDropdownAccountCode = document.createElement('select');
                    selectDropdownAccountCode.className = 'accountCode_id form_control';
                    selectDropdownAccountCode.name = 'accountCode_id[]';

                    const optionsAccountCode = data.accountCode;

                    const defaultOptionAccountCode = document.createElement('option');
                    defaultOptionAccountCode.value = '';
                    defaultOptionAccountCode.text = '-No Department-';
                    selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                    optionsAccountCode.forEach(optionJob => {
                        const optAccountCode = document.createElement('option');
                        optAccountCode.value = optionJob.id;
                        optAccountCode.textContent = optionJob.name;
                        selectDropdownAccountCode.appendChild(optAccountCode);
                    });
                    dropdownAccountCode.appendChild(selectDropdownAccountCode);
                    row.appendChild(dropdownAccountCode);

                    const qtyCell = document.createElement('td');
                    const inputQty = document.createElement('input');
                    inputQty.type = 'text';
                    inputQty.className = 'qty input50 form_control';
                    inputQty.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                        updateAmount(row);
                    });
                    inputQty.name = 'qty[]';
                    inputQty.value = '1';
                    qtyCell.appendChild(inputQty);
                    row.appendChild(qtyCell);

                    const priceCell = document.createElement('td');
                    const inputPrice = document.createElement('input');
                    inputPrice.type = 'text';
                    inputPrice.className = 'product_price input50 form_control';
                    // inputPrice.addEventListener('input', function() {
                    //     updateAmount(row);
                    // });
                    inputPrice.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                        updateAmount(row);
                    });
                    inputPrice.name = 'price[]';
                    inputPrice.value = data.product_detail.price;
                    GrandPrice = GrandPrice + Number(data.product_detail.price);
                    priceCell.appendChild(inputPrice);
                    row.appendChild(priceCell);

                    const dropdownVat = document.createElement('td');
                    const selectDropdownVat = document.createElement('select');
                    selectDropdownVat.addEventListener('change', function() {
                        // alert(`You selected: ${this.options[this.selectedIndex].text}`);
                        getIdVat($(this).val(), row);
                    });
                    selectDropdownVat.name = 'vat_id[]';
                    selectDropdownVat.className = 'vat_id form_control';
                    const optionsVat = data.tax;
                    var tax_rate = '00';
                    optionsVat.forEach(optionVat => {
                        const optVat = document.createElement('option');
                        optVat.value = optionVat.id;
                        if (optionVat.id == data.product_detail.tax_rate) {
                            tax_rate = optionVat.tax_rate;
                            optVat.setAttribute("selected", "selected");
                        }
                        optVat.textContent = optionVat.name;
                        selectDropdownVat.appendChild(optVat);
                    });
                    const inputVatRate = document.createElement('input');
                    inputVatRate.type = 'hidden';
                    inputVatRate.className = 'vat_ratePercentage';
                    inputVatRate.name = 'vat_ratePercentage[]';
                    inputVatRate.value = tax_rate;
                    dropdownVat.appendChild(inputVatRate);
                    dropdownVat.appendChild(selectDropdownVat);
                    row.appendChild(dropdownVat);

                    const vatCell = document.createElement('td');
                    const inputVat = document.createElement('input');
                    inputVat.type = 'text';
                    inputVat.className = 'vat form_control';
                    inputVat.style = "max-width:70px;";
                    inputVat.setAttribute('disabled', 'disabled');
                    inputVat.addEventListener('input', function() {
                        updateAmount(row);
                    });
                    inputVat.name = 'vat[]';
                    inputVat.value = parseFloat(tax_rate).toFixed(2);
                    vatCell.appendChild(inputVat);
                    row.appendChild(vatCell);

                    const amountCell = document.createElement('td');
                    amountCell.innerHTML = '£' + parseFloat(data.product_detail.price).toFixed(2);
                    amountCell.className = "price";
                    row.appendChild(amountCell);
                    totalAmount = totalAmount + Number(data.product_detail.price);

                    const delveriQTYCell = document.createElement('td');
                    delveriQTYCell.innerHTML = '-';
                    delveriQTYCell.className = 'text-center';
                    row.appendChild(delveriQTYCell);

                    const deleteCell = document.createElement('td');
                    deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                    deleteCell.addEventListener('click', function() {
                        removeRow(this);
                    });
                    row.appendChild(deleteCell);

                    tableBody.appendChild(row);
                    updateAmount(row)
                    $("#product_calculation").show();
                }

            }
        });
    }

    function removeRow(button, id = null) {
        // console.log(button);
        const table = document.getElementById("result");
        const tbody = table.querySelector("tbody");
        const rowCount = tbody ? tbody.rows.length : 0;
        if (rowCount <= 1) {
            $("#product_calculation").hide();
        }
        var row = button.parentNode;

        if (id) {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('purchase_productsDelete')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    // console.log(data);
                    if (data.success != true) {
                        alert("Something went wrong! Please try later");
                        return false;
                    } else {
                        row.parentNode.removeChild(row);
                        updateAmount(row);
                    }
                }
            });
        } else {
            row.parentNode.removeChild(row);
            updateAmount(row);
        }
    }
    // $(".quantity").on('keyup', function(){
    $(document).on("keyup", ".quantity", function() {
        var qty = $(this).val();
        var row = $(this).closest('tr');
        updateAmount(row);
        // var price = row.find("input#pricejob").val();
        // if(qty != ''){
        //     $('#pro_qty').val(qty);
        //     var totalPrice = qty * price;
        //     row.find("#pre_total_amount").text(totalPrice);
        // }else{
        //     $('#pro_qty').val('0');
        //     row.find("#pre_total_amount").text(price);
        // }
        //     var totalAmountAssign=0;
        //     $('.pre_total_amount').each(function(index){
        //         var amount_assign=$(this).text();
        //         totalAmountAssign=totalAmountAssign+Number(amount_assign);
        //     });
        //     $("#total_amount").text('£' + totalAmountAssign);
    });
    var check_paid_amount = 0;

    function updateAmount(row, paid_amount = 0) {
        // console.log(row)
        // const priceInput = row.querySelector('.price');
        // alert(typeof(paid_amount))

        if (paid_amount != 0) {
            check_paid_amount = paid_amount;
        }
        const priceInput = row.querySelector('.product_price');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('td:nth-last-child(3)');
        const price = parseFloat(priceInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 1;
        const amount = price * qty;
        amountCell.textContent = '£' + amount.toFixed(2);
        const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value;
        const vat = row.querySelector('.vat');
        const percentage = amount * vat_ratePercentage / 100;
        // alert(percentage)
        vat.value = percentage.toFixed(2);

        var calculation = 0;
        $('.price').each(function() {
            const priceText = $(this).text();
            const numericValue = parseFloat(priceText.replace(/[^\d.]/g, ''));
            // console.log(typeof(numericValue));
            calculation = calculation + numericValue;
        });
        var vat_amount = 0;
        $('.vat').each(function() {
            const vat = $(this).val();
            vat_amount = vat_amount + Number(vat);
        });
        totalAmount = calculation;
        // console.log(typeof(vat_amount));
        // document.getElementById('GrandTotalAmount').innerHTML='$'+totalAmount.toFixed(2);
        $("#productPrice").val(totalAmount.toFixed(2));
        $("#exact_vat").text('£' + totalAmount.toFixed(2));
        $("#vat").text('£' + vat_amount.toFixed(2));
        var total_vat = totalAmount + vat_amount;
        $("#total_vat").text('£' + total_vat.toFixed(2));
        var outstanding_amount = total_vat - check_paid_amount;
        $("#outstanding_vat").text('£' + outstanding_amount.toFixed(2));
    }

    function getIdVat(vat_id, row, paid_amount = 0) {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/vat_tax_details')}}",
            data: {
                vat_id: vat_id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                if (response) {
                    const vat_value = Number(response.data);
                    const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value = vat_value;
                    // var td=row.querySelector('td:nth-last-child(4)');
                    // var input = td.querySelector('.vat');
                    // // console.log(typeof(vat_value));
                    // input.value = vat_value.toFixed(2) || 0;
                    updateAmount(row, paid_amount);
                } else {
                    alert("Something went wrong");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
</script>
<script>
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#search-product').on('keyup', function() {
            let query = $(this).val();
            const divList = document.querySelector('.parent-container');

            if (query === '') {
                divList.innerHTML = '';
            }

            // Make an AJAX call only if query length > 2
            if (query.length > 2) {
                $.ajax({
                    url: "{{ route('item.ajax.searchProduct') }}", // Laravel route
                    method: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        console.log(response);
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                        // $('#results').html(response);
                        divList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'container'; // Optional: Add a class to the div for styling

                        // Step 2: Create a ul (unordered list)
                        const ul = document.createElement('ul');
                        ul.id = "productList";
                        // Step 3: Loop through the data and create li (list item) for each entry
                        response.forEach(item => {
                            const li = document.createElement('li'); // Create a new li element
                            li.textContent = item.product_name; // Set the text of the li item
                            li.id = item.id;
                            li.className = "editInput";
                            ul.appendChild(li); // Append the li to the ul
                            // const hr = document.createElement('hr');
                            // hr.className='dropdown-divider';
                            // ul.appendChild(hr);
                        });

                        // Step 4: Append the ul to the div
                        div.appendChild(ul);

                        // Step 5: Append the div to the parent container in the HTML
                        divList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            divList.innerHTML = '';
                            document.getElementById('search-product').value = '';
                            // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedId = event.target.id; // Get the ID of the clicked <li>
                                console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                                getProductData(selectedId);
                            }
                        });

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#results').empty(); // Clear results if the input is empty
            }
        });

    });
</script>
<script>
    $(document).ready(function() {
        var purchaseOrderId = '<?php if ((isset($purchase_orders) && $purchase_orders != '') && (isset($duplicate) && $duplicate == '')) {
                                    echo $purchase_orders->id;
                                } ?>'

        if (purchaseOrderId) {
            getAttachment(purchaseOrderId, '{{ url("getAllAttachmens") }}');
            getProductDetail(purchaseOrderId, '{{ url("getPurchaesOrderProductDetail") }}')
            getAllNewTaskList(purchaseOrderId, '{{ url("getAllNewTaskList") }}');
            getAllPurchaseInvoices(purchaseOrderId, '{{url("getAllPurchaseInvoices")}}');
            getAllPaymentPaids(purchaseOrderId, '{{url("getAllPaymentPaids")}}');
            defualt_date(0);
        } else {
            var purchaseOrderId = '<?php if (isset($purchase_orders) && $purchase_orders != '') {
                                        echo $purchase_orders->id;
                                    } ?>'
            getProductDetail(purchaseOrderId, '{{ url("getPurchaesOrderProductDetail") }}');
            defualt_date(1);
        }
        var reminderCount = '<?php echo count($reminder_data); ?>'
        if (reminderCount > 0) {
            $(".setRiminderTable").show();
        }
    });

    function getAllAttachment(data) {
        getAttachment(data.po_id, pageUrl = '{{ url("getAllAttachmens") }}')
    }

    function getAttachment(id, pageUrl = '{{ url("getAllAttachmens") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                var paginationAttachment = response.pagination;
                var data = response.data.data;
                // const attachments = response.data.data[0].po_attachments || [];
                const attachments = data;
                // console.log(attachments);
                const tbody = $('#attachments_result');
                tbody.empty();
                attachments.forEach(attachment => {
                    $("#deleteSelectedRows").show();
                    const attachmentType = attachment.attachment_type?.title || '';
                    const title = attachment.title || '';
                    const description = attachment.description || '';
                    const section = attachment.Purchase_ref || '';
                    const fileName = attachment.original_file_name || '';
                    const mime_type = attachment.mime_type || '';
                    const size = attachment.size || '';
                    const created_at = attachment.created_at || '';
                    var date = moment(created_at).format('DD/MM/YYYY HH:mm');
                    var imag_url = "<?php echo url('public/images/purchase_order/'); ?>" + '/' + attachment.file;
                    tbody.append(`
                        <tr>
                            <td><input type="checkbox" id="" class="delete_checkbox" value="` + attachment.id + `"></td>
                            <td>${attachmentType}</td>
                            <td><input type="hidden" name="purchaseattachment_id[]" value="${attachment.id}"><input type="text" class="form-control" name="purchaseattachment_title[]" value="${title}"></td>
                            <td>${description}</td>
                            <td>${section}</td>
                            <td>${fileName}</td>
                            <td>${mime_type} / ${size}</td>
                            <td>${date}</td>
                            <td><a href="` + imag_url + `" target="_blank"><i class="fa fa-eye"></i></a> &emsp; <img src="<?php echo url('public/frontEnd/jobs/images/delete.png'); ?>" alt="" class="attachment_delete image_style" data-delete=` + attachment.id + `></td>
                        </tr>
                    `);
                });
                var paginationControlsAttachment = $("#pagination-controls-Attachments");
                paginationControlsAttachment.empty();
                if (paginationAttachment.prev_page_url) {
                    paginationControlsAttachment.append('<button type="button" class="btn btn-default2" onclick="getAttachment(' + id + ', \'' + paginationAttachment.prev_page_url + '\')">Previous</button>');
                }
                if (paginationAttachment.next_page_url) {
                    paginationControlsAttachment.append('<button type="button" class="btn btn-default2" onclick="getAttachment(' + id + ', \'' + paginationAttachment.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }

    function getProductDetail(id, pageUrl = '{{ url("getPurchaesOrderProductDetail") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                var data = response.data[0];
                const tableBody = document.querySelector(`#result tbody`);
                var purchase_order_products = data.product_details.purchase_order_products;
                // console.log(purchase_order_products);return false;
                if (purchase_order_products.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id = 'EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center';

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                    var paid_amount = response.paid_amount;
                    $("#paid_amount").text("-£" + parseFloat(paid_amount).toFixed(2));
                    purchase_order_products.forEach(product => {
                        const row = document.createElement('tr');


                        // job dropdown
                        const dropdownJob = document.createElement('td');

                        const selectDropdownJob = document.createElement('select');
                        selectDropdownJob.name = 'job_id[]';
                        selectDropdownJob.className = "form_control";

                        const defaultOptionJob = document.createElement('option');
                        defaultOptionJob.value = '';
                        defaultOptionJob.text = '-Not Selected-';

                        selectDropdownJob.appendChild(defaultOptionJob);

                        const optionsJob = data.all_job;
                        optionsJob.forEach(optionJob => {
                            const optJob = document.createElement('option');
                            optJob.value = optionJob.id;
                            optJob.textContent = optionJob.name;
                            selectDropdownJob.appendChild(optJob);
                        });
                        dropdownJob.appendChild(selectDropdownJob);
                        row.appendChild(dropdownJob);
                        // end
                        const nameCell = document.createElement('td');
                        nameCell.innerHTML = data.purchase_order_products_detail.product_name;
                        row.appendChild(nameCell);

                        const codeCell = document.createElement('td');
                        // codeCell.textContent = data.purchase_order_products_detail.product_code;
                        const inputCode = document.createElement('input');
                        inputCode.className = 'product_code form-control';
                        inputCode.name = 'product_code[]';
                        inputCode.value = product.code;
                        codeCell.appendChild(inputCode);
                        row.appendChild(codeCell);

                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.className = 'product_id';
                        hiddenInput.name = 'product_id[]';
                        hiddenInput.value = data.purchase_order_products_detail.id;
                        row.appendChild(hiddenInput);
                        // purchase order product hidden id if not duplicate is null
                        <?php if ((isset($purchase_orders) && $purchase_orders != '') && (isset($duplicate) && $duplicate == '')) { ?>
                            const hiddenID = document.createElement('input');
                            hiddenID.type = 'hidden';
                            hiddenID.className = 'purchase_product_id';
                            hiddenID.name = 'purchase_product_id[]';
                            hiddenID.value = product.id;
                            row.appendChild(hiddenID);
                        <?php } ?>
                        // end

                        const descriptionCell = document.createElement('td');
                        const inputDescription = document.createElement('textarea');
                        inputDescription.className = 'description form-control';
                        inputDescription.setAttribute('rows', '1');
                        inputDescription.name = 'description[]';
                        inputDescription.value = product.description;
                        inputDescription.addEventListener('input', function() {
                            auto_grow(this);
                        });
                        descriptionCell.appendChild(inputDescription);
                        row.appendChild(descriptionCell);

                        const dropdownAccountCode = document.createElement('td');
                        const selectDropdownAccountCode = document.createElement('select');
                        selectDropdownAccountCode.className = 'accountCode_id form_control';
                        selectDropdownAccountCode.name = 'accountCode_id[]';
                        // selectDropdownAccountCode.addEventListener('click', function() {
                        //     var elements = document.getElementsByClassName('accountCode_id');
                        //     getAccountCode(elements);
                        // });

                        const optionsAccountCode = data.accountCode;

                        const defaultOptionAccountCode = document.createElement('option');
                        defaultOptionAccountCode.value = '';
                        defaultOptionAccountCode.text = '-No Department-';
                        selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                        optionsAccountCode.forEach(optionJob => {
                            const optAccountCode = document.createElement('option');
                            optAccountCode.value = optionJob.id;
                            optAccountCode.textContent = optionJob.name;
                            selectDropdownAccountCode.appendChild(optAccountCode);
                        });
                        dropdownAccountCode.appendChild(selectDropdownAccountCode);
                        row.appendChild(dropdownAccountCode);

                        const qtyCell = document.createElement('td');
                        const inputQty = document.createElement('input');
                        inputQty.type = 'text';
                        inputQty.className = 'qty input50 form-control';
                        inputQty.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row, paid_amount);
                        });
                        inputQty.name = 'qty[]';
                        inputQty.value = product.qty;
                        qtyCell.appendChild(inputQty);
                        row.appendChild(qtyCell);

                        const priceCell = document.createElement('td');
                        const inputPrice = document.createElement('input');
                        inputPrice.type = 'text';
                        inputPrice.className = 'product_price input50 form-control';
                        inputPrice.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row, paid_amount);
                        });
                        inputPrice.name = 'price[]';
                        inputPrice.value = product.price;
                        GrandPrice = GrandPrice + Number(product.price);
                        priceCell.appendChild(inputPrice);
                        row.appendChild(priceCell);

                        const dropdownVat = document.createElement('td');
                        const selectDropdownVat = document.createElement('select');
                        selectDropdownVat.addEventListener('change', function() {
                            getIdVat($(this).val(), row, paid_amount);
                        });
                        selectDropdownVat.name = 'vat_id[]';
                        selectDropdownVat.className = 'vat_id form_control';
                        const optionsVat = data.tax;
                        var tax_rate = '00';
                        optionsVat.forEach(optionVat => {
                            const optVat = document.createElement('option');
                            optVat.value = optionVat.id;
                            if (optionVat.id == product.vat_id) {
                                tax_rate = optionVat.tax_rate;
                                optVat.setAttribute("selected", "selected");
                            }
                            optVat.textContent = optionVat.name;
                            selectDropdownVat.appendChild(optVat);
                        });
                        const inputVatRate = document.createElement('input');
                        inputVatRate.type = 'hidden';
                        inputVatRate.className = 'vat_ratePercentage';
                        inputVatRate.name = 'vat_ratePercentage[]';
                        inputVatRate.value = tax_rate;
                        dropdownVat.appendChild(inputVatRate);
                        dropdownVat.appendChild(selectDropdownVat);
                        row.appendChild(dropdownVat);

                        const vatCell = document.createElement('td');
                        const inputVat = document.createElement('input');
                        inputVat.type = 'text';
                        inputVat.className = 'vat form-control';
                        inputVat.style = "max-width:70px;";
                        inputVat.setAttribute('disabled', 'disabled');
                        inputVat.addEventListener('input', function() {
                            updateAmount(row, paid_amount);
                        });
                        inputVat.name = 'vat[]';
                        inputVat.value = parseFloat(tax_rate).toFixed(2);
                        vatCell.appendChild(inputVat);
                        row.appendChild(vatCell);

                        const amountCell = document.createElement('td');
                        amountCell.innerHTML = '£' + parseFloat(product.price).toFixed(2);
                        amountCell.className = "price";
                        row.appendChild(amountCell);
                        totalAmount = totalAmount + Number(product.price);

                        const delveriQTYCell = document.createElement('td');
                        delveriQTYCell.innerHTML = '-';
                        delveriQTYCell.className = 'text-center';
                        row.appendChild(delveriQTYCell);

                        const deleteCell = document.createElement('td');
                        deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                        deleteCell.addEventListener('click', function() {
                            removeRow(this, product.id);
                        });
                        row.appendChild(deleteCell);

                        tableBody.appendChild(row);
                        updateAmount(row, paid_amount)
                    });
                    $("#product_calculation").show();

                }

                // var paginationProductDetails = response.pagination;

                // var paginationControlsProductDetail = $("#pagination-controls-Produc-details");
                // paginationControlsProductDetail.empty();
                // if (paginationProductDetails.prev_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="btn btn-default2" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                // }
                // if (paginationProductDetails.next_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="btn btn-default2" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                // }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }

    function auto_grow(element) {
        console.log("Here")
        element.style.height = "5px";
        element.style.height = (element.scrollHeight) + "px";
    }

    function getAllNewTaskList(id, pageUrl = '{{ url("getAllNewTaskList") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                var paginationNewTask = response.pagination;
                const newTask = response.data;
                // console.log(newTask);
                const tbody = $('#newtask_result');
                tbody.empty();
                var taskCount = 1;
                newTask.forEach(task => {
                    const created_at = moment(task.created_at).format('DD/MM/YYYY HH:mm');
                    const date = moment(task.date).format('DD/MM/YYYY HH:mm');
                    const executed = moment(task.executed).format('DD/MM/YYYY HH:mm');
                    const imag_path = '{{url("public/frontEnd/jobs/images/pencil.png")}}';
                    tbody.append(`
                        <tr>
                            <td>${date}</td>
                            <td>${task.ref}</td>
                            <td>${task.user}</td>
                            <td>${task.type}</td>
                            <td>${task.title}</td>
                            <td>${task.notes || ''}</td>
                            <td>${created_at}</td>
                            <td>-</td>
                            <td><img src="${imag_path}" height="16px" alt="" data-bs-toggle="modal" data-bs-target="#NewTaskModal" class="modal_dataTaskFetch image_style" data-id="${task.id}" data-po_id="${task.po_id}" data-supplier_id="${task.supplier_id}" data-user_id="${task.user_id}" data-title="${task.title}" data-task_type_id="${task.task_type_id}" data-start_date="${task.date}" data-start_time="${task.start_time}" data-end_date="${task.end_date}" data-end_time="${task.end_time}" data-is_recurring="${task.is_recurring}" data-notify="${task.notify}" data-notify_date="${task.notify_date}" data-notify_time="${task.notify_time}" data-notification="${task.notification}" data-email="${task.email}" data-sms="${task.sms}" data-notes="${task.notes}"></td>
                        </tr>
                    `);
                    taskCount++;
                });
                var paginationControlsNewTask = $("#pagination-controls-New-task");
                paginationControlsNewTask.empty();
                if (paginationNewTask.prev_page_url) {
                    paginationControlsNewTask.append('<button type="button" class="btn btn-default2" onclick="getAllNewTaskList(' + id + ', \'' + paginationNewTask.prev_page_url + '\')">Previous</button>');
                }
                if (paginationNewTask.next_page_url) {
                    paginationControlsNewTask.append('<button type="button" class="btn btn-default2" onclick="getAllNewTaskList(' + id + ', \'' + paginationNewTask.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }
    $(document).on('click', '.modal_dataTaskFetch', function() {
        $("#completeBTN").show();
        var taskId = $(this).data('id');
        var task_po_id = $(this).data('po_id');
        var task_supplier_id = $(this).data('supplier_id');
        var title = $(this).data('title');
        var userId = $(this).data('user_id');
        var taskTypeId = $(this).data('task_type_id');
        var startDate = $(this).data('start_date');
        var startTime = $(this).data('start_time');
        var endDate = $(this).data('end_date');
        var endTime = $(this).data('end_time');
        var isRecurring = $(this).data('is_recurring');
        var notify = $(this).data('notify');
        var notification = $(this).data('notification');
        var email = $(this).data('email');
        var sms = $(this).data('sms');
        var notifyDate = $(this).data('notify_date');
        var notifyTime = $(this).data('notify_time');
        var notes = $(this).data('notes');
        get_supplier_details();
        $('#task_id').val(taskId);
        $('#task_po_id').val(task_po_id);
        $('#task_supplier_id').val(task_supplier_id);
        $('#task_user_id').val(userId);
        $('#taskTitle').val(title);
        $('#taskTypeId').val(taskTypeId);
        $('#taskStartDate').val(startDate);
        $('#taskStartTime').val(startTime);
        $('#taskEndDate').val(endDate).removeAttr('disabled');
        $('#taskEndTime').val(endTime);
        $('#notify_date').val(notifyDate);
        $('#notify_time').val(notifyTime);
        $('#taskNotesText').val(notes);
        $(taskEndDate)
        if (isRecurring == 1) {
            $('#isRecurring').prop('checked', true);
        } else {
            $('#isRecurring').prop('checked', false);
        }
        if (notify == 1) {
            $('#yeson').prop('checked', true);
        } else {
            $('#yeson').prop('checked', false);
        }
    });
</script>
<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {

            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'PoAttachment';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        // console.log(data);
                        if (isAuthenticated(data) == false) {
                            return false;
                        }
                        if (data) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                        }
                        // return false;
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                    }
                });
            }
        }

    });
    $(document).on('click', '.delete_checkbox', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });
    $(document).on('click', '.attachment_delete', function() {
        var id = $(this).data('delete');
        if (confirm("Are you sure you want to delete this row?")) {
            $(this).closest('tr').remove();
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('/delete_po_attachment')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    // console.log(data);
                }
            });
        }
    });
</script>
<script>
    function getAllNewTask(data) {
        // console.log(data);
        getAllNewTaskList(data.po_id, pageUrl = '{{ url("getAllNewTaskList") }}');
    }

    function bgColorChange(button) {
        $('.bgColour').removeAttr('style');
        $("#recurringHideShow").hide();
        $("#taskHideShow").hide();
        if (button == 1) {
            $("#taskHideShow").show();
            $("#task_active_inactive").css('background-color', '#57c8f1');
        } else {
            $("#recurringHideShow").show();
            $("#recurring_active_inactive").css('background-color', '#57c8f1');
        }
    }

    function getAllAccountCodeList(data) {
        // console.log(data.data);
        var accList = data.data;
        $('.accountCode_id').append('<option value="' + accList.id + '">' + accList.name + '</option>')
    }

    function getAllVatTaxRate(data) {
        var vatList = data.data;
        $(".vat_id").append('<option value="' + vatList.id + '">' + vatList.name + '</option>');
    }
</script>
<script>
    $('#purchase_qoute_ref').on('keyup', function() {
        let search_purchase_qoute_ref = $(this).val();
        const purchase_customer_id = $("#purchase_customer_id").val();
        const purchase_qoute_refdivList = document.querySelector('.purchase_qoute_ref-container');

        if (search_purchase_qoute_ref === '') {
            purchase_qoute_refdivList.innerHTML = '';
        }
        if (search_purchase_qoute_ref.length > 2) {
            $.ajax({
                url: "{{ url('searchPurchase_qoute_ref') }}",
                method: 'post',
                data: {
                    search_purchase_qoute_ref: search_purchase_qoute_ref,
                    purchase_customer_id: purchase_customer_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // console.log(response);
                    // return false;
                    purchase_qoute_refdivList.innerHTML = "";
                    const div = document.createElement('div');
                    div.className = 'purchase_qoute_ref_container';


                    const ul = document.createElement('ul');
                    ul.id = "purchase_qoute_refList";
                    if (response.data.length > 0) {
                        response.data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.quote_ref;
                            li.id = item.id;
                            li.name = item.quote_ref;
                            li.className = "editInput";
                            ul.appendChild(li);
                            // const hr = document.createElement('hr');
                            // hr.className='dropdown-divider';
                            // ul.appendChild(hr);
                        });

                        div.appendChild(ul);

                        purchase_qoute_refdivList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            purchase_qoute_refdivList.innerHTML = '';
                            document.getElementById('purchase_qoute_ref').value = '';
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedPurchaseQuotRefId = event.target.id;
                                const selectedPurchaseQuoteName = event.target.name;
                                // console.log('Selected Customer ID:', selectedPurchaseQuotRefId);
                                // console.log('Selected Customer Name:', selectedPurchaseQuoteName);
                                $("#purchase_qoute_ref").val(selectedPurchaseQuoteName);
                                $("#selectedPurchaseQuotRefId").val(selectedPurchaseQuotRefId);
                            }
                        });
                    } else {
                        const Errorli = document.createElement('li');
                        Errorli.textContent = 'Sorry Data Not found';
                        Errorli.id = 'searchError';
                        Errorli.className = "editInput";
                        ul.appendChild(Errorli);
                        div.appendChild(ul);
                        purchase_qoute_refdivList.appendChild(div);
                        setTimeout(function() {
                            purchase_qoute_refdivList.innerHTML = '';
                        }, 1000);
                    }

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            purchase_qoute_refdivList.innerHTML = '';
            $('#results').empty();
        }
    });

    $('#purchase_job_ref').on('keyup', function() {
        let search_purchase_job_ref = $(this).val();
        const purchase_customer_id = $("#purchase_customer_id").val();
        const purchase_job_refdivList = document.querySelector('.purchase_job_ref-container');

        if (search_purchase_job_ref === '') {
            purchase_job_refdivList.innerHTML = '';
        }
        if (search_purchase_job_ref.length > 2) {
            $.ajax({
                url: "{{ url('searchPurchase_job_ref') }}",
                method: 'post',
                data: {
                    search_purchase_job_ref: search_purchase_job_ref,
                    purchase_customer_id: purchase_customer_id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // console.log(response);
                    // return false;
                    purchase_job_refdivList.innerHTML = "";
                    const div = document.createElement('div');
                    div.className = 'purchase_job_ref_container';


                    const ul = document.createElement('ul');
                    ul.id = "purchase_job_refList";
                    if (response.data.length > 0) {
                        response.data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.job_ref;
                            li.id = item.id;
                            li.name = item.job_ref;
                            li.className = "editInput";
                            ul.appendChild(li);
                            // const hr = document.createElement('hr');
                            // hr.className='dropdown-divider';
                            // ul.appendChild(hr);
                        });

                        div.appendChild(ul);

                        purchase_job_refdivList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            purchase_job_refdivList.innerHTML = '';
                            document.getElementById('purchase_job_ref').value = '';
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedPurchaseJobRefId = event.target.id;
                                const selectedPurchaseJobName = event.target.name;
                                // console.log('Selected Customer ID:', selectedPurchaseJobRefId);
                                // console.log('Selected Customer Name:', selectedPurchaseJobName);
                                $("#purchase_job_ref").val(selectedPurchaseJobName);
                                $("#selectedPurchaseJobRefId").val(selectedPurchaseJobRefId);
                            }
                        });
                    } else {
                        const Errorli = document.createElement('li');
                        Errorli.textContent = 'Sorry Data Not found';
                        Errorli.id = 'searchError';
                        Errorli.className = "editInput";
                        ul.appendChild(Errorli);
                        div.appendChild(ul);
                        purchase_job_refdivList.appendChild(div);
                        setTimeout(function() {
                            purchase_job_refdivList.innerHTML = '';
                        }, 1000);
                    }

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            purchase_job_refdivList.innerHTML = '';
            $('#results').empty();
        }
    });

    function openReminderModal(po_id) {
        // if(id != ''){
        //     alert("Please save Purchase Order first!");
        //     // return false;
        // }else{

        // }
        $("#clickyesno").removeClass('unclicked');
        $("#reminder_po_id").val(po_id);
        $("#ReminderModal").modal('show');
    }

    function getAllReminder(data) {
        $(".setRiminderTable").show();
        $("#reminder_data").append(`<tr>
            <td>` + data.title + `</td>    
            <td>` + data.reminder_date + `</td>    
            <td>` + data.reminder_time + `</td>    
            <td><span class="iconColrRad">Pending</span></td>    
            <td>
                <a href="javascript:void(0)" class="iconColrGreen fecth_data" data-id="` + data.id + `" data-title="` + data.title + `" data-user_id="` + data.user_id + `" data-reminder_date="` + data.reminder_date + `" data-reminder_time="` + data.reminder_time + `" data-notification="` + data.notification + `" data-sms="` + data.sms + `" data-email="` + data.email + `" data-notes="` + data.notes + `" data-icon="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a> | 
                <a href="javascript:void(0)" class="iconColrRad"><i class="fa fa-times" aria-hidden="true"></i></a>
            </td>    
        </tr>`);
    }
</script>
<script>
    function updateDueDate() {
        const selectElement = document.getElementById('purchase_payment_terms');
        const days = parseInt(selectElement.value);
        const currentDate = new Date();
        currentDate.setDate(currentDate.getDate() + days);
        const formattedDate = currentDate.toISOString().split('T')[0];
        document.getElementById('purchase_payment_due_date').value = formattedDate;
    }
</script>
<script>
    $(document).on('click', '.fecth_data', function() {
        $("#ReminderModal").modal('show');
        var id = $(this).data('id');
        var title = $(this).data('title');
        var user_id = $(this).data('user_id');
        var reminder_date = $(this).data('reminder_date');
        var reminder_time = $(this).data('reminder_time');
        var notification = $(this).data('notification');
        var sms = $(this).data('sms');
        var email = $(this).data('email');
        var notes = $(this).data('notes');
        var icon = $(this).data('icon');
        if (icon === 'eye') {
            $("#reminder_date").attr('disabled', 'disabled');
            $("#clickyesno").addClass('unclicked');
            $("#reminder_notification").attr('disabled', 'disabled');
            $("#reminder_sms").attr('disabled', 'disabled');
            $("#reminder_email").attr('disabled', 'disabled');
            $("#reminder_title").attr('disabled', 'disabled');
            $("#reminder_notes").attr('disabled', 'disabled');
        } else {
            $("#reminder_date").removeAttr('disabled', 'disabled');
            $("#clickyesno").removeClass('unclicked');
            $("#reminder_notification").removeAttr('disabled', 'disabled');
            $("#reminder_sms").removeAttr('disabled', 'disabled');
            $("#reminder_email").removeAttr('disabled', 'disabled');
            $("#reminder_title").removeAttr('disabled', 'disabled');
            $("#reminder_notes").removeAttr('disabled', 'disabled');
        }


        $("#reminder_id").val(id);
        $("#reminder_date").val(reminder_date);
        $("#reminder_time").val(reminder_time);
        $("#reminder_title").val(title);
        $("#reminder_notes").val(notes);

        $("#reminder_notification").prop('checked', notification == 1);
        $("#reminder_sms").prop('checked', sms == 1);
        $("#reminder_email").prop('checked', email == 1);

        if (user_id) {
            $('.multiselect-dropdown').hide();
            var userArray = user_id.split(',');
            $("#reminder_user option").each(function() {
                $(this).prop('selected', userArray.includes($(this).val()));
            });
        } else {
            $("#reminder_user option").prop('selected', false);
        }
        userArray.forEach(function(userId) {
            $(`#reminder_user option[value="${userId}"]`).prop('selected', true);
            $(`#reminder_user + .multiselect-container input[type="checkbox"][value="${userId}"]`).prop('checked', true);
        });
        MultiselectDropdown();

    });

    function getAllPurchaseInvoices(po_id, pageUrl = '{{ url("getAllPurchaseInvoices") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                po_id: po_id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                //return false;
                // var data=response.list_data.data;
                var data = response.list_data;
                const tableBody = document.querySelector(`#supplier_invoice_table tbody`);
                if (data.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id = 'EmptyError'
                    const noDataCell = document.createElement('td');

                    noDataCell.setAttribute('colspan', 4);
                    noDataCell.textContent = 'No products found';
                    noDataCell.style.textAlign = 'center';

                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    $("#SupplierInvoiceList").show();
                    const emptyErrorRow = document.getElementById('EmptyError');
                    if (emptyErrorRow) {
                        emptyErrorRow.remove();
                    }
                    data.forEach(invoice => {
                        var supplier_name = invoice.suppliers.name;
                        const row = document.createElement('tr');
                        var createinv_date = moment(invoice.created_at).format('DD/MM/YYYY');
                        var invoice_date = moment(invoice.invoice_date).format('DD/MM/YYYY');
                        var due_date = moment(invoice.due_date).format('DD/MM/YYYY');

                        const invcreated_at = document.createElement('td');
                        invcreated_at.innerHTML = createinv_date;
                        row.appendChild(invcreated_at);

                        const invrecorderBy = document.createElement('td');
                        invrecorderBy.innerHTML = "{{Auth::user()->name}}";
                        row.appendChild(invrecorderBy);

                        const inv_ref = document.createElement('td');
                        inv_ref.innerHTML = invoice.inv_ref;
                        row.appendChild(inv_ref);

                        const inv_date = document.createElement('td');
                        inv_date.innerHTML = invoice_date;
                        row.appendChild(inv_date);

                        const inv_duedate = document.createElement('td');
                        inv_duedate.innerHTML = due_date;
                        row.appendChild(inv_duedate);

                        const invdescription = document.createElement('td');
                        invdescription.innerHTML = invoice.notes || "";
                        row.appendChild(invdescription);

                        const invattachment = document.createElement('td');
                        invattachment.innerHTML = invoice.original_file_name || "";
                        row.appendChild(invattachment);

                        const invpaid_status = document.createElement('td');
                        invpaid_status.innerHTML = (invoice.oustanding_amount == 0) ? "Yes" : "No";
                        row.appendChild(invpaid_status);

                        const invnet_amount = document.createElement('td');
                        invnet_amount.innerHTML = invoice.net_amount;
                        row.appendChild(invnet_amount);

                        const invvat_amount = document.createElement('td');
                        invvat_amount.innerHTML = invoice.vat_amount;
                        row.appendChild(invvat_amount);

                        const invvat_grossamount = document.createElement('td');
                        invvat_grossamount.innerHTML = invoice.gross_amount;
                        row.appendChild(invvat_grossamount);

                        const invvat_paid = document.createElement('td');
                        invvat_paid.innerHTML = '0.00';
                        row.appendChild(invvat_paid);

                        const invvat_outstanding = document.createElement('td');
                        invvat_outstanding.innerHTML = invoice.oustanding_amount;
                        row.appendChild(invvat_outstanding);

                        const invActionHtml = `<div class="d-flex justify-content-end">
                                        <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link dropdown-toggle btn btn-default2" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="javascript:void(0)" class="dropdown-item">Edit Invoice</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="openRecordPaymentModal(` + invoice.po_id + `,'` + invoice.inv_ref + `',` + invoice.supplier_id + `,'` + invoice.gross_amount + `','` + invoice_date + `',` + invoice.oustanding_amount + `,'` + supplier_name + `')" class="dropdown-item">Record Payment</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" onclick="openInvoiceRecieveModal()" class="dropdown-item">Delete Invoice</a>
                                                
                                            </div>
                                        </div>
                                    </div>`;

                        const invvat_actionList = document.createElement('td');
                        invvat_actionList.innerHTML = invActionHtml;
                        row.appendChild(invvat_actionList);

                        tableBody.appendChild(row);
                        // updateAmount(row)
                    });
                    // $("#product_calculation").show();

                }

                // var paginationProductDetails = response.pagination;

                // var paginationControlsProductDetail = $("#pagination-controls-Produc-details");
                // paginationControlsProductDetail.empty();
                // if (paginationProductDetails.prev_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="btn btn-default2" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                // }
                // if (paginationProductDetails.next_page_url) {
                //     paginationControlsProductDetail.append('<button type="button" class="btn btn-default2" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                // }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }

    function getAllPaymentPaids(po_id, pageUrl = '{{ url("getAllPaymentPaids") }}') {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                po_id: po_id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                // return false;
                if (response.len > 0) {
                    $("#PaymentsPaid").show();
                    $("#payment_paid_result").html(response.data);
                } else {
                    $("#PaymentsPaid").hide();
                }

            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }

    $(document).on('click', '.image_delete_payment_paid', function() {
        var id = $(this).data('delete');
        var delete_from = $(this).data('delete_from');
        if (confirm("Are you sure to delete?")) {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('paymentPaidDelete')}}",
                data: {
                    id: id,
                    delete_from: delete_from,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    if (data.success === true) {
                        location.reload();
                    } else {
                        alert("Something went wrong");
                    }
                    // return false;
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    });

    function openRecordPaymentModal(po_id, ref, supplier_id, total_amount, date, oustanding_amount, supplier_name) {
        var total_amount = Number(total_amount);
        $("#recordPaymentModal").modal('show');
        $("#purchaseOrderRecordDate").text(ref + ' On ' + date);
        $("#recordPayment_po_id").val(po_id);
        $("#recordPaymentModalLabel").text("Record Payment - " + ref);
        $("#recordPayment_ppurchaseSupplier_id").val(supplier_id);
        // $("#recordPayment_ppurchaseProduct_id").val(product_id);
        $("#record_TotalAmount").text('£' + total_amount.toFixed(2));
        $("#record_OutstandingAmount").text('£' + oustanding_amount.toFixed(2));
        $("#record_AmountPaid").val(oustanding_amount.toFixed(2));
        $("#record_supplierName").text(supplier_name);

    }

    function saverecordPaymentModal() {
        $.ajax({
            type: "POST",
            url: "{{url('/savePurchaseOrderRecordPayment')}}",
            data: new FormData($("#recordPaymentForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_recordPaymentModal').addClass('success-message').text(response.message).show();
                    setTimeout(function() {
                        $('#message_recordPaymentModal').removeClass('success-message').text('').hide();
                        location.reload();
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_recordPaymentModal').addClass('error-message').text(response.message).show();
                    setTimeout(function() {
                        $('#error-message').text('').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
    $(document).on('input', '.product_code', function() {
        let input = $(this).val();
        if (input.length > 50) {
            $(this).val(input.substring(0, 50));
        }
    });
</script>
<script>
    function defualt_date(type) {
        if (type == 1) {
            flatpickr("#purchase_purchase_date", {
                dateFormat: "d/m/Y",
                defaultDate: new Date()
            });
            flatpickr("#purchase_payment_due_date", {
                dateFormat: "d/m/Y",
                defaultDate: new Date()
            });

        } else {
            flatpickr("#purchase_purchase_date", {
                dateFormat: "d/m/Y",
                defaultDate: "<?php if (isset($purchase_orders->purchase_date) && $purchase_orders->purchase_date != '') {
                                    echo \Carbon\Carbon::parse($purchase_orders->purchase_date)->format('d/m/Y');
                                } ?>"
            });
            flatpickr("#purchase_payment_due_date", {
                dateFormat: "d/m/Y",
                defaultDate: "<?php if (isset($purchase_orders->payment_due_date) && $purchase_orders->payment_due_date != '') {
                                    echo \Carbon\Carbon::parse($purchase_orders->payment_due_date)->format('d/m/Y');
                                } ?>"
            });
        }

    }

    function show_searchModal(seaceh_type) {
        $("#refrence_purchase_seacrh_modal").modal('show');
    }
</script>

<script>
    $('.tab-menu li a').on('click', function () {
        var $this = $(this);
        var target = $this.attr('data-rel');
        
        // find the closest tab-menu
        var $tabMenu = $this.closest('.tab-menu');
        var $tabContentContainer = $this.closest('.tab-teaser, .tab-container'); // adjust container if needed

        $tabMenu.find('li a').removeClass('active');
        $this.addClass('active');

        // Show the related tab-box inside the same section
        $tabContentContainer.find(".tab-box").hide();
        $tabContentContainer.find("#" + target).fadeIn('slow');

        return false;
    });
</script>

@endsection