<?php
// $alert_dynamic_form = App\DynamicForm::alertDynamicForm();
//     echo "<pre>"; print_r($alert_dynamic_form); die;
?>

<style type="text/css">
    .header-dys {
        width: 60%;
        float: right;
    }

    .select-dyslexia {
        float: right;
    }

    .select-dyslexia select {
        background-color: #1f88b5;
    }

    .select-dyslexia select>option {
        background-color: #1f88b5;
    }

    .form-group.has-feedback {
        padding: 0px 15px 10px 15px;
    }

    .sel_design_layout {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
    }
    .top-nav img {
        border: 2px solid #d9d9d9;
        object-fit: cover;
    }
    .uploadPopImg img {
        width: 100%;
        margin-bottom: 10px;
    }
    .uploadPopImg {
        width: 120px;
    }
    label.col-lg-2.control-label.mrtp70 {
        margin-top: 40px;
    }
    .mrtp80{
        margin-top: 46px;
    }
</style>
<!--header start-->
<header class="header fixed-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle hr-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="fa fa-bars"></span>
        </button>
        <!--logo start-->
        <div class="brand ">
            <a href="{{ url('/') }}" class="logo">
                <span style="color: white;">SCITS </span>
            </a>
        </div>
        <!--logo end-->
        <div class="horizontal-menu navbar-collapse collapse">
            <div class="wlcome-header"> Welcome Back, </div>
        </div>
        <div class="header-dys top-nav hr-top-nav cus-nav">
            <div class="col-md-8 col-sm-8 col-xs-12 col-lg-8">
                <?php
                if (Auth::check()) {
                    $design_layout_id = Auth::user()->design_layout;
                } else {
                    $design_layout_id = '0';
                }
                ?>
                <div class="select-dyslexia">
                    <select class="form-control sel_design_layout" name="design_layout_id">
                        <option value="0" <?php if (isset($design_layout_id)) {
                                                if ($design_layout_id == '0') {
                                                    echo "selected";
                                                }
                                            } ?>>Default</option>
                        <option value="1" <?php if (isset($design_layout_id)) {
                                                if ($design_layout_id == '1') {
                                                    echo "selected";
                                                }
                                            } ?>>Dyslexia</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-lg-4">
                <ul class="nav pull-left top-menu">
                    <!-- user login dropdown start-->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <?php
                            $user_image = Auth::user()->image;
                            if (empty($user_image)) {
                                $user_image = 'default_user.jpg';
                            }
                            $current_path = Request::path();
                           
                            $user_id = Auth::user()->id;
                            ?>
                            <!-- <img alt="" src="{{ userProfileImagePath.'/'.$user_image }}"> -->
                            <img alt="" src="{{ url('public/images/userProfileImages'.'/'.$user_image) }}">
                            <span class="username">{{ ucfirst(Auth::user()->name) }}</span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu extended logout">
                            <li><a href="{{ url('/my-profile/'.$user_id) }}"> <i class="fa fa-user-circle"></i> My Profile </a></li>
                            <li><a href="#" class="add_user"> <i class=" fa fa-user"></i> Add user </a></li>
                            <li><a href="{{ url('/forms') }}" data-toggle="modal"> <i class="fa fa-bolt"></i> Forms </a></li>
                            <li><a href="{{ url('/general-admin') }}"><i class="fa fa-cogs"></i> General Admin </a></li>
                            <li><a href="{{ url('/lock?path='.$current_path) }}"><i class="fa fa-lock"> </i> Lock</a></li>
                            <li><a href="#" class="hndovr_logbk"><i class="fa fa-address-book-o"></i> Hand Over </a></li>
                            <li><a href="{{ url('/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
                            <!-- Code given By Ethan start -->
                            @if(Auth::user()->user_type == "A")
                            <li id="switch_menu_itm"><a href="{{ url('/switch_home') }}"><i class="fa fa-home"></i> Switch Home</a></li>
                            @endif
                            <!-- Code given By Ethan End -->
                        </ul>
                    </li>
                    <!-- user login dropdown end -->
                </ul>
            </div>
        </div>
    </div>
</header>
<!--header end-->
@include('frontEnd.common.add_user')
@include('frontEnd.common.dynamic_forms')
@include('frontEnd.common.handover_logbook')

<script>
    $(".add_user").click(function() {
        $('#addServiceUserModal').modal('show');
    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('change', '.sel_design_layout', function() {

            var design_layout_id = $('select[name=design_layout_id]').val();
            var normal_layout_id = "{{ url('/change-design-layout/0') }}";
            var dyslexia_layout_id = "{{ url('/change-design-layout/1') }}";
            var no_layout_id = "{{ url('/') }}";
            if (design_layout_id == '0') {
                location.href = normal_layout_id;
            } else if (design_layout_id == '1') {
                location.href = dyslexia_layout_id;
            } else {
                location.href = no_layout_id;
            }
        });
    });
</script>


<script type="text/javascript">
    $(".hndovr_logbk").click(function() {
        $('.submt-srvc-user').click();
        // $(document).on('click','.hndovr_logbk',function(){
        // $('#ServiceUserlistModal').modal('show');
        $('#HandoverlogBookModal').modal('show');
    });
</script>