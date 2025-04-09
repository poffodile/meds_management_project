@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Call Back Quotes</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                    <a href="#" class="profileDrop">Search Quotes</a>
                </div>
            </div>
        </div>

        @include('frontEnd.salesAndFinance.quote.quote_buttons')

        <di class="row">
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
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="productDetailTable pt-3">
                        <table id="containerA" class="table mb-0" cellspacing="0" width="100%">
                            <thead class="table-light">
                                <tr>
                                    <td></td>
                                    <th>#</th>
                                    <th>Quote Ref </th>
                                    <th>Quote Date</th>
                                    <th>Customer Name</th>
                                    <th>Site / Delivery</th>
                                    <th>No. Quotes </th>
                                    <th>Sub Total</th>
                                    <th>VAT</th>
                                    <th>Total </th>
                                    <th>Deposit </th>
                                    <th>Outstanding</th>
                                    <th>profit</th>
                                    <th>Call Back On</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>
                                @php
                                $subTotal = 0;
                                $vat = 0;
                                $total = 0;
                                $deposit = 0;
                                $outstanding = 0;
                                $profit = 0;
                                @endphp
                                @if(!empty($quotes))
                                @foreach($quotes as $value)
                                @php
                                $subTotal += $value->sub_total ?? 0;
                                $vat += $value->vat_amount ?? 0;
                                $total += $value->total ?? 0;
                                $deposit += $value->deposit ?? 0;
                                $outstanding += $value->outstanding ?? 0;
                                $profit += $value->profit ?? 0;
                                @endphp
                                <tr>
                                    <td></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->quote_ref ?? '-'}}</td>
                                    <td>{{ $value->quota_date }}</td>
                                    <td>{{ $value->customer->name ?? '' }}</td>
                                    <td>{{ $value->customer_address }}</td>
                                    <td>1</td>
                                    <td>&#163;{{ $value->sub_total ?? '0.00' }}</td>
                                    <td>&#163;{{ $value->vat_amount ?? '0.00'}}</td>
                                    <td>&#163;{{ $value->total ?? '0.00'}}</td>
                                    <td>&#163;{{ $value->deposit ??  '0.00'}}</td>
                                    <td>&#163;{{ $value->outstanding ?? '0.00' }}</td>
                                    <td>&#163;{{ $value->profit ?? '0.00' }}</td>
                                    <td>{{ $value->callBack_dateTime }}</td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">
                                                    Action
                                                </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="#" class="dropdown-item">Send SMS</a>
                                                    <a href="{{ url('/quote/edit').'/'.$value->id }}" class="dropdown-item">Edit</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Preview</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Print</a>
                                                    <a href="" class="dropdown-item">Email</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Convert To Recurring Quote </a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Convert To New Job</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Convert To Recurring Job</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Convert To Invoice</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="" class="dropdown-item">Change To Processed</a>
                                                    <!-- <a href="javascript:void(0)" onclick="openCallBackModal()" id="changeToCallBack" data-id = "{{ $value->id }}" data-quote_ref="{{ $value->quote_ref }}" class="dropdown-item">Change To Call Back</a> -->
                                                    <a href="" class="dropdown-item">Change To Accepted</a>
                                                    <a href="" class="dropdown-item">Change To Rejected</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="#" class="dropdown-item set_value_on_CRM_model" class="dropdown-item">CRM History</a>
                                                    <hr class="dropdown-divider">
                                                    <a href="#" class="dropdown-item">Start Timer</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @else
                                <tr>
                                    <td></td>
                                    <td colspan="14">
                                        <label class="red_sorryText"> Sorry, there are no items available.. </label>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th colspan="7">Page Sub Total</th>
                                    <th>&#163;{{ number_format($subTotal, 2) }}</th>
                                    <th>&#163;{{ number_format($vat, 2) }}</th>
                                    <th>&#163;{{ number_format($total, 2) }}</th>
                                    <th>&#163;{{ number_format($deposit, 2) }}</th>
                                    <th>&#163;{{ number_format($outstanding, 2) }}</th>
                                    <th>&#163;{{ number_format($profit, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>

@include('components.quote.call-back')

@include('frontEnd.salesAndFinance.jobs.layout.footer')
