@include('frontEnd.salesAndFinance.jobs.layout.header')



<style>
    .maimTable.draftTable .dataTables_wrapper .dataTables_length, .maimTable.draftTable .dataTables_info, .maimTable.draftTable .paging_simple_numbers {
    display: none;
}
tfoot.draftFoot tr th {
    font-size: 12px;
}
</style>


<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>{{ $text }} Quotes</h3>
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
                <div class="maimTable draftTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!" onclick="hideShowDiv()" class="hidebtn">Show Search Filter</a>
                        </div>
                    </div>
                    <div class="searchJobForm" id="divTohide">
                        <form action="" class="p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">User:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Job Ref:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Site Address:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Appointment Date:</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Customer:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Time:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Region:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Completed On:</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Project:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">App. Type:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Appt. Created Date:</label>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="Start">
                                        </div>

                                        <div class="col-md-4">
                                            <input type="date" class="form-control editInput" id="inputName" value="End">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Job Priority:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Job Type:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">App. Priority:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Created By User:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">App. Status: </label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                <option>--Any--</option>
                                                <option>Yes</option>
                                                <option>No</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-12">
                                    <div class="pageTitleBtn justify-content-center">
                                        <a href="#" class="profileDrop px-3">Search </a>
                                        <a href="#" class="profileDrop px-3">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--  <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>
                                </div>
                            </div>
                        </div>
                    </div> -->
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
                                    @php
                                        if ($lastSegment == "accepted"){
                                    @endphp
                                        <th>Status</th>
                                    @php
                                        }
                                    @endphp
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
                                <td>£{{ $value->sub_total ?? '0.00' }}</td>
                                <td>£{{ $value->vat_amount ?? '0.00'}}</td>
                                <td>£{{ $value->total ?? '0.00'}}</td>
                                <td>£{{ $value->deposit ??  '0.00'}}</td>
                                <td>£{{ $value->outstanding ?? '0.00' }}</td>
                                <td>£{{ $value->profit ?? '0.00' }}</td>
                                <td>
                                    <div class="d-inline-flex align-items-center ">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">Action</a>
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
                                                <a href="" class="dropdown-item">Change To Call Back</a>
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
                                <td colspan="12">
                                    <label class="red_sorryText"> Sorry, there are no items available.. </label>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td colspan="6">Page Sub Total</td>
                                <td>&#163;{{ number_format($subTotal, 2) }}</td>
                                <td>&#163;{{ number_format($vat, 2) }}</td>
                                <td>&#163;{{ number_format($total, 2) }}</td>
                                <td>&#163;{{ number_format($deposit, 2) }}</td>
                                <td>&#163;{{ number_format($outstanding, 2) }}</td>
                                <td>&#163;{{ number_format($profit, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div> <!-- End off main Table -->
            </div>
        </di>
    </div>
</section>

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="quote_reject_model" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title pupTitle">Change To Rejected</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div class="modal-body">
                <form role="form" id="rejectReasonsForm">
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">Quote Ref </label>
                        <div class="col-md-9">
                            <input type="hidden" value="" name="quote_id" id="setQuoteId">
                            <input type="hidden" name="quote_reject_reason_id" value="">
                            <input type="text" class="form-control-plaintext editInput" id = "setQuoteRef" value="" readonly>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Reject Type</label>
                        <div class="col-sm-6">
                            <select class="form-control editInput selectOptions" name="reject_type_id" id="quote_reject_type">
                                <option>Please Select</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <a href="javascript:void(0)" class="formicon" onclick="openRejectTypeModal('quote_reject_type')" ><i class="fa-solid fa-square-plus"></i></a>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Reject Reason <span class="radStar ">*</span></label>
                        <div class="col-md-9">
                            <textarea name="reject_reasons" class="form-control textareaInput" rows="4" placeholder="Enter the reject reason" id=""></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" onclick="saveQuoteRejectReasons();">Save</button>
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('divTohide').style.display = 'none'; // Hide content
    });

    function statusChange(id, status) {
        $.ajax({
            url: '{{ route("quote.ajax.statusChange") }}',
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('cont ent')
            },
            data: {
                quote_id: id,
                status: status
            },
            success: function(response) {
                console.log(response);
                if (response.data == true) {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function openRejectModal(quote_ref, quote_id) {

        document.getElementById('setQuoteRef').value = quote_ref;
        document.getElementById('setQuoteId').value = quote_id;
        
        getAllRejectTypeList(document.getElementById('quote_reject_type'));
        $('#quote_reject_model').modal('show');
    }

    function saveQuoteRejectReasons(){
        
        var formData = $('#rejectReasonsForm').serialize();
        console.log(formData);
        $.ajax({
            url: '{{ route("quote.ajax.saveQuoteRejectReasonsType") }}', // Define the URL route for saving
            method: 'Post',
            headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
            data: formData,
            success: function(response) {
                $('#quote_reject_model').modal('hide');
                // alert(response.data);
                window.location.reload();
            },
            error: function(error) {
                console.error('Error saving reject reasons:', error);
            }
        });
    }
</script>

@include('components.quote.call-back')
@include('components.quote.quote-reject-type')

@include('frontEnd.salesAndFinance.jobs.layout.footer')