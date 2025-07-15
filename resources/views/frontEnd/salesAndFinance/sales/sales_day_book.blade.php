@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Sales Day Book')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')


<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Sales Day Book</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="jobsection justify-content-end">
                                <a href="#!" type="button" class="btn btn-warning openSalesDayBookModel" data-action="add"><i class="fa fa-plus"></i> Add</a>
                                <div>
                                    <select name="" class="form-control editInput selectOptions" id="getDataOnTax">
                                        <option value="0">Please Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange" id="salesDayBookTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Customer </th>
                                            <th>Date</th>
                                            <th>Invoice No.</th>
                                            <th>Net</th>
                                            <th>VAT</th>
                                            <th>Gross </th>
                                            <th>Rate </th>
                                            <!-- <th>Total </th> -->
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th style="text-align:right">Total:</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- End off main Table -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sales Day book Modal start here -->
<div class="modal fade" id="salesDayBookModel" tabindex="-1" aria-labelledby="add_sales_day_bookLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle"></h4>
            </div>
            <form id="salesDayBookForm">
                <div id="error-div"></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <input type="hidden" name="sales_day_book_id" id="sales_day_book_id">
                                <label> Customer <span class="radStar">*</span></label>
                                <input type="hidden" id="customer_id">
                                <select class="form-control editInput selectOptions" name="customer_id" id="getCustomerList">
                                    <option>Select Customer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label> Date <span class="radStar">*</span></label>
                                <input type="text" class="form-control " name="date" id="Date_input">
                            </div>
                            <div class="form-group">
                                <label> Invoice <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" name="invoice_no" id="Invoice_input" placeholder="Invoice no.">
                            </div>
                            <div class="form-group">
                                <label> Net <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" id="net_amount" name="netAmount" placeholder="" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label>VAT <span class="radStar">*</span></label>
                                <input type="hidden" id="tax_id">
                                <select class="form-control editInput selectOptions vat_input" name="Vat" id="vat_input">
                                    <option>-Not Assigned-</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Vat Amount <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" name="vatAmount" id="vat_amount" placeholder="" readonly>
                            </div>
                            <div class="form-group">
                                <label>Gross <span class="radStar">*</span></label>
                                <input type="text" class="form-control editInput" name="grossAmount" id="gross_amount" placeholder="" readonly>
                            </div>
                        </div>
                        <!-- End row -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="saveSalesDayBookModal">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- end here -->

<script>
    const salesDayBook = "{{ url('/sales/sales-day-book/delete/') }}";
    const customerList = '{{ route("customer.ajax.getCustomerList") }}';
    const getTaxRate = '{{ route("invoice.ajax.getActiveTaxRate") }}';
    const saveSalesDayBook = "{{ url('sales/save-sales-day-book') }}";
    const editSalesDayBook = "{{ url('sales/edit-sales-day-book') }}";
    const getSalesDayBook = "{{ url('sales/get-sales-day-book/data') }}";
</script>

<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/salesDayBook.js') }}"></script>
@endsection