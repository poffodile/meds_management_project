@extends('frontEnd.layouts.master')

@section('title','My Profile')

@section('content')

<style>
    img {
        background-color: white;
        width: 68%;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 2px;
    }

    .left-border {
        border-right: 1px solid #ddd;
    }

    .re-generateQR {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-right: 11px;
        margin-bottom: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .re-generateQR span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .re-generateQR span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .re-generateQR:hover span {
        padding-right: 20px;
    }

    .re-generateQR:hover span:after {
        opacity: 1;
        right: 0;
    }

    .view-QR {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-bottom: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .view-QR span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .view-QR span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .view-QR:hover span {
        padding-right: 20px;
    }

    .view-QR:hover span:after {
        opacity: 1;
        right: 0;
    }

    #download {
        width: 68%;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    #download button {
        border: 1px solid #1f88b5;
        box-shadow: none;
        color: #fff;
        background: #1f88b5;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-top: 20px;
        transition: all 0.5s;
        cursor: pointer;
    }

    #download button span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    #download button span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    #download button:hover span {
        padding-right: 20px;
    }

    #download button:hover span:after {
        opacity: 1;
        right: 0;
    }

    .set-width {
        width: 68%;
    }

    .profileDrop {
        background: #1f88b5;
        padding: 6px 10px;
        border-radius: 3px;
        color: #fff;
        font-size: 13px;
        text-decoration: none;
        transition: .6s;
        display: flex;
        line-height: 18px;
        align-items: center;
        gap: 6px;
    }

    .profileDrop:hover {
        background-color: #32323A;
        color: #fff !important;
    }

    .notifiNumberRadColor {
        width: 16px;
        height: 16px;
        background-color: #e10078;
        color: #fff;
        font-size: 10px;
        border-radius: 50%;
        text-align: center;
        line-height: 16px;
        margin: 2px;
        border: 1px solid #fff;
        font-weight: 500;
        letter-spacing: -1px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <!-- <div class="col-md-12">
                <div class="top_tabs">
                    <a href="#!" class="profileDrop"> <i class="fa fa-book"></i> My Diary</a>
                    <a href="#!" class="profileDrop" data-bs-toggle="modal" data-bs-target="#CRMHeaderPopup"><i class="fa fa-database"></i> CRM</a>
                    <a href="#!" class="profileDrop"><i class="fa fa-commenting"></i> Help Desk <span class="notifiNumberRadColor">2</span></a>
                    <a href="#!" class="profileDrop"><i class="fa fa-comment"></i> Messages </a>
                    <a href="#!" class="profileDrop"><i class="fa fa-bell"></i> Notifications <span class="notifiNumberRadColor">23</span> </a>
                    <a href="#!" class="profileDrop"><i class="fa fa-handshake-o"></i> Partners </a>
                </div>
            </div> -->
            @include('frontEnd.salesAndFinance.jobs.layout.secondheader')
        </div>
        <div class="row">
            <div class="col-md-12">
                <section class="panel">
                    <div class="panel-body profile-information">
                        <div class="col-md-2">
                            <?php
                            $user_image = $manager_profile->image;
                            if (empty($user_image)) {
                                $user_image = 'default_user.jpg';
                            }
                            ?>
                            <div class="profile-pic text-center">
                                <img src="{{ env('APP_URL') }}/{{ userProfileImagePath.'/'.$user_image }}" alt="" class="profile_staff" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="profile-desk" style="padding:0px">
                                <h1>{{ $manager_profile->name }}</h1>
                                <span class="text-muted">{{ $manager_profile->job_title }}</span>
                                <p class="top-def">{{ $manager_profile->description }}</p>
                                <p>
                                    <span class="profile-bigico" style="font-size:30px">
                                        <!-- <a href="#" title="Calendar"><i class="fa fa-calendar"></i></a> -->
                                        <!-- <a href="#" title="MFC" class="mfc"><i class="fa fa-user-times"></i></a> 
                                        <a href="#" title="LS" class="living-skill-list"><i class="fa fa-child"></i></a> 
                                        <a href="#" title="File Manager"><i class="fa fa-file-pdf-o"></i></a> -->
                                        <?php
                                        $url = "/";
                                        $logo_url = "/images/scits.png";
                                        $scits = "Scits";
                                        $facebook_slug = "https://www.facebook.com/login";
                                        $twitter_slug = "https://twitter.com/intent/tweet?url=http://www.socialcareitsolutions.co.uk&pictures";
                                        $instagram_slug = "https://instagram.com/share?url=http://www.socialcareitsolutions.co.uk&pictures";
                                        define('HTTP_ROOT', url('/'));
                                        ?>

                                        <a href="#" class="usd-icon add-petty-cash" title="Deposit petty cash" style="font-size:36px;"> <i class="fa fa-money"></i> </a>
                                        <a href="{{$facebook_slug}}" title="Share on Facebook"><i class="fa fa-facebook"></i></a>
                                        <a href="{{$twitter_slug}}" title="Share on Twitter"><i class="fa fa-twitter"></i></a>
                                        <a href="{{$instagram_slug}}" title="Share on Instagram"><i class="fa fa-instagram"></i></a>

                                    </span>
                                </p>
                                <p>
                            </div>
                        </div>
                        <div class="col-md-3 left-border">
                            <div class="profile-statistics">
                                <h1>{{ $manager_profile->payroll }}</h1>
                                <p>Pay Roll</p>
                                <h1>{{ $manager_profile->holiday_entitlement }}(Hrs)</h1>
                                <p>Holiday Entitlement</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="profile-statistics">
                                @if($qr_code_id == NULL) <button class="re-generateQR" onclick="generateQR(<?= $admin_id ?>);"><span>Generate QR</span></button> @else <button class="re-generateQR" onclick="generateQR(<?= $admin_id ?>);"><span>Re-Generate QR</span></button> <button class="view-QR" onclick="ViewQR(<?= $admin_id ?>);"><span>View</span></button> @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="qrcode"></div>
                            <div id='download'></div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-12">
                <section class="panel">
                    <header class="panel-heading tab-bg-dark-navy-blue cus-panel-heading">
                        <ul class="nav nav-tabs nav-justified ">
                            <li class="active">
                                <a data-toggle="tab" href="#overview"> Overview</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#manager_access_rights">Access Rights</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#my_profile_contacts" class="profile-contact-map"> Contacts</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#my_profile_info">Full Profile</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#profile_settings">Settings</a>
                            </li>
                        </ul>
                    </header>
                    <div class="panel-body">
                        <div class="tab-content tasi-tab">
                            <div id="overview" class="tab-pane active">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-4 col-xs-12 my_annual_record" manager_id="{{ $manager_id }}">
                                                <div class="profile-nav alt">
                                                    <a href="{{ url('/rota-dashboard') }}">
                                                        <section class="panel text-center">
                                                            <div class="user-heading alt wdgt-row purple-bg">
                                                                <i class="fa fa-files-o"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Manage<br>Annual Leave</h4>
                                                                    <p></p>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-4 col-xs-12">
                                                <div class="profile-nav alt">
                                                    <a href="{{ url('/rota-dashboard') }}">
                                                        <section class="panel text-center">
                                                            <div class="user-heading alt wdgt-row bg-blue">
                                                                <i class="fa fa-sliders"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Manage<br>Rota</h4>
                                                                    <p></p>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- fa-hourglass-end  fa-life-ring  fa-hourglass-half  fa-hourglass-start  fa-clock-o  fa-tachometer  fa-sliders   -->
                                            <div class="col-md-3 col-sm-4 col-xs-12" manager_id="{{ $manager_id }}">
                                                <div class="profile-nav alt">
                                                    <a href="{{ url('/rota-dashboard') }}">
                                                        <section class="panel text-center">
                                                            <div class="user-heading alt wdgt-row terques-bg">
                                                                <i class="fa fa-bed"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Manage<br>Sick Records</h4>
                                                                    <p></p>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-4 col-xs-12 my_task_allocation_list" manager_id="{{ $manager_id }}">
                                                <div class="profile-nav alt">
                                                    <section class="panel text-center">
                                                        <div class="user-heading alt wdgt-row red-bg">
                                                            <i class="fa fa-calendar"></i>
                                                        </div>
                                                        <div class="panel-body">
                                                            <div class="wdgt-value">
                                                                <h4 class="count">Task<br>Allocation</h4>
                                                                <p></p>
                                                            </div>
                                                        </div>
                                                    </section>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-4 col-xs-12" manager_id="{{ $manager_id }}">
                                                <div class="profile-nav alt">
                                                    <a href="{{ url('/my-profile/' . $manager_id . '/time-sheet') }}">
                                                        <section class="panel text-center">
                                                            <div class="user-heading alt wdgt-row label-inverse">
                                                                <i class="fa fa-file"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Add Time<br>  </h4>
                                                                    <p></p>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- <div class="col-md-2 col-sm-4 col-xs-12 leave_tracker_form">
                                                <div class="profile-nav alt">
                                                    <a href="{{ url('/my-profile/' . $manager_id . '/time-sheet') }}">
                                                        <section class="panel text-center profile-square">
                                                            <div class="user-heading alt wdgt-row label-inverse">
                                                                <i class="fa fa-file"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Add Leave<br>Tracker</h4>
                                                                    <p></p></div>
                                                            </div>
                                                        </section>
                                                    </a>
                                                </div>
                                            </div> -->

                                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="below-divider"></div>
                                            </div> -->
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="feed-box text-center"></div>
                                        <div class="profile-nav alt"></div>
                                        <!-- notification start -->
                                        <section class="panel">
                                            @include('frontEnd.common.notification_bar')
                                        </section>
                                        <!-- <section class="panel m-0">
                                            <header class="panel-heading"> Notification <span class="tools pull-right"> <a href="javascript:;" class="fa fa-chevron-down"></a> <a href="javascript:;" class="fa fa-cog"></a> <a href="javascript:;" class="fa fa-times"></a> </span> 
                                            </header>
                                            <div class="panel-body  min-ht-0">
                                            </div>
                                        </section> -->
                                    </div>
                                </div>
                            </div>
                            @include('frontEnd.personalManagement.elements.user_rights')
                            @include('frontEnd.personalManagement.elements.my_profile_contacts')
                            @include('frontEnd.personalManagement.elements.profile_detail')
                            @include('frontEnd.personalManagement.elements.profile_settings')
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!--  AddWeeklyMoney model start -->
<!-- <div class="modal fade" id="AddWeeklyMoney" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Set weekly allowance of Child</h4>
            </div>
            <form id="deposit_weekly_amount" method="post" action="{{ url('weekly-allowance/update') }}">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label class="col-md-4 m-t-20 text-right">Enter Amount</label>
                            <div class="col-md-6">
                                <div class="style-input m-t-15">
                                    <input type="text" name="amount_add" value="{{ $weekly_allowance }}" class="form-control">
                                </div>
                            </div>
                            <label class="col-md-12 m-t-10"> Note: This money will be automatically added to all the Child's account in every week.</label>
                            <div class="modal-footer recent-task-sec p-b-5">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                                <button class="btn btn-warning" type="submit"> Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>        
            </form>
        </div>
    </div>
</div> -->
<!--  AddWeeklyMoney model end -->
<script type="text/javascript">
    function facebook(url) {
        window.open(url, "myWindow", "status = 1, height = 462, width = 830,left = 100,top= 100, resizable = yes, scrolling=yes")
    }
    async function initMap() {
        const {
            Map
        } = await google.maps.importLibrary("maps");
    }
</script>


<!-- @include('frontEnd.personalManagement.elements.annual_leaves') -->
@include('frontEnd.personalManagement.elements.sick_leaves')
@include('frontEnd.personalManagement.elements.task_allocation')
@include('frontEnd.personalManagement.elements.change_password')
@include('frontEnd.personalManagement.elements.petty_cash')
@include('frontEnd.personalManagement.elements.petty_cash_detail')

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&AMP;sensor=false"></script>
<?php $api_key = env('GOOGLE_MAP_API_KEY'); ?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&callback=initMap">
</script>

<script>
    $(document).ready(function() {



        autosize($("textarea"));
        //google map
        function initialize() {

            var myLatlng = new google.maps.LatLng({
                {
                    $latitude = 37.7749;
                },
                {
                    $longitude = -122.4194;
                },
            });

            var mapOptions = {
                zoom: 15,
                scrollwheel: false,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }

            var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: '{{ $manager_profile->name }}'
            });
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        $('.profile-contact-map').click(function() {
            //google map in tab click initialize
            function initialize() {
                var myLatlng = new google.maps.LatLng({
                    {
                        $latitude
                    }
                }, {
                    {
                        $longitude
                    }
                });

                var mapOptions = {
                    zoom: 15,
                    scrollwheel: false,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }

                var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
                var marker = new google.maps.Marker({
                    position: myLatlng,
                    map: map,
                    title: '{{ $manager_profile->name }}'
                });
            }
            google.maps.event.addDomListener(window, 'click', initialize);
        });
    });
</script>

<script>
    // validations for weekly money allowance

    $(function() {
        $("#deposit_weekly_amount").validate({
            rules: {
                amount_add: {
                    required: true,
                    regex: /^[0-9 .]{1,10}$/
                },
            },

            messages: {
                amount_add: {
                    required: "This Field is required.",
                    regex: "This Field should contain numbers only."
                },
            },

            submitHandler: function(form) {
                form.submit();
            }
        })

        return false;
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function generateQR(id) {

        if (confirm("Are you sure you want to gerenarte re-generate QR code") == true) {
            var token = "<?= csrf_token() ?>";
            $.ajax({
                url: "{{ url('/qrcode') }}",
                type: "post",
                dataType: 'json',
                data: {
                    id: id,
                    val: 1,
                    _token: token
                },
                success: function(result) {
                    console.log(result);
                    document.getElementById('qrcode').innerHTML = "";
                    document.getElementById('download').innerHTML = "";

                    var qrcode = new QRCode(document.getElementById("qrcode"), {
                        text: `${result.qr_code_id}`,
                        width: 180, //default 128
                        height: 180,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                    document.getElementById('download').innerHTML = '<button onclick="myFunction()"><span>Download</span></button>';
                }
            });
        }
    }

    function ViewQR(id) {
        var token = "<?= csrf_token() ?>";
        $.ajax({
            url: "{{ url('/qrcode') }}",
            type: "post",
            dataType: 'json',
            data: {
                id: id,
                val: 0,
                _token: token
            },
            success: function(result) {
                console.log(result);
                document.getElementById('qrcode').innerHTML = "";
                document.getElementById('download').innerHTML = "";
                var qrcode = new QRCode(document.getElementById("qrcode"), {
                    text: `${result.qr_code_id}`,
                    width: 180, //default 128
                    height: 180,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                document.getElementById('download').innerHTML = '<button onclick="myFunction()"><span>Download</span></button>';
            }
        });
    }

    function myFunction() {
        let a = document.getElementById('qrcode')
        img = a.getElementsByTagName('img')[0];
        var element = document.createElement('a');
        element.setAttribute('href', img.src);
        element.setAttribute('download', 'qrcode.png');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
</script>

@endsection