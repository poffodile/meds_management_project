<div class="accordion-body">
                <?php
                $period = \Carbon\CarbonPeriod::create(date("Y-m-d", strtotime($rota_data->rota_start_date)), date("Y-m-d", strtotime($rota_data->rota_end_date)));
                $i = 1;
                foreach ($period as $date) {
                    $shift_count=App\RotaShift::where('rota_id', $rota_data->id)->where('rota_day_date', $date->format('Y-m-d'))->where('status', 1)->count();
                ?>
                    <div class="d-flex">
                        <div class="date-of-shift">
                            <strong>{{$date->format('D j M')}}</strong>
                        </div>
                        <div class="amount-of-shift">
                            <p><span id="shift_count">{{$shift_count}}</span> shifts</p>
                        </div>
                        <div class="add-shift-btn">
                            <!-- Button trigger modal -->
                            <button type="button" class="modal-btn" onclick="view_shift_model('<?= $date->format('l d F') ?>','<?= $date->format('D j M') ?>', <?= $i ?>)"> Add shift </button>

                            <!-- Modal -->

                        </div>
                    </div>
                    <?php
                    $rota_shift = App\RotaShift::where('rota_id', $rota_data->id)->where('rota_day_date', $date->format('Y-m-d'))->where('status', 1)->get();
                    // $userdata = array();
                    foreach ($rota_shift as $rota_shifts) {
                        $shift_id = $rota_shifts->id;
                        $shift_start = $rota_shifts->shift_start_time;
                        $shift_end = $rota_shifts->shift_end_time;
                        $description = $rota_shifts->description;

                        $list_emp = App\RotaAssignEmployee::where('rota_id', $rota_data->id)->where('shift_id', $shift_id)->where('status', 1)->first();
                        // foreach ($list_emp as $emp_ids) {
                        //     $userdata[] = App\ServiceUser::where('id', $emp_ids->emp_id)->get();
                        // }
                        $user_data = App\User::where('id', $list_emp->emp_id)->first();
                    // }
                    // foreach ($userdata as $user_data) {
                    ?>
                        <div class="w_full">
                            <?php for ($count = 0; $count < 24; $count++) {
                                echo  "<div class='hour_box' style='width: calc(4.16667%);'>
                                                        </div>";
                            } ?>
                            <!-- Button shift modal -->
                            <button type="button" class="shift_timing_btn" onclick="view_user_data(<?php echo $shift_id; ?>,`<?php echo $user_data->id; ?>`)" style="width: `+hours*4.16667+`%;  left: `+hours*4.16667+`%;" style="sdisplay: none;" data-testid="Shift card" style="width: 33.3333%; left: 37.5%;">
                                <div class="d-flex align-items-center">
                                    <div class="">
                                        <div class="name_of_member">
                                            <div class="">
                                                <?php
                                                $str = str_split($user_data->name);
                                                echo strtoupper($str[0]);
                                                $whatIWant = substr($user_data->name, strpos($user_data->name, " ") + 1);
                                                $str1 =  str_split($whatIWant);
                                                echo ($str1) ? strtoupper($str1[0]) : "";
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="">
                                        <div class="d-flex align-items-center">
                                            <div class="name_of_person">{{ $user_data->name }}</div>
                                            <div class="shift_timeing_duration">{{ \Carbon\Carbon::parse($shift_start)->format('h:i') }} - {{ \Carbon\Carbon::parse($shift_end) ->format('h:i') }}</div>
                                        </div>
                                        <div class="">{{ $description }}</div>
                                    </div>
                                </div>
                            </button>
                            <!-- Modal -->
                        </div>

                    <?php  } ?>
                    <div id="show_user_record<?= $i ?>">
                    </div>
                <?php $i++;
                }  //$dates = $period->toArray();
                ?>
            </div>