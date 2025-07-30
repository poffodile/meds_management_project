<!-- <script src="{{ url('public/frontEnd/js/moment-timezone.js') }}"></script> -->
<!-- <script type="text/javascript" src="{{ url('public/frontEnd/js/date.js') }}"></script> -->

<div class="col-md-5 col-sm-5 col-xs-12">

    <div class="profile-nav alt">

        <section class="panel">

            <div class="user-heading alt clock-row bg-themecolor">

                <!-- <h1>{{ date('F d') }}  <a href="{{ url('/system/calendar') }}"><?php if (isset($page)) {
                                                                                        if ($page = 'dashboard') { ?> <i class="fa fa-calendar pull-right"></i> <?php }
                                                                                                                                                        } ?> </a></h1> -->

                <p class="text-left">{{ date('Y, l') }}</p>

                <?php $hour = date('h');

                // $new_hour = $hour-3;

                $new_hour = $hour;

                ?>

                <p class="text-left"><span id="hh">{{ $new_hour }}</span>:<span id="mm">{{ date('i') }}</span> <span id="aa">{{ date('A') }}</span></p>

            </div>

            <ul id="clock">

                <li id="sec"></li>

                <li id="hour"></li>

                <li id="min"></li>

            </ul>



            <div class="below-clock">

                <div>

                    <i class="fa fa-key"></i>

                    <div class="key-sec cus-key-sec">

                        <h4>Section Key</h4>

                        <ul type="none">

                            <li> <span class="clr-prple"></span>Incentive Scheme</li>

                            <li><span class="clr-grn"></span> Health Record </li>

                            <li><span class="clr-skyblu"></span> Daily Record </li>

                            <li><span class="clr-ornge"></span> Placement Plan </li>

                            <li><span class="clr-red"></span> Needs Urgents attention </li>

                        </ul>

                    </div>

                </div>

                <!-- <ul class="below-list" type="none">

                    <li> <span class="bg-red"></span>  Ian Williams - Management Plans </li>

                    <li> <span class="bg-red"></span>  Jeremy Fisher - Management Plans </li>

                    <li> <span class="bg-red"></span>  Steven Hall - Management Plans </li>



                </ul> -->

                <ul class="below-list" type="none">

                    <?php

                    $plan_noti = App\Notification::dashboardPlanEventNotification();

                    //echo "<pre>";

                    //print_r($plan_noti);



                    foreach ($plan_noti as $key => $value) { ?>

                        <li> <span class="bg-red"></span> <a href="{{ url('/system/calendar') }}"> {{ ucfirst($value['su_name']) }} - {{ ucfirst($value['description']) }}</a> </li>

                    <?php } ?>

                </ul>

                <div class="below-divider"></div>

                <!-- <ul class="below-list" type="none">
                    <li> <span class="bg-seagreen"></span>  Ian Williams - Eye Exams </li>
                    <li> <span class="bg-blue"></span>  Jeremy Fisher - Management Plans </li>
                    <li> <span class="bg-purple"></span>  Lewis Dance - Incentive scheme booking </li>
                    <li> <span class="bg-orange"></span>  Lewis Dance - Placement plan task </li>
                </ul> -->

                <ul class="below-list" type="none">

                    <?php

                    $noti = App\Notification::dashboardEventNotification();

                    foreach ($noti as $key => $value) { ?>

                        <li> <span class="{{ $value['color_class'] }}"></span> <a href="{{ url('/system/calendar') }}">{{ ucfirst($value['su_name']) }} - {{ ucfirst($value['description']) }}</a> </li>

                    <?php    } ?> <!-- bg-seagreen -->

                </ul>

            </div>
        </section>
    </div>
</div>

<script>
    $(document).ready(function() {

        // var hour    = moment().tz("Europe/London").format("hh");
        // var minutes = moment().tz("Europe/London").format("mm");
        // var seconds = moment().tz("Europe/London").format("ss");
        // var year = moment().tz("Europe/London").format("YYYY");
        // var month = moment().tz("Europe/London").format("MM");
        // var date = moment().tz("Europe/London").format("DD");

        console.log("London: " + moment().tz("Europe/London").format());

        function update_time() {

            // var hours = moment().tz("Europe/London").format("HH");
            // var mins = moment().tz("Europe/London").format("mm");
            // var seconds = moment().tz("Europe/London").format("ss");
            // var year = moment().tz("Europe/London").format("YYYY");
            // var month = moment().tz("Europe/London").format("MM");
            // var date = moment().tz("Europe/London").format("DD");

            var today = new Date();
            console.log(today);

            var hours = today.getUTCHours() + 1 ;
            console.log(hours);

            var mins = today.getUTCMinutes();
            var mid = 'am';

            if (hours == 0) { //At 00 hours we need to show 12 am
                hours = 12;
            } else if (hours >= 12)
            {
                if (hours == 12) {
                    hours = hours;
                } else {
                    hours = hours - 12;
                    if (hours < 10) {
                        hours = '0' + hours;
                    }
                    mid = 'pm';
                }

                // hours=hours-12;
                // if (hours < 10) {
                //     hours = '0'+hours;
                // }
                mid = 'pm';
            }

            if(hours < 10){
                hours = '0'+hours;
            }
            if(mins < 10){
                mins = '0'+mins;
            }
            $("#hh").text(hours);
            $("#mm").text(mins);
            $("#aa").text(mid);
        }
        window.setInterval(update_time, 1000);
    });
</script>