@extends('frontEnd.layouts.login')
@section('title','Login')
@section('content')

<div class="formLoginSignin">
    <div class="">
        <div class="loginFormLogo">
            <img src="{{ asset('public/images/scits1.png') }}" alt="" />
        </div>
        <form method="post" class="form-signin" action="{{ url('admin/login') }}" method="post" id="login_form">
            @csrf
            <h2 class="form-signin-heading">sign in now</h2>
            <div class="login-wrap">
                <div class="user-login-info">

                    <!-- This is just for avoid functionaliy of remember password -->
                    <div class="inp-hide">
                        <input type="password" name="password" />
                        <input type="text" name="username" />
                    </div>

                    <div class="form-group ">
                        <input type="text" name="company_name" autocomplete="off" class="form-control " placeholder="Company Name" autofocus maxlength="255">
                    </div>
                    <div class="company_error"></div>

                    <div class="form-group inp-hide">
                        <select name="home" class="form-control fnt-size">
                            <option value="">Select Home</option>
                        </select>
                    </div>

                    <div class="form-group inp-hide">
                        <input type="text" name="username" class="form-control" placeholder="Username" autofocus maxlength="255">
                    </div>

                    <div class="form-group inp-hide">
                        <input type="password" name="password" class="form-control" placeholder="Password" maxlength="255">
                    </div>

                    <label class="checkbox">
                        <!-- <input type="checkbox" value="remember-me"> Remember me -->
                        <span class="pull-right">
                            <a data-toggle="modal" href="" class="forgot_pswrd"> Forgot Password?</a>
                        </span>
                    </label>

                    <!-- <div class="registration">
                    Don't have an account yet?
                    <a class="" href="registration.html">
                        Create an account
                    </a>
                </div> -->

                </div>
                <div class="c-btn-group">
                    <button class="btn btn-lg btn-login btn-block inp-hide" type="submit">Sign in</button>
                </div>
        </form>
    </div>
</div>

<!-- BACKEND Forgot Password Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="forgotPasswordModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ url('admin/forgot-password') }}" class="" id="forgot_pswrd_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Forgot Password ?</h4>
                </div>
                <div class="modal-body">
                    <p>Enter your e-mail address below to reset your password.</p>
                    <input type="email" name="email" required placeholder="Email" class="form-control placeholder-no-fix">
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="_token" value="">
                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                    <button class="btn btn-success" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- modal -->


<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.forgot_pswrd', function() {

            $('#forgot_pswrd_form').find('input').val('');
            //Error Message Hide
            $('.error').text('');
            $('input[name=\'_token\']').val("{{ csrf_token() }}");
            $('#forgotPasswordModal').modal('show');
        });
    });
</script>

<!-- VALIDATION -->
<script type="text/javascript">
    $(function() {
        $('#forgot_pswrd_form').validate({

            rules: {
                email: {
                    required: true,
                    email: true,
                    remote: "{{ url('admin/check-email-exists') }}"
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
        });
        return false;
    });
</script>

<script>
    $(document).ready(function() {
        $('input[name=\'company_name\']').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {

                var company_select = $(this);
                var company_name = company_select.val();

                if (company_name == '') {
                    $('.company_error').html('<label for="company_name" generated="true" class="error">This field is required.</label>');
                    return false;
                } else {
                    $('.company_error').html('');
                }

                if (company_name == "scits super admin") {

                    $('.company_error').html('');
                    $('select[name=\'home\']').closest('.form-group').addClass('inp-hide');
                    $('input[name=\'username\']').closest('.form-group').removeClass('inp-hide');
                    $('input[name=\'password\']').closest('.form-group').removeClass('inp-hide');
                    $('.btn-login').removeClass('inp-hide');

                    return false;
                }

                company_select.addClass('spinner');

                $.ajax({
                    type: 'get',
                    url: "{{ url('admin/get-homes') }}" + '/' + company_name,
                    success: function(resp) {

                        if (resp != '') {
                            $('.company_error').html('');
                            $('select[name=\'home\'] option').remove();
                            $('select[name=\'home\']').append('<option value="">Select Home</option>' + resp);
                            $('select[name=\'home\']').closest('.form-group').removeClass('inp-hide');
                        } else {
                            $('.company_error').html('<label for="company_name" generated="true" class="error">Company Name is not correct.</label>');
                            $('select[name=\'home\']').closest('.form-group').addClass('inp-hide');
                            $('input[name=\'username\']').closest('.form-group').addClass('inp-hide');
                            $('input[name=\'password\']').closest('.form-group').addClass('inp-hide');
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
                $('input[name=\'username\']').closest('.form-group').removeClass('inp-hide');
                $('input[name=\'password\']').closest('.form-group').removeClass('inp-hide');
                $('.btn-login').removeClass('inp-hide');
                $('.login-wrap').addClass('login-trans-bg');
            } else {
                $('input[name=\'username\']').closest('.form-group').addClass('inp-hide');
                $('input[name=\'password\']').closest('.form-group').addClass('inp-hide');
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
            messages: {
                company_name: "This field is required.",
                username: "This field is required.",
                password: "This field is required."

            },
            submitHandler: function(form) {
                form.submit();
            }
        })
    });
</script>

@endsection