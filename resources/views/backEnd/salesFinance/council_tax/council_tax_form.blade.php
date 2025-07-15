@extends('backEnd.layouts.master')
@section('title',' Add New')
@section('content')

<section id="main-content" class="">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">Add Council Tax</header>
                    <div class="panel-body">
                        <div class="position-center add-council-tax">
                            <form class="form-horizontal" method="POST" action="{{ url('/admin/finance/save-council-tax') }}" role="form">
                                @csrf
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label ">Flat number (if applicable)</label>
                                    <div class="col-lg-9">
                                        <input type="hidden" name="council_tax_id" value="{{ isset($council_tax->id) ? $council_tax->id : '' }}">
                                        <input type="text" class="form-control" id="" name="flat_number" placeholder="Flat number (if applicable)" value="{{ isset($council_tax->flat_number) ? $council_tax->flat_number : '' }}">
                                        @error('flat_number')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Address <span class="radStar">*</span> </label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Address" name="address" value="{{ isset($council_tax->address) ? $council_tax->address : '' }}">
                                        @error('address')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Post Code <span class="radStar">*</span> </label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Post Code" name="post_code" value="{{ isset($council_tax->post_code) ? $council_tax->post_code : '' }}">
                                        @error('post_code')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Council <span class="radStar">*</span> </label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Council" name="council" value="{{ isset($council_tax->council) ? $council_tax->council : '' }}">
                                        @error('council')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">No of Bedrooms</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="No of Bedrooms" name="no_of_bedrooms" value="{{ isset($council_tax->no_of_bedrooms) ? $council_tax->no_of_bedrooms : '' }}" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                        @error('no_of_bedrooms')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Occupancy</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Occupancy" name="occupancy" value="{{ isset($council_tax->occupancy) ? $council_tax->occupancy : '' }}">
                                        @error('occupancy')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Owned by Omega <span class="radStar">*</span> </label>
                                    <div class="col-lg-9">
                                        <input type="radio" id="OwnedbyOmegaYes" name="owned_by_omega" value="1" {{ isset($council_tax->owned_by_omega) && $council_tax->owned_by_omega == 1 ? 'checked' : '' }}>
                                        <label for="OwnedbyOmegaYes" class="control-label"> Yes</label>
                                        <input type="radio" id="OwnedbyOmegaNo" name="owned_by_omega" value="0" {{ isset($council_tax->owned_by_omega) && $council_tax->owned_by_omega == 0 ? 'checked' : '' }}>
                                        <label for="OwnedbyOmegaNo" class="control-label"> No</label>
                                        @error('owned_by_omega')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Exempt <span class="radStar">*</span> </label>
                                    <div class="col-lg-9">
                                        <input type="radio" id="ExemptYes" name="exempt" value="1" {{ isset($council_tax->exempt) && $council_tax->exempt == 1 ? 'checked' : '' }}>
                                        <label for="ExemptYes" class="control-label"> Yes</label>
                                        <input type="radio" id="ExemptNo" name="exempt" value="0" {{ isset($council_tax->exempt) && $council_tax->exempt == 0 ? 'checked' : '' }}>
                                        <label for="ExemptNo" class="control-label"> No</label>
                                        @error('exempt')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Last bill date</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="last_bill_date" name="last_bill_date" placeholder="Last bill date" value="{{ isset($council_tax->last_bill_date) ? date('d-m-Y', strtotime($council_tax->last_bill_date)) : '' }}" readonly>
                                        @error('last_bill_date')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-sm-3 control-label">Account number<span class="radStar">*</span></label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account number" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="{{ isset($council_tax->account_number) ? $council_tax->account_number : '' }}">
                                        @error('account_number')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Bill Period</label>
                                    <div class="col-lg-4">
                                        <input type="text" class="form-control" id="bill_period_start_date" name="bill_period_start_date" placeholder="Start Period" value="{{ isset($council_tax->bill_period_start_date) ? date('d-m-Y', strtotime($council_tax->bill_period_start_date)) : '' }}" readonly>
                                        @error('bill_period_start_date')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-5">
                                        <input type="text" class="form-control" id="bill_period_end_date" name="bill_period_end_date" placeholder="End Period" value="{{ isset($council_tax->bill_period_end_date) ? date('d-m-Y', strtotime($council_tax->bill_period_end_date)) : '' }}" readonly>
                                        @error('bill_period_end_date')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Amount paid</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Amount paid" name="amount_paid" value="{{ isset($council_tax->amount_paid) ? $council_tax->amount_paid : '' }}" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                        @error('amount_paid')
                                        <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-3 col-sm-3 control-label">Additional Notes</label>
                                    <div class="col-lg-9">
                                        <input type="text" class="form-control" id="" placeholder="Additional Notes" name="additional" value="{{ isset($council_tax->additional) ? $council_tax->additional : '' }}">
                                    </div>
                                </div>
                                <div class="form-group council-tax-btn">
                                    <div class="col-lg-offset-3 col-lg-9">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="submit" class="btn btn-default">Cencel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>


<script src="{{ url('public/js/salesFinance/council_tax.js') }}"></script>
@endsection