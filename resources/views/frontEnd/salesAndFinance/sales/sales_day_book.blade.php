@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Sales Day Book')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-body">
                        <div class="col-md-4 col-lg-4 col-xl-4 ">
                            <div class="pageTitle">
                                <h3>Sales Day Book</h3>
                            </div>
                        </div>
                        <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                            <div class="pageTitleBtn">
                                <a href="#" class="profileDrop">Search Book</a>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="maimTable">
                                <div class="printExpt">
                                    <div class="prntExpbtn">
                                        <a href="#!">Print</a>
                                        <a href="#!">Export</a>
                                    </div>
                                    <div class="searchFilter">
                                        <a href="#!">Show Search Filter</a>
                                    </div>
                                </div>
                                <div class="markendDelete">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="jobsection">
                                                <a href="{{ url('/sales/sales-day-book/add') }}" class="profileDrop">Add</a>
                                            </div>
                                        </div>
                                        <!-- <div class="col-md-5">
                                            <div class="pageTitleBtn p-0">
                                                <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>

                                <div class="productDetailTable pt-3">
                                    <table class="table tablechange mb-0" id="containerA">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Customer </th>
                                                <th>Date</th>
                                                <th>Invoice No.</th>
                                                <th>Net</th>
                                                <th>VAT</th>
                                                <th>Gross </th>
                                                <th>Rate </th>
                                                <th>Total </th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $totalNetAmount = 0;
                                            $totalVatAmount = 0;
                                            $totalGrossAmount = 0;
                                            $totalFinalAmount = 0;
                                            @endphp

                                            @foreach($salesDayBooks as $salesBook)

                                            @php
                                            $netAmount = $salesBook->netAmount ?? 0;
                                            $vatAmount = $salesBook->vatAmount ?? 0;
                                            $grossAmount = $salesBook->grossAmount ?? 0;
                                            $finalAmount = $netAmount + $vatAmount;

                                            $totalNetAmount += $netAmount;
                                            $totalVatAmount += $vatAmount;
                                            $totalGrossAmount += $grossAmount;
                                            $totalFinalAmount += $finalAmount;
                                            @endphp

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $salesBook->customer_name }}</td>
                                                <td>{{ $salesBook->date }}</td>
                                                <td>{{ $salesBook->invoice_no }}</td>
                                                <td>{{ $salesBook->netAmount }}</td>
                                                <td>{{ $salesBook->vatAmount }}</td>
                                                <td>{{ $salesBook->grossAmount }}</td>
                                                <td>{{ $salesBook->tax_rate_name }}</td>
                                                <td>{{ $salesBook->netAmount + $salesBook->vatAmount }}</td>
                                                <td>
                                                    <!-- <div class="d-flex justify-content-end actionDropdown">
                                                            <div class="nav-item dropdown">
                                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">Action</a>
                                                                <div class="dropdown-menu fade-up m-0">
                                                                    <a href="{{ url('sales/sales-day-book/edit/' . $salesBook->id) }}" class="dropdown-item">Edit</a>
                                                                    <a href="#!" class="dropdown-item deleteBtn" data-id="{{ $salesBook->id }}">Delete</a>
                                                                </div>
                                                            </div>
                                                        </div> -->
                                                    <div class="dropdown action_dropdown">
                                                        <a href="#" class="dropdown-toggle profileDrop" data-toggle="dropdown" aria-expanded="false">
                                                            <div class="action_drop">
                                                                <span>Action &nbsp;</span> <i class="fa fa-sort-desc" aria-hidden="true"></i>
                                                            </div>
                                                        </a>
                                                        <div class="dropdown-menu">
                                                            <a href="{{ url('sales/sales-day-book/edit/' . $salesBook->id) }}" class="dropdown-item ">Edit</a>
                                                            <a href="#!" class="dropdown-item deleteBtn" data-id="{{ $salesBook->id }}">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" rowspan="1">Page Sub Total</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalNetAmount, 2) }}</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalVatAmount, 2) }}</th>
                                                <th rowspan="1" colspan="2">£{{ number_format($totalGrossAmount, 2) }}</th>
                                                <th rowspan="1" colspan="2">£{{ number_format($totalFinalAmount, 2) }}</th>
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
    </div>
</section>
<script>
    const salesDayBook = "{{ url('/sales/sales-day-book/delete/') }}";
</script>

@endsection
<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/salesDayBook.js') }}"></script>