<div class="modal fade" id="{{ $reminderModalId }}" tabindex="-1" aria-labelledby="thirdModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="text-center mt-3" id="message_reminderModal" style="display:none"></div>
            <div class="modal-body p-0">
                <form id="{{ $reminderformId }}">

                  <div class="p-3">
                    @csrf
                    <input type="hidden" name="id" id="{{ $reminderId }}" value="">
                    <input type="hidden" name="po_id" id="{{ $hiddenForeignId }}" value="">
                    
                    <div class="mb-3 row">
                        <label for="title" class="col-sm-3 col-form-label">Reminder Date<span class="radStar ">*</span></label>
                        <div class="col-sm-4">
                            <input type="date" name="{{ $reminderDate }}" class="form-control editInput" id="{{ $reminderDate }}" value="">
                        </div>
                        <div class="col-sm-1 calendar_icon">
                            <i class="fa fa-calendar-alt"></i>
                        </div>
                        <div class="col-sm-2">
                            <select id="{{ $reminderTime }}" name="{{ $reminderTime }}" class="form-control editInput">
                            <?php
                                for ($hour = 0; $hour < 24; $hour++) {
                                    for ($minute = 0; $minute < 60; $minute += 5) {
                                        $time = sprintf('%02d:%02d', $hour, $minute);
                            ?>
                                    <option value="{{$time}}">{{$time}}</option>
                                <?php }}?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="{{ $reminderUser }}" class="col-sm-3 col-form-label">Reminder Email <span class="radStar ">*</span></label>
                        <div class="col-sm-9" id="clickyesno">
                            <select class="form-control editInput selectOptions reminder_email" id="{{ $reminderUser }}" name="user_id[]" multiselect-search="true" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">
                                <?php $users=App\User::getHomeUsers(Auth::user()->home_id);?>
                                @foreach($users as $userVal)
                                    <option value="{{$userVal->id}}">{{$userVal->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="description" class="col-sm-3 col-form-label">Send As<span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <label for="purchase_notify_who1" class="editInput">
                                <input type="checkbox" name="notification" id="{{ $reminderNotification }}" value="1"> Notification </label>
                            <label for="purchase_notify_who2" class="editInput">
                                <input type="checkbox" name="sms" id="{{ $reminderSms }}" value="1"> SMS
                            </label>
                            <label for="purchase_notify_who3" class="editInput">
                                <input type="checkbox" name="email" id="{{ $reminderEmail }}" value="1"> Email
                            </label>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="title" class="col-sm-3 col-form-label">Title <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control editInput" id="{{ $reminderTitle }}" value="" placeholder="Title">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="notes" class="col-sm-3 col-form-label">Notes</label>
                        <div class="col-sm-9">
                            <textarea class="form-control textareaInput" name="notes" id="{{ $reminderNotes }}" rows="3" placeholder="Description"></textarea>
                        </div>
                    </div>
                </div>

                    <!-- <div class="pageTitleBtn">
                        <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_attachment()"> Save</a>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div> -->

                    <div class="modal-footer customer_Form_Popup">
                        <a href="javascript:void(0)" id="{{ $saveButtonId }}" class="profileDrop crmNewBtn" onclick="save_reminder()"> Save</a>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{url('public/frontEnd/jobs/js/multiselect.js')}}"></script>
<script>
    function save_reminder(){
        $.ajax({
            type: "POST",
            url: "{{url('/save_reminder')}}",
            data: new FormData($("#{{ $reminderformId }}")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(response) {
                console.log(response);
                // return false;
                if(response.vali_error){
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                }else if(response.success === true){
                    $(window).scrollTop(0);
                    $('#message_reminderModal').addClass('success-message').show().text(response.message).show();
                    setTimeout(function() {
                        $('#message_reminderModal').removeClass('success-message').text('').hide();
                        $("#{{ $reminderModalId }}").modal('hide');
                        getAllReminder(response.data);
                    }, 3000);
                }else{
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