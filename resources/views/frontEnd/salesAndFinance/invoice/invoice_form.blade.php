@include('frontEnd.salesAndFinance.jobs.layout.header')

<style>
    .disabled-tab {
        pointer-events: none;
        opacity: 0.5;
    }
    .input_style table tbody textarea{
        resize: none;
        overflow: hidden;
    }
    .deleteRow {
        cursor: pointer;
    }
    .image_style {
        cursor: pointer;
    }
    .unclicked {
        pointer-events: none;
    }
</style>
<section class="main_section_page px-3">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4 ">
            <div class="pageTitle">
                @if(isset($invoice) && $invoice !='')
                <h3>{{$invoice->invoice_ref}}</h3>
                @else 
                <h3>New Invoice</h3>
                @endif
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="mt-1 mb-0 text-center" id="message_save"></div>
        </div>
    </div>

    <div class="row">
        <form id="all_data"class="customerForm">
            <input type="hidden" name="id" value="<?php if(isset($invoice) && $invoice !=''){echo $invoice->id;}?>">
            <div class="col-lg-12">
                <div class="newJobForm">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle">Customer / Billing Details</h4>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer <span class="radStar">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions InvoicecheckError" id="invoice_customer_id" name="customer_id" onchange="get_customer_details()">
                                                <option selected disabled>Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" <?php if(isset($invoice->customer_id) && $invoice->customer_id == $customer->id){echo 'selected';}?>>{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-1">
                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(1)"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Project</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" <?php if (!isset($invoice) && $invoice == '') {echo 'disabled';} ?> id="invoice_project_id" name="project_id">
                                                <option selected disabled>Select Customer First</option>
                                                @foreach($projects as $project)
                                                <option value="{{$project->id}}" <?php if (isset($invoice) && $invoice->project_id == $project->id) {echo 'selected'; } ?>>{{$project->project_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="javascript:void(0)" class="formicon" onclick="get_modal(2)"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Contact</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" <?php if (!isset($invoice) && $invoice == '') {echo 'disabled';} ?> id="invoice_contact_id" name="contact_id">
                                                <option selected disabled>Select Customer First</option>
                                                @foreach($additional_contact as $addContact)
                                                <option value="{{$addContact->id}}" <?php if (isset($invoice) && $invoice->contact_id == $addContact->id) { echo 'selected'; } ?>>{{$addContact->contact_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="javascript:void(0)" onclick="get_modal(3)" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Name <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput InvoicecheckError" value="<?php if (isset($invoice) && $invoice->name != '') {echo $invoice->name; } ?>" name="name" id="invoice_name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Address <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput InvoicecheckError" name="address" id="invoice_address" rows="3" placeholder="75 Cope Road Mall Park USA"><?php if (isset($invoice) && $invoice->address != '') {echo $invoice->address; } ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">City</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" value="<?php if (isset($invoice) && $invoice->city != '') {echo $invoice->city; } ?>" name="city" id="invoice_city">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" value="<?php if (isset($invoice) && $invoice->county != '') {echo $invoice->county; } ?>" name="county" id="invoice_county">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Postcode</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" placeholder="Postcode" name="postcode" id="invoice_Postcode" value="<?php if (isset($invoice) && $invoice->postcode != '') {echo $invoice->postcode; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputTelephone" class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="invoice_telephoneCode" name="telephone_code">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" <?php if (isset($invoice) && $invoice->telephone_code == $value->id) {echo 'selected'; } ?>> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="invoice_telephone" placeholder="Telephone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10" value="<?php if (isset($invoice) && $invoice->telephone != '') {echo $invoice->telephone; } ?>">
                                            <span style="color:red;display:none" id="InvoiceTelephoneErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" name="invoice_mobile_code" id="invoice_mobile_code">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" <?php if (isset($invoice) && $invoice->invoice_mobile_code == $value->id) {echo 'selected'; } ?>> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="invoice_mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10" value="<?php if (isset($invoice) && $invoice->mobile != '') {echo $invoice->mobile; } ?>">
                                            <span style="color:red;display:none" id="InvoiceMobileErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoice_email" name="email"  value="<?php if (isset($invoice) && $invoice->email != '') {echo $invoice->email; } ?>" onchange="invoice_check_email()">
                                            <span style="color:red" id="invoiceemailErr"></span>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle">Site / Delivery Details</h4>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Site</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="invoice_site_id" name="site_delivery_add_id" <?php if (!isset($invoice) && $invoice == '') {echo 'disabled';} ?> onchange="siteDetail()">
                                                <option>None</option>
                                                <option <?php if (isset($invoice) && $invoice->site_delivery_add_id == 0 || isset($invoice) && $invoice->site_delivery_add_id == '') { echo 'selected'; } ?> value="0">Same as customer</option>
                                                @foreach($site as $siteVal)
                                                    <option value="{{$siteVal->id}}" <?php if (isset($purchase_orders) && $purchase_orders->site_delivery_add_id == $siteVal->id) { echo 'selected'; } ?>>{{$siteVal->site_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-1">
                                            <a href="#!" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Region</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="invoiceRegions" name="region">
                                                <option selected disabled>None</option>
                                                @foreach($region as $reg)
                                                <option value="{{$reg->id}}" <?php if(isset($invoice) && $invoice->region == $reg->id){ echo "selected";}?>>{{$reg->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" onclick="openRegionModal('invoiceRegions');"><i class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                            
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label"> Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoice_siteName" name="site_name" value="<?php if (isset($invoice) && $invoice->site_name != '') { echo $invoice->site_name; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Company</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoicesite_companyName" name="company_name" value="<?php if (isset($invoice) && $invoice->company_name != '') { echo $invoice->company_name; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for=""
                                            class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="site_address" id="invoice_site_address" rows="3"
                                                placeholder="75 Cope Road Mall Park USA"><?php if (isset($invoice) && $invoice->site_address != '') { echo $invoice->site_address; } ?></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">City</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoice_site_city" name="site_city" value="<?php if (isset($invoice) && $invoice->site_city != '') { echo $invoice->site_city; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoice_site_county" name="site_county" placeholder="Site County" value="<?php if (isset($invoice) && $invoice->site_county != '') { echo $invoice->site_county; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for=""
                                            class="col-sm-3 col-form-label">Postcode</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="invoice_site_postcode" name="site_postcode" value="<?php if (isset($invoice) && $invoice->site_postcode != '') { echo $invoice->site_postcode; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for=""
                                            class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="invoice_siteTelephoneCode" name="site_telephone_code">
                                            <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" <?php if (isset($invoice) && $invoice->site_telephone_code == $value->id) { echo 'selected'; } ?>> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="invoice_siteTelephone" name="site_telephone" placeholder="Site Telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10" value="<?php if (isset($invoice) && $invoice->site_telephone != '') { echo $invoice->site_telephone; } ?>">
                                            <span style="color:red;display:none" id="InvoiceSiteTelephoneErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="invoice_siteMobileCode" name="site_mobile_code">
                                            <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" <?php if (isset($invoice) && $invoice->site_mobile_code == $value->id) { echo 'selected'; } ?>> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="invoice_site_mobile" name="site_mobile" value="<?php if (isset($invoice) && $invoice->site_mobile != '') { echo $invoice->site_mobile; } ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57 && value.length<10">
                                            <span style="color:red;display:none" id="InvoiceSiteMobileErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle">Invoice Details</h4>
                                    <div class="mb-3 row">
                                        <label class="col-sm-3 col-form-label">Invoice Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput" value="<?php if (isset($invoice) && $invoice->invoice_ref != '') { echo $invoice->invoice_ref; }else{ echo 'Invoice Ref ###'; } ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Invoice Type</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="invoce_type" name="invoice_type">
                                                <option value="1" <?php if (isset($invoice) && $invoice->invoice_type == 1) { echo 'selected'; } ?>>Service</option>
                                                <option value="2" <?php if (isset($invoice) && $invoice->invoice_type == 2) { echo 'selected'; } ?>>Product</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Customer Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" name="customer_ref" id="invoice_customer_ref" placeholder="Customer Ref if any" value="<?php if (isset($invoice) && $invoice->invoice_type == 2) { echo 'selected'; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Customer Job Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="invoice_customer_job_ref" name="customer_job_ref" placeholder="Customer Job if any" value="<?php if (isset($invoice) && $invoice->invoice_type == 2) { echo 'selected'; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Purch. Order Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="invoice_purchase_order_ref" name="purchase_order_ref" placeholder="Purchase Order Ref if any" value="<?php if (isset($invoice) && $invoice->invoice_type == 2) { echo 'selected'; } ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Invoice Date <span class="radStar">*</span> </label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control editInput InvoicecheckError" id="invoice_date" name="invoice_date" placeholder="" value="<?php if (isset($invoice) && $invoice->invoice_date != '') { echo $invoice->invoice_date; } ?>">
                                        </div>
                                        <!-- <div class="col-sm-2">
                                            <a href="#!" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                        </div> -->
                                    </div>
                                    
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Payment Terms</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="invoice_payment_terms" name="payment_terms">
                                                <option value="21" selected>Default (21)</option>
                                                @for($i = 0; $i <= 90; $i++)
                                                <option value="{{ $i }}" <?php if (isset($invoice) && $invoice->payment_terms == $i) { echo 'selected'; } ?>>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <label class="form-check-label checkboxtext pt-1">Days</label>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Due Date <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control editInput InvoicecheckError" id="invoice_due_date" name="due_date" placeholder="" value="<?php if (isset($invoice) && $invoice->due_date != '') { echo $invoice->due_date; } ?>">
                                        </div>
                                        <!-- <div class="col-sm-2">
                                            <a href="#!" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                        </div> -->
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="invoice_status" name="status">
                                                <option value="Draft">Draft</option>
                                                <option value="Invoiced">Invoiced</option>
                                                <option value="Outstanding">Outstanding</option>
                                                <option value="Paid" disabled>Paid</option>
                                                <option value="Cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-3 col-form-label">Tags</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="invoice_tags" name="tags">
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="javascript:void(0)" class="formicon"><i class="fa-solid fa-square-plus" onclick="get_modal(7)"></i></a>
                                        </div>
                                    </div>   
                                    <div class="mb-3 row">
                                        <label for="" class="col-sm-5 col-form-label">
                                            <a href="javascript:void(0)" onclick="openReminderModal(<?php if (isset($invoice) && $invoice != '') { echo $invoice->id; } ?>)" class="profileDrop pink"> <i class="fa fa-clock"></i> Set Reminder </a>
                                        </label>
                                    </div>  
                                    <div class="setRiminderTable" style="display:none">
                                        <div class="productDetailTable">
                                            <table class="table" id="">
                                                <thead class="table-light">
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
                                                            <a href="javascript:void(0)" class="iconColrGreen fecth_data" data-id="{{$reminderVal->id}}" data-invoice_id="{{ $reminderVal->invoice_id }}" data-title="{{$reminderVal->title}}" data-user_id="{{$reminderVal->user_id}}" data-reminder_date="{{$reminderVal->reminder_date}}" data-reminder_time="{{$reminderVal->reminder_time}}" data-notification="{{$reminderVal->notification}}" data-sms="{{$reminderVal->sms}}" data-email="{{$reminderVal->email}}" data-notes="{{$reminderVal->notes}}" data-icon="edit"><i class="material-symbols-outlined">edit</i></a>
                                                            <a href="javascript:void(0)" class="iconColrRad reminder_delete" data-delete="{{ $reminderVal->id }}"><i class="material-symbols-outlined">close</i></a>
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
                <div class="newJobForm mt-4">
                    <label class="upperlineTitle">Item Details</label>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="mb-3 row">
                                <label for="inputCountry" class="col-sm-2 col-form-label">Select product</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control editInput" id="search-product" placeholder="Type to add product">
                                    <div class="parent-container"></div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="plusandText">
                                        <a href="javascript:void(0)" onclick="get_modal(4)" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                        <span class="afterPlusText"> (Type to view product or <a href="javascript:void(0)" onclick="openProductmodal()">Click here</a> to view all assets)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="col-sm-5">
                            <div class="mb-3 row">
                                <div class="col-md-9 row">
                                    <label for="inputCountry" class="col-sm-5 col-form-label"> Template Options:</label>
                                    <div class="col-sm-7">
                                        <select class="form-control editInput selectOptions">
                                            <option>Show Product Details</option>
                                            <option>Default</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="pageTitleBtn justify-content-start p-0">
                                        <a href="#" class="profileDrop">Add Title</a>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <!-- <div class="col-sm-7">
                            <div class="mb-3 row">
                                <label for="inputCountry" class="col-sm-2 col-form-label">Catalogue:</label>
                                <div class="col-sm-3">
                                    <select class="form-control editInput selectOptions">
                                        <option>Default Pricing</option>
                                        <option>Default</option>
                                    </select>
                                </div>
                            </div>
                        </div> -->

                        <div class="col-sm-12">
                            <div class="productDetailTable table-responsive input_style">
                                <table class="table" id="result">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code </th>
                                            <th>Product </th>
                                            <th>Description</th>
                                            <th>
                                                <div class="tableplusBTN">
                                                    <span>Account Code </span>
                                                    <span class="plusandText ps-3">
                                                        <a href="javascript:void(0)" class="formicon pt-0" onclick="openAccountCodeModal(null)"> <i class="fa-solid fa-square-plus"></i> </a>
                                                    </span>
                                                </div>
                                            </th>
                                            <th>Qty </th>
                                            <th>Cost Price </th>
                                            <th>Cost Calc </th>
                                            <th>Price </th>
                                            <th>Discount </th>
                                            <th>
                                                <div class="tableplusBTN">
                                                    <span>VAT(%) </span>
                                                    <span class="plusandText ps-3">
                                                        <a href="javascript:void(0)" class="formicon pt-0" onclick="get_modal(5)"> <i class="fa-solid fa-square-plus"></i> </a>
                                                    </span>
                                                </div>
                                            </th>
                                            <th>VAT </th>
                                            <th>Amount</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot class="insrt_product_and_detail product_Det" id="product_calculation" style="display:none">
                                        <tr>
                                            <td class="border_tran" colspan="7"></td>
                                            <td colspan="3">Sub Total (exc. VAT)</td>
                                            <td id="exact_vat"></td>
                                            <td class="border_tran" colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td class="border_tran" colspan="7"></td>
                                            <td colspan="3">VAT</td>
                                            <td id="vat"></td>
                                            <td class="border_tran" colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td class="border_tran" colspan="7"></td>
                                            <td colspan="3"><strong>Total(inc.VAT)</strong></td>
                                            <td><strong id="total_vat"></strong></td>
                                            <td class="border_tran" colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td class="border_tran" colspan="7"></td>
                                            <td colspan="3"><strong>Paid</strong></td>
                                            <td><strong id="paid_amount">-£0.00</strong></td>
                                            <td class="border_tran" colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td class="border_tran" colspan="7"></td>
                                            <td colspan="3"><strong>Outstanding (inc.VAT)</strong></td>
                                            <td><strong id="outstanding_vat"></strong></td>
                                            <td class="border_tran" colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div><!-- End  off newJobForm -->
                
                <div class="newJobForm mt-4">
                    <label class="upperlineTitle">Notes</label>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle text-start">Customer Notes <span class="afterPlusText"> Will be included in invoive </span></h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="invoice_customer_notes" name="customer_notes" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle text-start">Terms</h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="invoice_terms_notes" name="terms" class="form-control">
                                    All Invoices must be paid within 7 days of the issue date. If you have any queries please contact 7025639852 immediately upon receiving this invoice
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="">
                                <h4 class="contTitle text-start">Internal Notes</h4>
                                <div class="mt-3">
                                    <textarea cols="40" rows="5" id="invoice_internal_notes" name="internal_notes" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End  off newJobForm -->

                <div class="newJobForm mt-4">
                    <label class="upperlineTitle">Attachments</label>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="py-4">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" onclick="get_modal(6)" class="profileDrop">New Attachments</a>
                                    <a href="javascript:void(0)" class="profileDrop">Upload Multi Attachment</a>
                                    <a href="javascript:void(0)" class="profileDrop">Preview Attachment(s)</a>
                                    <a href="javascript:void(0)" class="profileDrop">Download Attachment(s)</a>
                                    <a href="javascript:void(0)" class="profileDrop">Delete Attachment(s)</a>
                                </div>
                            </div>
                            @if(isset($invoice) && $invoice !='')
                            <div class="col-sm-12">
                                <div class="productDetailTable input_style">
                                    <table class="table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"></label></th>
                                                <th>ID</th>
                                                <th>Type</th>
                                                <th class="col-2">Title</th>
                                                <th>Description</th>
                                                <th>Section</th>
                                                <th>Customer Visible</th>
                                                <th>Mobile User Visible</th>
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
                </div><!-- End  off newJobForm -->

                <div class="newJobForm mt-4">
                    <label class="upperlineTitle">Tasks</label>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="py-4">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" onclick="get_modal(8)" class="profileDrop <?php if(isset($invoice) && $invoice ==''){ echo "disabled-tab"; }?>">New Tasks</a>
                                </div>
                                <div class="jobsection pt-3">
                                    <a href="#!" class="profileDrop bgColour" id="task_active_inactive" style="background-color:#474747" onclick="bgColorChange(1)">Tasks</a>
                                    <a href="#!" class="profileDrop bgColour" id="recurring_active_inactive" onclick="bgColorChange(2)">Recurring Tasks</a>
                                </div>
                                <div class="col-sm-12" id="taskHideShow">
                                <div class="productDetailTable">
                                    <table class="table">
                                        <thead class="table-light">
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
                                    <table class="table">
                                        <thead class="table-light">
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
                </div>

            </div>
        </form>
    </div>
    

    
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
            <div class="pageTitleBtn">
                <a href="javascript:void(0)" onclick="save_all_data()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                <!-- <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Action</a> -->

            </div>
        </div>
    </div>
</div>
</section>

<x-tag-modal
    modalId="TagModal"
    modalTitle="Add Tag"
    formId="add_tag_form"
    inputId="tag_title"
    statusId="tag_status"
    saveButtonId="saveTag"
    placeholderText="Tag" />

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

    <x-add-attachment-modal
    purchaseModalId="purchase_model"
    purchaseformId="purchase_Attachmentform"
    refTitle="Invoice"
    modalTitle="Add Attachment"
    typeId="purchase_typeId"
    inputTitle="purchase_title"
    selectfileName="purchase_file"
    inputDescription="purchase_description"
    saveButtonId="savePurchaseAttachment"
    saveButtonUrl="{{url('/invoices/invoice_attachmentSave')}}"
    hiddenForeignId="invoice_id" />

    <x-add-reminder
    reminderModalId="ReminderModal"
    modalTitle="Invoice Reminder"
    reminderformId="reminderform"
    reminderId="reminder_id"
    reminderDate="reminder_date"
    reminderTime="reminder_time"
    reminderUser="reminder_user"
    hiddenForeignId="reminder_invoice_id"
    reminderNotification="reminder_notification"
    reminderEmail="reminder_email"
    reminderSms="reminder_sms"
    reminderTitle="reminder_title"
    reminderNotes="reminder_notes"
    reminderSaveUrl="{{url('/invoices/save_reminder')}}"
    saveButtonId="saveReminder" />

    <x-new-task-modal
    modalId="NewTaskModal"
    modalTitle="New Task"
    formId="newTaskform"
    taskCustomerId="task_customer_id"
    taskId="task_id"
    foriegnId="task_invoice_id"
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
    saveNewTaskUrl="{{url('/invoices/new_task_save')}}"
    completeNewTaskUrl="{{url('/invoices/completeNewTaskUrl')}}"
    saveButtonId="saveNewTask" />

<script>
const tagURL = '{{ route("General.ajax.getTags") }}';
var get_itemUrl="{{ route('item.ajax.searchProduct') }}";
var result_product_calculationUrl="{{url('result_product_calculation')}}";
var vat_tax_detailsUrl="{{url('/vat_tax_details')}}";
var invoice_productsDeleteUrl="{{url('invoices/invoice_productsDelete')}}";
var invoice_saveUrl="{{url('/invoices/invoice_save')}}";
var get_customer_details_frontUrl="{{url('get_customer_details_front')}}";
var getCustomerSiteDetailsUrl='{{ route("customer.ajax.getCustomerSiteDetails") }}';
var redirectUrl="{{ url('invoices/invoice?key=Draft') }}";
var save_tagUrl="{{url('/save_tag')}}";
var getAttachmentPageUrl='{{ url("invoices/getInvoiceAllAttachmens") }}';
var customer_visibleURL='{{ url("invoices/customer_visibleUpdate") }}';
var mobile_user_visibleURL='{{ url("invoices/mobile_user_visibleUpdate") }}';
var deleteInvoiceAttachmentURL="{{url('/invoices/delete_invoice_attachment')}}";
var invoice_ReminderDeleteUrl="{{url('/invoices/delete_invoice_reminder')}}";
var getAllInvoiceNewTaskListUrl='{{ url("invoices/getAllInvoiceNewTaskList") }}';
var invoice_id="<?php if(isset($invoice) && $invoice !=''){echo $invoice->id;}?>";
var invoice_ref="<?php if(isset($invoice) && $invoice !=''){echo $invoice->invoice_ref;}?>";
var attachmentsFileURL="<?php echo url('public/images/invoice_attachment/'); ?>";
var delete_image="<?php echo url('public/frontEnd/jobs/images/delete.png'); ?>";
var edit_image='{{url("public/frontEnd/jobs/images/pencil.png")}}';
var token = '<?php echo csrf_token(); ?>'
var reminder_dataCount='<?php echo count($reminder_data);?>'
</script>
@include('components.add-customer-modal')
@include('components.contact-modal')
@include('components.add-site-modal')
@include('components.job-title-model')
@include('components.customer-type-modal')
@include('components.add-project-modal')
@include('components.department-model')
@include('components.product-list')
@include('components.account-code')
@include('frontEnd.salesAndFinance.item.common.addproductmodal')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
@include('components.region-model')
@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script type="text/javascript" src="{{ url('public/js/salesFinance/invoice/invoice_add.js') }}"></script>
<script>
<?php if(isset($invoice) && $invoice !=''){?>
    var id='{{$invoice->id}}';
    getProductDetail(id, '{{ url("invoices/getInvoiceProductDetail") }}');
    getAttachment(id, '{{ url("invoices/getInvoiceAllAttachmens") }}');
<?php }?>
</script>