@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="main_section_page px-3">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 col-lg-4 col-xl-4 ">
                        <div class="pageTitle">
                            <h3><?php if(isset($customer)){echo $customer->contact_name;}else{echo "New Customer";}?></h3>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                        <div class="pageTitleBtn">
                            <a href="javascript:void(0)" class="profileDrop" onclick="save_all_data('no')"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                            <a href="{{url('customers?list_mode=ACTIVE')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>

                        </div>
                    </div>
                </div>
                <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
                    <p>The Customer has been saved Successfully</p>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="newJobForm">
                            <form class="customerForm" id="customerForm">
                                @csrf
                                <input type="hidden" id="home_id" name="home_id" value="{{$home_id}}">
                                <input type="hidden" id="is_converted" name="is_converted" value="1">
                                <input type="hidden" id="id" name="id" value="<?php if(isset($key)){echo $key;}?>">
                        
                                <div class="row">
                                    <div class="col-md-4 col-lg-4 col-xl-4">
                                        <div class="formDtail" id="form1">
                                            <h4 class="contTitle">Customer Details</h4>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Customer
                                                        Name <span
                                                        class="radStar">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="name" name="name"
                                                            value="<?php if(isset($customer)){echo $customer->name;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputProject" class="col-sm-3 col-form-label">Customer Type</label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control editInput selectOptions get_customer_result"
                                                            id="customer_type_id" name="customer_type_id">
                                                            <option selected disabled>None</option>
                                                            <?php foreach($customer_type as $type){?>
                                                                <option value="{{$type->id}}" <?php if(isset($customer) && $type->id == $customer->customer_type_id){echo 'selected';}?>>{{$type->title}}</option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a href="javascript:void(0)" class="formicon" id="openPopupButton" onclick="get_modal(1)"><i
                                                                class="fa-solid fa-square-plus"></i></a>
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Conatact
                                                        Name <span
                                                        class="radStar">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="contact_name"
                                                            value="<?php if(isset($customer)){echo $customer->contact_name;}?>" name="contact_name">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputProject" class="col-sm-3 col-form-label">Job Title
                                                        (Position)</label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control editInput selectOptions get_job_title_result"
                                                            id="job_title" name="job_title">
                                                            <option selected disabled>None</option>
                                                            <?php foreach($job_title as $title){?>
                                                                <option value="{{$title->id}}" <?php if(isset($customer) && $title->id == $customer->job_title){echo 'selected';}?>>{{$title->name}}</option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a href="javascript:void(0)" class="formicon" onclick="get_modal(2)"><i
                                                                class="fa-solid fa-square-plus"></i></a>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="email" name="email"
                                                            value="<?php if(isset($customer)){echo $customer->email;}?>" onblur="getemail(1)">
                                                            <span id="emailErr1" style="color: red;"></span>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputTelephone"
                                                        class="col-sm-3 col-form-label">Telephone</label>
                                                        <div class="col-sm-2 pe-0">
                                                        <select class="form-control editInput selectOptions" id="telephone_country_code" name="telephone_country_code">
                                                            <option selected disabled>Please Select</option>
                                                            <?php foreach($country_code as $telephone_country_code){?>
                                                                <option value="{{$telephone_country_code->id}}" <?php if(isset($customer) && $customer->telephone_country_code == $telephone_country_code->id){echo 'selected';}?>>+{{$telephone_country_code->code}}</option>
                                                            <?php }?>
                                                        </select>
                                                        </div>
                                                        <div class="col-sm-1 numberHifan">
                                                            -
                                                        </div>
                                                    <div class="col-sm-6 ps-0">
                                                        <input type="text" class="form-control editInput"
                                                            id="telephone" name="telephone" value="<?php if(isset($customer)){echo $customer->telephone;}?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                                    <div class="col-sm-2 pe-0">
                                                        <select class="form-control editInput selectOptions" id="mobile_country_code" name="mobile_country_code">
                                                            <option selected disabled>Please Select</option>
                                                        <?php foreach($country_code as $mobile_country_code){?>
                                                                <option value="{{$mobile_country_code->id}}" <?php if(isset($customer) && $customer->mobile_country_code == $mobile_country_code->id){echo 'selected';}?>>+{{$mobile_country_code->code}}</option>
                                                            <?php }?>
                                                        </select>
                                                        </div>
                                                        <div class="col-sm-1 numberHifan">
                                                            -
                                                        </div>
                                                    <div class="col-sm-6 ps-0">
                                                        <input type="text" class="form-control editInput" id="mobile" name="mobile"
                                                            value="<?php if(isset($customer)){echo $customer->mobile;}?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Fax</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="fax" name="fax"
                                                            value="<?php if(isset($customer)){echo $customer->fax;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputAddress"
                                                        class="col-sm-3 col-form-label">Website</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="website" name="website"
                                                            value="<?php if(isset($customer)){echo $customer->website;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCounty" class="col-sm-3 col-form-label">Default
                                                        Catalogue</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="catalogue_id" name="catalogue_id">
                                                            <option selected disabled>None</option>
                                                            <option value="1" <?php if(isset($customer) && $customer->catalogue_id == 1){echo 'selected';}?>>ABC</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputPincode" class="col-sm-3 col-form-label">Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="status" name="status">
                                                            <option value="1" <?php if(isset($customer) && $customer->status ==1){echo 'selected';}?>>Active</option>
                                                            <option value="0" <?php if(isset($customer) && $customer->status ==0){echo 'selected';}?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-xl-4">
                                        <div class="formDtail" id="form2">
                                            <h4 class="contTitle">Address Details</h4>

                                                <div class="mb-3 row">
                                                    <label for="inputProject" class="col-sm-3 col-form-label">Region</label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control editInput selectOptions get_region_result"
                                                            id="region" name="region">
                                                            <option selected disabled>None</option>
                                                            <?php foreach($region as $region_val){?>
                                                                <option value="{{$region_val->id}}" <?php if(isset($customer) && $region_val->id == $customer->region){echo $customer->mobile;}?>>{{$region_val->title}}</option>
                                                        <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <a href="javascript:void(0)" class="formicon" onclick="get_modal(3)"><i
                                                                class="fa-solid fa-square-plus"></i></a>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputAddress"
                                                        class="col-sm-3 col-form-label">Address <span
                                                        class="radStar">*</span></label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control textareaInput" name="address"
                                                            id="address" rows="3"
                                                            placeholder="75 Cope Road Mall Park USA"><?php if(isset($customer)){echo $customer->address;}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCity" class="col-sm-3 col-form-label">City</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="city"
                                                            value="<?php if(isset($customer)){echo $customer->city;}?>" name="city">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCounty" class="col-sm-3 col-form-label">County</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="country"
                                                            placeholder="Site County" name="country" value="<?php if(isset($customer)){echo $customer->country;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputPincode"
                                                        class="col-sm-3 col-form-label">Pincode</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="postal_code"
                                                            value="<?php if(isset($customer)){echo $customer->postal_code;}?>" name="postal_code">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCountry"
                                                        class="col-sm-3 col-form-label">Country</label>
                                                    <div class="col-sm-9">
                                                      <select class="form-control editInput selectOptions"
                                                        id="country_code" name="country_code">
                                                        <option selected disabled>Select Coutry</option>
                                                            <?php foreach($country as $countryval){?>
                                                            <option value="{{$countryval->id}}" <?php if(isset($customer) && $countryval->id == $customer->country_code){echo 'selected';}?>>{{$countryval->name}}</option>
                                                        <?php }?>
                                                    </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputAddress" class="col-sm-3 col-form-label">Site
                                                        Notes</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control textareaInput" name="site_notes"
                                                            id="site_notes" rows="3" placeholder="Site Notes"><?php if(isset($customer)){echo $customer->site_notes;}?></textarea>
                                                    </div>
                                                </div>
                                                
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-lg-4 col-xl-4">
                                        <div class="formDtail" id="form3">
                                            <h4 class="contTitle">Other Details</h4>
                                                <div class="mb-3 row">
                                                    <label for="inputJobType"
                                                        class="col-sm-3 col-form-label">Currency</label>
                                                    <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions" id="currency" name="currency" required>
                                                        <option value="" selected disabled>None</option>
                                                        <?php foreach($country as $countryItem): ?>
                                                                <?php foreach($countryItem->currencies as $currency): ?>
                                                                    <option value="<?php echo $currency->currency_code; ?>" <?php if(isset($customer) && $currency->currency_code == $customer->currency){echo 'selected';}?>>
                                                                        <?php echo $countryItem->name; ?> (<?php echo $currency->currency_code; ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endforeach; ?>
                                                    </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCustomer" class="col-sm-3 col-form-label">Credit
                                                        Limit</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput textareaInput"
                                                            id="credit_limit" name="credit_limit" placeholder="Customer Ref if any" value="<?php if(isset($customer)){echo $customer->credit_limit;}?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputCustomer"
                                                        class="col-sm-3 col-form-label">Discount</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput textareaInput"
                                                            id="discount" name="discount" placeholder="Customer Job if any" value="<?php if(isset($customer)){echo $customer->discount;}?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputJobType" class="col-sm-3 col-form-label">Discount
                                                        Type</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="discount_type" name="discount_type">
                                                            <option value="1" <?php if(isset($customer) && $customer->discount_type == 1){echo 'selected';}?>>Percentage</option>
                                                            <option value="2" <?php if(isset($customer) && $customer->discount_type == 2){echo 'selected';}?>>Flat</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputPurchase" class="col-sm-3 col-form-label">Sage
                                                        Ref.</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput textareaInput"
                                                            id="saga_ref" name="saga_ref" placeholder="Sage Ref." value="<?php if(isset($customer)){echo $customer->saga_ref;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputJobType" class="col-sm-3 col-form-label">Company
                                                        Reg</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput textareaInput"
                                                            id="company_reg" name="company_reg" placeholder="Company Reg" value="<?php if(isset($customer)){echo $customer->company_reg;}?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputPriority" class="col-sm-3 col-form-label">VAT / Tax
                                                        No.</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput textareaInput"
                                                            id="vat_tax_no" name="vat_tax_no" placeholder="" value="<?php if(isset($customer)){echo $customer->vat_tax_no;}?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label class="col-sm-3 col-form-label">Payment Terms</label>
                                                    <div class="col-sm-6">
                                                        <select class="form-control editInput selectOptions"
                                                            id="payment_terms" name="payment_terms">
                                                            <option value="21" <?php if(isset($customer) && $customer->payment_terms == 21){echo 'selected';}?>>Default (21)
                                                            </option>
                                                            <?php for($i=1;$i<21;$i++){?>
                                                            <option value="{{$i}}" <?php if(isset($customer) && $i == $customer->payment_terms){echo 'selected';}?>>{{$i}}</option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <label class="form-check-label checkboxtext" for="checkalrt">
                                                            days</label>
                                                    </div>

                                                </div>

                                                <div class="mb-0 row">
                                                    <label class="col-sm-3 col-form-label">Assigned Products</label>
                                                    <div class="col-sm-9">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input assigned_product" type="radio"
                                                                name="radio" id="assigned_product1" <?php if(isset($customer) && $customer->assigned_product == 1){echo 'checked';}?>>
                                                            <label class="form-check-label checkboxtext"
                                                                for="inlineRadio1">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input assigned_product" type="radio"
                                                                name="radio" id="assigned_product2" <?php if(isset($customer) && $customer->assigned_product == 1){echo 'unchecked';}else{echo 'checked';}?>>
                                                            <label class="form-check-label checkboxtext"
                                                                for="inlineRadio2">No</label>
                                                        </div>
                                                    <input type="hidden" id="assigned_product" name="assigned_product" value="0">
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputTelephone" class="col-sm-3 col-form-label">Notes</label>
                                                    <div class="col-sm-9">
                                                        <textarea class="form-control textareaInput" name="notes"
                                                            id="notes" rows="3" placeholder="Site Notes"><?php if(isset($customer)){echo $customer->notes;}?></textarea>
                                                    </div>
                                                </div>
                                                <div class="mb-3 row">
                                                    <label for="inputMobile" class="col-sm-3 col-form-label">Dflt Products
                                                        Tax</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="product_tax" name="product_tax">
                                                            <option selected disabled>Please Select</option>
                                                            <?php foreach($tax as $tax_val){?>
                                                                <option value="{{$tax_val->id}}" <?php if(isset($customer) && $customer->product_tax == $tax_val->id){echo 'selected';}?>>{{$tax_val->name}}</option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-3 row">
                                                    <label for="inputCountry" class="col-sm-3 col-form-label">Dflt Services
                                                        Tax</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions"
                                                            id="service_tax" name="service_tax">
                                                            <option selected disabled>Please Select</option>
                                                            <?php foreach($tax as $tax_val1){?>
                                                                <option value="{{$tax_val1->id}}" <?php if(isset($customer) && $customer->service_tax == $tax_val1->id){echo 'selected';}?>>{{$tax_val1->name}}</option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>


                                <div class="newJobForm mt-4">
                                    <label class="upperlineTitle">Customer Message</label>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="mb-3 row">
                                                <label for="inputCountry" class="col-sm-2 col-form-label">Show
                                                    Message</label>
                                                <div class="col-sm-10">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="show_msg" name="show_msg" value="0" <?php if(isset($customer) && $customer->show_msg == 1){echo 'checked';}else{echo 'unchecked';}?>>
                                                        <label class="form-check-label checkboxtext" for="checkalrt">Yes,
                                                            show
                                                            the
                                                            message</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="" class="col-md-2 col-form-label">Message</label>
                                                <div class="col-md-10">
                                                    <textarea class="form-control textareaInput" name="msg"
                                                        id="msg" rows="3" placeholder="Site Notes"><?php if(isset($customer) && $customer->msg !=''){echo $customer->msg;}?></textarea>
                                                </div>
                                            </div>
                                            <div class="mb-3 row">
                                                <label for="" class="col-md-2 col-form-label">Section</label>
                                                <div class="col-md-10">
                                                    <select class="form-control editInput selectOptions"
                                                            id="section_id" name="section_id[]" multiselect-search="true" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">
                                                            <?php if(isset($customer) && $customer->section_id !=''){ $section_id=explode(',',$customer->section_id);?>
                                                            <option value="1" <?php if (in_array(1, $section_id)){echo 'selected';}?> selected>Quotes</option>
                                                            <option value="2" <?php if (in_array(2, $section_id)){echo 'selected';}?> selected>Job</option>
                                                            <option value="3" <?php if (in_array(3, $section_id)){echo 'selected';}?> selected>Invoice</option>
                                                            <?php }else{?>
                                                                <option value="1" selected>Quotes</option>
                                                                <option value="2" selected>Job</option>
                                                                <option value="3" selected>Invoice</option>
                                                            <?php }?>
                                                        </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End  off newJobForm -->
                            </form>
                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Additional Contacts</label>
                                <div class="row">
                                    <div class="col-sm-12 mb-3 mt-2">
                                        <div class="jobsection">
                                            <a href="javascript:void(0)" onclick="get_modal(4)" class="profileDrop">Add Contact</a>
                                            <a href="#" class="profileDrop">Export</a>
                                            <a href="#" class="profileDrop">Import</a>
                                            <label class="col-form-label"><a href="#!">Click here </a>to download import
                                                template</label>
                                            <label>
                                            <div class="d-inline-flex align-items-center ">
                                                    <div class="nav-item dropdown">
                                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                            Bulk Action
                                                        </a>
                                                        <div class="dropdown-menu fade-up m-0">
                                                            <!-- <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#SiteModel" class="dropdown-item modal_dataSite" >Edit Details</a>
                                                            <hr class="dropdown-divider"> -->
                                                            <a href="javascript:void(0)" class="dropdown-item" id="deleteSelectedContactRows">Delete</a>
                                                            <!-- <hr class="dropdown-divider">
                                                            <a href="javascript:void(0)" class="dropdown-item" >Manage Document/Equipments</a> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style=" width:60px;"><input type="checkbox" id="selectAllContact"></th>
                                                        <th>Contact Name</th>
                                                        <th>Customer Job Title </th>
                                                        <th>Email</th>
                                                        <th>Telephone </th>
                                                        <th>Mobile </th>
                                                        <th>Address </th>
                                                        <th>City </th>
                                                        <th>Country </th>
                                                        <th>Postcode </th>
                                                        <th>Default Billing </th>
                                                        <th> </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="contact_result">
                                                
                                                </tbody>
                                            </table>
                                            <div id="pagination-controls-contact"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End  off newJobForm -->

                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Customer Sites</label>
                                <div class="row">
                                    <div class="col-sm-12 mb-3 mt-2">
                                        <div class="jobsection">
                                            <a href="javascript:void(0)" onclick="get_modal(5)" class="profileDrop">Add Site</a>
                                            <a href="#" class="profileDrop">Export</a>
                                            <a href="#" class="profileDrop">Import</a>
                                            <label class="col-form-label"><a href="#!">Click here </a>to download import
                                                template</label>
                                            <label>
                                                <div class="d-inline-flex align-items-center ">
                                                    <div class="nav-item dropdown">
                                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                            Bulk Action
                                                        </a>
                                                        <div class="dropdown-menu fade-up m-0">
                                                            <!-- <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#SiteModel" class="dropdown-item modal_dataSite" >Edit Details</a>
                                                            <hr class="dropdown-divider"> -->
                                                            <a href="javascript:void(0)" class="dropdown-item" id="deleteSelectedRows">Delete</a>
                                                            <!-- <hr class="dropdown-divider">
                                                            <a href="javascript:void(0)" class="dropdown-item" >Manage Document/Equipments</a> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style=" width:60px;"><input type="checkbox" id="selectAll"></th>
                                                        <th>Site Name </th>
                                                        <th>Contact Name</th>
                                                        <th>Company</th>
                                                        <th>Email </th>
                                                        <th>Telephone </th>
                                                        <th>Mobile </th>
                                                        <th>Address </th>
                                                        <th>City </th>
                                                        <th>Country </th>
                                                        <th>Postcode </th>
                                                        <th>Region </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="site_result">
                                                   
                                                </tbody>
                                            </table>
                                            <div id="pagination-controls-site"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End  off newJobForm -->

                            <div class="newJobForm mt-4">
                                <label class="upperlineTitle">Customer Logins</label>
                                <div class="row">
                                    <div class="col-sm-12 mb-3 mt-2">
                                        <div class="jobsection">
                                            <a href="javascript:void(0)" onclick="get_modal(6)" class="profileDrop">Add Login</a>

                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="productDetailTable">
                                            <table class="table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Full Name </th>
                                                        <th>Username</th>
                                                        <th>Email</th>
                                                        <th>Telephone </th>
                                                        <th>Last Login </th>
                                                        <th>Status </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="login_result">
                                                    <?php foreach($login as $k=>$logv){?>
                                                        <tr>
                                                            <td>{{++$k}}</td>
                                                            <td>{{$logv->name}}</td>
                                                            <td>{{$logv->email}}</td>
                                                            <td>{{$logv->email}}</td>
                                                            <td>{{$logv->telephone}}</td>
                                                            <td>{{$logv->last_login}}</td>
                                                            <td><?php echo($logv->status == 1)?"Active":"In-active";?></td>
                                                            <td>
                                                            <img src="{{url('public/frontEnd/jobs/images/pencil.png')}}" height="16px" alt="" data-bs-toggle="modal" data-bs-target="#LoginModel" class="modal_datalogin" data-id="{{ $logv->id }}" data-email="{{ $logv->email }}" data-password_type="{{ $logv->password_type }}" data-name="{{$logv->name}}" data-telephone="{{$logv->telephone}}" data-access_rights="{{$logv->access_rights}}" data-projects="{{$logv->projects}}" data-notes="{{$logv->notes}}" data-last_login="{{$logv->last_login}}" data-status="{{$logv->status}}">&nbsp;
                                                            <img src="{{url('public/frontEnd/jobs/images/delete.png')}}" alt="" class="login_delete" data-delete="{{$logv->id}}">
                                                            </td>
                                                    </tr>
                                                    <?php }?>
                                                </tbody>
                                            </table>
                                            <div id="pagination-controls-login"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- End  off newJobForm -->


                        </div> <!-- End  off col-12 -->

                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="pageTitleBtn">
                            <a href="javascript:void(0)" class="profileDrop" onclick="save_all_datawithredirect()"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                                <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>

                            </div>
                        </div>

                    </div>
                </div>


            </div>
        </section>
        <!-- Customer type add Modal -->
<div class="modal fade" id="cutomer_type_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Add Customer Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="customer_type_form">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Customer Type <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="customer_type_name" class="form-control editInput" id="customer_type_name" value="" placeholder="Customer Type">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="customer_type_status" name="customer_type_status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_customer_type()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end here -->
     <!-- Job title Modal start -->
     <div class="modal fade" id="job_title_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Job Title - Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="job_title_form">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Job Title <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="job_title_name" class="form-control editInput" id="job_title_name" value="" placeholder="Job Title">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="job_title_status" name="job_title_status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_job_title()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
     <!-- end here -->
      <!-- Region Modal start here -->
      <div class="modal fade" id="region_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Add Region</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="region_form">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Region <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="region_name" class="form-control editInput" id="region_name" value="" placeholder="Region">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="region_status" name="region_status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_region()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
      <!-- end here -->
       <!-- Contact Model -->
       <div class="modal fade" id="ContactModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Customer Contact</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputName" class="col-sm-3 col-form-label">&emsp;Customer</label>
                            <div class="col-sm-9">
                                <p id="customer_name" class="customer_name"><?php if(isset($customer)){echo $customer->name;}?></p>
                            </div>
                        </div>
                        <div class="modal-body">
                            <form role="form" id="contact_form">
                                <input type="hidden" name="contact_id" id="contact_id">
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Default Billing</label>
                                    <div class="col-sm-9">
                                        <input type="radio" class="form-check-input" name="billing" id="billing1" value="1"> Yes
                                        <input type="radio" class="form-check-input" name="billing" id="billing0" value="0" checked> No
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Contact Name <span class="radStar">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="concontact_name" id="concontact_name">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputCustomer" class="col-sm-3 col-form-label">Job Title(Position)</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="contact_title_id" name="contact_title_id">
                                            <option selected disabled>Select Job Title</option>
                                            <?php foreach($job_title as $titlev){?>
                                            <option value="{{$titlev->id}}">{{$titlev->name}}</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_email" id="contact_email" onblur="getemail(2)">
                                        <span id="emailErr2" style="color: red;"></span>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Telephone</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_telephone" id="contact_telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Mobile</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_mobile" id="contact_mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Fax</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_fax" id="contact_fax">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Address Details</label>
                                    <div class="col-sm-9">
                                        Same as default
                                        <input type="checkbox" class="form-check-input" name="defaultaddcheck" id="defaultaddcheck">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Address <span class="radStar">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput" name="contact_address" id="contact_address" rows="3" placeholder="Site Notes"></textarea>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">City</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_city" id="contact_city">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Country</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_country" id="contact_country">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Postcode</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_postcode" id="contact_postcode">
                                    </div>
                                </div>
                                <div class="mb-3 row field">
                                        <label for="inputCountry" class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" name="contact_country_code" id="contact_country_code" required>
                                                <option value="" selected disabled>None</option>
                                                <?php foreach($country as $country_idc): ?>
                                                    <?php foreach($country_idc->currencies as $currency1): ?>
                                                        <option value="<?php echo $country_idc->id; ?>">
                                                            <?php echo $country_idc->name; ?> (<?php echo $currency1->currency_code; ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <div class="pageTitleBtn p-0">
                                <button type="button" class="profileDrop" id="addNotesType" onclick="save_contact()">Save</button>
                                <!-- <button type="button" class="profileDrop" onclick="SaveAndClose(1)">Save & Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Site Model start -->
            <div class="modal fade" id="SiteModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Site Address</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="mt-2 mb-0 text-center" id="message_site"></div>
                        </div>
                        <div class="modal-body">
                            <form role="form" id="site_form">
                                <input type="hidden" id="site_id" name="site_id">
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                            <div class="col-sm-9">
                                                <p id="customer_name" class="customer_name"><?php if(isset($customer)){echo $customer->name;}?></p>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Site Name <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_name" id="site_name">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Contact Name <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_contact_name" id="site_contact_name">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputCustomer" class="col-sm-3 col-form-label">Job Title(Position)</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput selectOptions" id="site_title_id" name="site_title_id">
                                                    <option selected disabled>Select Job Title</option>
                                                    <?php foreach($job_title as $titlev){?>
                                                    <option value="{{$titlev->id}}">{{$titlev->name}}</option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Company Name</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="company_name" id="company_name">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Email</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_email" id="site_email" onblur="getemail(3)">
                                                <span id="emailErr3" style="color: red;"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Telephone</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_telephone" maxlength="10" id="site_telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Mobile</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" maxlength="10" name="site_mobile" id="site_mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Fax</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_fax" id="site_fax">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <div class="mb-3 row">
                                            <label for="inputCustomer" class="col-sm-3 col-form-label">Region</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput selectOptions" id="site_region" name="site_region">
                                                    <option selected disabled>None</option>
                                                    <?php foreach($region as $site_region){?>
                                                            <option value="{{$site_region->id}}" >{{$site_region->title}}</option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Address <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control textareaInput" name="site_address" id="site_address" rows="3" placeholder="Site Notes"></textarea>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">City</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_city" id="site_city">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Country</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_country" id="site_country">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Postcode</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="site_postcode" id="site_postcode">
                                            </div>
                                        </div>
                                        <div class="mb-3 row field">
                                            <label for="inputCountry" class="col-sm-3 col-form-label">Country</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput selectOptions" name="site_country_id" id="site_country_id" required>
                                                    <option value="" selected disabled>None</option>
                                                    <?php foreach($country as $country_ids){?>
                                                        <option value="{{$country_ids->id}}">{{$country_ids->name}} ({{$country_ids->code}})</option>
                                                    <?php }?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3 row field">
                                            <label for="inputCountry" class="col-sm-3 col-form-label">Default Catalogue</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput selectOptions" name="site_catalogue_id" id="site_catalogue_id" required>
                                                    <option value="" selected disabled>None</option>
                                                    <option value="1">General Catalogue</option>
                                                    <option value="2">Custome Catalogue</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 row field">
                                    <label for="inputCountry" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                        <textarea name="site_note" id="site_note" rows="3" class="form-control textareaInput"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <div class="pageTitleBtn p-0">
                                <button type="button" class="profileDrop" id="addNotesType" onclick="save_site()">Save</button>
                                <!-- <button type="button" class="profileDrop" onclick="SaveAndClose(2)">Save & Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Login Model start -->
            <div class="modal fade" id="LoginModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Add Customer Login</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputName" class="col-sm-3 col-form-label">&emsp;Customer</label>
                            <div class="col-sm-9">
                                <p id="customer_name" class="customer_name"><?php if(isset($customer)){echo $customer->name;}?></p>
                            </div>
                        </div>
                        <div class="modal-body">
                            <form role="form" id="login_form">
                                <input type="hidden" id="login_id" name="login_id">
                            <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Email <span class="radStar">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="login_email" id="login_email">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Password Type <span class="radStar">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="radio" class="form-check-input" name="password_type" id="password_type1"> Generate Now
                                        <input type="radio" class="form-check-input" name="password_type" id="password_type2" checked> Email Password
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Name <span class="radStar">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="login_name" id="login_name">
                                    </div>
                                </div>
                                
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Telephone</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="login_telephone" id="login_telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    </div>
                                </div>
                                <div class="mb-3 row field">
                                    <label for="inputCountry" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" name="login_status" id="login_status" required>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Access Rights</label>
                                    <div class="col-sm-9">
                                        <input type="checkbox" id="login_check" name="login_check" value="1" class="login_check form-check-input"> Quotes
                                        <input type="checkbox" id="login_check" name="login_check" value="2" class="login_check form-check-input"> Jobs
                                        <input type="checkbox" id="login_check" name="login_check" value="3" class="login_check form-check-input"> Invoices
                                        <input type="checkbox" id="login_check" name="login_check" value="4" class="login_check form-check-input"> File Manager
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Projects</label>
                                    <div class="col-sm-9">
                                        <input type="radio" class="form-check-input" name="project" id="project1" checked> All
                                        <input type="radio" class="form-check-input" name="project" id="project2"> Customise
                                    </div>
                                </div>
                                
                                <div class="mb-3 row field">
                                    <label for="inputCountry" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-sm-12">
                                        <textarea name="login_note" id="login_note" rows="3" class="form-control textareaInput"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <div class="pageTitleBtn p-0">
                                <button type="button" class="profileDrop" id="addNotesType" onclick="save_login()">Save</button>
                                <!-- <button type="button" class="profileDrop" onclick="SaveAndClose(3)">Save & Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Modal End -->
<script type="text/javascript" src="{{url('public/frontEnd/jobs/js/multiselect.js')}}"></script>
<script>
    function get_modal(id){
        var key=$("#id").val();
        if(id == 4 || id == 5 || id == 6){
            if(key == ''){
                alert("Please save Customer first");
                return false;
            }else{
                if(id == 4){
                    $('#contact_form')[0].reset();
                    $("#ContactModel").modal('show');
                }else if(id == 5){
                    $('#site_form')[0].reset();
                    $("#SiteModel").modal('show');
                }else if(id == 6){
                    $('#login_form')[0].reset();
                    $("#LoginModel").modal('show');
                }
                
            }
        }else if(id == 1){
            $("#customer_type_form")[0].reset();
            $("#cutomer_type_modal").modal('show');
        }else if(id == 2){
            $("#job_title_form")[0].reset();
            $("#job_title_modal").modal('show');
        }else if(id == 3){
            $("#region_form")[0].reset();
            $("#region_modal").modal('show');
        }
    }
    function save_all_datawithredirect(){
        save_all_data('yes');
    }
   function save_all_data(text){
    var name=$("#name").val();
    var contact_name=$("#contact_name").val();
    var address=$("#address").val();
    if(name == ''){
        $("#name").css('border','1px solid red');
        $(window).scrollTop($('#name').position().top);
        return false;
    }else if(contact_name == ''){
        $("#name").css('border','');
        $("#contact_name").css('border','1px solid red');
        $(window).scrollTop($('#contact_name').position().top);
        return false;
    }else if(address == ''){
        $("#contact_name").css('border','');
        $("#address").css('border','1px solid red');
        $(window).scrollTop($('#address').position().top);
        return false;
    }else{
        $("#name").css('border','');
        $("#contact_name").css('border','');
        $("#address").css('border','');
        var token='<?php echo csrf_token();?>'
        $.ajax({
            type: "POST",
            url: "{{url('/customer_add_edit_save')}}",
            data: new FormData($("#customerForm")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                if(data.vali_error){
                    alert(data.vali_error);
                    $("#email").css('border','1px solid red');
                    return false;
                }else{
                    $("#email").css('border','');
                    $('.alert').show();
                    setTimeout(function() {
                        $('.alert').hide();
                        if(text == 'yes'){
                            window.location.href ="<?=url('customers?list_mode=ACTIVE')?>";
                        }else{
                            window.location.href ="<?=url('customer_add_edit?key=')?>"+data.id;
                        }
                    }, 3000);
                }
                
                
                // console.log(data.id);
                // $("#customer_id").val(data.id);
                // $("#site_customer_id").val(data.id);
                // $("#login_customer_id").val(data.id);
                // $(".customer_name").text(data.name);
            }
        });
    }
    
   }
   function save_customer_type(){
       var token='<?php echo csrf_token();?>'
       var title=$("#customer_type_name").val();
       var status=$("#customer_type_status").val();
       var home_id=$("#home_id").val();
       if(title == ''){
        $("#customer_type_name").css('border','1px solid red');
        return false;
       }else {
            $.ajax({
                type: "POST",
                url: "{{url('/save_customer_type')}}",
                data: {title:title,status:status,home_id:home_id,_token:token},
                success: function(data) {
                    console.log(data);
                    if($.trim(data) == 'error'){
                        alert("Something went wrong");
                        return false;
                    }else{
                        $("#cutomer_type_modal").modal('hide');
                        $('.get_customer_result').append(data);
                        // window.location.reload();
                    }
                    $("#cutomer_type_modal").modal('hide');
                    
                }
            });
       }
       
    }
    function save_job_title(){
        var token='<?php echo csrf_token();?>'
        var name=$("#job_title_name").val();
        var status=$("#job_title_status").val();
        var home_id=$("#home_id").val();
        if(name == ''){
        $("#job_title_name").css('border','1px solid red');
        return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{url('/save_job_title')}}",
                data: {name:name,status:status,home_id:home_id,_token:token},
                success: function(data) {
                    console.log(data);
                    
                    $("#job_title_modal").modal('hide');
                    $('.get_job_title_result').append(data);
                    // window.location.reload();
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
    function save_region(){
        var token='<?php echo csrf_token();?>'
        var title=$("#region_name").val();
        var status=$("#region_status").val();
        var home_id=$("#home_id").val();
        if(title == ''){
        $("#region_name").css('border','1px solid red');
        return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{url('/save_region')}}",
                data: {title:title,status:status,home_id:home_id,_token:token},
                success: function(data) {
                    console.log(data);
                    // return false;
                    if($.trim(data) == 'error'){
                        alert("Something went wrong");
                        return false;
                    }else{
                        $("#region_modal").modal('hide');
                        $('.get_region_result').append(data);
                        // window.location.reload();
                    }
                    $("#region_modal").modal('hide');
                }
            });
        }
    }
    document.querySelectorAll('.assigned_product').forEach(function(element) {
        element.addEventListener('change', function() {
            if (this.checked) {
                this.value = 1;
                document.getElementById('assigned_product').value = 1;
            } else {
                document.getElementById('assigned_product').value = 0;
            }
        });
    });
</script>
<script>
    function save_contact(){
        var token='<?php echo csrf_token();?>'
        var default_billing;
        if ($('#billing1').is(':checked')) {
            default_billing=1;
        }else {
            default_billing=0;
        }
        var userType=1;
        var contact_name=$("#concontact_name").val();
        var customer_id=$("#id").val();
        var job_title_id=$('#contact_title_id').val();
        var email=$("#contact_email").val();
        var telephone=$("#contact_telephone").val();
        var mobile=$("#contact_mobile").val();
        var fax=$("#contact_fax").val();
        var address=$("#contact_address").val();
        var city=$("#contact_city").val();
        var country=$("#contact_country").val();
        var postcode=$("#contact_postcode").val();
        var country_id=$("#contact_country_code").val();
        var id=$("#contact_id").val();
        var emailError=$("#emailErr2").text();
        if(contact_name == ''){
            $("#concontact_name").css('border','1px soild red');
            return false;
        }else if(address == ''){
            $("#concontact_name").css('border','');
            $("#address").css('border','1px soild red');
            return false;
        }else if(emailError.length>0){
            alert(emailError);
            return false;
        }else{
                $.ajax({
                type: "POST",
                url: "{{url('/save_contact')}}",
                data: {userType:userType,id:id,default_billing:default_billing,contact_name:contact_name,customer_id:customer_id,job_title_id:job_title_id,
                    email:email,telephone:telephone,mobile:mobile,fax:fax,address:address,city:city,country:country,postcode:postcode,
                    country_id:country_id,_token:token},
                success: function(response) {
                    console.log(response);
                    if(response.vali_error){
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $("#ContactModel").modal('hide');
                        location.reload();
                    }else{
                        alert("Something went wrong! Please try later");
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
        
    }
    function GetAllContact(userType,id,pageUrl){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {
                userType:userType,id: id,_token:token
            },
            success: function(response) {
                // console.log(response);
                // return false;
                var data = response.data;
                var paginationContact = response.pagination;
                var tableBody = $("#contact_result"); 
                tableBody.empty();
                var html='';
                if(data.length>0){
                    data.forEach(function(item) {
                        
                        html+= '<tr>' +
                            '<td><input type="checkbox" id="" class="checkboxContactId text-center" value="'+item.id+'">'+
                            '<td>' + item.contact + '</td>' +
                            '<td>' + item.job_title + '</td>' +       
                            '<td>' + item.email + '</td>' +       
                            '<td>' + item.telephone + '</td>' +        
                            '<td>' + item.mobile + '</td>' +
                            '<td>' + item.address + '</td>' +
                            '<td>' + item.city + '</td>' +
                            '<td>' + item.country + '</td>' +
                            '<td>' + item.postcode + '</td>' +
                            '<td>' + (item.default_billing == 1 ? "Yes" : "No") + '</td>' +
                            '<td>' +
                                    '<img src="public/frontEnd/jobs/images/pencil.png" height="16px" alt="Edit" ' +
                                    'class="modal_dataFetch" data-bs-toggle="modal" data-bs-target="#ContactModel" ' +
                                    'data-id="' + item.id + '" ' +
                                    'data-title="' + item.contact + '" ' +
                                    'data-job_title="' + (item.job_title ?? '') + '" ' +
                                    'data-email="' + item.email + '" ' +
                                    'data-telephone="' + item.telephone + '" ' +
                                    'data-mobile="' + item.mobile + '" ' +
                                    'data-address="' + item.address + '" ' +
                                    'data-city="' + item.city + '" ' +
                                    'data-country="' + item.country + '" ' +
                                    'data-postcode="' + item.postcode + '" ' +
                                    'data-default_billing="' + item.default_billing + '" ' +
                                    'data-fax="' + (item.fax ?? '') + '" ' +
                                    'data-country_id="' + item.country_id + '">' +
                                    '&nbsp;' +
                                    '<img src="public/frontEnd/jobs/images/delete.png" alt="Delete" class="contact_delete" data-delete="' + item.id + '">' +
                                    '</td>' +
                            '</tr>';
                    });
                }else{
                    html+='<tr> <td colspan="12"> <label class="red_sorryText">Sorry, no records to show</label> </td> </tr>';
                    
                }
                tableBody.html(html);
                var paginationControlsContact = $("#pagination-controls-contact");
                paginationControlsContact.empty();
                if (paginationContact.prev_page_url) {
                    paginationControlsContact.append('<button type="button" class="profileDrop" onclick="GetAllContact(1,' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
                }
                if (paginationContact.next_page_url) {
                    paginationControlsContact.append('<button type="button" class="profileDrop" onclick="GetAllContact(1,' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                location.reload();
            }
        });
    }
    $("#show_msg").change(function() {
        if ($('#show_msg').is(':checked')) {
            $("#show_msg").val(1);
        }else{
            $("#show_msg").val(0);
        }
    });
    $("#defaultaddcheck").change(function() {
        var check;
        if ($('#defaultaddcheck').is(':checked')) {
            check=1;
        }else {
            check=0;
        }
        var token='<?php echo csrf_token();?>'
        var customer_id=$("#id").val();
        var userType=1;
        $.ajax({
            type: "POST",
            url: "{{url('/default_address')}}",
            data: {userType:userType,check:check,customer_id:customer_id,_token:token},
            success: function(data) {
                console.log(data);
                if(check == 1){
                    $("#contact_address").val(data.details.address);
                    $("#contact_city").val(data.details.city);
                    $("#contact_country").val(data.details.country);
                    $("#contact_postcode").val(data.details.postal_code);
                }else{
                    $("#contact_address").val('');
                    $("#contact_city").val('');
                    $("#contact_country").val('');
                    $("#contact_postcode").val('');
                }
                $("#contact_country_code").html(data.reslut);
            }
        });
    });
    function save_site(){
        var token='<?php echo csrf_token();?>'
        var site_name=$("#site_name").val();
        var contact_name=$("#site_contact_name").val();
        var customer_id=$("#id").val();
        var title_id=$('#site_title_id').val();
        var company_name=$("#company_name").val();
        var email=$("#site_email").val();
        var telephone=$("#site_telephone").val();
        var mobile=$("#site_mobile").val();
        var fax=$("#site_fax").val();
        var region=$("#site_region").val();
        var address=$("#site_address").val();
        var city=$("#site_city").val();
        var country=$("#site_country").val();
        var catalogue=$("#site_catalogue_id").val();
        var post_code=$("#site_postcode").val();
        var country_id=$("#site_country_id").val();
        var notes=$("#site_note").val();
        var id=$("#site_id").val();
        var emailErr3=$("#emailErr3").text();
        if(site_name == ''){
            $("#site_name").css('border','1px solid red');
            // $(window).scrollTop($('#site_name').position().top);
            return false;
        }else if(contact_name == ''){
            $("#site_name").css('border','');
            $("#site_contact_name").css('border','1px solid red');
            // $(window).scrollTop($('#site_contact_name').position().top);
            return false;
        }else if(address == ''){
            $("#site_contact_name").css('border','');
            $("#site_address").css('border','1px solid red');
            // $(window).scrollTop($('#site_address').position().top);
            return false;
        }else if(emailErr3.length >0){
            alert(emailErr3);
            return false;
        }else{
            // alert(123);return false;
            $("#site_name").css('border','');
            $("#site_contact_name").css('border','');
            $("#site_address").css('border','');
            $.ajax({
            type: "POST",
            url: "{{url('/save_site')}}",
            data: {id:id,site_name:site_name,contact_name:contact_name,customer_id:customer_id,title_id:title_id,company_name:company_name,
                email:email,telephone:telephone,mobile:mobile,fax:fax,region:region,address:address,city:city,country:country,
                catalogue:catalogue,post_code:post_code,country_id:country_id,notes:notes,_token:token},
                success: function(response) {
                    console.log(response);
                    if(response.vali_error){
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_site').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_site').removeClass('success-message').text('').hide();
                            $("#SiteModel").modal('hide');
                            getAllSite(customer_id,'{{ url("getAllSite") }}');
                        }, 3000);
                    }else if(response.success === false){
                        $('#message_site').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                    }  
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    console.log('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
        
    }
    function getAllSite(customer_id,pageUrl = '{{ url("getAllSite") }}'){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {customer_id: customer_id,_token:token},
            success: function(response) {
                // console.log(response);
                // return false;
                var paginationSite = response.pagination;
                var data = response.data;
                // console.log(data);
                const tbody = $('#site_result');
                tbody.empty();
                data.forEach(site => {
                    tbody.append(`
                        <tr>
                            <td><input type="checkbox" id="" class="delete_checkbox text-center" value="`+site.id+`"></td>
                            <td>${site.site_name}</td>
                            <td>${site.contact_name}</td>
                            <td>${site.company_name || ''}</td>
                            <td>${site.email || ''}</td>
                            <td>${site.telephone || ''}</td>
                            <td>${site.mobile || ''}</td>
                            <td>${site.address}</td>
                            <td>${site.city || ''}</td>
                            <td>${site.country || ''}</td>
                            <td>${site.post_code || ''}</td>
                            <td>${site.site_regionName || ''}</td>
                            <td>
                                <div class="d-inline-flex align-items-center ">
                                    <div class="nav-item dropdown">
                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                            Action
                                        </a>
                                        <div class="dropdown-menu fade-up m-0">
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#SiteModel" class="dropdown-item modal_dataSite" data-id="` +site.id+ `" data-site_name="` +site.site_name+ `" data-contact_name="` +site.contact_name+ `" data-title_id="` +site.title_id+ `" data-company_name="` +site.company_name+ `" data-email="` +site.email+ `" data-telephone="` +site.telephone+ `" data-mobile="` +site.mobile+ `" data-fax="` +site.fax+ `" data-region="` +site.region+ `" data-address="` +site.address+ `" data-city="` +site.city+ `" data-country="` +site.country+ `" data-post_code="` +site.post_code+ `" data-country_id="` +site.country_id+ `" data-catalogue="` +site.catalogue+ `" data-notes="` +site.notes+ `">Edit Details</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" class="dropdown-item site_delete" data-delete="` +site.id+ `">Delete</a>
                                            <hr class="dropdown-divider">
                                            <a href="javascript:void(0)" class="dropdown-item" data-id="` +site.id+ `">Manage Document/Equipments</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                });
                var paginationControlsSite = $("#pagination-controls-site");
                paginationControlsSite.empty();
                if (paginationSite.prev_page_url) {
                    paginationControlsSite.append('<button type="button" class="profileDrop" onclick="getAllSite(' + customer_id + ', \'' + paginationSite.prev_page_url + '\')">Previous</button>');
                }
                if (paginationSite.next_page_url) {
                    paginationControlsSite.append('<button type="button" class="profileDrop" onclick="getAllSite(' + customer_id + ', \'' + paginationSite.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                location.reload();
            }
        });
    }
    function save_login(){
        var token='<?php echo csrf_token();?>'
        var email=$('#login_email').val();
        var customer_id=$("#id").val();
        var password_type;
        if ($('#password_type1').is(':checked')) {
            password_type = 1;
        } else {
            password_type = 2;
        }
        var name=$("#login_name").val();
        var telephone=$("#login_telephone").val();
        var status=$("#login_status").val();
       
        var access_rights=[];
        $('.login_check').each(function(){
            if ($(this).is(':checked')) {
                access_rights.push($(this).val());
            } 
        });
        access_rights=access_rights;
        var projects;
        if ($('#project1').is(':checked')) {
            projects = 1;
        } else {
            projects = 2;
        }
        var notes=$("#login_note").val();
        var id=$('#login_id').val();
        if(email == ''){
            $("#login_email").css('border','1px solid red');
            return false;
        }else if(name == ''){
            $("#login_email").css('border','');
            $("#login_name").css('border','1px solid red');
            return false;
        }else{
            $("#login_email").css('border','');
            $("#login_name").css('border','');
                $.ajax({
                type: "POST",
                url: "{{url('/save_login')}}",
                data: {id:id,email:email,customer_id:customer_id,password_type:password_type,name:name,telephone:telephone,status:status,
                    access_rights:access_rights,projects:projects,notes:notes,_token:token},
                success: function(data) {
                    console.log(data);
                    if(data.success === true){
                        $("#LoginModel").modal('hide');
                        location.reload();
                    }else{
                        alert("Someting went wrong");
                    } 
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    console.log('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
        
    }
    function getAllLogin(customer_id,pageUrl = '{{ url("getAllLogin") }}'){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            url: pageUrl,
            method: 'POST',
            data: {customer_id: customer_id,_token:token},
            success: function(response) {
                console.log(response);
                // return false;
                var paginationLogin = response.pagination;
                var data = response.data.data;
                // console.log(data);
                const tbody = $('#login_result');
                tbody.empty();
                var loginCount=1;
                data.forEach(login => {
                    const status=(login.status == 1)?'Active':'Inactive';
                    tbody.append(`
                        <tr>
                            <td>${loginCount}</td>
                            <td>${login.name}</td>
                            <td>${login.email}</td>
                            <td>${login.email || ''}</td>
                            <td>${login.telephone || ''}</td>
                            <td></td>
                            <td>${status}</td>
                            <td>
                                <img src="public/frontEnd/jobs/images/pencil.png" height="16px" alt="Edit" class="modal_datalogin" data-bs-toggle="modal" data-bs-target="#LoginModel" data-id="${login.id }" data-email="${login.email}" data-password_type="${ login.password_type}" data-name="${login.name}" data-telephone="${login.telephone}" data-access_rights="${login.access_rights}" data-projects="${login.projects}" data-notes="${login.notes}" data-last_login="${login.last_login}" data-status="${login.status}">&nbsp;
                                <img src="public/frontEnd/jobs/images/delete.png" alt="Delete" class="login_delete" data-delete="${login.id}">
                            </td>
                        </tr>
                    `);
                    loginCount++;
                });
                var paginationControlsLogin = $("#pagination-controls-login");
                paginationControlsLogin.empty();
                if (paginationLogin.prev_page_url) {
                    paginationControlsLogin.append('<button type="button" class="profileDrop" onclick="getAllLogin(' + customer_id + ', \'' + paginationLogin.prev_page_url + '\')">Previous</button>');
                }
                if (paginationLogin.next_page_url) {
                    paginationControlsLogin.append('<button type="button" class="profileDrop" onclick="getAllLogin(' + customer_id + ', \'' + paginationLogin.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                location.reload();
            }
        });
    }
</script>
<script>
    $(document).on('click','.contact_delete', function() {
        var id = $(this).data('delete');
        if (confirm("Are you sure you want to delete this row?")) {
            $(this).closest('tr').remove();
            var token='<?php echo csrf_token();?>'
            $.ajax({
            type: "POST",
            url: "{{url('/delete_contact')}}",
            data: {id:id,_token:token},
            success: function(data) {
                console.log(data);
            }
        });
        }
    });

    $('.site_delete').on('click',function(){
        var id = $(this).data('delete');
        if (confirm("Are you sure you want to delete this row?")) {
            $(this).closest('tr').remove();
            var token='<?php echo csrf_token();?>'
            $.ajax({
            type: "POST",
            url: "{{url('/delete_site')}}",
            data: {id:id,_token:token},
            success: function(data) {
                console.log(data);
            }
        });
        }
    });
    $(document).on('click','.login_delete', function(){
        var id = $(this).data('delete');
        if (confirm("Are you sure you want to delete this row?")) {
            $(this).closest('tr').remove();
            var token='<?php echo csrf_token();?>'
            $.ajax({
            type: "POST",
            url: "{{url('/delete_login')}}",
            data: {id:id,_token:token},
            success: function(data) {
                console.log(data);
            }
        });
        }
    });
</script>
<script>
    $(document).on('click','.modal_dataFetch', function() {
        var id = $(this).data('id');
        var title = $(this).data('title');
        var job_title = $(this).data('job_title');
        var email = $(this).data('email');
        var telephone = $(this).data('telephone');
        var mobile = $(this).data('mobile');
        var address = $(this).data('address');
        var city = $(this).data('city');
        var country = $(this).data('country');
        var postcode = $(this).data('postcode');
        var default_billing = $(this).data('default_billing');
        var fax = $(this).data('fax');
        var country_id = $(this).data('country_id');
        // alert(default_billing)
        $("#billing" + default_billing).prop('checked', true);
        $('#contact_id').val(id);
        $('#concontact_name').val(title);
        $('#contact_title_id').val(job_title);
        $('#contact_email').val(email);
        $('#contact_telephone').val(telephone);
        $('#contact_mobile').val(mobile);
        $('#contact_fax').val(fax);
        $('#contact_address').val(address);
        $('#contact_city').val(city);
        $('#contact_country').val(country);
        $('#contact_postcode').val(postcode);
        $('#contact_country_code').val(country_id);
    });

    $(document).on('click','.modal_dataSite', function() {
        var id = $(this).data('id');
        var site_name = $(this).data('site_name');
        var contact_name = $(this).data('contact_name');
        var title_id = $(this).data('title_id');
        var company_name = $(this).data('company_name');
        var email = $(this).data('email');
        var telephone = $(this).data('telephone');
        var mobile = $(this).data('mobile');
        var fax = $(this).data('fax');
        var region = $(this).data('region');
        var address = $(this).data('address');
        var city = $(this).data('city');
        var country = $(this).data('country');
        var post_code = $(this).data('post_code');
        var country_id = $(this).data('country_id');
        var catalogue = $(this).data('catalogue');
        var notes = $(this).data('notes');
        
        $("#site_id").val(id);
        $("#site_name").val(site_name);
        $("#site_contact_name").val(contact_name);
        $("#site_title_id").val(title_id);
        $("#company_name").val(company_name);
        $("#site_email").val(email);
        $("#site_telephone").val(telephone);
        $("#site_mobile").val(mobile);
        $("#site_fax").val(fax);
        $("#site_region").val(region);
        $("#site_address").val(address);
        $("#site_city").val(city);
        $("#site_country").val(country);
        $("#site_postcode").val(post_code);
        $("#site_country_id").val(country_id);
        $("#site_catalogue_id").val(catalogue);
        $("#site_note").val(notes);
    });
    
    $(document).on('click','.modal_datalogin', function() {
        var id = $(this).data('id');
        var email = $(this).data('email');
        var name = $(this).data('name');
        var password_type = $(this).data('password_type');
        var telephone = $(this).data('telephone');
        var projects = $(this).data('projects');
        var notes = $(this).data('notes');
        var last_login = $(this).data('last_login');
        var status = $(this).data('status');
        var access_rights = $(this).data('access_rights');
        var access_rightsArray = access_rights.split(',');
        $('.login_check').each(function(){
            if (access_rightsArray.includes($(this).val())) {
                $(this).prop('checked', true); 
            } else {
                $(this).prop('checked', false);
            }
        });
        $("#login_id").val(id);
        $("#login_email").val(email);
        $("#login_name").val(name);
        $("#password_type"+password_type).prop('checked', true);
        $("#login_telephone").val(telephone);
        $("#login_status").val(status);
        $("#project"+projects).prop('checked', true);
        $("#login_note").val(notes);
    });
    
</script>
<script>
    function getemail(id)
  {
    var email;
    if(id == 1){
         email= $('#email').val();
    }else if(id == 2){
        email=$("#contact_email").val();
    }else if(id == 3){
        email=$("#site_email").val();
    }
    validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    if (email.search(validRegExp) == -1) 
    {
        $('#emailErr'+id).text("Please enter correct email address");
      return false;
    }else{
        $('#emailErr'+id).text("");
    }
  }
</script>
<script>
   $("#deleteSelectedRows").on('click', function() {
    let ids = [];
    
    $('.delete_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    if(ids.length == 0){
        alert("Please check the checkbox for delete");
    }else{
        if(confirm("Are you sure to delete?")){
            // console.log(ids);
            var token='<?php echo csrf_token();?>'
            var model='Constructor_customer_site';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    // console.log(data);
                    if(data){
                        location.reload();
                    }else{
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

$('.delete_checkbox').on('click', function() {
    if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
        $('#selectAll').prop('checked', true);
    } else {
        $('#selectAll').prop('checked', false);
    }
});

$("#deleteSelectedContactRows").on('click', function() {
    let ids = [];
    
    $('.checkboxContactId:checked').each(function() {
        ids.push($(this).val());
    });
    if(ids.length == 0){
        alert("Please check the checkbox for delete");
    }else{
        if(confirm("Are you sure to delete?")){
            // console.log(ids);
            var token='<?php echo csrf_token();?>'
            var model='Constructor_additional_contact';
            $.ajax({
                type: "POST",
                url: "{{url('/bulk_delete')}}",
                data: {ids:ids,model:model,_token:token},
                success: function(data) {
                    // console.log(data);
                    if(data){
                        location.reload();
                    }else{
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
$('.checkboxContactId').on('click', function() {
    if ($('.checkboxContactId:checked').length === $('.checkboxContactId').length) {
        $('#selectAllContact').prop('checked', true);
    } else {
        $('#selectAllContact').prop('checked', false);
    }
});
$("#selectAllContact").on('click',function(){
    $('.checkboxContactId').prop('checked', $(this).prop('checked'));
})
 </script>
 <script>
    $(document).ready(function(){
        var customer_id=$("#id").val();
        getAllSite(customer_id,pageUrl = '{{ url("getAllSite") }}');
        GetAllContact(1,customer_id,pageUrl = '{{ url("get_all_crm_customer_contacts") }}');
        getAllLogin(customer_id,pageUrl = '{{ url("getAllLogin") }}');
    });
 </script>
@include('frontEnd.salesAndFinance.jobs.layout.footer')