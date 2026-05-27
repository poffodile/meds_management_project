<?php
$logBooks = $data['logBooks'];
$image_id = $data['image_id'];

if ($image_id != '') {
	$image = env('APP_URL') . adminImgPath . '/' . $image_id;
} else {
	$image = env('APP_URL') . adminImgPath . '/default_user.jpg';
}

$url = "/";
$logo_url = "/images/scits.png";
$scits = "Scits";
$facebook_slug = "http://www.facebook.com/sharer.php?u=http://www.socialcareitsolutions.co.uk&pictures=" . asset('public/images/scits.png') . "&p[title]=Scits";

//$facebook_slug="http://www.facebook.com/sharer.php?u=".url($url)."&t=Scits&p[url]=".asset($logo_url)."&p[title]=".$scits;
/*    $twitter_slug="https://twitter.com/intent/tweet?url=".url('/');
$google_slug="https://plus.google.com/share?url=".url('/');
*/
$twitter_slug = "https://twitter.com/intent/tweet?url=http://www.socialcareitsolutions.co.uk&pictures";
$google_slug = "https://plus.google.com/share?url=http://www.socialcareitsolutions.co.uk&pictures";

?>
<!DOCTYPE html>
<html>
<head>
	<title>Scits</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous"> -->
	<style>
		* {
			padding: 0;
			margin: 0;
		}

		html,
		body {
			display: block;
			font-family: DejaVu Sans, sans-serif;
		}

		.header {
			position: relative;
			padding: 10px;
			width: 100%;
			background-color: #333;
		}

		.footer {
			padding: 10px;
			background-color: #333;
			text-align: center;
			word-spacing: 0;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			table-layout: fixed;
			/* prevents overflow */
		}

		thead {
			background-color: #f2f2f2;
			color: #1f88b5;
			padding: 6px;
		}

		th,
		td {
			border: 1px solid #ccc;
			padding: 10px 5px;
			font-size: 12px;
			text-align: left;
			vertical-align: top;
			word-wrap: break-word;
			white-space: wrap;
		}
		th {
			font-size: 13px;
		}
		.justify {
			word-break: break-word;
			overflow-wrap: break-word;
			white-space: normal;
		}
	</style>
</head>

<body>
	<div class="header">
		<img src="{{ $image }}" style="height:80px;">
		<img src="{{ asset('public/images/scits.png') }}" style="float:right;height:80px;">
	</div>
	<table class="table">
		<thead>
			<tr style="color:#1f88b5;" class="table-active">
				<th style="width:5%;">Id A</th>
				<th style="width:15%;">Staff Name</th>
				<th style="width:15%;">Child</th>
				<th style="width:15%;">Category</th>
				<th style="width:15%;">Title</th>
				<th style="width:20%;">Details</th>
				<th style="width:10%;">Date</th>
				<th style="width:5%;">Late</th>
			</tr>
		</thead>
		<tbody>

			@foreach ($logBooks as $logBook)
			<tr style="width: 100%; padding:5px; text-align:top;">
				<td>{{$logBook->id}}</td>
				<td>{{$logBook->staff_name}}</td>
				<td>{{$logBook->service_user_name ?? ""}}</td>
				<td>{{$logBook->category_name}}</td>
				<td>{{$logBook->title}}</td>
				<td class="justify">{{$logBook->details}}</td>
				<td class="justify">{{$logBook->date}}</td>
				<td>{{$logBook->is_late ? "Yes" : "No"}}</td>
			</tr>
			@endforeach
		</tbody>
	</table>
	<div class="footer">
		<div style="color:#fff;text-decoration:uppercase;margin:0;">
			<p style="font-size: 12px;">© {{ date('Y') }} Omega Care Group (SCITS). All Rights Reserved | www.socialcareitsolutions.co.uk</p>
			<!-- <ul type="none" style="padding:10px;"> -->

			<!-- <li style="display:inline-block;margin-right:5px;"><a href="{{$facebook_slug}}"><img src="{{ asset('public/images/facebook.png') }}" style="max-width:30px;"></a></li>
				<li style="display:inline-block;margin-right:5px;"><a href="{{$twitter_slug}}"><img src="{{ asset('public/images/twitter.png') }}" style="max-width:30px;"></a></li>
				<li style="display:inline-block;margin-right:5px;"><a href="{{$google_slug}}"><img src="{{ asset('public/images/googleplus.png') }}" style="max-width:30px;"></a></li> -->
			<!-- <li style="display:inline-block;"><a href="javascript:void(0)"><img src="{{ asset('public/images/instagram.png') }}" style="max-width:30px;"></a></li> -->
			<!-- </ul> -->
		</div>
		<!-- <div> -->
		<!-- <p style="color:#fff;text-decoration:uppercase;margin:0;">{{ date('Y') }} &copy; Scits</p> -->
	</div>
	</div>
</body>

</html>