@extends('frontEnd.layouts.master')
@section('title', 'Client Invoicing')
@section('content')

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="staffHeaderp">
                    <div>
                        <h1 class="mainTitlep">Client Invoicing</h1>
                        <p class="header-subtitle mb-0">Manage client invoices and track payments</p>
                    </div>
                    <div>
                        <button class="bgBtn" data-toggle="modal" data-target="#createInvoiceModal"><i class="bx  bx-plus me-2"></i>Create Invoice</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-3">
                <div class="rota_dash-card gradp-blue-50 p-4 lightBorderp rouded8">
                    <div class="rota_dash-left w100">
                        <div class="mb-3">
                            <i class="bx bx-file-detail fs30 textBlue"></i>
                        </div>
                        <p class="fs13 textBlue">Total Invoiced</p>
                        <h1 class="h1Pay700 darkBlueTextp mt-0">£{{ number_format($totalInvoiced, 2) }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="rota_dash-card gradp-orange-50 p-4 lightBorderp rouded8">
                    <div class="rota_dash-left w100">
                        <div class="mb-3">
                            <i class="bx bx-dollar fs30 orangeText"></i>
                        </div>
                        <p class="fs13 orangeText">Outstanding</p>
                        <h1 class="h1Pay700 darkOrangeTextp mt-0">£{{ number_format($outstanding, 2) }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="rota_dash-card gradp-green-50 p-4 lightBorderp rouded8">
                    <div class="rota_dash-left w100">
                        <div class="mb-3">
                            <i class="bx bx-check-circle fs30 greenText"></i>
                        </div>
                        <p class="fs13 greenText">Paid</p>
                        <h1 class="h1Pay700 darkGreenTextp mt-0">£{{ number_format($paid, 2) }}</h1>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="rota_dash-card gradp-red-50 p-4 lightBorderp rouded8">
                    <div class="rota_dash-left w100">
                        <div class="mb-3">
                            <i class="bx bx-alert-triangle fs30 redtext"></i>
                        </div>
                        <p class="fs13 redtext">Overdue</p>
                        <h1 class="h1Pay700 textRedp mt-0">{{ $overdueCount }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt20">
            <div class="col-lg-12">
                <div class="emergencyMain p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-4">
                            <div class="input-group searchWithtabs" style="width:100%">
                                <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                <input type="text" id="invoiceSearch" class="form-control" placeholder="Search by client or invoice number...">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <select class="form-control" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Invoiced" {{ request('status') == 'Invoiced' ? 'selected' : '' }}>Invoiced</option>
                                <option value="Outstanding" {{ request('status') == 'Outstanding' ? 'selected' : '' }}>Outstanding</option>
                                <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="invoiceList">
            @forelse($invoices as $invoice)
            <div class="col-lg-12 invoice-card" data-status="{{ $invoice->status }}" data-ref="{{ $invoice->invoice_ref }}" data-client="{{ optional($invoice->customers)->name ?? '' }}">
                <div class="bBorderCard mt-4 p24">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex gap-3 mb-3">
                                <h5 class="h5Head">{{ $invoice->invoice_ref }}</h5>
                                <div>
                                    @php
                                        $badgeClass = [
                                            'Draft' => 'purpleBadges',
                                            'Invoiced' => 'buleBadges',
                                            'Outstanding' => 'yellowBadges',
                                            'Paid' => 'greenbadges',
                                            'Cancelled' => 'redbadges'
                                        ][$invoice->status] ?? 'graybadges';
                                    @endphp
                                    <span class="careBadg {{ $badgeClass }}">{{ $invoice->status }}</span>
                                </div>
                            </div>
                            <h6 class="h6Head textGray mb-0">
                                {{ optional($invoice->customers)->name ?? 'Unknown Payer' }}
                            </h6>
                        </div>
                        <div class="d-flex gap-2">
                            @if($invoice->status == 'Draft')
                                <button class="borderBtn edit-invoice" 
                                    data-id="{{ $invoice->id }}" 
                                    data-date="{{ $invoice->invoice_date }}" 
                                    data-due="{{ $invoice->due_date }}"
                                    data-toggle="modal" data-target="#editInvoiceModal">
                                    <i class="bx bx-edit me-2 f18"></i> Edit
                                </button>
                                <button class="bgBtn blackBtn mark-status" data-id="{{ $invoice->id }}" data-status="Invoiced">
                                    <i class="bx bx-send me-2 f18"></i> Process
                                </button>
                            @else
                                <span class="textGray fs12"><i class="bx bx-lock-alt me-1"></i> Locked</span>
                                @if($invoice->status != 'Paid')
                                    <button class="bgBtn pgreenBtn mark-status" data-id="{{ $invoice->id }}" data-status="Paid">
                                        <i class="bx bx-check-circle me-2 f18"></i> Mark Paid
                                    </button>
                                @endif
                                <a href="{{ route('roster.invoice.download-pdf', $invoice->id) }}" class="borderBtn" target="_blank">
                                    <i class="bx bx-arrow-to-bottom-stroke me-2 f18"></i> PDF
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-3">
                            <div>
                                <p class="textGray fs13">Invoice Date</p>
                                <h6 class="h6Head">{{ date('d M Y', strtotime($invoice->invoice_date)) }}</h6>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div>
                                <p class="textGray fs13">Due Date</p>
                                <h6 class="h6Head">{{ date('d M Y', strtotime($invoice->due_date)) }}</h6>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div>
                                <p class="textGray fs13">Total Amount</p>
                                <h6 class="h6Head">£{{ number_format($invoice->Total, 2) }}</h6>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div>
                                <p class="textGray fs13">Outstanding</p>
                                <h6 class="h6Head textRedp">£{{ number_format($invoice->outstanding, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-lg-12 text-center mt-5">
                <p class="textGray">No invoices found.</p>
            </div>
            @endforelse
        </div>
    </div>
</main>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content premium-modal">
            <form id="createInvoiceForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="h5Head modTitle">Create New Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="textGray fs13 font600 mb-2">Service User (Client)</label>
                            <select name="client_id" class="form-control premium-input" required>
                                <option value="">Select Service User</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 form-group mt-4">
                            <label class="textGray fs13 font600 mb-2">Billing Period Type</label>
                            <select name="period_type" class="form-control premium-input" required>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group mt-4">
                            <label class="textGray fs13 font600 mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control premium-input" required>
                        </div>
                        <div class="col-md-4 form-group mt-4">
                            <label class="textGray fs13 font600 mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control premium-input" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-3">
                    <button type="button" class="borderBtn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="bgBtn">Generate Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content premium-modal">
            <form id="editInvoiceForm">
                @csrf
                <input type="hidden" name="id" id="edit_invoice_id">
                <div class="modal-header border-0 pb-0">
                    <h5 class="h5Head modTitle">Edit Invoice Details</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-group">
                        <label class="textGray fs13 font600 mb-2">Invoice Date</label>
                        <input type="date" name="invoice_date" id="edit_invoice_date" class="form-control premium-input" required>
                    </div>
                    <div class="form-group mt-4">
                        <label class="textGray fs13 font600 mb-2">Due Date</label>
                        <input type="date" name="due_date" id="edit_due_date" class="form-control premium-input" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-3">
                    <button type="button" class="borderBtn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="bgBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Create Invoice
    $('#createInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: "{{ route('roster.invoice.create') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Edit Modal Data Pop
    $(document).on('click', '.edit-invoice', function() {
        $('#edit_invoice_id').val($(this).data('id'));
        $('#edit_invoice_date').val($(this).data('date'));
        $('#edit_due_date').val($(this).data('due'));
    });

    // Update Invoice
    $('#editInvoiceForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: "{{ route('roster.invoice.update') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(response.message);
                }
            }
        });
    });

    // Update Status
    $(document).on('click', '.mark-status', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');
        if(confirm('Are you sure you want to change status to ' + status + '?')) {
            $.ajax({
                url: "{{ route('roster.invoice.update-status') }}",
                type: "POST",
                data: {
                    id: id,
                    status: status,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });

    // Filters Logic
    var filterTimeout;
    $('#invoiceSearch, #statusFilter').on('keyup change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            var searchTerm = $('#invoiceSearch').val().toLowerCase();
            var statusTerm = $('#statusFilter').val();
            var visibleCount = 0;

            $('.invoice-card').each(function() {
                var ref = ($(this).data('ref') || "").toString().toLowerCase();
                var client = ($(this).data('client') || "").toString().toLowerCase();
                var status = ($(this).data('status') || "").toString();

                var matchSearch = ref.indexOf(searchTerm) > -1 || client.indexOf(searchTerm) > -1;
                var matchStatus = statusTerm === "" || status === statusTerm;

                if(matchSearch && matchStatus) {
                    $(this).fadeIn(200);
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0) {
                if($('#no-results').length === 0) {
                    $('#invoiceList').append('<div id="no-results" class="col-lg-12 text-center mt-5"><p class="textGray">No matching invoices found.</p></div>');
                }
            } else {
                $('#no-results').remove();
            }
        }, 150);
    });
});
</script>

<style>
.purpleBadges { background: #f3e8ff; color: #7e22ce; }
.buleBadges { background: #e0f2fe; color: #0369a1; }
.yellowBadges { background: #fefce8; color: #a16207; }
.greenbadges { background: #f0fdf4; color: #15803d; }
.redbadges { background: #fef2f2; color: #b91c1c; }
.caret-locked { opacity: 0.6; pointer-events: none; }

.premium-modal {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}
.modTitle {
    font-size: 1.25rem;
    color: #1a202c;
    padding: 1rem 0;
}
.premium-input {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.6rem 1rem;
    height: auto;
    font-size: 0.95rem;
    transition: all 0.2s;
}
.premium-input:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    outline: none;
}
.modal-dialog {
    margin-top: 10%;
}
.modal-header .close {
    padding: 1.5rem;
    margin: -1rem -1rem -1rem auto;
}
</style>

@endsection