@extends('backEnd.layouts.master')
@section('title',' Add New')
@section('content')

<!--main content start-->
<section id="main-content" class="">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <header class="panel-heading">
                       @if(isset($sales_day_book))
                        Edit
                        @else
                        Add
                        @endif
                        Sales Day Book 
                    </header>
                    @if (session('success'))
                    <div class="aalert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" method="POST" action="{{ url('/admin/sales-finance/sales/save-sales-day-book') }}" role="form">
                                @csrf
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Customer <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="hidden" id="sales_day_book_id" name="sales_day_book_id" value="{{ isset($sales_day_book->id) ? $sales_day_book->id : '' }}">
                                        <input type="hidden" id="customer_id" name="customer_id" value="{{ isset($sales_day_book->customer_id) ? $sales_day_book->customer_id : '' }}">
                                        <select class="form-control" id="getCustomerList" name="customer_id">
                                            <option value="">Select Customer</option>
                                        </select>
                                        @error('customer_id')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Date <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="Date_input" name="date" placeholder="Date" value="{{ isset($sales_day_book->date) ? date('d-m-Y', strtotime($sales_day_book->date)) : '' }}">
                                        @error('date')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Invoice <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="Invoice_input" name="invoice_no" placeholder="Invoice no." value="{{ isset($sales_day_book->invoice_no) ? $sales_day_book->invoice_no : '' }}">
                                        @error('invoice_no')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Net <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" placeholder="Net Amount" name="netAmount" value="{{ isset($sales_day_book->netAmount) ? $sales_day_book->netAmount : '' }}" id="net_amount" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                        @error('netAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">VAT <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="hidden" class="form-control" id="tax_id" placeholder="Tax ID" value="{{ isset($sales_day_book->Vat) ? $sales_day_book->Vat : '' }}">
                                        <select class="form-control" id="vat_input" name="Vat">
                                            <option value="">Select VAT</option>
                                        </select>
                                        @error('Vat')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">VAT Amount <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" placeholder="VAT Amount" name="vatAmount" id="vat_amount" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="{{ isset($sales_day_book->vatAmount) ? $sales_day_book->vatAmount : '' }}" readonly>
                                        @error('vatAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Gross <span class="radStar">*</span> </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" placeholder="Gross" name="grossAmount" id="gross_amount" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="{{ isset($sales_day_book->grossAmount) ? $sales_day_book->grossAmount : '' }}" readonly>
                                        @error('grossAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="submit" class="btn btn-default">Cencel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>
<script>
    $(document).ready(function() {
        $('#Date_input').datepicker({
            format: 'dd-mm-yyyy'
        });

        $('#Date_input').on('change', function() {
            $('#Date_input').datepicker('hide');
        });

        $("#salesDayBookModel").scroll(function() {
            $('#Date_input').datepicker('place');
        });
        getCustomerList();
        const vatInputValue = document.getElementById('vat_input'); // Assumes ID is 'vat_input'
        taxRate(vatInputValue);
    });

    document.addEventListener('DOMContentLoaded', function() {
        let vatInput = document.getElementById('vat_input');
        let netAmountInput = document.getElementById('net_amount');
        let vatAmountInput = document.getElementById('vat_amount');
        let grossAmountInput = document.getElementById('gross_amount');

        function calculateTax() {
            let netAmount = parseFloat(netAmountInput.value) || 0;
            let selectedOption = vatInput.options[vatInput.selectedIndex];
            let taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;

            let vatAmount = (netAmount * taxRate) / 100;
            let grossAmount = netAmount + vatAmount;

            vatAmountInput.value = vatAmount.toFixed(2);
            grossAmountInput.value = grossAmount.toFixed(2);
        }

        // Trigger calculation on VAT change or Net Amount change
        vatInput.addEventListener('change', calculateTax);
        netAmountInput.addEventListener('input', calculateTax);
    });

    function getCustomerList() {
        $.ajax({
            url: '{{ url("admin/getCustomerList") }}',
            method: 'GET',
            success: function(response) {
                console.log("Customer list data:", response.data);
                const selectedCustomerId = document.getElementById('customer_id').value;
                const customerSelect = document.getElementById('getCustomerList');
                customerSelect.innerHTML = '<option>Select Customer</option>';

                if (Array.isArray(response.data)) {
                    response.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        if (selectedCustomerId && user.id == selectedCustomerId) {
                            option.selected = true;
                        }
                        customerSelect.appendChild(option);
                    });
                } else {
                    console.warn("response.data is not an array");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", error);
            }
        });



    }



    function taxRate(dropdown) {
        $.ajax({
            url: '{{ url("admin/sales-finance/sales/getTaxRate") }}',
            method: 'GET',
            success: function(response) {
                console.log("response.data", response.data);
                if (Array.isArray(response.data)) {
                    // const dropdown = document.getElementById('vat_input'); // Assumes ID is 'vat_input'
                    if (!dropdown) {
                        console.error("Element with ID not found");
                        return;
                    }

                    dropdown.innerHTML = ''; // Clear existing options

                    const optionInitial = document.createElement('option');
                    const preTaxID = document.getElementById('tax_id').value;
                    optionInitial.textContent = "Please Select";
                    optionInitial.value = 0;
                    dropdown.appendChild(optionInitial);

                    response.data.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = code.name + " (" + code.tax_rate + "%)";
                        option.setAttribute('data-tax-rate', code.tax_rate);
                        if (preTaxID && code.id == preTaxID) {
                            option.selected = true;
                        }
                        dropdown.appendChild(option);
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
</script>
@endsection