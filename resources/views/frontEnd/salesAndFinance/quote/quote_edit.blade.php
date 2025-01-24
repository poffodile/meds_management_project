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

    .contantbodypopup .nav-pills .nav-link.active,
    .contantbodypopup .nav-pills .show>.nav-link {
        color: #fff;
        background-color: #333;
    }

    .contantbodypopup .nav-pills .nav-link {
        border-radius: 3px;
    }
</style>

<section class="main_section_page px-3 pt-0">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>{{ $quoteData['quote_ref'] }}</h3>
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
                                            <input type="hidden" id="quote_id" name="quote_id" value="{{ $quoteData['id'] }}">
                                            <input type="text" class="form-control-plaintext editInput" id="" value="{{ $quoteData['quote_ref'] }}" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputCustomer" class="col-sm-3 col-form-label">Customer<span class="radStar">*</span></label>
                                        <div class="col-sm-7">
                                            <input type="hidden" value="{{ $quoteData['customer']['customer.id'] }}" id="setCustomerId">
                                            <select class="form-control editInput selectOptions" name="customer_id" id="getCustomerList">
                                                <option value="">Select Customer</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="plusandText">
                                                <a href="#!" id="OpenAddCustomerModal" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Status</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control-plaintext editInput" id="inputName" value="{{ $quoteData['status'] }}">
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
                                            <input type="hidden" value="{{ $quoteData['billing_add_id'] }}" id="edit_customer_billing_id">
                                            <input type="hidden" id="billing_add_id" name="billing_add_id">
                                            <select class="form-control editInput selectOptions" id="billingDetailContact">
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
                                            <input type="text" class="form-control editInput" value="{{ $quoteData['customer']['contact_name'] ?? '' }}" id="billingDetailsName" placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address <span class="radStar">*</span></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address" id="billingDetailsAddress" rows="3" placeholder="Address">{{ $quoteData['customer']['address'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="billingCustomerCity" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerCity" value="{{ $quoteData['customer']['city'] ?? '' }}" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerCounty" value="{{ $quoteData['customer']['country'] ?? '' }}" placeholder="County">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput" id="billingCustomerPostcode" value="{{ $quoteData['customer']['postal_code'] ?? '' }}" placeholder="Postcode">
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
                                            <select class="form-control editInput selectOptions" name="telephone_country_code" id="billingCustomerTelephoneCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['telephone_country_code']) && $quoteData['customer']['telephone_country_code'] == $value->id) ? 'selected' : '' }}> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="billingCustomerTelephone" value="{{ $quoteData['customer']['telephone'] ?? '' }}" placeholder="Telephone">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" name="mobile_country_code" id="billingCustomerMobileCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['mobile_country_code']) && $quoteData['customer']['mobile_country_code'] == $value->id) ? 'selected' : '' }}> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="billingCustomerMobile" value="{{ $quoteData['customer']['mobile'] ?? '' }}" placeholder="Mobile">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Email Address</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput" id="billingDetailsEmail" value="{{ $quoteData['customer']['email'] ?? '' }}" placeholder="Email Address">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <input type="hidden" value="{{ $quoteData['customer']['country_code'] ?? '' }}" id="edit_country_code">
                                            <select class="form-control editInput" name="" id="billingCustomerCountry">
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['country_code']) && $quoteData['customer']['country_code'] == $value->id) ? 'selected' : '' }}>{{ $value->name }}</option>
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
                                            <input type="hidden" id="edit_customer_site_id" name="{{ $quoteData['site_add_id'] }}">
                                            <select class="form-control editInput selectOptions" id="customerSiteDetails">
                                                <option>Same As Customer</option>
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
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteName" value="{{ $quoteData['customer']['contact_name'] ?? '' }}" placeholder="Name">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="customerSiteCompany" class="col-sm-3 col-form-label">Company </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteCompany" value="{{ $quoteData['customer']['name'] ?? '' }}" placeholder="Company">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address</label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput" name="address" id="customerSiteAddress" rows="3" placeholder="Address">{{ $quoteData['customer']['address'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="customerSiteCity" class="col-sm-3 col-form-label">City </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSiteCity" value="{{ $quoteData['customer']['name'] ?? '' }}" placeholder="City">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">County </label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" value="{{ $quoteData['customer']['country'] ?? '' }}" id="customerSiteCounty" placeholder="County">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Postcode </label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control editInput textareaInput" id="customerSitePostCode" value="{{ $quoteData['customer']['postal_code'] ?? '' }}" placeholder="Postcode">
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="plusandText">
                                                <a href="#!" class="formicon"><i class="fa-solid fa-magnifying-glass-location"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Telephone </label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="customerSiteTelephoneCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['telephone_country_code']) && $quoteData['customer']['telephone_country_code'] == $value->id) ? 'selected' : '' }}> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="customerSiteTelephone" value="{{ $quoteData['customer']['telephone'] ?? '' }}" placeholder="Telephone ">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="customerSiteMobileCode">
                                                <option value="">Please Select</option>
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['mobile_country_code']) && $quoteData['customer']['mobile_country_code'] == $value->id) ? 'selected' : '' }}> + {{ $value->code }} - {{ $value->name}} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput" id="customerSiteMobile" value="{{ $quoteData['customer']['mobile'] ?? '' }}" placeholder="Mobile">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputMobile" class="col-sm-3 col-form-label">Country </label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput" name="" id="customerSiteDetailsCountry">
                                                @foreach($countries as $value)
                                                <option value="{{ $value->id }}" {{ (isset($quoteData['customer']['country_code']) && $quoteData['customer']['country_code'] == $value->id) ? 'selected' : '' }}>{{ $value->name }}</option>
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
                                                    <a href="#!" id="" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
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
                                                    <a href="#!" id="openSiteDeliveryModal" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
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
                                                    <a href="#!" id="" onclick="openRegionModal('siteDeliveryRegions');" class="formicon"><i class="fa-solid fa-square-plus"></i></a>
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
                                                <option value="1">Draft</option>
                                                <option value="2">Processed</option>
                                                <option value="3">Call back</option>
                                                <option value="4">Accepted</option>
                                                <option value="5">Rejected</option>
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
                                                                    <tr>
                                                                        <td>1</td>
                                                                        <td>{{ $quoteData['quote_ref'] }}</td>
                                                                        <td>-</td>
                                                                        <td>{{ $quoteData['quota_date'] }}</td>
                                                                        <td>{{ $quoteData['expiry_date'] }}</td>
                                                                        <td>&#163;{{ $quoteData['sub_total'] ?? '0.00' }}</td>
                                                                        <td>&#163;{{ $quoteData['vat_amount'] ?? '0.00' }}</td>
                                                                        <td>&#163;{{ $quoteData['total_amount'] ?? '0.00' }}</td>
                                                                        <td>&#163;{{ $quoteData['deposit'] ?? '0.00' }}</td>
                                                                        <td>&#163;{{ $quoteData['outstanding'] ?? '0.00' }}</td>
                                                                        <td>{{ $quoteData['status'] }}</td>
                                                                        <td>
                                                                            <div class="d-inline-flex align-items-center ">
                                                                                <div class="nav-item dropdown">
                                                                                    <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">Action</a>
                                                                                    <div class="dropdown-menu fade-up m-0">
                                                                                        <a href="{{ url('/quote-details/edit').'/'.$quoteData['id'] }}" class="dropdown-item">Edit</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Preview</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Print</a>
                                                                                        <a href="" class="dropdown-item">Email</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Duplicate</a>
                                                                                        <a href="" class="dropdown-item">Duplicate for New Customer</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Create Purchase Order </a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Convert To New Job</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Convert To Recurring Job</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Convert To Invoice</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="" class="dropdown-item">Change To Processed</a>
                                                                                        <a href="" class="dropdown-item">Change To Call Back</a>
                                                                                        <a href="" class="dropdown-item">Change To Accepted</a>
                                                                                        <a href="" class="dropdown-item">Change To Rejected</a>
                                                                                        <a href="" class="dropdown-item">New Task</a>
                                                                                        <hr class="dropdown-divider">
                                                                                        <a href="#" class="dropdown-item set_value_on_CRM_model" class="dropdown-item">CRM History</a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
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
                                                <a href="#!" class="profileDrop ms-3"> Send To Planner</a>
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
                                                                <th></th>
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
                                                    <th>Product <i class="fa fa-info-circle"></i></th>
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
                                                    <th>Cost Price($) </th>
                                                    <th>Cost Calc</th>
                                                    <th>Price($) </th>
                                                    <th>Markup(%)</th>
                                                    <th>VAT(%) </th>
                                                    <th>Discount </th>
                                                    <th class="tableAmountRight">Amount </th>
                                                    <th>Profit </th>
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
                                                                        <th>Cost Price($)</th>
                                                                        <th>Price($)</th>
                                                                        <th>Amount($) </th>
                                                                        <th>Profit($)</th>
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
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Amount (inc. VAT)<span class="radStar">*</span></label>
                                                                    <div class="col-sm-1 pe-0">
                                                                        <input class="form-control editInput text-center" value="$" disabled="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <input type="text" class="form-control editInput" id="inputCity" value="$ 0.00">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Reference<span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Reference">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Description<span class="radStar">*</span></label>
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
                                                        <div class="tab-pane fade" id="billingDetails" role="tabpanel" aria-labelledby="billingDetails-tab" tabindex="0">
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">First Name<span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Arjun">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Last Name<span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="Kumar">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Email Address<span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity" placeholder="info@gmail.com">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Telephone<span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" id="inputCity">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Address Line1<span class="radStar">*</span></label>
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
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">City<span class="radStar">*</span></label>
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
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Postcode<span class="radStar">*</span></label>
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
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Payment Type<span class="radStar">*</span></label>
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
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Invoice Date<span class="radStar">*</span></label>
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="24/10/2024">
                                                            </div>
                                                            <div class="col-sm-1 ps-0">
                                                                <a href="#!"><span class="material-symbols-outlined">calendar_month</span></a>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Due Date<span class="radStar">*</span></label>
                                                            <div class="col-sm-5">
                                                                <input type="text" class="form-control editInput" id="inputCity" value="24/10/2024">
                                                            </div>
                                                            <div class="col-sm-1 ps-0">
                                                                <a href="#!"><span class="material-symbols-outlined">calendar_month</span></a>
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputName" class="col-sm-3 col-form-label">Line Item</label>
                                                            <div class="col-sm-9">
                                                                <input type="text" class="form-control editInput" id="inputName" placeholder="Line Item">
                                                            </div>
                                                        </div>
                                                        <div class="mb-2 row">
                                                            <label for="inputCity" class="col-sm-3 col-form-label">Line Description<span class="radStar">*</span></label>
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
                                                            <label for="inputCity" class="col-sm-3 col-form-label">VAT (%)<span class="radStar">*</span></label>
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
                                                            <label for="inputName" class="col-sm-3 col-form-label">Totel (inc. VAT)</label>
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
                                                        <label class="red_sorryText">Sorry, no records to show</label>
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
                                                    <td colspan="8"></td>
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

                        <!-- ************************************* Start Attechments ******************************** -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Attachments</label>
                            <div class="row">
                                <div class="col-sm-12 mb-3 mt-2">
                                    <div class=" p-0">
                                        <a href="javascript:void(0)" class="profileDrop" id="new_Attachment_open_model">New Attachment</a>
                                        <a href="{{ route('quote.addMultiAttachment', ['quote_id' => $quoteData['id']]) }}" class="profileDrop">Upload Multi Attachment</a>
                                        <a href="javascript:void(0)" id="downloadSelected" class="profileDrop">Download Attachment(s)</a>
                                        <a href="javascript:void(0)" id="deleteSelected" class="profileDrop">Delete Attachment(s)</a>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="productDetailTable">
                                        <table class="table" id="attachmentTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th><input type="checkbox" id="selectAll"></th>
                                                    <th>Type</th>
                                                    <th>Title </th>
                                                    <th>Description</th>
                                                    <th>Section </th>
                                                    <th>Customer Visible </th>
                                                    <th>Mobile User Visible </th>
                                                    <th>File Name</th>
                                                    <th>Mime Type / Size</th>
                                                    <th>Created On</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ************************************* End Of Attechment ******************************** -->

                        <!-- ******************************* Start Task ***************************** -->
                        <div class="newJobForm mt-4">
                            <label class="upperlineTitle">Tasks</label>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="tabheadingTitle pb-3 pt-2">
                                        <a href="#" class="profileDrop me-3" data-bs-toggle="modal" data-bs-target="#newTaskModal"> New Task</a>
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
                                                                <table class="table" id="quoteTaskList">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Date </th>
                                                                            <th>Ref </th>
                                                                            <th>User</th>
                                                                            <th>Type </th>
                                                                            <th>Title </th>
                                                                            <th>Notes</th>
                                                                            <th>Created On </th>
                                                                            <th>Executed</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Button trigger modal -->
                                                </div>
                                                <!-- ENd col-9 -->
                                            </div>
                                            <div class="tb-pane fade" id="nav-RecurringTasks" role="tabpanel" aria-labelledby="nav-RecurringTasks-tab" tabindex="0">
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
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ********************************** -->
                                        <div class="modal fade" id="newTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content add_Customer">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fs-5" id="newTaskModalLabel">New Task</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body ">
                                                        <form id="quoteTaskFormDatas">
                                                            <div class="contantbodypopup p-0">
                                                                <ul class="nav nav-pills taskTimer mb-3" id="pills-tab" role="tablist">
                                                                    <li class="nav-item me-2" role="presentation">
                                                                        <button class="nav-link profileDrop active" id="newTasks-tab" data-bs-toggle="pill" data-bs-target="#newTasks" type="button"
                                                                            role="tab" aria-controls="newTasks" aria-selected="true">Task</button>
                                                                    </li>
                                                                    <li class="nav-item me-2" role="presentation">
                                                                        <button class="nav-link profileDrop" id="newTaskTimer-tab" data-bs-toggle="pill" data-bs-target="#newTaskTimer" type="button" role="tab" aria-controls="newTaskTimer" aria-selected="false">Timer</button>
                                                                    </li>
                                                                </ul>
                                                                <div class="tab-content" id="pills-tabContent">
                                                                    <div class="tab-pane fade show active" id="newTasks" role="tabpanel" aria-labelledby="newTasks-tab" tabindex="0">
                                                                        <div class="newJobForm card">
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Customer</label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control-plaintext editInput" id="setCustomerNameInTask" value="" readonly="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task User<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="hidden" name="form_type" id="form_type" value="">
                                                                                            <input type="hidden" name="edit_quote_task_id" id="edit_quote_task_id" value="">
                                                                                            <select class="form-control editInput selectOptions" name="user_id" id="quoteTaskUser">
                                                                                                @foreach($users as $value)
                                                                                                <option value="{{ $value->id }}" @if($value->id == $loginCustomer) selected @endif>{{ $value->name }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Title<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" name="title" id="title">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task Type<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-7">
                                                                                            <select class="form-control editInput selectOptions" name="task_type" id="setTaskTypeData">
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-sm-1 ps-0">
                                                                                            <div class="plusandText">
                                                                                                <a href="#!" class="formicon" onclick="openTaskTypeModal('setTaskTypeData')"><i class="fa-solid fa-square-plus"></i></a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Start Date <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-4">
                                                                                            <input type="date" class="form-control editInput" name="start_date" id="start_date">
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <input type="time" class="form-control editInput" name="start_time" id="start_time">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">End Date <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-4">
                                                                                            <input type="date" class="form-control editInput" name="end_date" id="end_date">
                                                                                        </div>
                                                                                        <div class="col-sm-4">
                                                                                            <input type="time" class="form-control editInput" name="end_time" id="end_time">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCounty" class="col-sm-4 col-form-label pt-0">Is Recuring Task?</label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="checkbox" name="is_recurring" value="1" id="is_recurring">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Notify</label>
                                                                                        <div class="col-sm-3">
                                                                                            <input type="checkbox" name="notify" valie="1" id="yesOn">
                                                                                            <label class="col-form-label" for="yesOn">Yes, ON </label>
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                            <input type="date" class="form-control editInput" name="notify_date" id="notify_date">
                                                                                            <input type="time" class="form-control editInput" name="notify_time" id="notify_time">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <div class="col-md-3"></div>
                                                                                        <div class="col-sm-9">
                                                                                            <div id="optionsDiv">
                                                                                                <label class="editInput"><input type="checkbox" value="1" id="notification" name="notification"> Notification</label>
                                                                                                <label class="editInput"><input type="checkbox" value="1" id="email" name="email"> Email</label>
                                                                                                <label class="editInput"><input type="checkbox" value="1" id="sms" name="sms"> SMS</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Related To :</label>
                                                                                        <div class="col-sm-9">
                                                                                            <input type="text" class="form-control-plaintext editInput" value="{{ $quoteData['quote_ref']}}" readonly="">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCity" class="col-sm-3 col-form-label">Notes</label>
                                                                                        <div class="col-sm-9">
                                                                                            <textarea class="form-control textareaInput rounded-1" id="notes" name="notes" rows="5" placeholder="Notes"></textarea>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <!--  -->
                                                                    <div class="tab-pane fade" id="newTaskTimer" role="tabpanel" aria-labelledby="newTaskTimer-tab" tabindex="0">
                                                                        <div class="newJobForm card">
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Customer</label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control-plaintext editInput" id="setCustomerNameInTimer" value="" readonly="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Task User <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <select class="form-control editInput selectOptions" id="quoteTimerUser">
                                                                                                @foreach($users as $value)
                                                                                                <option value="{{ $value->id }}" @if($value->id == $loginCustomer) selected @endif>{{ $value->name }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Title<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <input type="text" class="form-control editInput" id="timerTitle">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Timer<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <button type="button" class="profileDrop" id="toggleTimerBtn"><i class="fa fa-play"></i> Start</button>
                                                                                            <span id="timerDisplay">00:00:00</span>
                                                                                            <input type="hidden" name="start_time_timer" id="start_time_timer">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCounty" class="col-sm-3 col-form-label pt-0">Related To</label>
                                                                                        <div class="col-sm-9">
                                                                                            <span class="editInput" id="relatedTo">{{ $quoteData['quote_ref'] }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Task Type<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control editInput selectOptions" id="setTaskTypeOnTimer">

                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-sm-1 ps-0">
                                                                                            <div class="plusandText">
                                                                                                <a href="#!" class="formicon" data-bs-toggle="modal" onclick="openTaskTypeModal('setTaskTypeOnTimer')" data-bs-target="#newQuotePop">
                                                                                                    <i class="fa-solid fa-square-plus"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCity" class="col-sm-3 col-form-label">Notes<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <textarea class="form-control textareaInput rounded-1" name="Notes" id="timerNotes" rows="5" placeholder="Notes"></textarea>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!--  -->
                                                                </div>
                                                            </div> <!-- end modal body -->
                                                            <div class="modal-footer customer_Form_Popup">
                                                                <button type="button" class="btn profileDrop" onclick="saveQuoteTaskFormData();">Save</button>
                                                                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <!-- ********************************************************** -->
                                            </div>

                                            <!-- ********************************** -->
                                            <div class="modal fade" id="newTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newTaskModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content add_Customer">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fs-5" id="newTaskModalLabel">New Task</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body ">
                                                            <div class="contantbodypopup p-0">
                                                                <ul class="nav nav-pills taskTimer mb-3" id="pills-tab" role="tablist">
                                                                    <li class="nav-item me-2" role="presentation">
                                                                        <button class="activetb active profileDrop" id="newTasks-tab" data-bs-toggle="pill" data-bs-target="#newTasks" type="button" role="tab" aria-controls="newTasks" aria-selected="true">Task</button>
                                                                    </li>
                                                                    <li class="nav-item me-2" role="presentation">
                                                                        <button class="profileDrop" id="newTaskTimer-tab" data-bs-toggle="pill" data-bs-target="#newTaskTimer" type="button" role="tab" aria-controls="newTaskTimer" aria-selected="false">Timer</button>
                                                                    </li>
                                                                </ul>
                                                                <div class="tab-content" id="pills-tabContent">
                                                                    <div class="tab-pane fade show active" id="newTasks" role="tabpanel" aria-labelledby="newTasks-tab" tabindex="0">
                                                                        <div class="newJobForm card">
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Customer</label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control-plaintext editInput" id="" readonly="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task User<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <select class="form-control editInput selectOptions" id="inputCustomer">
                                                                                                <option>-Not Assigned-</option>
                                                                                                <option>Customer-2</option>
                                                                                                <option>Customer-3</option>
                                                                                                <option>Customer-4</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Title<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" id="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task Type<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-7">
                                                                                            <select class="form-control editInput selectOptions" id="inputCustomer">
                                                                                                <option>-Not Assigned-</option>
                                                                                                <option>Customer-2</option>
                                                                                                <option>Customer-3</option>
                                                                                                <option>Customer-4</option>
                                                                                            </select>
                                                                                        </div>
                                                                                        <div class="col-sm-1 ps-0">
                                                                                            <div class="plusandText">
                                                                                                <a href="#!" class="formicon" data-bs-toggle="modal" data-bs-target="#newQuotePop">
                                                                                                    <i class="fa-solid fa-square-plus"></i>
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Start Date<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" id="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">End Date<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" id="">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCounty" class="col-sm-4 col-form-label pt-0">Is Recuring Task?</label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="checkbox">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-md-6">
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Notify</label>
                                                                                        <div class="col-sm-3">
                                                                                            <input type="checkbox" id="yesOn">
                                                                                            <label class="col-form-label" for="yesOn">Yes, ON </label>
                                                                                        </div>
                                                                                        <div class="col-sm-5">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Related To :</label>
                                                                                        <div class="col-sm-9">
                                                                                            <input type="text" class="form-control-plaintext editInput" value="Prathima" readonly="">
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-2 row">
                                                                                        <label for="inputCity" class="col-sm-3 col-form-label">Notes<span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <textarea class="form-control textareaInput rounded-1" name="Notes" rows="5" placeholder="Notes"></textarea>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <!--  -->
                                                                    <div class="tab-pane fade" id="newTaskTimer" role="tabpanel" aria-labelledby="newTaskTimer-tab" tabindex="0">
                                                                        <div class="newJobForm card">
                                                                            <div class="mb-2 row">
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">First Name<span class="radStar">*</span></label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control editInput" id="inputCity" placeholder="Arjun">
                                                                                </div>
                                                                            </div>
                                                                            <div class="mb-2 row">
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Last Name<span class="radStar">*</span></label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control editInput" id="inputCity" placeholder="Kumar">
                                                                                </div>
                                                                            </div>
                                                                            <div class="mb-2 row">
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Email Address<span class="radStar">*</span></label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control editInput" id="inputCity" placeholder="info@gmail.com">
                                                                                </div>
                                                                            </div>
                                                                            <div class="mb-2 row">
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Telephone<span class="radStar">*</span></label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control editInput" id="inputCity">
                                                                                </div>
                                                                            </div>
                                                                            <div class="mb-2 row">
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Address Line1<span class="radStar">*</span></label>
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
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">City<span class="radStar">*</span></label>
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
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Postcode<span class="radStar">*</span></label>
                                                                                <div class="col-sm-9">
                                                                                    <input type="text" class="form-control editInput" id="inputCity">
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
                                            <!-- ***************************************** -->

                                        </div><!-- End off col-sm-12 -->
                                    </div>
                                </div>
                                <!-- ****************************** End of Task ***************************** -->

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


<!-- Add Task Modal Start -->
<!-- <div class="modal fade" id="quote_task_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">New Task</h5>
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
</div> -->
<!-- Add Task Type Modal End -->

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
                                        <label for="inputAddress" class="col-form-label"><span id="setSiteAddress">{{ $quoteData['customer']['name']}}</span> </label>
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
                                        <label for="inputAddress" class="col-form-label"><span id="setCustomerName">{{ $quoteData['customer']['name'] }}</span> </label>
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
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address Details<span class="radStar ">*</span></label>
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
                <h5 class="modal-title pupTitle">Add Attachment</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="attachmentTypeForm">
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Quote Ref </label>
                        <div class="col-md-9">
                            <input type="hidden" value="{{ $quoteData['id'] }}" name="quote_id" id="quote_id">
                            <input type="text" class="form-control-plaintext editInput" id="" value="{{ $quoteData['quote_ref'] }}" readonly>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Type</label>
                        <div class="col-md-9">
                            <select name="attachment_type" id="modale_status" class="form-control editInput">
                                <option value="">Please Select</option>
                                @foreach($attachment_type as $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">File Name <span class="radStar ">*</span></label>
                        <div class="col-md-9">
                            <input type="file" name="image" class="form-control editInput " placeholder="" id="">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Title</label>
                        <div class="col-md-9">
                            <input type="text" name="title" class="form-control editInput " placeholder="Title" id="">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Description</label>
                        <div class="col-md-9">
                            <textarea name="description" class="form-control textareaInput" rows="4" placeholder="Description" id=""></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" id="saveAttachmentType">Save</button>
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
@include('components.task-type')

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
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>


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

    document.addEventListener('DOMContentLoaded', function() {
        const yesOnCheckbox = document.getElementById('yesOn');
        const optionsDiv = document.getElementById('optionsDiv');
        optionsDiv.style.display = 'none';
        // Add an event listener to the "Yes, ON" checkbox
        yesOnCheckbox.addEventListener('change', function() {
            if (this.checked) {
                optionsDiv.style.display = 'block'; // Show optionsDiv
            } else {
                optionsDiv.style.display = 'none'; // Hide optionsDiv
            }
        });
    });

    $(document).ready(function() {

        document.getElementById('hideQuoteDetails').style.display = "none";
        document.getElementById('hideQuoteDiv').style.display = "none";
        document.getElementById('hideDepositSection').style.display = "none";
        getQuoteTaskList(document.querySelector('#quoteTaskList tbody'));



        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // start here js for time start and pause
        let timerInterval;
        let elapsedSeconds = 0;
        let isRunning = false;

        function toggleTimer() {
            if (isRunning) {
                // Pause the timer
                clearInterval(timerInterval);
                document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-play"></i> Start';
            } else {
                // Start the timer
                timerInterval = setInterval(function() {
                    elapsedSeconds++;
                    document.getElementById('timerDisplay').textContent = formatTime(elapsedSeconds);
                    document.getElementById('start_time_timer').value = formatTime(elapsedSeconds);
                }, 1000);
                document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-stop"></i> Pause';
            }
            isRunning = !isRunning; // Toggle the running state
        }

        function formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
        }

        function pad(number) {
            return number < 10 ? '0' + number : number;
        }

        document.getElementById('toggleTimerBtn').addEventListener('click', toggleTimer);

        getQuoteAttachmentsOnPageLoad();
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
        const setCustomerId = document.getElementById('setCustomerId').value;

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
                    if (user.id == setCustomerId) {
                        option.selected = true; // Mark as selected
                        document.getElementById('setCustomerNameInTask').value = user.name;
                        document.getElementById('setCustomerNameInTimer').value = user.name;
                    }
                    get_customer_type.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        getTaskType(document.getElementById("setTaskTypeData"));
        getTaskType(document.getElementById("setTaskTypeOnTimer"));



        const edit_customer_billing_id = document.getElementById('edit_customer_billing_id').value;
        if (setCustomerId === edit_customer_billing_id) {
            $.ajax({
                url: '{{ route("customer.ajax.getCustomerBillingAddress") }}',
                method: 'POST',
                data: {
                    id: setCustomerId
                },
                success: function(response) {
                    console.log(response.message);
                    var billingDetailContact = document.getElementById('billingDetailContact');
                    billingDetailContact.innerHTML = '';

                    const optionDefault = document.createElement('option');
                    optionDefault.value = setCustomerId;
                    optionDefault.text = "Default";
                    billingDetailContact.appendChild(optionDefault);

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
        } else {
            setCustomerBillingData(edit_customer_billing_id);
        }


        $('#billingDetailContact').on('change', function() {
            var selected = document.getElementById('getCustomerList').value;
            console.log(selected);
            if ($(this).val() === selected) {
                getBillingDetailsData($(this).val());
            } else {
                setCustomerBillingData($(this).val());
            }
        });

        const edit_site_id = document.getElementById('edit_customer_billing_id').value;
        if (setCustomerId === edit_customer_billing_id) {
            $.ajax({
                url: '{{ route("customer.ajax.getCustomerSiteAddress") }}',
                method: 'POST',
                data: {
                    id: setCustomerId
                },
                success: function(response) {
                    console.log(response.data);

                    const optionDefault = document.createElement('option');
                    optionDefault.value = setCustomerId;
                    optionDefault.text = "Same As Default";
                    billingDetailContact.appendChild(optionDefault);

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


        $('#AddQuoteButton').on('click', function() {
            var customer = document.getElementById('getCustomerList').value;
            if (customer === "") {
                alert('Please select the customer');
            } else {
                // getTags(document.getElementById('quoteTag'))
                // getRegions(document.getElementById('siteDeliveryRegions'));
                const selectCustomer = document.getElementById('getCustomerList');
                const selectedText = selectCustomer.options[selectCustomer.selectedIndex].text;
                document.getElementById('setCustomerNameInCustomerdetails').value = selectedText;
                document.getElementById('yourQuoteSection').style.display = "none";
                document.getElementById('hideQuoteDiv').style.display = "block";
                document.getElementById('hideCustomerDetails').style.display = "none";
                document.getElementById('hideQuoteDetails').style.display = "block";
            }
        });

        $('#saveCustomerContactData').on('click', function() {
            var formData = $('#add_customer_contact_form').serialize();
            $.ajax({
                url: '{{ route("customer.ajax.SaveCustomerContactData") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);
                    alert(response.message);
                    setCustomerBillingData(response.lastid);
                    $('#add_customer_contact_modal').modal('hide');
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

        $('#OpenAddCustomerContact').on('click', function() {
            getCustomerJobTitle(document.getElementById('customer_job_titile_id'));
            $('#add_customer_contact_modal').modal('show');
        });

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

        $('#new_Attachment_open_model').on('click', function() {
            $('#new_Attachment_model').modal('show');
        });

        $('#saveAttachmentType').on('click', function(e) {

            let formData = new FormData($('#attachmentTypeForm')[0]);
            console.log(formData);

            $.ajax({
                url: '{{ route("quote.ajax.saveAttachmentData") }}', // Replace with your Laravel route URL
                type: 'POST',
                data: formData,
                contentType: false, // Required for FormData
                processData: false, // Required for FormData
                success: function(response) {
                    // Handle success
                    alert(response.data);
                    console.log(response.id);
                    $('#new_Attachment_model').modal('hide'); // Hide the modal
                    getQuoteAttachments(response.id);
                },
                error: function(xhr) {
                    // Handle error
                    const errors = xhr.responseJSON.errors || {
                        message: xhr.responseJSON.message
                    };
                    let errorMessage = 'Error saving the attachment:\n';
                    for (let key in errors) {
                        errorMessage += `${errors[key]}\n`;
                    }
                    alert(errorMessage);
                }
            });
        });

    });

    // function openTaskModal() {
    //     $('#quote_task_modal').modal('show');
    // }

    function getQuoteAttachmentsOnPageLoad() {
        const quote_id = document.getElementById('quote_id').value;
        console.log("quote_id", quote_id);
        $.ajax({
            url: '{{ route("quote.ajax.getAttachmentDataOnQuoteId") }}', // Replace with your Laravel route URL
            type: 'POST',
            data: {
                quote_id: quote_id
            },
            success: function(response) {
                // Handle success
                console.log(response);

                const tableBody = $('#attachmentTable tbody');
                console.log(tableBody);
                tableBody.empty(); // Clear existing rows
                if (response.data == "No data") {
                    console.log(response.data);
                } else {
                    // Assuming `response` contains an array of attachments
                    const attachments = Array.isArray(response.data) ? response.data : [response.data];

                    attachments.forEach(attachment => {
                        console.log(attachment);
                        // const attachmentTypeTitle = attachment.attachment_type ? attachment.attachment_type.title : '';
                        const customer_visible = attachment.customer_visible = 1 ? "grayCheck" : "grencheck";
                        const mobile_user_visible = attachment.mobile_user_visible = 1 ? "grayCheck" : "grencheck";

                        const id = attachment.id;
                        const row = `
                        <tr data-id="${id}">
                            <td><input type="checkbox" class="selectRow"></td>
                            <td>${attachment.attachmentType}</td>
                            <td>${attachment.title}</td>
                            <td>${attachment.description}</td>
                            <td>Quote</td>
                            <td> <span class="${customer_visible}"><i class="fa-solid fa-circle-check"></i></span> </td>
                            <td> <span class="${mobile_user_visible}"><i class="fa-solid fa-circle-check"></i></span></td>
                            <td>${attachment.original_name}</td>
                            <td>${attachment.mime_type} / ${attachment.size} KB</td>
                            <td>${new Date(attachment.created_at).toLocaleString()}</td>
                            <td><a href="${attachment.timestamp_name}" target="_blank"> <i class="fas fa-eye"></i></a> | <i class="fa fa-times"></i> | <a href="#!" onclick="downloadAttachmentFile('${attachment.timestamp_name}');"> <i class="fas fa-download"></i></a> | <a href="javascript:void(0)" onclick="deleteAttachmentFile('${attachment.id}');"> <i class="fas fa-trash-alt"></i></a> </td>
                        </tr>
                    `;
                        // console.log(row);
                        tableBody.append(row);
                    });
                }


            },
            error: function(xhr) {
                // Handle error
                const errors = xhr.responseJSON.errors || {
                    message: xhr.responseJSON.message
                };
                let errorMessage = 'Error on getting the attachment:\n';
                for (let key in errors) {
                    errorMessage += `${errors[key]}\n`;
                }
                alert(errorMessage);
            }
        });

    }

    function downloadAttachmentFile(fileName) {
        const fileUrl = fileName; // Construct the file URL dynamically
        const anchor = document.createElement('a');
        anchor.href = fileUrl;
        anchor.download = fileName; // Optional: Rename the file for the user
        anchor.click();
    }

    function getQuoteAttachments(attachment_id) {
        $.ajax({
            url: '{{ route("quote.ajax.getAttachmentData") }}', // Replace with your Laravel route URL
            type: 'POST',
            data: {
                attachment_id: attachment_id
            },
            success: function(response) {
                // Handle success
                const tableBody = $('#attachmentTable tbody');
                console.log(tableBody);

                if (response.data == "No data") {

                } else {
                    const attachments = Array.isArray(response.data) ? response.data : [response.data];

                    attachments.forEach(attachment => {
                        console.log(attachment);
                        const customer_visible = attachment.customer_visible = 1 ? "grayCheck" : "grencheck";
                        const mobile_user_visible = attachment.mobile_user_visible = 1 ? "grayCheck" : "grencheck";

                        const id = attachment.id;
                        const row = `
                                <tr>
                                    <td><input type="checkbox"></td>
                                    <td>${attachment.attachmentType}</td>
                                    <td>${attachment.title}</td>
                                    <td>${attachment.description}</td>
                                    <td>Quote</td>
                                    <td> <span class="${customer_visible}"><i class="fa-solid fa-circle-check"></i></span> </td>
                                    <td> <span class="${mobile_user_visible}"><i class="fa-solid fa-circle-check"></i></span></td>
                                    <td>${attachment.original_name}</td>
                                    <td>${attachment.mime_type} / ${attachment.size} KB</td>
                                    <td>${new Date(attachment.created_at).toLocaleString()}</td>
                                    <td><a href="${attachment.timestamp_name}" target="_blank"> <i class="fas fa-eye"></i></a> | <i class="fa fa-times"></i> | <a href="#!" onclick="deleteAttachmentFile('${attachment.id}');"> <i class="fas fa-trash-alt"></i></a></td>
                                </tr>
                            `;
                        console.log(row);
                        tableBody.append(row);
                    });
                }
                // Assuming `response` contains an array of attachments
            },
            error: function(xhr) {
                // Handle error
                const errors = xhr.responseJSON.errors || {
                    message: xhr.responseJSON.message
                };
                let errorMessage = 'Error on getting the attachment:\n';
                for (let key in errors) {
                    errorMessage += `${errors[key]}\n`;
                }
                alert(errorMessage);
            }
        });
    }

    function saveQuoteTaskFormData() {

        const activeTab = document.querySelector('.taskTimer .nav-link.active');
        console.log(activeTab);
        let data;
        // Check the active tab by its ID
        if (activeTab.id === 'newTasks-tab') {
            // Task tab is active, set task fields
            console.log('Task fields set');
            data = {
                type: 'task',
                quote_id: document.getElementById('quote_id').value,
                edit_quote_task_id: document.getElementById('edit_quote_task_id').value,
                user_id: document.getElementById('quoteTaskUser').value,
                title: document.getElementById('title').value,
                task_type_id: document.getElementById('setTaskTypeData').value,
                start_date: document.getElementById('start_date').value,
                start_time: document.getElementById('start_time').value,
                end_date: document.getElementById('end_date').value,
                end_time: document.getElementById('end_time').value,
                is_recurring: document.getElementById('is_recurring').checked ? 1 : 0,
                yesOn: document.getElementById('yesOn').checked ? 1 : 0,
                notify_date: document.getElementById('notify_date').value,
                notify_time: document.getElementById('notify_time').value,
                notification: document.getElementById('notification').checked ? 1 : 0,
                email: document.getElementById('email').checked ? 1 : 0,
                sms: document.getElementById('sms').checked ? 1 : 0,
                notes: document.getElementById('notes').value,
            };
            console.log("data", data);
        } else if (activeTab.id === 'newTaskTimer-tab') {
            // Timer tab is active, set timer fields
            console.log('Timer fields set');
            data = {
                type: 'timer',
                quote_id: document.getElementById('quote_id').value,
                edit_quote_task_id: document.getElementById('edit_quote_task_id').value,
                user_id: document.getElementById('quoteTimerUser').value,
                title: document.getElementById('timerTitle').value,
                start_date: moment().format('YYYY-MM-DD'),
                start_time: document.getElementById('start_time_timer').value,
                task_type_id: document.getElementById('setTaskTypeOnTimer').value,
                notes: document.getElementById('timerNotes').value,
            };
        }



        $.ajax({
            url: '{{ route("quote.ajax.saveQuoteTask") }}',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function(response) {
                alert(response.data);
                console.log(response.id);
                $('#newTaskModal').modal('hide');
                getQuoteTaskList(document.querySelector('#quoteTaskList tbody'));
            },
            error: function(xhr) {
                // Handle error
                const errors = xhr.responseJSON.errors || {
                    message: xhr.responseJSON.message
                };
                let errorMessage = 'Error saving the task:\n';
                for (let key in errors) {
                    errorMessage += `${errors[key]}\n`;
                }
                alert(errorMessage);
            }
        });
    }


    // Function to populate the table
    function populateTable(data, tableBody) {
        data.forEach(item => {
            // Create a new row
            const row = document.createElement('tr');

            const created_at = moment(item.created_at).format('DD/MM/YYYY HH:mm');
            const date = moment(item.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
            const time = moment(item.start_time, 'HH:mm:ss').format('HH:mm');

            // Create cells and append them to the row
            const dateCell = document.createElement('td');
            dateCell.textContent = date + " " + time;
            row.appendChild(dateCell);

            const related = document.createElement('td');
            related.innerHTML = item.quote_ref;
            row.appendChild(related);

            const nameCell = document.createElement('td');
            nameCell.innerHTML = item.userName;
            row.appendChild(nameCell);

            const quote_task_title = document.createElement('td');
            quote_task_title.textContent = item.task_type_id;
            row.appendChild(quote_task_title);

            const typeCell = document.createElement('td');
            typeCell.textContent = item.title;
            row.appendChild(typeCell);

            const notesCell = document.createElement('td');
            notesCell.innerHTML = item.notes;
            row.appendChild(notesCell);

            const create_time = document.createElement('td');
            create_time.innerHTML = created_at;
            row.appendChild(create_time);

            const idCell = document.createElement('td');
            idCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="edit"><i class="fa fa-edit"></i></a> <i class="fa fa-times"></i>`;
            row.appendChild(idCell);

            // Append the row to the table body
            tableBody.appendChild(row);
        });
    }

    function getQuoteTaskList(tableBody) {
        const quote_id = document.getElementById('quote_id').value;
        $.ajax({
            url: '{{ route("quote.ajax.getQuoteTaskList") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data: {
                quote_id: quote_id
            },
            success: function(response) {
                console.log(response.data);
                tableBody.innerHTML = '';

                // Call the function to populate the table with the data array
                populateTable(response.data, tableBody);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function deleteAttachmentFile(id) {
        alert(id);

        // Confirm deletion
        if (confirm("Are you sure you want to delete this row?")) {
            // Make AJAX call to delete the row on the server
            $.ajax({
                url: '{{ route("quote.ajax.deleteAttachment") }}', // Replace with your server URL
                method: 'POST', // Replace with appropriate HTTP method
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table
                        row.remove();
                    } else {
                        alert("Failed to delete the row.");
                    }
                },
                error: function() {
                    alert("An error occurred while trying to delete the row.");
                }
            });
        }
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

                let selectElement = document.getElementById('customerSiteDetails'); // or document.querySelector('[name="mySelectName"]');
                let customerSiteDelivery = document.getElementById('customerSiteDelivery'); // or document.querySelector('[name="mySelectName"]');

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

    function setCustomerBillingData(id) {
        // alert(id)
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerBillingAddressData") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);

                let selectElement = document.getElementById('billingDetailContact'); // Get the select element

                // Create and append a new option
                let newOption = document.createElement('option');
                newOption.value = response.data[0].id;
                newOption.text = response.data[0].contact_name;
                selectElement.appendChild(newOption);

                // Set the new option as selected
                newOption.selected = true;
                setFieldValues(['billing_add_id', 'siteCustomerId', 'site_delivery_add_id'], response.data[0].id);

                // billing details data set
                document.getElementById('billingDetailsName').value = document.getElementById('customerSiteName').value = response.data[0].contact_name;
                document.getElementById('customer_contact_id').value = document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('billingDetailsAddress').value = document.getElementById('customerSiteAddress').value = response.data[0].address;
                document.getElementById('billingDetailsEmail').value = response.data[0].email;
                document.getElementById('billingCustomerCity').value = document.getElementById('customerSiteCity').value = response.data[0].city;
                document.getElementById('billingCustomerCounty').value = document.getElementById('customerSiteCounty').value = response.data[0].county;
                document.getElementById('billingCustomerPostcode').value = document.getElementById('customerSitePostCode').value = response.data[0].pincode;
                document.getElementById('billingCustomerTelephone').value = document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                document.getElementById('billingCustomerMobile').value = document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

                // Customer Site Address Data Set
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function getBillingDetailsData(id) {
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

                setFieldValues(['billing_add_id', 'site_delivery_add_id', 'siteCustomerId', 'customer_id_site_delivery'], contactData.id);
                setFieldValues(['billingDetailsName', 'customerSiteName', 'customerSiteDeliveryName'], contactData.contact_name);
                setTextContent(['setCustomerName', 'setSiteAddress', 'customerSiteCompany', 'customerSiteDeliveryCompany', 'setSiteDeliveryAddress'], contactData.name);
                setFieldValues(['billingDetailsAddress', 'customerSiteAddress', 'customerSiteDeliveryAdd'], contactData.address);
                setFieldValues(['billingDetailsEmail', 'customerSiteDeliveryEmail'], contactData.email);
                setFieldValues(['billingCustomerCity', 'customerSiteCity'], contactData.city);
                setFieldValues(['billingCustomerCounty', 'customerSiteCounty'], contactData.country);
                setFieldValues(['billingCustomerPostcode', 'customerSitePostCode', 'customerSiteDeliveryPostCode'], contactData.postal_code);
                setFieldValues(['billingCustomerTelephone', 'customerSiteTelephone', 'customerSiteDeliveryTelephone'], contactData.telephone);
                setFieldValues(['billingCustomerMobile', 'customerSiteMobile', 'customerSiteDeliveryMobile'], contactData.mobile);
                // customer_contact_id

                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

                // Customer Site Address Data Set
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);

                // Customer Site Delivery Address Data Set
                selectPrevious(document.getElementById('customerSiteDeliveryCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteDeliveryTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteDeliveryMobileCode"), response.data[0].mobile_country_code);
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
                console.log(response.data);

                let selectElement = document.getElementById('billingDetailContact'); // Get the select element

                // Create and append a new option
                let newOption = document.createElement('option');
                newOption.value = response.data[0].id;
                newOption.text = response.data[0].contact_name;
                selectElement.appendChild(newOption);

                // Set the new option as selected
                newOption.selected = true;
                setFieldValues(['billing_add_id', 'siteCustomerId', 'site_delivery_add_id'], response.data[0].id);

                // billing details data set
                document.getElementById('billingDetailsName').value = document.getElementById('customerSiteName').value = response.data[0].contact_name;
                document.getElementById('customer_contact_id').value = document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('billingDetailsAddress').value = document.getElementById('customerSiteAddress').value = response.data[0].address;
                document.getElementById('billingDetailsEmail').value = response.data[0].email;
                document.getElementById('billingCustomerCity').value = document.getElementById('customerSiteCity').value = response.data[0].city;
                document.getElementById('billingCustomerCounty').value = document.getElementById('customerSiteCounty').value = response.data[0].county;
                document.getElementById('billingCustomerPostcode').value = document.getElementById('customerSitePostCode').value = response.data[0].pincode;
                document.getElementById('billingCustomerTelephone').value = document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                document.getElementById('billingCustomerMobile').value = document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

                // Customer Site Address Data Set
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }


    function setCustomerBillingData(id) {
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerBillingAddressData") }}',
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);

                let selectElement = document.getElementById('billingDetailContact'); // Get the select element

                // Create and append a new option
                let newOption = document.createElement('option');
                newOption.value = response.data[0].id;
                newOption.text = response.data[0].contact_name;
                selectElement.appendChild(newOption);

                // Set the new option as selected
                newOption.selected = true;
                setFieldValues(['billing_add_id', 'siteCustomerId', 'site_delivery_add_id'], response.data[0].id);

                // billing details data set
                document.getElementById('billingDetailsName').value = document.getElementById('customerSiteName').value = response.data[0].contact_name;
                document.getElementById('customer_contact_id').value = document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('billingDetailsAddress').value = document.getElementById('customerSiteAddress').value = response.data[0].address;
                document.getElementById('billingDetailsEmail').value = response.data[0].email;
                document.getElementById('billingCustomerCity').value = document.getElementById('customerSiteCity').value = response.data[0].city;
                document.getElementById('billingCustomerCounty').value = document.getElementById('customerSiteCounty').value = response.data[0].county;
                document.getElementById('billingCustomerPostcode').value = document.getElementById('customerSitePostCode').value = response.data[0].pincode;
                document.getElementById('billingCustomerTelephone').value = document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                document.getElementById('billingCustomerMobile').value = document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
                selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

                // Customer Site Address Data Set
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
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

    function upload() {
        var imgcanvas = document.getElementById("canv1");
        var fileinput = document.getElementById("finput");
        var image = new SimpleImage(fileinput);
        image.drawTo(imgcanvas);
    }

    $(document).ready(function() {

        // Enable/disable "Delete Selected" button based on checkbox selection
        $(document).on("change", ".selectRow, #selectAll", function() {
            const anySelected = $(".selectRow:checked").length > 0;
            $("#deleteSelected").prop("disabled", !anySelected);

            if (this.id === "selectAll") {
                $(".selectRow").prop("checked", $(this).prop("checked"));
            }
        });

        // Handle delete selected rows
        $("#deleteSelected").click(function() {
            if (confirm("Are you sure you want to delete the selected rows?")) {
                // Collect all selected row IDs
                const ids = $(".selectRow:checked")
                    .map(function() {
                        return $(this).closest("tr").data("id");
                    })
                    .get();

                if (ids.length > 0) {
                    // Send AJAX request to delete rows
                    $.ajax({
                        url: '{{ route("quote.ajax.deleteAttachment") }}', // Your server endpoint
                        method: 'POST', // HTTP method
                        data: {
                            ids: ids,
                            _token: '{{ csrf_token() }}' // CSRF token for Laravel
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove rows from the table
                                $(".selectRow:checked").each(function() {
                                    $(this).closest("tr").remove();
                                });
                                alert("Selected rows deleted successfully.");
                                $("#deleteSelected").prop("disabled", true);
                                $("#selectAll").prop("checked", false);
                            } else {
                                alert("Failed to delete the selected rows.");
                            }
                        },
                        error: function() {
                            alert("An error occurred while deleting the rows.");
                        }
                    });
                }
            }
        });

        // Handle "Download Selected" button
        $("#downloadSelected").click(function() {
            const selectedFiles = [];

            // Collect selected files
            $(".selectRow:checked").each(function() {
                const row = $(this).closest("tr");
                const fileUrl = row.find("a[target='_blank']").attr("href");
                selectedFiles.push(fileUrl);
            });

            if (selectedFiles.length > 0) {
                downloadMultipleFiles(selectedFiles);
            }
        });

        // Function to trigger download for each file
        function downloadMultipleFiles(files) {
            files.forEach((fileUrl) => {
                const a = document.createElement("a");
                a.href = fileUrl;
                a.download = ""; // Optional: Set custom filename
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        }

    });
    // ****************************************************************


    //**************insrtTitle
    function insrtAppoinment() {
        const node = document.createElement("tr");
        node.classList.add("add_insrtAppoinment");
        node.innerHTML = `<td>
                    <div class="d-flex">
                        <p class="leftNum">1</p>
                        <select class="form-control editInput selectOptions setUserData" id="">
                            <option>Select user</option>
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
                        <select class="form-control editInput selectOptions setAppointmentType">
                        </select>
                    </div>
                    <div class="Priority">
                        <label>Priority :</label>
                        <select class="form-control editInput selectOptions" id="inputJobType">
                            <option value="1">Low</option>
                            <option value="2" selected>Medium</option>
                            <option value="3">High</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="statuswating">
                        <label class="form-check-label" for="floatingAppointment">Awaiting </label>
                        <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                    </div>
                    <div class="tabheadingTitle">
                        <a href="#" class="profileDrop me-3"> Notify</a>
                    </div>
                </td>  
            `;

        appointmentType();
        getUsersData();

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
        salesAppointment(tableBody);
    }

    function salesAppointment(table) {

        let existingTfoot = table.querySelector('tfoot');
        if (existingTfoot) {
            // Remove the existing footer
            existingTfoot.remove();
        }

        const tfoot = document.createElement('tfoot');

        const footerRow = document.createElement('tr');
        const footerCell = document.createElement('td');

        // Set the cell to span all columns (adjust colspan to your table structure)
        footerCell.colSpan = 8; // Change 8 to the number of columns in your table
        footerCell.innerHTML = `<a href="#!" class="profileDrop ms-3">Save Appointment(s)</a>`;

        // Append the cell to the row
        footerRow.appendChild(footerCell);

        // Append the row to the footer
        tfoot.appendChild(footerRow);

        // Append the footer to the table
        table.appendChild(tfoot);

    }

    function getUsersData() {
        $.ajax({
            url: '{{ route("quote.ajax.getUsersData") }}',
            method: 'GET',
            success: function(response) {
                console.log("response.userData", response.data);

                const data = response.data;
                const users = document.querySelectorAll('.setUserData');
                console.log("Users:", users);

                if (users.length === 0) {
                    console.error("No elements found with class 'setUserData'");
                    return;
                }

                users.forEach(user => {

                    // Clear existing options if needed
                    user.innerHTML = ''; // Optional, uncomment if required
                    const defaultOption = document.createElement('option');
                    defaultOption.value = ''; // Use an empty value for the "Please Select" option
                    defaultOption.textContent = 'Please Select';
                    defaultOption.disabled = true; // Make it unselectable (optional)
                    defaultOption.selected = true; // Pre-select it by default
                    user.appendChild(defaultOption);

                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        user.appendChild(option);
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }

    function appointmentType() {
        $.ajax({
            url: '{{ route("job.ajax.jobAppointment") }}',
            method: 'GET',
            success: function(response) {
                console.log("response.jobAppointment", response.data);

                const data = response.data;
                const appointments = document.querySelectorAll('.setAppointmentType');
                console.log("Appointments:", appointments);

                if (appointments.length === 0) {
                    console.error("No elements found with class 'setAppointmentType'");
                    return;
                }

                appointments.forEach(appointment => {
                    // Clear existing options if needed
                    appointment.innerHTML = ''; // Optional, uncomment if required

                    data.forEach(item => {
                        console.log("Item:", item);

                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name;
                        appointment.appendChild(option);
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    }
</script>