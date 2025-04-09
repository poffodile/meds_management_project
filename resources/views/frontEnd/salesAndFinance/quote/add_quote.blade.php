@include('frontEnd.salesAndFinance.jobs.layout.header')


<section class="main_section_page px-3 pt-0">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>New Quote</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                    <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i>
                        </span></a>
                </div>
            </div>
        </div>
        <!--  -->

        <div class="row">
            <div class="col-lg-12">
                <div class="newJobForm card">
                    <div class="row">
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle">Customer Details</h4>
                                <form action="" class="customerForm">

                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Quote Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput"
                                                id="inputName" value="Auto generate" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer
                                            <span class="radStar">*</span>
                                        </label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>-Not Assigned-</option>
                                                <option>Customer-2</option>
                                                <option>Customer-3</option>
                                                <option>Customer-4</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon" data-bs-toggle="modal"
                                                    data-bs-target="#newQuotePop"><i
                                                        class="fa-solid fa-square-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- start Customer popup-->
                                    <div class="modal fade" id="newQuotePop" tabindex="-1"
                                        aria-labelledby="customerModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content add_Customer">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="customerModalLabel">Add Customer
                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 col-lg-6 col-xl-6">
                                                            <div class="formDtail">
                                                                <form action="" class="customerForm">
                                                                    <div class="mb-2 row">
                                                                        <label for="inputName"
                                                                            class="col-sm-3 col-form-label">Customer
                                                                            Name*</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputName"
                                                                                value="John Smith">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputCustomer"
                                                                            class="col-sm-3 col-form-label">Customer
                                                                            Type*</label>
                                                                        <div class="col-sm-7">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>Genrale Customer
                                                                                </option>
                                                                                <option>Analytical Customer
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-1">
                                                                            <a href="#!" class="formicon"><i
                                                                                    class="fa-solid fa-square-plus"></i></a>
                                                                        </div>
                                                                    </div><!-- End off Customer -->

                                                                    <div class="mb-2 row">
                                                                        <label for="inputName"
                                                                            class="col-sm-3 col-form-label">Conatact
                                                                            Name*</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputName"
                                                                                value="John Smith">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputProject"
                                                                            class="col-sm-3 col-form-label">Job
                                                                            Title (Position)</label>
                                                                        <div class="col-sm-4">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>Please Select
                                                                                </option>
                                                                                <option>Supervisor</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <a href="#!" class="formicon"><i
                                                                                    class="fa-solid fa-square-plus"></i></a>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputEmail"
                                                                            class="col-sm-3 col-form-label">Email</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputEmail"
                                                                                value="roxy.scits@gmail.com">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputTelephone"
                                                                            class="col-sm-3 col-form-label">Telephone</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputTelephone"
                                                                                value="14000883788">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputMobile"
                                                                            class="col-sm-3 col-form-label">Mobile</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputMobile"
                                                                                value="Type No.">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputAddress"
                                                                            class="col-sm-3 col-form-label">Fax</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCity" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputCity"
                                                                            class="col-sm-3 col-form-label">Website</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCity"
                                                                                value="Port Elizabeth">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputProject"
                                                                            class="col-sm-3 col-form-label">Payment
                                                                            Terms</label>
                                                                        <div class="col-sm-7">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>Defoult (21)
                                                                                </option>
                                                                                <option>0</option>
                                                                                <option>1</option>
                                                                                <option>2</option>
                                                                                <option>3</option>
                                                                                <option>4</option>
                                                                                <option>5</option>
                                                                                <option>6</option>
                                                                                <option>7</option>
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
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>British Pound - GBP
                                                                                </option>
                                                                                <option>0</option>
                                                                                <option>1</option>
                                                                                <option>2</option>
                                                                                <option>3</option>
                                                                                <option>4</option>
                                                                                <option>5</option>
                                                                                <option>6</option>
                                                                                <option>7</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>


                                                                    <div class="mb-2 row">
                                                                        <label for="inputCounty"
                                                                            class="col-sm-3 col-form-label">Credit
                                                                            Limit</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCounty" value="$: 0">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputPincode"
                                                                            class="col-sm-3 col-form-label">Discount</label>
                                                                        <div class="col-sm-4">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputPincode" value="6001">
                                                                        </div>
                                                                        <div class="col-sm-5">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>Persontage</option>
                                                                                <option>Flat</option>
                                                                                <option>1</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputCountry"
                                                                            class="col-sm-3 col-form-label">VAT
                                                                            / Tax No.</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCountry" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputProject"
                                                                            class="col-sm-3 col-form-label">Default
                                                                            Catalogue</label>
                                                                        <div class="col-sm-9">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>None</option>
                                                                                <option>ABCD</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputProject"
                                                                            class="col-sm-3 col-form-label">Status</label>
                                                                        <div class="col-sm-9">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>Active</option>
                                                                                <option>Inactive</option>
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
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>None</option>
                                                                                <option>Default</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            <a href="#!" class="formicon"
                                                                                id="PopupQuote2Button"><i
                                                                                    class="fa-solid fa-square-plus"></i></a>
                                                                        </div>


                                                                        <!--Start Region Popup -->

                                                                        <div id="popup2Quote" class="popup">
                                                                            <div class="popup-content">
                                                                                <div class="popupTitle">
                                                                                    <span class="">Add
                                                                                        Region</span>
                                                                                    <span class="close"
                                                                                        id="close2Quote">&times;</span>
                                                                                </div>
                                                                                <div class="contantbodypopup">
                                                                                    <form action=""
                                                                                        class="customerForm">
                                                                                        <div class="mb-2 row">
                                                                                            <label
                                                                                                for="inputCity"
                                                                                                class="col-sm-3 col-form-label">Region*</label>
                                                                                            <div
                                                                                                class="col-sm-9">
                                                                                                <input
                                                                                                    type="text"
                                                                                                    class="form-control editInput"
                                                                                                    id="inputCity"
                                                                                                    value="Port Elizabeth">
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="mb-2 row">
                                                                                            <label
                                                                                                for="inputCity"
                                                                                                class="col-sm-3 col-form-label">Status</label>
                                                                                            <div
                                                                                                class="col-sm-9">
                                                                                                <select
                                                                                                    class="form-control editInput selectOptions"
                                                                                                    id="inputCustomer">
                                                                                                    <option>
                                                                                                        None
                                                                                                    </option>
                                                                                                    <option>
                                                                                                        Default
                                                                                                    </option>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </form>
                                                                                </div>

                                                                                <div
                                                                                    class="popupF  customer_Form_Popup">

                                                                                    <button type="button"
                                                                                        class="profileDrop">Save</button>
                                                                                    <button type="button"
                                                                                        class="profileDrop">Save
                                                                                        & Close</button>

                                                                                </div>
                                                                            </div>

                                                                        </div>

                                                                        <!-- End off region Popup -->
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputAddress"
                                                                            class="col-sm-3 col-form-label">Address</label>
                                                                        <div class="col-sm-9">
                                                                            <textarea
                                                                                class="form-control textareaInput"
                                                                                name="address" id="inputAddress"
                                                                                rows="3"
                                                                                placeholder="75 Cope Road Mall Park USA"></textarea>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputCity"
                                                                            class="col-sm-3 col-form-label">City</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCity"
                                                                                value="Port Elizabeth">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputCounty"
                                                                            class="col-sm-3 col-form-label">County</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputCounty"
                                                                                placeholder="Site County">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label for="inputPincode"
                                                                            class="col-sm-3 col-form-label">Pincode</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputPincode" value="6001">
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputCountry"
                                                                            class="col-sm-3 col-form-label">Country</label>
                                                                        <div class="col-sm-9">
                                                                            <select
                                                                                class="form-control editInput selectOptions"
                                                                                id="inputCustomer">
                                                                                <option>United kingdom (+44)
                                                                                </option>
                                                                                <option>United kingdom (+44)
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputAddress"
                                                                            class="col-sm-3 col-form-label">Site
                                                                            Notes</label>
                                                                        <div class="col-sm-9">
                                                                            <textarea
                                                                                class="form-control textareaInput"
                                                                                name="address" id="inputAddress"
                                                                                rows="3"
                                                                                placeholder="Site Notes"></textarea>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputName"
                                                                            class="col-sm-3 col-form-label">Sage
                                                                            Ref.</label>
                                                                        <div class="col-sm-9">
                                                                            <input type="text"
                                                                                class="form-control editInput"
                                                                                id="inputName" value="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-2 row">
                                                                        <label
                                                                            class="col-sm-3 col-form-label">Assign
                                                                            Products</label>
                                                                        <div class="col-sm-9">

                                                                            <div
                                                                                class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="inlineRadioOptions"
                                                                                    id="inlineRadio1"
                                                                                    value="option1" checked>
                                                                                <label
                                                                                    class="form-check-label checkboxtext"
                                                                                    for="inlineRadio1">Yes</label>
                                                                            </div>
                                                                            <div
                                                                                class="form-check form-check-inline">
                                                                                <input class="form-check-input"
                                                                                    type="radio"
                                                                                    name="inlineRadioOptions"
                                                                                    id="inlineRadio2"
                                                                                    value="option2">
                                                                                <label
                                                                                    class="form-check-label checkboxtext"
                                                                                    for="inlineRadio2">No</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-2 row">
                                                                        <label for="inputAddress"
                                                                            class="col-sm-3 col-form-label">Notes</label>
                                                                        <div class="col-sm-9">
                                                                            <textarea
                                                                                class="form-control textareaInput"
                                                                                name="address" id="inputAddress"
                                                                                rows="3"
                                                                                placeholder="Site Notes"></textarea>
                                                                        </div>
                                                                    </div>


                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div> <!-- End row -->
                                                </div>
                                                <div class="modal-footer customer_Form_Popup">

                                                    <button type="button" class="profileDrop">Save</button>
                                                    <button type="button" class="profileDrop">Save &
                                                        Close</button>
                                                    <button type="button" class="profileDrop"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End off Customer popup -->








                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput"
                                                id="inputName" value="Auto generate" readonly>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle">Billing Details</h4>
                                <form action="" class="customerForm">
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Contact </label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>-Not Assigned-</option>
                                                <option>Customer-2</option>
                                                <option>Customer-3</option>
                                                <option>Customer-4</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon"><i
                                                        class="fa-solid fa-square-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label"> Name <span
                                                class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="inputEmail"
                                                placeholder="Company Name">
                                        </div>
                                    </div>


                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address <span
                                                class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address"
                                                id="inputAddress" rows="3" placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputCustomer" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputPurchase" placeholder="County">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode
                                        </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputPurchase" placeholder="Postcode">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon"><i
                                                        class="fa-solid fa-magnifying-glass-location"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone
                                        </label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>+91</option>
                                                <option>+441</option>
                                                <option>+735</option>
                                                <option>+7235</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="inputEmail"
                                                placeholder="Telephone ">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>+91</option>
                                                <option>+441</option>
                                                <option>+735</option>
                                                <option>+7235</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="inputEmail"
                                                placeholder="Telephone ">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Email Address
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="inputMobile"
                                                placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="inputMobile"
                                                placeholder="Email Address">
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                        <div class="col-md-4 col-lg-4 col-xl-4">
                            <div class="formDtail">
                                <h4 class="contTitle"> Customer Site Details</h4>
                                <form action="" class="customerForm">
                                    <div class="mb-3 row">
                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Site</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>-Not Assigned-</option>
                                                <option>Customer-2</option>
                                                <option>Customer-3</option>
                                                <option>Customer-4</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon"><i
                                                        class="fa-solid fa-square-plus"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Name </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputCustomer" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Company
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputCustomer" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress"
                                            class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address"
                                                id="inputAddress" rows="3" placeholder="Address"></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputCustomer" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputPurchase" placeholder="County">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode
                                        </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput"
                                                id="inputPurchase" placeholder="Postcode">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon"><i
                                                        class="fa-solid fa-magnifying-glass-location"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone
                                        </label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>+91</option>
                                                <option>+441</option>
                                                <option>+735</option>
                                                <option>+7235</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="inputEmail"
                                                placeholder="Telephone ">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions"
                                                id="inputCustomer">
                                                <option>+91</option>
                                                <option>+441</option>
                                                <option>+735</option>
                                                <option>+7235</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="inputEmail"
                                                placeholder="Telephone ">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country
                                        </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="inputMobile"
                                                placeholder="Email Address">
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                </div> <!-- End  off newJobForm -->

                <div class="newJobForm mt-4">
                    <label class="upperlineTitle">Your Quotes</label>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="extraInformationTab">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <button class="nav-link active" id="nav-Notes-tab" data-bs-toggle="tab"
                                            data-bs-target="#nav-Notes" type="button" role="tab"
                                            aria-controls="nav-Notes" aria-selected="true">Quotes</button>
                                        <button class="nav-link" id="nav-Tasks-tab" data-bs-toggle="tab"
                                            data-bs-target="#nav-Tasks" type="button" role="tab"
                                            aria-controls="nav-Tasks" aria-selected="false">Sales
                                            Appointments</button>

                                    </div>
                                </nav>
                                <div class="tab-content" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="nav-Notes" role="tabpanel"
                                        aria-labelledby="nav-Notes-tab" tabindex="0">
                                        <div class="tabheadingTitle">
                                            <a href="#" class="profileDrop me-3"> Add Quotes</a>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="mb-3 row">
                                                <div class="col-md-12">
                                                    <div class="productDetailTable pt-3">
                                                        <table class="table" id="containerA">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th># </th>
                                                                    <th>Quote Ref </th>
                                                                    <th>Job Ref</th>
                                                                    <th>Quote Date </th>
                                                                    <th>Expiry Date </th>
                                                                    <th>Sub Total</th>
                                                                    <th>VAT</th>
                                                                    <th>Total</th>
                                                                    <th>Deposit </th>
                                                                    <th>Outstanding</th>
                                                                    <th>Status</th>
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

                                            <!-- Button trigger modal -->
                                        </div><!-- ENd col-9 -->
                                    </div>
                                    <div class="tab-pane fade" id="nav-Tasks" role="tabpanel"
                                        aria-labelledby="nav-Tasks-tab" tabindex="0">
                                        <div class="tabheadingTitle">
                                            <a href="#!" class="profileDrop me-3" onclick="insrtAppoinment()"> New Appointments</a>
                                            <a href="send_To_planner.html" class="profileDrop"> Send To Planner</a>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="productDetailTable mt-3">
                                                <table class="table" id="containerA">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>User</th>
                                                            <th>Start Date / Time</th>
                                                            <th>End Date / Time </th>
                                                            <th>Notes</th>
                                                            <th>Appointment Type </th>
                                                            <th>Status </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="add_insrtAppoinment">

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>


     

            </div> <!-- End col-12 -->
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="pageTitleBtn">
                    <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    <a href="#" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i></a>
                </div>
            </div>
        </div>
        <!--  -->
    </div>
</section>


@include('frontEnd.salesAndFinance.jobs.layout.footer')


<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/js/bootstrap.bundle.js"></script>
<script src="https://www.ville-pont-eveque.fr/tools/library/DataTables/media/js/jquery.dataTables.js"></script>
<script
    src="https://www.ville-pont-eveque.fr/tools/library/DataTables/extensions/Select/js/dataTables.select.js"></script>
<script src="https://cdn.zingchart.com/zingchart.min.js"></script>
<script src="https://www.dukelearntoprogram.com/course1/common/js/image/SimpleImage.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script src="assets/js/custom.js"></script>
<script>
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

    CKEDITOR.replace('textarea8', editor_config);
    CKEDITOR.replace('textarea9', editor_config);
    //Text Editer
    function upload() {
        var imgcanvas = document.getElementById("canv1");
        var fileinput = document.getElementById("finput");
        var image = new SimpleImage(fileinput);
        image.drawTo(imgcanvas);
    }
</script>
<script>
    // **********Add Region

    const PopupQuote2Button = document.getElementById('PopupQuote2Button');
    const popup2Quote = document.getElementById('popup2Quote');
    const close2Quote = document.getElementById('close2Quote');

    PopupQuote2Button.addEventListener('click', () => {
        popup2Quote.style.display = 'block';
        setTimeout(() => {
            popup2Quote.style.opacity = '1';
        }, 50); // Delay added for transition effect
    });

    close2Quote.addEventListener('click', () => {
        popup2Quote.style.opacity = '0';
        setTimeout(() => {
            popup2Quote.style.display = 'none';
        }, 300); // Ensure the popup is hidden after the transition ends
    });
    // *******************ENd Add Region

    // **************************(Type to add product or Click Hrer

    const cost_product_popup = document.getElementById('cost_product_popup');
    const costProPopupcont = document.getElementById('costProPopupcont');
    const closeCostPopup = document.getElementById('closeCostPopup');

    cost_product_popup.addEventListener('click', () => {
        costProPopupcont.style.display = 'block';
        setTimeout(() => {
            costProPopupcont.style.opacity = '1';
        }, 50);
    });

    closeCostPopup.addEventListener('click', () => {
        costProPopupcont.style.opacity = '0';
        setTimeout(() => {
            costProPopupcont.style.display = 'none';
        }, 300);
    });
    // **************************End (Type to add product or Click Hrer

    // *********************************Product Cetagory Popup

    const prod_catgry_smallPop = document.getElementById('prod_catgry_smallPop');
    const quote_catgsmallpop = document.getElementById('quote_catgsmallpop');
    const close_quote_catagPopup = document.getElementById('close_quote_catagPopup');

    prod_catgry_smallPop.addEventListener('click', () => {
        quote_catgsmallpop.style.display = 'block';
        setTimeout(() => {
            quote_catgsmallpop.style.opacity = '1';
        }, 50);
    });

    close_quote_catagPopup.addEventListener('click', () => {
        quote_catgsmallpop.style.opacity = '0';
        setTimeout(() => {
            quote_catgsmallpop.style.display = 'none';
        }, 300);
    });
    // **************************End Product Cetagory Popup

    // *********************************quotr_salesTaxRate Popup

    const quotr_salesTaxRatepopup = document.getElementById('quotr_salesTaxRatepopup');
    const quote_salesTax_popup = document.getElementById('quote_salesTax_popup');
    const closequote_salesTaxPopup = document.getElementById('closequote_salesTaxPopup');

    quotr_salesTaxRatepopup.addEventListener('click', () => {
        quote_salesTax_popup.style.display = 'block';
        setTimeout(() => {
            quote_salesTax_popup.style.opacity = '1';
        }, 50);
    });

    closequote_salesTaxPopup.addEventListener('click', () => {
        quote_salesTax_popup.style.opacity = '0';
        setTimeout(() => {
            quote_salesTax_popup.style.display = 'none';
        }, 300);
    });
    // **************************End quotr_salesTaxRate Popup
</script>

<script>
    //**************insrtProduct
    function insrtProduct() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt");
        node.innerHTML = `

                <td>
                    <div class="CSPlus">
                        <span class="plusandText">
                            <a href="#!" class="formicon pt-0 me-2" onclick="insrtProduct()"> <i
                                    class="fa-solid fa-square-plus"></i> </a>
                            <input type="text"
                                class="form-control editInput input80"
                                value="CS-0001">
                        </span>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput"
                            value="CS-0001">
                    </div>
                </td>
                <td>
                    <div class="">
                        <textarea class="form-control textareaInput" name="address"
                            id="inputAddress" rows="2"
                            placeholder="Address"></textarea>
                    </div>
                </td>
                <td>
                    <div class="">
                        <select class="form-control editInput selectOptions"
                            id="inputCustomer">
                            <option>No account</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="1">
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="100.00">
                    </div>
                </td>
                <td>
                    <div class="calculatorIcon">
                        <span class="plusandText">
                            <a href="#!" class="formicon pt-0"
                                data-bs-toggle="modal"
                                data-bs-target="#calculatePop"> <span
                                    class="material-symbols-outlined">
                                    calculate
                                </span> </a>
                        </span>
                    </div>

                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="90.00">
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="0">
                    </div>
                </td>
                <td>
                    <div class="">
                        <select class="form-control editInput selectOptions"
                            id="inputCustomer">
                            <option>Please Select</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="d-flex">
                        <input type="text"
                            class="form-control editInput input50 me-2" value="0">
                        <select class="form-control editInput selectOptions input50"
                            id="inputCustomer">
                            <option>Please Select</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <span>$90.00</span>
                </td>
                <td>
                    <span>$-10.00</span>
                    <div class="minusnmber pt-1">(-11.11%)</div>
                </td>
                <td>
                    <div class="statuswating">
                        <span class="oNOfswich">
                            <input type="checkbox">
                        </span>
                        <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </td>
            
            `;
        const tableBody = document.querySelector(".add_table_insrt");
        if (tableBody) {
            tableBody.appendChild(node);

            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_table_insrt' not found.");
        }
    }

    //**************insrtTitle
    function insrtTitle() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt");
        node.innerHTML = `
        <td>
            <div class="CSPlus">
                <span class="plusandText">
                    <a href="#!" onclick="insrtTitle()" class="formicon pt-0 me-2">
                        <i class="fa-solid fa-square-plus"></i>
                    </a>
                    <label>Title*:</label>
                    <input type="text" class="form-control editInput ms-3" placeholder="Type to add product">
                </span>
            </div>
        </td>
        <td colspan="12">
            <input type="text" class="form-control editInput" placeholder="Type to add product">
        </td>
        <td>
            <div class="statuswating">
                <span class="oNOfswich">
                    <input type="checkbox">
                </span>
                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
            </div>
        </td>
    `;
        const tableBody = document.querySelector(".add_table_insrt");
        if (tableBody) {
            tableBody.appendChild(node);

            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_table_insrt' not found.");
        }
    }

    //**************insrtImgappend
    function insrtImgappend() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt"); // Adding the class to the row

        node.innerHTML = `
        <td colspan="13">
            <div class="d-flex">
                <div class="CSPlus">
                    <span class="plusandText pt-1">
                        <a href="#!" onclick="insrtImgappend()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                        <label></label>
                    </span>
                </div>
                <div class="addimg">
                    <img class="insrtImg" src="assets/imagrs/imgad1.png">
                </div>
            </div>
        </td>
        <td>
            <div class="statuswating">
                <span class="oNOfswich">
                    <input type="checkbox">
                </span>
                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
            </div>
        </td>            
    `;

        // Select the table body using a class instead of an ID
        const tableBody = document.querySelector(".add_table_insrt");

        if (tableBody) {
            tableBody.appendChild(node);

            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when the close button is clicked
            });
        } else {
            console.error("Table body with class 'add_table_insrt' not found.");
        }
    }

    //**************insrtDescription
    function insrtDescription() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt");
        node.innerHTML = `
        <td colspan="13">
            <div class="d-flex">
                <div class="CSPlus">
                    <span class="plusandText pt-1">
                        <a href="#!" onclick="insrtDescription()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>    
                    </span>
                </div>
                <input type="text" class="form-control editInput" id="inputCountry" placeholder="Type to add product">
            </div>
        </td>
        <td>
            <div class="statuswating">
                <span class="oNOfswich">
                    <input type="checkbox">
                </span>
                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
            </div>
        </td>                     
    `;


        // Get table body element
        const tableBody = document.querySelector(".add_table_insrt");
        if (tableBody) {
            tableBody.appendChild(node);

            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_table_insrt' not found.");
        }
    }

    //**************insrt Section Title
    function insrtSection() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt");
        node.innerHTML = `
    <td colspan="14" class="p-0">
        <div class="newJobForm">
            <div class="d-flex">
            <div class="CSPlus">
                <span class="plusandText pt-1">
                    <a href="#!" onclick="insrtSection()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                    <label class="secTitle">Section Title* :</label>
                </span>
            </div>
            <input type="text" class="form-control editInput" id="inputCountry" placeholder="Type to add product">
            
            <div class="pageTitleBtn p-0">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                        + Section Insert </a>
                    <div class="dropdown-menu fade-up m-0">
                        <a href="#!" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#productModalBAC">insert Section Product</a>
                        <a href="#!" class="dropdown-item col-form-label" onclick="insrtSectionTtle()">insert Section Title</a>
                        <a href="#!" class="dropdown-item col-form-label" onclick="insrtSectionImg()">insert Section Image</a>
                        <a href="#!" class="dropdown-item col-form-label" onclick="insrtSectionDescription()">insert Section Description</a>
                    </div>
                </div>
            </div>

            <div class="statuswating text-end ps-3">
                <span class="oNOfswich">
                    <input type="checkbox">
                </span>
                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
            </div>
            </div>

            <div class="productDetailTable mt-3">
                <table class="table" id="containerA">
                    <thead class="table-light">
                        <tr>
                            <th>Code </th>
                            <th>Product <i class="fa  fa-info-circle"></i> </th>
                            <th>Description</th>
                            <th>
                                <div class="tableplusBTN">
                                    <span>Account Code </span>
                                    <span class="plusandText ps-3">
                                        <a href="#!" class="formicon pt-0"> <i
                                                class="fa-solid fa-square-plus"></i> </a>
                                    </span>
                                </div>
                            </th>
                            <th>Qty </th>
                            <th>Cost Price($) </th>
                            <th>Cost Calc</th>
                            <th>Price($) </th>
                            <th>Markup(%)</th>
                            <th>VAT(%) </th>
                            <th>Discount </th>
                            <th>Amount </th>
                            <th>Profit </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="add_sectionTitle">
                         <tr>
                            <td>
                    <div class="CSPlus">
                        <span class="plusandText">
                            <a href="#!" class="formicon pt-0 me-2" onclick="insrtProduct()"> <i
                                    class="fa-solid fa-square-plus"></i> </a>
                            <input type="text"
                                class="form-control editInput input80"
                                value="CS-0001">
                        </span>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput"
                            value="CS-0001">
                    </div>
                </td>
                <td>
                    <div class="">
                        <textarea class="form-control textareaInput" name="address"
                            id="inputAddress" rows="2"
                            placeholder="Address"></textarea>
                    </div>
                </td>
                <td>
                    <div class="">
                        <select class="form-control editInput selectOptions"
                            id="inputCustomer">
                            <option>No account</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="1">
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="100.00">
                    </div>
                </td>
                <td>
                    <div class="calculatorIcon">
                        <span class="plusandText">
                            <a href="#!" class="formicon pt-0"
                                data-bs-toggle="modal"
                                data-bs-target="#calculatePop"> <span
                                    class="material-symbols-outlined">
                                    calculate
                                </span> </a>
                        </span>
                    </div>

                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="90.00">
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50"
                            value="0">
                    </div>
                </td>
                <td>
                    <div class="">
                        <select class="form-control editInput selectOptions"
                            id="inputCustomer">
                            <option>Please Select</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="d-flex">
                        <input type="text"
                            class="form-control editInput input50 me-2" value="0">
                        <select class="form-control editInput selectOptions input50"
                            id="inputCustomer">
                            <option>Please Select</option>
                            <option>Default</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <span>$90.00</span>
                </td>
                <td>
                    <span>$-10.00</span>
                    <div class="minusnmber pt-1">(-11.11%)</div>
                </td>
                <td>
                    <div class="statuswating">
                        <span class="oNOfswich">
                            <input type="checkbox">
                        </span>
                        <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </td>    
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </td>                    
    `;
        // Get table body element 
        const tableBody = document.querySelector(".add_table_insrt");
        if (tableBody) {
            tableBody.appendChild(node);
            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_table_insrt' not found.");
        }
    }


    //**************Table inner Table insrtSectionTtle
    function insrtSectionTtle() {
        const node = document.createElement("tr");
        node.classList.add("add_sectionTitle");
        node.innerHTML = `
                  <td>
            <div class="CSPlus">
                <span class="plusandText">
                    <a href="#!" onclick="insrtSectionTtle()" class="formicon pt-0 me-2">
                        <i class="fa-solid fa-square-plus"></i>
                    </a>
                    <label>Title*:</label>
                    <input type="text" class="form-control editInput ms-3" placeholder="Type to add product">
                </span>
            </div>
        </td>
        <td colspan="12">
            <input type="text" class="form-control editInput" placeholder="Type to add product">
        </td>
        <td>
            <div class="statuswating">
                <span class="oNOfswich">
                    <input type="checkbox">
                </span>
                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
            </div>
        </td>   
    `;
        // Get table body element
        const tableBody = document.querySelector(".add_sectionTitle");
        if (tableBody) {
            tableBody.appendChild(node);
            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_sectionTitle' not found.");
        }
    }

    //**************Table inner Table insrtSectionImg
    function insrtSectionImg() {
        const node = document.createElement("tr");
        node.classList.add("add_sectionTitle");
        node.innerHTML = `
                    <td colspan="13">
                        <div class="d-flex">
                            <div class="CSPlus">
                                <span class="plusandText pt-1">
                                    <a href="#!" onclick="insrtSectionImg()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                                    <label></label>
                                </span>
                            </div>
                            <div class="addimg">
                                <img class="insrtImg" src="assets/imagrs/imgad1.png">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="statuswating">
                            <span class="oNOfswich">
                                <input type="checkbox">
                            </span>
                            <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                        </div>
                    </td>  
                    `;
        // Get table body element
        const tableBody = document.querySelector(".add_sectionTitle");
        if (tableBody) {
            tableBody.appendChild(node);
            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_sectionTitle' not found.");
        }
    }

    //**************Table inner Table insrtSectionDescription
    function insrtSectionDescription() {
        const node = document.createElement("tr");
        node.classList.add("add_sectionTitle");
        node.innerHTML = `
                     <td colspan="13">
                            <div class="d-flex">
                                <div class="CSPlus">
                                    <span class="plusandText pt-1">
                                        <a href="#!" onclick="insrtSectionDescription()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>    
                                    </span>
                                </div>
                                <input type="text" class="form-control editInput" id="inputCountry" placeholder="Type to add product">
                            </div>
                        </td>
                        <td>
                            <div class="statuswating">
                                <span class="oNOfswich">
                                    <input type="checkbox">
                                </span>
                                <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                            </div>
                        </td>   
                    `;
        // Get table body element
        const tableBody = document.querySelector(".add_sectionTitle");
        if (tableBody) {
            tableBody.appendChild(node);
            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_sectionTitle' not found.");
        }
    }



    //**************insrtTitle
    function insrtAppoinment() {
        const node = document.createElement("tr");
        node.classList.add("add_insrtAppoinment");
        node.innerHTML = `
              
                <td>
                    <div class="d-flex">
                        <p class="leftNum">1</p>
                        <select class="form-control editInput selectOptions" id="inputJobType">
                            <option>Select user</option>
                            <option>Default</option>
                        </select>
                        <a href="#!" class="callIcon"><i class="fa-solid fa-square-phone"></i></a>
                    </div>
                    <div class="alertBy">
                        <label><strong>Alert By :</strong></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                            <label class="form-check-label" for="inlineCheckbox1">SMS</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                            <label class="form-check-label" for="inlineCheckbox2">Email</label>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="addDateAndTime">
                        <div class="startDate">
                            <input type="date" name="date" class=" editInput">
                            <input type="time" name="time" class=" editInput">
                        </div>
                    </div>
                    <div class="pt-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="floatingAppointment" value="option2">
                            <label class="form-check-label" for="floatingAppointment">Floating Appointment</label>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="addDateAndTime">
                        <div class="endDate">
                            <input type="date" name="date" class=" editInput">
                            <input type="time" name="time" class=" editInput">
                        </div>
                    </div>
                </td>
                <td>
                    <div class="addTextarea">
                        <textarea cols="40" rows="5" placeholder="Type Notes...">Type Notes... </textarea>
                    </div>
                </td>
                <td class="col-2">
                    <div class="appoinment_type">
                        <select class="form-control editInput selectOptions" id="inputJobType">
                            <option>Select user</option>
                            <option>Default</option>
                        </select>
                    </div>
                    <div class="Priority">
                        <label>Priority :</label>
                        <select class="form-control editInput selectOptions" id="inputJobType">
                            <option>Select user</option>
                            <option>Default</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="statuswating">
                        <select class="form-control editInput selectOptions" id="inputJobType">
                            <option>Select user</option>
                            <option>Default</option>
                        </select>
                        <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                    <div class="tabheadingTitle">
                        <a href="#" class="profileDrop me-3"> Notify</a>
                    </div>
                    
                </td>
                                                         
            `;
        const tableBody = document.querySelector(".add_insrtAppoinment");
        if (tableBody) {
            tableBody.appendChild(node);

            // Add event listener to the close button
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function() {
                node.remove(); // Remove the row when close button is clicked
            });
        } else {
            console.error("Table body with ID 'add_insrtAppoinment' not found.");
        }
    }





    // Select all tab buttons
    const tabs = document.querySelectorAll('.nav-item button');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();

            tabs.forEach(button => {
                button.classList.remove('activetb');
                document.querySelector(button.getAttribute('data-bs-target')).classList.remove('show', 'activetb');
            });

            tab.classList.add('activetb');
            document.querySelector(tab.getAttribute('data-bs-target')).classList.add('show', '');
        });
    });
</script>
<script>
    function upload() {
        var imgcanvas = document.getElementById("canv1");
        var fileinput = document.getElementById("finput");
        var image = new SimpleImage(fileinput);
        image.drawTo(imgcanvas);
    }
</script>
</body>

</html>