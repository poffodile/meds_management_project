<!--header start-->
<style>
    .adv-table .clearfix {
    margin-bottom: 12px;
}
</style>
<header class="header fixed-top clearfix">
<!--logo start-->
<div class="brand">
    <a href="{{ url('/admin') }}" class="logo">
        <span style="color: white;">SCITS
    </a>
    <div class="sidebar-toggle-box">
        <div class="fa fa-bars"></div>
    </div>
</div>
<!--logo end-->
<div class="top-nav clearfix">
    <!--search & user info start-->
    <ul class="nav pull-right top-menu">
        <!-- <li>
            <input type="text" class="form-control search" placeholder=" Search">
        </li> -->
        <!-- user login dropdown start-->
        <?php $admin = Session::get('scitsAdminSession');
            $image = env('APP_URL').adminImgPath.'/default_user.jpg';
            if(!empty($admin->image)) {
                $image = env('APP_URL').adminImgPath.'/'.$admin->image;
            }
            $agent_name = '';
            if(Session::has('scitsAgentSession')){ 
                $agent_image = Session::get('scitsAgentSession')->image;
                $image = env('APP_URL').userProfileImagePath.'/'.$agent_image;
                $agent_name = Session::get('scitsAgentSession')->name;
                //echo "<pre>"; print_r($agent_name); die;
            }
        ?>
        <li class="dropdown">
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <img alt="" src="{{ $image }}"/>
                <span class="username">
                    @if(!empty($agent_name))
                       {{ $agent_name }}
                    @else
                        {{ $admin->name }}
                    @endif
                </span>
                <b class="caret"></b>
            </a>
            <ul class="dropdown-menu extended logout">
                <li><a href="{{ url('admin/profile') }}"><i class=" fa fa-suitcase"></i>Profile</a></li>
                <!-- <li><a href="#"><i class="fa fa-cog"></i> Settings</a></li> -->
                <li><a href="{{ url('admin/logout') }}"><i class="fa fa-key"></i> Log Out</a></li>
            </ul>
        </li>
        <!-- user login dropdown end -->
        <li>
            <!-- <div class="toggle-right-box">
                <div class="fa fa-bars"></div>
            </div> -->
        </li>
    </ul>
    <!--search & user info end-->
</div>
</header>
<!--header end-->