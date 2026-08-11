@extends('frontEnd.layouts.login')
@section('title','Admin Set Password')
@section('content')
    

<form class="form-signin" action="{{ url('admin/system-admin/set-password') }}" method="post" id="set_password_form">
<!-- id="user_set_password" -->
    <h2 class="form-signin-heading">Admin Set Password</h2>
    <div class="login-wrap">
        <p>User Name</p>

        <input name="system_admin_id" type="hidden" value="{{ $system_admin_id }}">
        <input name="security_code" type="hidden" value="{{ $security_code }}">
        <input name="_token" type="hidden"  value="{{ csrf_token() }}">
        <input name="user_name" type="text" class="form-control" readonly="readonly" placeholder="UserName" value="{{ $user_name }}" style="background-color:white">
        <p>Enter your password below</p>
        <input name="password" type="password" class="form-control" placeholder="Password" id="password" minlength="4" maxlength="20">
        <input name="confirm_password" type="password" class="form-control" placeholder="Re-type Password" minlength="4" maxlength="20">
    
        <button class="btn btn-lg btn-login btn-block set-pass-btn" type="submit">Submit</button>

        <!-- <div class="registration">
            Already Registered.
            <a class="" href="login.html">
                Login 
            </a>
        </div> -->
    </div>
</form>

<script type="text/javascript">
/*  $(document).ready(function(){
        
        //$(set-pass-btn
        var password = $('input[name=\'password\']').val();
        var confirm_password = $('input[name=\'confirm_password\']').val();
         alert(password);
    });*/
</script>


<script>
    $(function(){
        $('#set_password_form').validate({
            rules: {
                password: "required",
                confirm_password: {
                equalTo : "#password"
                }
            },
            messages: {
                password:"This field is required.",
                confirm_password:"Password and confirm password mismatch."   
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;  
    });
</script>

<!-- <script>
// just for the demos, avoids form submit
    jQuery.validator.setDefaults({
      debug: true,
      success: "valid"
    });
    $( "#set_password_form" ).validate({
        rules: {
        password: "required",
        confirm_password: {
          equalTo: "#password"
        }
        }
    });
</script> -->


@endsection