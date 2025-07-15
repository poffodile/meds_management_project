<?php 
    $name = '/default_user.jpg';
    $default_img = url(careTeam.$name);
?>

<div class="col-md-12">
    <div class="prf-box">
        <h3 class="prf-border-head">Care team <a href="#" class="btn btn-white clr-blue plus-ryt" data-toggle="modal" data-target="#care_team_add_"> <i class="fa fa-plus plus-icn"></i></a>  
        </h3>
        
        <?php foreach($care_team as $member){
        
            $image = url(careTeam.$name);
            if(!empty($member->image)){
                $image = url(careTeam.'/'.$member->image);
            }
        ?>
        <div class=" wk-progress tm-membr">
            <div class="col-md-2 col-xs-2">
                <div class="tm-avatar">
                    <img src="{{ $image }}" alt=""/>
                </div>
            </div>
            <div class="col-md-7 col-xs-7">
                <span class="tm">{{ $member->name }}</span>
            </div>
            <div class="col-md-3 col-xs-3"> 
                <a href="#" class="btn btn-white care_team_view_mdl" data-toggle="modal" data-target="#care_team_View_{{ $member->id }}">Contact</a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!--Add Care Team popup -->
<div class="modal fade" id="care_team_add_" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Add Care Team</h4>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ url('/service/care_team/add/'.$service_user_id) }}" enctype="multipart/form-data" id='add_care_team'>
                    <div class="row">

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7  r-p-0"> Staff Member </label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0 ">
                                    <div class="select-style">
                                        <select name="staff_member_id">
                                            <option value="">Select Staff Member</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user['id'] }}" staff_name="{{ $user['name'] }}" staff_email="{{ $user['email'] }}" phone_no="{{ $user['phone_no'] }}" image="{{ $user['image'] }}" class="sel_staff">{{ $user['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0">Name</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control" value="" name="name" maxlength="255" id="staff_name" />
                                    </div>
                                </div>
                            </div>
                        </div>
                       <!--  <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0"> Job Title</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control" name="job_title" value="" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7  r-p-0"> Job Title </label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0 ">
                                    <div class="select-style">
                                        <select name="job_title">
                                            <option value="">Select</option>
                                            @foreach($care_team_job_title as $value)
                                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0">Phone No</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control" value="" name="phone_no" maxlength="15" id="phone_no" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0">Email</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control" value="" name="email" maxlength="255" id="staff_email" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0">Address</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <textarea name="address" class="form-control" rows="3" cols="20" name="address" maxlength="1000"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">         
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 r-p-0">Image</label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0">
                                    <div class="fileupload fileupload-new input-group popovr" data-provides="fileupload">
                                        <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                            <img src="{{ $default_img }}" alt="No Image" class="temp_img" id="staff_img_src"/>
                                        </div>
                                        <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                        </div>
                                        <div class="btn-file">
                                            <span class="btn btn-white ">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                            </span>
                                            <input name="image" type="file" class="default" id="img_upload1">
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer modal-bttm m-b-10">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="staff_image_name" value="" class="staff_image_name">
                            <button class="btn btn-default cancel-btn" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            <button class="btn btn-warning" type="submit"> Confirm </button>
                        </div>
                    </div>
                </form>
            </div>           
        </div>
    </div>
 </div>

<?php    
    foreach ($care_team as $team) { 
        $image1 = url(careTeam.$name);
        if(!empty($team->image)) {
            $image1 = url(careTeam.'/'.$team->image);
    }
?>

<!--View Care Team popup -->
<div class="modal fade" id="care_team_View_{{ $team->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn-edit" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a href="" style="font-size:18px; padding-right:8px;" class="close team-edit edit_care_team_btn"><i class="fa fa-pencil" title="Edit"></i></a>
                <a href="{{ url('/service/care_team/delete/'.$team->id) }}" style="font-size:18px; padding-right:8px;" class="close delete_care_team_btn" ><i class="fa fa-trash" title="Delete"></i></a>
                <h4 class="modal-title">Care Team</h4>
            </div>
            <div class="modal-body">
                <div class="row">         
                    <form method="post" action="{{ url('/service/care_team/edit') }}" enctype="multipart/form-data" id='edit_care_team_validation'>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Name :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" name="name" class="form-control edit_care_team" disabled="disabled" value="{{ $team->name }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7  r-p-0"> Job Title </label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0 p-b-5">
                                    <div class="select-style edit_care_team">
                                        <select name="job_title" disabled="disabled" id="select-j-title" class="edit_care_team">
                                            <option value="">Select</option>
                                            @foreach($care_team_job_title as $value)
                                                <option value="{{ $value->id }}" {{ ($team->job_title_id == $value->id) ? 'selected' : '' }} >
                                                {{ $value->title }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Phone No.</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" name="phone_no" class="form-control edit_care_team" disabled="disabled" value="{{ $team->phone_no }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Email :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control edit_care_team" name="email" disabled="disabled" value="{{ $team->email }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Address :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0 ">
                                    <div class="input-group popovr">
                                        <textarea name="address" class="form-control edit_care_team" disabled="disabled" rows="3" cols="20" maxlength="1000" id="ct_info">{{ $team->address }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>     

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">         
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Image :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="fileupload fileupload-new input-group popovr" data-provides="fileupload">
                                        <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                            <img src="{{ $image1 }}" alt="No Image" class="temp_img" id="old_image1"/>
                                        </div>
                                        <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                        </div>
                                        <div class="btn-file edit-team-image" style="display:none;">
                                            <span class="btn btn-white ">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                            </span>
                                            <input name="image" type="file" class="default" id="img_upload1">
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer modal-bttm m-t-0" style="visibility:hidden">
                            <input type="hidden" name="care_team_id" value="{{ $team->id }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-default cancel-btn-edit" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            <button class="btn btn-warning" type="submit"> Submit </button>
                        </div>
                    </form>
                </div>
            </div>           
        </div>
    </div>
 </div>
<?php } ?>

<script>
    //making editable 
    $(document).ready(function() {
        $(document).on('click','.edit_care_team_btn', function(){

            $(this).parent().next('div').find('.modal-footer').removeAttr('style');
            $(this).parent().next('div').find('.edit_care_team').removeAttr('disabled');
            //  $('#select-j-title').removeAttr('disabled');
            $('.edit-team-image').css('display','block');
            autosize($("textarea"));
            return false;
        });
    });
</script>

<script type="text/javascript">
    $(document).on('click','.care_team_view_mdl', function(){
        setTimeout(function () {
            var elmnt = document.getElementById("ct_info");
            var scroll_height = elmnt.scrollHeight;
            if(scroll_height == '0') { 
                $('#ct_info').height('100');
            } else {
                $('#ct_info').height(scroll_height);
            }
            console.log(scroll_height);
        },200);
    });
</script>

<script>
    //delete team member
    $(document).ready(function(){
        $('.delete_care_team_btn').click(function(){
           if(confirm('Do you want to delete this care team member ?')){
            //window.location="{{ url('/service/care_team/delete/') }}"+'/'+care_team_id;
            } else{
                return false;
            }
        });
    });
</script>

<script>
    $(function() {
        $("#add_care_team").validate({
            rules: {
                
                job_title: {
                    required: true,  
                    //regex: /^[a-zA-Z'\s]{1,40}$/ 
                    regex: /^[a-zA-Z0-9\s]{1,40}$/          
                },
                name: {
                    required: true,
                    regex: /^[a-zA-Z'.\s]{1,40}$/
                },
                email: {
                    //required: true,
                    email: true
                },
                address:{ 
                    required: true,
                    regex: /^[a-zA-Z0-9'#-,.\s]{1,1000}$/
                },
                // image:{
                //     required: true
                // },
                phone_no:{
                    required: true,
                    regex: /^[0-9+\s]{10,13}$/
                },
                // staff_member_id:{
                //     required: true,
                // }

            },
            messages: {
                job_title: "This field is required.",
                name: "This field is required.",
                // email: "This field is required.",
                // image: "This field is required.",
                phone_no:{
                    required: "This field is required.",
                    regex: "Phone number is invalid."
                },
                address:{
                    required: "This field is required.",
                    regex: "This field should contain maximum 1000 characters."
                },
                // staff_member_id:{
                //     required: "This field is required.",
                //     // regex: "This field should contain maximum 1000 characters."
                // }    
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;   
    });
</script>

<script>
    $(function() {
        $("#edit_care_team_validation").validate({
            rules: {
                
                job_title: {
                    required: true,  
                    regex: /^[a-zA-Z0-9\s]{1,40}$/          
                },
                name: {
                    required: true,
                    regex: /^[a-zA-Z'.\s]{1,40}$/
                },
                email: {
                    //required: true,
                    email: true
                },
                address:{ 
                    required: true,
                    regex: /^[a-zA-Z0-9'#,-.\s]{1,1000}$/
                },
                phone_no:{
                    required: true,
                    regex: /^[0-9+\s]{10,13}$/
                }
            },
            messages: {
                job_title: "This field is required.",
                name: "This field is required.",
                // email: "This field is required.",
                address:{
                    required: "This field is required.",
                    regex: "This field should contain maximum 1000 characters."
                },
                phone_no:{
                    required: "This field is required.",
                    regex: "Phone number is invalid."
                }
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;   
    });
</script>

<script>
    $(document).ready(function(){  
        $(document).on('click','.cancel-btn',function(){
            $('#add_care_team').find('input').val('');
            $('#add_care_team').find('textarea').val('');
            $('#add_care_team').find('img').attr('src','');
            $('label.error').hide();
            var token = "{{ csrf_token() }}";
            $('input[name=\'_token\']').val(token);
        });
    });
</script>

<script>
    $(document).ready(function()
    {
        $("#img_upload1").change(function()
        {   
            var img_name = $(this).val();
            if(img_name != "" && img_name!=null)
            {
                var img_arr=img_name.split('.');
                var ext = img_arr.pop();
                ext     = ext.toLowerCase();
                if(ext =="jpg" || ext =="jpeg" || ext =="gif" || ext =="png")
                {
                    input=document.getElementById('img_upload1');
                    if(input.files[0].size > 2097152 || input.files[0].size <  10240)
                    {
                      $(this).val('');
                      $("#img_upload1").removeAttr("src");
                      alert("image size should be at least 10KB and upto 2MB");
                      return false;
                    }
                }
                else
                {
                    $(this).val('');
                    alert('Please select an image .jpg, .png, .gif file format type.');
                }
            }
        return true;
        }); 
    });
</script>

<script>
    $(document).ready(function() {

        $(document).on('click','.sel_staff',function(){

            var abc         = $(this);
            var staff_id    = abc.val();
            var staff_name  = abc.attr('staff_name');
            var staff_email = abc.attr('staff_email');
            var phone_no    = abc.attr('phone_no');
            var image       = abc.attr('image');

            $('#staff_name').val(staff_name);
            $('#staff_email').val(staff_email);
            $('#phone_no').val(phone_no);
            $('.staff_image_name').val(image);
            
            var img = "{{ userProfileImagePath }}";
            var preview_image = '';

            if(image == '' && image == null) {
                preview_image = img+'/default_user.jpg';
            } else {
                preview_image = img+'/'+image;
            }
            $('#staff_img_src').attr('src', preview_image);

        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        var image = $('.sel_staff').attr('image');
        $('.staff_image_name').val(image);
    });
</script>