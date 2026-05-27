<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report - {{ $group['week_label'] }}</title>
    <style type="text/css">
        *,
        ::before,
        ::after {
            box-sizing: border-box;
            border-width: 0;
            border-style: solid;
            border-color: #e5e7eb;
        }

        ::before,
        ::after {
            --tw-content: '';
        }

        html {
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -moz-tab-size: 4;
            tab-size: 4;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }

        body {
            margin: 0;
            line-height: inherit;
            background-color: #f8fafc;
        }

        table {
            text-indent: 0;
            border-color: inherit;
            border-collapse: collapse;
        }

        .w-full {
            width: 100%;
        }

        .border-collapse {
            border-collapse: collapse;
        }

        .whitespace-nowrap {
            white-space: nowrap;
        }

        .border-b {
            border-bottom-width: 1px;
        }

        .border-b-2 {
            border-bottom-width: 2px;
        }

        .border-r {
            border-right-width: 1px;
        }

        .border-main {
            border-color: #1c1c1c;
        }

        .bg-main {
            background-color: #1c1c1c;
        }

        .bg-slate-100 {
            background-color: #f1f5f9;
        }

        .p-3 {
            padding: 0.75rem;
        }

        .px-14 {
            padding-left: 3.5rem;
            padding-right: 3.5rem;
        }

        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .py-10 {
            padding-top: 2.5rem;
            padding-bottom: 2.5rem;
        }

        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
            width: 100%;
            max-width: 1000px;
            margin: 2rem auto;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
        }

        .no-print-btn {
            background: #1c1c1c;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .no-print-btn:hover {
            background: #333;
            transform: translateY(-1px);
        }

        .py-6 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }

        .pb-3 {
            padding-bottom: 0.75rem;
        }

        .pl-2 {
            padding-left: 0.5rem;
        }

        .pl-3 {
            padding-left: 0.75rem;
        }

        .pl-4 {
            padding-left: 1rem;
        }

        .pr-3 {
            padding-right: 0.75rem;
        }

        .pr-4 {
            padding-right: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .align-top {
            vertical-align: top;
        }

        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }

        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }

        .font-bold {
            font-weight: 700;
        }

        .text-main {
            color: #1c1c1c;
        }

        .text-neutral-600 {
            color: #525252;
        }

        .text-neutral-700 {
            color: #404040;
        }

        .text-slate-400 {
            color: #94a3b8;
        }

        .text-white {
            color: #fff;
        }

        .greenText {
            color: #10b981;
        }

        @media print {
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }

            .py-4 {
                margin: 0;
                box-shadow: none;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        @media screen and (max-width: 1000px) {
            .py-4 {
                margin: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .px-14 {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .py-10 {
                padding-top: 1.5rem;
                padding-bottom: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="py-4" id="payroll-content">
        <div class="no-print" style="padding: 10px 20px; display: flex; justify-content: flex-end; border-bottom: 1px solid #eee; margin-bottom: 10px; gap: 10px;">
            @php
            $path = trim(request()->path(), '/');
            $isApi = request()->is('api/*');
            $isPayslip = str_contains($path, 'staff-payslip');
            $segments = explode('/', $path);

            if ($isPayslip) {
            $staff_id = $segments[count($segments) - 2];
            $routeName = $isApi ? 'api.staff.payslip.download' : 'roster.staff.payslip.download';
            $downloadUrl = route($routeName, [$staff_id, $group['week_key']]);
            } else {
            $downloadUrl = route('roster.payroll.report.download', $group['week_key']);
            }
            @endphp

            <button type="button" onclick="window.print()" class="no-print-btn" style="background: #64748b;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z" />
                </svg>
                Print
            </button>

            <a href="{{ $downloadUrl }}" class="no-print-btn" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                </svg>
                Download PDF
            </a>
        </div>
        <div class="px-14 py-6">
            <table class="w-full border-collapse border-spacing-0">
                <tbody>
                    <tr>
                        <td class="w-full align-top">
                            <div>
                                <img src="{{ asset('public/images/ewm_logo.png') }}" style="height: 60px;" />
                            </div>
                        </td>
                        <td class="align-top">
                            <div class="text-sm">
                                <table class="border-collapse border-spacing-0">
                                    <tbody>
                                        <tr>
                                            <td class="border-r pr-4">
                                                <div>
                                                    <p class="whitespace-nowrap text-slate-400 text-right">Date</p>
                                                    <p class="whitespace-nowrap font-bold text-main text-right">{{ date('M d, Y') }}</p>
                                                </div>
                                            </td>
                                            <td class="pl-4">
                                                <div>
                                                    <p class="whitespace-nowrap text-slate-400 text-right">Payroll #</p>
                                                    <p class="whitespace-nowrap font-bold text-main text-right">PAY-{{ str_replace('-', '', $group['week_key']) }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-slate-100 px-14 py-6 text-sm">
            <table class="w-full border-collapse border-spacing-0">
                <tbody>
                    <tr>
                        <td class="w-1/2 align-top">
                            <div class="text-sm text-neutral-600">
                                <p class="font-bold fontSize-18">{{ $group['week_label'] }}</p>
                                <p style="color: #10b981; font-weight: 600;">Status: Processed</p>
                                <p>{{ $group['week_range'] }}</p>
                                <p><strong> Pay Date:</strong> {{ $group['pay_date'] }}</p>
                            </div>
                        </td>
                        <td class="w-1/2 align-top text-right">
                            <div class="text-sm text-neutral-600">
                                <p class="font-bold">Home: {{ $group['home_name'] ?? 'Social Care Solutions' }}</p>
                                <p>Report Generated by: {{ $group['generated_by'] ?? ($group['staff_breakdown'][0]['name'] ?? 'System') }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-14 py-10 text-sm text-neutral-700" style="overflow-x: auto;">
            <table class="w-full border-collapse border-spacing-0">
                <thead>
                    <tr>
                        <td class="border-b-2 border-main pb-3 pl-3 font-bold text-main text-center">#</td>
                        <td class="border-b-2 border-main pb-3 pl-2 font-bold text-main">Staff Member</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Hours</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Gross</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Tax (12%)</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">NI (8%)</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-center font-bold text-main">Pension</td>
                        <td class="border-b-2 border-main pb-3 pl-2 text-right font-bold text-main pr-3">Net Pay</td>
                    </tr>
                </thead>
                <tbody>
                    @php $totalNet = 0; $totalTax = 0; $totalNI = 0; $totalPension = 0; @endphp
                    @foreach($group['staff_breakdown'] as $index => $staff)
                    @php
                    $gross = floatval(str_replace(',', '', $staff['gross']));
                    $tax = $gross * 0.12;
                    $ni = $gross * 0.08;
                    $pension = 0; // Simplified
                    $net = $gross - $tax - $ni - $pension;

                    $totalNet += $net;
                    $totalTax += $tax;
                    $totalNI += $ni;
                    @endphp
                    <tr>
                        <td class="border-b py-3 pl-3 text-center">{{ $index + 1 }}.</td>
                        <td class="border-b py-3 pl-2 font-bold">{{ $staff['name'] }}</td>
                        <td class="border-b py-3 pl-2 text-center">{{ $staff['hours'] }}</td>
                        <td class="border-b py-3 pl-2 text-center">£{{ number_format($gross, 2) }}</td>
                        <td class="border-b py-3 pl-2 text-center text-neutral-600">£{{ number_format($tax, 2) }}</td>
                        <td class="border-b py-3 pl-2 text-center text-neutral-600">£{{ number_format($ni, 2) }}</td>
                        <td class="border-b py-3 pl-2 text-center text-neutral-600">£0.00</td>
                        <td class="border-b py-3 pl-2 pr-3 text-right font-bold greenText">£{{ number_format($net, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="8" class="pt-6">
                            <table class="w-full border-collapse border-spacing-0">
                                <tbody>
                                    <tr>
                                        <td class="w-full"></td>
                                        <td>
                                            <table class="w-full border-collapse border-spacing-0">
                                                <tbody>
                                                    <tr>
                                                        <td class="border-b p-3">
                                                            <div class="whitespace-nowrap text-slate-400">Gross Total:</div>
                                                        </td>
                                                        <td class="border-b p-3 text-right">
                                                            <div class="whitespace-nowrap font-bold text-main">£{{ number_format($group['total_gross'], 2) }}</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="border-b p-3">
                                                            <div class="whitespace-nowrap text-slate-400">Total Deductions:</div>
                                                        </td>
                                                        <td class="border-b p-3 text-right">
                                                            <div class="whitespace-nowrap font-bold text-main">£{{ number_format($totalTax + $totalNI + $totalPension, 2) }}</div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-main p-3">
                                                            <div class="whitespace-nowrap font-bold text-white">Net Total:</div>
                                                        </td>
                                                        <td class="bg-main p-3 text-right">
                                                            <div class="whitespace-nowrap font-bold text-white">£{{ number_format($totalNet, 2) }}</div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-14 py-6 text-xs text-neutral-500 italic text-center">
            <p>This is a computer generated document. For inquiries, please contact the finance department.</p>
        </div>
    </div>
</body>

</html>