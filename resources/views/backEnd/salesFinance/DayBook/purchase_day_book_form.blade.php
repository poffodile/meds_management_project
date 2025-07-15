@extends('backEnd.layouts.master')
@section('title',' Add New')
@section('content')
<section id="main-content" class="">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        @if(isset($purchase_day_book))
                        Edit
                        @else
                        Add
                        @endif
                        Purchase Day Book 
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

                    @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        {{ session('info') }}
                    </div>
                    @endif
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" method="post" action="{{ route('backend.purchase_day_book.store') }}" role="form">
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Supplier <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        @csrf
                                        <input type="hidden" id="purchase_day_book_id" name="purchase_day_book_id" value="{{ isset($purchase_day_book) ? $purchase_day_book->id : '' }}">
                                        <input type="hidden" id="supplier_id" value="{{ isset($purchase_day_book) ? $purchase_day_book->supplier_id : '' }}">
                                        <select class="form-control" id="Supplier_input" name="supplier_id">
                                            <option value="">Select Supplier</option>
                                        </select>
                                        @error('supplier_id')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Date <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="date" id="Date_input" value="{{ isset($purchase_day_book) ? \Carbon\Carbon::parse($purchase_day_book->date)->format('d-m-Y') : '' }}" placeholder="Date">
                                        @error('date')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Net <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="netAmount" value="{{ isset($purchase_day_book) ? $purchase_day_book->netAmount : '' }}" id="net_amount" placeholder="Net" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                        @error('netAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Total Amount (to be paid)</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" id="totalAmount" value="{{ isset($purchase_day_book) ? ($purchase_day_book->grossAmount - $purchase_day_book->reclaim) : ''}}" placeholder="Total Amount (to be paid)" readonly oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">VAT <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="hidden" id="tax_id" value="{{ isset($purchase_day_book) ? $purchase_day_book->Vat : '' }}">
                                        <select class="form-control" id="vat_input" name="Vat">
                                            <option value="">Select VAT</option>
                                        </select>
                                        @error('Vat')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">VAT Amount <span class="radStar">*</span> </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="vatAmount" value="{{ isset($purchase_day_book) ? $purchase_day_book->vatAmount : '' }}" id="vat_amount" placeholder="VAT Amount" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" readonly>
                                        @error('vatAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Gross <span class="radStar">*</span> </label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="grossAmount" value="{{ isset($purchase_day_book) ? $purchase_day_book->grossAmount : '' }}" id="gross_amount" placeholder="Gross" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" readonly>
                                          @error('grossAmount')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Reclaim</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="reclaim" value="{{ isset($purchase_day_book) ? $purchase_day_book->reclaim : ''}}" id="reclaim_amount" placeholder="Reclaim" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Not Claim</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="not_reclaim" id="not_claim" value="{{ isset($purchase_day_book) ? $purchase_day_book->not_reclaim : '' }}" placeholder="Not Claim" readonly>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Expenses</label>
                                    <div class="col-lg-10">
                                        <input type="hidden" id="expenses_id" value="{{ isset($purchase_day_book) ? $purchase_day_book->expense_type : '' }}">
                                        <select class="form-control" id="expenses" name="expense_type">
                                            <option value="">Select Expenses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Expense Amount</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" name="expense_amount" value="{{ isset($purchase_day_book) ? $purchase_day_book->expense_amount : '' }}" id="expenses_amount" placeholder="Expense Amount" readonly>
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
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>

@endsection

<script>
    
    setTimeout(function() {
        // Hide all alerts after 3 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 3000); // 3000 milliseconds = 3 seconds


    document.addEventListener("DOMContentLoaded", function() {

        // Initialize the datepicker
        $('#Date_input').datepicker({
            format: 'dd-mm-yyyy'
        });
        $('#Date_input').on('change', function() {
            $('#Date_input').datepicker('hide');
        });

        $("#purchase_day_book_form").scroll(function() {
            $('#Date_input').datepicker('place');
        });

        getSupplierList();
        getPurchaseExpense();

        const vatInputValue = document.getElementById('vat_input'); // Assumes ID is 'vat_input'
        taxRate(vatInputValue);

        // Add event listener to update the input field when an option is selected
        vatInputValue.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const taxRateValue = selectedOption.getAttribute('data-tax-rate');
            console.log("Selected tax rate:", taxRateValue);
            // Update the input field or perform any other action with the tax rate value
        });

        let vatInput = document.getElementById('vat_input');
        let netAmountInput = document.getElementById('net_amount');
        let vatAmountInput = document.getElementById('vat_amount');
        let grossAmountInput = document.getElementById('gross_amount');

        let not_claim = document.getElementById('not_claim');
        let reclaim_amount = document.getElementById('reclaim_amount');
        let totalAmountInput = document.getElementById('totalAmount');
        let expensesAmountInput = document.getElementById('expenses_amount');
        let expenses = document.getElementById('expenses');


        function getCalculatedData() {
            $.ajax({
                type: "GET",
                url: "{{ url('admin/sales-finance/purchase/reclaimPercantage') }}",
                success: function(response) {
                    console.log("reclaimPercantage ", response.data);
                    var vatAmount = vatAmountInput.value;
                    console.log("vatAmount", vatAmount);
                    let reclaimed = (vatAmount * response.data) / 100;
                    console.log("Reclaimed Amount:", reclaimed.toFixed(2));
                    reclaim_amount.value = reclaimed.toFixed(2);
                    var not_claimedAmt = (parseFloat(vatAmount) - parseFloat(reclaimed)).toFixed(2);
                    not_claim.value = not_claimedAmt;
                    totalAmount = parseFloat(grossAmountInput.value) - parseFloat(reclaim_amount.value);
                    totalAmountInput.value = totalAmount.toFixed(2);

                    let input = document.getElementById('purchase_day_book_id');
                    if (input.value.trim() !== "") {
                        console.log("Input has a value:", input.value);
                        expensesAmountInput.value = not_claimedAmt;
                    }
                }
            });
        }

        function calculateTax() {
            let netAmount = parseFloat(netAmountInput.value) || 0;
            // console.log("Net Amount:", netAmount);
            let selectedOption = vatInput.options[vatInput.selectedIndex];
            // console.log("Selected Option:", selectedOption);
            let taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;
            // console.log("Tax Rate:", taxRate);
            let vatAmount = (netAmount * taxRate) / 100;
            let grossAmount = netAmount + vatAmount;
            // console.log("Gross Amount:", grossAmount);
            $.ajax({
                type: "GET",
                url: "{{ url('admin/sales-finance/purchase/purchase-day-book-reclaim-per') }}",
                data: '',
                success: function(data) {
                    console.log("Response ", data);
                    if (data == "0") { // check Home is not registered
                        getCalculatedData();
                    } else if (data == "1") {
                        not_claim.value = vat_amount.value;
                        reclaim_amount.value = totalAmountInput.value = "00.00";
                    }
                }
            });

            vatAmountInput.value = vatAmount.toFixed(2);
            grossAmountInput.value = grossAmount.toFixed(2);
            expenses.value = '';
            expensesAmountInput.value = "";
        }

        // Trigger calculation on VAT change or Net Amount change
        vatInput.addEventListener('change', calculateTax);
        netAmountInput.addEventListener('input', calculateTax);

        document.getElementById("expenses").addEventListener("change", function() {
            var not_claimedAmt = not_claim.value;
            expensesAmountInput.value = not_claimedAmt;
        });
    });



    //  get Suppliers list
    function getSupplierList() {
        $.ajax({
            url: "{{ url('admin/sales-finance/purchase/getSupplierData') }}",
            method: 'GET',
            success: function(response) {
                console.log("supplier list data:", response.data);
                const selectedSupplierId = document.getElementById('supplier_id').value;
                const supplierSelect = document.getElementById('Supplier_input');
                supplierSelect.innerHTML = '<option>Select Suppliers</option>';

                if (Array.isArray(response.data)) {
                    response.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        if (selectedSupplierId && user.id == selectedSupplierId) {
                            option.selected = true;
                        }
                        supplierSelect.appendChild(option);
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

    // Get Purchase Expeanses
    function getPurchaseExpense() {
        $.ajax({
            url: "{{ route('purchase.getPurchaseExpense') }}",
            method: 'GET',
            success: function(response) {
                console.log("purchase expenses:", response.data);
                const selectedexpednseId = document.getElementById('expenses_id').value;
                const expensesSelect = document.getElementById('expenses');
                expensesSelect.innerHTML = '<option>Select Expenses</option>';

                if (Array.isArray(response.data)) {
                    response.data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.title;
                        if (selectedexpednseId && user.id == selectedexpednseId) {
                            option.selected = true;
                        }
                        expensesSelect.appendChild(option);
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