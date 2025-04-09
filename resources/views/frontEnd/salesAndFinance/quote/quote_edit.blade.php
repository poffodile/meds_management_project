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
                <div class="alert alert-danger">{{ session('error') }} </div>
                @endif

                @foreach ($errors->all() as $error)
                <div class="alert alert-danger">{{ $error }}</div>
                    @endforeach
                    <form action="{{ url('/quote/saveQuoteData') }}" method="POST" id="myForm" class="customerForm mt-3">
                        @csrf
                        <input type="hidden" value="{{ $type}}" id="page_type">
                        <input type="hidden" name="quote_ref" value="{{ $quoteData['quote_ref'] ?? '' }}">
                        <div class="newJobForm card">
                            <!-- Customer section start -->
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
                                            <label for="inputCustomer" class="col-sm-3 col-form-label">Customer <span class="radStar">*</span></label>
                                            <div class="col-sm-7">
                                                <input type="hidden" value="{{ $quoteData['customer']['customer.id'] }}" id="setCustomerId">
                                                <select class="form-control editInput selectOptions" name="customer_id" id="getCustomerListedit">
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
                                                    <option>None</option>
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
                            <!-- Customer section End -->
                            
                            <!-- quote section start -->
                            <div class="row" id="hideQuoteDetails">
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
                                                    <option value="1" {{ $quoteData['project_id'] == 1 ? 'selected' : '' }}>Test 1</option>
                                                    <option value="2" {{ $quoteData['project_id'] == 2 ? 'selected' : '' }}>Test 2</option>
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
                                                <!-- <input type="hidden" name="quote_id"> -->
                                                <!-- <input type="hidden" name="quote_ref"> -->
                                                <input type="text" class="form-control-plaintext editInput" id="inputName" value="{{ $quoteData['quote_ref']  }}" readonly>
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
                                            <input type="text" class="form-control editInput textareaInput" id="" value="{{ $quoteData['customer_ref'] }}" name="customer_ref" placeholder="Customer Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Customer Job Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="inputPurchase" value="{{ $quoteData['customer_job_ref'] }}" name="customer_job_ref" placeholder="Customer Job Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputPurchase" class="col-sm-3 col-form-label">Purchase Order Ref</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" id="" value="{{ $quoteData['purchase_order_ref'] }}" name="purchase_order_ref" placeholder="Purchase Order Ref">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Source</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput" name="source" id="">
                                                <option value="">None</option>
                                                @foreach($quoteSource as $value)
                                                <option {{ $quoteData['source'] == $value->id ? 'selected' : '' }} value="{{ $value->id}}">{{ $value->title }}</option>
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
                                                <option value="AM" {{ $quoteData['period'] == 'AM' ? 'selected' : '' }}>AM</option>
                                                <option value="PM" {{ $quoteData['period'] == 'PM' ? 'selected' : '' }}>PM</option>
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

                                                <option value="Draft" {{ $quoteData['status'] == "Draft" ? 'selected' : '' }}>Draft</option>
                                                <option value="Processed" {{ $quoteData['status'] == "Processed" ? 'selected' : '' }}>Processed</option>
                                                <option value="Call back" {{ $quoteData['status'] == "Call back" ? 'selected' : '' }}>Call back</option>
                                                <option value="Accepted" {{ $quoteData['status'] == "Accepted" ? 'selected' : '' }}>Accepted</option>
                                                <option value="Rejected" {{ $quoteData['status'] == "Rejected" ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Tags</label>
                                        <div class="col-sm-7">
                                            <input type="hidden" id="hiddenQuote" value="{{ $quoteData['tags']}}">
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
                            <!-- quote section End -->
                        </div>

                        <!--   off newJobForm -->
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
                                                </div>
                                                <!-- ENd col-9 -->
                                            </div>
                                            <div class="tab-pane fade" id="nav-Tasks" role="tabpanel" aria-labelledby="nav-Tasks-tab" tabindex="0">
                                                <div class="tabheadingTitle">
                                                    <a href="#!" class="profileDrop me-3" onclick="insrtAppoinment()"> New Appointments</a>
                                                    <a href="#!" class="profileDrop"> Send To Planner</a>
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
                        <div class="newJobForm mt-4" id="hideItemDetails">
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
                                                    <th>Cost Price($) </th>
                                                    <th>Cost Calc</th>
                                                    <th>Price($) </th>
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
                        <div class="newJobForm mt-4" id="hideExtraInformation">
                            <label class="upperlineTitle">Extra Information</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="">
                                        <h4 class="contTitle text-start">Description</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" name="extra_information" id="textarea8">{{ $quoteData['extra_information'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="">
                                        <h4 class="contTitle text-start">Customer Notes</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea9" name="customer_notes"> {{ $quoteData['customer_notes'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pt-3">
                                        <h4 class="contTitle text-start">Terms</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea10" name="tearms"> {{ $quoteData['tearms'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pt-3">
                                        <h4 class="contTitle text-start">Internal Notes</h4>
                                        <div class="mt-3">
                                            <textarea cols="40" rows="5" id="textarea11" name="internal_notes"> {{ $quoteData['internal_notes'] }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End  off newJobForm -->

                        <!-- ***************************************Start deposit Details****************************************** -->
                        <div class="newJobForm mt-4" id="hideDepositSection">
                            <label class="upperlineTitle">Deposit Details</label>
                            <div class="row">
                                <div class="col-sm-3 mb-3 mt-2">
                                    <div class=" p-0">
                                        <a href="javascript:void(0)" class="profileDrop" id="createDepositModelOpen" data-bs-toggle="modal" data-bs-target="#creaditDepositModal">Create Deposit</a>
                                        <span class="col-form-label">
                                            or
                                        </span>
                                        <a href="javascript:void(0)" class="profileDrop" id="getTaxtInvoiceRateValue" data-bs-toggle="modal" data-bs-target="#creaditDepositInvoiceModal">Create Deposit Invoice</a>
                                    </div>
                                </div>

                                <!-- ************************* -->
                                <div class="modal fade" id="creaditDepositModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="creaditDepositModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-5" id="creaditDepositModalLabel">Create Deposit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Tabs -->
                                                <ul class="nav nav-tabs" id="modalTabs">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href="#tab1" data-toggle="tab">Payment Details</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#tab2" data-toggle="tab">Payment Type</a>
                                                    </li>
                                                </ul>

                                                <!-- Tab Content -->
                                                <div class="tab-content">
                                                    <form action="" id="createDepositForm">
                                                        <div class="tab-pane fade show active" id="tab1">
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Invoice</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="hidden" name="quote_deposit_id" id="quote_deposit_id">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName"
                                                                            value="{{ $quoteData['quote_ref'] }} - Quote Date {{ $quoteData['quota_date_deposit'] }}" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="hidden">
                                                                        <input type="text" class="form-control-plaintext editInput" id="inputName" value="{{ $quoteData['customer']['name'] }}" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Total (inc. VAT)</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="setTotalCreditAmount" value="" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputName" class="col-sm-3 col-form-label">Outstanding Amount</label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control-plaintext editInput" id="setOustandingCreditAmount" value="" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Percentage <span class="radStar">*</span></label>
                                                                    <div class="col-sm-5">
                                                                        <input type="number" class="form-control editInput" name="deposit_percantage" min="0" max="100" maxlength="3" oninput="this.value = this.value.slice(0, 3)" id="deposit_percantage" value="100">
                                                                    </div>
                                                                    <div class="col-sm-1 ps-0">
                                                                        <input class="form-control editInput text-center" value="%" disabled="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Amount (inc. VAT) <span class="radStar">*</span></label>
                                                                    <div class="col-sm-1 pe-0">
                                                                        <input class="form-control editInput text-center" value="&#163;" disabled="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <input type="text" class="form-control editInput" name="amount" id="deposit_amount" value="">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Reference <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <input type="text" class="form-control editInput" name="reference" id="reference" placeholder="Reference">
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Description <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <textarea class="form-control textareaInput rounded-1" name="description" id="description" rows="3" placeholder="Description"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="tab2">
                                                            <div class="mb-2 row">
                                                                <div class="col-sm-12">
                                                                    <div class="text-end">
                                                                        <h5>Paying Now: <span id="payingNow">$12000.00</span></h5>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="newJobForm card">
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Payment Type <span class="radStar">*</span></label>
                                                                    <div class="col-sm-9">
                                                                        <select class="form-control editInput selectOptions" name="payment_type" id="payment_type">
                                                                            @foreach($paymentType as $value)
                                                                            <option value="{{ $value->id }}">{{ $value->title }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-2 row">
                                                                    <label for="inputCity" class="col-sm-3 col-form-label">Deposit Date <span class="radStar">*</span></label>
                                                                    <div class="col-sm-5">
                                                                        <input type="date" class="form-control editInput" id="deposit_date" name="deposit_date" value="{{ now()->format('Y-m-d') }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn profileDrop" id="prevTab" style="display: none;">Previous</button>
                                                <button type="button" class="btn profileDrop" id="nextTab">Next</button>
                                                <button type="button" class="btn profileDrop" id="saveButton" style="display: none;">Save</button>
                                                <button type="button" class="btn profileDrop" data-bs-dismiss="modal" id="cancelButton">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ************************ -->

                            <!-- *********************** -->
                            <div class="modal fade" id="creaditDepositInvoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" style="display: none;" aria-labelledby="creaditDepositInvoiceModalModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content add_Customer">
                                        <div class="modal-header">
                                            <h5 class="modal-title fs-5" id="creaditDepositInvoiceModalModalLabel">Create Deposit Invoice</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        @php
                                        use Carbon\Carbon;

                                        $today = Carbon::now(); // Get today's date
                                        $dateAfter21Days = $today->addDays(21); // Add 21 days to today's date
                                        @endphp
                                        <div class="modal-body ">
                                            <div class="contantbodypopup p-0">
                                                <div class="newJobForm card">
                                                    <div class="mb-2 row">
                                                        <input type="hidden" id="edit_customer_deposit_invoice">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">Invoice Date <span class="radStar">*</span></label>
                                                        <div class="col-sm-5">
                                                            <input type="date" class="form-control editInput" id="invoice_date" value="{{ now()->format('d/m/Y') }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">Due Date <span class="radStar">*</span></label>
                                                        <div class="col-sm-5">
                                                            <input type="date" class="form-control editInput" id="due_date" value="{{ $dateAfter21Days->format('d/m/Y') }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Line Item <span class="radStar">*</span></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control editInput" id="line_item" placeholder="Line Item">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">Line Description <span class="radStar">*</span></label>
                                                        <div class="col-sm-9">
                                                            <textarea class="form-control textareaInput rounded-1" name="address" id="line_description" rows="3" placeholder="Description"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">Deposit Percentage <span class="radStar">*</span></label>
                                                        <div class="col-sm-4">
                                                            <input type="number" class="form-control editInput" id="deposit_percentage_invoice" min="0" max="100" maxlength="3" oninput="this.value = this.value.slice(0, 3)" value="0">
                                                        </div>
                                                        <div class="col-sm-3 ps-0">
                                                            <input type="hidden" id="setDepositAmountHidden">
                                                            <input class="form-control editInput text-center" value="" id="setDepositAmount" disabled="">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">Sub Total <span class="radStar">*</span></label>
                                                        <div class="col-sm-1 pe-0">
                                                            <input class="form-control editInput text-center" value="$" disabled="">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <input type="text" class="form-control editInput" id="sub_total_invoice" value="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputCity" class="col-sm-3 col-form-label">VAT (%) <span class="radStar">*</span></label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" id="getTaxtRateHidden">
                                                            <select class="form-control editInput selectOptions" id="getTaxRateValue">
                                                                <option>-Please Select-</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="inputName" class="col-sm-3 col-form-label">Total (inc. VAT) <span class="radStar">*</span></label>
                                                        <div class="col-sm-9">
                                                            <input type="hidden" id="setDepositInvoiceAmountHidden">
                                                            <input type="text" class="form-control-plaintext editInput" id="setDepositInvoiceAmount" value="£0.00" readonly="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end modal body -->
                                        <div class="modal-footer customer_Form_Popup">
                                            <button type="button" class="btn profileDrop" id="saveInvoiceDepositAmount">Save</button>
                                            <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- *********************** -->

                            <div class="col-sm-12">
                                <h4 class="contTitle text-start mb-2 mt-2">Deposits</h4>
                                <div class="productDetailTable">
                                    <table class="table" id="depositData">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Deposit Date </th>
                                                <th>Mode of Payment </th>
                                                <th>Reference</th>
                                                <th>Description </th>
                                                <th>Created On </th>
                                                <th>Deposit Amount </th>
                                                <th>Refunded</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot></tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <h4 class="contTitle text-start mb-2 mt-2 ">Deposit Invoices</h4>
                                <div class="productDetailTable">
                                    <table class="table" id="invoiceDeposit">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Invoice Ref </th>
                                                <th>Invoice </th>
                                                <th>Due Date</th>
                                                <th>Sub Total </th>
                                                <th>VAT </th>
                                                <th>Total </th>
                                                <th>Outstanding</th>
                                                <th>Created On</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="8">No Records</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- **************************************End of deposit Details****************************************** -->

                        <!-- <div id="hideAttachmentTask"> -->

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
                        <div class="newJobForm mt-4" id="hidequoteTasks">
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
                                                                                        <label class="col-sm-4 col-form-label">Task User <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-4 col-form-label">Title <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" name="title" id="title">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task Type <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-3 col-form-label">Title <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-9">
                                                                                            <input type="text" class="form-control editInput" id="timerTitle">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-3 col-form-label">Timer <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-3 col-form-label">Task Type <span class="radStar">*</span></label>
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
                                                                                        <label for="inputCity" class="col-sm-3 col-form-label">Notes <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-4 col-form-label">Task User <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-4 col-form-label">Title <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" id="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">Task Type <span class="radStar">*</span></label>
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
                                                                                        <label class="col-sm-4 col-form-label">Start Date <span class="radStar">*</span></label>
                                                                                        <div class="col-sm-8">
                                                                                            <input type="text" class="form-control editInput" id="">
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="mb-2 row">
                                                                                        <label class="col-sm-4 col-form-label">End Date <span class="radStar">*</span></label>
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
                                                                                        <label for="inputCity" class="col-sm-3 col-form-label">Notes <span class="radStar">*</span></label>
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
                                                                                <label for="inputCity" class="col-sm-3 col-form-label">Postcode  <span class="radStar">*</span></label>
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
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                                <div class="pageTitleBtn">
                                    <!-- <input type="Submit" class="profileDrop"> -->
                                    <button type="button" id="submitBtn" class="profileDrop">Save</button>
                                    <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
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
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.3.2/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>


@include('frontEnd.salesAndFinance.jobs.layout.footer')
<script>
    const getAttachmentDataOnQuoteId = '{{ route("quote.ajax.getAttachmentDataOnQuoteId") }}';
    const getCustomerBillingAddressData = '{{ route("customer.ajax.getCustomerBillingAddressData") }}';
    const getQuoteTaskListURL = '{{ route("quote.ajax.getQuoteTaskList") }}';
    const getCustomerBillingAddress = '{{ route("customer.ajax.getCustomerBillingAddress") }}';
    const getCustomerData = '{{ route("customer.ajax.getCustomerList") }}';
    const getDepositeData = '{{ route("quote.ajax.getDepositeData") }}';
    const getQuoteInvoiceDeposit = '{{ route("quote.ajax.getQuoteInvoiceDeposit") }}';
    const getTagsURL = '{{ route("General.ajax.getTags") }}';
    const getQuoteTypesURL = '{{ route("quote.ajax.getQuoteTypes") }}';
    const getCustomerDetailsURL = '{{ route("customer.ajax.getCustomerDetails") }}';
    const getQuoteProductListURL = '{{ route("quote.ajax.getQuoteProductList") }}';
    const getActiveTaxRateURL = '{{ route("invoice.ajax.getActiveTaxRate") }}';
    const getCustomerSiteAddressURL = '{{ route("customer.ajax.getCustomerSiteAddress") }}';

    const getActiveAccountCodeURL = '{{ route("Invoice.ajax.getActiveAccountCode") }}';
    const getProductFromIdURL = '{{ route("item.ajax.getProductFromId") }}';
    // '{{ route("customer.ajax.getCustomerBillingAddress") }}'
</script>

<script type="text/javascript" src="{{ url('public/js/salesFinance/quotes/customQuoteForm.js') }}"></script>