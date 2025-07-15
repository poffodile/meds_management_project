@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')
<!-- Top Bar Info Section End Here -->
<!-- Working Pattern form start from here -->
<style>
    section#container {
        height: auto;
    }
  input.form-control, select.form-control {
    height: 40px;
    }

</style>
<section id="main-content">
    <div class="wrapper">
        <div class="row working-time-pattern annual-leave">
            <div class="col-md-12">
                <div class="panel">
                    <header class="panel-heading">
                        <h4 class="head">Add <span id="employee_name_head"></span> for <span id="emp_name_leave"></span>
                            <?php
                            $name_first =  App\ServiceUser::select('name')->where('home_id', Auth::user()->home_id)->where('is_deleted', 0)->first();
                            echo $name_first->name;
                            ?> </h4>
                    </header>
                    <div class="tab-content">

                        <form action="{{ url('/add-leave') }}" method="POST">
                            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                            <div class="first-section m-4">
                                <div class="mb-3 row">
                                    <label for="name" class="col-sm-2 col-form-label">Employee</label>
                                    <div class="col-sm-3 col-md-3 position-rel">
                                        <input type="text" placeholder="Employees name" id="employees_name" class="form-control d-none"
                                            id="staticEmail" value="">
                                        <span class="position-abs"><i class="fa fa-search" aria-hidden="true"></i></span>
                                        <div class="position-rel">
                                            <input type="text" id="add_fullname"
                                                onclick="showSelect(event)" placeholder="Search or select employee" class="form-control"
                                                id="staticEmail" value="">
                                            <input type="hidden" name="employee_list" id="user_id">
                                            <span class="position-abs"><i class="fa fa-search" aria-hidden="true"></i></span>
                                            <ul class="customSelect" id="selectDropdown">
                                                <?php $i = 1; ?>
                                                <?php //dd($users); 
                                                ?>
                                                @foreach($users as $user)
                                                <li class="d-flex customSelectItem">
                                                    <input id="employee<?php echo $i; ?>" class="d-none" type="radio" name="selectEmployee">
                                                    <div class="labelElement p-2">
                                                        <label for="employee<?php echo $i; ?>" onclick="Employee_name(<?= $user->id ?>,'<?= $user->name ?>')" class="d-flex align-items-center">
                                                            <div class="firstLastLetter">
                                                                <?php
                                                                $parts = explode(" ", $user->name);
                                                                foreach ($parts as $name) {
                                                                    echo substr(strtoupper($name), 0, 1);
                                                                }
                                                                // $str = str_split($user->name); echo strtoupper($str[0]); 
                                                                // $whatIWant = substr($user->name, strpos($user->name, " ") + 1);

                                                                // print($whatIWant);
                                                                // if(isset($whatIWant)){

                                                                //     $str1 =  str_split($whatIWant); echo strtoupper($str1[0]);
                                                                // }    
                                                                ?>
                                                            </div>
                                                            <input type="hidden" id="starting_id" value="{{ $user->id }}">
                                                            <div id="full_name" class="fullName">{{ $user->name }} </div>
                                                            <div>
                                                                <span class="customRadio"><span class="dot"></span></span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </li>
                                                <?php $i++; ?>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="static" class="col-sm-2 col-form-label">Absence type</label>
                                    <div class="col-sm-3 col-md-3">
                                        <select class="form-select form-control" id="leaveTypes_id" name="leave_type"
                                            aria-label="Default select example">
                                            <optgroup label="Frequently used">
                                                @foreach($leavetype as $leavetypes)
                                                @if($leavetypes->leave_category == 1 )
                                                @if($leave == $leavetypes->id )
                                                <option selected value="{{ $leavetypes->id }}">{{ $leavetypes->leave_name}}
                                                    @else
                                                <option value="{{ $leavetypes->id }}">{{ $leavetypes->leave_name}}</option>
                                                @endif

                                                @endif
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="Others">
                                                @foreach($leavetype as $leavetypes)
                                                @if($leavetypes->leave_category == 2)
                                                @if($leave == $leavetypes->id )
                                                <option selected value="{{ $leavetypes->id }}">{{ $leavetypes->leave_name}}
                                                    @else
                                                <option value="{{ $leavetypes->id }}">{{ $leavetypes->leave_name}}</option>
                                                @endif
                                                @endif
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3 row" id="section2">
                                    <label for="" class="col-sm-2 col-form-label">Ongoing absence
                                        <span class="position-rel tooltip_custom">
                                            <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="16" width="16" viewBox="0 0 32 32" style="vertical-align: middle;">
                                                <path d="M13 13s2.6-.012 3.025 0c.825.012 1.475.725 1.475 1.55v8.887c0 .825-.65 1.538-1.475 1.55a1.502 1.502 0 0 1-1.525-1.5v-7.5H13c-.825 0-1.5-.675-1.5-1.5s.675-1.488 1.5-1.488zm3-13C7.175 0 0 7.175 0 16s7.175 16 16 16 16-7.175 16-16S24.825 0 16 0zm0 29C8.838 29 3 23.163 3 16S8.838 3 16 3s13 5.838 13 13-5.837 13-13 13zm1.5-20.5a1.5 1.5 0 1 1-3.001-.001A1.5 1.5 0 0 1 17.5 8.5z"></path>
                                            </svg>
                                            <span class="position-abs">
                                                <div>
                                                    <p>An ongoing absence spans across multiple days. Set an absence as ongoing when you are unsure when the employee will return.</p>
                                                </div>
                                            </span>
                                        </span>
                                    </label>
                                    <div class="col-sm-3 col-md-3">
                                        <input type="radio" id="sickLeaveYes" class="d-none custom_radio_input" name="ongoingLeave" value="yes">
                                        <label for="sickLeaveYes" class="custom_label">
                                            <span class="parent_custom_radio">
                                                <span class="d-flex align-items-center justify-content-center custom_radio"></span>
                                            </span>
                                            Yes
                                        </label>
                                        <input type="radio" id="sickLeaveNo" class="d-none custom_radio_input" name="ongoingLeave" value="no" checked="">
                                        <label for="sickLeaveNo" class="custom_label">
                                            <span class="parent_custom_radio">
                                                <span class="d-flex align-items-center justify-content-center custom_radio"></span>
                                            </span>
                                            No
                                        </label>
                                    </div>
                                </div>
                                <div id="section3">
                                    <div class="mb-3 row">
                                        <label for="name" class="col-sm-2 col-form-label">Date</label>
                                        <div class="col-sm-3 col-md-3">
                                            <input type="date" name="late_date" placeholder="Pattern Name" class="form-control" id="staticEmail" max="<?php echo date("Y-m-d"); ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label for="name" class="col-sm-2 col-form-label">Late by</label>
                                        <div class="col-sm-3 col-md-3">
                                            <input type="time" name="late_time" placeholder="Pattern Name" class="form-control" id="staticEmail" value="">
                                        </div>
                                    </div>
                                </div>

                                <div id="section4">
                                    <div class="mb-3 row">
                                        <label for="name" class="col-sm-2 col-form-label">Start</label>
                                        <div class="col-sm-3 col-md-3">
                                            <input type="date" id="start_date_validate" name="start_date" placeholder="Pattern Name" class="form-control"
                                                id="staticEmail" value="" onchange="dateValidate()">
                                        </div>
                                        <div class="col-md-2" id="section5">
                                            <select class="form-select form-control" name="start_date_full_half" aria-label="Default select example">
                                                <option selected="" value="1">Full Day</option>
                                                <option value="2">Second Half</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3 row" id="already_leave">
                                        <label for="name" class="col-sm-2 col-form-label"></label>
                                        <div class="col-sm-6 col-md-6">
                                            <div class="d-flex p-3" style="background-color: rgb(255, 239, 212);">
                                                <div>
                                                    <svg width="32" style="fill: #E5801A;" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" class="ms-4 fill-warning-500">
                                                        <path d="M16 30C8.275 30 2 23.725 2 16C2 8.275 8.275 2 16 2C23.725 2 30 8.275 30 16C30 23.725 23.725 30 16 30ZM16 4C9.387 4 4 9.387 4 16C4 22.613 9.387 28 16 28C22.613 28 28 22.613 28 16C28 9.387 22.613 4 16 4Z"></path>
                                                        <path d="M19 22H17V13C17 12.45 16.55 12 16 12H13C12.45 12 12 12.45 12 13C12 13.55 12.45 14 13 14H15V22H13C12.45 22 12 22.45 12 23C12 23.55 12.45 24 13 24H19C19.55 24 20 23.55 20 23C20 22.45 19.55 22 19 22Z"></path>
                                                        <path d="M17 9C17 9.552 16.552 10 16 10C15.448 10 15 9.552 15 9C15 8.448 15.448 8 16 8C16.552 8 17 8.448 17 9Z"></path>
                                                    </svg>
                                                </div>
                                                <div class="ms-3">
                                                    Existing absences fall within this date range. Check <a href="./personal-detail.html">your profile</a> for more details.
                                                    <ul id="previous_leave_response">

                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="name" class="col-sm-2 col-form-label">End</label>
                                        <div class="col-sm-3 col-md-3">
                                            <input type="date" name="end_date" placeholder="Select date" class="form-control"
                                                id="staticEmail" value="">
                                        </div>
                                        <div class="col-md-2" id="section6">
                                            <select class="form-select form-control" name="end_date_full_half" aria-label="Default select example">
                                                <option selected="" value="1">Full Day</option>
                                                <option value="2">Second Half</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                                <div class="mb-3 row" id="section7">
                                    <label for="name" class="col-sm-2 col-form-label">Working days missed</label>
                                    <div class="col-sm-2 col-md-2">
                                        <input type="number" name="missed_days" placeholder="days" class="form-control" id="staticEmail" value="">
                                    </div>
                                </div>
                                <!-- <div class="mb-3 row" id="section8">
                                                    <label for="name" class="col-sm-2 col-form-label">Hours deducted</label>
                                                    <div class="col-md-6">
                                                        <div class="col-sm-3 col-md-3">
                                                            <input type="time" name="late_time" placeholder="Pattern Name" class="form-control" id="staticEmail" value="">
                                                        </div>
                                                        <p class="mt-3" id="hours_error">michael's allowance is -12 hours with an average day of 0 hours. We have estimated that 0 hours need to be deducted from the allowance but you can amend this.</p>
                                                    </div>
                                                </div> -->
                                <div class="mb-3 row">
                                    <label for="select" id="textarea" class="col-sm-2 col-form-label">Notes</label>
                                    <div class="col-sm-3 col-md-3">
                                        <textarea name="notes" placeholder="Notes regarding the absence" id="textarea" cols="10" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <input type="submit" class="absance-btn" value="Add absence">
                                    <a href="{{ url('/rota') }}" class="dash-btn">Back to dashboard</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Working Pattern form end here -->
<!-- </div>
</div>
</div>
</div>
</section> -->
@include('rotaStaff.components.footer')

<script>
    function Employee_name(id, name) {
        document.getElementById('add_fullname').value = name;
        var element = document.getElementById("selectDropdown");
        element.classList.remove("show_select");
        document.getElementById('emp_name_leave').innerHTML = name;
        document.getElementById('user_id').value = id;
    }

    function dateValidate() {
        var start_date_input = document.getElementById('start_date_validate').value;
        var user_id = $('#user_id').val()
        // alert(user_id);
        var token = "<?= csrf_token() ?>";
        $.ajax({
            url: "{{ url('/date_validation_for_user') }}",
            type: "post",
            dataType: 'json',
            data: {
                start_date_input: start_date_input,
                start_id: user_id,
                _token: token
            },
            success: function(result) {
                console.log(result);
                if (result.length != 0) {
                    $('#already_leave').show();
                    document.getElementById('previous_leave_response').innerHTML =
                        `<li><span class="fw-bolder">Annual Leave:</span>  ` + result[0] + ` - ` + result[1] + `</li>`;
                } else {
                    $('#already_leave').hide();
                }

            }
        });
    }
    $(document).ready(function() {
        $('#already_leave').hide();
        $('#hours_error').hide();
        document.getElementById('user_id').value = $('#starting_id').val();
        document.getElementById('add_fullname').value = document.querySelector('#full_name').innerHTML;
        var select = document.getElementById('leaveTypes_id');
        var text = select.options[select.selectedIndex].text;
        console.log(text);
        document.getElementById('employee_name_head').innerHTML = text;
        var leaveTypes_id = document.getElementById('leaveTypes_id').value;
        if (leaveTypes_id == 1) {
            $('#section8').show();
        } else {
            $('#section8').hide();
        }
        if (leaveTypes_id == 2) {
            $('#section2').show();
            $('#section5').show();
            $('#section6').show();
            $('#section7').show();
        } else {
            $('#section2').hide();
            $('#section5').hide();
            $('#section6').hide();
            $('#section7').hide();
        }
        if (leaveTypes_id == 3) {
            $('#section3').show();
            $('#section4').hide();

        } else {
            $('#section3').hide();
            $('#section4').show();
        }
        if (leaveTypes_id == 1 || leaveTypes_id == 2 || leaveTypes_id == 4) {
            $('#section4').show();
        } else {
            $('#section4').hide();
        }
        $('#leaveTypes_id').on('change', function() {
            var select = document.getElementById('leaveTypes_id');
            var text = select.options[select.selectedIndex].text;
            console.log(text);
            document.getElementById('employee_name_head').innerHTML = text;
            var leaveTypes_id = document.getElementById('leaveTypes_id').value;
            // alert(leaveTypes_id);
            if (leaveTypes_id == 1) {
                $('#section8').show();
            } else {
                $('#section8').hide();
            }
            if (leaveTypes_id == 2) {
                $('#section2').show();
                $('#section7').show();
            } else {
                $('#section2').hide();
                $('#section7').hide();
            }
            if (leaveTypes_id == 3) {
                $('#section3').show();
                $('#section4').hide();
            } else {
                $('#section3').hide();
                $('#section4').show();
            }
            if (leaveTypes_id == 1 || leaveTypes_id == 2 || leaveTypes_id == 4) {
                $('#section4').show();
            } else {
                $('#section4').hide();
            }
        });
    });

    function showSelect(ev) {
        ev.stopPropagation();
        document.getElementById('selectDropdown').classList.toggle('show_select');
    }
    window.onclick = function(event) {
        const el = document.getElementById('selectDropdown');
        if (el.classList.contains("show_select")) {
            el.classList.remove("show_select");
        }
    }
</script>