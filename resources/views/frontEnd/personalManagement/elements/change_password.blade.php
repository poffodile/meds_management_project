
<!-- Change Password Modal -->

<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="float: left; width: 100%">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Change Password</h4>
            </div>
            <div class="modal-body">
                <div class="add-shifts">
           
                    <form class="form-horizontal" id="change_password_form">
                        @include('frontEnd.common.popup_alert_messages')
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Current Password : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="" style="width: 100%">
                                  <input name="current_password" required value="" class="form-control" placeholder='Current password' maxlength="30" type="password">
                                </div>
                                <div class="curr_pswrd_error" ></div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">New Password : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="" style="width: 100%">
                                  <input name="new_password" id="new_password" required value="" class="form-control" placeholder='New password' minlength="4" maxlength="30" type="password">
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Confirm Password : </label>
                            <div class="col-md-10 col-sm-9 col-xs-12">
                                <div class="" style="width: 100%">
                                  <input name="confirm_password" value="" class="form-control m-t-10" placeholder='Confirm password' maxlength="30" type="password">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer modal-bttm m-b-10">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <!-- <input type="hidden" name="home_id" value="{{ Auth::User()->home_id }}">
                            <input type="hidden" name="user_id" value="{{ Auth::User()->id }}"> -->

                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary ">Confirm</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $(document).on('click', '.chnge_passwrd_btn', function(){
            $('#change_password_form').find('input').val('');             
        });
    });
</script>
<script>
    $(function(){
        $('#change_password_form').validate({
            
            rules: {
                current_password: "required",
                new_password:{
                    required:true,
                    minlength:4,
                    maxlength:20,
                    regex:"^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{4,}$"
                },
                confirm_password: {
                    equalTo :"#new_password"
                }
            },
            messages:{
                new_password:{
                    regex:"New password contain at least 1 capital letter, 1 small and 1 number"
                },
                confirm_password:"New password and confirm password mismatch." 
            },
            submitHandler: function(form) {
    
                $('.loader').show();
                $('body').addClass('body-overflow');

                var formdata = $('#change_password_form').serialize();
                $.ajax({
                    type:'post', 
                    url :"{{ url('/profile/change-password') }}",
                    data: formdata,
                    //dataType:'json',
                    success: function(resp) {
                        if(isAuthenticated(resp) == false) {
                            return false;
                        }
                        
                        if(resp.response == "ok") {

                            $("#changePasswordModal").modal('hide');
                            $('.ajax-alert-suc').find('.msg').text('Password has been changed successfully.');
                            $('.ajax-alert-suc').show();
                            setTimeout(function(){$(".ajax-alert-suc").fadeOut()}, 5000); 

                            // $('span.popup_success_txt').text("Password changed successfully.");
                            // $('.popup_success').show();  

                        }   else if(resp.response=="not_correct") {
                            
                            $('.curr_pswrd_error').html("<label for='current_password' generated='true' class='error'>Current Password is incorrect.</label>");
                            
                        } else if(resp.response == "user_not_found"){

                            $('span.popup_error_txt').text("User not found.");
                            $('.popup_error').show();  
                            setTimeout(function(){$(".popup_error").fadeOut()}, 5000);
                        }
                        else {

                            $('span.popup_error_txt').text("{{ COMMON_ERROR }}");
                            $('.popup_error').show();
                            setTimeout(function(){$(".popup_error").fadeOut()}, 5000);
                            
                        }
                        // $("#changePasswordModal").modal('hide');
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                    }

                });
            }
        })
        return false;  
    });
</script>

<!-- <script>
    $(function(){
        $('#change_password_form').validate({
            rules: {
                current_password: "required",
                new_password: "required",
                confirm_password: {
                    required:true,
                    equalTo : "#new_password"
                },
            },
            messages: {
                current_password:"*required.",
                new_password:"*required.",
                confirm_password:{
                    equalTo:"Password and confirm password mismatch.",
                    required:"*required."
                }    
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;  
    });
</script> -->