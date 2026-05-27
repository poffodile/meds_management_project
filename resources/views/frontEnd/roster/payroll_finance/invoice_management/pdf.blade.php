<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_ref }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 14px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
        }

        .header {
            margin-bottom: 40px;
        }

        .header p {
            margin: 0;
        }

        .company-info {
            float: right;
            text-align: right;
        }

        .invoice-details {
            margin-top: 20px;
            float: left;
        }

        .invoice-details h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .billing-info {
            margin-top: 200px;
            width: 100%;
            border-collapse: collapse;
        }

        .billing-info td {
            width: 50%;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .items-table th {
            background: #f8f9fa;
            border-bottom: 2px solid #edeff2;
            padding: 12px;
            text-align: left;
            color: #555;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #edeff2;
        }

        .totals {
            float: right;
            margin-top: 30px;
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .grand-total {
            border-top: 2px solid #2c3e50;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 10px;
            color: #2c3e50;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <strong>{{ $home->title ?? 'Social Care Solutions' }}</strong><br>
                {{ $home->address ?? 'Main Office Street' }}<br>
                {{ $home->city ?? 'LocationCity' }}, {{ $home->postcode ?? 'PC123' }}<br>
                Phone: {{ $home->phone_no ?? '0123456789' }}
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Reference:</strong> {{ $invoice->invoice_ref }}</p>
                <p><strong>Date:</strong> {{ date('d M Y', strtotime($invoice->invoice_date)) }}</p>
                <p><strong>Due Date:</strong> {{ date('d M Y', strtotime($invoice->due_date)) }}</p>
            </div>
        </div>

        <table class="billing-info">
            <tr>
                <td>
                    <p class="textGray fs12" style="margin-bottom: 5px; color: #777;">BILL TO</p>
                    <strong>
                        @if($download_type == 'self')
                            {{ $customer->name ?? 'Service User' }}
                        @else
                            @php
                                $targetFunding = $onboardingDetails->firstWhere('id', $download_type);
                            @endphp
                            {{ $targetFunding->name ?? ($customer->name ?? 'Service User') }}
                        @endif
                    </strong><br>
                    {{ $customer->address ?? '' }}<br>
                    {{ $customer->city ?? '' }}, {{ $customer->postcode ?? '' }}<br>
                    {{ $customer->email ?? '' }}
                </td>
                <td></td>
            </tr>
        </table>

        @php
            $careAmount = 0;
            foreach($products as $product) {
                if (stripos($product->description, 'care services') !== false) {
                    $careAmount += ($product->qty * $product->price);
                }
            }
            $grossCareWithVat = $careAmount * 1.20;
            
            $totalDeductions = 0;
            $fundingList = [];
            foreach($onboardingDetails as $detail) {
                $deduction = ($detail->type == 1) ? ($grossCareWithVat * ($detail->vat / 100)) : $detail->vat;
                $totalDeductions += $deduction;
                $fundingList[$detail->id] = [
                    'name' => $detail->name,
                    'amount' => $deduction,
                    'is_percentage' => ($detail->type == 1),
                    'percent' => $detail->vat
                ];
            }
        @endphp

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if($download_type == 'self')
                    {{-- Self invoice shows everything --}}
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->description }}</td>
                        <td style="text-align: center;">{{ $product->qty }}</td>
                        <td style="text-align: right;">£{{ number_format($product->price, 2) }}</td>
                        <td style="text-align: right;">£{{ number_format($product->qty * $product->price, 2) }}</td>
                    </tr>
                    @endforeach
                @else
                    {{-- Funding invoice shows only the specific funding item --}}
                    @php 
                        $selected = $fundingList[$download_type] ?? null; 
                    @endphp
                    @if($selected)
                    <tr>
                        <td>Funding Contribution: {{ $selected['name'] }} {{ $selected['is_percentage'] ? '('.$selected['percent'].'%)' : '' }}</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right;">£{{ number_format($selected['amount'], 2) }}</td>
                        <td style="text-align: right;">£{{ number_format($selected['amount'], 2) }}</td>
                    </tr>
                    @endif
                @endif
            </tbody>
        </table>

        <div class="totals">
            <table style="width: 100%;">
                @if($download_type == 'self')
                    @php
                        $subTotal = $invoice->sub_total;
                        $vatAmount = $subTotal * 0.20;
                        $grossTotal = $subTotal + $vatAmount;
                    @endphp
                    <tr>
                        <td style="padding: 5px 0; color: #777;">Sub Total:</td>
                        <td style="text-align: right; padding: 5px 0;">£{{ number_format($subTotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #777;">VAT (20%):</td>
                        <td style="text-align: right; padding: 5px 0;">£{{ number_format($vatAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: #777; font-weight: bold;">Gross Total:</td>
                        <td style="text-align: right; padding: 5px 0; font-weight: bold;">£{{ number_format($grossTotal, 2) }}</td>
                    </tr>
                    @foreach($onboardingDetails as $detail)
                        @php $dAmount = $fundingList[$detail->id]['amount'] ?? 0; @endphp
                        <tr>
                            <td style="padding: 5px 0; color: #e53e3e; font-style: italic;">Less: {{ $detail->name }}</td>
                            <td style="text-align: right; padding: 5px 0; color: #e53e3e;">-£{{ number_format($dAmount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="grand-total">
                        <td style="padding: 10px 0;">AMOUNT DUE:</td>
                        <td style="text-align: right; padding: 10px 0;">£{{ number_format($grossTotal - $totalDeductions, 2) }}</td>
                    </tr>
                @else
                    {{-- Funding invoice total --}}
                    @php $sAmount = $fundingList[$download_type]['amount'] ?? 0; @endphp
                    <tr class="grand-total">
                        <td style="padding: 10px 0;">TOTAL AMOUNT:</td>
                        <td style="text-align: right; padding: 10px 0;">£{{ number_format($sAmount, 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            Generated on {{ date('d/m/Y H:i') }} | {{ $home->title ?? 'Social Care Solutions' }}
        </div>
    </div>
</body>

</html>