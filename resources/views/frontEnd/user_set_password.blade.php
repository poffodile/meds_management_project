@extends('frontEnd.layouts.login')
@section('title','User Set Password')
@section('content')
    

<form class="form-signin" action="{{ url('users/set-password') }}" method="post" id="set_user_password">
    <!-- id="user_set_password" -->
    <h2 class="form-signin-heading">User Set Password</h2>
    <div class="login-wrap">
        <p>User Name</p>

        <input name="user_id" type="hidden" value="{{ $user_id }}">
        <input name="security_code" type="hidden" value="{{ $security_code }}">
        <input name="_token" type="hidden"  value="{{ csrf_token() }}">
        <input name="user_name" type="text" class="form-control" readonly="readonly" placeholder="UserName" value="{{ $user_name }}" style="background-color:white">
        <p>Enter your password below</p>
        <input name="password" type="password" class="form-control" placeholder="Password" id="password">
        <input name="confirm_password" type="password" class="form-control" placeholder="Re-type Password">
       
        <button class="btn btn-lg btn-login btn-block set-pass-btn" type="submit">Submit</button>

        <!-- <div class="registration">
            Already Registered.
            <a class="" href="login.html">
                Login 
            </a>
        </div> -->
    </div>
</form>


<script>
    $(function(){
        $('#set_user_password').validate({
            rules: {
                password:{
                    minlength:4,
                    required:true,
                    maxlength:20,
                    regex:"^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{4,}$"
                },
                confirm_password: {
                equalTo : "#password"
                }
            },
            messages: {
                password:{
                   regex:"Password contain at least 1 capital letter, 1 small and 1 number."
                }
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;  
    });
</script>

<!-- <script>
    $(function(){
        $('#set_user_password').validate({
            rules: {
                password: "required",
                confirm_password: {
                equalTo : "#password"
                }
            },
            messages: {
                password:"This field is required."   
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;  
    });
</script> -->
@endsection