<style>
    .nowrapText {
        white-space: nowrap;
    }

    .nav-tabs .nav-link {
        color: #000;
    }
</style>

<div class="modal fade bd-example-modal-lg" id="{{ $modalId }}" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="secondModalLabel">{{ $modalTitle }}</h4>
            </div>
            <div id="message_show" class="mt-3 text-center"></div>
            <div class="modal-body">

                <form id="{{ $formId }}">
                    <input type="hidden" name="{{ $taskCustomerId }}" id="{{ $taskCustomerId }}" class="customer_id">
                    <input type="hidden" name="id" id="{{ $taskId }}" class="task_id">
                    <input type="hidden" name="{{ $foriegnId }}" id="{{ $foriegnId }}" class="{{ $foriegnId }}">
                    <input type="hidden" name="form_type" id="form_type" value="">
                    @csrf
                    <div class="tab-teaser">
                        <div class="tab-menu">
                            <ul>
                                <li><a href="#" class="active btn" data-rel="Task">Task</a></li>
                                <li><a href="#" data-rel="Timer" class="btn">Timer</a></li>
                            </ul>
                        </div>
                        <div class="tab-main-box">
                            <div class="tab-box" id="Task" style="display:block;">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-2">
                                            <label id="{{ $modalLabelTitle }}">Customer</label>
                                            <p class="customer_name"></p>
                                        </div>
                                        <div class="mb-3">
                                            <label>Task User <span class="radStar ">*</span></label>
                                            <select class="form-control editInput" name="user_id" id="{{ $userId }}">
                                                @foreach($users as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Title <span class="radStar ">*</span></label>
                                            <input type="text" class="form-control editInput" id="{{ $taskTitle }}" name="title" value="">
                                        </div>
                                        <div class="mb-3">
                                            <label>Task Type <span class="radStar ">*</span></label>
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <select class="form-control editInput" name="task_type_id" id="{{ $taskTypeId }}">
                                                        @foreach($task_type as $type)
                                                        <option value="{{$type->id}}">{{$type->title}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" id="openThirdModal"><i class="fa fa-plus-square"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Start Date <span class="radStar ">*</span></label>
                                            <div class="row">
                                                <div class="col-sm-9">
                                                    <input type="date" class="form-control editInput" name="start_date" id="{{ $taskStartDate }}" onchange="setMinEndDate()" min="">
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="time" class="form-control editInput" name="start_time" id="{{ $taskStartTime }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>End Date <span class="radStar ">*</span></label>
                                            <div class="row">
                                                <div class="col-sm-9">
                                                    <input type="date" class="form-control editInput" name="end_date" id="{{ $taskEndDate }}" value="" min="" disabled>
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="time" class="form-control editInput" name="end_time" id="{{ $taskEndTime }}" value="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="row">
                                                <label class="col-sm-2">Notify ? </label>
                                                <div class="col-sm-10">
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="checkbox" name="notify" id="yeson" value="1" required>
                                                        <label class="col-form-label nowrapText" for="yeson">Yes, On</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-9">
                                                    <input type="date" class="form-control editInput" id="{{ $notifyDate }}" name="notify_date">
                                                </div>
                                                <div class="col-sm-3">
                                                    <input type="time" class="form-control editInput" id="{{ $notifyTime }}" name="notify_time">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="optionsDiv" style="display:none">
                                            <div>
                                                <label>Send as <span class="radStar ">*</span> </label>
                                                <label class="editInput"><input type="checkbox" value="1" id="" name="notification"> Notification</label>
                                                <label class="editInput"><input type="checkbox" value="1" id="" name="email"> Email</label>
                                                <label class="editInput"><input type="checkbox" value="1" id="" name="sms"> SMS</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Related To</label>
                                            <span class="editInput" id="related_To">PO-0001</span>
                                        </div>
                                        <div class="mb-3">
                                            <label>Notes</label>
                                            <textarea name="notes" class="form-control textareaInput" rows="5" id="{{ $taskNotesText }}"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label>Is Reccurring Task ?</label>
                                            <input type="checkbox" value="1" name="is_recurring" class="form-check-input" id="isRecurring">
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="recurrence_div" style="display:none;">
                                    <div class="col-sm-6">
                                        <div class="newJobForm mt-4">
                                            <label class="upperlineTitle">Recurrence Pattern</label>
                                            <div class="Priority">
                                                <label>Create Task</label>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <select class="form-control editInput selectOptions" name="create_task" id="">
                                                            <option>0</option>
                                                        </select>
                                                    </div>
                                                    <label class="col-sm-8 col-form-label">Days before</label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio1" value="1" checked>
                                                        <label class="col-form-label" for="inlineRadio1">End after</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio2" value="2">
                                                        <label class="col-form-label" for="inlineRadio2">End By</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="task_end_repe_date" id="inlineRadio3" value="3">
                                                        <label class="col-form-label" for="inlineRadio3">No End Date</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="Priority">
                                                <div id="repetitation">
                                                    <label>No. of Repetitaion</label>
                                                    <input type="text" class="form-control editInput" name="repetitation">
                                                </div>
                                                <div id="task_end_date">
                                                    <label>Task End Date</label>
                                                    <input type="date" class="form-control editInput" name="task_end_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="newJobForm mt-4">
                                            <label class="upperlineTitle">Range of Recurrence</label>
                                            <div class="Priority">
                                                <label>Task Frequency</label>
                                                <select class="form-control editInput selectOptions" name="task_frequency" id="task_frequency">
                                                    <option value="1">Daily</option>
                                                    <option value="2">Weekly</option>
                                                    <option value="3">Monthly</option>
                                                    <!-- <option value="4">Yearly</option> -->
                                                </select>
                                            </div>
                                            <div id="daily">
                                                <div class="row py-1">
                                                    <div class="col-sm-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="daily" id="" value="" checked>
                                                            <label class="col-form-label" for="">Every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <div class="row">
                                                            <div class="col-sm-2 px-0 text-center">
                                                                <input class="form-control editInput" type="text" name="daily_days" id="" value="">
                                                            </div>
                                                            <label class="col-sm-8 col-form-label">Days</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="daily" id="" value="">
                                                            <label class="col-form-label" for="inlineRadioEvery">Every Weekday </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="weekly">
                                                <div class="row py-1">
                                                    <div class="col-sm-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="weekly" id="" value="" checked>
                                                            <label class="col-form-label" for="">Every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <div class="row">
                                                            <div class="col-sm-2 px-0">
                                                                <input class="form-control editInput" type="text" name="weekly_days" id="" value="">
                                                            </div>
                                                            <label class="col-sm-10 col-form-label">Weeks</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="weekly" id="" value="">
                                                            <label class="col-form-label" for="">Every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-5">
                                                        <div class="row">
                                                            <div class="col-sm-2 px-0">
                                                                <input class="form-control editInput" type="text" name="weekly_weekday" id="" value="">
                                                            </div>
                                                            <label class="col-sm-10 col-form-label" for="inlineRadioEvery">Weeks on</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-11 px-0 days_check">
                                                                @foreach($weeks as $weekDays)
                                                                <div>
                                                                    <label class="col-form-label" for="{{ $weekDays->name }}">{{ $weekDays->name }}</label>
                                                                    <input class="form-check-input" type="checkbox" name="weekly_weeks_{{ $weekDays->iteration }}" id="{{ $weekDays->name }}" value="{{ $weekDays->id }}">
                                                                </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="monthly">
                                                <div class="row py-1">
                                                    <div class="col-sm-2">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="monthly" id="" value="day" checked>
                                                            <label class="col-form-label" for="">Day</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-4 px-0">
                                                                <select class="form-control editInput selectOptions" name="monthly_days" id="">
                                                                    @for($i=1; $i<=30; $i++)
                                                                        <option value="{{ $i }}">{{ $i }}</option>
                                                                        @endfor
                                                                </select>
                                                            </div>
                                                            <label class="col-sm-8 col-form-label">off every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="row">
                                                            <div class="col-sm-2 px-0">
                                                                <input class="form-control editInput" type="text" name="monthly_month" id="" value="">
                                                            </div>
                                                            <label class="col-sm-10 col-form-label">Months</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-2">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="monthly" id="" value="">
                                                            <label class="col-form-label" for="">Every</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="row">
                                                            <div class="col-sm-11 px-0">
                                                                <select class="form-control editInput selectOptions" name="every_month_day" id="">
                                                                    <option value="1">First</option>
                                                                    <option value="2">Second</option>
                                                                    <option value="3">Third</option>
                                                                    <option value="4">Fourth</option>
                                                                    <option value="5">Last</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="row">
                                                            <div class="col-sm-11 px-0">
                                                                <select class="form-control editInput selectOptions" id="" name="every_monthly_month">
                                                                    <option value="">Day</option>
                                                                    <option value="">WeekDay</option>
                                                                    <option value="">Weekend Day</option>
                                                                    @foreach($weeks as $value)
                                                                    <option value="{{ $value->id }}">{{ $value->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-4 px-0">
                                                                <label class="col-form-label" for="">of Every</label>
                                                            </div>
                                                            <div class="col-sm-2 px-0">
                                                                <input class="form-control editInput" type="text" name="every_month_of_month" id="" value="">
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <label class="col-form-label" for="">Months</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!--  -->
                            </div>
                            <div class="tab-box" id="Timer">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <label>Task User <span class="radStar ">*</span></label>
                                            <select class="form-control editInput" name="user_id_timer" id="">
                                                @foreach($users as $value)
                                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Title <span class="radStar ">*</span></label>
                                            <input type="text" class="form-control editInput" name="title_timer" id="staticEmail">
                                        </div>
                                        <div class="mb-3">
                                            <label>Timer</label>
                                            <button class="btn btn-primary" id="toggleTimerBtn"><i class="fa fa-play"></i> Start</button>
                                            <span id="timerDisplay">00:00:00</span>
                                            <input type="hidden" name="start_time_timer" id="start_time_timer">
                                        </div>
                                        <!-- <div class="mb-3">
                                        <label for="related_to" class="col-sm-4 col-form-label">Related To</label>
                                        <div class="col-sm-8">
                                            <span class="editInput" id="relatedTo"></span>
                                        </div>
                                    </div> -->
                                        <div class="mb-3">
                                            <label>Task Type <span class="radStar ">*</span></label>
                                            <div class="row">
                                                <div class="col-sm-10">
                                                    <select class="form-control editInput" name="task_type_timer_id" id="task_type_timer">
                                                        @foreach($task_type as $type1)
                                                        <option value="{{$type1->id}}">{{$type1->title}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <a href="#!" class="formicon" id="openThirdModal2"><i class="fa fa-plus-square"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Notes</label>
                                            <textarea rows="5" name="notes_timer" class="form-control textareaInput" id=""></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- tab -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <a href="#" class="btn btn-warning crmNewBtn" id="completeBTN" style="display:none"> Complete</a>
                <!-- <a href="#" class="profileDrop p-2 crmNewBtn" > Close</a> -->
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-warning crmNewBtn" id="{{ $saveButtonId }}"> Save</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        document.getElementById('weekly').style.display = 'none';
        document.getElementById('monthly').style.display = 'none';
        document.getElementById('task_end_date').style.display = 'none';

        $('#isRecurring').on('change', function() {
            if ($(this).is(':checked')) {
                $('#recurrence_div').show();
            } else {
                $('#recurrence_div').hide();
            }
        });

        $('input[name="task_end_repe_date"]').on('change', function() {

            // Hide both divs initially
            $('#repetitation').hide();
            $('#task_end_date').hide();

            var value = $(this).val();
            // Show the appropriate div based on the selected radio button
            if (value === '1') {
                $('#repetitation').show();
            } else if (value === '2') {
                $('#task_end_date').show();
            }
        });

        document.getElementById('task_frequency').addEventListener('change', function() {
            document.getElementById('daily').style.display = 'none';
            document.getElementById('weekly').style.display = 'none';
            document.getElementById('monthly').style.display = 'none';

            var selectedValue = this.value;
            // Show the appropriate div based on the selected option
            if (selectedValue == 1) {
                document.getElementById('daily').style.display = 'block';
            } else if (selectedValue == 2) {
                document.getElementById('weekly').style.display = 'block';
            } else if (selectedValue == 3) {
                document.getElementById('monthly').style.display = 'block';
            }
        });

        // start here js for time start and pause
        let timerInterval;
        let elapsedSeconds = 0;
        let isRunning = false;

        function toggleTimer() {
            var staticEmail = $("input[name=title_timer]").val();
            if (staticEmail == '') {
                alert("Please fill all required field");
                return false;
            } else {
                if (isRunning) {
                    clearInterval(timerInterval);
                    document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-play"></i> Start';
                } else {
                    timerInterval = setInterval(function() {
                        elapsedSeconds++;
                        document.getElementById('timerDisplay').textContent = formatTime(elapsedSeconds);
                        document.getElementById('start_time').value = formatTime(elapsedSeconds);
                    }, 100);
                    document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-stop"></i> Pause';
                }
                isRunning = !isRunning;
            }

        }

        function formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
        }

        function pad(number) {
            return number < 10 ? '0' + number : number;
        }

        document.getElementById('toggleTimerBtn').addEventListener('click', toggleTimer);
    });
</script>
<script>
    const mainCheckbox = document.getElementById('yeson');
    const optionsDiv = document.getElementById('optionsDiv');
    // Open the second modal without hiding the first one
    $('#openSecondModal').on('click', function() {
        optionsDiv.style.display = 'none';
        $('#secondModal').modal('show');
    });
    mainCheckbox.addEventListener('change', function() {
        if (mainCheckbox.checked) {
            optionsDiv.style.display = 'block';
        } else {
            optionsDiv.style.display = 'none';
        }
    });
</script>
<script>
    const current_date = new Date().toISOString().split("T")[0];
    document.getElementById("{{ $taskStartDate }}").setAttribute("min", current_date);
    document.getElementById("{{ $taskEndDate }}").setAttribute("min", current_date);

    function setMinEndDate() {
        $('#{{ $taskEndDate }}').removeAttr('disabled');
        const startDate = document.getElementById("{{ $taskStartDate }}").value;
        document.getElementById("{{ $taskEndDate }}").setAttribute("min", startDate);
    }
</script>
<script>
    $("#{{$saveButtonId}}").on('click', function() {
        // const formTypeInput = document.getElementById('form_type');
        // if (document.getElementById('nav-home').classList.contains('active')) {
        //     formTypeInput.value = 'task_form';
        // } else if (document.getElementById('nav-profile').classList.contains('active')) {
        //     formTypeInput.value = 'timer_form';
        // }
        const formTypeInput = document.getElementById('form_type');
        const activeTab = document.querySelector('a.btn.active');

        if (activeTab) {
            const formType = activeTab.getAttribute('data-rel');
            formTypeInput.value = formType.toLowerCase() === 'task' ? 'task_form' : 'timer_form';
        }
        $.ajax({
            type: "POST",
            url: "{{ $saveNewTaskUrl }}",
            data: new FormData($("#{{$formId}}")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_show').addClass('success-message').text(response.message).show();
                    getAllNewTask(response.data);
                    setTimeout(function() {
                        $('#message_show').removeClass('success-message').text('').hide();
                        $("#{{ $modalId }}").modal('hide');
                    }, 3000);
                } else if (response.success === false) {
                    $('#message_show').addClass('error-message').text(response.message).show();
                    setTimeout(function() {
                        $('#error-message').text('').fadeOut();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Validation Errors:\n';
                    for (let field in errors) {
                        // errorMessage += `${errors[field].join(', ')}\n`;
                        alert(errors[field]);
                        return false;
                    }
                    // alert(errorMessage);
                } else {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            }
        });
    });
    $(document).on('click', '#completeBTN', function() {
        var id = $("#task_id").val();
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            url: "{{ $completeNewTaskUrl }}",
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                console.log(response);
                if (response.success === true) {
                    location.reload();
                } else {
                    alert("Something went wrong!");
                    return false;
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });

    });
</script>