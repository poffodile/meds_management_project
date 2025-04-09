@include('frontEnd.salesAndFinance.jobs.layout.header')
@section('title','Quotes')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    tfoot.table.totlepayment.add_table_insrt33 tr td {
        font-size: 12px;
        line-height: 22px;
    }

    td.borderNone {
        border: none;
    }

    .tableAmountRight {
        text-align: right;
    }

    .totleBold {
        font-weight: 600;
    }
</style>

<section class="main_section_page px-3 pt-0">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>New Quote</h3>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                @foreach ($errors->all() as $error)
                <div class="alert alert-danger">{{ $error }}</div>
                @endforeach
                <form action="{{ url('/quote/saveQuoteData') }}" method="post" class="customerForm mt-3">
                    @csrf
                    <div class="newJobForm card">
                        <div class="row" id="hideCustomerDetails">
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle">Customer Details</h4>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Quote Ref</label>
                                        <div class="col-sm-9">
                                            <!-- <input type="hidden" name="quote_ref"> -->
                                            <input type="text" class="form-control-plaintext editInput" id="" value="Auto generate" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer <span class="radStar">*</span></label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions" name="customer_id" id="getCustomerList">
                                                <option value="">Select Customer</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" id="OpenAddCustomerModal" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput" id="inputName" value="Draft" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle mb-3">Billing Details</h4>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Contact </label>
                                        <div class="col-sm-7">
                                            <input type="hidden" id="billing_add_id" name="billing_add_id">
                                            <select class="form-control editInput selectOptions" disabled id="billingDetailContact">
                                                <option>Select Customer First</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" id="OpenAddCustomerContact" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label"> Name <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="billingDetailsName" placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address" id="billingDetailsAddress" rows="3" placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="billingCustomerCity" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <!-- <div class="wrapper">
                                                <label for="datepicker">
                                                    <input type="text" id="datepicker" class="form-control editInput" autocomplete="off" placeholder="21/08/2025">
                                                </label>
                                            </div> -->
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerCity" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerCounty" placeholder="County">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerPostcode" placeholder="Postcode">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" class="formicon"><i class="fa-solid fa-magnifying-glass-location"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" name="telephone_country_code" id="billingCustomerTelephoneCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="billingCustomerTelephone" placeholder="Telephone">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" name="mobile_country_code" id="billingCustomerMobileCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="billingCustomerMobile" placeholder="Mobile">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Email Address</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="billingDetailsEmail" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput" name="" id="billingCustomerCountry">
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-4 col-xl-4">
                                <div class="formDtail">
                                    <h4 class="contTitle mb-3"> Customer Site Details</h4>
                                    <div class="mb-3 row">
                                        <label for="inputJobRef" class="col-sm-3 col-form-label">Site</label>
                                        <div class="col-sm-7">
                                            <!-- <input type="hidden" id="customer_site_id" name="customer_site_id"> -->
                                            <select class="form-control editInput selectOptions" disabled id="customerSiteDetails">
                                                <option>-Not Assigned-</option>
                                            </select>
                                            <input type="hidden" name="site_add_id" id="siteCustomerId">
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" id="openCustomerSiteAddress" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="customerSiteName" class="col-sm-3 col-form-label">Name </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteName" placeholder="Name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="customerSiteCompany" class="col-sm-3 col-form-label">Company </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteCompany" placeholder="Company">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address" id="customerSiteAddress" rows="3" placeholder="Address"></textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="customerSiteCity" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteCity" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteCounty" placeholder="County">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSitePostCode" placeholder="Postcode">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" class="formicon"><i class="fa-solid fa-magnifying-glass-location"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone </label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="customerSiteTelephoneCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="customerSiteTelephone" placeholder="Telephone ">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="customerSiteMobileCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="customerSiteMobile" placeholder="Mobile">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country </label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput" name="" id="customerSiteDetailsCountry">
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="hideQuoteDetails">
                            <div class="row">
                                <div class="col-md-4 col-lg-4 col-xl-4">
                                    <div class="formDtail">
                                        <h4 class="contTitle">Customer Details</h4>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Customer <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control-plaintext editInput" id="setCustomerNameInCustomerdetails" readonly>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputCustomer" class="col-sm-3 col-form-label">Project </label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions" name="project_id" id="">
                                                    <option value="">None</option>
                                                    <option value="1">Test 1</option>
                                                    <option value="2">Test 2</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="plusandText">
                                                    <a href="javascript:void(0)" id="" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-xl-4">
                                    <div class="formDtail">
                                        <h4 class="contTitle mb-3">Site / Delivery Details</h4>
                                        <div class="mb-3 row">
                                            <label for="inputName" class="col-sm-3 col-form-label">Site </label>
                                            <div class="col-sm-7">
                                                <input type="hidden" name="site_delivery_add_id" id="site_delivery_add_id">
                                                <select class="form-control editInput selectOptions" id="customerSiteDelivery">
                                                    <option>Same As Customer</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="plusandText">
                                                    <a href="javascript:void(0)" id="openSiteDeliveryModal" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label"> Region <span
                                                    class="radStar">*</span></label>
                                            <div class="col-sm-7">
                                                <select class="form-control editInput selectOptions" name="region" id="siteDeliveryRegions">
                                                    <option>None</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="plusandText">
                                                    <a href="javascript:void(0)" id="" onclick="openRegionModal('siteDeliveryRegions');" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputAddress" class="col-sm-3 col-form-label">Name <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput textareaInput" name="site_name" id="customerSiteDeliveryName" placeholder="Name">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="customerSiteDeliveryCompany" class="col-sm-3 col-form-label">Company </label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput textareaInput" name="company_name" id="customerSiteDeliveryCompany" placeholder="Company">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputPurchase" class="col-sm-3 col-form-label">Address</label>
                                            <div class="col-sm-9">
                                                <textarea class="form-control textareaInput" name="address" id="customerSiteDeliveryAdd" rows="3" placeholder="Address"></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode</label>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control editInput textareaInput" name="company_name" id="customerSiteDeliveryPostCode" placeholder="Postcode">
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="plusandText">
                                                    <a href="#!" class="formicon"><i class="fa-solid fa-magnifying-glass-location"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputEmail" class="col-sm-3 col-form-label">Telephone</label>
                                            <div class="col-sm-2">
                                                <select class="form-control editInput selectOptions" name="telephone_country_code" id="customerSiteDeliveryTelephoneCode">
                                                    <option value="">Please Select</option>
                                                    @foreach($countries as $value)
                                                    <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control editInput" name="telephone" id="customerSiteDeliveryTelephone" placeholder="Telephone">
                                            </div>
                                        </div>

                                        <div class="mb-3 row">
                                            <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                            <div class="col-sm-2">
                                                <select class="form-control editInput selectOptions" name="mobile_country_code" id="customerSiteDeliveryMobileCode">
                                                    <option value="">Please Select</option>
                                                    @foreach($countries as $value)
                                                    <option value="{{ $value->id }}"> + {{ $value->code }} - {{ $value->name}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control editInput" name="mobile" id="customerSiteDeliveryMobile" placeholder="Mobile">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputMobile" class="col-sm-3 col-form-label">Email Address</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" name="email" id="customerSiteDeliveryEmail" placeholder="Email Address">
                                            </div>
                                        </div>
                                        <div class="mb-3 row">
                                            <label for="inputMobile" class="col-sm-3 col-form-label">Country</label>
                                            <div class="col-sm-9">
                                                <select class="form-control editInput" name="country_id" id="customerSiteDeliveryCountry">
                                                    @foreach($countries as $value)
                                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-lg-4 col-xl-4">
                                    <div class="formDtail">
                                        <h4 class="contTitle mb-3"> Quote Details</h4>
                                        <div class="mb-3 row">
                                            <label for="inputJobRef" class="col-sm-3 col-form-label">Quote Ref</label>
                                            <div class="col-sm-9">
                                                <input type="hidden" name="quote_id">
                                                <!-- <input type="hidden" name="quote_ref"> -->
                                                <input type="text" class="form-control-plaintext editInput" id="inputName" value="Auto generate" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="quoteType" class="col-sm-3 col-form-label">Quote Type </label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput" name="quota_type" id="quoteType">
                                                <option value="">-Select-</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" id="OpenQuoteTypeModel" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Quote Date <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control editInput textareaInput" value="<?= date('Y-m-d'); ?>" name="quota_date" id="" required>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Expiry Date</label>
                                        <div class="col-sm-9">
                                            <input type="date" class="form-control editInput textareaInput" name="expiry_date" value="{{ \Carbon\Carbon::now()->addMonth(1)->format('Y-m-d') }}" id="">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer Ref </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="" name="customer_ref" placeholder="Customer Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Customer Job Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="inputPurchase" name="customer_job_ref" placeholder="Customer Job Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Purchase Order Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="" name="purchase_order_ref" placeholder="Purchase Order Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Source</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput" name="source" id="">
                                                <option value="">None</option>
                                                @foreach($quoteSource as $value)
                                                <option value="{{ $value->id}}">{{ $value->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="javascript:void(0)" id="OpenAddQuoteSourceModal" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Prefered Job Date</label>
                                        <div class="col-sm-3">
                                            <input type="date" class="form-control editInput textareaInput" id="inputPurchase" name="performed_job_date">
                                        </div>
                                        <div class="col-sm-6">
                                            <select class="form-control editInput" name="period" id="">
                                                <option value="">Any Time</option>
                                                <option value="AM">AM</option>
                                                <option value="PM">PM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput" name="status" id="">
                                                <option value="Draft">Draft</option>
                                                <option value="Processed">Processed</option>
                                                <option value="Call back">Call back</option>
                                                <option value="Accepted">Accepted</option>
                                                <option value="Rejected">Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Tags</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput" name="tags" id="quoteTag">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" id="OpenAddQuoteTag" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- End  off newJobForm -->
                    <div class="newJobForm mt-4" id="yourQuoteSection">
                        <label class="upperlineTitle">Your Quotes</label>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="extraInformationTab">
                                    <nav>
                                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                            <button class="nav-link active" id="nav-Notes-tab" data-bs-toggle="tab" data-bs-target="#nav-Notes" type="button" role="tab" aria-controls="nav-Notes" aria-selected="true">Quotes</button>
                                            <button class="nav-link" id="nav-Tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-Tasks" type="button" role="tab" aria-controls="nav-Tasks" aria-selected="false">Sales Appointments</button>
                                        </div>
                                    </nav>
                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="nav-Notes" role="tabpanel" aria-labelledby="nav-Notes-tab" tabindex="0">
                                            <div class="tabheadingTitle">
                                                <a href="#" class="profileDrop me-3" id="AddQuoteButton"> Add Quotes</a>
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
                                                                        <th></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Button trigger modal -->
                                            </div><!-- ENd col-9 -->
                                        </div>
                                        <div class="tab-pane fade" id="nav-Tasks" role="tabpanel" aria-labelledby="nav-Tasks-tab" tabindex="0">
                                            <div class="tabheadingTitle">
                                                <a href="#!" class="profileDrop me-3" onclick="insrtAppoinment()"> New Appointments</a>
                                                <a href="#!" class="profileDrop"> Send To Planner</a>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="productDetailTable mt-3">
                                                    <table class="table mt-3" id="containerA">
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
                                                        <tbody class="add_insrtAppoinment"></tbody>
                                                        <tfoot>
                                                            <a href="#!" class="profileDrop"> Save Appointment(s)</a>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End  off Your Quotes -->

                    <!-- End off col-md-12 -->
                    <div id="hideQuoteDiv">
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Items Details</label>
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="mb-3 row">
                                        <label for="inputCountry" class="col-sm-2 col-form-label">Select product</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control editInput" id="search-product" placeholder="Type to add product">
                                        </div>
                                        <div class="parent-container"></div>
                                        <div class="col-sm-7">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon" id="openAddProductModal" onclick="itemsAddProductModal(2)"><i class="fa-solid fa-square-plus"></i> </a>
                                                <span class="afterPlusText"> (Type to view product or <a href="#!" onclick="openProductListModal()">Click here</a> to view all assets)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCountry" class="col-sm-2 col-form-label">Catalogue</label>
                                        <div class="col-sm-3">
                                            <select class="form-control editInput selectOptions" id="">
                                                <option>Default Price</option>
                                                <option>Default</option>
                                                <option>Default</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCountry" class="col-sm-2 col-form-label">Markup Based on</label>
                                        <div class="col-sm-3">
                                            <select class="form-control editInput selectOptions" id="">
                                                <option>Price</option>
                                                <option>Cost Price</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="pageTitleBtn p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link profileDrop" onclick="openProductListModal();">insert Product</a>

                                            <!-- <a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false"> + Insert </a>
                                            <div class="dropdown-menu fade-up m-0" style="">
                                                <a href="#!" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#productModalBAC">insert Product</a>
                                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtTitle()">insert Title</a>
                                                <a href="#!" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#attachmentsPopup">insert Image</a>
                                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtDescription()">insert Description</a>
                                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtSection()">insert Section</a>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>


                                <!-- insrt Image Modal -->
                                <div class="modal fade" id="attachmentsPopup" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Insert Image</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">

                                                <p class="uploadImg">
                                                    <i class="fa fa-cloud-upload"></i>
                                                    <input type="file" multiple="false" accept="image/*" id="finput" onchange="upload()">
                                                </p>
                                                <canvas id="canv1"></canvas>
                                            </div>
                                            <div class="modal-footer">
                                                <div class="pageTitleBtn p-0">
                                                    <a href="#!" class="profileDrop" onclick="insrtImgappend()" data-bs-dismiss="modal"> Apply</a>
                                                    <a href="#!" class="profileDrop" data-bs-dismiss="modal"> Close</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- End insrt Image Modal -->

                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table mb-0" id="quoteProducts">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Code </th>
                                                    <th>Product
                                                        <a href="" class="wrapper">
                                                            <i class="fa  fa-info-circle"></i>
                                                            <div class="custom-tooltip">If Inventory or one of our
                                                                Accounting integrations are active then the Product Name field will
                                                                be locked</div>
                                                        </a>
                                                    </th>
                                                    <th>Description</th>
                                                    <th>
                                                        <div class="tableplusBTN">
                                                            <span>Account Code </span>
                                                            <span class="plusandText ps-3">
                                                                <a href="#!" class="formicon pt-0" onclick="openAccountCodeModal('set')" id=""> <i class="fa-solid fa-square-plus"></i> </a>
                                                            </span>
                                                        </div>
                                                    </th>
                                                    <th>Qty </th>
                                                    <th>Cost Price(£) </th>
                                                    <th>Cost Calc</th>
                                                    <th>Price(£) </th>
                                                    <th>Markup(%)</th>
                                                    <th>VAT(%) </th>
                                                    <th>Discount </th>
                                                    <th class="tableAmountRight">Amount </th>
                                                    <th>Profit
                                                        <a href="" class="wrapper">
                                                            <i class="fa  fa-info-circle"></i>
                                                            <div class="custom-tooltip">Gross profit margin is a measure of profitability that shows the percentage of revenue that exceeds the cost of goods sold (COGS)</div>
                                                        </a>
                                                    </th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody class="add_table_insrt">
                                            </tbody>
                                            <tfoot class="table totlepayment add_table_insrt33" id="containerA">
                                            </tfoot>
                                        </table>

                                        <!-- CalculatePop Modal -->
                                        <div class="modal fade" id="calculatePop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content add_Customer">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="customerModalLabel">Cost calculator </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h4 class="contTitle text-start">Product</h4>
                                                        <div class="productDetailTable mt-2">
                                                            <table class="table" id="containerA">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Code</th>
                                                                        <th>Product </th>
                                                                        <th>Description</th>
                                                                        <th>Qty</th>
                                                                        <th>Cost Price(£)</th>
                                                                        <th>Price(£)</th>
                                                                        <th>Amount(£) </th>
                                                                        <th>Profit(£)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="CSPlus">
                                                                                <span class="plusandText">
                                                                                    <input type="text" class="form-control editInput input80" value="T3-0001">
                                                                                </span>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <input type="text" class="form-control editInput" value="TEst-331">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <textarea class="form-control textareaInput rounded-0" name="address" id="inputAddress" rows="1" placeholder="Address"></textarea>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <select class="form-control editInput selectOptions" id="inputCustomer">
                                                                                    <option>No account</option>
                                                                                    <option>Default</option>
                                                                                    <option>Default</option>
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <input type="text" class="form-control editInput input50" value="1">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <input type="text" class="form-control editInput input50" value="100.00">
                                                                            </div>
                                                                        </td>

                                                                        <td>
                                                                            <div class="">
                                                                                <input type="text" class="form-control editInput input50" value="90.00">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="">
                                                                                <input type="text" class="form-control editInput input50" value="90.00">
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <h4 class="contTitle text-start">Cost</h4>
                                                        <div class="mb-3 mt-2 row">
                                                            <div class="col-sm-3">
                                                                <input type="text" class="form-control editInput" id="inputCountry" placeholder="Type to add product">
                                                            </div>
                                                            <div class="col-sm-7">
                                                                <div class="plusandText">
                                                                    <a href="#!" class="formicon" id="cost_product_popup"><i class="fa-solid fa-square-plus"></i></a>
                                                                    <span class="afterPlusText"> (Type to view product or <a href="#!" onclick="openProductListModal()">Click here</a> to view all assets)</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="costProdut">
                                                            <div class="productDetailTable">
                                                                <table class="table" id="containerA">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Code</th>
                                                                            <th>Product </th>
                                                                            <th>Description</th>
                                                                            <th>Qty</th>
                                                                            <th>Cost Price($)</th>
                                                                            <th>Price($)</th>
                                                                            <th>Amount($) </th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td colspan="7">
                                                                                <div class="addProduvtBg text-center">
                                                                                    <h5 class="addproductCentertext">Add products as costs to get started! <br>Search Products or
                                                                                        <a href="#!">click here</a> to view all products.
                                                                                    </h5>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan="3"></td>
                                                                            <td>Totle</td>
                                                                            <td>0</td>
                                                                            <td>$0.00</td>
                                                                            <td>$0.00</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <!-- End off Modal-body -->
                                                    <div class="modal-footer customer_Form_Popup">
                                                        <button type="button" class="profileDrop">Save</button>
                                                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End offCalculatePop Modal -->

                                        <!-- Modal -->

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End  off newJobForm -->

                        <!-- End off View Product -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Extra Information</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="">
                                        <h4 class="contTitle text-start">Description</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" name="extra_information" id="textarea8"> </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="">
                                        <h4 class="contTitle text-start">Customer Notes</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea9" name="customer_notes"> </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pt-3">
                                        <h4 class="contTitle text-start">Terms</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea10" name="tearms"> </textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pt-3">
                                        <h4 class="contTitle text-start">Internal Notes</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea11" name="internal_notes"> </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End  off newJobForm -->

                    <!--  -->
                    <div id="hideDepositSection">
                        <!-- ***************************************Start deposit Details****************************************** -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Deposit Details</label>
                            <div class="row">
                                <div class="col-sm-3 mb-3 mt-2">
                                    <div class=" p-0">
                                        <a href="#!" class="profileDrop" data-bs-toggle="modal" data-bs-target="#creaditDepositModal">Creadit Deposit</a>
                                        <span class="col-form-label">
                                            or
                                        </span>
                                        <a href="#!" class="profileDrop" data-bs-toggle="modal" data-bs-target="#creaditDepositInvoiceModal">Creadit Deposit Invoice</a>
                                    </div>
                                </div>


                                <!-- ************************* -->
                                <div class="modal fade" id="creaditDepositModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="creaditDepositModalLabel" style="display: none;" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content add_Customer">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-5" id="creaditDepositModalLabel">Creadit Deposit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body ">
                                                <div class="contantbodypopup p-0">

                                                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                                        <li class="nav-item me-2" role="presentation">
                                                            <button class="profileDrop" id="paymentDetails-tab" data-bs-toggle="pill" data-bs-target="#paymentDetails" type="button" role="tab" aria-controls="paymentDetails" aria-selected="false" tabindex="-1">Payment Details</button>
                                                        </li>
                                                        <li class="nav-item me-2" role="presentation">
                                                            <button class="profileDrop" id="billingDetails-tab" data-bs-toggle="pill" data-bs-target="#billingDetails" type="button" role="tab" aria-controls="billingDetails" aria-selected="false" tabindex="-1">Billing Details</button>
                                                        </li>
                                                        <li class="nav-item me-2" role="presentation">
                                                            <button class="profileDrop active activetb" id="paymentType-tab" data-bs-toggle="pill" data-bs-target="#paymentType" type="button" role="tab" aria-controls="paymentType" aria-selected="true">Payment Type</button>
                                                        </li>
                                                    </ul>
                                                    <div class="tab-content" id="pills-tabContent">
                                                        <div class="tab-pane fade" id="paymentDetails" role="tabpanel" aria-labelledby="paymentDetails-tab" tabindex="0">
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Invoice</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName" value="QU-0021 - Quote Date 24/10/2024" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName" value="Prathima" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Totle (inc. VAT)</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName" value="$0.00" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Outstanding Amount</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName" value="$12,000.00" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Persontage
                                                                         <span class="radStar">*</span></label>
                                                                    <div class="col-sm-5">
                                                                        <input type="text" class="form-control editInput" id="inputCity" value="100">
                                                                    </div>
                                                                    <div class="col-sm-1 ps-0">
                                                                        <input class="form-control editInput text-center" value="%" disabled="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Amount (inc. VAT) <span class="radStar">*</span></label>
                                                                    <div class="col-sm-1 pe-0">
                                                                        <input class="form-control editInput text-center" value="$" disabled="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <input type="text" class="form-control editInput" id="inputCity" value="$ 0.00">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Reference <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Reference">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Description <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <textarea class="form-control textareaInput rounded-1" name="address" id="description" rows="3" placeholder="Description"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCounty" class="col-sm-3 col-form-label pt-0">Take Card Payment Now?</label>
                                                                    <div class="col-sm-8">
                                                                        <span class="oNOfswich">
                                                                            <input type="checkbox">
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--  -->
                                                        <div class="tab-pane fade" id="billingDetails" role="tabpanel" aria-labelledby="billingDetails-tab" tabindex="0">
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">First Name <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Arjun">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Last Name <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Kumar">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Email Address <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="info@gmail.com">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Telephone <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Address Line1 <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="USA">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Address Line2</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">City <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">County</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Country</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="United Stat Kingdom">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Postcode <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--  -->
                                                        <div class="tab-pane fade active show" id="paymentType" role="tabpanel" aria-labelledby="paymentType-tab" tabindex="0">
                                                            <div class="mb-2 row">
                                                                <div class="col-sm-12">
                                                                    <div class="text-end">
                                                                        <h5>Paying Now: $12000.00</h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Payment Type <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <select class="form-control editInput selectOptions" id="inputCustomer">
                                                                            <option>Cash</option>
                                                                            <option>paypal</option>
                                                                            <option>Card</option>
                                                                            <option>Bank</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Date
                                                                        <span class="radStar">*</span></label>
                                                                    <div class="col-sm-5">
                                                                        <input type="text" class="form-control editInput" id="inputCity" value="24/10/2024">
                                                                    </div>
                                                                    <div class="col-sm-1 ps-0">
                                                                        <span class="material-symbols-outlined">
                                                                            calendar_month
                                                                        </span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <!--  -->
                                                    </div>
                                                </div>
                                            </div> <!-- end modal body -->
                                            <div class="modal-footer customer_Form_Popup">
                                                <button type="button" class="btn profileDrop">Next</button>
                                                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ************************ -->
                                <!-- *********************** -->
                                <div class="modal fade" id="creaditDepositInvoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="creaditDepositInvoiceModalModalLabel" style="display: none;" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content add_Customer">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-5" id="creaditDepositInvoiceModalModalLabel">Creadit Deposit Invoice</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body ">
                                                <div class="contantbodypopup p-0">
                                                    <div class="newJobForm card">
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Invoice Date
                                                                <span class="radStar">*</span></label>
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="24/10/2024">
                                                            </div>
                                                            <div class="col-sm-1 ps-0">
                                                                <a href="#!">
                                                                    <span class="material-symbols-outlined">
                                                                        calendar_month
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Due Date
                                                                <span class="radStar">*</span></label>
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="24/10/2024">
                                                            </div>
                                                            <div class="col-sm-1 ps-0">
                                                                <a href="#!">
                                                                    <span class="material-symbols-outlined">
                                                                        calendar_month
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputName" class="col-sm-3 col-form-label">Line Item</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control editInput" id="inputName" placeholder="Line Item">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Line Description <span class="radStar">*</span></label>
                                                            <div class="col-sm-9">
                                                                <textarea class="form-control textareaInput rounded-1" name="address" id="description" rows="3" placeholder="Description"></textarea>
                                                            </div>
                                                        </div>


                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Deposit Persontage
                                                                 <span class="radStar">*</span></label>
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="100">
                                                            </div>
                                                            <div class="col-sm-2 ps-0">
                                                                <input class="form-control editInput text-center" value="% of $.00" disabled="">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Sub Totle <span class="radStar">*</span></label>
                                                            <div class="col-sm-1 pe-0">
                                                                <input class="form-control editInput text-center" value="$" disabled="">
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="0.00">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">VAT (%) <span class="radStar">*</span></label>
                                                            <div class="col-sm-9">
                                                                <select class="form-control editInput selectOptions" id="inputCustomer">
                                                                    <option>-Please Select-</option>
                                                                    <option>Customer-2</option>
                                                                    <option>Customer-3</option>
                                                                    <option>Customer-4</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2 row">
                                                            <label for="inputName" class="col-sm-3 col-form-label">Totle (inc. VAT)</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control-plaintext editInput" id="inputName" value="$0.00" readonly="">
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div> <!-- end modal body -->
                                            <div class="modal-footer customer_Form_Popup">
                                                <button type="button" class="btn profileDrop">Save</button>
                                                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- *********************** -->


                                <!-- <div class="col-sm-3 mb-3 mt-2">
                                    <div class=" p-0">
                                        <a href="#" class="profileDrop">Creadit Deposit</a>
                                        <span class="col-form-label">
                                            or
                                        </span>
                                        <a href="#" class="profileDrop">Creadit Deposit Invoice</a>
                                    </div>
                                </div> -->

                                <div class="col-sm-12">
                                    <h4 class="contTitle text-start mb-2 mt-2">Deposits</h4>
                                    <div class="productDetailTable">
                                        <table class="table" id="containerA">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Deposit Date </th>
                                                    <th>Mode of Payment </th>
                                                    <th>Reference</th>
                                                    <th>Description </th>
                                                    <th>Created On </th>
                                                    <th>Deposit Amount </th>
                                                    <th>Refunded</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7">
                                                        <label class="red_sorryText">
                                                            Sorry, no records to show
                                                        </label>
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <h4 class="contTitle text-start mb-2 mt-2 ">Deposit Invoices</h4>
                                    <div class="productDetailTable">
                                        <table class="table" id="containerA">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice Ref </th>
                                                    <th>Invoice </th>
                                                    <th>Due Date</th>
                                                    <th>Sub Total </th>
                                                    <th>VAT </th>
                                                    <th>Total </th>
                                                    <th>Outstanding Created On</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="8">

                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- **************************************End of deposit Details****************************************** -->
                    </div>
                    <!--  -->
                    <div id="hideAttachmentTask">
                        <!-- *************************Start Task*************************************************** -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Tasks</label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="tabheadingTitle pb-3 pt-2">
                                        <a href="#" class="profileDrop me-3"> New Task</a>
                                    </div>
                                    <div class="extraInformationTab">
                                        <nav>
                                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                                <button class="nav-link active" id="tab_quoteTask" data-bs-toggle="tab" data-bs-target="#nav-tab_quoteTask" type="button" role="tab"
                                                    aria-controls="nav-Notes" aria-selected="true">Task</button>
                                                <button class="nav-link" id="nav-RecurringTasks-tab" data-bs-toggle="tab" data-bs-target="#nav-RecurringTasks" type="button" role="tab"
                                                    aria-controls="nav-Tasks" aria-selected="false">Recurring Task</button>
                                            </div>
                                        </nav>
                                        <div class="tab-content" id="nav-tabContent">
                                            <div class="tab-pane fade show active" id="nav-tab_quoteTask" role="tabpanel" aria-labelledby="tab_quoteTask" tabindex="0">
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
                                                </div>
                                                <!-- ENd col-9 -->
                                            </div>
                                            <div class="tab-pane fade" id="nav-RecurringTasks" role="tabpanel" aria-labelledby="nav-RecurringTasks-tab" tabindex="0">

                                                <div class="col-sm-12">

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

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- **********************End of Task************************ -->

                        <!-- ***********************************Start Attechments******************************** -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Attachments</label>
                            <div class="row">

                                <div class="col-sm-12 mb-3 mt-2">
                                    <div class=" p-0">
                                        <a href="javascript:void(0)" class="profileDrop" id="new_Attachment_open_model">New Attachment</a>
                                        <a href="javascript:void(0)" class="profileDrop">Upload Multi Attachment</a>
                                        <a href="javascript:void(0)" class="profileDrop">Preview Attachment(s)</a>
                                        <a href="javascript:void(0)" class="profileDrop">Download Attachment</a>

                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <h4 class="contTitle text-start mb-2 mt-2">Deposits</h4>
                                    <div class="productDetailTable">
                                        <table class="table" id="containerA">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Type</th>
                                                    <th>Title </th>
                                                    <th>Description</th>
                                                    <th>Section </th>
                                                    <th>Customer Visible </th>
                                                    <th>Mobile User Visible </th>
                                                    <th>File Name</th>
                                                    <th>Mime Type / Size</th>
                                                    <th>Created On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7">
                                                        <label class="red_sorryText">
                                                            Sorry, no attachment(s) found
                                                        </label>
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- ***************************************End Of Attechment********************************************** -->
                    </div>


                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="pageTitleBtn">
                                <!-- <a href="#" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a> -->
                                <button type="submit" class="profileDrop">Save</button>
                                <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- End col-12 -->
        </div>
    </div>
</section>



<!-- Add Customer Modal Start -->
<div class="modal fade" id="QuotecustomerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="add_customer_form" class="add_customer_form">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Customer Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="name" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCustomer" class="col-sm-3 col-form-label">Customer Type </label>
                                    <div class="col-sm-7">
                                        <select class="form-control editInput selectOptions get_customer_result" name="customer_type_id" id="get_customer_type">
                                            <option selected disabled>None</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-1">
                                        <a href="javascript:void(0)" class="formicon" id="OpenCustomerTypeModel"><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="contact_name" id="customer_contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Job Title (Position)</label>
                                    <div class="col-sm-4">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="job_title" id="customer_job_title_id">
                                            <option selected disabled>Please Select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputEmail" class="col-sm-3 col-form-label">Email <span class="radStar ">*</span> </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="email" id="customer_email">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputTelephone" class="col-sm-3 col-form-label">Telephone <span class="radStar ">*</span> </label>
                                    <div class="col-sm-3">
                                        <select class="form-control editInput selectOptions" name="telephone_country_code" id="billingTelephoneCountryCode"></select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="telephone" id="customer_phone">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                    <div class="col-sm-3">
                                        <select class="form-control editInput selectOptions" name="mobile_country_code" id="billingMobileCountryCode"></select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="telephone" id="customer_phone">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Fax</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="fax" id="customer_fax">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCity" class="col-sm-3 col-form-label">Website</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="website" id="customer_website">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Payment Terms</label>
                                    <div class="col-sm-7">
                                        <select class="form-control editInput selectOptions" name="payment_terms" id="customer_payment_terms">
                                            <option value="21">Defoult (21) </option>
                                            <?php for ($i = 1; $i < 21; $i++) { ?>
                                                <option value="{{$i}}">{{$i}}</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <span class="afterInputText"> Days </span>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                        <!-- British Pound - GBP -->
                                        <select class="form-control editInput selectOptions" name="currency" id="customer_currency_id">

                                            <option selected disabled>Please Select</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCounty" class="col-sm-3 col-form-label">Credit Limit</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="credit_limit" id="customer_credit_limit">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputPincode"
                                        class="col-sm-3 col-form-label">Discount</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="discount" id="customer_discount">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Discount Type</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" name="discount_type" id="customer_percentage">
                                            <option selected disabled>Please Select</option>
                                            <option value="1">Percantage</option>
                                            <option value="2">Flat</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-3 col-form-label">VAT / Tax No.</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="vat_tax_no" id="customer_vat">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">Default Catalogue</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" name="catalogue_id" id="customer_catalogue">
                                            <option>None</option>
                                            <option>ABCD</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" name="status" id="customer_status">
                                            <option value='1'>Active</option>
                                            <option value='0'>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Region</label>
                                    <div class="col-sm-7">
                                        <select class="form-control editInput selectOptions get_region_result" name="region" id="customerRegion">
                                            <option>None</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openRegionModal('customerRegion')" id=""><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress"
                                        class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput" name="address" id="cuatomer_address" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCity"
                                        class="col-sm-3 col-form-label">City</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="city" id="customer_city">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCounty" class="col-sm-3 col-form-label">County</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="country" id="customer_country_input">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputPincode"
                                        class="col-sm-3 col-form-label">Pincode</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="postal_code" id="customer_pincode">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry"
                                        class="col-sm-3 col-form-label">Country</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" name="country_code" id="customer_country_id">
                                            @foreach($countries as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Site Notes</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput" rows="3" name="site_notes" id="customer_site_note"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Sage Ref.</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" name="saga_ref" id="customer_sage_ref">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Assign Products</label>
                                    <div class="col-sm-9">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="1" name="assigned_product" id="customer_yes" checked>
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="2" name="assigned_product" id="custoemr_no">
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput" name="notes" rows="3" id="customer_note"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="SaveCustomerData">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer Modal End -->

<!-- Add Customer Type Modal Start -->
<div class="modal fade" id="quote_cutomer_type_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">Add Customer Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add_customer_type_form">
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-3 col-form-label">Customer Type <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control editInput" id="customer_type_name" value="" placeholder="Customer Type">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-3 col-form-label">Status</label>
                        <div class="col-sm-9">
                            <select id="customer_type_status" name="status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="pageTitleBtn">
                        <a href="#" class="profileDrop p-2 crmNewBtn" id="saveAddCustomerType"> Save</a>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer Type Modal End -->

<!-- Add Site Address Modal Start -->
<div class="modal fade" id="add_site_address_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="">Add Site Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="add_customer_site_details_form" class="add_customer_form">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <input type="hidden" name="customer_id" id="customer_id_site_add">
                                    <label for="inputName" class="col-sm-4 col-form-label">Customer </label>
                                    <div class="col-sm-8">
                                        <label for="inputAddress" class="col-form-label"><span id="setSiteAddress"></span> </label>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Site Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="site_name" id="customer_contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="contact_name" id="customer_contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-4 col-form-label">Job Title (Position)</label>
                                    <div class="col-sm-6">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="title_id" id="siteJobTitle">
                                            <option>Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('siteJobTitle')" id=""><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Company Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="company_name" id="customer_contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputEmail" class="col-sm-4 col-form-label">Email <span class="radStar ">*</span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="email" id="customer_email">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputTelephone" class="col-sm-4 col-form-label">Telephone <span class="radStar ">*</span> </label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="telephone_country_code" id="siteAddressTelephoneCode">
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="telephone" id="customer_phone">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputMobile" class="col-sm-4 col-form-label">Mobile</label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="mobile_country_code" id="siteAddressMobileCode">
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="mobile" id="customer_mobile">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Fax</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="fax" id="customer_fax">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-4 col-form-label">Region</label>
                                    <div class="col-sm-6">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="region" id="getSiteAddressRegion">
                                            <option>Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openRegionModal('getSiteAddressRegion')" id="OpenCustomerRegionModel"><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control textareaInput" name="address" id="cuatomer_address" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCity" class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="city" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCounty" class="col-sm-4 col-form-label">County</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="country" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputPincode" class="col-sm-4 col-form-label">Pincode</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="post_code" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <select class="form-control editInput selectOptions" name="country_id" id="siteAddressCountry">
                                            <option selected disabled>Select Country</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-4 col-form-label">Default Catalogue</label>
                                    <div class="col-sm-8">
                                        <select class="form-control editInput selectOptions" name="catalogue" id="">
                                            <option>None</option>
                                            <option value="1">ABCD</option>
                                            <option value="2">Test</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputName" class="col-sm-2 col-form-label">Notes </label>
                        <div class="col-sm-10">
                            <textarea class="form-control textareaInput" name="notes" id="" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="saveCustomerSiteDetails">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Site Address Modal End -->

<!-- Add Customer Contact Modal Start -->
<div class="modal fade" id="add_customer_contact_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Customer Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="add_customer_contact_form" class="add_customer_form">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Customer </label>
                                    <div class="col-sm-8">
                                        <label for="inputAddress" class="col-form-label"><span id="setCustomerName"></span> </label>
                                        <input type="hidden" name="customer_id" id="customer_contact_id">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCustomer" class="col-sm-4 col-form-label">Default Billing </label>
                                    <div class="col-sm-8">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="1" name="default_billing" id="customer_yes" checked>
                                            <label class="form-check-label checkboxtext editInput" for="inlineRadio1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" value="0" name="default_billing" id="custoemr_no">
                                            <label class="form-check-label checkboxtext editInput" for="inlineRadio2">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="contact_name" id="customer_contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-4 col-form-label">Job Title (Position)</label>
                                    <div class="col-sm-6">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="job_title_id" id="customer_job_titile_id">
                                            <option>Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('customer_job_titile_id')" id="OpenCustomerJobTitleModel"><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputEmail" class="col-sm-4 col-form-label">Email <span class="radStar ">*</span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="email" id="customer_email">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputTelephone" class="col-sm-4 col-form-label">Telephone <span class="radStar ">*</span> </label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="telephone_country_code" id="setBillingAddressTelephoneCountryCode">
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="telephone" id="customer_phone">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputMobile" class="col-sm-4 col-form-label">Mobile</label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="mobile_country_code" id="setBillingAddressMobileCountryCode">
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="mobile" id="customer_mobile">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Fax</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="fax" id="customer_fax">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address Details <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <label for="inputAddress" class="col-form-label">Same as Default <input type="checkbox" value="1" id="same_as_default" name="same_as_default"></label>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control textareaInput" name="address" id="cuatomer_address" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCity" class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="city" id="customer_city">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCounty" class="col-sm-4 col-form-label">County</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="county" id="customer_country_input">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputPincode" class="col-sm-4 col-form-label">Pincode</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="pincode" id="customer_pincode">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <select class="form-control editInput selectOptions" name="country" id="getCountryList">
                                            <option selected disabled>Select Country</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="saveCustomerContactData">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer Contact Modal End -->

<!-- Add Customer Site Delivery Contact Modal Start -->
<div class="modal fade" id="add_site_delivery_address_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="">Add Site Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" id="add_customer_site_delivery_form" class="add_customer_form">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <input type="hidden" name="customer_id" id="customer_id_site_delivery">
                                    <label for="inputName" class="col-sm-4 col-form-label">Customer </label>
                                    <div class="col-sm-8">
                                        <label for="inputAddress" class="col-form-label"><span id="setSiteDeliveryAddress"></span> </label>
                                        <!-- <input type="hidden" name="customer_id" id="siteCustomerDeliveryId"> -->
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Site Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="site_name" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="contact_name" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-4 col-form-label">Job Title (Position)</label>
                                    <div class="col-sm-6">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="title_id" id="siteDeliveryJobTitle">
                                            <option>Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('siteDeliveryJobTitle')" id="OpenSiteDeliveryAddressJobTitleModel"><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-4 col-form-label">Company Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="company_name" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputEmail" class="col-sm-4 col-form-label">Email <span class="radStar ">*</span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="email" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputTelephone" class="col-sm-4 col-form-label">Telephone <span class="radStar ">*</span> </label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="telephone_country_code" id="siteDeliveryAddressTelephoneCode">
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="telephone" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputMobile" class="col-sm-4 col-form-label">Mobile</label>
                                    <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" name="mobile_country_code" id="siteDeliveryAddressMobileCode">
                                        </select>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control editInput" name="mobile" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Fax</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="fax" id="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-4 col-form-label">Region</label>
                                    <div class="col-sm-6">
                                        <select class="form-control editInput selectOptions get_job_title_result" name="region" id="getSiteDeliveryAddressRegion">
                                            <option>Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" id="OpenSiteDeliveryRegionModel"><i class="fa-solid fa-square-plus"></i></a>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address <span class="radStar ">*</span></label>
                                    <div class="col-sm-8">
                                        <textarea class="form-control textareaInput" name="address" id="" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCity" class="col-sm-4 col-form-label">City</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="city" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCounty" class="col-sm-4 col-form-label">County</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="country" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputPincode" class="col-sm-4 col-form-label">Pincode</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" name="post_code" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-4 col-form-label">Country</label>
                                    <div class="col-sm-8">
                                        <select class="form-control editInput selectOptions" name="country_id" id="siteDeliveryAddressCountry">
                                            <option selected disabled>Select Country</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputCountry" class="col-sm-4 col-form-label">Default Catalogue</label>
                                    <div class="col-sm-8">
                                        <select class="form-control editInput selectOptions" name="catalogue" id="">
                                            <option>None</option>
                                            <option value="1">ABCD</option>
                                            <option value="2">Test</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label for="inputName" class="col-sm-2 col-form-label">Notes </label>
                        <div class="col-sm-10">
                            <textarea class="form-control textareaInput" name="notes" id="" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="saveCustomerSiteDeliveryDetails">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer Site Delivery Contact Modal End -->

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="new_Attachment_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title pupTitle">ABCD</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="">

                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Quote Type <span class="radStar ">*</span></label>
                        <div class="col-md-9">
                            <input type="hidden" name="quote_type_id" id="quote_type_id">
                            <input type="text" name="title" class="form-control editInput " placeholder="Quote Type" id="title">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Number of days</label>
                        <div class="col-md-9">
                            <input type="text" name="number_of_days" class="form-control editInput " placeholder="Number of days" id="number_of_days" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Status</label>
                        <div class="col-md-9">
                            <select name="status" id="modale_status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">InActive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" id="">Save</button>
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Include the region modal  -->
@include('components.region-model')

@include('components.job-title-model')

@include('components.account-code')

@include('components.product-list')

<!-- Include the quote type modal component -->
<x-quote-type-modal
    modalId="quoteTypeModal"
    modalTitle="Quote Type - Add"
    formId="add_quote_type_form"
    inputId="JobTitle"
    statusId="status"
    saveButtonId="saveQuoteTypeQuote"
    placeholderText="Job Title" />

<x-quote-source
    modalId="quoteSourceModal"
    modalTitle="Quote Source - Add"
    formId="add_quote_source_form"
    inputId="JobTitle"
    statusId="status"
    saveButtonId="saveQuoteSourceQuote"
    placeholderText="Quote Source" />

<x-tag-modal
    modalId="quoteTagModal"
    modalTitle="Add Tag"
    formId="add_quote_tag_form"
    inputId="quoteTag"
    statusId="status"
    saveButtonId="saveQuoteTag"
    placeholderText="Tag" />

@include('frontEnd.salesAndFinance.item.common.addproductmodal')
@include('frontEnd.salesAndFinance.item.common.productcategoryaddmodal')
<script type="text/javascript" src="{{ url('public/js/salesFinance/customeQuoteForm.js') }}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

@include('frontEnd.salesAndFinance.jobs.layout.footer')
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

    CKEDITOR.replace('textarea8', editor_config);
    CKEDITOR.replace('textarea9', editor_config);
    CKEDITOR.replace('textarea10', editor_config);
    CKEDITOR.replace('textarea11', editor_config);
    //Text Editer

    // document.querySelector("#listAllProduct").addEventListener("click", function(e) {
    //     const productIds = document.getElementById('selectedProductIds').value;
    //     const productIdsArray = JSON.parse(productIds || '[]');
    //     productIdsArray.forEach(id => {
    //         getProductData(id); // Call the function for each ID
    //     });
    // });

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
        // getCustomerList();

        getQuoteType(document.getElementById('quoteType'));

        document.getElementById('hideQuoteDiv').style.display = "none";
        document.getElementById('hideQuoteDetails').style.display = "none";
        document.getElementById('hideAttachmentTask').style.display = "none";
        document.getElementById('hideDepositSection').style.display = "none";

        $('#getCustomerList').on('click', function() {
            // getCustomerList();
            document.getElementById('customerSiteDetails').removeAttribute('disabled');
            document.getElementById('billingDetailContact').removeAttribute('disabled');

            const billingDetailContact = document.getElementById('billingDetailContact');
            billingDetailContact.innerHTML = '';

            var getCustomerListValue = document.getElementById('getCustomerList');

            const option = document.createElement('option');
            option.value = getCustomerListValue.value;
            option.text = "Default";
            billingDetailContact.appendChild(option);


            $.ajax({
                url: '{{ route("customer.ajax.getCustomerBillingAddress") }}',
                method: 'POST',
                data: {
                    id: getCustomerListValue.value
                },
                success: function(response) {
                    console.log(response.data);

                    response.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.text = user.contact_name;
                        billingDetailContact.appendChild(option);
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });

            getBillingDetailsData(getCustomerListValue.value, '1');


            const customerSiteDetails = document.getElementById('customerSiteDetails');
            const customerSiteDelivery = document.getElementById('customerSiteDelivery');

            customerSiteDetails.innerHTML = '';
            customerSiteDelivery.innerHTML = '';

            const option3 = document.createElement('option');
            option3.value = getCustomerListValue.value;
            option3.text = "Same as customer";
            const option4 = option3.cloneNode(true);
            customerSiteDetails.appendChild(option3);
            customerSiteDelivery.appendChild(option4);

            removeAddCustomerSiteAddress(customerSiteDetails, customerSiteDelivery, getCustomerListValue.value);
        });

        $('#AddQuoteButton').on('click', function() {
            var customer = document.getElementById('getCustomerList').value;
            if (customer === "") {
                alert('Please select the customer');
            } else {
                getTags(document.getElementById('quoteTag'))
                getRegions(document.getElementById('siteDeliveryRegions'));
                const selectCustomer = document.getElementById('getCustomerList');
                const selectedText = selectCustomer.options[selectCustomer.selectedIndex].text;
                document.getElementById('setCustomerNameInCustomerdetails').value = selectedText;
                document.getElementById('yourQuoteSection').style.display = "none";
                document.getElementById('hideQuoteDiv').style.display = "block";
                document.getElementById('hideCustomerDetails').style.display = "none";
                document.getElementById('hideQuoteDetails').style.display = "block";
            }
        });

        $('#saveQuoteTag').on('click', function() {
            var quoteTag = document.getElementById('quoteTag');
            saveFormData(
                'add_quote_tag_form', // formId
                '{{ route("General.ajax.saveQuoteTag") }}', // saveUrl
                'quoteTagModal', // modalId
                getTags, // callback function after success
                quoteTag
            );
        });

        $('#billingDetailContact').on('change', function() {
            var selected = document.getElementById('getCustomerList').value;
            console.log(selected);
            if ($(this).val() === selected) {
                getBillingDetailsData($(this).val(), "2");
            } else {
                setCustomerBillingData($(this).val());
            }
        });

        $('#customerSiteDetails').on('change', function() {
            var selected = document.getElementById('getCustomerList').value;
            console.log($(this).val());
            if ($(this).val() === selected) {

                $.ajax({
                    url: '{{ route("customer.ajax.getCustomerDetails") }}',
                    method: 'POST',
                    data: {
                        id: $(this).val()
                    },
                    success: function(response) {
                        console.log(response.data);

                        // document.getElementById('customer_site_id').value = response.data[0].id;
                        document.getElementById('siteCustomerId').value = response.data[0].id;
                        document.getElementById('customerSiteName').value = response.data[0].contact_name;
                        document.getElementById('customerSiteAddress').value = response.data[0].address;
                        document.getElementById('customerSiteCity').value = response.data[0].city;
                        document.getElementById('customerSiteCounty').value = response.data[0].country;
                        document.getElementById('customerSitePostCode').value = response.data[0].postal_code;
                        document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                        document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                        document.getElementById('setSiteAddress').textContent = document.getElementById('customerSiteCompany').value = response.data[0].name;
                        selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                        selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                        selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                setSiteAddressDetails($(this).val());
            }
        });

        $('#customerSiteDelivery').on('change', function() {
            var selected = document.getElementById('getCustomerList').value;
            console.log($(this).val());
            if ($(this).val() === selected) {

                $.ajax({
                    url: '{{ route("customer.ajax.getCustomerDetails") }}',
                    method: 'POST',
                    data: {
                        id: $(this).val()
                    },
                    success: function(response) {
                        console.log(response.data);
                        document.getElementById('site_delivery_add_id').value = response.data[0].id;
                        document.getElementById('customerSiteDeliveryName').value = response.data[0].contact_name;
                        document.getElementById('customerSiteDeliveryAdd').value = response.data[0].address;
                        document.getElementById('customerSiteDeliveryPostCode').value = response.data[0].postal_code;
                        document.getElementById('customerSiteDeliveryTelephone').value = response.data[0].telephone;
                        document.getElementById('customerSiteDeliveryMobile').value = response.data[0].mobile;
                        document.getElementById('customerSiteDeliveryEmail').value = response.data[0].email;
                        document.getElementById('customerSiteDeliveryCompany').value = response.data[0].name;
                        selectPrevious(document.getElementById('customerSiteDeliveryCountry'), response.data[0].country_code);
                        selectPrevious(document.getElementById("customerSiteDeliveryTelephoneCode"), response.data[0].telephone_country_code);
                        selectPrevious(document.getElementById("customerSiteDeliveryMobileCode"), response.data[0].mobile_country_code);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } else {
                setSiteDeliveryDetails($(this).val());
            }
        });

        $('#saveQuoteSourceQuote').on('click', function() {
            var formData = $('#add_quote_source_form').serialize();
            $.ajax({
                url: '{{ route("quote.ajax.saveQuoteSources") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    console.log(response.id);
                    setSiteAddressDetails(response.id);
                    $('#quoteSourceModal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax Call for saving Customer Type
        $('#saveCustomerSiteDetails').on('click', function() {
            var formData = $('#add_customer_site_details_form').serialize();
            $.ajax({
                url: '{{ route("customer.ajax.saveCustomerSiteAddress") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    console.log(response.id);
                    setSiteAddressDetails(response.id);
                    // removeAddCustomerSiteAddress(document.getElementById('customerSiteDetails'),document.getElementById('customerSiteDelivery'), response.id);
                    $('#add_site_address_modal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $('#saveCustomerSiteDeliveryDetails').on('click', function() {
            var formData = $('#add_customer_site_delivery_form').serialize();
            $.ajax({
                url: '{{ route("customer.ajax.saveCustomerSiteAddress") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    console.log(response.id);
                    setSiteAddressDetails(response.id);
                    $('#add_site_delivery_address_modal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        $('#saveQuoteTypeQuote').on('click', function() {
            var formData = $('#add_quote_type_form').serialize();
            $.ajax({
                url: '{{ route("quote.ajax.saveQuoteType") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    console.log(response.id);
                    setSiteAddressDetails(response.id);
                    $('#quoteTypeModal').modal('hide');
                    getQuoteType(document.getElementById('quoteType'));
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Ajax Call for saving Customer Type
        $('#saveAddCustomerType').on('click', function() {
            var formData = $('#add_customer_type_form').serialize();
            $.ajax({
                url: '{{ route("quote.ajax.saveCustomerType") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#quote_cutomer_type_modal').modal('hide');
                    getCustomerType();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });

        // Save Customer Data
        $('#saveCustomerContactData').on('click', function() {
            var formData = $('#add_customer_contact_form').serialize();
            var formDataArray = $('#add_customer_contact_form').serializeArray();

            $.ajax({
                url: '{{ route("customer.ajax.SaveCustomerContactData") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    setCustomerBillingData(response.lastid);
                    // $('#add_customer_contact_modal')[0].reset();
                    $('#add_customer_contact_modal').find('form')[0].reset();
                    $('#add_customer_contact_modal').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });


        // Save Customer Data
        $('#SaveCustomerData').on('click', function() {
            var formData = $('#add_customer_form').serialize();
            $.ajax({
                url: '{{ route("customer.ajax.SaveCustomerData") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert(response.message);
                    getCustomerList();
                    $('#QuotecustomerPop').modal('hide');
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        });


    });

    function getCustomerType() {
        $.ajax({
            url: '{{ route("quote.ajax.getCustomerType") }}',
            success: function(response) {
                console.log(response.message);
                const get_customer_type = document.getElementById('get_customer_type');
                get_customer_type.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    get_customer_type.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getProductData(selectedId) {
        $.ajax({
            url: '{{ route("item.ajax.getProductFromId") }}',
            method: 'Post',
            data: {
                id: selectedId
            },
            success: function(response) {
                console.log("response.data", response.data);
                quoteProductTable(response.data, 'quoteProducts', 'add');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getAccountCode() {
        $.ajax({
            url: '{{ route("Invoice.ajax.getActiveAccountCode") }}',
            method: 'GET',
            success: function(response) {
                console.log("response.getActiveAccountCode", response.data);
                // Ensure response.data contains the account codes
                if (Array.isArray(response.data)) {
                    // Iterate over all Account Code dropdowns and populate them
                    document.querySelectorAll('#accoutCodeList').forEach(dropdown => {
                        dropdown.innerHTML = ''; // Clear existing options

                        const optionInitial = document.createElement('option');
                        optionInitial.textContent = "-No Department-"; // Use appropriate key from your response
                        optionInitial.value = "";
                        dropdown.appendChild(optionInitial);
                        // Append new options
                        response.data.forEach(code => {
                            const option = document.createElement('option');
                            option.value = code.id;
                            option.textContent = code.departmental_code + "-" + code.name; // Use appropriate key from your response
                            dropdown.appendChild(option);
                        });
                    });
                } else {
                    console.error("Invalid response format");
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    // function getAccountCode() {
    //     $.ajax({
    //         url: '{{ route("Invoice.ajax.getActiveAccountCode") }}',
    //         method: 'GET',
    //         success: function(response) {
    //             console.log("response.getActiveAccountCode", response.data);

    //             // Ensure response.data contains the account codes
    //             if (Array.isArray(response.data)) {
    //                 // Iterate over all dropdowns with class `accountCodeList` and populate them
    //                 document.querySelectorAll('#accountCodeList').forEach(dropdown => {
    //                     dropdown.innerHTML = ''; // Clear existing options

    //                     // Add initial option
    //                     const optionInitial = document.createElement('option');
    //                     optionInitial.textContent = "-No Department-";
    //                     optionInitial.value = "";
    //                     dropdown.appendChild(optionInitial);

    //                     // Append new options
    //                     response.data.forEach(code => {
    //                         const option = document.createElement('option');
    //                         option.value = code.id;
    //                         option.textContent = code.departmental_code + "-" + code.name;
    //                         dropdown.appendChild(option);
    //                     });
    //                 });
    //             } else {
    //                 console.error("Invalid response format");
    //             }
    //         },
    //         error: function(xhr, status, error) {
    //             console.error(error);
    //         }
    //     });
    // }


    function taxRate() {
        $.ajax({
            url: '{{ route("invoice.ajax.getActiveTaxRate") }}',
            method: 'GET',
            success: function(response) {
                console.log("response.data", response.data);
                if (Array.isArray(response.data)) {
                    // Iterate over all Account Code dropdowns and populate them
                    document.querySelectorAll('#getTaxRate').forEach(dropdown => {
                        dropdown.innerHTML = ''; // Clear existing options

                        const optionInitial = document.createElement('option');
                        optionInitial.textContent = "Please Select"; // Use appropriate key from your response
                        optionInitial.value = 0;
                        dropdown.appendChild(optionInitial);
                        // Append new options
                        response.data.forEach(code => {
                            const option = document.createElement('option');
                            option.value = code.id; // Use appropriate key from your response
                            option.textContent = code.name; // Use appropriate key from your response
                            option.setAttribute('data-rate', code.tax_rate);
                            if (code.id === 2) {
                                option.selected = true; // Select the option where id = 2
                            }
                            dropdown.appendChild(option);
                        });
                    });
                } else {
                    console.error("Invalid response format");
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function applyMarkup() {
        document.getElementById('markUpLinkRemove').innerHTML = '';
        document.getElementById('markUpLinkRemove').innerHTML = '<span>Markup</span> <input type="text" class="input50 form-control editInput footMarkup" name="mark" id="footMarkup"><span>%</span>';
    }

    // function getTaxRateOnTaxId(taxID) {
    //     $.ajax({
    //         url: '{{ route("invoice.ajax.getTaxRateOnTaxId") }}',
    //         method: 'Post',
    //         data: {
    //             id: 2
    //         },
    //         success: function(response) {
    //             console.log("response.data", response.data);
    //             document.querySelector('.selectedTaxID').value = response.data;
    //         },
    //         error: function(xhr, status, error) {
    //             console.error(error);
    //         }
    //     });
    // }

    $(document).on("change", ".vat", function() {
        let selectedRate = $(this).find("option:selected").data("rate");
        $(this).closest("tr").find(".selectedTaxID").val(selectedRate);
        console.log(selectedRate); // Output the selected VAT rate

        const table = document.querySelector(`#quoteProducts`);
        calculateRowsValue(table); // Call function with selected rate

    });

    $(document).on("change", ".discount_type_value", function() {
        const table = document.querySelector(`#quoteProducts`);
        calculateRowsValue(table); // Call function with selected rate
    });

    $(document).on("change", ".discount, .priceMarkup ", function() {
        const table = document.querySelector(`#quoteProducts`);
        calculateRowsValue(table); // Call function with selected rate
    });

    $(document).on("input", "#discountInput", function() {
        let discountValue = parseInt(this.value);
        document.querySelectorAll(".discount").forEach(element => {
            element.value = discountValue; // Set discount with %
        });

        document.querySelectorAll(".discount_type_value").forEach(select => {
            select.value = "%";
        });

        const table = document.querySelector(`#quoteProducts`);
        calculateRowsValue(table);
    });

    $(document).on("input", "#footMarkup", function() {
        console.log(this.value);
        let markValue = parseInt(this.value);

        document.querySelectorAll(".priceMarkup").forEach(element => {
            element.value = markValue; 
        });

        const table = document.querySelector(`#quoteProducts`);
        calculateRowsValue(table);
    });


    function tableFootForProduct(tableName) {
        const table = document.querySelector(`#${tableName}`);

        if (!isFooterAppended) {
            const tableFoot = table.querySelector('.add_table_insrt33');
            tableFoot.innerHTML += `<tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">Sub Total (exc. VAT) <input type="hidden" name="sub_total" id="InputFootAmount"></td>
                                        <td class="tableAmountRight" id="footAmount">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>   
                                        <td colspan="2">
                                            <div class="discountInput">
                                              <span>Discount</span><input type="text" class="form-control editInput input50 discountInputField" id="discountInput" value="0" data-table="${tableName}">
                                                <span>%</span>
                                            </div>
                                        </td>
                                        <td class="tableAmountRight" id="footDiscount">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">
                                            <div class="discountInput" id="markUpLinkRemove">
                                                <a href="javascript:void(0)" onclick="applyMarkup();"> Apply overall markup</a>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">VAT<input type="hidden" name="vat_amount" id="InputFootVatAmount"></td>
                                        <td class="tableAmountRight" id="footVatAmount">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2" style="border-bottom: 1px solid #000;"><strong>Total(inc.VAT)<input type="hidden" name="total" id="inputFootTotalDiscountVat"></strong></td>
                                        <td style="border-bottom: 1px solid #000;" class="tableAmountRight totleBold" id="footTotalDiscountVat">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">Profit<input type="hidden" name="profit" id="inputFootProfit"></td>
                                        <td class="tableAmountRight" id="footProfit">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">Margin</td>
                                        <td class="tableAmountRight" id="footMargin">00.00%</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2">Deposit</td>
                                        <td class="tableAmountRight" id="footDeposit">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="9" class="borderNone"></td>
                                        <td colspan="2" style="border-bottom: 1px solid #000;"><strong>Outstanding (inc.VAT)<input type="hidden" name="outstanding" id="inputFootOutstandingAmount"></strong></td>
                                        <td style="border-bottom: 1px solid #000;" class="tableAmountRight totleBold" id="footOutstandingAmount">£00.00</td>
                                        <td colspan="2" class="borderNone"></td>
                                    </tr>`;
            isFooterAppended = true;

            // Ensure the input is correctly selected
            const discountInput = table.querySelector('#discountInput');
            if (!discountInput) {
                console.error('Discount input not found.');
                return;
            }

            // Attach the event listener
            discountInput.addEventListener('input', function() {
                const discountValue = parseFloat(this.value) || 0;

                // Update all elements with the class "discount"
                document.querySelectorAll('.discount').forEach(discountElement => {
                    discountElement.value = discountValue.toFixed(2); // Format to 2 decimal places
                });

                // Call calculateRowsValue function
                if (typeof calculateRowsValue === 'function') {
                    calculateRowsValue(table);
                } else {
                    console.error('calculateRowsValue function is not defined.');
                }
            });

        }
    }

    function calculateDiscount(originalPrice, discountValue, discountType) {
        let finalPrice;

        if (discountType === "%") {
            // Percentage discount
            finalPrice = originalPrice * (discountValue / 100);
            console.log("finalPrice", finalPrice);
        } else if (discountType === "£") {
            // Fixed amount discount
            finalPrice = discountValue;
            console.log("finalPrice", finalPrice);
        }

        return finalPrice > 0 ? finalPrice : 0; // Ensure price doesn't go negative
    }


    function calculateRowsValue(table) {
        const rows = table.querySelectorAll('tbody tr');

        let totalQuantity = 0;
        let totalCostPrice = 0;
        let totalPrice = 0;
        let totalMarkup = 0;

        let totalVAT = 0;
        let vat = 20;

        let totalProfit = 0;
        let totalDiscount = 0;

        let profitElement;
        let profitValue;
        let numericProfit;
        let totalMargin = 0;

        let price = 0;

        const doller = `£`;

        rows.forEach(row => {

            // Get input values from the row
            totalQuantity = parseInt(row.querySelector('.quantity').value) || 0;
            totalPrice = parseFloat(row.querySelector('.price').value) || 0;
            discount = parseInt(row.querySelector('.discount').value) || 0;
            console.log("discount input", discount);
            discount_type_value = row.querySelector('.discount_type_value').value;
            console.log("discount_type_value", discount_type_value);
            discountAmount = calculateDiscount(totalPrice, discount, discount_type_value);
            totalCostPrice = parseFloat(row.querySelector('.costPrice').value) || 0;
            totalMarkup = parseInt(row.querySelector('.priceMarkup').value) || 0;
            vat = parseInt(row.querySelector('.selectedTaxID').value) || 0;

            priceWithDiscount = totalPrice - discountAmount;

            markupAmount = (priceWithDiscount * totalMarkup) / 100; // Percentage markup
            console.log("markupAmount", markupAmount);

            // Calculate selling price (Cost Price + Markup - Discount)
            sellingPrice = priceWithDiscount + markupAmount;
            console.log("sellingPrice", sellingPrice);
            totalDiscount += discountAmount;

            // Calculate Amount (Quantity × Selling Price)
            amount = totalQuantity * sellingPrice;
            console.log(amount);
            price += amount;

            // Calculate VAT amount
            vatAmount = (amount * vat) / 100;
            console.log(vatAmount);
            totalVAT += vatAmount;
            // Calculate Profit ((Selling Price - Cost Price) × Quantity)
            profit = (sellingPrice - totalCostPrice) * totalQuantity;
            console.log(sellingPrice);
            totalProfit += profit;

            // Calculate margin
            margin = parseFloat((profit / sellingPrice) * 100);
            totalMargin += margin;
            console.log(margin);

            row.querySelector('.amount').textContent = doller + amount.toFixed(2);

            // Update row output fields
            row.querySelector('.profit').textContent = doller + profit.toFixed(2);

            if (margin >= 0) {
                row.querySelector('.footRowMargin').classList.add('minusnmberGreen');
            } else {
                row.querySelector('.footRowMargin').classList.add('minusnmberRed');
            }
            row.querySelector('.footRowMargin').textContent = '(' + margin.toFixed(2) + '%' + ')';

        });
        console.log("Total Quantity: ", totalQuantity);
        console.log("Total Cost Price: ", totalCostPrice);
        console.log("Total Price: ", price);
        console.log("Total Markup: ", totalMarkup);
        console.log("Total VAT: ", totalVAT);
        console.log("Total Discount: ", totalDiscount);
        console.log("Total Profit: ", totalProfit);
        console.log("Total totalMargin: ", totalMargin);

        document.getElementById('footAmount').textContent = doller + price.toFixed(2);
        document.getElementById('InputFootAmount').value = price.toFixed(2);
        document.getElementById('footDiscount').textContent = doller + totalDiscount.toFixed(2);
        document.getElementById('footVatAmount').textContent = doller + totalVAT.toFixed(2);
        document.getElementById('InputFootVatAmount').value = totalVAT.toFixed(2);
        document.getElementById('footTotalDiscountVat').textContent = doller + (price + totalVAT).toFixed(2);
        document.getElementById('inputFootTotalDiscountVat').value = (price + totalVAT).toFixed(2);
        document.getElementById('footProfit').textContent = doller + totalProfit.toFixed(2);
        document.getElementById('inputFootProfit').value = totalProfit.toFixed(2);
        document.getElementById('footMargin').textContent = totalMargin.toFixed(2) + "%";
        document.getElementById('footOutstandingAmount').textContent = doller + (price + totalVAT).toFixed(2);
        document.getElementById('inputFootOutstandingAmount').value = (price + totalVAT).toFixed(2);

    }

    let isFooterAppended = false;
    let rowIndex = 0;

    function quoteProductTable(data, tableId, type) {
        const table = document.querySelector(`#${tableId}`);
        // Populate rows as usual if data is not empty
        data.forEach(item => {
            console.log("1", rowIndex);
            const tableBody = document.querySelector(`#${tableId} tbody`);
            const node = document.createElement("tr");
            taxRate();
            getAccountCode();
            node.classList.add("add_table_insrt");
            node.innerHTML = `<td>
                    <div class="CSPlus">
                        <span class="plusandText">
                            <a href="javascript:void(0)" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                              <input type="hidden" name="products[${rowIndex}][type]" value="${type}">
                            <input type="hidden" name="products[${rowIndex}][id]" value="${item.id}">
                            <input type="text" class="form-control editInput input80" name="products[${rowIndex}][product_code]" value="${item.product_code}">
                        </span>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput" name="products[${rowIndex}][product_name]" value="${item.product_name}">
                    </div>
                </td>
                <td>
                    <div class="">
                        <textarea class="form-control textareaInput" id="inputAddress" name="products[${rowIndex}][description]" rows="2" placeholder="Description"></textarea>
                    </div>
                </td>
                <td>
                    <div class="">
                        <select class="form-control editInput selectOptions accoutCodeList" name="products[${rowIndex}][account_code]" id="accoutCodeList">
                            <option>-No Department-</option> 
                        </select>
                    </div>
                </td>
                <td>
                    <div class=""><input type="text" class="form-control editInput input50 quantity" name="products[${rowIndex}][quantity]" value="1"></div>
                </td>
                <td>
                    <div class=""><input type="text" class="form-control editInput input50 costPrice" name="products[${rowIndex}][cost_price]" value="${parseFloat(item.cost_price || 0).toFixed(2)}"></div>
                </td>
                <td>
                    <div class="calculatorIcon">
                        <span class="plusandText">
                            <a href="javascript:void(0)" class="formicon pt-0" data-bs-toggle="modal" data-bs-target="#calculatePop"> <span class="material-symbols-outlined">calculate </span> </a>
                        </span>
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50 price" name="products[${rowIndex}][price]" value="${parseFloat(item.price || 0).toFixed(2)}">
                    </div>
                </td>
                <td>
                    <div class="">
                        <input type="text" class="form-control editInput input50 priceMarkup" name="products[${rowIndex}][markup]" value="${parseFloat(item.markup || 0 ).toFixed(2)}">
                    </div>  
                </td>
                <td>
                    <div class="">
                        <input type="hidden" class="selectedTaxID" value="20">
                        <select class="form-control editInput selectOptions vat" name="products[${rowIndex}][VAT]" id="getTaxRate">
                            <option>Please Select</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="d-flex">
                        <input type="text" class="form-control editInput input50 me-2 discount" name="products[${rowIndex}][discount]" value="${parseInt(item.discount || 0)}">
                        <select class="form-control editInput selectOptions input50 discount_type_value" name="products[${rowIndex}][discount_type]">
                            <option value="£">£</option>
                            <option value="%">%</option>
                        </select>
                    </div>
                </td>
                <td>
                    <span class="amount">£00.00</span>
                </td>   
                <td>
                    <span class="profit">£00.00</span>
                    <div class="pt-1 footRowMargin">(00.00%)</div>
                </td>
                <td>
                    <div class="statuswating">
                        <span class="oNOfswich">
                            <input type="checkbox">
                        </span>
                        <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </td>`;

            tableFootForProduct(tableId);
            isFooterAppended = true;
            rowIndex++;
            console.log('2', rowIndex);

            if (tableBody) {
                tableBody.appendChild(node);


                attachRowEventListeners(node, table)
                const closeButton = node.querySelector('.closeappend');
                closeButton.addEventListener('click', function() {
                    node.remove(); // Remove the row when close button is clicked 
                    clearFooter(table);
                    calculateRowsValue(table);
                });
            } else {
                console.error("Table body with ID 'add_table_insrt' not found.");
            }

        });
        calculateRowsValue(table);
    }

    function attachRowEventListeners(row, table) {
        // Attach change events for quantity, costPrice, price, etc.
        row.querySelector('.quantity')?.addEventListener('input', () => calculateRowsValue(table));
        row.querySelector('.costPrice')?.addEventListener('input', () => calculateRowsValue(table));
        row.querySelector('.price')?.addEventListener('input', () => calculateRowsValue(table));
        row.querySelector('.discount')?.addEventListener('input', () => calculateRowsValue(table));
        row.querySelector('.vat')?.addEventListener('change', () => calculateRowsValue(table));
    }

    function clearFooter(table) {
        const tableBody = table.querySelector('tbody');
        const tableFoot = table.querySelector('tfoot');
        if (tableBody && tableBody.children.length === 0 && tableFoot) {
            tableFoot.innerHTML = ''; // Clear the footer
            isFooterAppended = false; // Reset the flag
        }
    }

    function getQuoteType(quoteType) {
        $.ajax({
            url: '{{ route("quote.ajax.getQuoteTypes") }}',
            success: function(response) {
                console.log(response.data);
                quoteType.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    quoteType.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getCustomerList() {

        $.ajax({
            url: '{{ route("customer.ajax.getCustomerList") }}',
            success: function(response) {
                console.log(response.data);
                var get_customer_type = document.getElementById('getCustomerList');
                // get_customer_type.innerHTML = '';

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    get_customer_type.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getCountriesListCustomer(selectElement) {
        $.ajax({
            url: '{{ route("ajax.getCountriesList") }}',
            method: 'GET',
            success: function(response) {
                console.log(response.Data);
                selectElement.innerHTML = '';
                response.Data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    selectElement.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getTags(tags) {
        $.ajax({
            url: '{{ route("General.ajax.getTags") }}',
            method: 'GET',
            success: function(response) {
                console.log("jxcnjfjnfnk", response.data);
                tags.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    tags.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getCurrencyData(customer_currency) {
        $.ajax({
            url: '{{ route("currency.ajax.getCurrencyData") }}',
            method: 'GET',
            success: function(response) {
                console.log("jxcnjfjnfnk", response.data);
                customer_currency.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name + " - " + user.code;
                    customer_currency.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function saveFormData(formId, saveUrl, modalId, callback, callBackValue = null) {
        var formData = $('#' + formId).serialize();
        console.log(formData);

        $.ajax({
            url: saveUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                alert(response.message);
                $('#' + modalId).modal('hide');
                if (callback && typeof callback === 'function') {
                    callback(callBackValue);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }



    function selectPrevious(Select, previouslySelected) {
        // Loop through the options in the select field
        const options = Select.options;

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === previouslySelected) {
                options[i].selected = true; // Set the previously selected country
                break;
            }
        }
    }

    function setFieldValues(fields, value) {
        fields.forEach(fieldId => {
            document.getElementById(fieldId).value = value;
        });
    }

    function setTextContent(fields, value) {
        fields.forEach(fieldId => {
            document.getElementById(fieldId).textContent = value;
        });
    }

    function getBillingDetailsData(id, type) {

        console.log("billingID", id);
        console.log("type", type);
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerDetails") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log("getCustomerDetails", response.data);
                var contactData = response.data[0];
                // billing details data set
                // setFieldValues([], contactData.id);

                setFieldValues(['billing_add_id'], contactData.id);
                setFieldValues(['billingDetailsName'], contactData.contact_name);
                setTextContent(['setCustomerName'], contactData.name);
                setFieldValues(['billingDetailsAddress'], contactData.address);
                setFieldValues(['billingDetailsEmail'], contactData.email);
                setFieldValues(['billingCustomerCity'], contactData.city);
                setFieldValues(['billingCustomerCounty'], contactData.country);
                setFieldValues(['billingCustomerPostcode'], contactData.postal_code);
                setFieldValues(['billingCustomerTelephone'], contactData.telephone);
                setFieldValues(['customerSiteMobile'], contactData.mobile);
                // customer_contact_id

                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

                if (contactData.default_billing === 1 && id === contactData.id) {

                    setTextContent(['customer_id_site_delivery', 'siteCustomerId', 'site_delivery_add_id'], contactData.id);
                    setTextContent(['customerSiteDeliveryName', 'customerSiteName', 'site_delivery_add_id'], contactData.contact_name);
                    setTextContent(['setSiteAddress', 'customerSiteCompany', 'customerSiteDeliveryCompany', 'setSiteDeliveryAddress'], contactData.name);
                    setFieldValues(['customerSiteAddress', 'customerSiteDeliveryAdd'], contactData.address);
                    document.getElementById('customerSiteDeliveryEmail').value = contactData.email;
                    document.getElementById('customerSiteCity').value = contactData.city;
                    document.getElementById('customerSiteCounty').value = contactData.country;
                    setFieldValues(['customerSitePostCode', 'customerSiteDeliveryPostCode'], contactData.postal_code);
                    setFieldValues(['customerSiteTelephone', 'customerSiteDeliveryTelephone'], contactData.telephone);
                    setFieldValues(['customerSiteMobile', 'customerSiteDeliveryMobile'], contactData.mobile);

                    // Customer Site Address Data Set
                    selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                    selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                    selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);

                    // Customer Site Delivery Address Data Set
                    selectPrevious(document.getElementById('customerSiteDeliveryCountry'), response.data[0].country_code);
                    selectPrevious(document.getElementById("customerSiteDeliveryTelephoneCode"), response.data[0].telephone_country_code);
                    selectPrevious(document.getElementById("customerSiteDeliveryMobileCode"), response.data[0].mobile_country_code);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        }); 
    }

    function setSiteAddressDetails(id) {
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerSiteDetails") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);

                let selectElement = document.getElementById('customerSiteDetails'); 
                let customerSiteDelivery = document.getElementById('customerSiteDelivery'); 

                let newOption = document.createElement('option');
                newOption.value = response.data[0].id;
                newOption.text = response.data[0].site_name;
                const option1 = newOption.cloneNode(true);
                newOption.selected = true;
                selectElement.appendChild(newOption);
                customerSiteDelivery.appendChild(option1);


                // document.getElementById('customerSiteDetails');
                document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('customerSiteName').value = response.data[0].contact_name;
                document.getElementById('customerSiteAddress').value = response.data[0].address;
                document.getElementById('customerSiteCity').value = response.data[0].city;
                document.getElementById('customerSiteCounty').value = response.data[0].country;
                document.getElementById('customerSitePostCode').value = response.data[0].post_code;
                document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                document.getElementById('setSiteAddress').textContent = response.data[0].name;
                document.getElementById('customerSiteCompany').value = response.data[0].company_name;
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_id);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }



    function setSiteDeliveryDetails(id) {
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerSiteDetails") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);
                document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('customerSiteDeliveryName').value = response.data[0].contact_name;
                document.getElementById('customerSiteDeliveryAdd').value = response.data[0].address;
                document.getElementById('customerSiteDeliveryPostCode').value = response.data[0].post_code;
                document.getElementById('customerSiteDeliveryTelephone').value = response.data[0].telephone;
                document.getElementById('customerSiteDeliveryMobile').value = response.data[0].mobile;
                document.getElementById('customerSiteDeliveryEmail').value = response.data[0].email;
                document.getElementById('customerSiteDeliveryCompany').value = response.data[0].company_name;
                selectPrevious(document.getElementById('customerSiteDeliveryCountry'), response.data[0].country_id);
                selectPrevious(document.getElementById("customerSiteDeliveryTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteDeliveryMobileCode"), response.data[0].mobile_country_code);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function removeAddCustomerSiteAddress(customerSiteDetails, customerSiteDelivery, id) {
        console.log(id);
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerSiteAddress") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);
                // alert(response.data);    
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.site_name;
                    const option1 = option.cloneNode(true);
                    customerSiteDetails.appendChild(option);
                    customerSiteDelivery.appendChild(option1);
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function setCustomerBillingData(id) {
        // alert(id)
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerBillingAddressData") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log("getCustomerBillingAddressData", response.data);

                let selectElement = document.getElementById('billingDetailContact'); // Get the select element
                let newId = response.data[0].id; // Get the ID to be added

                // Check if the ID already exists in the select options
                let exists = Array.from(selectElement.options).some(option => option.value == newId);

                if (!exists) {
                    // Create and append a new option
                    let newOption = document.createElement('option');
                    newOption.value = newId;
                    newOption.text = response.data[0].contact_name;
                    selectElement.appendChild(newOption);
                    newOption.selected = true;
                }

                // Set the new option as selected
                setFieldValues(['billing_add_id', 'siteCustomerId'], response.data[0].id);

                // billing details data set
                document.getElementById('billingDetailsName').value = response.data[0].contact_name;
                document.getElementById('customer_contact_id').value = response.data[0].id;
                document.getElementById('billingDetailsAddress').value = response.data[0].address;
                document.getElementById('billingDetailsEmail').value = response.data[0].email;
                document.getElementById('billingCustomerCity').value = response.data[0].city;
                document.getElementById('billingCustomerCounty').value = response.data[0].county;
                document.getElementById('billingCustomerPostcode').value = response.data[0].pincode;
                document.getElementById('billingCustomerTelephone').value = response.data[0].telephone;
                document.getElementById('billingCustomerMobile').value = response.data[0].mobile;
                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);


                if ((response.default_billing) === 1) {
                    document.getElementById('site_delivery_add_id').value = response.data[0].id;
                    document.getElementById('customerSiteName').value = response.data[0].contact_name;
                    document.getElementById('siteCustomerId').value = response.data[0].id;
                    document.getElementById('customerSiteAddress').value = response.data[0].address;
                    document.getElementById('customerSiteCity').value = response.data[0].city;
                    document.getElementById('customerSiteCounty').value = response.data[0].county;
                    document.getElementById('customerSitePostCode').value = response.data[0].pincode;
                    document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                    document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                    // Customer Site Address Data Set
                    selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                    selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                    selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getUsersList() {
        alert();
        $.ajax({
            url: '{{ route("quote.ajax.getUsersData") }}',
            method: 'GET',
            success: function(response) {
                console.log(response.data);
                const usersList = document.getElementById('getUsersList');
                // alert(response.data);    
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    usersList.appendChild(option);
                });

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    window.onload = function() {
        var buttons = document.querySelectorAll('.hide-on-load');
        buttons.forEach(function(button) {
            button.style.display = 'none';
        });
    };

    // js for Add Customer modal open
    const OpenAddCustomerModal = document.getElementById('OpenAddCustomerModal');
    const QuotecustomerPop = document.getElementById('QuotecustomerPop');

    OpenAddCustomerModal.onclick = function() {
        getRegions(document.getElementById('customerRegion'));
        getCustomerType();
        getCurrencyData(document.getElementById('customer_currency_id'));
        getCountriesList(document.getElementById('billingTelephoneCountryCode'));
        getCountriesList(document.getElementById('billingMobileCountryCode'));
        getCustomerJobTitle(document.getElementById('customer_job_title_id'));
        $('#QuotecustomerPop').modal('show');
    }
    // js for Add Customer modal open

    // js for Add customer Type modal
    $('#OpenCustomerTypeModel').on('click', function() {
        $('#quote_cutomer_type_modal').modal('show');
    });

    $('#OpenQuoteTypeModel').on('click', function() {
        $('#quoteTypeModal').modal('show');
    });

    $('#OpenAddQuoteSourceModal').on('click', function() {
        $('#quoteSourceModal').modal('show');
    });



    // js for Add Customer Contact modal
    const OpenAddCustomerContact = document.getElementById('OpenAddCustomerContact');
    const add_customer_contact_modal = document.getElementById('add_customer_contact_modal');
    var customer_job_titile_id = document.getElementById('customer_job_titile_id');
    OpenAddCustomerContact.onclick = function() {
        var customer = document.getElementById('getCustomerList').value;
        if (customer === "") {
            alert('Please select the customer');
        } else {
            getCountriesListWithNameCode(document.getElementById('getCountryList'));
            getCustomerJobTitle(customer_job_titile_id);
            getCountriesList(setBillingAddressTelephoneCountryCode);
            getCountriesList(setBillingAddressMobileCountryCode);
            var getCustomerList = document.getElementById('getCustomerList').value
            document.getElementById('customer_contact_id').value = getCustomerList;
            document.getElementById('customer_id_site_add').value = getCustomerList;
            document.getElementById('customer_id_site_delivery').value = getCustomerList;

            $('#add_customer_contact_modal').modal('show');
        }
    }
    // js for Add Customer Contact modal

    // js for Add Site Address modal
    $('#openCustomerSiteAddress').on('click', function() {
        var customer = document.getElementById('getCustomerList').value;
        if (customer === "") {
            alert('Please select the customer');
        } else {
            getRegions(document.getElementById('getSiteAddressRegion'));
            getCountriesListWithNameCode(document.getElementById('siteAddressCountry'));
            getCustomerJobTitle(document.getElementById('siteJobTitle'));
            getCountriesList(document.getElementById('siteAddressMobileCode'));
            getCountriesList(document.getElementById('siteAddressTelephoneCode'));
            $('#add_site_address_modal').modal('show');
        }
    });

    $('#openSiteDeliveryModal').on('click', function() {
        getCustomerJobTitle(document.getElementById('siteDeliveryJobTitle'));
        getCountriesList(document.getElementById('siteDeliveryAddressTelephoneCode'));
        getCountriesList(document.getElementById('siteDeliveryAddressMobileCode'));
        getRegions(document.getElementById('getSiteDeliveryAddressRegion'));
        getCountriesListWithNameCode(document.getElementById('siteDeliveryAddressCountry'));
        $('#add_site_delivery_address_modal').modal('show');
    });

    $('#openABCProductModal').on('click', function() {
        $('#productModalBAC').modal('show');
    });

    $('#OpenAddQuoteTag').on('click', function() {
        $('#quoteTagModal').modal('show');
    });

    $('#new_Attachment_open_model').on('click', function() {
        $('#new_Attachment_model').modal('show');
    });

    function additemsCatagoryModal(th) {
        //alert();
        $('#category_name').val('');
        $('#parentcategory').val('');
        $('#product_category_status').val(1);
        $('#productCategoryID').val('');
        $('#productCategorytype').val(th);
        $('#itemsCatagoryModal').modal('show');
    }

    // **************************impExpClickbtnPopup

    const impExpClickbtnPopup = document.getElementById('impExpClickbtnPopup');
    const importExportpopup = document.getElementById('importExportpopup');
    const closeimportExportPopup = document.getElementById('closeimportExportPopup');

    impExpClickbtnPopup.addEventListener('click', () => {
        importExportpopup.style.display = 'block';
        setTimeout(() => {
            importExportpopup.style.opacity = '1';
        }, 50);
    });

    closeimportExportPopup.addEventListener('click', () => {
        importExportpopup.style.opacity = '0';
        setTimeout(() => {
            importExportpopup.style.display = 'none';
        }, 300);
    });
    // **************************End impExpClickbtnPopup
    // **************************Purchase Tax Rate

    const purchaseTaxRatePop = document.getElementById('purchaseTaxRatePop');
    const purchasepopup = document.getElementById('purchasepopup');
    const closePurchasePopup = document.getElementById('closePurchasePopup');

    purchaseTaxRatePop.addEventListener('click', () => {
        purchasepopup.style.display = 'block';
        setTimeout(() => {
            purchasepopup.style.opacity = '1';
        }, 50);
    });

    closePurchasePopup.addEventListener('click', () => {
        purchasepopup.style.opacity = '0';
        setTimeout(() => {
            purchasepopup.style.display = 'none';
        }, 300);
    });
    // **************************End Purchase Tax Rate
    // **************************Product Cetagory
    const productCetagoryPopup = document.getElementById('productCetagoryPopup');
    const cetagpopup = document.getElementById('cetagpopup');
    const closecatagPopup = document.getElementById('closecatagPopup');

    productCetagoryPopup.addEventListener('click', () => {
        cetagpopup.style.display = 'block';
        setTimeout(() => {
            cetagpopup.style.opacity = '1';
        }, 50);
    });

    closecatagPopup.addEventListener('click', () => {
        cetagpopup.style.opacity = '0';
        setTimeout(() => {
            cetagpopup.style.display = 'none';
        }, 300);
    });
    // **************************End Product Cetagory


    //**************insrtTitle
    function insrtTitle() {
        const node = document.createElement("tr");
        node.classList.add("add_table_insrt");
        node.innerHTML = `
            <form>
                <td>
                    <div class="CSPlus">
                        <span class="plusandText">
                            <a href="javascript:void(0)" onclick="insrtTitle()" class="formicon pt-0 me-2">
                                <i class="fa-solid fa-square-plus"></i>
                            </a>
                            <label>Title*:</label>
                            <input type="text" class="form-control editInput ms-3" name="item[][title][item_title]" placeholder="Type to add product">
                        </span>
                    </div>
                </td>
                <td colspan="12">
                    <input type="text" class="form-control editInput" name="item[][title][item_desc]" placeholder="Type to add product">
                </td>
                <td>
                    <div class="statuswating">
                        <span class="oNOfswich">
                            <input type="checkbox">
                        </span>
                        <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                </td> 
            </form>
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
                            <a href="javascript:void(0)" onclick="insrtImgappend()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                            <label></label>
                        </span>
                    </div>
                    <div class="addimg">
                        <img class="insrtImg" src="assets/imagrs/imgad1.png">
                        <input type="hidden" name="item[]['image'][]['itemImage']" value="image">

                    </div>
                </div>
            </td>
            <td>
                <div class="statuswating">
                    <span class="oNOfswich">
                        <input type="checkbox">
                    </span>
                    <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                            <a href="javascript:void(0)" onclick="insrtDescription()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>    
                        </span>
                    </div>
                    <input type="text" class="form-control editInput" name="item[][description][item_description]" id="" placeholder="Type to add product">
                </div>
            </td>
            <td>
                <div class="statuswating">
                    <span class="oNOfswich">
                        <input type="checkbox">
                    </span>
                    <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                            <a href="javascript:void(0)" onclick="insrtSection()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                            <label class="secTitle">Section Title* :</label>
                        </span>
                    </div>
                    <input type="text" class="form-control editInput" name="item[]['section_title_name']" id="inputCountry" placeholder="Type to add product">
                    
                    <div class="pageTitleBtn p-0">
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">
                                + Section Insert </a>
                            <div class="dropdown-menu fade-up m-0">
                                <a href="javascript:void(0)" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#productModalBAC">insert Section Product</a>
                                <a href="javascript:void(0)" class="dropdown-item col-form-label" onclick="insrtSectionTtle()">insert Section Title</a>
                                <a href="javascript:void(0)" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#attachmentsPopup">insert Section Image</a>
                                <a href="javascript:void(0)" class="dropdown-item col-form-label" onclick="insrtSectionDescription()">insert Section Description</a>
                            </div>
                        </div>
                    </div>

                    <div class="statuswating text-end ps-3">
                        <span class="oNOfswich">
                            <input type="checkbox">
                        </span>
                        <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                                                <a href="javascript:void(0)" id="openSectionAccountCode" class="formicon pt-0"> <i class="fa-solid fa-square-plus"></i> </a>
                                            </span>
                                        </div>
                                    </th>
                                    <th>Qty </th>
                                    <th>Cost Price(£) </th>
                                    <th>Cost Calc</th>
                                    <th>Price(£) </th>
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
                                                <a href="javascript:void(0)" class="formicon pt-0 me-2" onclick="insrtProduct()"> <i
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
                                                <a href="javascript:void(0)" class="formicon pt-0"
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
                                            <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                            <a href="javascript:void(0)" onclick="insrtSectionTtle()" class="formicon pt-0 me-2">
                                <i class="fa-solid fa-square-plus"></i>
                            </a>
                            <label>Title*:</label>
                            <input type="hidden" name="item[][itemDetails]" value="section_title">
                            <input type="text" class="form-control editInput ms-3" name="item[][section_title][section_item_title]" placeholder="Type to add product">
                        </span>
                    </div>
                </td>
                <td colspan="12">
                    <input type="text" class="form-control editInput" name="item[][section_title][section_item_description]" placeholder="Type to add product">
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
                                    <a href="javascript:void(0)" onclick="insrtSectionImg()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                                    <label></label>
                                </span>
                            </div>
                            <div class="addimg">
                                <input type="hidden" name="item[][section_image][section_item_image]" value="section_image">
                                <img class="insrtImg" src="assets/imagrs/imgad1.png">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="statuswating">
                            <span class="oNOfswich">
                                <input type="checkbox">
                            </span>
                            <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                                        <a href="javascript:void(0)" onclick="insrtSectionDescription()" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>    
                                    </span>
                                </div>
                                <input type="hidden" name="item[][itemDetails]" value="section_description">
                                <input type="text" class="form-control editInput" name="item[][section_description][section_item_description]" id="inputCountry" placeholder="Section Description">
                            </div>
                        </td>
                        <td>
                            <div class="statuswating">
                                <span class="oNOfswich">
                                    <input type="checkbox">
                                </span>
                                <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
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
                        <select class="form-control editInput selectOptions onclick="getUsersList();" >
                            <option>Select user</option>
                            <option>Default</option>
                        </select>
                        <a href="javascript:void(0)" class="callIcon"><i class="fa-solid fa-square-phone"></i></a>
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
                        <select class="form-control editInput selectOptions" id="">
                            <option>Select user</option>
                            <option>Install</option>
                            <option>Cold Call</option>
                            <option>Maintenance</option>
                        </select>
                    </div>
                    <div class="Priority">
                        <label>Priority :</label>
                        <select class="form-control editInput selectOptions" id="">
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="statuswating">

                        <div><label for="inputPurchase" class="col-sm-3 col-form-label">Awaiting</label></div>
                        <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                      <div class="tabheadingTitle">
                         <label for="inputPurchase" class="col-sm-3 col-form-label"><input type="checkbox">Dispatch Now</label>
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
</script>
<script>
    function upload() {
        var imgcanvas = document.getElementById("canv1");
        var fileinput = document.getElementById("finput");
        var image = new SimpleImage(fileinput);
        image.drawTo(imgcanvas);
    }



    $(document).on("input", ".quantity", function() {
        $(this).val($(this).val().replace(/\D/g, "")); // Remove non-numeric characters
    });

    $(document).on("input", ".costPrice, .price, .priceMarkup, .discount", function() {
        let value = $(this).val();

        // Allow only numbers and up to two decimal places
        let validValue = value.replace(/[^0-9.]/g, ""); // Remove non-numeric characters except '.'

        // Ensure only one decimal point is allowed
        let decimalCount = (validValue.match(/\./g) || []).length;
        if (decimalCount > 1) {
            validValue = validValue.slice(0, -1);
        }

        // Ensure correct decimal formatting (only up to two decimal places)
        if (validValue.includes(".")) {
            let parts = validValue.split(".");
            if (parts[1].length > 2) {
                validValue = parts[0] + "." + parts[1].slice(0, 2);
            }
        }

        // Update input value with the cleaned valid value
        $(this).val(validValue);
    });


    $(document).on("input", ".costPrice", function() {
        // Remove any alphabetic characters from the input field
        $(this).val($(this).val().replace(/[a-zA-Z]/g, ''));
    });
</script>