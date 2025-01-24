<!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('public/images/favicon.ico') }}">
	<title>Purchase Order invoice</title>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
	<style type="text/css">
		*{
			padding: 0;
			margin: 0;
		}
		body {
			font-family: 'Roboto', sans-serif;
			background: #fff;
		}

		table {
			width: 100%;
			border-collapse: collapse;
		}

		.main-center-box {
			width: 770px;
			height:100vh;
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
		.totlePrice{
			padding-top: 10px;
			white-space: nowrap;
            text-align: end;
		}
		.pagination{
			width: 100%;
			display: flex;
			list-style: none;
			justify-content: flex-end;
			font-size: 14px;
			font-weight: 400;
			padding-top: 12px;
			border-top: 1px solid #eee;
			margin-top: 12px;
		}
		ul.pagination li {
			padding: 0 10px;
		}
		.main-table{
			min-height:500px;
			position: relative;
		}
		.table_footer{
			/* position: fixed;
			top: 100px;
			left: 0;
			width: 100%; */
			/* z-index: 10; */
			margin-top:200px;
			
		}
	</style>
</head>

<body>
	<div class="main-center-box">
		<div style="background:#f2f3f5;padding:12px;float:left; width: 100%;">
			<table table="border" cellpadding="0" cellspacing="0" width="100%"
				style="background:#fafafa;padding: 12px;">
				<tbody style="padding-bottom: 40px;">
					<tr>
						<!-- <td style="width: 100%;float: left; text-align: center;">
							<h4 style="margin-top: 0;margin-bottom: 0;font-size: 22px;">Order Invoice</h4>
						</td> -->
						<td style="width: 60%;float: left;">
							<table cellpadding="0" cellspacing="0"
								style="text-align: left;padding: 20px 0;float:left ;">
								<tr>
									<td style="font-size:18px;font-weight:600;color:#000;">
                                        <img src="{{url('public/images/ewm_logo.png')}}" style="width:180px;">
                                    </td>
								</tr>
								<tr>
									<td>
										<h3 style="margin-bottom:0;font-size:24px;margin-top:0; padding: 8px;">
											<strong>PurchaseOrder - {{$po_details->purchase_order_ref}} </strong> </h3>
										<p style="margin-bottom:0;font-size:18px;margin-top:0; padding: 8px;">
											<strong>Date: {{date('d/m/Y',strtotime($po_details->purchase_date))}} </strong> </p>
									</td>
								</tr>
							</table>
						</td>
						<td style="width: 40%;float: left;">
							<table cellpadding="0" cellspacing="0"
								style="padding: 10px 0; width:100%;text-align: right;">
								<tr>
									<td style="font-size:18px;font-weight:600;color:#000;padding-top:15px;margin-bottom: 0">
										{{$company}}
									</td>
								</tr>
								<tr>
									<td>
										<p style="margin:2px 0;font-size:14px;"><?php echo $current_location;?></p>
										<p style="margin:2px 0;font-size:14px;"><strong>Tel:
										</strong> +44{{$phone_no}}</p>
										<p style="margin:2px 0;font-size:14px;"><strong>Email:		
										</strong>{{$email}}</p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="width: 60%;float: left;">
							<table cellpadding="0" cellspacing="0" width="100%"
								style="text-align:left;padding-top:20px;border-top:1px solid #000;border-bottom:1px solid #000; padding-bottom: 20px;width: 100%;">
								<thead>
									<tr style="font-size: 14px;">
										<th>
											<p style="margin: 0; text-align:left;">{{$po_details->suppliers->name}}</p>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td style="font-size: 13px;">
											<p style="margin: 5px 0;">{{$po_details->suppliers->contact_name}} </p>
											<p style="margin: 5px 0;">{{$po_details->suppliers->address}} </p>
											<p style="margin: 5px 0;">{{$po_details->suppliers->city}}</p>
											<!-- <p style="margin: 5px 0;">GB</p> -->
										</td>
									</tr>
								</tbody>
							</table>
						</td>
						<td style="width: 40%;float: left;">
							<table cellpadding="0" cellspacing="0" width="100%"
								style="text-align:left;padding-top:20px;border-top:1px solid #000;border-bottom:1px solid #000; padding-bottom: 20px;">
								<thead>
									<tr style="font-size: 14px;">
										<th>
											<p style="margin: 0;text-align:left;">Site / Delivry Address</p>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<?php if($po_details->site_id == 0){
											$site_detail=App\Models\Customer::find($po_details->customer_id);
											// echo "<pre>";print_r($site_detail);die;
										?>
										<td style="font-size: 13px;">
											<p style="margin: 5px 0;">{{$site_detail->address ?? ""}} </p>
											<p style="margin: 5px 0;">{{$site_detail->city ?? ""}} </p>
											<p style="margin: 0;font-size: 13px;">{{$site_detail->postal_code ?? ""}}</p>
											<!-- <p style="margin: 5px 0;">London</p>
											<p style="margin: 5px 0;">SW7 1EE</p> -->
										</td>
										<?php }else{
											$site_detail=App\Models\Constructor_customer_site::find($po_details->site_id);
										?>
											<td style="font-size: 13px;">
											<p style="margin: 5px 0;">{{$site_detail->address ?? ""}} </p>
											<p style="margin: 5px 0;">{{$site_detail->city ?? ""}} </p>
											<p style="margin: 0;font-size: 13px;">{{$site_detail->post_code ?? ""}}</p>
											<!-- <p style="margin: 5px 0;">London</p>
											<p style="margin: 5px 0;">SW7 1EE</p> -->
										</td>
										<?php }?>
									</tr>

								</tbody>
							</table>
						</td>
					</tr>
					<tr>
						<td style="width: 100%;float:left;" colspan="2">
							<div class="main-table">
								<table cellpadding="0" cellspacing="0" width="100%" class="table">
									<tbody>
										<tr style="font-size: 14px;background-color: #e5e5e5; text-align: center;">
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
										$sub_total_amount=0;
										$vat_amount=0;
										foreach($po_details->purchaseOrderProducts as $key=>$val){
											$qty=$val->qty*$val->price;
											$sub_total_amount=$sub_total_amount+$qty;
											$vat=$qty*$val->vat/100;
											$vat_amount=$vat_amount+$vat;
											$product_details=App\Models\Product::find($val->product_id);
											
										?>
										<tr class="product_details">
											<td>
												<p style="margin: 0;font-size: 14px;">{{++$key}}</p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">{{$product_details->product_name}}</p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">{{$val->description}} </p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">{{$val->qty}}</p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">£{{$val->price}}</p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">{{$val->vat}}</p>
											</td>
											<td>
												<p style="margin: 0;font-size: 14px;">£{{$qty}}</p>
											</td>
										</tr>
										<?php }?>
									</tbody>
								</table>
								<div style="margin-top:200px;">
									<table cellpadding="0" cellspacing="0" width="100%" class="table" >
										<tbody class="table_footer">
											<tr class="product_details">
												<th colspan="7"></th>
												<th> <p class="totlePrice" style="margin: 0;font-size: 14px;font-weight: 400;">Sub Total (exc. VAT) </p> </th>
												<th><p class="totlePrice" style="margin: 0;font-size: 14px;font-weight: 400;">£{{$sub_total_amount}}</p> </th>
											</tr>
											<tr class="product_details">
												<th colspan="7"></th>
												<th> <p class="totlePrice" style="margin: 0;font-size: 14px;font-weight: 400;">VAT</p> </th>
												<th><p class="totlePrice" style="margin: 0;font-size: 14px;font-weight: 400;">£{{$vat_amount}}</p> </th>
											</tr>
											<tr class="product_details">
												<th colspan="7"></th>
												<th> <p class="totlePrice" style="margin: 0;font-size: 14px;">Total (inc. VAT)</p> </th>
												<?php 
													$total_amount=$sub_total_amount+$vat_amount;
												?>
												<th><p class="totlePrice" style="margin: 0;font-size: 14px;">£{{$total_amount}}</p> </th>
											</tr>
											<tr class="product_details">
												<th  colspan="7"></th>
												<th colspan="2">
													<div class="pagination" style="text-align:right;">
														<span>Page</span>
														<span> 1 </span>
														<span> of </span>
														<span> 1</span>
													</div>	
												</th>
												<th></th>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>

</html>