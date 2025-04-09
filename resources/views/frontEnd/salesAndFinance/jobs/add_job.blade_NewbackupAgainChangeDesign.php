@include('frontEnd.jobs.layout.header')

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>New Jobs</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                    <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>

                </div>
            </div>
        </div>
        <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
            <p>{{$task}} Successfully Done</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="progress-bar">
                    <div class="step">
                        <p>Customer Details</p>
                        <div class="bullet">
                            <span>1</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <div class="step">
                        <p>Site Details</p>
                        <div class="bullet">
                            <span>2</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <div class="step">
                        <p>Jobs Details</p>
                        <div class="bullet">
                            <span>3</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <div class="step">
                        <p>Product Details</p>
                        <div class="bullet">
                            <span>4</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <!-- <div class="step">
                                <p>Asset Details</p>
                                <div class="bullet">
                                    <span>5</span>
                                </div>
                                <div class="check fas fa-check"></div>
                            </div> -->
                    <div class="step">
                        <p>Appoinments</p>
                        <div class="bullet">
                            <span>5</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <div class="step">
                        <p>Notes</p>
                        <div class="bullet">
                            <span>6</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                    <div class="step">
                        <p>Attachments</p>
                        <div class="bullet">
                            <span>7</span>
                        </div>
                        <div class="check fas fa-check"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="newJobForm">


                    <div class="containerForm">

                        <div class="form-outer">
                            <form id="form_data">
                                @csrf
                                <input type="hidden" id="id" name="id">
                                <input type="hidden" id="home_id" name="home_id" value="{{$home_id}}">
                                <input type="hidden" id="user_id" name="user_id" value="{{Auth::user()->id}}">
                                <input type="hidden" id="last_job_id" name="last_job_id" value="<?php if (isset($last_job_id->id)) {
                                                                                                    echo $last_job_id->id;
                                                                                                } ?>">
                                <div class="page slide-page">
                                    <div class="title">Customer Details</div>

                                    <div class="mb-2 row field">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Customer <span
                                                class="radStar">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="customer_id" name="customer_id" required onchange="get_customer_details()">
                                                <option selected disabled>Select Customer</option>
                                                <?php foreach ($customers as $cust) { ?>
                                                    <option value="{{$cust->id}}">{{$cust->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-1">
                                            <a href="#!" class="formicon" id="openPopupButton"
                                                data-bs-toggle="modal" data-bs-target="#customerPop"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                        <div class="col-sm-1">
                                            <a href="#!" class="formicon"><i class="fa-solid fa-clock"></i></a>
                                        </div>
                                    </div>

                                    <!-- *********************************************************************** -->
 
                                    <div class="mb-2 row field">
                                        <label for="inputProject"
                                            class="col-sm-3 col-form-label">Project</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" required
                                                disabled id="project_id" name="project_id">
                                                <option selected>Select Customer First</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" id="" onclick="get_modal(4)"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-2 row field">
                                        <label for="inputProject"
                                            class="col-sm-3 col-form-label">Contact</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" required
                                                disabled id="contact_id" name="contact_id">
                                                <option selected>Select Customer First</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" id="" onclick="get_modal(5)"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>

                                    <div class="mb-2 row field">
                                        <label for="inputName" class="col-sm-3 col-form-label">Name <span
                                                class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" required name="name" id="name">
                                        </div>
                                    </div>

                                    <div class="mb-3 row field">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputTelephone"
                                            class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-1">
                                            <select class="form-control editInput selectOptions" required>
                                                <option>+444</option>
                                                <option>+91</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control editInput" id="telephone" name="telephone" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-1">
                                            <select class="form-control editInput selectOptions" required>
                                                <option>+444</option>
                                                <option>+91</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control editInput" id="contact" name="contact" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 row field">
                                        <label for="inputName" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" rows="3"
                                                required id="address" name="address"></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3 row field">
                                        <label for="inputCity" class="col-sm-3 col-form-label">City</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="city" name="city" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCounty" class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="country" name="country" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputPincode"
                                            class="col-sm-3 col-form-label">Pincode</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="pincode" name="pincode" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCountry"
                                            class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="country_id" name="country_id" required>
                                                <option selected disabled>Select Country</option>
                                                <?php foreach ($country as $country_val) { ?>
                                                    <option value="{{$country_val->id}}" class="country_code">{{$country_val->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="field nextfornBtn">
                                        <button type="button" class="firstNext next profileDrop">Next</button>
                                    </div>
                                </div>

                                <div class="page">
                                    <div class="title">Site Details</div>
                                    <div class="mb-2 row field">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Site</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions get_site_result" required
                                                disabled id="site_id" name="site_id">
                                                <option selected>Default</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" id="openPopupButton" onclick="get_modal(6)"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>

                                    <div class="mb-3 row field">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Region</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions get_region_result" id="region" name="region" required>
                                                <option selected disabled>Select Region</option>
                                                <?php foreach($region as $site_region){?>
                                                    <option value="{{$site_region->id}}">{{$site_region->name}}</option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" onclick="get_modal(3)"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputProject"
                                            class="col-sm-3 col-form-label">Company</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="company" id="company" class="form-control">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputName" class="col-sm-3 col-form-label">Contact</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput"
                                                id="profession_name" value="Lisa (Manager)" readonly="">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputName" class="col-sm-3 col-form-label">Contact
                                            Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" required id="conatact_name" name="conatact_name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="site_email" name="site_email" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputTelephone"
                                            class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-1">
                                            <select class="form-control editInput selectOptions" required>
                                                <option>+444</option>
                                                <option>+91</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control editInput" id="site_telephone" name="site_telephone" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-1">
                                            <select class="form-control editInput selectOptions" required>
                                                <option>+444</option>
                                                <option>+91</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control editInput" id="site_mobile" name="site_mobile" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputAddress"
                                            class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" rows="3"
                                                required id="site_address" name="site_address"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCity" class="col-sm-3 col-form-label">City</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="site_city" name="site_city" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCounty" class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="site_country" name="site_country" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputPincode"
                                            class="col-sm-3 col-form-label">Pincode</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="site_pincode" name="site_pincode" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCountry"
                                            class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="site_country_id" name="site_country_id" required>
                                                <option selected disabled>Select Country</option>
                                                <?php foreach ($country as $country_v) { ?>
                                                    <option value="{{$country_v->id}}">{{$country_v->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Notes</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" rows="3"
                                                required id="notes" name="notes"></textarea>
                                        </div>
                                    </div>
                                    <div class="field btns nextfornBtn">
                                        <button class="prev-1 prev profileDrop">Previous</button>
                                        <button type="button" class="next-1 next profileDrop">Next</button>
                                    </div>
                                </div>
                                <div class="page">
                                    <div class="title">Job Details</div>

                                    <div class="mb-3 row field">
                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Job Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput"
                                                value="Auto generate" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer
                                            Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                required id="customer_ref" name="customer_ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer Job
                                            Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                required id="cust_job_ref" name="cust_job_ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Purch. Order
                                            Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                required id="purchase_order_ref" name="purchase_order_ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputJobType" class="col-sm-3 col-form-label">Job
                                            Type*</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" id="job_type" name="job_type" required>
                                                <option selected disabled>Please Select</option>
                                                <?php foreach ($job_type as $type) { ?>
                                                    <option value="{{$type->id}}">{{$type->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon" onclick="get_modal(7)"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputPriority"
                                            class="col-sm-3 col-form-label">Priority</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions"
                                                id="priorty" name="priorty">
                                                <option selected disabled>None</option>
                                                <option value="1">Normal</option>
                                                <option value="2">Medium</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2 row field">
                                        <label class="col-sm-3 col-form-label">Alert Customer</label>
                                        <div class="col-sm-9">

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                    name="alert_customer" id="alert_customer" value="0"
                                                    required>
                                                <label class="form-check-label checkboxtext" for="checkalrt">By
                                                    Email</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label class="col-sm-3 col-form-label">On Rout SMS Alert</label>
                                        <div class="col-sm-9">

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="on_route_sms" id="on_route_sms" value="1"
                                                    required>
                                                <label class="form-check-label checkboxtext"
                                                    for="inlineRadio1">Yes</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio"
                                                    name="on_route_sms" id="on_route_sms" value="2"
                                                    checked>
                                                <label class="form-check-label checkboxtext"
                                                    for="inlineRadio2">No</label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputTelephone" class="col-sm-3 col-form-label">Start
                                            Date*</label>
                                        <div class="col-sm-4">
                                            <input type="date" class="form-control editInput" id="start_date" name="start_date" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row field">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Complete
                                            By</label>
                                        <div class="col-sm-4">
                                            <input type="date" id="complete_by" name="complete_by" class="form-control editInput" required>
                                        </div>
                                    </div>

                                    <div class="mb-3 row field">
                                        <label for="inputCountry" class="col-sm-3 col-form-label">Tags</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="tags" name="tags" required>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="#!" class="formicon"><i
                                                    class="fa-solid fa-square-plus"></i></a>
                                        </div>
                                    </div>

                                    <div class="field btns nextfornBtn">
                                        <button class="prev-2 prev profileDrop">Previous</button>
                                        <button type="button" class="next-2 next profileDrop">Next</button>
                                    </div>
                                </div>
                                <div class="page">
                                    <!-- <div class="title">Contact Info 3:</div> -->

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Product Details</label>
                                        <div class="row">
                                            <div class="col-sm-9">
                                                <div class="mb-3 row">
                                                    <label for="inputCountry"
                                                        class="col-sm-2 col-form-label">Select product</label>
                                                    <div class="col-sm-3">
                                                        <input type="text" class="form-control editInput" placeholder="Type to add product" onkeyup="get_search()">
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <div class="plusandText">
                                                            <a href="#!" class="formicon" onclick="get_modal(7)"><i
                                                                    class="fa-solid fa-square-plus"></i>
                                                            </a>
                                                            <span class="afterPlusText"> (Type to view product
                                                                or <a href="Javascript:void(0)" onclick="show_product_model()">Click
                                                                    here</a> to view all assets)</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="pageTitleBtn p-0">
                                                    <a href="#" class="profileDrop">Add Title</a>
                                                    <a href="#" class="profileDrop">Show Variations</a>
                                                    <a href="#" class="profileDrop bg-secondary">Export</a>

                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="productDetailTable">
                                                    <table class="table" id="result">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Code </th>
                                                                <th>Product </th>
                                                                <th>Description</th>
                                                                <th>Qty </th>
                                                                <th>Cost Price(£) </th>
                                                                <th>Price(£) </th>
                                                                <th>Discount </th>
                                                                <th>VAT(%) </th>
                                                                <th>Amount Assigned To </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr id="product_result">

                                                            </tr>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td id="pro_qty">0.00</td>
                                                                <td id="pro_cost_price">£0.00</td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td id="total_amount">£0.00</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->
                                    <div class="field btns nextfornBtn mt-3 p-0">
                                        <button class="prev-3 prev profileDrop">Previous</button>
                                        <button type="button" class="next-3 next profileDrop" onclick="save_job_product()">Next</button>
                                    </div>
                                </div>
                                <!-- </form> -->

                                <!-- <div class="page">
                                            <div class="title">Asset</div>
                                            <div class="newJobForm mt-4">
                                                <label class="upperlineTitle">Asset Details</label>
                                                <div class="row">
                                                    <div class="col-sm-9">
                                                        <div class="mb-3 row">
                                                            <label for="inputCountry"
                                                                class="col-sm-2 col-form-label">Select Asset</label>
                                                            <div class="col-sm-3">
                                                                <input type="text" class="form-control editInput"
                                                                    id="inputCountry" placeholder="Type to add Asset">
                                                            </div>
                                                            <div class="col-sm-7">
                                                                <div class="plusandText">
                                                                    <a href="#!" class="formicon"><i
                                                                            class="fa-solid fa-square-plus"></i>
                                                                    </a>
                                                                    <span class="afterPlusText"> (Type to view Asset or
                                                                        <a href="#!">Click
                                                                            here</a> to view all assets)</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12">
                                                        <div class="productDetailTable">
                                                            <table class="table">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Asset Ref </th>
                                                                        <th>Title </th>
                                                                        <th>Description</th>
                                                                        <th>Asset Status </th>
                                                                        <th>Assigned To </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>..</td>
                                                                        <td>..</td>
                                                                        <td>..</td>
                                                                        <td>..</td>
                                                                        <td>..</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field btns nextfornBtn mt-3 p-0">
                                                <button class="prev-4 prev profileDrop">Previous</button>
                                                <button type="button" class="next-4 next profileDrop">Next</button>
                                            </div>
                                        </div> -->

                                <div class="page">
                                    <!-- <form id="appointment_form">
                                    
                                         @csrf -->
                                    <div class="title">Appoinments</div>

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Appoinments</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection">
                                                    <a href="#" class="profileDrop">Smart Planning</a>
                                                    <a href="javascript:void(0)" onclick="new_appointment()" class="profileDrop">New User Appoinment</a>
                                                    <a href="#" class="profileDrop">Send To Planner</a>
                                                </div>
                                            </div>

                                            <div class="col-sm-12">
                                                <div class="productDetailTable pt-3">
                                                    <table class="table table-bordered" id="appointment_table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>User </th>
                                                                <th>Appoinment Type </th>
                                                                <th>Date / Time</th>
                                                                <th>User Notes </th>
                                                                <th>Status </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" id="count_number" value="1">
                                                                    <div class="d-flex">
                                                                        <p class="leftNum">1</p>
                                                                        <select class="form-control editInput selectOptions" id="user_id" name="user_id[]">
                                                                            <option selected disabled>Select user</option>
                                                                            <?php foreach ($users as $user) { ?>
                                                                                <option value="{{$user->id}}">{{$user->name}}</option>
                                                                            <?php } ?>
                                                                        </select>
                                                                        <a href="#!" class="callIcon"><i
                                                                                class="fa-solid fa-square-phone"></i></a>
                                                                    </div>
                                                                    <div class="alertBy">
                                                                        <label><strong>Alert By
                                                                                :</strong></label>
                                                                        <div
                                                                            class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                id="alert_by_check_1"
                                                                                value="0">
                                                                            <label class="form-check-label"
                                                                                for="inlineCheckbox1">SMS</label>
                                                                        </div>
                                                                        <div
                                                                            class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                id="alert_by_check_2"
                                                                                value="1">
                                                                            <label class="form-check-label"
                                                                                for="inlineCheckbox2">Email</label>
                                                                        </div>
                                                                        <input type="hidden" name="alert_by[]" id="alert_by" class="alert_by">
                                                                    </div>
                                                                </td>
                                                                <td class="col-2">
                                                                    <div class="appoinment_type">
                                                                        <select
                                                                            class="form-control editInput selectOptions"
                                                                            id="appointment_type_id" name="appointment_type_id[]">
                                                                            <option selected disabled>Select Appointment Type</option>
                                                                            <?php foreach ($appointment_type as $appointmentv) { ?>
                                                                                <option value="{{$appointmentv->id}}">{{$appointmentv->name}}</option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="Priority">
                                                                        <label>Priority :</label>
                                                                        <select
                                                                            class="form-control editInput selectOptions"
                                                                            id="priority" name="priority[]">
                                                                            <option selected disabled>Select Priority</option>
                                                                            <option>Default</option>
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="addDateAndTime">
                                                                        <div class="startDate">
                                                                            <input type="date" name="appointment_start_date[]"
                                                                                class=" editInput">
                                                                            <input type="time" name="start_time[]"
                                                                                class=" editInput">
                                                                        </div>
                                                                        <span class="p-2">To</span>
                                                                        <div class="endDate">
                                                                            <input type="date" name="end_date[]"
                                                                                class=" editInput">
                                                                            <input type="time" name="end_time[]"
                                                                                class=" editInput">
                                                                        </div>
                                                                    </div>
                                                                    <div class="pt-3">
                                                                        <div
                                                                            class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                id="appointment_checkbox1"
                                                                                value="option1">
                                                                            <label class="form-check-label"
                                                                                for="singleAppointment">Single
                                                                                Appointment</label>
                                                                        </div>
                                                                        <div
                                                                            class="form-check form-check-inline">
                                                                            <input class="form-check-input"
                                                                                type="checkbox"
                                                                                id="appointment_checkbox2"
                                                                                value="option2">
                                                                            <label class="form-check-label"
                                                                                for="floatingAppointment">Floating
                                                                                Appointment</label>
                                                                        </div>
                                                                        <input type="hidden" name="appointment_checkbox[]" id="appointment_checkbox" class="appointment_checkbox">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="addTextarea">
                                                                        <textarea cols="40" rows="5" id="appointment_notes" name="appointment_notes[]">

                                                                    </textarea>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="statuswating">
                                                                        <select
                                                                            class="form-control editInput selectOptions"
                                                                            id="appointment_status" name="appointment_status[]">
                                                                            <option selected disabled>Select Status</option>
                                                                            <option value="1">Awaiting</option>
                                                                            <option value="2">Received</option>
                                                                            <option value="3">Accepted</option>
                                                                            <option value="4">Declined</option>
                                                                            <option value="5">on Route</option>
                                                                            <option value="6">On Site</option>
                                                                            <option value="7">Completed</option>
                                                                            <option value="8">Follow On</option>
                                                                            <option value="9">Abandoned</option>
                                                                            <option value="10">No Access</option>
                                                                            <option value="11">Cancelled</option>
                                                                            <option value="12">On Hold</option>
                                                                        </select>
                                                                        <a href="javascript:void(0)" onclick="deleteRow(this)"><i
                                                                                class="fa-solid fa-circle-xmark"></i></a>

                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <div class="Priority">
                                                                        <label><strong>Travel Time
                                                                                -</strong></label>
                                                                        <input type="text"
                                                                            class="form-control editInput"
                                                                            id="input_time1"
                                                                            placeholder="" onkeyup="get_time()"><label>
                                                                            Mins</label>
                                                                    </div>
                                                                </td>
                                                                <td></td>
                                                                <td>
                                                                    <div class="Priority">
                                                                        <label><strong>Appointment Time
                                                                                -</strong></label>
                                                                        <input type="text"
                                                                            class="form-control editInput"
                                                                            id="input_time2"
                                                                            placeholder="" onkeyup="get_time()"><label> Mins
                                                                            <strong>Total Time -</strong>
                                                                            <font id="time_show">0h
                                                                                0mins</font>
                                                                        </label>
                                                                    </div>
                                                                    <input type="hidden" id="appointment_time" class="appointment_time" name="appointment_time[]">
                                                                </td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                            <tr class="del-btn">
                                                                <td>
                                                                    <div class="Priority p-0">
                                                                        <label class="p-0"><strong>Assigned
                                                                                Products: </strong><a
                                                                                href="#!">All</a> None</label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="pageTitleBtn p-0">
                                                                        <a href="#" class="profileDrop">Asign
                                                                            Product</a>
                                                                    </div>
                                                                </td>
                                                                <td></td>
                                                                <td colspan="2">
                                                                    <div class="pageTitleBtn p-0">
                                                                        <a href="#" class="profileDrop">Add
                                                                            Title</a>
                                                                        <a href="#" class="profileDrop">Show
                                                                            Variations</a>
                                                                        <a href="#"
                                                                            class="profileDrop bg-secondary">Export</a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr class="del-btn">
                                                                <td colspan="5" class="padingtableBottom"></td>
                                                            </tr>
                                                        </tbody>
                                                        <div id="appointment_result"></div>

                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->
                                    <!-- <form> -->
                                    <div class="field btns nextfornBtn mt-3 p-0">
                                        <button class="prev-4 prev profileDrop">Previous</button>
                                        <button type="button" class="next-4 next profileDrop">Next</button>
                                    </div>
                                </div>

                                <div class="page">
                                    <div class="title">Notes</div>
                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Notes</label>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="">
                                                    <h4 class="contTitle text-start">Customer Notes</h4>
                                                    <div class="mt-3">
                                                        <textarea cols="40" rows="5" id="customer_notes" name="customer_notes">

                                                                  </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="">
                                                    <h4 class="contTitle text-start">Internal Notes</h4>
                                                    <div class="mt-3">
                                                        <textarea cols="40" rows="5" id="internal_notes" name="internal_notes">

                                                                  </textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->


                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Expenses</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection pt-2 pb-3">
                                                    <a href="#" class="profileDrop">New Expense</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="productDetailTable">
                                                    <table class="table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th>Appointment </th>
                                                                <th>User</th>
                                                                <th>Expense Name </th>
                                                                <th>Expense Ref. </th>
                                                                <th>Notes</th>
                                                                <th>Deseble</th>
                                                                <th>Attachment</th>
                                                                <th>Net Amount</th>
                                                                <th>VAT Amount</th>
                                                                <th>Cross Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="12">
                                                                    <label class="red_sorryText">
                                                                        Sorry, no item(s) found
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Perchase Orders</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection pt-2 pb-3">
                                                    <a href="#" class="profileDrop">New Perchase Orders</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="productDetailTable">
                                                    <table class="table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th>PO Ref. </th>
                                                                <th>Supporter</th>
                                                                <th>Sub Total </th>
                                                                <th>VAT </th>
                                                                <th>Total (inc. VAT)</th>
                                                                <th>Creadit Note (inc. VAT)</th>
                                                                <th> Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="9">
                                                                    <label class="red_sorryText">
                                                                        Sorry, no item(s) found
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Appointment Time Summary</label>
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <div class="productDetailTable">
                                                    <table class="table table-borderless">
                                                          <tbody>
                                                            <tr>
                                                                <td>Totle Distance</td>
                                                                <td>0.0 (miles)</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Number Of Appointment</td>
                                                                <td>1</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Totle Travel Time</td>
                                                                <td>0hrs 0mins</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Totle Travel Time</td>
                                                                <td>0hrs 0mins</td>
                                                            </tr>
                                                          </tbody>
                                                          <tfoot style="border-top: 1px solid #000;">
                                                            <tr>
                                                                <td>Totle :</td>
                                                                <td>0hrs 0mins</td>
                                                            </tr>
                                                          </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Finance (Estimated)</label>
                                        <div class="row">
                                        <div class="col-sm-7">
                                                <div class="productDetailTable">
                                                    <table class="table table-borderless">
                                                          <tbody>
                                                            <tr>
                                                                <td>Product(s) Cost</td>
                                                                <td colspan="3">$100.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Perchase Order(s) Cost</td>
                                                                <td colspan="3">$0.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Appointment(s) Cost</td>
                                                                <td colspan="3">$0.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Expense</td>
                                                                <td colspan="3">$0.00</td>
                                                            </tr>
                                                          </tbody>
                                                          <tfoot style="border-top: 1px solid #000;">
                                                            <tr>
                                                                <td>Totle Cost :</td>
                                                                <td>$100.00</td>
                                                                <td>Profit :</td>
                                                                <td>$14900.00 <span style="color: #20a900;">(99.33%)</span></td>
                                                            </tr>
                                                          </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-sm-5">
                                                <div class="productDetailTable">
                                                    <table class="table ">
                                                          <tbody>
                                                            <tr>
                                                                <td>Product(s) Sub Total (exc. VAT):</td>
                                                                <td>$14,000.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Billable Expenses(s) (exc. VAT)</td>
                                                                <td>$0.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Sub Talal (exc. VAT):</td>
                                                                <td>$14,000.00</td>
                                                            </tr>
                                                            <tr>
                                                                <td>VAT:</td>
                                                                <td>$3,000.00</td>
                                                            </tr>
                                                          </tbody>
                                                          <tfoot style="border-top: 1px solid #000;">
                                                            <tr>
                                                                <td>Invoice Total (inc. VAT):</td>
                                                                <td>$18,000.0</td>
                                                            </tr>
                                                          </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->


                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Invoices</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection pt-2 pb-3">
                                                    <a href="#" class="profileDrop">Create Part Invoice</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="productDetailTable">
                                                    <table class="table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Invoices Ref.</th>
                                                                <th>Section</th>
                                                                <th>Invoice Date</th>
                                                                <th>Due Date</th>
                                                                <th>Sub Total </th>
                                                                <th>VAT </th>
                                                                <th>Total </th>
                                                                <th>Oustanding</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="10">
                                                                    <label class="red_sorryText">
                                                                        Sorry, no item(s) found
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->

                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Appointment</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection pt-2 pb-3">
                                                    <a href="#" class="profileDrop">Upload Appointment</a>
                                                    <a href="#" class="profileDrop">Upload Malti Appointment</a>
                                                    <a href="#" class="profileDrop">Preview Appointment</a>
                                                    <a href="#" class="profileDrop">Download Appointment</a>

                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="productDetailTable">
                                                    <table class="table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Type</th>
                                                                <th>Title</th>
                                                                <th>Description</th>
                                                                <th>section</th>
                                                                <th>Customer Visitle </th>
                                                                <th>Mobile User Visitle</th>
                                                                <th>File Name </th>
                                                                <th>Mame Type/Size</th>
                                                                <th>Created On</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="10">
                                                                    <label class="red_sorryText">
                                                                        Sorry, no Appointment(s) found
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                                <td>..</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->


                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Tasks</label>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="jobsection pt-2 pb-3">
                                                    <a href="#" class="profileDrop">New Task</a>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="extraInformationTab">
                                                    <nav>
                                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                        <button class="nav-link active" id="nav-Tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-Tasks" type="button" role="tab" aria-controls="nav-Tasks" aria-selected="true">Tasks</button>
                                                            <button class="nav-link" id="nav-Notes-tab" data-bs-toggle="tab" data-bs-target="#nav-Notes" type="button" role="tab" aria-controls="nav-Notes" aria-selected="false" tabindex="-1">Recursing Task</button>
                                                            
                                                        </div>
                                                    </nav>
                                                    <div class="tab-content" id="nav-tabContent">
                                                    <div class="tab-pane fade active show" id="nav-Tasks" role="tabpanel" aria-labelledby="nav-Tasks-tab" tabindex="0">
                        
                                                            <div class="col-sm-12">

                                                                <div class="productDetailTable mt-3">
                                                                    <table class="table" id="containerA">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th># </th>
                                                                                <th>Date </th>
                                                                                <th>Ref.</th>
                                                                                <th>User </th>
                                                                                <th>Type </th>
                                                                                <th>Title </th>
                                                                                <th>Notes </th>
                                                                                <th>Created On </th>
                                                                                <th>Excuted</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td colspan="9">
                                                                                    <label class="red_sorryText">
                                                                                        Sorry, no Task(s) found
                                                                                    </label>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>.</td>
                                                                                <td>
                                                                                    <div class="nav-item dropdown tableActionBtn">
                                                                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                                                            Action
                                                                                        </a>
                                                                                        <div class="dropdown-menu fade-up m-0">
                                                                                            <a href="#" class="dropdown-item">Products</a>
                                                                                            <a href="#" class="dropdown-item">Our Team</a>
                                                                                            <a href="#" class="dropdown-item">Testimonial</a>
                                                                                            <a href="#" class="dropdown-item">Our Works</a>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="nav-Notes" role="tabpanel" aria-labelledby="nav-Notes-tab" tabindex="0">
                                                            <div class="tabheadingTitle">
                                                                <h3>Notes - </h3>
                                                            </div>
                                                            <div class="col-sm-12">
                                                                <div class="mb-3 row">
                                                                    <label for="inputCountry" class="col-sm-2 col-form-label">Select product</label>
                                                                    <div class="col-sm-2">
                                                                        <input type="text" class="form-control editInput" id="inputCountry" placeholder="Type to add product">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="plusandText">
                                                                            <a href="#!" class="formicon" data-bs-toggle="modal" data-bs-target="#notesPopup"><i class="fa-solid fa-square-plus"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-7 mt-3">
                                                                        <textarea class="form-control textareaInput" name="address" id="inputAddress" rows="3" placeholder="75 Cope Road Mall Park USA"></textarea>
                                                                    </div>
                                                                    <div class="col-sm-3 mt-3">
                                                                        <div class="jobsection">
                                                                            <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save Notes</a>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12">
                                                                        <div class="productDetailTable pt-3">
                                                                            <table class="table" id="containerA">
                                                                                <thead class="table-light">
                                                                                    <tr>
                                                                                        <th>1 </th>
                                                                                        <th>Date </th>
                                                                                        <th>By</th>
                                                                                        <th>Type </th>
                                                                                        <th>Notes </th>
                                                                                        <th>. </th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td>...</td>
                                                                                        <td>...</td>
                                                                                        <td>...</td>
                                                                                        <td>...</td>
                                                                                        <td>...</td>
                                                                                        <td>...</td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            
                                                            </div><!-- ENd col-9 -->
                                                        </div>
                                                        

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    <div class="field btns nextfornBtn mt-3 p-0">
                                        <button class="prev-4 prev profileDrop">Previous</button>
                                        <button type="button" onclick="get_notes()" class="next-4 next profileDrop">Next</button>
                                    </div>
                                </div>

                                <div class="page">
                                    <div class="title">Attachments</div>
                                    <div class="newJobForm mt-4">
                                        <label class="upperlineTitle">Attachments</label>
                                        <div class="row">
                                            <div class="col-sm-12">

                                                <div class="py-4">
                                                    <div class="jobsection">
                                                        <input type="file" id="attachments" name="attachments" class="profileDrop">Upload Attachments
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- End  off newJobForm -->

                                    <div class="field btns nextfornBtn mt-3 p-0">
                                        <button class="prev-5 prev profileDrop">Previous</button>
                                        <button type="button" onclick="get_attachment_save()" class="submit profileDrop">Submit</button>
                                    </div>
                                </div>




                            </form>
                        </div>
                    </div>

                </div>










            </div> <!-- End  off col-12 -->
            <!-- start Customer popup-->
            <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerModalLabel">Add
                                Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="customerForm">
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Customer
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_name">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCustomer"
                                                    class="col-sm-3 col-form-label">Customer
                                                    Type <span class="radStar ">*</span></label>
                                                <div class="col-sm-7">
                                                    <select class="form-control editInput selectOptions get_customer_result"
                                                        id="customer_type_id">
                                                        <option selected disabled>None</option>
                                                        <?php foreach($customer_types as $cust_type){?>
                                                            <option value="{{$cust_type->id}}">{{$cust_type->name}}</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-1">
                                                    <a href="#!" class="formicon" onclick="get_modal(1)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div><!-- End off Customer -->

                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Conatact
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_contact_name">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Job
                                                    Title (Position)</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions get_job_title_result"
                                                        id="customer_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $val_title){?>
                                                            <option value="{{$val_title->id}}">{{$val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" onclick="get_modal(2)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputEmail"
                                                    class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_email">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputTelephone"
                                                    class="col-sm-3 col-form-label">Telephone</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_phone">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputMobile"
                                                    class="col-sm-3 col-form-label">Mobile</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_mobile">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Fax</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_fax">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">Website</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_website">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Payment
                                                    Terms</label>
                                                <div class="col-sm-7">
                                                    <select class="form-control editInput selectOptions"
                                                        id="customer_payment_terms">
                                                        <option value="21">Defoult (21)
                                                        </option>
                                                        <?php for($i=1;$i<21;$i++){?>
                                                        <option value="{{$i}}">{{$i}}</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <span class="afterInputText">
                                                        Days
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Currency</label>
                                                    <div class="col-sm-9">
                                                    <!-- British Pound - GBP -->
                                                        <select class="form-control editInput selectOptions" id="customer_currency_id">
                                                            <option selected disabled>Please Select</option>
                                                            <?php foreach($country as $countryItem): ?>
                                                                <?php foreach($countryItem->currencies as $currency): ?>
                                                                    <option value="<?php echo $currency->currency_code; ?>">
                                                                        <?php echo $countryItem->name; ?> (<?php echo $currency->currency_code; ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                
                                            </div>


                                            <div class="mb-2 row">
                                                <label for="inputCounty" class="col-sm-3 col-form-label">Credit
                                                    Limit</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_credit_limit">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Discount</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_discount">
                                                </div>
                                                
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Discount Type</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions" id="customer_percentage">
                                                            <option selected disabled>Please Select</option>
                                                            <option value="1">Persontage</option>
                                                            <option value="2">Flat</option>
                                                        </select>
                                                    </div>
                                            </div>
                                            
                                            
                                            <div class="mb-2 row">
                                                <label for="inputCountry" class="col-sm-3 col-form-label">VAT
                                                    / Tax No.</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_vat">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Default Catalogue</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="customer_catalogue">
                                                        <option>None</option>
                                                        <option>ABCD</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Status</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="customer_status">
                                                        <option value='1'>Active</option>
                                                        <option value='0'>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="">
                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Region</label>
                                                <div class="col-sm-7">
                                                    <select class="form-control editInput selectOptions get_region_result"
                                                        id="customer_region">
                                                        <option>None</option>
                                                        <?php foreach($region as $region_val){?>
                                                            <option value="{{$region_val->id}}">{{$region_val->name}}</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" id="openPopupButton" onclick="get_modal(3)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>


                                                <!--Start Region Popup -->

                                                <!-- <div id="popup" class="popup">
                                                    <div class="popup-content">
                                                        <div class="popupTitle">
                                                            <span class="">Add
                                                                Region</span>
                                                            <span class="close" id="closePopup">&times;</span>
                                                        </div>
                                                        <div class="contantbodypopup">
                                                            <form action="" class="customerForm">
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity"
                                                                        class="col-sm-3 col-form-label">Region*</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text"
                                                                            class="form-control editInput"
                                                                            id="inputCity"
                                                                            value="Port Elizabeth">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity"
                                                                        class="col-sm-3 col-form-label">Status</label>
                                                                    <div class="col-sm-9">
                                                                        <select
                                                                            class="form-control editInput selectOptions"
                                                                            id="inputCustomer">
                                                                            <option> None </option>
                                                                            <option> Default </option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>

                                                        <div class="popupF  customer_Form_Popup">

                                                            <button type="button"
                                                                class="profileDrop">Save</button>
                                                            <button type="button" class="profileDrop">Save &
                                                                Close</button>
                                                            <button type="button" class="profileDrop"
                                                                data-bs-dismiss="modal">Cancel</button>

                                                        </div>
                                                    </div>

                                                </div> -->

                                                <!-- End off region Popup -->



                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Address</label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" id="cuatomer_address" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">City</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_city">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCounty"
                                                    class="col-sm-3 col-form-label">County</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_country_input">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Pincode</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_pincode">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="customer_country_id">
                                                        <option selected disabled>Select Coutry</option>
                                                        <?php foreach($country as $countryval){?>
                                                        <option value="{{$countryval->code}}">{{$countryval->name}} ({{$countryval->code}})</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress" class="col-sm-3 col-form-label">Site
                                                    Notes</label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" rows="3" id="customer_site_note"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Sage
                                                    Ref.</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="customer_sage_ref" >
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label class="col-sm-3 col-form-label">Assign
                                                    Products</label>
                                                <div class="col-sm-9">

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="customer_yes" checked>
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="custoemr_no">
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio2">No</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Notes</label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" rows="3" id="customer_note"></textarea>
                                                </div>
                                            </div>


                                        </form>
                                    </div>
                                </div>
                            </div> <!-- End row -->
                        </div>
                        <div class="modal-footer customer_Form_Popup">

                            <button type="button" class="profileDrop" onclick="save_customer()">Save</button>
                            <button type="button" class="profileDrop" onclick="save_customerClose()">Save &
                                Close</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End off Customer popup -->
            <!-- Modal Start here -->
            <div class="modal fade" id="product_model" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel">Product</h5>
                            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button> -->
                        </div>
                        <div class="modal-body">
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
                                            <tr onclick="selectProduct({{$details1->id}})">
                                                <td>{{$details1->product_code}}</td>
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
            <!-- end here -->
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
    
      
       <!-- Project Modal start here -->
       <div class="modal fade" id="project_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Add Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="project_form">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Ref</label>
                            <div class="col-sm-9">
                                <p>Project Ref ###</p>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Customer</label>
                            <div class="col-sm-9">
                            <p id="project_customer_name"></p>
                            </div>
                        </div>
                        <input type="hidden" id="project_customer_id">
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Name <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" value="" id="project_name">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Start Date <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control editInput"  id="project_start_date">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">End Date <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control editInput" id="project_end_date">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Project Value </label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control editInput" id="project_value">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                <textarea name="project_description" id="project_description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select id="project_status" name="project_status" class="form-control editInput">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="pageTitleBtn">
                            <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_project()"> Save</a>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
       <!-- end here -->
        <!-- Contact Modal start here -->
        <div class="modal fade" id="contact_modal" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerModalLabel">Add Customer Contact</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form id="contact_form">
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                                <div class="col-sm-9">
                                                    <p id="contact_customer_name"></p>
                                                </div>
                                            </div>
                                            <input type="hidden" id="contact_customer_id">
                                            
                                            <div class="mb-2 row">
                                                <label class="col-sm-3 col-form-label">Assign
                                                    Products</label>
                                                <div class="col-sm-9">

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_yes">
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio1">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_no" checked>
                                                        <label class="form-check-label checkboxtext"
                                                            for="inlineRadio2">No</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Conatact
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_contact_name">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Job
                                                    Title (Position)</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions get_job_title_result"
                                                        id="contact_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $con_val_title){?>
                                                            <option value="{{$con_val_title->id}}">{{$con_val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" onclick="get_modal(2)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputEmail"
                                                    class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_email">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputTelephone"
                                                    class="col-sm-3 col-form-label">Telephone</label>
                                                    <div class="col-sm-2">
                                                    <select class="form-control editInput selectOptions">
                                                        <option>+444</option>
                                                        <option>+91</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_phone">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputMobile"
                                                    class="col-sm-3 col-form-label">Mobile</label>
                                                    <div class="col-sm-2">
                                                        <select class="form-control editInput selectOptions">
                                                            <option>+444</option>
                                                            <option>+91</option>
                                                        </select>
                                                    </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_mobile">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Fax</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_fax">
                                                </div>
                                            </div>
                                            
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="">
                                        <div class="mb-2 row">
                                            <label class="col-sm-3 col-form-label">Address Details</label>
                                            <div class="col-sm-9">

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="inlineRadioOptions" id="contact_default_address" onchange="default_address()">
                                                </div>
                                            </div>
                                        </div>
                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" id="contact_address" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">City</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_city">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCounty"
                                                    class="col-sm-3 col-form-label">County</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_country_input">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Pincode</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_pincode">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="contact_country_id">
                                                        <option selected disabled>Select Coutry</option>
                                                        <?php foreach($country as $countryval){?>
                                                        <option value="{{$countryval->id}}" class="contact_country_id">{{$countryval->name}} ({{$countryval->code}})</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div> <!-- End row -->

                        </div>
                        <div class="modal-footer customer_Form_Popup">

                            <button type="button" class="profileDrop" onclick="save_contact()">Save</button>
                            <button type="button" class="profileDrop" onclick="save_contactClose()">Save &
                                Close</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        <!-- end here -->
         <!-- Add Product Modal start here -->
         <div class="modal fade" id="add_product_modal" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerModalLabel">Add Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form id="add_product_form">
                                        <div class="col-sm-9">

                                        <div class="form-check form-check-inline">
                                        <label class="form-check-label checkboxtext"
                                        for="inlineRadio1">This Customer Only</label>
                                            <input class="form-check-input" type="radio" name="product_yes" id="product_yes">
                                            <label class="form-check-label checkboxtext"
                                                for="inlineRadio1">Yes, display only for this customer</label>
                                        </div>
                                        <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Product Category</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions get_product_category_result"
                                                        id="contact_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $con_val_title){?>
                                                            <option value="{{$con_val_title->id}}">{{$con_val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" onclick="get_modal(9)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                            <!-- <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Product Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <p id="contact_customer_name"></p>
                                                </div>
                                            </div> -->
                                            <input type="hidden" id="product_customer_id">
                                            
                                            
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Product Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="product_name">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Product Type</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions "
                                                        id="contact_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $con_val_title){?>
                                                            <option value="{{$con_val_title->id}}">{{$con_val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Product Code</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="product_name">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputEmail"
                                                    class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_email">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputTelephone"
                                                    class="col-sm-3 col-form-label">Telephone</label>
                                                    <div class="col-sm-2">
                                                    <select class="form-control editInput selectOptions">
                                                        <option>+444</option>
                                                        <option>+91</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_phone">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputMobile"
                                                    class="col-sm-3 col-form-label">Mobile</label>
                                                    <div class="col-sm-2">
                                                        <select class="form-control editInput selectOptions">
                                                            <option>+444</option>
                                                            <option>+91</option>
                                                        </select>
                                                    </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_mobile">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Fax</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_fax">
                                                </div>
                                            </div>
                                            
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="">
                                        <div class="mb-2 row">
                                            <label class="col-sm-3 col-form-label">Address Details</label>
                                            <div class="col-sm-9">

                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="inlineRadioOptions" id="contact_default_address" onchange="default_address()">
                                                </div>
                                            </div>
                                        </div>
                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" id="contact_address" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">City</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_city">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCounty"
                                                    class="col-sm-3 col-form-label">County</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_country_input">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Pincode</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="contact_pincode">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="contact_country_id">
                                                        <option selected disabled>Select Coutry</option>
                                                        <?php foreach($country as $countryval){?>
                                                        <option value="{{$countryval->id}}" class="contact_country_id">{{$countryval->name}} ({{$countryval->code}})</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div> <!-- End row -->

                        </div>
                        <div class="modal-footer customer_Form_Popup">

                            <button type="button" class="profileDrop" onclick="save_contact()">Save</button>
                            <button type="button" class="profileDrop" onclick="save_contactClose()">Save &
                                Close</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
         <!-- end here -->
         <!-- Site Modal start here -->
         <div class="modal fade" id="site_modal" tabindex="-1" aria-labelledby="customerModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content add_Customer">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerModalLabel">Add Site Address</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form id="site_form">
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                                <div class="col-sm-9">
                                                    <p id="site_customer_name"></p>
                                                </div>
                                            </div>
                                            <input type="hidden" id="site_customer_id">
                                            
                                            

                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Site
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_name">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Contact
                                                    Name <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_contact_name">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputProject" class="col-sm-3 col-form-label">Job
                                                    Title (Position)</label>
                                                <div class="col-sm-4">
                                                    <select class="form-control editInput selectOptions get_job_title_result"
                                                        id="site_job_titile_id">
                                                        <option selected disabled>Please Select
                                                        </option>
                                                        <?php foreach($job_title as $site_val_title){?>
                                                            <option value="{{$site_val_title->id}}">{{$site_val_title->name}}</option>
                                                            <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" onclick="get_modal(2)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputName" class="col-sm-3 col-form-label">Company
                                                    Name</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_company_name">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputEmail"
                                                    class="col-sm-3 col-form-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_email">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputTelephone"
                                                    class="col-sm-3 col-form-label">Telephone</label>
                                                    <div class="col-sm-2">
                                                    <select class="form-control editInput selectOptions">
                                                        <option>+444</option>
                                                        <option>+91</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_phone">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputMobile"
                                                    class="col-sm-3 col-form-label">Mobile</label>
                                                    <div class="col-sm-2">
                                                        <select class="form-control editInput selectOptions">
                                                            <option>+444</option>
                                                            <option>+91</option>
                                                        </select>
                                                    </div>
                                                <div class="col-sm-7">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_mobile">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Fax</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_fax">
                                                </div>
                                            </div>
                                            
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-6 col-xl-6">
                                    <div class="formDtail">
                                        <form action="" class="">
                                            <div class="mb-2 row">
                                                <label for="inputProject"
                                                    class="col-sm-3 col-form-label">Region</label>
                                                <div class="col-sm-7">
                                                    <select class="form-control editInput selectOptions get_region_result"
                                                        id="site_region">
                                                        <option selected disabled>None</option>
                                                        <?php foreach($region as $siteregion_val){?>
                                                            <option value="{{$siteregion_val->id}}">{{$siteregion_val->name}}</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" id="openPopupButton" onclick="get_modal(3)"><i
                                                            class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputAddress"
                                                    class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                                <div class="col-sm-9">
                                                    <textarea class="form-control textareaInput" id="site_address" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCity"
                                                    class="col-sm-3 col-form-label">City</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_city">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCounty"
                                                    class="col-sm-3 col-form-label">County</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_country_input">
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputPincode"
                                                    class="col-sm-3 col-form-label">Pincode</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control editInput"
                                                        id="site_pincode">
                                                </div>
                                            </div>

                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Country</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="site_modal_country_id">
                                                        <option selected disabled>Select Coutry</option>
                                                        <?php foreach($country as $sitecountryval){?>
                                                        <option value="{{$sitecountryval->id}}" class="site_modal_country_id">{{$sitecountryval->name}} ({{$sitecountryval->code}})</option>
                                                        <?php }?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-2 row">
                                                <label for="inputCountry"
                                                    class="col-sm-3 col-form-label">Default Catalogue</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control editInput selectOptions"
                                                        id="site_catalogue_id">
                                                        <option selected disabled>None</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-12 col-xl-12">
                                    <div class="mb-2 row">
                                        <label for="inputCountry"
                                            class="col-sm-3 col-form-label">Default Catalogue</label>
                                        <div class="col-sm-12">
                                            <textarea name="" id="site_notes" class="form-control" rows="10" cols="15"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- End row -->

                        </div>
                        <div class="modal-footer customer_Form_Popup">

                            <button type="button" class="profileDrop" onclick="save_site()">Save</button>
                            <button type="button" class="profileDrop" onclick="save_siteClose()">Save &
                                Close</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
         <!-- end here -->
          <!-- Job Type Modal start here -->
          <div class="modal fade" id="job_type_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content add_Customer">
                    <div class="modal-header">
                        <h5 class="modal-title" id="thirdModalLabel">Add Job Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="job_type_form">
                            <div class="mb-3 row">
                                <label for="inputJobRef" class="col-sm-3 col-form-label">Job Type <span class="radStar ">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" name="job_type_name" class="form-control editInput" id="job_type_name" value="" placeholder="Job Type">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="inputJobRef" class="col-sm-3 col-form-label">Number of Days</label>
                                <div class="col-sm-9">
                                    <input type="text" name="no_of_days" class="form-control editInput" id="no_of_days" value="14">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="inputJobRef" class="col-sm-3 col-form-label">Customer Visible</label>
                                <div class="col-sm-9">
                                    <input type="checkbox" name="customer_visible" class="" id="customer_visible" checked> Yes, visible to customer
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="inputJobRef" class="col-sm-3 col-form-label">Appointment Type</label>
                                <div class="col-sm-9">
                                    <select id="Job_appointment_type" name="Job_appointment_type" class="form-control editInput">
                                        <?php foreach($appointment_type as $job_app){?>
                                            <option value="{{$job_app->id}}">{{$job_app->name}}</option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-9">
                                    <select id="job_type_status" name="Job_type_status" class="form-control editInput">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="pageTitleBtn">
                                <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_job_type()"> Save</a>
                                <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_close_job_type()"> Save & Close</a>
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

        </div>
    </div>
</section>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script>
    initMultiStepForm();

    function initMultiStepForm() {
        const progressNumber = document.querySelectorAll(".step").length;
        const slidePage = document.querySelector(".slide-page");
        const submitBtn = document.querySelector(".submit");
        const progressText = document.querySelectorAll(".step p");
        const progressCheck = document.querySelectorAll(".step .check");
        const bullet = document.querySelectorAll(".step .bullet");
        const pages = document.querySelectorAll(".page");
        const nextButtons = document.querySelectorAll(".next");
        const prevButtons = document.querySelectorAll(".prev");
        const stepsNumber = pages.length;

        if (progressNumber !== stepsNumber) {
            console.warn(
                "Error, number of steps in progress bar do not match number of pages"
            );
        }

        document.documentElement.style.setProperty("--stepNumber", stepsNumber);

        let current = 1;

        for (let i = 0; i < nextButtons.length; i++) {
            nextButtons[i].addEventListener("click", function(event) {
                event.preventDefault();

                inputsValid = validateInputs(this);
                // inputsValid = true;

                if (inputsValid) {
                    get_form(i);
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    $('.alert').show();
                    setTimeout(function() {
                        slidePage.style.marginLeft = `-${(100 / stepsNumber) * current
                            }%`;
                        bullet[current - 1].classList.add("active");
                        progressCheck[current - 1].classList.add("active");
                        progressText[current - 1].classList.add("active");
                        current += 1;
                        $('.alert').hide();
                    }, 3000);
                }
            });
        }

        for (let i = 0; i < prevButtons.length; i++) {
            prevButtons[i].addEventListener("click", function(event) {
                event.preventDefault();
                slidePage.style.marginLeft = `-${(100 / stepsNumber) * (current - 2)
                }%`;
                bullet[current - 2].classList.remove("active");
                progressCheck[current - 2].classList.remove("active");
                progressText[current - 2].classList.remove("active");
                current -= 1;
            });
        }
        submitBtn.addEventListener("click", function() {
            bullet[current - 1].classList.add("active");
            progressCheck[current - 1].classList.add("active");
            progressText[current - 1].classList.add("active");
            current += 1;
            setTimeout(function() {
                alert("Your Form Successfully Done");
                location.reload();
            }, 800);
        });

        // function validateInputs(ths) {
        //     let inputsValid = true;

        //     const inputs =
        //         ths.parentElement.parentElement.querySelectorAll("input");
        //     for (let i = 0; i < inputs.length; i++) {
        //         const valid = inputs[i].checkValidity();
        //         if (!valid) {
        //             inputsValid = false;
        //             inputs[i].classList.add("invalid-input");
        //         } else {
        //             inputs[i].classList.remove("invalid-input");
        //         }
        //     }
        //     return inputsValid;
        // }Ram
        function validateInputs(ths) {
            let inputsValid = true;
            const inputs = ths.parentElement.parentElement.querySelectorAll("input, select, textarea");
            for (let i = 0; i < inputs.length; i++) {
                const element = inputs[i];
                let valid = true;
                if (element.tagName.toLowerCase() === "select") {
                    valid = element.value !== "";
                } else {
                    valid = element.checkValidity();
                }
                if (!valid) {
                    inputsValid = false;
                    element.classList.add("invalid-input");
                } else {
                    element.classList.remove("invalid-input");
                }
            }
            return inputsValid;
        }
    }
</script>
<script>
    function get_customer_details() {
        var customer_id = $("#customer_id").val();
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
                    $("#company").val(customerData.name)
                    $("#contact").val(customerData.mobile);
                    $("#project_customer_name").text(customerData.contact_name);
                    $("#contact_customer_name").text(customerData.contact_name);
                    $("#site_customer_name").text(customerData.contact_name);
                    $("#project_customer_id").val(customerData.id);
                    $("#contact_customer_id").val(customerData.id);
                    $("#site_customer_id").val(customerData.id);
                    $("#profession_name").val(customerData.contact_name + " (" + customerData.customer_profession.name + ")");
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
                });



            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }

    function get_form(form_id) {
        // alert(form_id)
        if (form_id == 0 || form_id == 1 || form_id == 2) {
            get_saveFromData();
        } else if (form_id == 4) {
            get_save_appointment();
        } else if (form_id == 3 || form_id == 5 || form_id == 6) {
            return true;
        } else {
            return false;
        }
    }

    function get_saveFromData() {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/job_add_edit_save')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                $("#id").val(data.id);
                // $("#site_customer_id").val(data.id);
                // $("#login_customer_id").val(data.id);
                // $(".customer_name").text(data.name);
            }
        });
    }

    function save_job_product() {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/save_job_product')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                $("#id").val(data.id);

            }
        });
    }

    function get_save_appointment() {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/get_save_appointment')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                $("#id").val(data.id);

            }
        });
    }
</script>
<script>
    function get_search() {
        var search_value = $("#search_value").val();
        var token = '<?php echo csrf_token(); ?>'
        if (search_value.length > 2) {
            $.ajax({
                type: "POST",
                url: "{{url('search_value_front')}}",
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

    function selectProduct(id) {
        // alert(id)
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('result_product_calculation')}}",
            data: {
                id: id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                $("#product_result").html(data);

            }
        });
        $("#temp_result1").hide();
    }

    function removeRow(button) {
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    function get_data_product() {
        $('#product_model').modal('hide');
    }

    function get_delete_jobproduct(id) {
        if (confirm("Do you want to delete it ?")) {
            var token = '<?php echo csrf_token(); ?>'
            var table = 'jobs';
            $.ajax({
                type: "POST",
                url: "{{url('delete_function')}}",
                data: {
                    id: id,
                    table: table,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    window.location.reload();
                }
            });
        }
    }

    function get_notes() {
        var token = '<?php echo csrf_token(); ?>'
        var id = $("#id").val();
        var last_job_id = $("#last_job_id").val();
        var customer_notes = $("#customer_notes").val();
        var internal_notes = $("#internal_notes").val()
        $.ajax({
            type: "POST",
            url: "{{url('job_add_edit_save')}}",
            data: {
                id: id,
                last_job_id: last_job_id,
                customer_notes: customer_notes,
                internal_notes: internal_notes,
                _token: token
            },
            success: function(data) {
                console.log(data);
                // window.location.reload();
            }
        });
    }

    function get_attachment_save() {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/job_add_edit_save')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                $("#id").val(data.id);
            }
        });
    }

    function new_appointment() {
        var token = '<?php echo csrf_token(); ?>';
        var count_number = $('#count_number').val();

        $.ajax({
            type: "POST",
            url: "{{url('/new_appointment_add_section')}}",
            data: {
                count_number: count_number,
                _token: token
            },
            success: function(data) {
                console.log(data);
                $("#appointment_table tbody").last().append(data);
                count_number++;
                $('#count_number').val(count_number);
            },
            error: function(xhr, status, error) {
                console.error("An error occurred: " + error);
            }
        });
    }

    function deleteRow(element) {
        // element.closest('tbody').remove();
        $(element).closest('tr').nextUntil('tr:has(td[colspan])').addBack().remove();
        $(element).closest('tr').remove();
    }

    function get_time() {
        var input_time1 = $("#input_time1").val();
        var input_time2 = $("#input_time2").val();
        var minutes;
        if (input_time2) {
            minutes = input_time2;
        } else {
            minutes = input_time1;
        }
        var hours = Math.floor(minutes / 60);
        var remainingMinutes = minutes % 60;
        $("#appointment_time").val(hours + " h " + remainingMinutes + " mins");
        $("#time_show").text(hours + " h " + remainingMinutes + " mins")
        // return hours + " hours and " + remainingMinutes + " minutes";
    }
</script>
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

    CKEDITOR.replace('textarea1', editor_config);
    CKEDITOR.replace('textarea2', editor_config);
    CKEDITOR.replace('textarea3', editor_config);
    //Text Editer




    const openPopupButton = document.getElementById('openPopupButton');
    const popup = document.getElementById('popup');
    const closePopup = document.getElementById('closePopup');

    openPopupButton.addEventListener('click', () => {
        popup.style.display = 'block';
        setTimeout(() => {
            popup.style.opacity = '1';
        }, 50); // Delay added for transition effect
    });

    closePopup.addEventListener('click', () => {
        popup.style.opacity = '0';
        setTimeout(() => {
            popup.style.display = 'none';
        }, 300); // Ensure the popup is hidden after the transition ends
    });
</script>
<!-- Modal code -->
 <script>
    function get_modal(modal){  
        // alert(modal)
        var customer_select_check=$("#customer_id").val();
        if(customer_select_check == null && (modal == 4 || modal == 5)){
            alert("Please select customer");
            return false;
        }else{
            if(modal == 1){
                $("#customer_type_form")[0].reset();
                $("#cutomer_type_modal").modal('show');
            }else if(modal == 2){
                $("#job_title_form")[0].reset();
                $("#job_title_modal").modal('show');
            }else if(modal == 3){
                $("#region_form")[0].reset();
                $("#region_modal").modal('show');
            }else if(modal == 4){
                $("#project_form")[0].reset();
                $("#project_modal").modal('show');
            }else if(modal == 5){
                $("#contact_form")[0].reset();
                $("#contact_modal").modal('show');
            }else if(modal == 6){
                $("#site_form")[0].reset();
                $("#site_modal").modal('show');
            }else if(modal == 7){
                $("#job_type_form")[0].reset();
                $("#job_type_modal").modal('show');
            }else if(modal == 8){

            }
        }
        
    }
 </script>

<!-- end here -->
<script>
    function save_customer_type(){
       var token='<?php echo csrf_token();?>'
       var name=$("#customer_type_name").val();
       var status=$("#customer_type_status").val();
       var home_id=$("#home_id").val();
       if(name == ''){
        $("#customer_type_name").addClass('invalid-input');
        return false;
       }else {
            $.ajax({
                type: "POST",
                url: "{{url('/save_customer_type')}}",
                data: {name:name,status:status,home_id:home_id,_token:token},
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
        $("#job_title_name").addClass('invalid-input');
        return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{url('/save_job_title')}}",
                data: {name:name,status:status,home_id:home_id,_token:token},
                success: function(data) {
                    console.log(data);
                    if($.trim(data) == 'error'){
                        alert("Something went wrong");
                        return false;
                    }else{
                        $("#job_title_modal").modal('hide');
                        $('.get_job_title_result').append(data);
                        // window.location.reload();
                    }
                    $("#job_title_modal").modal('hide');
                }
            });
        }
    }
    function save_region(){
        var token='<?php echo csrf_token();?>'
        var name=$("#region_name").val();
        var status=$("#region_status").val();
        var home_id=$("#home_id").val();
        if(name == ''){
        $("#region_name").addClass('invalid-input');
        return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{url('/save_region')}}",
                data: {name:name,status:status,home_id:home_id,_token:token},
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
    function save_customerClose(){
        save_customer();
        // $("#customerPop").modal('hide');

    }
    function save_customer(){
        var token='<?php echo csrf_token();?>'
        var name=$("#customer_name").val();
        var home_id=$("#home_id").val();
        var customer_type_id=$("#customer_type_id").val();
        var contact_name=$("#customer_contact_name").val();
        var job_title=$("#customer_job_titile_id").val();
        var email=$("#customer_email").val();
        var telephone=$("#customer_phone").val();
        var mobile=$("#customer_mobile").val();
        var fax=$("#customer_fax").val();
        var website=$("#customer_website").val();
        var catalogue_id=$("#customer_catalogue").val();
        var region=$("#customer_region").val();
        var address=$("#cuatomer_address").val();
        var city=$("#customer_city").val();
        var country=$("#customer_country_input").val();
        var postal_code=$("#customer_pincode").val();
        var country_code=$("#customer_country_id").val();
        var currency=$("#customer_currency_id").val();
        var credit_limit=$("#customer_credit_limit").val();
        var discount=$("#customer_discount").val();
        var discount_type=$("#customer_percentage").val();
        var saga_ref=$("#customer_sage_ref").val();
        
        var site_notes=$("#customer_site_note").val();
        var vat_tax_no=$("#customer_vat").val();
        var payment_terms=$("#customer_payment_terms").val();
        var assigned_product=0;
        if($("#customer_yes").is(':checked')){
            assigned_product=1;
        }
        var notes=$("#customer_note").val();
        var is_converted=1;
        var status=$("#customer_status").val();

        $.ajax({
            type: "POST",
            url: "{{url('/customer_add_edit_save')}}",
            data: {status:status,is_converted:is_converted,notes:notes,assigned_product:assigned_product,payment_terms:payment_terms,vat_tax_no:vat_tax_no,site_notes:site_notes,saga_ref:saga_ref,discount_type:discount_type,discount:discount,credit_limit:credit_limit,currency:currency,country_code:country_code,postal_code:postal_code,country:country,city:city,address:address,region:region,catalogue_id:catalogue_id,website:website,fax:fax,mobile:mobile,telephone:telephone,email:email,name:name,status:status,home_id:home_id,customer_type_id:customer_type_id,contact_name:contact_name,job_title:job_title,_token:token},
            success: function(data) {
                console.log(data);
                window.location.reload();
            }
        });

    }
    function save_project(){
        var token='<?php echo csrf_token();?>'
        var project_name=$("#project_name").val();
        var home_id=$("#home_id").val();
        var start_date=$("#project_start_date").val();
        var end_date=$("#project_end_date").val();
        var project_value=$("#project_value").val();
        var description=$("#project_description").val();
        var status=$("#project_status").val();
        var customer_name=$("#project_customer_id").val();
        if(project_name == ''){
            $('#project_name').css('border','1px solid red');
            // $(window).scrollTop($('#project_name').position().top);
            return false;
        }else if(start_date == ''){
            $('#project_name').css('border','');
            $("#project_start_date").css('border','1px solid red');
            return false;
        }else if(end_date == ''){
            $("#project_start_date").css('border','');
            $("#project_end_date").css('border','1px solid red');
            return false;
        }else {
            $("#project_end_date").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/project_save')}}",
                data: {customer_name:customer_name,description:description,project_value:project_value,end_date:end_date,start_date:start_date,home_id:home_id,project_name:project_name,status:status,_token:token},
                success: function(data) {
                    console.log(data);
                    $("#project_modal").modal('hide');
                    $("#project_id").append(data);
                }
            });
        }
    }
    function default_address(){
        if ($('#contact_default_address').is(':checked')) {
            var customer_id = $("#customer_id").val();
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
                    data.forEach(contactData => {
                    $("#name").val(contactData.name);
                    $("#email").val(contactData.email);
                    $("#telephone").val(contactData.telephone);
                    $("#mobile").val(contactData.mobile);
                    $("#contact_address").val(contactData.address);
                    $("#contact_city").val(contactData.city);
                    $("#contact_country_input").val(contactData.country);
                    $("#contact_pincode").val(contactData.postal_code);
                    
                    $(".contact_country_id").each(function() {
                        if ($(this).val() === contactData.country_code) {
                            $(this).prop('selected', true);
                        }
                    });
            });  
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        }else {
            $("#name").val('');
            $("#email").val('');
            $("#telephone").val('');
            $("#mobile").val('');
            $("#contact_address").val('');
            $("#contact_city").val('');
            $("#contact_country_input").val('');
            $("#contact_pincode").val('');
            $("#contact_country_id").val('');
        }
    }
    function save_contactClose(){
        save_contact();
        $("#contact_modal").modal('hide');
    }
    function save_contact(){
        var token='<?php echo csrf_token();?>'
        var default_billing; 
        if ($('#contact_default_yes').is(':checked')) {
            default_billing=1;
        }else {
            default_billing=0;
        }
        var customer_id=$("#contact_customer_id").val();
        var contact_name=$("#contact_contact_name").val();
        // alert(contact_name)
        var job_title_id=$("#contact_job_titile_id").val();
        var email=$("#contact_email").val();
        var telephone=$("#contact_phone").val();
        var mobile=$("#contact_mobile").val();
        var fax=$("#contact_fax").val();
        var address=$("#contact_address").val();
        var city=$("#contact_city").val();
        var country=$("#contact_country_input").val();
        var postcode=$("#contact_pincode").val();
        var country_id=$("#contact_country_id").val();
        
        if(contact_name == ''){
            $('#contact_contact_name').css('border','1px solid red');
            return false;
        }else if(address == ''){
            $('#contact_contact_name').css('border','');
            $("#contact_address").css('border','1px solid red');
            return false;
        }else {
            $("#project_end_date").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/contact_save')}}",
                data: {country_id:country_id,postcode:postcode,country:country,city:city,address:address,fax:fax,mobile:mobile,telephone:telephone,email:email,job_title_id:job_title_id,contact_name:contact_name,customer_id:customer_id,default_billing:default_billing,_token:token},
                success: function(data) {
                    console.log(data);
                    $("#contact_modal").modal('hide');
                    $("#contact_id").append(data);
                }
            });
        }
    }
    function save_siteClose(){
        save_site();
        $("#site_modal").modal('hide');
    }
    function save_site(){
        var token='<?php echo csrf_token();?>'
        var site_name=$("#site_name").val();
        var contact_name=$("#site_contact_name").val();
        var title_id=$("#site_job_titile_id").val();
        var email=$("#site_email").val();
        var telephone=$("#site_phone").val();
        var mobile=$("#site_mobile").val();
        var fax=$("#site_fax").val();
        var region=$("#site_region").val();
        var address=$("#site_address").val();
        var city=$("#site_city").val();
        var country=$("#site_country_input").val();
        var post_code=$("#site_pincode").val();
        var country_id=$("#site_modal_country_id").val();
        var catalogue=$("#site_catalogue_id").val();
        var notes=$("#site_notes").val();
        var site_customer_name=$("#site_customer_name").text();
        var site_company_name=$("#site_company_name").val();
        var customer_id=$("#site_customer_id").val();
        if(site_name == ''){
            $('#site_name').css('border','1px solid red');
            return false;
        }else if(contact_name == ''){
            $('#site_name').css('border','');
            $("#site_contact_name").css('border','1px solid red');
            return false;
        }else if(address == ''){
            $('#site_contact_name').css('border','');
            $("#site_address").css('border','1px solid red');
            return false;
        }else {
            $("#site_address").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/site_save')}}",
                data: {customer_id:customer_id,site_company_name:site_company_name,site_customer_name:site_customer_name,notes:notes,catalogue:catalogue,country_id:country_id,post_code:post_code,country:country,city:city,address:address,region:region,fax:fax,mobile:mobile,telephone:telephone,email:email,title_id:title_id,contact_name:contact_name,site_name:site_name,_token:token},
                success: function(data) {
                    console.log(data);
                    $("#site_modal").modal('hide');
                    $("#site_id").append(data);
                }
            });
        }


    }
    function save_close_job_type(){
        save_job_type();
        $("#job_type_modal").modal('hide');
    }
    function save_job_type(){
        var token='<?php echo csrf_token();?>'
        var name=$("#job_type_name").val();
        var home_id=$("#home_id").val();
        var default_days=$("#no_of_days").val();
        var customer_visible=0;
        if ($('#customer_visible').is(':checked')) {
            customer_visible=1;
        }
        var appointment_id=$("#Job_appointment_type").val();
        var status=$("#job_type_status").val();
        if(name == ''){
            $('#name').css('border','1px solid red');
            return false;
        }else {
            $("#name").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/job_type_save?key=from_job')}}",
                data: {status:status,appointment_id:appointment_id,customer_visible:customer_visible,default_days:default_days,home_id:home_id,name:name,_token:token},
                success: function(data) {
                    console.log(data);
                    $("#job_type_modal").modal('hide');
                    $("#job_type").append(data);
                }
            });
        }
    }
</script>

@include('frontEnd.jobs.layout.footer')