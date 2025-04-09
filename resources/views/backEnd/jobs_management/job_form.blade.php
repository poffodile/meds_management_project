@extends('backEnd.layouts.master')

@section('title',':User Form')

@section('content')

<style type="text/css">
    .position-center label {
        font-size: 20px;
        font-weight: 500;
    }

    .position-center {
        width: 100%
    }

    .ui-widget-content .ui-icon {
        background-image: url(images/ui-icons_444444_256x240.png) !important;
    }

    .position-center .assign-access {
        font-size: 16px;
        font-weight: 500;
    }

    .add_field_button-area {
        margin: 20px 0px 0px 0px;
    }

    .col-lg-offset-3 .btn.btn-primary {
        margin: 0px 10px 0px 0px;
    }

    .qual_upload {
        margin: 20px 0px 0px 0px;
    }


    .input-group-addon {
        border: none;
    }

    .input-group-addon.remove-addon {
        padding: 5px 0px 15px 0px;
    }

    .custom-legend {
        position: absolute;
        top: -10px;
        left: 20px;
        background-color: white;
        font-weight: bold;
        padding: 0 10px;
    }

    .custom-fieldset {
        position: relative;
        border: 2px solid #00000026;
        padding: 10px;
        margin-top: 20px;
    }

    .appointment_button {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .count {
        height: 30px;
        width: 30px;
        line-height: 15px;
        border-radius: 15px;
    }

    .leftNum {
        background: #53a6dc;
        padding: 4px;
        color: #fff;
        text-align: center;
        font-size: 14px;
        border-radius: 50%;
        width: 30px;
        height: 28px;
        line-height: 22px;
        margin-right: 4px;
    }

    .callIcon {
        font-size: 30px;
        color: #53a6dc;
        margin-left: 6px;
    }

    .addTextarea textarea {
        text-indent: 10px;
        width: 100%;
    }

    .addDateAndTime {
        display: flex;
    }

    .addDateAndTime .editInput {
        border: 1px solid #dee2e6;
    }

    .Priority {
        display: flex;
        padding-top: 6px;
    }

    .Priority label {
        padding: 10px;
        white-space: nowrap;
    }

    .statuswating {
        display: flex;
    }

    .statuswating a i {
        font-size: 22px;
        color: #53a6dc;
        line-height: 31px;
        margin-left: 8px;
    }

    .displaynone {
        display: none;
    }
</style>
<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{$task}} Job
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" id="form_data" enctype="multipart/form-data">
                                <div class="from_outside_border">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h4 class="contTitle">Customer Details</h4>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Customer*</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="customer_id" id="customer_id" onchange="get_customer_details()">
                                                        <option disabled selected>select Customer</option>
                                                        <?php foreach ($customers as $cust) { ?>
                                                            <option value="{{$cust->id}}" <?php if (isset($job_details) && $job_details->customer_id == $cust->id) {
                                                                                                echo 'selected';
                                                                                            } ?>>{{$cust->name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                    <p style="color:red;display:none" id="Customer_idError">* Customer is Required Field *</p>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Project</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="project_id" id="project_id" disabled>
                                                        <option disabled selected>select Customer First</option>
                                                        <?php foreach ($projects as $val) { ?>
                                                            <option value="{{$val->id}}" <?php if (isset($job_details) && $job_details->customer_id == $val->id) {
                                                                                                echo 'selected';
                                                                                            } ?>>{{$val->project_name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                    <p style="color:red;display:none" id="project_idError">* Project is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Contact</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="contact_id" id="contact_id" disabled>
                                                        <option value="default" selected>Select Customer First</option>

                                                    </select>
                                                    <p style="color:red;display:none" id="contact_idError">* Contact is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Name*</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="name" id="name" class="form-control" placeholder="Name" value="<?php if (isset($job_details)) {
                                                                                                                                                echo $job_details->name;
                                                                                                                                            } ?>" maxlength="255">
                                                    <p style="color:red;display:none" id="nameError">* Name is Required Field *</p>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Email</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="<?php if (isset($job_details)) {
                                                                                                                                                    echo $job_details->email;
                                                                                                                                                } ?>">
                                                    <p style="color:red;display:none" id="emailError">* Email is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Telephone</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Telephone" value="<?php if (isset($job_details)) {
                                                                                                                                                                echo $job_details->telephone;
                                                                                                                                                            } ?>" maxlength="255">
                                                    <p style="color:red;display:none" id="telephoneError">* Telephone is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Mobile</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile" value="<?php if (isset($job_details)) {
                                                                                                                                                        echo $job_details->contact;
                                                                                                                                                    } ?>" maxlength="255">
                                                    <p style="color:red;display:none" id="mobileError">* Mobile is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Address</label>
                                                <div class="col-lg-9">
                                                    <textarea name="address" id="address" class="form-control" rows="3" cols="6"><?php if (isset($job_details)) {
                                                                                                                                        echo $job_details->address;
                                                                                                                                    } ?></textarea>
                                                    <p style="color:red;display:none" id="addressError">* Address is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">City</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="city" id="city" class="form-control" placeholder="City" value="<?php if (isset($job_details)) {
                                                                                                                                                echo $job_details->city;
                                                                                                                                            } ?>">
                                                    <p style="color:red;display:none" id="cityError">* City is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="country" id="country" class="form-control" placeholder="Country" value="<?php if (isset($job_details)) {
                                                                                                                                                            echo $job_details->country;
                                                                                                                                                        } ?>">
                                                    <p style="color:red;display:none" id="countryError">* Country is Required Field *</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Pincode</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="pincode" id="pincode" class="form-control" placeholder="Pincode" value="<?php if (isset($job_details)) {
                                                                                                                                                            echo $job_details->pincode;
                                                                                                                                                        } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">

                                                    <select class="form-control" name="country_code" id="country_code">
                                                        <option disabled selected>select Country</option>
                                                        <?php foreach ($country as $val_country) { ?>
                                                            <option value="{{$val_country->id}}" class="country_code">{{$val_country->name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="contTitle">Site Details</h4>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Site</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="site_id" id="site_id" disabled>
                                                        <option desabled selected>select Site</option>
                                                        <option value="1" <?php if (isset($job_details) && $job_details->site_id == 1) {
                                                                                echo 'selected';
                                                                            } ?>>Site-1</option>
                                                        <option value="2" <?php if (isset($job_details) && $job_details->site_id == 2) {
                                                                                echo 'selected';
                                                                            } ?>>Site-2</option>
                                                        <option value="3" <?php if (isset($job_details) && $job_details->site_id == 3) {
                                                                                echo 'selected';
                                                                            } ?>>Site-3</option>
                                                        <option value="4" <?php if (isset($job_details) && $job_details->site_id == 4) {
                                                                                echo 'selected';
                                                                            } ?>>Site-4</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Region</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="region" id="region">
                                                        <option desabled selected>select Site</option>
                                                        <option value="1">India</option>
                                                        <option value="2">Canada</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Company</label>
                                                <div class="col-lg-9">
                                                    <input type="text" class="form-control" name="company_id" id="company_id">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Contact Name</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="conatact_name" id="conatact_name" class="form-control" placeholder="Contact Name" value="<?php if (isset($job_details)) {
                                                                                                                                                                            echo $job_details->conatact_name;
                                                                                                                                                                        } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Email</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_email" id="site_email" class="form-control" placeholder="Siet Email" value="<?php if (isset($job_details)) {
                                                                                                                                                                    echo $job_details->site_email;
                                                                                                                                                                } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Telephone</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_telephone" id="site_telephone" class="form-control" placeholder="Siet Telephone" value="<?php if (isset($job_details)) {
                                                                                                                                                                                echo $job_details->site_telephone;
                                                                                                                                                                            } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Mobile</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_mobile" id="site_mobile" class="form-control" placeholder="Siet Mobile" value="<?php if (isset($job_details)) {
                                                                                                                                                                        echo $job_details->site_mobile;
                                                                                                                                                                    } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Address</label>
                                                <div class="col-lg-9">
                                                    <textarea name="site_address" id="site_address" id="site_address" placeholder="Siet Address" class="form-control" rows="3" cols="6"><?php if (isset($job_details)) {
                                                                                                                                                                                            echo $job_details->site_address;
                                                                                                                                                                                        } ?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">City</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_city" id="site_city" class="form-control" placeholder="Siet City" value="<?php if (isset($job_details)) {
                                                                                                                                                                echo $job_details->site_city;
                                                                                                                                                            } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_country" id="site_country" class="form-control" placeholder="Siet Country" value="<?php if (isset($job_details)) {
                                                                                                                                                                        echo $job_details->site_country;
                                                                                                                                                                    } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Pincode</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_pincode" id="site_pincode" class="form-control" placeholder="Siet Pincode" value="<?php if (isset($job_details)) {
                                                                                                                                                                        echo $job_details->site_pincode;
                                                                                                                                                                    } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">

                                                    <select class="form-control" name="site_country_code" id="site_country_code">
                                                        <option disabled selected>select Country</option>
                                                        <?php foreach ($country as $site_country) { ?>
                                                            <option value="{{$site_country->id}}" class="site_country_code">{{$site_country->name}}</option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Notes</label>
                                                <div class="col-lg-9">
                                                    <textarea name="notes" id="notes" class="form-control" rows="3" cols="6"><?php if (isset($job_details)) {
                                                                                                                                    echo $job_details->notes;
                                                                                                                                } ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="contTitle">Jobs Details</h4>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Customer Ref</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="cust_ref" id="cust_ref" class="form-control" placeholder="Customer Ref If any" value="<?php if (isset($job_details)) {
                                                                                                                                                                        echo $job_details->customer_ref;
                                                                                                                                                                    } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Customer Job Ref</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="cust_job_ref" id="cust_job_ref" class="form-control" placeholder="Customer Job Ref If any" value="<?php if (isset($job_details)) {
                                                                                                                                                                                    echo $job_details->cust_job_ref;
                                                                                                                                                                                } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Purch. Order Ref</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="order_ref" id="order_ref" class="form-control" placeholder="Purchase Order Ref if any" value="<?php if (isset($job_details)) {
                                                                                                                                                                                echo $job_details->purchase_order_ref;
                                                                                                                                                                            } ?>" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Job Type</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="job_type" id="job_type">
                                                        <option disabled selected>select Job Type</option>
                                                        <?php foreach ($job_type as $type) { ?>
                                                            <option value="{{$type->id}}" <?php if (isset($job_details) && $job_details->job_type == $type->id) {
                                                                                                echo 'selected';
                                                                                            } ?>>{{$type->name}}</option>
                                                        <?php } ?>


                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Priority</label>
                                                <div class="col-lg-9">
                                                    <select class="form-control" name="priority" id="priority">
                                                        <option disabled selected>select Priority</option>
                                                        <option value="1" <?php if (isset($job_details) && $job_details->priorty == 1) {
                                                                                echo 'selected';
                                                                            } ?>>Normal</option>
                                                        <option value="2" <?php if (isset($job_details) && $job_details->priorty == 2) {
                                                                                echo 'selected';
                                                                            } ?>>Medium</option>
                                                        <option value="3" <?php if (isset($job_details) && $job_details->priorty == 3) {
                                                                                echo 'selected';
                                                                            } ?>>None</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Alert Customer</label>
                                                <div class="col-lg-9">
                                                    <input type="checkbox" name="alert_cust" id="alert_cust" value="0" <?php if (isset($job_details) && $job_details->alert_customer == 1) {
                                                                                                                            echo 'checked';
                                                                                                                        } ?>> By Email
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">On Rout SMS Alert</label>
                                                <div class="col-lg-9">
                                                    <input type="radio" name="sms" id="sms" value="1" <?php if (isset($job_details) && $job_details->on_route_sms == 1) {
                                                                                                            echo 'checked';
                                                                                                        } ?>> Yes
                                                    <input type="radio" name="sms" id="sms1" value="2" <?php if (isset($job_details) && $job_details->on_route_sms == 2) {
                                                                                                            echo 'checked';
                                                                                                        } else {
                                                                                                            echo 'checked';
                                                                                                        } ?>> No
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Start Date*</label>
                                                <div class="col-lg-9">
                                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php if (isset($job_details)) {
                                                                                                                                            echo $job_details->start_date;
                                                                                                                                        } ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Complete By</label>
                                                <div class="col-lg-9">
                                                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php if (isset($job_details)) {
                                                                                                                                        echo $job_details->complete_by;
                                                                                                                                    } ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Tags</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="tags" id="tags" class="form-control" value="<?php if (isset($job_details)) {
                                                                                                                                echo $job_details->tags;
                                                                                                                            } ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Short Description <span class="radStar"> *</span>(max 250 characters)</label>
                                                <div class="col-lg-9">
                                                    <textarea name="description" class="form-control" placeholder="description" rows="3" cols="6" onkeyup="get_char()" id="short_dec"><?php if (isset($job_details)) {
                                                                                                                                                                                            echo $job_details->description;
                                                                                                                                                                                        } ?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-3 control-label">Desc./Instructions</label>
                                                <div class="col-lg-9">
                                                    <textarea class="form-control editor" placeholder="description" rows="3" cols="6"><?php if (isset($job_details)) {
                                                                                                                                            echo $job_details->short_decinc;
                                                                                                                                        } ?></textarea>
                                                    <textarea name="short_decinc" class="form-control displaynone" placeholder="description" rows="3" cols="1" id="description1"></textarea>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <div class="from_outside_border mrg_tp">
                                <div class="custom-legend">Product Details</div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3 mt-3">
                                                <div class="form-group mt-3">
                                                    <label class="col-lg-2 control-label">Select Product</label>
                                                    <div class="col-md-4">
                                                    <input type="search" class="form-control" id="search_value" onkeyup="get_search()">
                                                    </div>
                                                    <div class="col-md-5">
                                                    <button class="btn btn-primary" type="button" style="width: 30px;height: 30px;font-size: 10px;font:initial;" onclick="show_product_model()">+</button>
                                                    <p style="display:inline-block">(Type to view product or <a href="javascript:" onclick="show_product_model()">Click here</a> to views all products)</p>
                                                    </div>
                                                </div>


                                                <div class="heading">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead id="result">
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Description</th>
                                                                    <th>QTY</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="details_pro">
                                                                <?php if (isset($job_details) && count($jobassign_products) > 0) {
                                                                    foreach ($jobassign_products as $v) {
                                                                        $product_details = App\Models\Product::where('id', $v->product_id)->first();
                                                                ?>
                                                                        <tr>
                                                                            <th>{{$product_details->product_name}}</th>
                                                                            <th>{{$product_details->description}}</th>
                                                                            <th>{{$v->qty}}</th>
                                                                            <th><a href="javascript:void(0)" class="btn btn-danger" onclick="get_delete_jobproduct({{$v->id}})">Delete</a></th>
                                                                        </tr>
                                                                    <?php }
                                                                } else { ?>
                                                                    <tr id="temp_result1">
                                                                        <td style="color:red;text-align:center" colspan="4">Sorry No data</td>
                                                                    </tr>
                                                                <?php } ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="from_outside_border mrg_tp">
                                    <div class="custom-legend">Notes</div>
                                    <div class="row">
                                            <div class="col-md-12 mb-3 mt-3">
                                                <!-- <div class="custom-fieldset"> -->
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6 form-group">
                                                            <label class="col-lg-12 control-label">Customer Notes</label>
                                                            <div class="col-lg-12">
                                                                <textarea class="form-control editor" placeholder="Customer Notes" rows="3" cols="6" id="short_decinc1"><?php if (isset($job_details)) {
                                                                                                                                                                            echo $job_details->customer_notes;
                                                                                                                                                                        } ?></textarea>
                                                                <textarea name="customer_notes" class="form-control displaynone" rows="3" cols="6" id="description2"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 form-group">
                                                            <label class="col-lg-12 control-label">Internal Notes </label>
                                                            <div class="col-lg-12">
                                                                <textarea class="form-control editor" placeholder="Internal Notes" rows="3" cols="6" id="short_decinc2"><?php if (isset($job_details)) {
                                                                                                                                                                            echo $job_details->internal_notes;
                                                                                                                                                                        } ?></textarea>
                                                                <textarea name="internal_notes" class="form-control displaynone" rows="3" cols="6" id="description3"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <!-- </div> -->
                                            </div>
                                    </div>
                                </div>

                                <div class="from_outside_border mrg_tp">
                                    <div class="row">
                                        <label class="col-lg-3 control-label">Attachments*</label>
                                        <div class="col-lg-9">
                                            <input type="file" name="img_upload" id="img_upload" class="form-control">
                                            <input type="hidden" value="<?php if (isset($job_details)) {
                                                                            echo $job_details->attachments;
                                                                        } ?>" name="old_image">
                                        </div>
                                    </div>
                                </div>
                          

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-10">
                                            <div class="add-admin-btn-area pddtp">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="id" id="id" value="<?php if (isset($job_details)) {
                                                                                                    echo $job_details->id;
                                                                                                } ?>">
                                                <input type="hidden" name="last_job_id" id="last_job_id" value="{{ $last_job_id->id ?? '' }}">
                                                <button type="button" class="btn btn-primary save-btn" onclick="get_save_data()">Save</button>

                                                <a href="{{ url('admin/jobs_list') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal Start here -->
        <div class="modal fade" id="product_model" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <h4 class="modal-title pupTitle" id="productModalLabel">Product</h4>
                    </div>
                    <div class="modal-body pdbotm">
                    <div class="row">
                            <div class="col-md-12">
                                <div class="">
                        <div class="form-group">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Category</th>
                                            <th>Product</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody id="search_result">
                                        @foreach($product_details1 as $details1)
                                        <tr onclick="selectProduct(this)">
                                            <td>{{$details1->id}}</td>
                                            <td>{{$details1->name}}</td>
                                            <td>{{$details1->product_name}}</td>
                                            <td>{{$details1->description}}</td>
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div style="display:flex;justify-content: end;">
                            <button class="btn btn-primary" onclick="get_data_product()">Choose</button>
                        </div>
                                </div>
                            </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->
    </section>
</section>

<script>
    function get_char() {
        var text = $('#short_dec');
        if (text.val().length === 250) {
            text.attr('readonly', 'readonly');
        } else if (text.val().length > 250) {
            text.val('');
        }
    }
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/29.1.0/classic/ckeditor.js"></script>
<script>
    let editors = [];
    document.querySelectorAll('.editor').forEach((element, index) => {
        ClassicEditor
            .create(element)
            .then(editor => {
                editors[index] = editor;
            })
            .catch(error => {
                console.error(error);
            });
    });
    console.log(editors)
</script>

<script>
    $(document).ready(function() {
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#old_image').attr('src', e.target.result);
                    //$('#old_image').attr('src', e.target.result).width(150).height(170);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#img_upload").change(function() {
            var img_name = $(this).val();

            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();
                if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
                    input = document.getElementById('img_upload');
                    if (input.files[0].size > 2097152 || input.files[0].size < 10240) {
                        $(this).val('');
                        $("#img_upload").removeAttr("src");
                        alert("image size should be at least 10KB and upto 2MB");
                        return false;
                    } else {
                        readURL(this);
                    }
                } else {
                    $(this).val('');
                    alert('Please select an image .jpg, .png, .gif file format type.');
                }
            }
            return true;
        });
    });
</script>
<script>
    function get_save_data() {
        // alert("hit hai")
        var id = $('#id').val();
        var Customer_id = $('#customer_id').val();
        var name = $("#name").val();
        var project_id = $('#project_id').val();
        var contact_id = $("#contact_id").val();
        var email = $("#email").val();
        var telephone = $("#telephone").val();
        var mobile = $("#mobile").val();
        var address = $("#address").val();
        var city = $("#city").val();
        var country = $("#country").val();

        var token = '<?php echo csrf_token(); ?>';
        var firstErrorField = null;
        if (Customer_id == '' || Customer_id == null) {
            $("#Customer_idError").show();
            if (!firstErrorField) firstErrorField = $('#Customer_id');
        } else {
            $("#Customer_idError").hide();
        }

        if (name == '') {
            $("#nameError").show();
            if (!firstErrorField) firstErrorField = $('#name');
        } else {
            $("#nameError").hide();
        }

        if (project_id == '' || project_id == null) {
            $("#project_idError").show();
            if (!firstErrorField) firstErrorField = $('#project_id');
        } else {
            $("#project_idError").hide();
        }

        if (contact_id == '' || contact_id == null) {
            $("#contact_idError").show();
            if (!firstErrorField) firstErrorField = $('#contact_id');
        } else {
            $("#contact_idError").hide();
        }

        if (email == '') {
            $("#emailError").show();
            if (!firstErrorField) firstErrorField = $('#email');
        } else {
            $("#emailError").hide();
        }

        if (telephone == '') {
            $("#telephoneError").show();
            if (!firstErrorField) firstErrorField = $('#telephone');
        } else {
            $("#telephoneError").hide();
        }

        if (mobile == '') {
            $("#mobileError").show();
            if (!firstErrorField) firstErrorField = $('#mobile');
        } else {
            $("#mobileError").hide();
        }
        if (address == '') {
            $("#addressError").show();
            if (!firstErrorField) firstErrorField = $('#address');
        } else {
            $("#addressError").hide();
        }
        if (city == '') {
            $("#cityError").show();
            if (!firstErrorField) firstErrorField = $('#city');
        } else {
            $("#cityError").hide();
        }
        if (country == '') {
            $("#countryError").show();
            if (!firstErrorField) firstErrorField = $('#country');
        } else {
            $("#countryError").hide();
        }

        if (firstErrorField) {
            firstErrorField.focus();
            return false;
        } else {
            editors.forEach((editor, index) => {
                $(`#description${index + 1}`).val(editor.getData());
            });
            $.ajax({
                type: "POST",
                url: "{{url('admin/job_save_data')}}",
                data: new FormData($("#form_data")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    console.log(data);
                    if ($.trim(data) == "done") {
                        window.location.href = '<?php echo url('admin/jobs_list'); ?>';
                    }
                }
            });
        }
    }

    function get_search() {
        var search_value = $("#search_value").val();
        var token = '<?php echo csrf_token(); ?>'
        if (search_value.length > 2) {
            $.ajax({
                type: "POST",
                url: "{{url('admin/search_value')}}",
                data: {
                    search_value: search_value,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    // 
                    $("#product_model").modal('show');
                    $('#search_result').html(data);

                }
            });
        }
    }

    function show_product_model() {
        $('#product_model').modal('show');
    }

    function selectProduct(row) {
        var cells = row.getElementsByTagName("td");
        var code = cells[0].innerText;
        var category = cells[1].innerText;
        var product = cells[2].innerText;
        var description = cells[3].innerText;
        var resultTable = document.getElementById("result");
        var newRow = document.createElement("tr");
        var productCell = document.createElement("td");
        productCell.innerText = product;
        var descriptionCell = document.createElement("td");
        descriptionCell.innerText = description;
        var qtyCell = document.createElement("td");
        qtyCell.innerHTML = '<input type="number" class="form-control" value="1" name="quantity[]">';
        var actionCell = document.createElement("td");
        actionCell.innerHTML = '<button class="btn btn-danger" onclick="removeRow(this)">Delete<input type="hidden" value="' + code + '" name="product_detail_id[]"></button>';
        newRow.appendChild(productCell);
        newRow.appendChild(descriptionCell);
        newRow.appendChild(qtyCell);
        newRow.appendChild(actionCell);
        resultTable.appendChild(newRow);
        // $('#product_model').modal('hide');
        $("#temp_result1").hide();
    }

    function removeRow(button) {
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    function get_data_product() {
        $('#product_model').modal('hide');
    }

    $('#alert_cust').change(function() {
        var alert_cust = $('#alert_cust');
        if ($('#alert_cust').is(':checked')) {
            alert_cust.val(1);
        }
    });

    function get_delete_jobproduct(id) {
        if (confirm("Do you want to delete it ?")) {
            var token = '<?php echo csrf_token(); ?>'
            $.ajax({
                type: "POST",
                url: "{{url('admin/get_delete_jobproduct')}}",
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    window.location.reload();
                }
            });
        }
    }

    function get_customer_details() {
        var customer_id = $("#customer_id").val();
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('admin/get_customer_details')}}",
            data: {
                customer_id: customer_id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                $('#project_id').removeAttr('disabled');
                $('#contact_id').removeAttr('disabled');
                $('#site_id').removeAttr('disabled');
                data.forEach(customerData => {
                    $("#name").val(customerData.name);
                    $("#email").val(customerData.email);
                    $("#telephone").val(customerData.telephone);
                    $("#mobile").val(customerData.mobile);
                    $("#address").val(customerData.address);
                    $("#city").val(customerData.city);
                    $("#country").val(customerData.country);
                    $("#pincode").val(customerData.postal_code);
                    $("#conatact_name").val(customerData.contact_name);
                    $("#site_email").val(customerData.email);
                    $("#site_telephone").val(customerData.telephone);
                    $("#site_mobile").val(customerData.mobile);
                    $("#site_address").val(customerData.address);
                    $("#site_city").val(customerData.city);
                    $("#site_country").val(customerData.country);
                    $("#site_pincode").val(customerData.postal_code);
                    $("#company_id").val(customerData.name)
                    var project = '<option value="0" selected>Select Project</option>';
                    if (customerData.customer_project && Array.isArray(customerData.customer_project)) {
                        for (let i = 0; i < customerData.customer_project.length; i++) {
                            project += '<option value="' + customerData.customer_project[i].id + '">' + customerData.customer_project[i].project_name + '</option>';
                        }
                    }
                    document.getElementById('project_id').innerHTML = project;

                    var contact = '<option value="0" selected>Default</option>';
                    if (customerData.additional_contact && Array.isArray(customerData.additional_contact)) {
                        for (let i = 0; i < customerData.additional_contact.length; i++) {
                            contact += '<option value="' + customerData.additional_contact[i].id + '">' + customerData.additional_contact[i].contact_name + '</option>';
                        }
                    }
                    document.getElementById('contact_id').innerHTML = contact;

                    var site = '<option value="default" selected>Select Site</option>';
                    if (customerData.sites && Array.isArray(customerData.sites)) {
                        for (let i = 0; i < customerData.sites.length; i++) {
                            site += '<option value="' + customerData.sites[i].id + '">' + customerData.sites[i].site_name + '</option>';
                        }
                    }
                    document.getElementById('site_id').innerHTML = site;
                    // var country_code_selected = document.querySelectorAll(".country_code");
                    $(".country_code").each(function() {
                        if ($(this).val() === customerData.country_code) {
                            $(this).prop('selected', true);
                        }
                    });
                    $(".site_country_code").each(function() {
                        if ($(this).val() === customerData.country_code) {
                            $(this).prop('selected', true);
                        }
                    });
                    // $(".country_code.site_country_code").each(function() {
                    //     if ($(this).val() === customerData.country_code) {
                    //         $(this).prop('selected', true);
                    //     }
                    // });
                    // console.log(country_code_selected);
                });



            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }
</script>

@endsection