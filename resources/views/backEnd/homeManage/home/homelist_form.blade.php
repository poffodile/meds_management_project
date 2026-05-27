@extends('backEnd.layouts.master')
@section('title',' Homelist Form')
@section('content')

<?php
if (isset($homelist)) {
	$action 	= url('admin/homelist/edit/' . $homelist->id);
	$task 		= "Edit";
	$form_id 	= 'edit_homelist_form';
} else {
	$action 	= url('admin/homelist/add');
	$task 		= "Add";
	$form_id 	= 'add_homelist_form';
}
?>

<script src="{{ url('public/backEnd/js/jquery.validate.min.js') }}"></script>


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
									<label class="col-lg-2 control-label">Title</label>
									<div class="col-lg-10">
										<input type="text" name="title" class="form-control" placeholder="title" value="{{ (isset($homelist->title)) ? $homelist->title : '' }}" maxlength="255">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-2 control-label">Address</label>
									<div class="col-lg-10">
										<textarea name="address" class="form-control" placeholder="address" rows="3" maxlength="1000">{{ (isset($homelist->address)) ? $homelist->address : '' }}</textarea>
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-2 control-label">Location History</label>
									<div class="col-lg-10">
										<input type="text" name="location_history_duration" class="form-control" placeholder="Location history duration" value="{{ (isset($homelist->location_history_duration)) ? $homelist->location_history_duration : '' }}" maxlength="255">
										<p>Days for which location history will be saved</p>
									</div>
								</div>

								<div class="form-group has-feedback">
									<label class="col-lg-2 control-label">Rota Time Format</label>
									<div class="col-lg-10">
										<select name="rota_time_format" class="form-control" data-fv-field="status">
											<option value="12" <?php if (isset($homelist)) {
																	echo $homelist->rota_time_format == '12' ? 'selected' : '';
																} ?>>12 Hours</option>
											<option value="24" <?php if (isset($homelist)) {
																	echo $homelist->rota_time_format == '24' ? 'selected' : '';
																} ?>>24 Hours</option>
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
								if (isset($homelist->image)) {
									if (!empty($homelist->image)) {
										$image = home . '/' . $homelist->image;
									}
								}
								?>
								<div class="form-group">
									<label class="col-lg-2 control-label"></label>
									<div class="col-lg-10">
										<img src="{{ $image }}" id="old_image" alt="No image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
									</div>
								</div>

								<div class="form-group">
									<label class="col-lg-2 control-label">Image</label>
									<div class="col-md-10">
										<input type="file" id="img_upload" name="image" val="">
									</div>
								</div>

								<div class="form-actions">
									<div class="row">
										<div class="col-lg-offset-2 col-lg-10">
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
											<input type="hidden" name="home_id" value="{{ (isset($homelist->id)) ? $homelist->id : '' }}">
											<button type="submit" class="btn green btn-primary">Submit</button>
											<a href="{{ url('admin/homelist') }}">
												<button type="button" class="btn default btn-default" name="cancel">Cancel</button>
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