@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase Day Book')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Purchase Day Book</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                <a href="#!" type="button" class="btn btn-warning openPurchaseDayBookModel" data-action="add"><i class="fa fa-plus"></i> Add</a>
                                <div>
                                    <select name="" class="form-control editInput selectOptions" id="getDataOnTax">
                                        <option value="0">Please Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange" id="purchaseDayBookTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Supplier </th>
                                            <th>Date</th>
                                            <th>Net</th>
                                            <th>VAT</th>
                                            <th>Gross </th>
                                            <th>Rate </th>
                                            <th>Total </th>
                                            <th>Reclaim </th>
                                            <th>Not Reclaim </th>
                                            <th>Expense Type </th>
                                            <th>Expense Amount </th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th style="text-align:right">Total:</th>
                                            <th></th> <!-- Net Amount -->
                                            <th></th> <!-- Net Amount -->
                                            <th></th> <!-- Net Amount -->
                                            <th></th> <!-- VAT Amount -->
                                            <th></th> <!-- Gross Amount -->
                                            <th></th> <!-- Tax Rate -->
                                            <th></th> <!-- Final Amount -->
                                            <th></th> <!-- Reclaim -->
                                            <th></th> <!-- Not Reclaim -->
                                            <th></th> <!-- Title -->
                                            <th></th> <!-- Expense Amount -->
                                            <th></th> <!-- Actions -->
                                        </tr>
                                    </tfoot>

                                </table>
                            </div>
                            <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Purchase day book Modal start here -->
<div class="modal fade" id="purchase_day_book_form" tabindex="-1" aria-labelledby="purchase_day_book_formLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle"></h4>
            </div>
            <form id="save-purchase-day-book">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="formDtail">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Supplier_input"> Supplier <span class="radStar">*</span></label>
                                            <div>
                                                <input type="hidden" name="purchase_day_book_id" id="purchase_day_book_id">
                                                <input type="hidden" id="supplier_id">
                                                <select class="form-control editInput selectOptions" name="supplier_id" id="Supplier_input">
                                                    <option>Please Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="Date_input"> Date <span class="radStar">*</span></label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="date" id="Date_input">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="net_amount"> Net <span class="radStar">*</span></label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="netAmount" id="net_amount" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');"
                                                
                                                >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="gross_amount">Total Amount (to be paid)</label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="" id="totalAmount" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="vat_input">VAT <span class="radStar">*</span></label>
                                            <div>
                                                <input type="hidden" id="tax_id">
                                                <select class="form-control editInput selectOptions vat_input" name="Vat" id="vat_input">
                                                    <option>-Not Assigned-</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="vat_amount">VAT Amount <span class="radStar">*</span></label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="vatAmount" id="vat_amount" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gross_amount">Gross <span class="radStar">*</span></label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="grossAmount" id="gross_amount" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="reclaim_amount"> Reclaim </label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="reclaim" id="reclaim_amount" placeholder="" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="not_claim"> Not Claim </label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="not_reclaim" id="not_claim" placeholder="" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="expenses"> Expenses <span class="radStar">*</span></label>
                                            <div>
                                                <input type="hidden" id="expenses_id">
                                                <select class="form-control editInput selectOptions" name="expense_type" id="expenses">
                                                    <option>Please Select</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="rate_input">Expense Amount</label>
                                            <div>
                                                <input type="text" class="form-control editInput" name="expense_amount" id="expenses_amount" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End row -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="savePurchaseDayBook">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end here -->
<script>
    const purchaseDayBook = "{{ url('/purchase/purchase-day-book/delete/') }}";
    const getSuppliersList = "{{ url('/purchase/getSupplierData') }}";
    const getPurchaseExpenses = "{{ url('/purchase/getPurchaseExpense') }}";
    const getTaxRate = '{{ route("invoice.ajax.getActiveTaxRate") }}';
    const reclaimPercantage = "{{ url('/purchase/purchase-day-book-reclaim-per') }}";
    const calculatedData = "{{ url('/purchase/reclaimPercantage') }}";
    const savePurchaseDayBook = "{{ url('purchase/save-purchase-day-book') }}";
    const getPurchaseDayBook = "{{ url('purchase/purchase-daybook/data') }}";
</script>

<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/purchaseDayBook.js') }}"></script>
@endsection