@extends('frontEnd.layouts.login')
@section('title','Login')
@section('content')
<style>
    .bg_color {
        background-color: #0877bd !important;
        border-bottom: 10px solid #1d6797 !important;
    }

    .name_company:focus {
        border: 1px solid #1d6797 !important;
    }

    .control_form:focus {
        border: 1px solid #1d6797 !important;
    }

    .user-login-info .form-group:nth-of-type(3) .user_name:focus {
        border: 1px solid #1d6797 !important;
        border-color: #1d6797;
    }

    .user_name:focus {
        border: 1px solid #1d6797 !important;
    }

    .sub_btn {
        background-color: #aec785 !important;
    }

    .forget_pas {
        color: #1d6797 !important;
    }
</style>
<!-- <div class="form-signin" > -->
<form method="post" class="form-signin" action="{{ url('/login') }}" method="post" id="login_form" autocomplete="off">
    @csrf
    <h2 class="form-signin-heading bg_color">sign in now</h2>
    <div class="login-wrap">
        <div class="user-login-info">

            @include('frontEnd.common.popup_alert_messages')

            <!-- This is just for avoid functionaliy of remember password -->
            <div class="inp-hide">
                <input type="password" name="password" />
                <input type="text" name="username" />
            </div>

            <div class="form-group ">
                <input type="text" name="company_name" autocomplete="off" class="form-control name_company" placeholder="Company Name" autofocus>
            </div>
            <div class="company_error"></div>

            <div class="form-group ">
                <select name="home" class="form-control inp-hide fnt-size control_form">
                    <option value="">Select Home</option>
                </select>
            </div>

            <div class="form-group">
                <input type="text" name="username" style="" required class="form-control inp-hide user_name" placeholder="Username" required autocomplete="off">
            </div>

            <div class="form-group">
                <input type="password" name="password" required class="form-control inp-hide user_pass" placeholder="Password" required autocomplete="off">
            </div>

            <label class="checkbox">
                <!-- <input type="checkbox" value="remember-me"> Remember me -->
                <span class="pull-right">
                    <a data-toggle="modal" class="forget_pas" href="#forgotPasswordModal"> Forgot Password?</a>
                </span>

            </label>



        </div>
        <div class="c-btn-group">
            <button class="btn btn-lg btn-login btn-block inp-hide sub_btn" type="submit">Sign in</button>
        </div>
</form>
<!-- <div> -->


<!-- Forgot Password Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="forgotPasswordModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ url('/forgot-password') }}" class="" id="forgot_pswrd_form">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Forgot Password ?</h4>
                </div>
                <div class="modal-body">
                    <p>Enter your e-mail address below to reset your password.</p>
                    <input type="email" name="email" required autocomplete="off" placeholder="Email" class="form-control placeholder-no-fix">
                </div>
                <div class="modal-body email_error"></div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <button class="btn btn-success " type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Forgot Password Modal -->
<?php $all_companies = App\Admin::select('id', 'company')
    ->where('access_type', 'O')
    ->where('is_deleted', '0')
    ->get()
    ->toArray();
// echo "<pre>"; print_r($all_companies); die;
?>
<!-- Agent Login Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="AgentLoginModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ url('/agent/login')}}" class="" id="agent_login_form">
                @csrf
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Agent Login</h4>
                </div>
                <div class="modal-body">
                    <p>Select Company </p>
                    <select class="form-control placeholder-no-fix" name="company_id">
                        <option value="">Select Company</option>
                        <?php foreach ($all_companies as $key => $value) { ?>
                            <option value="{{$value['id']}}">{{ $value['company']}}</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="modal-body">
                    <p>Enter your username </p>
                    <input type="name" name="username" required autocomplete="off" placeholder="Username" class="form-control placeholder-no-fix">
                </div>
                <div class="modal-body">
                    <p>Enter your password </p>
                    <input type="password" name="password" required autocomplete="off" placeholder="Password" class="form-control placeholder-no-fix">
                </div>


                <div class="modal-body email_error"></div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <button class="btn btn-success " type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Agent login Modal -->
<script>
    $(document).ready(function() {
        $('input[name=\'company_name\']').keydown(function(event) {

            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {

                var company_select = $(this);
                var company_name = company_select.val();
                if (company_name == '') {
                    $('.company_error').html('<label for="company_name" generated="true" class="error">*This field is required.</label>');
                    return false;
                } else {
                    $('.company_error').html('');
                }

                if (company_name === "scits super admin") {

                    $('.company_error').html('');
                    $('select[name=\'home\']').closest('.form-group').addClass('inp-hide');
                    $('input[name=\'username\']').closest('.form-group').removeClass('inp-hide');
                    $('input[name=\'password\']').closest('.form-group').removeClass('inp-hide');
                    // $('input[name=\'username\']').removeClass('inp-hide');
                    // $('input[name=\'password\']').removeClass('inp-hide');
                    $('.btn-login').removeClass('inp-hide');

                    return false;
                }

                company_select.addClass('spinner');

                $.ajax({
                    type: 'get',
                    url: "{{ url('get-homes') }}" + '/' + company_name,
                    success: function(resp) {

                        if (resp != '') {

                            $('select[name=\'home\'] option').remove();
                            $('select[name=\'home\']').append('<option value="">Select Home</option>' + resp);
                            $('select[name=\'home\']').removeClass('inp-hide');


                        } else {
                            $('.company_error').html('<label for="company_name" generated="true" class="error">Company Name is not correct.</label>');
                            $('select[name=\'home\']').addClass('inp-hide');
                            $('input[name=\'username\']').addClass('inp-hide');
                            $('input[name=\'password\']').addClass('inp-hide');
                            $('.btn-login').addClass('inp-hide');
                            $('.login-wrap').removeClass('login-trans-bg');
                        }
                        company_select.removeClass('spinner');
                    }
                });
                return false;
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('select[name=\'home\']').change(function(event) {
            var home_id = $(this).val();
            if (home_id != '') {
                $('input[name=\'username\']').removeClass('inp-hide');
                $('input[name=\'password\']').removeClass('inp-hide');
                $('.btn-login').removeClass('inp-hide');
                $('.login-wrap').addClass('login-trans-bg');
            } else {
                $('input[name=\'username\']').addClass('inp-hide');
                $('input[name=\'password\']').addClass('inp-hide');
                $('.btn-login').addClass('inp-hide');
                $('.login-wrap').removeClass('login-trans-bg');
            }
            return false;

        });
    });
</script>

<script>
    $(function() {
        $("#login_form").validate({
            rules: {
                company_name: "required",
                username: "required",
                password: "required"
            },

            submitHandler: function(form) {
                form.submit();
            }
        })
    });
</script>

<script>
    $(function() {
        $('#forgot_pswrd_form').validate({

            rules: {
                email: {
                    required: true,
                    email: true,
                    remote: "{{ url('/check-email-exists') }}"
                },
            },
            messages: {
                email: {
                    required: "This Field is required.",
                    email: "This Email is not valid.",
                    remote: "User with this email does not exist."
                },
            },
            submitHandler: function(form) {

                form.submit();

            }
        })
    });
</script>
<script>
    // $("#yes_logout").on('click',function(){
    //     $.ajax({
    //         url: "{{url('/yes_logout')}}",
    //         method: "POST",
    //         data: {_token:"{{csrf_token()}}"},
    //         success: function (response) {
    //             console.log(response);
                
    //             if (response.success === true) {
    //             //    alert("Please try to login again");
    //                 location.reload();
    //             } else {
    //                 alert("Error: " + response.message);
    //             }
    //         },
    //         error: function (xhr) {
    //             if (xhr.status === 422) {
    //                 let errors = xhr.responseJSON.errors;
    //                 let errorMessages = '';

    //                 $.each(errors, function (key, value) {
    //                     errorMessages += value[0] + "\n";
    //                 });
    //                 alert(errorMessages);
    //             } else {
    //                 alert("Something went wrong. Please try again.");
    //             }
    //         }
    //     });
    // });
    $("#no_logout").on('click',function(){
         $.ajax({
            url: "{{url('/no_logout')}}", // Laravel route or API endpoint
            method: "POST",
            data: {_token:"{{csrf_token()}}"},
            success: function (response) {
                console.log(response);
                
                if (response.success === true) {
                    location.reload();
                } else {
                    alert("Something went wrong");
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                   
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';

                    $.each(errors, function (key, value) {
                        errorMessages += value[0] + "\n";
                    });
                    alert(errorMessages);
                } else {
                    alert("Something went wrong. Please try again.");
                }
            }
        });
    });
</script>


@endsection