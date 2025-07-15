<div class="modal fade" id="{{ $reminderModalId }}" tabindex="-1" aria-labelledby="thirdModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">{{ $modalTitle }}</h4>
            </div>
            <form id="{{ $reminderformId }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mt-3" id="message_reminderModal" style="display:none"></div>
                    <input type="hidden" name="id" id="{{ $reminderId }}" value="">
                    <input type="hidden" name="po_id" id="{{ $hiddenForeignId }}" value="">
                    <div class="mb-3">
                        <label>Reminder Date <span class="radStar ">*</span></label>
                        <div class="row">
                            <div class="col-sm-7">
                                <input type="date" name="{{ $reminderDate }}" class="form-control editInput" id="{{ $reminderDate }}" value="">
                            </div>
                            <div class="col-sm-5">
                                <select id="{{ $reminderTime }}" name="{{ $reminderTime }}" class="form-control editInput">
                                    <?php
                                    for ($hour = 0; $hour < 24; $hour++) {
                                        for ($minute = 0; $minute < 60; $minute += 5) {
                                            $time = sprintf('%02d:%02d', $hour, $minute);
                                    ?>
                                            <option value="{{$time}}">{{$time}}</option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="{{ $reminderUser }}">Reminder Email <span class="radStar ">*</span></label>
                        <div id="clickyesno">
                            <select class="form-control editInput selectOptions reminder_email" id="{{ $reminderUser }}" name="user_id[]" multiselect-search="true" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">
                                <?php $users = App\User::getHomeUsers(Auth::user()->home_id); ?>
                                @foreach($users as $userVal)
                                <option value="{{$userVal->id}}">{{$userVal->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Send As <span class="radStar ">*</span></label>
                        <div>
                            <label for="{{ $reminderNotification }}" class="editInput">
                                <input type="checkbox" name="notification" id="{{ $reminderNotification }}" value="1"> Notification </label>
                            <label for="{{ $reminderSms }}" class="editInput">
                                <input type="checkbox" name="sms" id="{{ $reminderSms }}" value="1"> SMS</label>
                            <label for="{{ $reminderEmail }}" class="editInput">
                                <input type="checkbox" name="email" id="{{ $reminderEmail }}" value="1"> Email</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Title <span class="radStar ">*</span></label>
                        <input type="text" name="title" class="form-control editInput" id="{{ $reminderTitle }}" value="" placeholder="Title">
                    </div>
                    <div class="mb-3">
                        <label>Notes</label>
                        <textarea class="form-control textareaInput" name="notes" id="{{ $reminderNotes }}" rows="3" placeholder="Description"></textarea>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a href="javascript:void(0)" id="{{ $saveButtonId }}" class="btn btn-warning" onclick="save_reminder()"> Save</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{url('public/frontEnd/jobs/js/multiselect.js')}}"></script>
<script>
    function save_reminder() {
        $.ajax({
            type: "POST",
            url: "{{ $reminderSaveUrl }}",
            data: new FormData($("#{{ $reminderformId }}")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
                    $(window).scrollTop(0);
                    $('#message_reminderModal').addClass('success-message').show().text(response.message).show();
                    setTimeout(function() {
                        $('#message_reminderModal').removeClass('success-message').text('').hide();
                        $("#{{ $reminderModalId }}").modal('hide');
                        var id = $("#{{ $reminderId }}").val();
                        if (id == '' || id == null || id == 'undefined') {
                            getAllReminder(response.data);
                        }
                    }, 3000);
                } else {
                    alert("Something went wrong! Please try later");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
</script>