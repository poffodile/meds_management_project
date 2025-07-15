@extends('backEnd.layouts.master')
@section('title',' Purchase Day Book')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Purchase Day Book
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="col-lg-3 col-sm-3">
                                    <input type="hidden" id="tax_id">
                                    <select class="form-control" name="tax_rate" id="getDataOnTax">
                                        <option value="0">Please Select</option>
                                    </select>
                                </div>
                                <div class="btn-group">
                                    <a href="{{url('admin/sales-finance/purchase/purchase-day-book-add')}}" id="editable-sample_new" class="btn btn-primary"> Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <table class="display table table-bordered table-striped" id="purchaseDayBookTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Supplier</th>
                                        <th>Date</th>
                                        <th>Net</th>
                                        <th>VAT</th>
                                        <th>Gross</th>
                                        <th>Rate</th>
                                        <th>Total</th>
                                        <th>Reclaim</th>
                                        <th>Not Reclaim</th>
                                        <th>Expense Type</th>
                                        <th>Expense Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
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
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>

<script>
    const getTaxRate = '{{ url("admin/sales-finance/sales/getTaxRate") }}';
    const getPurchaseDayBook = "{{ url('admin/sales-finance/purchase/purchase-daybook/data') }}";
    const purchaseDayBook = "{{ url('admin/sales-finance/purchase/purchase-day-book-delete/') }}";

    $(document).on('click', '.openPurchaseDayBookModel', function() {
        const mode = $(this).data('mode');
        const id = $(this).data('id');

        window.location.href = '{{ url("admin/sales-finance/purchase/purchase-day-book-edit/") }}' + "/" + id;
    });
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/purchaseDayBook.js') }}"></script>
@endsection