@php
$raw_home_id = Auth::user()->real_home_id;
$allowed_ids = array_filter(explode(',', str_replace(' ', '', $raw_home_id)));
@endphp

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

    .mrtp80 {
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
            <a href="{{ url('/roster') }}" class="logo"><img src="{{ url('public/images/n-logo1.jpg') }}"></a>
        </div>
        <!--logo end-->
        <div class="header-dys top-nav hr-top-nav cus-nav">
            <div class="col-md-8 col-sm-8 col-xs-12 col-lg-8">
                @php
                $design_layout_id = Auth::check() ? Auth::user()->design_layout : '0';
                @endphp

                @if(Auth::check() && count($allowed_ids) > 1)
                @php
                $allowed_homes = \App\Home::whereIn('id', $allowed_ids)->get();
                @endphp
                <div class="select-dyslexia" style="margin-right: 15px;">
                    <select class="form-control" style="background-color: #aec785; color: #fff; border: 1px solid #aec785;" onchange="window.location.href='{{ url('/switch_home_submit') }}?home='+this.value">
                        <option value="">Switch Home</option>
                        @foreach($allowed_homes as $home)
                        <option value="{{ $home->id }}" {{ Auth::user()->home_id == $home->id ? 'selected' : '' }}>{{ $home->title }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12 col-lg-4">
                <ul class="nav pull-left top-menu">
                    <!-- A is used for the Admin before it is for the Agent but now agent data should be removed -->
                    @if(Auth::user()->user_type == "O" || Auth::user()->user_type == "A")
                    <li style="list-style: none;">
                        <a href="{{ url('/admin') }}" class="btn allBtnUseColor" style="font-size: 12px !important; padding: 5px 12px !important; margin-right: 15px; border-radius: 4px !important; font-weight: bold; text-decoration: none; display: inline-block; margin-top: 3px;">
                            <i class="fa fa-cog"></i> Admin Panel
                        </a>
                    </li>
                    @endif
                    <!-- user login dropdown start-->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            @php
                            $user_image = Auth::user()->image ?: 'default_user.jpg';
                            $current_path = Request::path();
                            $user_id = Auth::user()->id;
                            @endphp
                            <!-- <img alt="" src="{{ userProfileImagePath.'/'.$user_image }}"> -->
                            <img alt="" src="{{ url('public/images/userProfileImages'.'/'.$user_image) }}">
                            <span class="username">{{ ucfirst(Auth::user()->name) }}</span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu extended logout">
                            <li><a href="{{ url('/my-profile/'.$user_id) }}"> <i class="fa fa-user-circle"></i> My Profile </a></li>
                            <li><a href="{{ url('/lock?path='.$current_path) }}"><i class="fa fa-lock"> </i> Lock</a></li>
                            <li><a href="{{ url('/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
                            <!-- Code given By Ethan start -->
                            <!-- @if(Auth::user()->user_type == "A" || Auth::user()->user_type == "M")
                            <li id="switch_menu_itm"><a href="{{ url('/switch_home') }}"><i class="fa fa-home"></i> Switch Home</a></li>
                            @endif -->
                            <!-- Code given By Ethan End -->
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

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
            var no_layout_id = "{{ url('/roster') }}";
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