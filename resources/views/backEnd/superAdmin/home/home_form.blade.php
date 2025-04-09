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
										<textarea name="address" class="form-control" placeholder="address" rows="3" maxlength="1000">{{ (isset($system_admin_home->address)) ? $system_admin_home->address : '' }}</textarea>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-3 control-label">Location History Duration</label>
									<div class="col-lg-9">
										<input type="text" name="location_history_duration" class="form-control" placeholder="Location history duration" value="{{ (isset($system_admin_home->location_history_duration)) ? $system_admin_home->location_history_duration : '' }}" maxlength="255">
										<p>Days for which location history will be saved</p>
									</div>
								</div>
								
								<div class="form-group yes_no_btn">
									<label class="col-lg-3 control-label">Registered with Ofsted or CIW</label>
									<div class="col-lg-9 d-flex align-items-center gap-2">
										<input type="radio" name="is_registered" id="is_registered1" value="1" {{ isset($system_admin_home->is_registered) && $system_admin_home->is_registered == 1 ? 'checked' : '' }}>
										<label for="is_registered1" class="control-label">Yes </label>
										<input type="radio" name="is_registered" id="is_registered2" value="0" {{ isset($system_admin_home->is_registered) && $system_admin_home->is_registered == 0 ? 'checked' : '' }}>
										<label for="is_registered2" class=" control-label">No </label>
									</div>
								</div>
								<?php $rota_time_format = (isset($system_admin_home->rota_time_format)) ? $system_admin_home->rota_time_format : ''; ?>
								<div class="form-group has-feedback">
									<label class="col-lg-3 control-label">Rota Time Format</label>
									<div class="col-lg-9">
										<select name="rota_time_format" class="form-control" data-fv-field="status">
											<option value="12" {{ $rota_time_format == '12' ? 'selected' : '' }}>12 Hours</option>
											<option value="24" {{ $rota_time_format == '24' ? 'selected' : '' }}>24 Hours</option>
										</select>
									</div>
								</div>


								<!--<div class="form-group">
                                <label for="inputEmail1" class="col-lg-2 control-label">Email</label>
                                <div class="col-lg-10">
                                    <input type="email" name="name" class="form-control" id="inputEmail1" placeholder="Email">
                                </div>
                            </div> -->
								<?php
								$image = home . '/default_home.png';

								if (isset($system_admin_home->image)) {
									if (!empty($system_admin_home->image)) {
										$image = env('APP_URL') . home . '/' . $system_admin_home->image;
									}
								}
								?>
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

	$(document).ready(function () {
        $('#home_type').change(function () {
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

@endsection