@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Purchase Day Book')
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
                                <h3>Purchase Day Book</h3>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="maimTable">
                                <div class="printExpt">
                                    <div class="prntExpbtn">
                                        <a href="#!">Print</a>
                                        <a href="#!">Export</a>
                                    </div>
                                </div>
                                <div class="markendDelete">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="jobsection">
                                                <a href="{{ url('purchase/purchase-day-book/add') }}" class="profileDrop">Add</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="productDetailTable pt-3">
                                    <table class="table tablechange mb-0" id="containerA">
                                        <thead class="table-light">
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
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $totalNetAmount = 0;
                                            $totalVatAmount = 0;
                                            $totalGrossAmount = 0;
                                            $totalFinalAmount = 0;
                                            $totalReclaim = 0;
                                            $totalNotReclaim = 0;
                                            $totalExpense = 0;
                                            @endphp

                                            @foreach($purchaseDayBook as $purchaseBook)

                                            @php
                                            $netAmount = $purchaseBook->netAmount ?? 0;
                                            $vatAmount = $purchaseBook->vatAmount ?? 0;
                                            $grossAmount = $purchaseBook->grossAmount ?? 0;
                                            $reclaim = $purchaseBook->reclaim;
                                            $not_reclaim = $purchaseBook->not_reclaim;
                                            $expense_amount = $purchaseBook->expense_amount;
                                            $finalAmount = $netAmount + $vatAmount;

                                            $totalNetAmount += $netAmount;
                                            $totalVatAmount += $vatAmount;
                                            $totalGrossAmount += $grossAmount;
                                            $totalFinalAmount += $finalAmount;
                                            $totalReclaim += $reclaim;
                                            $totalNotReclaim += $not_reclaim;
                                            $totalExpense += $expense_amount;
                                            @endphp

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $purchaseBook->customer_name }}</td>
                                                <td>{{ $purchaseBook->date }}</td>
                                                <td>{{ $purchaseBook->netAmount }}</td>
                                                <td>{{ $purchaseBook->vatAmount }}</td>
                                                <td>{{ '£'.$purchaseBook->grossAmount ?? '' }}</td>
                                                <td>{{ $purchaseBook->tax_rate_name }}</td>
                                                <td>£{{ ($purchaseBook->netAmount + $purchaseBook->vatAmount) - $purchaseBook->reclaim    }}</td>
                                                <td>{{ $purchaseBook->reclaim ? '£' . $purchaseBook->reclaim : '' }}</td>
                                                <td>{{ $purchaseBook->not_reclaim }}</td>
                                                <td>{{ $purchaseBook->title }}</td>
                                                <td>{{ $purchaseBook->expense_amount ? '£' . $purchaseBook->expense_amount : '' }}</td>
                                                <td>
                                                    <!-- <div class="d-flex justify-content-end actionDropdown">
                                                        <div class="nav-item dropdown">
                                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">Action</a>
                                                            <div class="dropdown-menu fade-up m-0">
                                                                <a href="{{ url('purchase/purchase-day-book/edit/' . $purchaseBook->id) }}" class="dropdown-item">Edit</a>
                                                                <a href="#!" class="dropdown-item deleteBtn" data-id="{{ $purchaseBook->id }}">Delete</a>
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
                                                            <a href="{{ url('purchase/purchase-day-book/edit/' . $purchaseBook->id) }}" class="dropdown-item ">Edit</a>
                                                            <a href="#!" class="dropdown-item deleteBtn" data-id="{{ $purchaseBook->id }}">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" rowspan="1">Page Sub Total</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalNetAmount, 2) }}</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalVatAmount, 2) }}</th>
                                                <th rowspan="1" colspan="2">£{{ number_format($totalGrossAmount, 2) }}</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalFinalAmount, 2) }}</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalReclaim, 2) }}</th>
                                                <th rowspan="1" colspan="2">£{{ number_format($totalNotReclaim, 2) }}</th>
                                                <th rowspan="1" colspan="1">£{{ number_format($totalExpense, 2) }}</th>
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
    </div>
</section>
<script>
    const salesDayBook = "{{ url('/purchase/purchase-day-book/delete/') }}";
</script>

@endsection
<script type="text/javascript" src="{{ url('public/js/salesFinance/dayBook/purchaseDayBook.js') }}"></script>