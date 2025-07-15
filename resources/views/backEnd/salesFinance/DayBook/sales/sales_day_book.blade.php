@extends('backEnd.layouts.master')
@section('title',' Sales Day Book')
@section('content')

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Sales Day Book
                    </header>
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
                                    <a href="{{url('admin/sales-finance/sales/sales-day-book-add')}}" id="editable-sample_new" class="btn btn-primary">
                                        Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            <table class="display table table-bordered table-striped" id="salesDayBookTable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Invoice No.</th>
                                        <th>Net</th>
                                        <th>VAT</th>
                                        <th>Gross</th>
                                        <th>Rate</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
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
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>
<script>
    const salesDayBook = "{{ url('/admin/sales-finance/sales/sales-day-book-delete/') }}";
    const getSalesDayBook = "{{ url('admin/sales-finance/sales/daybook/data') }}";
    const getTaxRate = '{{ url("admin/sales-finance/sales/getTaxRate") }}';
    $(document).on('click', '.openSalesDayBookModel', function() {
        const mode = $(this).data('mode');
        const id = $(this).data('id');

        window.location.href = '{{ url("admin/sales-finance/sales/sales-day-book-edit/") }}' + "/" + id;
    });
</script>
<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/salesDayBook.js') }}"></script>

@endsection