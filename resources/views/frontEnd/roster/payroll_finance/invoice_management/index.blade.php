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
                        <div class="d-flex gap-3">
                            <button class="borderBtn" data-toggle="modal" data-target="#bulkGenerateModal"><i class="bx  bx-sync me-2"></i>Bulk Generate</button>
                            <button class="bgBtn" data-toggle="modal" data-target="#createInvoiceModal"><i class="bx  bx-plus me-2"></i>Create Invoice</button>
                        </div>
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
            <div class="col-lg-12 invoice-card" data-status="{{ $invoice->status }}" data-ref="{{ $invoice->invoice_ref }}" data-client="{{ optional($invoice->serviceUser)->name ?? '' }}">
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
                                {{ optional($invoice->serviceUser)->name ?? 'Unknown Payer' }}
                            </h6>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="borderBtn view-invoice" data-id="{{ $invoice->id }}">
                                <i class="bx bx-show me-2 f18"></i> View
                            </button>
                            @if($invoice->status == 'Draft')
                            <button class="borderBtn regenerate-invoice" data-id="{{ $invoice->id }}" title="Recalculate from billing settings">
                                <i class="bx bx-refresh me-2 f18"></i> Recalculate
                            </button>
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
                            <div class="lockBadge">
                                <i class="bx bx-lock-alt me-1"></i> Locked
                            </div>
                            @if($invoice->status != 'Paid')
                            <button class="bgBtn pgreenBtn mark-status" data-id="{{ $invoice->id }}" data-status="Paid">
                                <i class="bx bx-check-circle me-2 f18"></i> Mark Paid
                            </button>
                            <button type="button" class="borderBtn openPdfModal" data-id="{{$invoice->id}}" title="Download PDF">
                                <i class='bx bx-download me-2 f18'></i> PDF
                            </button>
                            @endif
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

<!-- Bulk Generate Modal -->
<div class="modal fade" id="bulkGenerateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content premium-modal">
            <form id="bulkGenerateForm">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="h5Head modTitle">Bulk Generate Invoices</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <p class="textGray fs13 mb-4">This will automatically generate draft invoices for clients who have completed shifts in the given period.</p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="textGray fs13 font600 mb-2">Start Date</label>
                            <input type="date" name="start_date" class="form-control premium-input" value="{{ now()->startOfWeek()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="textGray fs13 font600 mb-2">End Date</label>
                            <input type="date" name="end_date" class="form-control premium-input" value="{{ now()->endOfWeek()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-end gap-3">
                    <button type="button" class="borderBtn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="bgBtn"><i class="bx bx-play-circle me-1"></i> Generate for Completed Shifts</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content premium-modal">
            <div class="modal-header border-0 pb-0">
                <h5 class="h5Head modTitle">Invoice Details - <span id="view_inv_ref"></span></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table custom-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody id="viewInvoiceItems">
                            <!-- Items will be loaded here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-right font-weight-bold">Sub Total</td>
                                <td class="text-right" id="view_inv_subtotal"></td>
                            </tr>
                            <tr>
                                <td class="text-right font-weight-bold">VAT (20%)</td>
                                <td class="text-right" id="view_inv_vat"></td>
                            </tr>
                            <tr class="f18" style="color: #0369a1;">
                                <td class="text-right font-weight-bold">Total Amount</td>
                                <td class="text-right font-weight-bold" id="view_inv_total"></td>
                            </tr>
                        </tfoot>
                        <tbody id="fundingRows" style="border-top: none !important;">
                            <!-- Funding deductions here -->
                        </tbody>
                        <tfoot>
                            <tr class="f18" style="color: #15803d;">
                                <td class="text-right font-weight-bold">Authority Amount</td>
                                <td class="text-right font-weight-bold" id="view_inv_authority"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="bgBtn" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // View Invoice Details
        $(document).on('click', '.view-invoice', function() {
            var id = $(this).data('id');
            $('#viewInvoiceItems').html('<tr><td colspan="2" class="text-center"><i class="bx bx-loader-alt bx-spin"></i> Loading...</td></tr>');
            $('#viewInvoiceModal').modal('show');

            $.ajax({
                url: "{{ route('roster.invoice.get-details') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        $('#view_inv_ref').text(response.invoice.invoice_ref);
                        $('#view_inv_subtotal').text('£' + parseFloat(response.invoice.sub_total || 0).toFixed(2));
                        $('#view_inv_vat').text('£' + parseFloat(response.invoice.VAT_amount || 0).toFixed(2));
                        $('#view_inv_total').text('£' + parseFloat(response.invoice.Total).toFixed(2));

                        var html = '';
                        var careAmount = 0;
                        response.products.forEach(function(item) {
                            html += `<tr>
                                <td>${item.description}</td>
                                <td class="text-right">£${parseFloat(item.price).toFixed(2)}</td>
                            </tr>`;
                            
                            // Check if this is a Care Service line to calculate Authority Amount
                            if (item.description.toLowerCase().includes('care services')) {
                                careAmount += parseFloat(item.price);
                            }
                        });
                        $('#viewInvoiceItems').html(html);

                        // Authority Amount Calculation
                        var authorityAmount = careAmount * 1.20;
                        var fundingHtml = '';

                        if (response.onboardingDetails && response.onboardingDetails.length > 0) {
                            response.onboardingDetails.forEach(function(detail) {
                                var deduction = 0;
                                var val = parseFloat(detail.vat || 0);

                                if (detail.type == 1) { // Percentage
                                    deduction = authorityAmount * (val / 100);
                                    fundingHtml += `<tr>
                                        <td class="text-right text-muted italic fs13">Less: ${detail.name} (${val}%)</td>
                                        <td class="text-right text-muted fs13">-£${deduction.toFixed(2)}</td>
                                    </tr>`;
                                } else { // Amount
                                    deduction = val;
                                    fundingHtml += `<tr>
                                        <td class="text-right text-muted italic fs13">Less: ${detail.name}</td>
                                        <td class="text-right text-muted fs13">-£${deduction.toFixed(2)}</td>
                                    </tr>`;
                                }
                                authorityAmount -= deduction;
                            });
                        }
                        
                        $('#fundingRows').html(fundingHtml);
                        $('#view_inv_authority').text('£' + authorityAmount.toFixed(2));
                    } else {
                        toastr.error('Failed to load invoice details.');
                    }
                }
            });
        });

        $(document).on('click', '.openPdfModal', function() {
            var invoiceId = $(this).data('id');
            $('#pdf_invoice_id').val(invoiceId);
            $('#pdfFundingOptions').empty();
            
            // Fetch invoice details to get funding options
            $.ajax({
                type: "GET",
                url: "{{route('roster.invoice.get-details')}}",
                data: { id: invoiceId },
                success: function(response) {
                    if (response.success) {
                        if (response.onboardingDetails && response.onboardingDetails.length > 0) {
                            response.onboardingDetails.forEach(function(detail, index) {
                                var html = `
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="pdf_funding_${detail.id}" name="pdf_type" value="${detail.id}" class="custom-control-input">
                                        <label class="custom-control-label" for="pdf_funding_${detail.id}">${detail.name}</label>
                                    </div>`;
                                $('#pdfFundingOptions').append(html);
                            });
                        } else {
                            $('#pdfFundingOptions').html('<p class="text-muted italic small">No funding sources available for this invoice.</p>');
                        }
                        $('#downloadPdfModal').modal('show');
                    } else {
                        toastr.error('Failed to fetch invoice details.');
                    }
                }
            });
        });

        $(document).on('click', '#confirmPdfDownload', function() {
            var invoiceId = $('#pdf_invoice_id').val();
            var pdfType = $('input[name="pdf_type"]:checked').val();
            
            // Build the URL correctly using the named route
            var baseUrl = "{{ route('roster.invoice.download-pdf', ['id' => ':id']) }}";
            var url = baseUrl.replace(':id', invoiceId) + "?type=" + pdfType;
            
            window.location.href = url;
            $('#downloadPdfModal').modal('hide');
        });
        // Bulk Generate
        $('#bulkGenerateForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $(this).find('button[type="submit"]');
            var originalText = btn.html();
            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Running...');

            $.ajax({
                url: "{{ route('roster.invoice.bulk-generate') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    toastr.error('Something went wrong. Please try again.');
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
        // Create Invoice
        $('select[name="client_id"]').on('change', function() {
            var clientId = $(this).val();
            if (clientId) {
                $.ajax({
                    url: "{{ route('roster.invoice.get-billing-info') }}",
                    type: "GET",
                    data: {
                        id: clientId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('select[name="period_type"]').val(response.billing_frequency);
                        }
                    }
                });
            }
        });

        $('#createInvoiceForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: "{{ route('roster.invoice.create') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
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
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        // Regenerate Invoice Amount/Breakdown
        $(document).on('click', '.regenerate-invoice', function() {
            var id = $(this).data('id');
            if (confirm('Recalculate this invoice based on latest billing rates and service charges?')) {
                $.ajax({
                    url: "{{ route('roster.invoice.regenerate') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            }
        });

        // Update Status
        $(document).on('click', '.mark-status', function() {
            var id = $(this).data('id');
            var status = $(this).data('status');
            if (confirm('Are you sure you want to change status to ' + status + '?')) {
                $.ajax({
                    url: "{{ route('roster.invoice.update-status') }}",
                    type: "POST",
                    data: {
                        id: id,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
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

                    if (matchSearch && matchStatus) {
                        $(this).fadeIn(200);
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (visibleCount === 0) {
                    if ($('#no-results').length === 0) {
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
    .purpleBadges {
        background: #f3e8ff;
        color: #7e22ce;
    }

    .buleBadges {
        background: #e0f2fe;
        color: #0369a1;
    }

    .yellowBadges {
        background: #fefce8;
        color: #a16207;
    }

    .greenbadges {
        background: #f0fdf4;
        color: #15803d;
    }

    .redbadges {
        background: #fef2f2;
        color: #b91c1c;
    }

    .lockBadge {
        background: #f8fafc;
        color: #64748b;
        padding: 0 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        border: 1.5px solid #e2e8f0;
        height: 42px;
        transition: all 0.3s ease;
    }

    .lockBadge i {
        font-size: 18px;
    }

    .careBadg {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .caret-locked {
        opacity: 0.6;
        pointer-events: none;
    }

    .bBorderCard {
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .bBorderCard:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        border-color: #e2e8f0;
    }

    .bBorderCard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.3s ease;
    }

    .bBorderCard[data-status="Paid"]::before {
        background: #22c55e;
    }

    .bBorderCard[data-status="Draft"]::before {
        background: #a855f7;
    }

    .bBorderCard[data-status="Outstanding"]::before {
        background: #eab308;
    }

    .bBorderCard[data-status="Invoiced"]::before {
        background: #3b82f6;
    }

    .h5Head {
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    .h6Head {
        font-weight: 600;
        color: #334155;
    }

    .searchWithtabs {
        background: #fff;
        border: 1.5px solid #eef2f7;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .searchWithtabs:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.05);
    }

    .searchWithtabs .form-control {
        border: none;
        padding-left: 10px;
    }

    .searchWithtabs .input-group-addon {
        background: transparent;
        border: none;
        padding-left: 15px;
        color: #94a3b8;
    }

    .premium-modal {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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

    <!-- Download PDF Selection Modal -->
    <div class="modal fade" id="downloadPdfModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Download Invoice PDF</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="font-weight-bold">Select invoice recipient:</p>
                    <input type="hidden" id="pdf_invoice_id">
                    <div id="pdfOptionsContainer">
                        <div class="custom-control custom-radio mb-3">
                            <input type="radio" id="pdf_self" name="pdf_type" value="self" class="custom-control-input" checked>
                            <label class="custom-control-label font-weight-bold" for="pdf_self">Self (Client Remainder)</label>
                            <small class="d-block text-muted">Weekly Care Services + Expenses + VAT (Less Deductions)</small>
                        </div>
                        <hr>
                        <p class="text-muted small mb-2">Or select a Funding Source:</p>
                        <div id="pdfFundingOptions">
                            <!-- Funding options will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmPdfDownload">
                        <i class='bx bx-download'></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection