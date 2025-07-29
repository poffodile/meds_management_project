@extends('frontEnd.layouts.login')
@section('title','Login')
@section('content')
<style>
    .bg_color {
        background-color: #0877bd !important;
        border-bottom: 10px solid #1d6797 !important;
    }
    .user-login-info .form-group .user_name:focus .control_form:focus .user_name:focus{
        border: 1px solid #1d6797 !important;
    }
    .sub_btn {
        background-color: #aec785 !important;
    }
    .forget_pas {
        color: #1d6797 !important;
    }
</style>

<?php 
// Ram 17/07/2025 Replaced hardcoded company-based home fetching with dynamic fetching based on the logged-in user's company (admin_id). see on controller code
// $admin_id = App\Admin::where('company','like',"Omega care group")->where('is_deleted', 0)->value('id');
// $homes = App\Home::select('id','title')->where('admin_id',$admin_id)->where('is_deleted','0')->get()->toArray();
?>

<form method="post" class="form-signin" action="{{ url('/switch_home_submit') }}" id="login_form" autocomplete="off" >
    @csrf
    <h2 class="form-signin-heading bg_color">Switch home now</h2>
    <div class="login-wrap">
        <div class="user-login-info">       
            <div class="form-group ">

                <select name="home" class="form-control fnt-size control_form">
                <option value="">Select Home</option>
                
                <?php foreach($homes as $home) { ?>
					<option value="{{ $home['id'] }}" >{{ $home['title'] }}</option>
				<?php } ?>	
               
                <!--<option value="1">Station Road</option>
                <option value="2">Garmoyle</option>
                <option value="3">Lily Road</option>
                <option value="4">Alton Road</option>
                <option value="5">Grey Road</option>
                <option value="6">New Alton Road</option>
                <option value="7">Miranda House</option>
                <option value="8">Hawthorne Road</option>
                <option value="9">Alexandra House</option>
                <option value="10">Gemini House</option> -->
              </select>
            </div>  
    </div>
    <div class="c-btn-group">
      <button class="btn btn-lg btn-login btn-block sub_btn" type="submit">Switch Home</button>
    </div>
</form>

<script >
    $(function() {
        $("#login_form").validate({ 
            rules: {
                company_name:"required",
            },           
            submitHandler: function(form) {
              form.submit();
            }
        }) 
    });
</script>
@endsection