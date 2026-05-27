@extends('frontEnd.layouts.master')
@section('title', 'Payroll Process')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="staffHeaderp">
                    <div>
                        <h1 class="mainTitlep">Payroll Processing</h1>
                        <p class="header-subtitle mb-0">Generate payslips and export to accounting software</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="bBorderCard mt20 p24">
                    <h5 class="h5Head"> Processing Steps:</h5>
                    <div class="row">
                        <div class="col-lg-8">
                            <ol class="orderListpayPro">
                                <li class="textGray fs13">Ensure all timesheets are approved for the period</li>
                                <li class="textGray fs13">Click "Process Payroll" for the desired period </li>
                                <li class="textGray fs13">System calculates gross pay, deductions, and net pay</li>
                                <li class="textGray fs13">Holiday accruals are automatically calculated and updated </li>
                                <li class="textGray fs13">NMW compliance is verified for all staff</li>
                                <li class="textGray fs13">Export to Sage/Xero for payment processing</li>
                            </ol>
                        </div>
                        <!-- <div class="col-lg-4 text-right">
                            <div class="p-3 muteBg rounded12 border">
                                <p class="textGray fs12 mb-1">Current Home</p>
                                <h6 class="mb-0">{{ Auth::user()->home->name ?? 'Default Home' }}</h6>
                            </div>
                        </div> -->
                    </div>
                </div>

                @if($payrollGroups->count() > 0)
                @foreach($payrollGroups as $group)
                <div class="bBorderCard greenBorderClr mt-4 p-0">
                    <div class="muteBg bottomRadUnset p24 rounded12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="h3Head">{{ $group['week_label'] }}</h3>
                                <div class="mb-2">
                                    <span class="careBadg {{ $group['status'] == 'processed' ? 'greenbadges' : 'bluebadges' }}">
                                        {{ ucfirst($group['status']) }}
                                    </span>
                                </div>
                                <p class="textGray fs13 mb-1">{{ $group['week_range'] }}</p>
                                <p class="text-primary fs12 mb-1">Included Categories: {{ $group['categories'] ?: 'None' }}</p>
                                <p class="h7Head textGray"> Expected Pay Date: {{ $group['pay_date'] }} </p>
                            </div>
                            <div class="text-right">
                                <p class="textGray fs13">Total Gross</p>
                                <h5 class="h5Bold greenText">£{{ number_format($group['total_gross'], 2) }}</h5>
                                <p class="textGray fs13">Est. Net Pay (Approx)</p>
                                <h5 class="h5Head">£{{ number_format($group['total_gross'] * 0.8, 2) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="p24">
                        <div class="row">
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Staff Members</p>
                                    <h5 class="h5Head">{{ $group['staff_count'] }}</h5>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Total Hours</p>
                                    <h5 class="h5Head">{{ number_format($group['total_hours'], 1) }}</h5>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Approved Shifts</p>
                                    <h5 class="h5Head greenText">{{ $group['timesheet_count'] }}</h5>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Pending</p>
                                    <h5 class="h5Head {{ $group['pending_hours'] > 0 ? 'text-danger' : '' }}">
                                        {{ number_format($group['pending_hours'], 1) }}
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Staff Breakdown Section -->
                        <div class="mt-4 pt-3 border-top">
                            <button class="btn btn-link btn-sm p-0 text-primary d-flex align-items-center" type="button" data-toggle="collapse" data-target="#staff-{{ $loop->index }}">
                                <i class="bx bx-chevron-down fs-20 me-1"></i> View Staff Data for this week ({{ $group['staff_count'] }})
                            </button>
                            <div class="collapse mt-3" id="staff-{{ $loop->index }}">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover border mb-0" style="font-size: 13px;">
                                        <thead class="muteBg">
                                            <tr>
                                                <th class="p-2">Staff Name</th>
                                                <th class="p-2 text-center">Total Hours</th>
                                                <th class="p-2 text-right">Gross Pay</th>
                                                <th class="p-2">Categories Worked</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['staff_breakdown'] as $staff)
                                            <tr>
                                                <td class="p-2 font-weight-bold">{{ $staff['name'] }}</td>
                                                <td class="p-2 text-center">{{ $staff['hours'] }} hrs</td>
                                                <td class="p-2 text-right text-success font-weight-bold">£{{ $staff['gross'] }}</td>
                                                <td class="p-2"><span class="textGray fs12">{{ $staff['categories'] }}</span></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex gap-3">
                                @if($group['status'] == 'processed')
                                <div>
                                    <button class="borderBtn"> <i class="bx bx-arrow-to-bottom-stroke f18 me-3"></i>Export to CSV</button>
                                </div>
                                <div>
                                    <a href="{{ url('roster/payroll-report/' . $group['week_key']) }}" target="_blank" class="borderBtn"> 
                                        <i class="bx bx-file-detail f18 me-3"></i>View Payslips
                                    </a>
                                </div>
                                @else
                                <div>
                                    <button class="bgBtn pgreenBtn process-payroll-btn"
                                        data-week="{{ $group['week_key'] }}">
                                        <i class="bx bx-play-circle me-3 f20"></i> Process Payroll
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="bBorderCard mt-4 p24 text-center">
                    <i class="bx bx-info-circle f48 mb-3 text-muted"></i>
                    <h5 class="h5Head">No approved timesheets found.</h5>
                    <p class="textGray fs13">Please approve shifts in the Timesheet Reconciliation dashboard to see them here.</p>
                    <a href="{{ route('roster.payroll.finance.reconciliation') }}" class="bgBtn pgreenBtn mt-3" style="display:inline-block">Go to Reconciliation</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        $('.process-payroll-btn').on('click', function() {
            var weekStart = $(this).data('week');
            var btn = $(this);

            if (!confirm('Are you sure you want to process payroll for this week?')) return;

            btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-3 f20"></i> Processing...');

            $.ajax({
                url: "{{ route('payroll.process.week') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    week_start: weekStart
                },
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error('Something went wrong');
                        btn.prop('disabled', false).html('<i class="bx bx-play-circle me-3 f20"></i> Process Payroll');
                    }
                },
                error: function() {
                    toastr.error('Server error occurred');
                    btn.prop('disabled', false).html('<i class="bx bx-play-circle me-3 f20"></i> Process Payroll');
                }
            });
        });
    });
</script>
@endsection