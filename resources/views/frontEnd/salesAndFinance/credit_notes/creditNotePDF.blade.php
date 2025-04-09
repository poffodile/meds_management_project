<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="shortcut icon" type="image/x-icon" href="{{ url('public/images/favicon.ico') }}">
	<title>Purchase Order invoice</title>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
	<style type="text/css">
		* {
			padding: 0;
			margin: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Roboto', sans-serif;
			font-size: 14px;
			line-height: 1.4;
			background: #f2f3f5;
			color: #000;
			margin: 0;
			padding: 0;
			position: relative;
		}

		table {
			width: 100%;
			border-collapse: collapse;
		}

		.main-center-box {
			width: 750px;
		}

		@media only screen and (max-width: 600px) {
			.main-center-box {
				width: 100%;
				margin: 0 auto;
			}
		}

		.product_details td {
			padding: 6px 0;
			text-align: center;
			border: 1px solid #eee;
		}

		.product_details {
			width: 100%;
		}

		.totlePrice {
			padding-top: 5px;
			white-space: nowrap;
			text-align: right;
		}

		.pagination {
			width: 100%;
			display: flex;
			list-style: none;
			justify-content: flex-end;
			font-size: 14px;
			font-weight: 400;
		}

		/* 
		.main-table {
			padding-bottom: 160px;
		} */

		ul.pagination li {
			padding: 0 10px;
		}
		.watermark {
			position: absolute;
			top: 40%;
			left: 50%;
			transform: translate(-50%, -50%) rotate(-45deg);
			opacity: 0.2;
			font-size: 10rem;
			color:rgba(88, 86, 86, 0.97);
			font-family: Arial, sans-serif;
			pointer-events: none;
			/* z-index: 9999;  */
			font-weight: bold;
		}
	</style>
</head>

<body>
<div class="watermark">Paid</div>
	<div class="main-center-box">
		<div style="background: #f2f3f5; padding: 20px; float: left; width: 100%; min-height: 100vh;">
			<table table="border" cellpadding="0" cellspacing="0" width="100%"
				style=" position: relative; ">
				<tbody style="padding-bottom: 40px;">
					<tr>
						<!-- <td style="width: 100%;float: left; text-align: center;">
							<h4 style="margin-top: 0;margin-bottom: 0;font-size: 22px;">Order Invoice</h4>
						</td> -->
						<td style="width: 60%; float: left;">
							<table cellpadding="0" cellspacing="0"
								style="text-align: left; float: left; margin-bottom: 10px;">
								<tr>
									<td style="font-size: 18px; font-weight: 600; color: #000;">
										<img src="{{url('public/images/ewm_logo.png')}}" style="width: 180px;">
									</td>
								</tr>
								<tr>
									<td>
										<h3 style="margin-bottom: 0; font-size: 24px; margin-top: 0; padding-top: 8px; padding-bottom: 8px;">
											<strong>Credit Note - {{$credit_details->credit_ref}}</strong>
										</h3>
										<p style="margin-bottom: 0; font-size: 18px; margin-top: 0; padding-top: 8px; padding-bottom: 8px;">
											<strong>Date: {{date('d/m/Y',strtotime($credit_details->date))}}</strong>
										</p>
									</td>
								</tr>
							</table>
						</td>
						<td style="width: 40%; float: left;">
							<table cellpadding="0" cellspacing="0"
								style="padding: 10px 0; width: 100%; text-align: right; margin-bottom: 10px;">
								<tr>
									<td style="font-size: 18px; font-weight: 600; color: #000; padding-top: 15px; margin-bottom: 0">
										<?php if(!empty($company)){echo $company;}else{echo "SCITS";}?>
									</td>
								</tr>
								<tr>
									<td>
										<p style="margin: 2px 0; font-size: 14px;"><?php if(!empty($current_location)){echo $current_location;}else{echo " ";} ?></p>
										<p style="margin: 2px 0; font-size: 14px;"><strong>Tel:
											</strong> +44{{$phone_no}}</p>
										<p style="margin: 2px 0; font-size: 14px;"><strong>Email:
											</strong>{{$email}}</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr style="border-bottom: 1px solid #000;">
						<td style="width: 60%; float: left; padding-top: 10px; padding-bottom: 10px;">
							<table cellpadding="0" cellspacing="0" width="100%"
								style="text-align: left; width: 100%;">
								<thead>
									<tr style="font-size: 14px;">
										<th>
											<p style="margin: 0; text-align: left;">{{$credit_details->suppliers->name}}</p>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="font-size: 13px;">
											<p style="margin: 5px 0;">{{$credit_details->suppliers->contact_name}} </p>
											<p style="margin: 5px 0;">{{$credit_details->suppliers->address}} </p>
											<p style="margin: 5px 0;">{{$credit_details->suppliers->city}}</p>
											<!-- <p style="margin: 5px 0;">GB</p> -->
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						
					</tr>
					<tr>
						<td style="width: 100%; float: left;" colspan="2">
							<div class="main-table">
								<table cellpadding="0" cellspacing="0" width="100%" class="table" style=" margin-top:20px;">
									<tbody>
										<tr style="font-size: 14px; background-color: #e5e5e5; text-align: center;">
											<td style="width: 20px;">
												<p style="padding: 6px 4px;"><strong>#</strong></p>
											</td>
											<td style="width: 60px;">
												<p style="padding: 6px 4px;"><strong>Product</strong></p>
											</td>
											<td style="width: 90px;">
												<p style="padding: 6px 4px;"><strong>Description</strong></p>
											</td>
											<td style="width: 50px;">
												<p style="padding: 6px 4px;"><strong>Qty</strong></p>
											</td>
											<td style="width: 50px;">
												<p style="padding: 6px 4px;"><strong>Unit Price</strong></p>
											</td>
											<td style="width: 60px;">
												<p style="padding: 6px 4px;"><strong>VAT(%)</strong></p>
											</td>
											<td style="width: 70px;">
												<p style="padding: 6px 4px;"><strong>Net Amount</strong></p>
											</td>
										</tr>
										<?php
										$sub_total_amount = 0;
										$vat_amount = 0;
										foreach ($credit_details->creditNoteProducts as $key => $val) {
											$qty = $val->qty * $val->price;
											$sub_total_amount = $sub_total_amount + $qty;
											$vat = $qty * $val->vat / 100;
											$vat_amount = $vat_amount + $vat;
											$product_details = App\Models\Product::find($val->product_id);

										?>
											<tr class="product_details">
												<td>
													<p style="margin: 0; font-size: 14px;">{{++$key}}</p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">{{$product_details->product_name}}</p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">{{$val->description}} </p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">{{$val->qty}}</p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">£{{$val->price}}</p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">{{$val->vat}}</p>
												</td>
												<td>
													<p style="margin: 0; font-size: 14px;">£{{$qty}}</p>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					<tr>
						<td style="width: 100%; float: left;" colspan="2">
							<div style="position: fixed; bottom: 20px; left: 20px; right: 20px;">
								<table cellpadding="0" cellspacing="0" width="100%" class="table">
									<tbody style=" width: 100%; float: left;">
										<tr class="product_details">
											<th colspan="5" style="width: 60%;"></th>
											<th colspan="1" style="width: 20%; text-align: right;">
												<p class="totlePrice" style="margin: 0; font-size: 14px; font-weight: 400;">Sub Total (exc. VAT) </p>
											</th>
											<th colspan="1" style="width: 20%; text-align: right;">
												<p class="totlePrice" style="margin: 0; font-size: 14px; font-weight: 400;">£{{$sub_total_amount}}</p>
											</th>
										</tr>
										<tr class="product_details">
											<th colspan="5" style="width: 60%;"></th>
											<th colspan="1" style="width: 20%; text-align: right; padding-bottom:6px;">
												<p class="totlePrice" style="margin: 0; font-size: 14px; font-weight: 400;">Sub Total (exc. VAT) </p>
											</th>
											<th colspan="1" style="width: 20%; text-align: right; padding-bottom:6px;">
												<p class="totlePrice" style="margin: 0; font-size: 14px; font-weight: 400;">£{{$sub_total_amount}}</p>
											</th>
										</tr>
										<tr class="product_details">
											<th colspan="5" style="width: 60%;"></th>
											<th colspan="1" style="width: 20%; text-align: right; border-top: 1px solid #000;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 10px; font-size: 14px;">Total (inc. VAT)</p>
											</th>
											<?php
											$total_amount = $sub_total_amount + $vat_amount;
											?>
											<th colspan="1" style="width: 20%; text-align: right;border-top: 1px solid #000;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 10px; font-size: 14px;">£{{$total_amount}}</p>
											</th>
										</tr>
										<tr class="product_details">
											<th colspan="5" style="width: 60%;"></th>
											<th colspan="1" style="width: 20%; text-align: right;border-top: 1px solid #000;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 6px; font-size: 14px; font-weight: 400;">VAT</p>
											</th>
											<th colspan="1" style="width:20%;text-align:right; border-top: 1px solid #000;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 6px; font-size: 14px; font-weight: 400;">£{{$vat_amount}}</p>
											</th>
										</tr>
										<tr class="product_details">
											<th colspan="5" style="width: 60%;"></th>
											<th colspan="1" style="width: 20%; text-align: right; border-top: 1px solid #000;padding-bottom:10px;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 10px; font-size: 14px;">Total (inc. VAT)</p>
											</th>
											<?php
											$total_amount = $sub_total_amount + $vat_amount;
											?>
											<th colspan="1" style="width: 20%; text-align: right;border-top: 1px solid #000;padding-bottom:10px;">
												<p class="totlePrice" style="margin: 0; padding-bottom: 10px; font-size: 14px;">£{{$total_amount}}</p>
											</th>
										</tr>
										<tr class="product_details" style="border-top: 1px solid #000; padding-top:10px;">
											<th colspan="5"></th>
											<th colspan="2" >
												<div class="pagination" style="text-align: right;">
													<span>Page</span>
													<span> 1 </span>
													<span> of </span>
													<span> 1</span>
												</div>
											</th>
										</tr>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>

</html>