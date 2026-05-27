@extends('backEnd.layouts.master')

@section('title',' System Admin Home Form')

@section('content')

<?php
if (isset($system_admin_home)) {
	$action = url('admin/system-admin/home/edit/' . $system_admin_home->id);
	$task = "Edit";
	$form_id = 'edit_homelist_form';
} else {
	$action = url('admin/system-admin/homes/add/' . $system_admin_id);
	$task = "Add";
	$form_id = 'add_homelist_form';
}
?>

<script src="{{ url('public/backEnd/js/jquery.validate.min.js') }}"></script>
<style>
	.yes_no_btn input {
		margin-top: 8px;
	}

	.yes_no_btn .d-flex label {
		margin-right: 20px;
	}

	.pac-container {
		z-index: 10000 !important;
	}
</style>

<section id="main-content" class="">
	<section class="wrapper">
		<div class="row">
			<div class="col-lg-12">
				<section class="panel">
					<header class="panel-heading">
						{{ $task }} Home
					</header>
					@include('backEnd.common.alert_messages')
					<div class="panel-body">
						<div class="position-center">
							<form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
								<div class="form-group">
									<label class="col-lg-3 control-label">Title</label>
									<div class="col-lg-9">
										<input type="text" name="title" class="form-control" placeholder="title" value="{{ (isset($system_admin_home->title)) ? $system_admin_home->title : '' }}" maxlength="255">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Address</label>
									<div class="col-lg-9">
										<input type="text" name="address" id="address_input" class="form-control" placeholder="Enter your address" value="@if(isset($system_admin_home->address)){{ str_replace(["\r\n", "\r", "\n"], ' ', $system_admin_home->address) }}@elseif(isset($company_settings->address)){{ str_replace(["\r\n", "\r", "\n"], ' ', $company_settings->address) }}@endif" autocomplete="off" required>
										<div id="map" style="height: 300px; width: 100%; margin-top: 10px; display: none;"></div>
										<p id="map-error" class="text-danger mt-2" style="display: none;"></p>

										<input type="hidden" name="latitude" id="latitude" value="{{ isset($system_admin_home->latitude) ? $system_admin_home->latitude : '' }}">
										<input type="hidden" name="longitude" id="longitude" value="{{ isset($system_admin_home->longitude) ? $system_admin_home->longitude : '' }}">
										<input type="hidden" name="place_id" id="place_id" value="{{ isset($system_admin_home->place_id) ? $system_admin_home->place_id : '' }}">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Home Location</label>
									<div class="col-lg-9">
										<div class="checkbox">
											<label>
												<input type="checkbox" name="is_home_area" id="is_home_area_checkbox" value="1"
													{{ (isset($system_admin_home->is_home_area) && $system_admin_home->is_home_area == 1) || (!isset($system_admin_home) && isset($company_settings->is_home_area) && $company_settings->is_home_area == 1) ? 'checked' : '' }}>
												(Check if this home have home area list)
											</label>
										</div>
									</div>
								</div>

								<div id="home_area_list_section" style="display: {{ (isset($system_admin_home->is_home_area) && $system_admin_home->is_home_area == 1) || (!isset($system_admin_home) && isset($company_settings->is_home_area) && $company_settings->is_home_area == 1) ? 'block' : 'none' }};">
									<div class="form-group">
										<label class="col-lg-3 control-label">Home Area List</label>
										<div class="col-lg-9">
											<div id="home_area_inputs">
												@if(isset($home_areas) && count($home_areas) > 0)
												@foreach($home_areas as $area)
												<div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">
													<input type="text" name="home_area_names[]" class="form-control" placeholder="Area name" value="{{ $area->name }}">
													<button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>
												</div>
												@endforeach
												@elseif(!isset($system_admin_home) && isset($company_areas) && count($company_areas) > 0)
												@foreach($company_areas as $area)
												<div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">
													<input type="text" name="home_area_names[]" class="form-control" placeholder="Area name" value="{{ $area->area_name }}">
													<button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>
												</div>
												@endforeach
												@else
												<div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">
													<input type="text" name="home_area_names[]" class="form-control" placeholder="Area name">
													<button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>
												</div>
												@endif
											</div>
											<button type="button" id="add_area_btn" class="btn btn-success btn-sm" style="margin-top:10px;"><i class="fa fa-plus"></i> Add Area</button>
										</div>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Clock in/Clock out Range</label>
									<div class="col-lg-9">
										<input type="text" name="home_area" id="home_area" class="form-control" placeholder="Home area" value="{{ (isset($system_admin_home->home_area)) ? $system_admin_home->home_area : (isset($company_settings->clock_in_range) ? $company_settings->clock_in_range : '') }}" maxlength="255">
										<span class="help-block">(In meters or min 10 meters)</span>
									</div>
								</div>

								<!-- <div class="form-group">
									<label class="col-lg-3 control-label">Location History Duration</label>
									<div class="col-lg-9">
										<input type="text" name="location_history_duration" class="form-control" placeholder="Location history duration" value="{{ (isset($system_admin_home->location_history_duration)) ? $system_admin_home->location_history_duration : '' }}" maxlength="255">
										<p>Days for which location history will be saved</p>
									</div>
								</div> -->
								<?php $rota_time_format = (isset($system_admin_home->rota_time_format)) ? $system_admin_home->rota_time_format : ''; ?>
								<!-- <div class="form-group has-feedback">
									<label class="col-lg-3 control-label">Rota Time Format</label>
									<div class="col-lg-9">
										<select name="rota_time_format" class="form-control" data-fv-field="status">
											<option value="12" {{ $rota_time_format == '12' ? 'selected' : '' }}>12 Hours</option>
											<option value="24" {{ $rota_time_format == '24' ? 'selected' : '' }}>24 Hours</option>
										</select>
									</div>
								</div> -->


								<!--<div class="form-group">
                                <label for="inputEmail1" class="col-lg-2 control-label">Email</label>
                                <div class="col-lg-10">
                                    <input type="email" name="name" class="form-control" id="inputEmail1" placeholder="Email">
                                </div>
                            </div> -->
								<?php
								// $image = home . '/default_home.png';
								$image = env('APP_URL') . home . '/default_home.png';

								if (isset($system_admin_home->image)) {
									if (!empty($system_admin_home->image)) {
										$image = env('APP_URL') . home . '/' . $system_admin_home->image;
									}
								}
								?>
								<div class="form-group">
									<label class="col-lg-3 control-label">Weekly Rate (Service Users)</label>
									<div class="col-lg-9">
										<input type="number" step="0.01" name="weekly_allowance_service_users" class="form-control" placeholder="Weekly Rate" value="{{ (isset($system_admin_home->weekly_allowance_service_users)) ? $system_admin_home->weekly_allowance_service_users : (isset($company_settings->weekly_allowance_service_users) ? $company_settings->weekly_allowance_service_users : '') }}">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Monthly Rate (Service Users)</label>
									<div class="col-lg-9">
										<input type="number" step="0.01" name="monthly_allowance_service_users" class="form-control" placeholder="Monthly Rate" value="{{ (isset($system_admin_home->monthly_allowance_service_users)) ? $system_admin_home->monthly_allowance_service_users : (isset($company_settings->monthly_allowance_service_users) ? $company_settings->monthly_allowance_service_users : '') }}">
									</div>
								</div>
								<div class="form-group">
									<label class="col-lg-3 control-label"></label>
									<div class="col-lg-9">
										<img src="{{ $image }}" id="old_image" alt="No image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Image</label>
									<div class="col-md-9">
										<input type="file" id="img_upload" name="image" val="">
									</div>
								</div>

								<div class="form-actions">
									<div class="row">
										<div class="col-lg-offset-3 col-lg-10">
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
											<input type="hidden" name="id" value="{{ (isset($system_admin_home->id)) ? $system_admin_home->id : '' }}">
											<button type="submit" class="btn btn-primary">Save</button>
											<a href="{{ url('admin/system-admin/homes/'.$system_admin_id) }}">
												<button type="button" class="btn btn-default" name="cancel">Cancel</button>
											</a>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
				</section>
			</div>
		</div>
	</section>
</section>

<script>
	$(document).ready(function() {
		function readURL(input) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function(e) {
					$('#old_image').attr('src', e.target.result);
					//$('#old_image').attr('src', e.target.result).width(150).height(170);
				};
				reader.readAsDataURL(input.files[0]);
			}
		}
		$("#img_upload").change(function() {
			var img_name = $(this).val();

			if (img_name != "" && img_name != null) {
				var img_arr = img_name.split('.');
				var ext = img_arr.pop();
				ext = ext.toLowerCase();
				if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
					input = document.getElementById('img_upload');
					if (input.files[0].size > 2097152 || input.files[0].size < 10240) {
						$(this).val('');
						$("#img_upload").removeAttr("src");
						alert("image size should be at least 10KB and upto 2MB");
						return false;
					} else {
						readURL(this);
					}
				} else {
					$(this).val('');
					alert('Please select an image .jpg, .png, .gif file format type.');
				}
			}
			return true;
		});

		$("#security_policy").change(function() {
			var pdf_name = $(this).val();

			if (pdf_name != "" && pdf_name != null) {
				var img_arr = pdf_name.split('.');
				var ext = img_arr.pop();
				ext = ext.toLowerCase();
				if (ext == "pdf") {
					input = document.getElementById('security_policy');
					// if(input.files[0].size > 2097152 || input.files[0].size <  10240)
					// {
					//   $(this).val('');
					//   $("#security_policy").removeAttr("src");
					//   alert("file size should be at least 10KB and upto 2MB");
					//   return false;
					// }
					// else
					// {
					// readURL(this);
					// }   
				} else {
					$(this).val('');
					alert('Please select pdf file format.');
				}
			}
			return true;
		});
	});

	$(document).ready(function() {
		$('#home_type').change(function() {
			var selectedType = $(this).val();
			if (selectedType === 'residential') {
				$('#residential_rooms').show();
				$('#accommodation_rooms').hide();
			} else if (selectedType === 'accommodation') {
				$('#accommodation_rooms').show();
				$('#residential_rooms').hide();
			} else {
				$('#residential_rooms, #accommodation_rooms').hide();
			}
		});

		$('#add_area_btn').click(function() {
			var areaInputHtml = '<div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">' +
				'<input type="text" name="home_area_names[]" class="form-control" placeholder="Area name">' +
				'<button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>' +
				'</div>';
			$('#home_area_inputs').append(areaInputHtml);
		});

		$(document).on('click', '.remove-area-btn', function() {
			$(this).closest('.area-input-group').remove();
		});

		$('#is_home_area_checkbox').change(function() {
			if ($(this).is(':checked')) {
				$('#home_area_list_section').show();
			} else {
				$('#home_area_list_section').hide();
			}
		});
	});
</script>


<script>
	// $("#add_homelist_form").validate({
	//     rules: 
	//     {
	//         "security_policy": {
	//             required: true,
	//             // extension: "png|jpg|gif|jpeg"
	//         }

	//     },
	//     messages: 
	//     {
	//         "security_policy": {
	//             required: 'Please select file',
	//             // extension: "Please select image in png,jpg and jpeg format."
	//         }
	//     }

	// });
</script>

<?php $google_map_api_key = config('services.google.map_api_key') ?? env('GOOGLE_MAP_API_KEY') ?? 'AIzaSyBQhN-xkQiUIQ9toO-KRdb9wqtc_cGbAqo'; ?>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $google_map_api_key }}&libraries=places&callback=initMap" async defer></script>
<script>
	let map;
	let marker;
	let autocomplete;
	let geocoder;

	function initMap() {
		const defaultLat = parseFloat($('#latitude').val()) || 51.5074; // Default: London, UK or saved lat
		const defaultLng = parseFloat($('#longitude').val()) || -0.1278; // Default: London, UK or saved lng
		const initialLocation = {
			lat: defaultLat,
			lng: defaultLng
		};

		const mapElement = document.getElementById("map");
		mapElement.style.display = "block"; // Always show if we have lat/lng or default

		map = new google.maps.Map(mapElement, {
			center: initialLocation,
			zoom: $('#latitude').val() ? 15 : 6, // Zoom to 6 for full UK view if no location saved
		});

		marker = new google.maps.Marker({
			map: map,
			position: initialLocation,
			draggable: true,
		});

		geocoder = new google.maps.Geocoder();

		const input = document.getElementById("address_input");
		autocomplete = new google.maps.places.Autocomplete(input, {
			componentRestrictions: {
				country: "gb"
			} // Restrict autocomplete results to the UK
		});
		autocomplete.bindTo("bounds", map);

		// Geocode prefilled default address on load if coordinates are not set
		const initialAddress = input.value.trim();
		if (initialAddress && !$('#latitude').val()) {
			geocoder.geocode({
				address: initialAddress
			}, (results, status) => {
				if (status === "OK" && results[0]) {
					const place = results[0];
					$('#latitude').val(place.geometry.location.lat());
					$('#longitude').val(place.geometry.location.lng());
					$('#place_id').val(place.place_id || '');

					map.panTo(place.geometry.location);
					map.setZoom(15);
					marker.setPosition(place.geometry.location);
				}
			});
		}

		// Clear coordinates if the user manually changes the address input
		$(input).on('input', function() {
			$('#latitude').val('');
			$('#longitude').val('');
			$('#place_id').val('');
		});

		autocomplete.addListener("place_changed", () => {
			const place = autocomplete.getPlace();
			$('#map-error').hide();

			if (!place.geometry || !place.geometry.location) {
				$('#map-error').text("No details available for input: '" + place.name + "'").show();
				// clear hidden fields
				$('#latitude').val('');
				$('#longitude').val('');
				$('#place_id').val('');
				return;
			}

			// Update hidden fields
			$('#latitude').val(place.geometry.location.lat());
			$('#longitude').val(place.geometry.location.lng());
			$('#place_id').val(place.place_id || '');

			// Adjust map
			map.panTo(place.geometry.location);
			map.setZoom(15);
			marker.setPosition(place.geometry.location);
		});

		// Listen to marker drag events
		marker.addListener("dragend", () => {
			const newPos = marker.getPosition();

			// Reverse geocode to get address
			geocoder.geocode({
				location: newPos
			}, (results, status) => {
				if (status === "OK" && results[0]) {
					const place = results[0];
					input.value = place.formatted_address;
					$('#latitude').val(newPos.lat());
					$('#longitude').val(newPos.lng());
					$('#place_id').val(place.place_id || '');
					$('#map-error').hide();
				} else {
					$('#map-error').text("Could not find address for this location.").show();
				}
			});
		});

		// Validate on form submit
		$('#{{ $form_id }}').on('submit', function(e) {
			if (!$('#latitude').val() || !$('#longitude').val()) {
				e.preventDefault();
				$('#map-error').text("Please select a valid address from the dropdown or map.").show();
				input.focus();
			}
		});
	}
</script>

@endsection