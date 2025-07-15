@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Council Tax')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')


<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Council Tax</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="jobsection justify-content-end mb-0">
                                        <a href="javascript:void(0)" class="btn btn-warning openModalBtn" data-action='add' id=''><i class="fa fa-plus"></i> Add</a>
                                        <!-- <a href="javascript:void(0)" class="profileDrop">Export</a> -->
                                    </div>
                                </div>
                            </div>
                            <div class="maimtable productDetailTable mb-4 table-responsive">
                                <table id="council_tax" class="table border-top border-bottom tablechange" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Flat number <br> (if applicable)</th>
                                            <th>Address</th>
                                            <th>PostCode</th>
                                            <th>Council</th>
                                            <th>No of Bedrooms</th>
                                            <th>Owned by Omega</th>
                                            <th>Occupancy</th>
                                            <th>Exempt </th>
                                            <th>Account number</th>
                                            <th>Last bill</th>
                                            <th>Bill period</th>
                                            <th>Amount paid</th>
                                            <th>Additional Notes </th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($councilTaxs as $councilTax)
                                        <tr>
                                            <td>{{ $loop->iteration}}</td>
                                            <td>{{ $councilTax->flat_number }}</td>
                                            <td>{{ $councilTax->address }}</td>
                                            <td>{{ $councilTax->post_code }}</td>
                                            <td>{{ $councilTax->council }}</td>
                                            <td>{{ $councilTax->no_of_bedrooms }}</td>
                                            <td>{{ $councilTax->owned_by_omega == 1 ? 'Yes' : 'No' }}</td>
                                            <td>{{ $councilTax->occupancy }}</td>
                                            <td>{{ $councilTax->exempt == 1 ? 'Yes' : 'No' }}</td>
                                            <td>{{ $councilTax->account_number }}</td>
                                            <td class="white_space_nowrap">{{ $councilTax->last_bill_date }}</td>
                                            <td class="white_space_nowrap">{{ $councilTax->bill_period_start_date }} - {{ $councilTax->bill_period_end_date }}</td>
                                            <td>{{ $councilTax->amount_paid }}</td>
                                            <td>{{ $councilTax->additional }}</td>
                                            <td> <a href="#!" class="openModalBtn" data-action="edit" data-id="{{ $councilTax->id }}" data-flat-number="{{ $councilTax->flat_number }}" data-address="{{ $councilTax->address }}" data-post_code="{{ $councilTax->post_code}}" data-council="{{ $councilTax->council }}" data-no_of_bedrooms="{{ $councilTax->no_of_bedrooms }}" data-owned_by_omega="{{ $councilTax->owned_by_omega }}" data-occupancy="{{ $councilTax->occupancy }}" data-exempt="{{ $councilTax->exempt }}" data-account_number="{{ $councilTax->account_number }}" data-last_bill_date="{{ $councilTax->last_bill_date }}" data-bill_period_start_date="{{ $councilTax->bill_period_start_date }}" data-bill_period_end_date="{{ $councilTax->bill_period_end_date }}" data-amount_paid="{{ $councilTax->amount_paid }}" data-additional="{{ $councilTax->additional }}" id=""><i class="fa fa-pencil" aria-hidden="true"></i></a> | <a href="#!" class="deleteBtn" data-id="{{ $councilTax->id }}"><i class="fa fa-trash radStar" aria-hidden="true"></i></a> </td>
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
    </div>
</section>

<!-- Modal -->
<div class="modal fade" id="AddCouncilTax" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="error-text"></div>
            <form action="" id="addCouncilTaxForm" class="customerForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Add Council Tax</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="formDtail">
                                <div class="form-group">
                                    <label> Flat number (if applicable) </label>
                                    <div>
                                        <input type="hidden" name="council_tax_id" id="council_tax_id">
                                        <input type="text" class="form-control editInput" id="flat_num" name="flat_number" placeholder="Flat 1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label> Address <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" id="address" name="address" placeholder="40-42 Kemble Street, Prescot">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <label> Post Code <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput" id="postcode" name="post_code" placeholder="L34 5SQ">
                                        </div>
                                        <div class="col-sm-6 ps-3">
                                            <label> Council <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput" id="council" name="council" placeholder="Knowsley">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <label>No of Bedrooms </label>
                                            <input type="text" class="form-control editInput" name="no_of_bedrooms" id="no_of_bedrooms" placeholder="4" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                        </div>
                                        <div class="col-sm-6 ps-3">
                                            <label>Occupancy </label>
                                            <input type="text" class="form-control editInput" id="occupancy" name="occupancy" placeholder="2">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <label>Owned by Omega <span class="radStar">*</span></label>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="form-check-label m-0" for="ownedByOmegayes">Yes</label>
                                                <input class="form-check-input mt-0" type="radio" name="owned_by_omega" value="1" id="ownedByOmegayes">
                                                <label class="form-check-label m-0" for="ownedByOmegano">No</label>
                                                <input class="form-check-input mt-0" type="radio" name="owned_by_omega" value="0" id="ownedByOmegano">
                                            </div>
                                        </div>
                                        <div class="col-sm-6 ps-3">
                                            <label>Exempt <span class="radStar">*</span></label>
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="form-check-label m-0" for="exempt_yes">Yes</label>
                                                <input class="form-check-input mt-0" type="radio" name="exempt" value="1" id="exemptyes">
                                                <label class="form-check-label m-0" for="exempt_no">No</label>
                                                <input class="form-check-input mt-0" type="radio" name="exempt" value="0" id="exemptno">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Account number<span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" id="account_number" name="account_number" placeholder="Account number" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Last bill</label>
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="last_bill_date" id="last_bill_date" type="text" value="" autocomplete="off" class="form-control" placeholder="Last Bill Date">
                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary" type="button" id="openCalendarLastBillBtn">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Bill period </label>
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                                <input name="bill_period_start_date" id="bill_period_start_date" type="text" value="" autocomplete="off" class="form-control" placeholder="Start Date">
                                                <span class="input-group-btn datetime-picker2 btn_height">
                                                    <button class="btn btn-primary" type="button" id="openCalendarBillPeriodStartBtn">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 ps-3">
                                            <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                                <input name="bill_period_end_date" id="bill_period_end_date" type="text" value="" autocomplete="off" class="form-control" placeholder="End Date">
                                                <span class="input-group-btn datetime-picker2 btn_height">
                                                    <button class="btn btn-primary" type="button" id="openCalendarBillPeriodEndBtn">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                        <div id="dateError"> </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Amount paid </label>
                                    <div>
                                        <input type="text" class="form-control editInput" id="amount_paid" name="amount_paid" placeholder="Amount paid" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Additional Notes </label>
                                    <div>
                                        <input type="text" class="form-control editInput" id="additional_notes" name="additional" placeholder="Additional Notes">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="saveCouncilTax">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ url('public/js/salesFinance/council_tax.js') }}"></script>
<script>
    deleteURL = "{{ url('finance/delete-council-tax') }}/";
    saveData = "{{ url('finance/save-council-tax') }}";
    editData = "{{ url('finance/edit-council-tax') }}";
</script>
@endsection