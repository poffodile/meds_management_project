@extends('backEnd.layouts.master')

@section('title',' : User Form')

@section('content')

<style type="text/css">
    .position-center label {
        font-size: 20px;
        font-weight: 500;
    }

    .hover {
        cursor: pointer;
    }

    .custom-fieldset {
        position: relative;
        border: 2px solid #00000026;
        padding: 10px;
        margin-top: 20px;
        margin: 10px;
    }

    .custom-legend {
        position: absolute;
        top: -10px;
        left: 20px;
        background-color: white;
        font-weight: bold;
        padding: 0 10px;
    }

    .headBgColor {
        background-color: #1fb5ad;
    }

    .modal-header.headBgColor h4 {
        font-size: 16px;
        font-weight: 600;
        color: #fff;
    }

    /* .modal-body .row {
        margin-bottom: 1rem;
    } */
</style>

<section id="main-content">
    <div class="wrapper">

        <!-- page start-->
        <section class="panel">
            <header class="panel-heading">
                {{$task}} Purchase Order
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
                <p>The Purchase Order has been saved Successfully</p>
            </div>
            <div class="panel-body">
                <!-- page start-->
                <form class="form-horizontal" role="form" enctype="multipart/form-data" id="form_data">
                    @csrf
                    <div class="from_outside_border">
                        <div class="row">
                            <div class="col-md-4">
                                <div>
                                    <h4 class="contTitle">Supplier Details</h4>
                                    <div class="row form-group">
                                        <label for="Supplier" class="col-lg-4 col-sm-4 control-label">Supplier</label>
                                        <div class="col-lg-7">
                                            <select name="Supplier" id="Supplier" class="form-control">
                                                <option selected disabled>Select Supplier</option>
                                                <option value="">Abhishek</option>
                                                <option value="">Manisha</option>
                                                <option value="">Mahima</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(1)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Contact" class="col-lg-4 col-sm-4 control-label">Contact</label>
                                        <div class="col-lg-7">
                                            <select class="form-control get_customer_result" name="Contact" id="Contact">
                                                <option selected disabled>Default</option>
                                                <option value="">Supplier</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(2)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Name_input" class="col-lg-4 col-sm-4 control-label">Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Name_input" name="Name_input" placeholder="Swapnil" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputAddress" class="col-lg-4 col-sm-4 control-label">Address</label>
                                        <div class="col-lg-8">
                                            <textarea class="form-control textareaInput" name="inputAddress" id="inputAddress" rows="3" cols="70"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="City" class="col-lg-4 col-sm-4 control-label">City</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="City" name="City" placeholder="City" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="County" class="col-lg-4 col-sm-4 control-label">County</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="County" name="County" placeholder="County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Postcode" class="col-lg-4 col-sm-4 control-label">Postcode</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Postcode" name="Postcode" placeholder="Postcode" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="telephone" class="col-lg-4 col-sm-4 control-label">Telephone</label>
                                        <div class="col-lg-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="telephone_country_code" name="telephone_country_code">
                                                <option selected disabled>Please Select</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Telephone" value="" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="mobile" class="col-lg-4 col-sm-4 control-label">Mobile</label>
                                        <div class="col-sm-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="mobile_country_code" name="mobile_country_code">
                                                <option selected disabled>Please Select</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Mobile" value="" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Email" class="col-lg-4 col-sm-4 control-label">Email</label>
                                        <div class="col-lg-8">
                                            <input type="email" class="form-control" id="Email" name="Email" placeholder="Email" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <h4 class="contTitle">Customer / Delivery Details</h4>
                                    <div class="row form-group">
                                        <label for="Supplier" class="col-lg-4 col-sm-4 control-label">Customer</label>
                                        <div class="col-lg-7">
                                            <select name="Supplier" id="Supplier" class="form-control ">
                                                <option selected disabled>Select Supplier</option>
                                                <option value="">Abhishek</option>
                                                <option value="">Manisha</option>
                                                <option value="">Mahima</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(3)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Project" class="col-lg-4 col-sm-4 control-label">Project</label>
                                        <div class="col-lg-7">
                                            <select name="Project" id="Project" class="form-control ">
                                                <option selected disabled></option>
                                                <option value="">Abhishek</option>
                                                <option value="">Manisha</option>
                                                <option value="">Mahima</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(4)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Site" class="col-lg-4 col-sm-4 control-label">Site</label>
                                        <div class="col-lg-7">
                                            <select name="Site" id="Site" class="form-control ">
                                                <option selected disabled>Same as Customer</option>
                                                <option value="">Abhishek</option>
                                                <option value="">Manisha</option>
                                                <option value="">Mahima</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(5)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="name" class="col-lg-4 col-sm-4 control-label">Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Company" class="col-lg-4 col-sm-4 control-label">Company</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Company" name="Company" placeholder="County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputAddress" class="col-lg-4 col-sm-4 control-label">Address</label>
                                        <div class="col-lg-8">
                                            <textarea class="form-control textareaInput" name="inputAddress" id="inputAddress" rows="3" cols="70"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="City" class="col-lg-4 col-sm-4 control-label">City</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="City" name="City" placeholder="City" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="County" class="col-lg-4 col-sm-4 control-label">County</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="County" name="County" placeholder="County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Postcode" class="col-lg-4 col-sm-4 control-label">Postcode</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Postcode" name="Postcode" placeholder="Postcode" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="telephone" class="col-lg-4 col-sm-4 control-label">Telephone</label>
                                        <div class="col-lg-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="telephone_country_code" name="telephone_country_code">
                                                <option selected disabled>Please Select</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Telephone" value="" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="mobile" class="col-lg-4 col-sm-4 control-label">Mobile</label>
                                        <div class="col-sm-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="mobile_country_code" name="mobile_country_code">
                                                <option selected disabled>Please Select</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                                <option value="">+91 India</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" id="mobile" name="mobile" placeholder="Mobile" value="" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Expected" class="col-lg-4 col-sm-4 control-label">Expected Delivery On</label>
                                        <div class="col-lg-8">
                                            <input type="date" class="form-control" id="Expected" name="Expected" placeholder="" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="">
                                    <h4 class="contTitle">Purchase Order Details</h4>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-6 col-sm-6 control-label">Purchase Order Ref.</label>
                                        <div class="col-lg-5">
                                            <span>Auto generate</span>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Department</label>
                                        <div class="col-lg-7">
                                            <select name="Department" id="Department" class="form-control ">
                                                <option selected disabled>Please Select </option>
                                                <option value="">Management Department</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(6)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Purchase-date" class="col-lg-4 col-sm-4 control-label">Purchase Date</label>
                                        <div class="col-lg-8">
                                            <input type="date" class="form-control" id="Purchase-date" name="Purchase-date" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Reference" class="col-lg-4 col-sm-4 control-label">Reference</label>
                                        <div class="col-lg-8">
                                            <input type="text" name="Reference" id="Reference" class="form-control" placeholder="Reference (if any)">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Quote_ref" class="col-lg-4 col-sm-4 control-label">Quote Ref.</label>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="Quote_ref" name="Quote_ref" placeholder="Quote Ref (if any)" value="">
                                        </div>
                                        <div class="col-lg-1 icon_blue">
                                            <a href="javascript:void(0)" onclick="get_modal(19)"><i class="fa fa-search"></i></a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Job_reg" class="col-lg-4 col-sm-4 control-label">Job Reg</label>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="Job_reg" name="Job_reg" placeholder="Job Reg (if any)" value="">
                                        </div>
                                        <div class="col-lg-1 icon_blue">
                                            <a href="javascript:void(0)" onclick="get_modal(19)"><i class="fa fa-search"></i></a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="vat_tax_no" class="col-lg-4 col-sm-4 control-label">Invoice Ref</label>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" id="vat_tax_no" name="vat_tax_no" placeholder="Invoice Ref (if any)" value="">
                                        </div>
                                        <div class="col-lg-1 icon_blue">
                                            <a href="javascript:void(0)" onclick="get_modal(19)"><i class="fa fa-search"></i></a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="payment_terms" class="col-lg-4 col-sm-4 control-label">Payment Terms</label>
                                        <div class="col-lg-5">
                                            <select class="form-control editInput selectOptions" id="payment_terms" name="payment_terms">
                                                <option value="">0</option>
                                                <option value="">1</option>
                                                <option value="">2</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2">
                                            <span class="afterInputText">Days</span>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Payment-due-date" class="col-lg-4 col-sm-4 control-label">Payment Due Date</label>
                                        <div class="col-lg-8">
                                            <input type="date" class="form-control" id="Payment-due-date" name="Payment-due-date" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-4 col-sm-4 control-label">Status</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Tags</label>
                                        <div class="col-lg-7">
                                            <select name="Tags" id="Tags" class="form-control ">
                                                <option selected disabled>None</option>
                                                <option value="">#Ram</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(7)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-primary" onclick="get_modal(14)"><i class="fa fa-clock-o"></i> Set Reminder</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End off from_outside_border -->
                </form>
                <!-- </form> -->

                <div class="from_outside_border mrg_tp">
                    <label class="upperlineTitle">Product Details</label>
                    <div class="row">
                        <div class="form-group padd0">
                            <div class="col-sm-12 for_spacing_b">
                                <div class="row d-flex align-items-center">
                                    <label for="" class="col-lg-2">Select product</label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control" placeholder="Type to add product">
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-center gap-3">
                                            <a class="icon_blue" onclick="get_modal(8)"><i class="fa fa-plus-circle"></i></a>
                                            <span class="afterPlusText"> (Type to view product or <a class="javascript:void(0)" onclick="get_modal(20)">Click here</a> to view all assets)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="padd0">
                            <table class="table">
                                <thead>
                                    <tr class="active">
                                        <th>Job</th>
                                        <th>Product</th>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>
                                            <div class="d-flex align-items-center gap-2">
                                                Account Code <a onclick="get_modal(9)" class="icon_blue"><i class="fa fa-plus-circle"></i></a>
                                            </div>
                                        </th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>
                                            <div class="d-flex align-items-center gap-2">
                                                Price VAT% <a onclick="get_modal(10)" class="icon_blue"><i class="fa fa-plus-circle"></i></a>
                                            </div>
                                        </th>
                                        <th>VAT</th>
                                        <th>Amount</th>
                                        <th>Delivered QTY</th>
                                        <th>Quantity Available</th>
                                    </tr>
                                </thead>
                                <tbody id="contact_result">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="from_outside_border mrg_tp">
                    <label class="upperlineTitle">Notes</label>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle">Supplier Notes</h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="purchase_supplier_notes" name="supplier_notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle">Delivery Notes</h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="purchase_delivery_notes" name="delivery_notes"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle">Internal Notes</h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="purchase_internal_notes" name="internal_notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="from_outside_border mrg_tp">
                    <label class="upperlineTitle">Attachments</label>
                    <div class="row">
                        <div class="col-md-12 jobsection">
                            <a class="btn btn-primary" onclick="get_modal(16)">New Attachments</a>
                        </div>
                    </div>
                </div>

                <div class="from_outside_border mrg_tp">
                    <label class="upperlineTitle">Tasks</label>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="jobsection">
                                <a href="javascript:void(0)" class="btn btn-primary" onclick="get_modal(17)">New Task</a>
                            </div>
                        </div>
                        <div class="col-sm-12 padd0">
                            <div class="tab-container Task_tabs">
                                <div class="tab-menu">
                                    <ul>
                                        <li><a href="javascript:void(0)" class="tab-a active-a" data-id="Tasks" id="task_active_inactive">Tasks</a></li>
                                        <li><a href="javascript:void(0)" class="tab-a" id="recurring_active_inactive" data-id="Recurring_task">Recurring Tasks</a></li>
                                    </ul>
                                </div>
                                <!-- tab 1 Start -->
                                <div class="tab tab-active" data-id="Tasks">
                                    <div class="jobsection">
                                        <table class="table">
                                            <thead>
                                                <tr class="active">
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
                                            <tbody id="newtask_result"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- tab 2 Start -->
                                <div class="tab" data-id="Recurring_task">
                                    <div class="jobsection">
                                        <table class="table">
                                            <thead>
                                                <tr class="active">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pddtp d-flex justify-content-end gap-3 padd0">
                    <button type="button" class="btn btn-primary" onclick="get_data()" id="submit_btnMain">
                        <i class="fa fa-floppy-o"></i> Save</button>
                    <button type="button" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back</button>
                    <button type="button" class="btn btn-primary"><i class="fa fa-chevron-down"></i> Action </button>
                </div><!-- add action dropdown  -->
            </div>
        </section>
        <!-- page end-->

        <!-- New Supplier Modal start (modal id = 1) -->
        <div class="modal fade in" id="Add_Supplier_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">New Supplier </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Supplier_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="Supplier_input" class="col-lg-4 col-sm-4 control-label">Supplier Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Supplier_input" name="Supplier_input" placeholder="Enter Supplier Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="SupplierCode_input" class="col-lg-4 col-sm-4 control-label">Supplier Code</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="SupplierCode_input" name="SupplierCode_input" placeholder="Enter Supplier Code" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="ContactName_input" class="col-lg-4 col-sm-4 control-label">Contact Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="ContactName_input" name="ContactName_input" placeholder="Enter Contact Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="email_input" class="col-lg-3 col-sm-3 control-label">Email</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="email_input" name="email_input" placeholder="Enter Supplier email" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="fax_input" class="col-lg-3 col-sm-3 control-label">Fax</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="fax_input" name="fax_input" placeholder="Enter Supplier Fax" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Telephone</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_telephone_country_code" name="contact_telephone_country_code">
                                                <option selected disabled>Please Select</option>

                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_telephone" name="customer_telephone" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Mobile</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_mobile_country_code" name="contact_mobile_country_code">
                                                <option selected disabled>Please Select</option>

                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <label for="supplier_website" class="col-sm-3 col-form-label">Website</label>
                                        <div class="col-sm-2 pe-0">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">http://</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Website" id="supplier_website" name="website">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_status" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_status" name="status">
                                                <option selected disabled>Please Select</option>
                                                <option value='1'>Active</option>
                                                <option value='0'>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="address_input" class="col-lg-3 col-sm-3 control-label">Address</label>
                                        <div class="col-lg-9">
                                            <textarea name="address_input" id="address_input" class="form-control" placeholder="Enter Supplier Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="city_input" class="col-lg-3 col-sm-3 control-label">City</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="city_input" name="city_input" placeholder="Enter Supplier City" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="County_input" class="col-lg-3 col-sm-3 control-label">County</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="County_input" name="County_input" placeholder="Enter Supplier County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Postcode_input" class="col-lg-3 col-sm-3 control-label">Postcode</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Postcode_input" name="Postcode_input" placeholder="Enter Supplier Postcode" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_country_id"
                                            class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_country_id" name="country_id">
                                                <option selected disabled>Select Country</option>
                                                <option value="">India</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_account_ref" class="col-sm-3 col-form-label">Account Ref.</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Account Ref." id="supplier_account_ref" name="account_ref">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_currency_id" class="col-sm-3 col-form-label">Currency</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_currency_id" name="currency_id">
                                                <option value="" selected>British Pound</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_creadit_limit" class="col-sm-3 col-form-label">Credit Limit</label>
                                        <div class="col-sm-2 pe-0">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">£</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Credit Limit" name="creadit_limit" id="supplier_creadit_limit" value="" maxlength="8">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-12 col-xl-12">
                                    <div class="mb-2 notes_input">
                                        <label for="supplier_note" class="col-form-label">Notes</label>
                                        <textarea class="form-control textareaInput" placeholder="Enter Supplier Notes" rows="3" id="supplier_note" name="notes"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Supplier Contact modal start  (modal id = 2)-->
        <div class="modal fade in" id="Add_Supplier_Contact_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">Add Supplier Contact</h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Supplier_Contact_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="Supplier_input" class="col-lg-4 col-sm-4 control-label">Supplier</label>
                                        <div class="col-lg-8">
                                            <input type="text" readonly class="border-0" name="Supplier_input" value="Abhishek">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="ContactName_input" class="col-lg-4 col-sm-4 control-label">Contact Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="ContactName_input" name="ContactName_input" placeholder="Enter Contact Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Supplier" class="col-lg-4 col-sm-4 control-label">Job Title (Position)</label>
                                        <div class="col-lg-7">
                                            <select name="Supplier" id="Supplier" class="form-control">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Python Developer</option>
                                                <option value="">iOS Developer</option>
                                                <option value="">Php Developer</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(11)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="email_input" class="col-lg-3 col-sm-3 control-label">Email</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="email_input" name="email_input" placeholder="Enter Supplier email" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="fax_input" class="col-lg-3 col-sm-3 control-label">Fax</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="fax_input" name="fax_input" placeholder="Enter Supplier Fax" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Telephone</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_telephone_country_code" name="contact_telephone_country_code">
                                                <option selected disabled>Please Select</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_telephone" name="customer_telephone" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Mobile</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_mobile_country_code" name="contact_mobile_country_code">
                                                <option selected disabled>Please Select</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="address_input" class="col-lg-4 col-sm-4 control-label">Address Details</label>
                                        <div class="col-lg-8">
                                            <label for="defaltbtn"> same as default</label>
                                            <input type="checkbox" id="defaltbtn" name="defaltbtn">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="address_input" class="col-lg-3 col-sm-3 control-label">Address</label>
                                        <div class="col-lg-9">
                                            <textarea name="address_input" id="address_input" class="form-control" placeholder="Enter Supplier Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="city_input" class="col-lg-3 col-sm-3 control-label">City</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="city_input" name="city_input" placeholder="Enter Supplier City" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="County_input" class="col-lg-3 col-sm-3 control-label">County</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="County_input" name="County_input" placeholder="Enter Supplier County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Postcode_input" class="col-lg-3 col-sm-3 control-label">Postcode</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Postcode_input" name="Postcode_input" placeholder="Enter Supplier Postcode" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_country_id"
                                            class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_country_id" name="country_id">
                                                <option selected disabled>Select Country</option>
                                                <option value="">India</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Customer Modal Start (modal id = 3) -->
        <div class="modal fade in" id="Add_Customer_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Customer </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Customer_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="Customer_Name" class="col-lg-3 col-sm-3 control-label">Customer Name</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Customer_Name" name="Customer Name" placeholder="Enter Customer Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Customer_Type" class="col-lg-3 col-sm-3 control-label">Customer Type</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Customer_Type" name="Customer_Type" placeholder="Enter Customer Type" value="">
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(12)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="ContactName_input" class="col-lg-3 col-sm-3 control-label">Contact Name</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="ContactName_input" name="ContactName_input" placeholder="Enter Contact Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Job Title (Position)</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>Please Select</option>
                                                <option value="">Python Developer</option>
                                                <option value="">Python Developer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="email_input" class="col-lg-3 col-sm-3 control-label">Email</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="email_input" name="email_input" placeholder="Enter Supplier email" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="fax_input" class="col-lg-3 col-sm-3 control-label">Fax</label>
                                        <div class="col-lg-9">
                                            <input type="email" class="form-control" id="fax_input" name="fax_input" placeholder="Enter Supplier Fax" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Telephone</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_telephone_country_code" name="contact_telephone_country_code">
                                                <option selected disabled>Please Select</option>

                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_telephone" name="customer_telephone" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Mobile</label>
                                        <div class="col-sm-2 pe-0">
                                            <select class="form-control editInput selectOptions" id="contact_mobile_country_code" name="contact_mobile_country_code">
                                                <option selected disabled>Please Select</option>

                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <label for="supplier_website" class="col-sm-3 col-form-label">Website</label>
                                        <div class="col-sm-2 pe-0">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">http://</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Website" id="supplier_website" name="website">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="payment_terms" class="col-lg-3 col-sm-3 control-label">Payment Terms</label>
                                        <div class="col-lg-6">
                                            <select class="form-control editInput selectOptions" id="payment_terms" name="payment_terms">
                                                <option value="">0</option>
                                                <option value="">1</option>
                                                <option value="">2</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-2">
                                            <span class="afterInputText">Days</span>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_currency_id" class="col-sm-3 col-form-label">Currency</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_currency_id" name="currency_id">
                                                <option value="" selected>British Pound</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_creadit_limit" class="col-sm-3 col-form-label">Credit Limit</label>
                                        <div class="col-sm-2 pe-0">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">£</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Credit Limit" name="creadit_limit" id="supplier_creadit_limit" value="" maxlength="8">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Discount_input" class="col-lg-3 col-sm-3 control-label">Discount</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Discount_input" name="Discount_input" placeholder="Enter Discount" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="DiscountType_status" class="col-sm-3 col-form-label">Discount Type</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="DiscountType_status" name="status">
                                                <option selected disabled>Please Select</option>
                                                <option value='0'>Percentage</option>
                                                <option value='0'>Flat</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="VAT_input" class="col-lg-3 col-sm-3 control-label">VAT / Tax No</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="VAT_input" name="VAT_input" placeholder="Enter VAT/TAX No." value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Default_Catalogue" class="col-sm-3 col-form-label">Default Catalogue</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="Default_Catalogue" name="status">
                                                <option selected disabled>None</option>
                                                <option value='0'>ABCD</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_status" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_status" name="status">
                                                <option selected disabled>Please Select</option>
                                                <option value='1'>Active</option>
                                                <option value='0'>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="Region_input" class="col-lg-3 col-sm-3 control-label">Region</label>
                                        <div class="col-lg-8">
                                            <select name="" id="" class="form-control">
                                                <option selected disabled>None</option>
                                                <option value="">USA</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(13)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="address_input" class="col-lg-3 col-sm-3 control-label">Address</label>
                                        <div class="col-lg-9">
                                            <textarea name="address_input" id="address_input" class="form-control" placeholder="Enter Supplier Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="city_input" class="col-lg-3 col-sm-3 control-label">City</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="city_input" name="city_input" placeholder="Enter Supplier City" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="County_input" class="col-lg-3 col-sm-3 control-label">County</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="County_input" name="County_input" placeholder="Enter Supplier County" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Pincode_input" class="col-lg-3 col-sm-3 control-label">Pincode</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Pincode_input" name="Pincode_input" placeholder="Enter Supplier Pincode" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_country_id"
                                            class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="supplier_country_id" name="country_id">
                                                <option selected disabled>Select Country</option>
                                                <option value="">India</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Site_Notes_input" class="col-lg-3 col-sm-3 control-label">Site Notes</label>
                                        <div class="col-lg-9">
                                            <textarea name="Site_Notes_input" id="Site_Notes_input" class="form-control" placeholder="Enter Site Notes"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Sage_input" class="col-lg-3 col-sm-3 control-label">Sage Ref.</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Sage_input" name="Sage_input" placeholder="Enter Sage Ref." value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Assign_productt_limit" class="col-sm-4 ol-form-label">Assign Products</label>
                                        <div class="col-sm-8 d-flex align-items-center gap-3">
                                            <input type="radio" id="Assign_yes" name="Assign_Products">
                                            <label for="Assign_yes" class="mb-0">Yes</label>
                                            <input type="radio" id="Assign_no" name="Assign_Products">
                                            <label for="Assign_no" class="mb-0">No</label>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="supplier_note" class="col-sm-3 col-form-label">Notes</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" placeholder="Enter Supplier Notes" rows="3" id="supplier_note" name="notes"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Project modal start here (modal id = 4) -->
        <div class="modal fade in" id="Add_Project_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Project </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Project_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Project Ref</label>
                                        <div class="col-lg-9">
                                            <input type="text" readonly class="border-0" value="Project Ref ###">
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Customer</label>
                                        <div class="col-lg-9">
                                            <input type="text" readonly class="border-0" value="Customer">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Project Name</label>
                                        <div class="col-lg-9">
                                            <input type="text" name="customer_name" id="customer_name" placeholder="Enter Project Name" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Start_date" class="col-lg-3 col-sm-3 control-label">Start Date</label>
                                        <div class="col-lg-9">
                                            <input type="date" class="form-control" id="Start_date" name="Start_date" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="End_date" class="col-lg-3 col-sm-3 control-label">End Date</label>
                                        <div class="col-lg-9">
                                            <input type="date" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Project_value" class="col-sm-3 col-form-label">Project Value</label>
                                        <div class="col-sm-2 pe-0">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">£</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Project Value" id="Project_value" value="" maxlength="8">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Description</label>
                                        <div class="col-lg-9">
                                            <textarea name="site_address" placeholder="Enter Project Description" class="form-control" id="site_address" rows="3" cols="6"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Site Address modal start here (modal id = 5) -->
        <div class="modal fade in" id="Add_Site_Address_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Site Address </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Site_Address_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- <div class="custom-legend"><strong>Customer Site</strong></div> -->
                                    <input type="hidden" value="" name="site_id" id="site_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Customer</label>
                                                <div class="col-lg-9">
                                                    <input type="text" readonly class="border-0" value="Customer">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Site Name <span class="radStar ">*</span></label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="site_name" id="site_name" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-4 control-label">Contact Name <span class="radStar ">*</span></label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="site_contact_name" id="site_contact_name" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label for="Supplier" class="col-lg-4 col-sm-4 control-label">Job Title (Position)</label>
                                                <div class="col-lg-7">
                                                    <select name="Supplier" id="Supplier" class="form-control">
                                                        <option selected disabled>Please Select</option>
                                                        <option value="">Python Developer</option>
                                                        <option value="">iOS Developer</option>
                                                        <option value="">Php Developer</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-1" id="inputPlusCircle">
                                                    <a class="javascript:void(0)" onclick="get_modal(11)"><i class="fa  fa-plus-circle"></i> </a>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Company Name</label>
                                                <div class="col-lg-9">
                                                    <input type="text" name="company_name" id="company_name" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Email</label>
                                                <div class="col-lg-9">
                                                    <input type="email" name="site_email" id="site_email" class="form-control" onblur="getemail(3)">
                                                    <span id="emailErr3" style="color: red;"></span>
                                                </div>
                                            </div>

                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Telephone</label>
                                                <div class="col-sm-2 pe-0">
                                                    <select class="form-control editInput selectOptions" id="contact_telephone_country_code" name="contact_telephone_country_code">
                                                        <option selected disabled>Please Select</option>

                                                    </select>
                                                </div>
                                                <div class="col-lg-7">
                                                    <input type="text" id="customer_telephone" name="customer_telephone" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Mobile</label>
                                                <div class="col-sm-2 pe-0">
                                                    <select class="form-control editInput selectOptions" id="contact_mobile_country_code" name="contact_mobile_country_code">
                                                        <option selected disabled>Please Select</option>

                                                    </select>
                                                </div>
                                                <div class="col-lg-7">
                                                    <input type="text" id="customer_mobile" name="customer_mobile" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label for="fax_input" class="col-lg-3 col-sm-3 control-label">Fax</label>
                                                <div class="col-lg-9">
                                                    <input type="email" class="form-control" id="fax_input" name="fax_input" placeholder="Enter Site Fax" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row form-group">
                                                <label for="Region_input" class="col-lg-3 col-sm-3 control-label">Region</label>
                                                <div class="col-lg-8">
                                                    <select name="" id="" class="form-control">
                                                        <option selected disabled>None</option>
                                                        <option value="">USA</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-1" id="inputPlusCircle">
                                                    <a class="javascript:void(0)" onclick="get_modal(13)"><i class="fa  fa-plus-circle"></i> </a>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Address <span class="radStar ">*</span></label>
                                                <div class="col-lg-9">
                                                    <textarea name="site_address" class="form-control" id="site_address" rows="3" cols="6"></textarea>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">City</label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="site_city" name="site_city" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="site_country" name="site_country" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Post Code</label>
                                                <div class="col-lg-9">
                                                    <input type="text" id="site_post_code" name="site_post_code" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Country</label>
                                                <div class="col-lg-9">
                                                    <select id="site_country_id" name="site_country_id" class="form-control">
                                                        <option selected disabled>Select Country</option>

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row form-group">
                                                <label class="col-lg-3 control-label">Default Catalogue</label>
                                                <div class="col-lg-9">
                                                    <select id="site_catalogue" name="site_catalogue" class="form-control">
                                                        <option selected disabled>Select Catalogue</option>
                                                        <option value="1">General</option>
                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-md-12 col-lg-12 col-xl-12">
                                            <div class="mb-2 notes_input">
                                                <label for="site_note" class="col-form-label">Notes</label>
                                                <textarea class="form-control textareaInput" placeholder="Enter Sites Notes" rows="3" id="site_note" name="notes"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Department add Modal start here (modal id = 6) -->
        <div class="modal fade in" id="Department_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Department - Add </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Department_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Department</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Department_input" name="Department_input" placeholder="Enter Department" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Tags Modal start here  (modal id = 7) -->
        <div class="modal fade in" id="Add_Tags_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Tag </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Tags_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Tag</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Tag_input" name="Tag_input" placeholder="Enter Tag" value="">
                                            <b class="text-danger">Note: Comma not allowed in the tag. The previous name will not be populated by the rename tag.</b>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Product Modal start here  (modal id = 8) -->
        <div class="modal fade in" id="Product_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">Product</h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Product_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Customer</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>- All -</option>
                                                <option value="">Riya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Product Category</label>
                                        <div class="col-lg-7">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Any Categores -</option>
                                                <option value="">Green</option>
                                                <option value="">yellow</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(15)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label"> Product Name</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="product_name_input" name="product_name_input" placeholder="Enter Product Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Product Type</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Product</option>
                                                <option value="">Green</option>
                                                <option value="">yellow</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Product Code</label>
                                        <div class="col-lg-5">
                                            <input type="text" class="form-control" id="Product_code" name="Product_code" placeholder="Enter Product Code" value="">
                                        </div>
                                        <div class="col-lg-3">
                                            <button type="button" class="btn btn-primary">Generate</button>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Cost Price</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="cost_price" name="cost_price" placeholder="Enter Cost Price" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Markup</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Markup" name="Markup" placeholder="Enter Markup" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Price</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Price" name="Price" placeholder="Enter Price" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Description</label>
                                        <div class="col-lg-8">
                                            <textarea name="Description" id="Description" placeholder="Description" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Show on Template</label>
                                        <div class="col-lg-7">
                                            <label class="togglebtn">
                                                <input class="toggle-checkbox" type="checkbox">
                                                <div class="toggle-switch"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Bar Code</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Bar_Code" name="Bar_Code" placeholder="Enter Bar Code" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Sales Tax Rate</label>
                                        <div class="col-lg-7">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Please Select -</option>
                                                <option value="">VAT 5</option>
                                                <option value="">VAT 20</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(10)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Purchase Tax Rate</label>
                                        <div class="col-lg-7">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Please Select -</option>
                                                <option value="">Green</option>
                                                <option value="">yellow</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-1" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal(10)"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Nominal Code</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Nominal_Code" name="Nominal_Code" placeholder="Enter Nominal Code" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Sales A/c Code</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Please Select -</option>
                                                <option value="">test 1</option>
                                                <option value="">test 1 </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Purchase A/c Code</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Please Select -</option>
                                                <option value="">test 1</option>
                                                <option value="">test 1 </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Expense A/c Code</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option disabled selected>- Please Select -</option>
                                                <option value="">test 1</option>
                                                <option value="">test 1 </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-4 control-label">Location</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="Location" name="Location" placeholder="Enter Location" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-4 col-sm-4 control-label">Status</label>
                                        <div class="col-lg-8">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="Attachment" class="col-lg-4 col-sm-4 control-label">Attachment</label>
                                        <div class="col-lg-8">
                                            <input type="file" id="Attachment" name="Attachment" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add department code Modal start here (modal id = 9) -->
        <div class="modal fade in" id="Departmental_Code_Add_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Departmental Code - Add </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Departmental_Code_Add_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Name</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Name" name="Name" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Departmental Code</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Departmental_Code" name="Departmental_Code" placeholder="" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Tax Rate start here (modal id = 10) -->
        <div class="modal fade in" id="Add_Tax_Rate_modal" tabindex="-1"
            role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Tax Rate </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Tax_Rate_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Tax Rate Name</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Tag_input" name="Tag_input" placeholder="Tax Rate Name" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Tax Rate</label>
                                        <div class="col-lg-7 pe-0">
                                            <input type="text" class="form-control" id="TagRate_input" name="TagRate_input" placeholder="Tax Rate" value="">
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="tag_box text-center">
                                                <span style="padding:7px">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">External Tax Code</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="External_tax-input" name="External_tax-input" placeholder="External Tax Rate" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Expiry Date</label>
                                        <div class="col-lg-9">
                                            <input type="date" class="form-control" id="Expiry_input" name="Tag_input">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 pddtp">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="save_customer_type()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- job title add Modal start here (modal id = 11) -->
        <div class="modal fade in" id="Job_Title_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Job Title - Add </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Job_Title_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Job Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Job_Title_input" name="Job_Title_input" placeholder="Enter Job Title" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Customer Type Modal start here (modal id = 12) -->
        <div class="modal fade in" id="add_Customer_Type_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Customer Type </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="add_Customer_Type_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Customer Type</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="add_Customer_Type_input" name="add_Customer_Type_input" placeholder="Enter Customer Type" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- add region Modal start here (modal id = 13) -->
        <div class="modal fade in" id="add_region_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Region </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="add_region_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Region</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="add_region_input" name="add_region_input" placeholder="Enter Region" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- PO Reminder Modal start here  (modal id = 14) -->
        <div class="modal fade in" id="PO_Reminder_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> PO Reminder </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="PO_Reminder_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label for="Expected" class="col-lg-3 col-sm-3 control-label">Reminder Date</label>
                                        <div class="col-lg-6">
                                            <input type="date" class="form-control" id="Expected" name="Expected" placeholder="" value="">
                                        </div>
                                        <!-- <div class="col-lg-1 icon_blue"><i class="fa fa-calendar text-danger"></i></div> -->
                                        <div class="col-lg-3">
                                            <input type="time" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Reminder Email</label>
                                        <div class="col-md-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">test</option>
                                                <option value="">test</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Send As</label>
                                        <div class="col-lg-9">
                                            <input type="checkbox" id="Notification">
                                            <label for="Notification">Notification</label>&nbsp;
                                            <input type="checkbox" id="SMS">
                                            <label for="SMS">SMS</label>&nbsp;
                                            <input type="checkbox" id="Email">
                                            <label for="Email">Email</label>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Notes</label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" placeholder="Description" rows="4"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Product Category  Modal start here  (modal id = 15)-->
        <div class="modal fade in" id="Product_Category_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Product Category </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Product_Category_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Product Category</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="Product_Category_input" name="Product_Category_input" placeholder="Enter Product Category" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Parent Category</label>
                                        <div class="col-lg-9">
                                            <select name="Parent_Category" id="Parent_Category" class="form-control">
                                                <option selected disabled>Please Select</option>
                                                <option value="">test</option>
                                                <option value="">test</option>
                                                <option value="">test</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Add Attachment  Modal start here (modal id = 16) -->
        <div class="modal fade in" id="Add_Attachment_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Add Attachment </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Add_Attachment_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Purchase Ref</label>
                                        <div class="col-lg-9">
                                            <input type="text" readonly class="border-0" name="Supplier_input" value="PO-002">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Type</label>
                                        <div class="col-lg-9">
                                            <select name="Parent_Category" id="Parent_Category" class="form-control">
                                                <option selected disabled>Please Select</option>
                                                <option value="">test</option>
                                                <option value="">test</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">File Name</label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control">
                                            <p>(Max File size 25 MB)</p>
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Title</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="Title">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Description (Max 500 Characters)</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="Description">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!--New Task  Modal start here (modal id = 17) -->
        <div class="modal fade in" id="New_Task_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">New Task </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="New_Task_form">
                            @csrf
                            <div>
                                <div class="tab-container">
                                    <div class="tab-menu">
                                        <ul>
                                            <li><a href="javascript:void(0)" class="tab-a active-a" data-id="tab1">Task</a></li>
                                            <li><a href="javascript:void(0)" class="tab-a" data-id="tab2">Timer</a></li>
                                        </ul>
                                    </div>
                                    <!-- tab 1 Start -->
                                    <div class="tab tab-active" data-id="tab1">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Supplier</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" readonly class="border-0" name="Supplier_input" value="Ram Kumar">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Task User</label>
                                                    <div class="col-lg-9">
                                                        <select name="Parent_Category" id="Parent_Category" class="form-control">
                                                            <option selected disabled>Please Select</option>
                                                            <option value="">Mick Carter</option>
                                                            <option value="">Mick Carter</option>
                                                            <option value="">Mick Carter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Title</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" name="Title_input" value="">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label for="Task_Type" class="col-lg-3 col-sm-3 control-label">Task Type</label>
                                                    <div class="col-lg-8">
                                                        <select name="" class="form-control" id="">
                                                            <option selected disabled>Please Select</option>
                                                            <option value="">test</option>
                                                            <option value="">test</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-1" id="inputPlusCircle">
                                                        <a class="javascript:void(0)" onclick="get_modal(18)"><i class="fa  fa-plus-circle"></i> </a>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label for="Start_date" class="col-lg-3 col-sm-3 control-label">Start Date</label>
                                                    <div class="col-lg-5">
                                                        <input type="date" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <input type="time" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label for="End_date" class="col-lg-3 col-sm-3 control-label">End Date</label>
                                                    <div class="col-lg-5">
                                                        <input type="date" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <input type="time" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label for="Recurring_task" class="col-lg-4 col-sm-4 control-label">Is Reccurring Task ?</label>
                                                    <input type="checkbox" name="Recurring_task" id="Recurring_task">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Notify ?</label>
                                                    <div class="col-lg-3 pe-0">
                                                        <input type="checkbox" name="Notify" id="Notify">
                                                        <label for="Notify">Yes, On</label>
                                                    </div>
                                                    <div class="col-lg-3 ps-0">
                                                        <input type="date" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                    <div class="col-lg-3 ps-0">
                                                        <input type="time" class="form-control" id="End_date" name="End_date" placeholder="" value="">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Related To</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" readonly class="border-0" name="Supplier_input" value="PO-002">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Notes</label>
                                                    <div class="col-lg-9">
                                                        <textarea name="" rows="4" id="" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 check_hide">
                                                <form>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="from_outside_border mrg_tp">
                                                                <label class="upperlineTitle">Recurrence Pattern</label>
                                                                <div class="check_hide_main">
                                                                    <div class="row form-group">
                                                                        <label class="col-lg-3 control-label">Create Task</label>
                                                                        <div class="col-lg-4">
                                                                            <select name="" id="" class="form-control">
                                                                                <option value="0">0</option>
                                                                                <option value="1">1</option>
                                                                                <option value="2">2</option>
                                                                            </select>
                                                                        </div>
                                                                        <label class="col-lg-3 control-label" for="">Days Before</label>
                                                                    </div>
                                                                    <div class="row form-group">
                                                                        <div class="col-md-4">
                                                                            <input type="radio" name="date" id="end_after">
                                                                            <label for="end_after">End After</label>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <input type="radio" name="date" id="end_by">
                                                                            <label for="end_by">End By</label>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <input type="radio" name="date" id="No_End_Date">
                                                                            <label for="No_End_Date">No End Date</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row form-group">
                                                                        <label class="col-lg-5 control-label">No. Of Repetitaion</label>
                                                                        <div class="col-lg-7 ps-0">
                                                                            <input type="text" class="form-control" name="Supplier_input" value="">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="from_outside_border mrg_tp">
                                                                <label class="upperlineTitle">Range Of Recurrence</label>
                                                                <div class="check_hide_main">
                                                                    <div class="row form-group">
                                                                        <label class="col-lg-4 control-label">Task Frequency</label>
                                                                        <div class="col-lg-8">
                                                                            <select name="" id="" class="form-control">
                                                                                <option value="">Daily</option>
                                                                                <option value="">Weekly</option>
                                                                                <option value="">Monthly</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row form-group">
                                                                        <div class="col-md-4 pe-0">
                                                                            <input type="radio" name="date" id="Every">
                                                                            <label for="Every">Every</label>
                                                                        </div>
                                                                        <div class="col-md-4 pe-0">
                                                                            <input type="text" class="form-control" id="days">
                                                                        </div>
                                                                        <label for="days" class="col-md-3">Days</label>
                                                                    </div>
                                                                    <div class="row form-group">
                                                                        <div class="col-md-8">
                                                                            <input type="radio" name="date" id="Every_Weekday">
                                                                            <label for="Every_Weekday">Every Weekday</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="noti_button">
                                                    <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                                    <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- tab 2 Start -->
                                    <div class="tab " data-id="tab2">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Task User</label>
                                                    <div class="col-lg-9">
                                                        <select name="Parent_Category" id="Parent_Category" class="form-control">
                                                            <option selected disabled>Please Select</option>
                                                            <option value="">Mick Carter</option>
                                                            <option value="">Mick Carter</option>
                                                            <option value="">Mick Carter</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Title</label>
                                                    <div class="col-lg-9">
                                                        <input type="text" class="form-control" name="Title_input" value="">
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Timer</label>
                                                    <div class="col-lg-9">
                                                        <button class="btn btn-primary">Start</button> 00:00:00
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row form-group">
                                                    <label for="Task_Type" class="col-lg-3 col-sm-3 control-label">Task Type</label>
                                                    <div class="col-lg-8">
                                                        <select name="" class="form-control" id="">
                                                            <option selected disabled>Please Select</option>
                                                            <option value="">test</option>
                                                            <option value="">test</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-lg-1" id="inputPlusCircle">
                                                        <a class="javascript:void(0)" onclick="get_modal(18)"><i class="fa  fa-plus-circle"></i> </a>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <label class="col-lg-3 control-label">Notes</label>
                                                    <div class="col-lg-9">
                                                        <textarea name="" rows="4" id="" class="form-control"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="noti_button">
                                                    <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                                    <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Task Type - Add Modal start here (modal id = 18) -->
        <div class="modal fade in" id="task_type_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Task Type - Add </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="task_type_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <label class="col-lg-3 control-label">Task Type</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="task_type_input" name="task_type_input" placeholder="Enter Task Type" value="">
                                        </div>
                                    </div>
                                    <div class="row form-group">
                                        <label for="inputEmail1" class="col-lg-3 col-sm-3 control-label">Status</label>
                                        <div class="col-lg-9">
                                            <select name="" class="form-control" id="">
                                                <option selected disabled>Please Select</option>
                                                <option value="">Active</option>
                                                <option value="">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" onclick="get_save_site()">Save</a>
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!--Search  Modal start here (modal id = 19) -->
        <div class="modal fade in" id="Search_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">Search </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="Search_form">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label for="">Search In :</label>
                                        </div>
                                        <div class="col-md-2 pe-0">
                                            <input type="text" class="form-control" placeholder="Quote">
                                        </div>
                                        <div class="col-md-2 pe-0">
                                            <input type="text" class="form-control" placeholder="Alex-Hill">
                                        </div>
                                        <div class="col-md-2 pe-0">
                                            <input type="text" class="form-control" placeholder="Keywords">
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-primary" type="button">Search</button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Job Ref</th>
                                                    <th>Type</th>
                                                    <th>Customer</th>
                                                    <th>Site</th>
                                                    <th class="col-lg-4">Description</th>
                                                    <th>Complete By</th>
                                                    <th>Completed On</th>
                                                    <th>Status</th>
                                                    <th>Product(s)</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1</td>
                                                    <td>JOB-0031</td>
                                                    <td>Service</td>
                                                    <td>Alex Hill</td>
                                                    <td>uk</td>
                                                    <td class="description">Lorem ipsum, dolor sit amet consectetur adipisicing elit. Numquam enim dolorum qui odit ex impedit officia dolor adipisci quos, maiores alias est culpa modi ipsa sit accusamus veritatis quidem? Odit magni magnam maiores voluptatibus in nostrum nesciunt asperiores quam at.</td>
                                                    <td>28/02/2025</td>
                                                    <td></td>
                                                    <td>Appointed</td>
                                                    <td><a href="#!">View</a></td>
                                                    <td><button type="button" class="btn btn-primary">Add</button></td>
                                                    <td><button type="button" class="btn btn-primary">Add Items</button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Description</th>
                                                    <th>Cost Price</th>
                                                    <th>Quantity</th>
                                                    <th>Include</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Product Test</td>
                                                    <td class="description"></td>
                                                    <td>$100.00</td>
                                                    <td><input type="text" class="form-control"></td>
                                                    <td><input type="checkbox"></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="text-end">
                                                        <button type="submit" class="btn btn-primary">Confirm Selection</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="noti_button">
                                        <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->

        <!-- Product List Modal start here (modal id = 20) -->
        <div class="modal fade in" id="product_list_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle"> Product List </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <form id="product_list_form">
                            @csrf
                            <div class="tab-container product_list_tabs">
                                <div class="tab-menu">
                                    <ul>
                                        <li><a href="javascript:void(0)" class="tab-a active-a" data-id="Product">Product (s) <span class="badge">04</span></a></li>
                                        <li><a href="javascript:void(0)" class="tab-a" data-id="Service">Service (s) <span class="badge">01</span></a></li>
                                        <li><a href="javascript:void(0)" class="tab-a" data-id="Consumable">Consumable (s) <span class="badge">01</span></a></li>
                                        <li><a href="javascript:void(0)" class="tab-a" data-id="Product_Group">Product Group (s) <span class="badge">36</span></a></li>
                                    </ul>
                                </div>
                                <!-- tab 1 Start -->
                                <div class="tab tab-active" data-id="Product">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-4 pe-0">
                                                    <select name="" id="" class="form-control">
                                                        <option selected disabled>-Any Category-</option>
                                                        <option value="">text</option>
                                                        <option value="">text</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 pe-0">
                                                    <input type="text" class="form-control" placeholder="Search Team">
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn-primary" type="button">Search</button>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="alphabetic_List">
                                                    <span><a href="#!">All</a></span>
                                                    <span><a href="#!">A</a></span>
                                                    <span><a href="#!">B</a></span>
                                                    <span><a href="#!">C</a></span>
                                                    <span><a href="#!">D</a></span>
                                                    <span><a href="#!">E</a></span>
                                                    <span><a href="#!">F</a></span>
                                                    <span><a href="#!">G</a></span>
                                                    <span><a href="#!">H</a></span>
                                                    <span><a href="#!">I</a></span>
                                                    <span><a href="#!">J</a></span>
                                                    <span><a href="#!">K</a></span>
                                                    <span><a href="#!">L</a></span>
                                                    <span><a href="#!">M</a></span>
                                                    <span><a href="#!">N</a></span>
                                                    <span><a href="#!">O</a></span>
                                                    <span><a href="#!">P</a></span>
                                                    <span><a href="#!">Q</a></span>
                                                    <span><a href="#!">R</a></span>
                                                    <span><a href="#!">S</a></span>
                                                    <span><a href="#!">T</a></span>
                                                    <span><a href="#!">U</a></span>
                                                    <span><a href="#!">V</a></span>
                                                    <span><a href="#!">W</a></span>
                                                    <span><a href="#!">X</a></span>
                                                    <span><a href="#!">Y</a></span>
                                                    <span><a href="#!">Z</a></span>
                                                    <span><a href="#!">0</a></span>
                                                    <span><a href="#!">1</a></span>
                                                    <span><a href="#!">2</a></span>
                                                    <span><a href="#!">3</a></span>
                                                    <span><a href="#!">4</a></span>
                                                    <span><a href="#!">5</a></span>
                                                    <span><a href="#!">6</a></span>
                                                    <span><a href="#!">7</a></span>
                                                    <span><a href="#!">8</a></span>
                                                    <span><a href="#!">9</a></span>
                                                </div>
                                                <p><strong>1-1 of 1 product(s)</strong></p>
                                            </div>
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
                                                    <tbody>
                                                        <tr>
                                                            <td>P1-0001</td>
                                                            <td>test</td>
                                                            <td>Product 101</td>
                                                            <td class="description">test</td>
                                                        </tr>
                                                        <tr>
                                                            <td>P1-0002</td>
                                                            <td>test</td>
                                                            <td>Product 101</td>
                                                            <td class="description">test</td>
                                                        </tr>
                                                        <tr>
                                                            <td>P1-0003</td>
                                                            <td>test</td>
                                                            <td>Product 101</td>
                                                            <td class="description">test</td>
                                                        </tr>
                                                        <tr>
                                                            <td>P1-0004</td>
                                                            <td>test</td>
                                                            <td>Product 101</td>
                                                            <td class="description">test</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="noti_button">
                                                <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- tab 2 Start -->
                                <div class="tab " data-id="Service">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-4 pe-0">
                                                    <select name="" id="" class="form-control">
                                                        <option selected disabled>-Any Category-</option>
                                                        <option value="">text</option>
                                                        <option value="">text</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 pe-0">
                                                    <input type="text" class="form-control" placeholder="Search Team">
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn-primary" type="button">Search</button>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="alphabetic_List">
                                                    <span><a href="#!">All</a></span>
                                                    <span><a href="#!">A</a></span>
                                                    <span><a href="#!">B</a></span>
                                                    <span><a href="#!">C</a></span>
                                                    <span><a href="#!">D</a></span>
                                                    <span><a href="#!">E</a></span>
                                                    <span><a href="#!">F</a></span>
                                                    <span><a href="#!">G</a></span>
                                                    <span><a href="#!">H</a></span>
                                                    <span><a href="#!">I</a></span>
                                                    <span><a href="#!">J</a></span>
                                                    <span><a href="#!">K</a></span>
                                                    <span><a href="#!">L</a></span>
                                                    <span><a href="#!">M</a></span>
                                                    <span><a href="#!">N</a></span>
                                                    <span><a href="#!">O</a></span>
                                                    <span><a href="#!">P</a></span>
                                                    <span><a href="#!">Q</a></span>
                                                    <span><a href="#!">R</a></span>
                                                    <span><a href="#!">S</a></span>
                                                    <span><a href="#!">T</a></span>
                                                    <span><a href="#!">U</a></span>
                                                    <span><a href="#!">V</a></span>
                                                    <span><a href="#!">W</a></span>
                                                    <span><a href="#!">X</a></span>
                                                    <span><a href="#!">Y</a></span>
                                                    <span><a href="#!">Z</a></span>
                                                    <span><a href="#!">0</a></span>
                                                    <span><a href="#!">1</a></span>
                                                    <span><a href="#!">2</a></span>
                                                    <span><a href="#!">3</a></span>
                                                    <span><a href="#!">4</a></span>
                                                    <span><a href="#!">5</a></span>
                                                    <span><a href="#!">6</a></span>
                                                    <span><a href="#!">7</a></span>
                                                    <span><a href="#!">8</a></span>
                                                    <span><a href="#!">9</a></span>
                                                </div>
                                                <p><strong>1-1 of 1 product(s)</strong></p>
                                            </div>
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
                                                    <tbody>
                                                        <tr>
                                                            <td>P2-0001</td>
                                                            <td>test</td>
                                                            <td>Product 201</td>
                                                            <td class="description">service product test</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="noti_button">
                                                <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- tab 3 Start -->
                                <div class="tab " data-id="Consumable">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-4 pe-0">
                                                    <select name="" id="" class="form-control">
                                                        <option selected disabled>-Any Category-</option>
                                                        <option value="">text</option>
                                                        <option value="">text</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 pe-0">
                                                    <input type="text" class="form-control" placeholder="Search Team">
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn-primary" type="button">Search</button>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="alphabetic_List">
                                                    <span><a href="#!">All</a></span>
                                                    <span><a href="#!">A</a></span>
                                                    <span><a href="#!">B</a></span>
                                                    <span><a href="#!">C</a></span>
                                                    <span><a href="#!">D</a></span>
                                                    <span><a href="#!">E</a></span>
                                                    <span><a href="#!">F</a></span>
                                                    <span><a href="#!">G</a></span>
                                                    <span><a href="#!">H</a></span>
                                                    <span><a href="#!">I</a></span>
                                                    <span><a href="#!">J</a></span>
                                                    <span><a href="#!">K</a></span>
                                                    <span><a href="#!">L</a></span>
                                                    <span><a href="#!">M</a></span>
                                                    <span><a href="#!">N</a></span>
                                                    <span><a href="#!">O</a></span>
                                                    <span><a href="#!">P</a></span>
                                                    <span><a href="#!">Q</a></span>
                                                    <span><a href="#!">R</a></span>
                                                    <span><a href="#!">S</a></span>
                                                    <span><a href="#!">T</a></span>
                                                    <span><a href="#!">U</a></span>
                                                    <span><a href="#!">V</a></span>
                                                    <span><a href="#!">W</a></span>
                                                    <span><a href="#!">X</a></span>
                                                    <span><a href="#!">Y</a></span>
                                                    <span><a href="#!">Z</a></span>
                                                    <span><a href="#!">0</a></span>
                                                    <span><a href="#!">1</a></span>
                                                    <span><a href="#!">2</a></span>
                                                    <span><a href="#!">3</a></span>
                                                    <span><a href="#!">4</a></span>
                                                    <span><a href="#!">5</a></span>
                                                    <span><a href="#!">6</a></span>
                                                    <span><a href="#!">7</a></span>
                                                    <span><a href="#!">8</a></span>
                                                    <span><a href="#!">9</a></span>
                                                </div>
                                                <p><strong>1-1 of 1 product(s)</strong></p>
                                            </div>
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
                                                    <tbody>
                                                        <tr>
                                                            <td>P2-0001</td>
                                                            <td>test</td>
                                                            <td>Product 201</td>
                                                            <td class="description">service product test</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="noti_button">
                                                <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- tab 4 Start -->
                                <div class="tab " data-id="Product_Group">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6 pe-0">
                                                    <input type="text" class="form-control" placeholder="Search Team">
                                                </div>
                                                <div class="col-md-3">
                                                    <button class="btn btn-primary" type="button">Search</button>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="alphabetic_List">
                                                    <span><a href="#!">All</a></span>
                                                    <span><a href="#!">A</a></span>
                                                    <span><a href="#!">B</a></span>
                                                    <span><a href="#!">C</a></span>
                                                    <span><a href="#!">D</a></span>
                                                    <span><a href="#!">E</a></span>
                                                    <span><a href="#!">F</a></span>
                                                    <span><a href="#!">G</a></span>
                                                    <span><a href="#!">H</a></span>
                                                    <span><a href="#!">I</a></span>
                                                    <span><a href="#!">J</a></span>
                                                    <span><a href="#!">K</a></span>
                                                    <span><a href="#!">L</a></span>
                                                    <span><a href="#!">M</a></span>
                                                    <span><a href="#!">N</a></span>
                                                    <span><a href="#!">O</a></span>
                                                    <span><a href="#!">P</a></span>
                                                    <span><a href="#!">Q</a></span>
                                                    <span><a href="#!">R</a></span>
                                                    <span><a href="#!">S</a></span>
                                                    <span><a href="#!">T</a></span>
                                                    <span><a href="#!">U</a></span>
                                                    <span><a href="#!">V</a></span>
                                                    <span><a href="#!">W</a></span>
                                                    <span><a href="#!">X</a></span>
                                                    <span><a href="#!">Y</a></span>
                                                    <span><a href="#!">Z</a></span>
                                                    <span><a href="#!">0</a></span>
                                                    <span><a href="#!">1</a></span>
                                                    <span><a href="#!">2</a></span>
                                                    <span><a href="#!">3</a></span>
                                                    <span><a href="#!">4</a></span>
                                                    <span><a href="#!">5</a></span>
                                                    <span><a href="#!">6</a></span>
                                                    <span><a href="#!">7</a></span>
                                                    <span><a href="#!">8</a></span>
                                                    <span><a href="#!">9</a></span>
                                                </div>
                                                <p><strong>1-1 of 1 product(s)</strong></p>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Product Group</th>
                                                            <th>Description</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Ram Testing</td>
                                                            <td class="description">testing....</td>
                                                            <th>
                                                                <div class="col-lg-1" id="inputPlusCircle">
                                                                    <a class="javascript:void(0)" onclick="get_modal()"><i class="fa  fa-plus-circle"></i> </a>
                                                                </div>
                                                            </th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="noti_button">
                                                <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end here -->
    </div>
</section>

<script src="{{url('public/backEnd/js/multiselect.js')}}"></script>
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
<!-- for modal start -->


<script>
    function get_modal(id) {
        var key = $("#id").val();
        if (id == 4 || id == 5 || id == 6 || id == 7 || id == 8 || id == 9 || id == 10 || id == 11 || id == 12 || id == 13 || id == 14 || id == 15 || id == 16 || id == 17 || id == 18 || id == 19 || id == 20) {
            if (key == '') {
                alert("Please save Customer first");
                return false;
            } else {
                if (id == 4) {
                    $('#Add_Project_form')[0].reset();
                    $("#Add_Project_modal").modal('show');
                } else if (id == 5) {
                    $('#Add_Site_Address_form')[0].reset();
                    $("#Add_Site_Address_modal").modal('show');
                } else if (id == 6) {
                    $('#Department_form')[0].reset();
                    $("#Department_modal").modal('show');
                } else if (id == 7) {
                    $('#Add_Tags_form')[0].reset();
                    $("#Add_Tags_modal").modal('show');
                } else if (id == 8) {
                    $('#Product_form')[0].reset();
                    $("#Product_modal").modal('show');
                } else if (id == 9) {
                    $('#Departmental_Code_Add_form')[0].reset();
                    $("#Departmental_Code_Add_modal").modal('show');
                } else if (id == 10) {
                    $('#Add_Tax_Rate_form')[0].reset();
                    $("#Add_Tax_Rate_modal").modal('show');
                } else if (id == 11) {
                    $('#Job_Title_form')[0].reset();
                    $("#Job_Title_modal").modal('show');
                } else if (id == 12) {
                    $('#add_Customer_Type_form')[0].reset();
                    $("#add_Customer_Type_modal").modal('show');
                } else if (id == 13) {
                    $('#add_region_form')[0].reset();
                    $("#add_region_modal").modal('show');
                } else if (id == 14) {
                    $('#PO_Reminder_form')[0].reset();
                    $("#PO_Reminder_modal").modal('show');
                } else if (id == 15) {
                    $('#Product_Category_form')[0].reset();
                    $("#Product_Category_modal").modal('show');
                } else if (id == 16) {
                    $('#Add_Attachment_form')[0].reset();
                    $("#Add_Attachment_modal").modal('show');
                } else if (id == 17) {
                    $('#New_Task_form')[0].reset();
                    $("#New_Task_modal").modal('show');
                } else if (id == 18) {
                    $('#task_type_form')[0].reset();
                    $("#task_type_modal").modal('show');
                } else if (id == 19) {
                    $('#Search_form')[0].reset();
                    $("#Search_modal").modal('show');
                } else if (id == 20) {
                    $('#product_list_form')[0].reset();
                    $("#product_list_modal").modal('show');
                }
            }
        } else if (id == 1) {
            $("#Add_Supplier_form")[0].reset();
            $("#Add_Supplier_modal").modal('show');
        } else if (id == 2) {
            $("#Add_Supplier_Contact_form")[0].reset();
            $("#Add_Supplier_Contact_modal").modal('show');
        } else if (id == 3) {
            $("#Add_Customer_form")[0].reset();
            $("#Add_Customer_modal").modal('show');
        }
    }
</script>
<!-- modal end -->

<script>
    //for tabs container
    // $(document).ready(function() {
    //     $('.tab-a').click(function() {
    //         $(".tab").removeClass('tab-active');
    //         $(".tab[data-id='" + $(this).attr('data-id') + "']").addClass("tab-active");
    //         $(".tab-a").removeClass('active-a');
    //         $(this).parent().find(".tab-a").addClass('active-a');
    //     });
    // });

    $(document).ready(function() {
        $('.tab-a').click(function() {
            let parentContainer = $(this).closest('.tab-container');

            parentContainer.find(".tab").removeClass('tab-active');
            parentContainer.find(".tab-a").removeClass('active-a');

            let tabId = $(this).attr('data-id');
            parentContainer.find(".tab[data-id='" + tabId + "']").addClass("tab-active");
            $(this).addClass('active-a');
        });
    });

    //hide-show 
    $(document).ready(function() {
        $("#Recurring_task").change(function() {
            if ($(this).is(":checked")) {
                $(".check_hide").show();
            } else {
                $(".check_hide").hide();
            }
        });
    });
</script>

<script>
    function getemail(id) {
        var email;
        if (id == 1) {
            email = $('#email').val();
        } else if (id == 2) {
            email = $("#customer_email").val();
        } else if (id == 3) {
            email = $("#site_email").val();
        } else if (id == 4) {
            email = $("#login_email").val();
        }
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<script>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        if (email.search(validRegExp) == -1) {
            $('#emailErr' + id).text("Please enter correct email address");
            return false;
        } else {
            $('#emailErr' + id).text("");
        }
    }
</script>

@endsection