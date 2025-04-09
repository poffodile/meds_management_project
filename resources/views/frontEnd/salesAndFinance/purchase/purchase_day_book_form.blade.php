@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Add Purchase Day Book')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')


<section class="wrapper">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="panel">
          <div class="panel-body">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
              <div class="pageTitle">
                <h3>Purchase Day Book</h3>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12">
                <form action="{{ url('purchase/save-purchase-day-book') }}" method="POST" class="customerForm">
                  @csrf
                  <div class="newJobForm card mt-4">
                    <div class="row">
                      <div class="col-md-6 col-lg-6 col-xl-6">
                        <div class="formDtail">
                          <div class="form-group row">
                            <label for="Supplier_input" class="col-sm-3 col-form-label"> Supplier <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <input type="hidden" name="purchase_day_book_id" value="{{ isset($purchaseBook->id) ? $purchaseBook->id : '' }}">
                              <select class="form-control editInput selectOptions" name="supplier_id" id="Supplier_input">
                                <option>Please Select</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ isset($purchaseBook) && $purchaseBook->supplier_id === $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="Date_input" class="col-sm-3 col-form-label"> Date <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <input type="Date" class="form-control editInput" name="date" value="{{ isset($purchaseBook->date) ? $purchaseBook->date : '' }}" id="Date_input">
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="net_amount" class="col-sm-3 col-form-label"> Net <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="netAmount" id="net_amount" value="{{ isset($purchaseBook->netAmount) ? $purchaseBook->netAmount : '' }}" placeholder="">
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="gross_amount" class="col-sm-3 col-form-label">Total Amount (to be paid)</label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="" id="totalAmount" value="{{ isset($purchaseBook) ? ($purchaseBook->grossAmount + ($purchaseBook->reclaim ?? 0)) : '' }}" readonly>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="expenses" class="col-sm-3 col-form-label"> Expenses <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <select class="form-control editInput selectOptions" name="expense_type" id="expenses">
                                <option>Please Select</option>
                                @foreach($purchase_expenses as $purchase_expense)
                                <option value="{{ $purchase_expense->id }}" {{ isset($purchaseBook->expense_type) && ($purchaseBook->expense_type == $purchase_expense->id) ? 'selected' : '' }}>{{ $purchase_expense->title }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="rate_input" class="col-sm-3 col-form-label">Expense Amount</label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="expense_amount" id="expenses_amount" value="{{ isset($purchaseBook->expense_amount) ? $purchaseBook->expense_amount :  '' }}" readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6 col-lg-6 col-xl-6">
                        <div class="formDtail">
                          <div class="form-group row">
                            <label for="vat_input" class="col-sm-3 col-form-label">VAT <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <select class="form-control editInput selectOptions" name="Vat" id="vat_input">
                                <option>-Not Assigned-</option>
                                @foreach($taxRates as $taxrate)
                                <option value="{{ $taxrate->id }}" {{ isset($purchaseBook) && $purchaseBook->Vat  === $taxrate->id  ? 'selected' : '' }} data-tax-rate="{{ $taxrate->tax_rate }}">{{ $taxrate->name }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="vat_amount" class="col-sm-3 col-form-label">VAT Amount</label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="vatAmount" value="{{ isset($purchaseBook->vatAmount) ? $purchaseBook->vatAmount : ''}}" id="vat_amount" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="gross_amount" class="col-sm-3 col-form-label">Gross</label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="grossAmount" id="gross_amount" value="{{ isset($purchaseBook->grossAmount) ? $purchaseBook->grossAmount :  '' }}" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="reclaim_amount" class="col-sm-3 col-form-label"> Reclaim <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="reclaim" id="reclaim_amount" value="{{ isset($purchaseBook->reclaim) ? $purchaseBook->reclaim : ''   }}" placeholder="" readonly>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="not_claim" class="col-sm-3 col-form-label"> Not Claim <span class="radStar">*</span></label>
                            <div class="col-sm-8">
                              <input type="text" class="form-control editInput" name="not_reclaim" id="not_claim" value="{{ isset($purchaseBook->not_reclaim) ? $purchaseBook->not_reclaim :  '' }}" placeholder="" readonly>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-12">
                        <div class="pageTitleBtn">
                          <button type="submit" class="profileDrop reDesignBtn"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let vatInput = document.getElementById('vat_input');
    let netAmountInput = document.getElementById('net_amount');
    let vatAmountInput = document.getElementById('vat_amount');
    let grossAmountInput = document.getElementById('gross_amount');

    let not_claim = document.getElementById('not_claim');
    let reclaim_amount = document.getElementById('reclaim_amount');
    let totalAmountInput = document.getElementById('totalAmount');
    let expensesAmountInput = document.getElementById('expenses_amount');



    function getCalculatedData() {
      $.ajax({
        type: "GET",
        url: "{{ url('/purchase/reclaimPercantage') }}",
        success: function(response) {
          console.log("reclaimPercantage ", response.data);
          var vatAmount = vatAmountInput.value;
          console.log("vatAmount", vatAmount);

          let reclaimed = (vatAmount * response.data) / 100;

          console.log("Reclaimed Amount:", reclaimed.toFixed(2));
          reclaim_amount.value = reclaimed.toFixed(2);
          var not_claimedAmt = (parseFloat(vatAmount) - parseFloat(reclaimed)).toFixed(2);
          not_claim.value = not_claimedAmt;
          expensesAmountInput.value = not_claimedAmt;
          totalAmount = parseFloat(grossAmountInput.value) - parseFloat(reclaim_amount.value);
          totalAmountInput.value = totalAmount.toFixed(2);
        }
      });
    }

    function calculateTax() {
      let netAmount = parseFloat(netAmountInput.value) || 0;
      let selectedOption = vatInput.options[vatInput.selectedIndex];
      let taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;

      let vatAmount = (netAmount * taxRate) / 100;
      let grossAmount = netAmount + vatAmount;

      $.ajax({
        type: "GET",
        url: "{{ url('/purchase/purchase-day-book-reclaim-per') }}",
        data: '',
        success: function(data) {
          console.log("Response ", data);
          if (data == "0") {
            getCalculatedData(); // Now this function is defined and accessible
          } else if (data == "1") {
            not_claim.value = vat_amount.value;
            reclaim_amount.value = totalAmountInput.value = "00.00";
            // document.getElementById("expenses_amount").value = not_claim; // Set input value
          }
        }
      });

      vatAmountInput.value = vatAmount.toFixed(2);
      grossAmountInput.value = grossAmount.toFixed(2);


      expensesAmountInput.value = "";
    }

    // Trigger calculation on VAT change or Net Amount change
    vatInput.addEventListener('change', calculateTax);
    netAmountInput.addEventListener('input', calculateTax);

    document.getElementById("expenses").addEventListener("change", function() {
      var not_claimedAmt = document.getElementById('not_claim').value;
      document.getElementById("expenses_amount").value = not_claimedAmt;

    });

  });
</script>

@endsection