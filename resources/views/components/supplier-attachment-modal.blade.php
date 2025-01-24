<div class="modal fade" id="attachment_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">Add Attachment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="alert alert-success mt-3" id="alert_message_attachment" style="display:none"></div>
            <div class="modal-body p-0">
                <form id="attachment_form">

                  <div class="p-3">
                    @csrf
                    <input type="hidden" name="supplier_id" id="supplier_id" value="">
                    <input type="hidden" name="attachment_id" id="attachment_id" value="">
                    <div class="mb-3 row">
                        <label for="type_id" class="col-sm-3 col-form-label">Type</label>
                        <div class="col-sm-9">
                            <select id="type_id" name="type_id" class="form-control editInput">
                                <option value="1">Document</option>
                                <option value="2">Equipment</option>
                                <option value="3">Others</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="title" class="col-sm-3 col-form-label">Title <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" name="title" class="form-control editInput" id="title" value="" placeholder="Title">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="description" class="col-sm-3 col-form-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control textareaInput" name="description" id="description" rows="3" placeholder="Description"></textarea>
                        </div>
                    </div>
                    <div class="mb-0 row">
                        <label class="col-sm-3 col-form-label">Reminder?</label>
                        <div class="col-sm-9">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input reminder" type="radio" name="reminder" id="reminder1" value="1">
                                <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input reminder" type="radio" name="reminder" id="reminder2" checked value="0">
                                <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 row HideShow" style="display:none;">
                        <label for="reminder_date" class="col-sm-3 col-form-label">Reminder Date <span class="radStar ">*</span></label>
                        <div class="col-sm-3">
                            <input type="date" name="reminder_date" class="form-control editInput" id="reminder_date" onchange="setMinEndDate(null,null)" min="">
                        </div>
                        <div class="col-sm-1 calendar_icon">
                            <i class="fa fa-calendar-alt"></i>
                        </div>
                        <label class="col-sm-2 col-form-label">reminder before</label>
                        <div class="col-sm-3">
                            <div class="d-flex">
                            <select class="form-control editInput selectOptions input50" id="payment_terms" name="reminder_before_days">
                                <option value="0">0</option>
                            </select>
                            <label class="col-form-label ms-2" for="checkalrt">
                                day(s)</label>
                            </div>
                        </div>
                     
                    </div>
                    <div class="mb-2 row HideShow" style="display:none;">
                        <label for="reminder_email" class="col-sm-3 col-form-label">Reminder Email <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <!-- <input type="email" name="reminder_email" class="form-control editInput" id="reminder_email" value="" placeholder="Email"> -->
                            <select class="form-control editInput selectOptions" id="reminder_email" name="reminder_email[]" multiselect-search="true" multiselect-select-all="true" multiselect-max-items="4" multiple="multiple">
                                @foreach($supplier_email as $email)
                                    <option value="{{$email->email}}">{{$email->email}}</option>
                                @endforeach
                            </select>
                            <p class="col-form-label">(Maximum 5 emails allowed)</p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="attachment" class="col-sm-3 col-form-label">File Name <span class="radStar ">*</span></label>
                        <div class="col-sm-9">
                            <input type="file" name="attachment" class="form-control editInput" id="attachment">
                            <span class="editInput">(Max file size 25 MB)</span>
                            <span id="fileSizeError" style="color: red; display: none;">File larger than 25 MB.</span>
                            <span id="file_name"></span>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <a id="supplier_attachmentview_file" target="_blank" style="text-decoration:none">View</a>
                        </div>
                    </div>
                </div>

                    <!-- <div class="pageTitleBtn">
                        <a href="#" class="profileDrop p-2 crmNewBtn" onclick="save_attachment()"> Save</a>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div> -->

                    <div class="modal-footer customer_Form_Popup">
                        <a href="#" class="profileDrop crmNewBtn" onclick="save_attachment()"> Save</a>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{url('public/frontEnd/jobs/js/multiselect.js')}}"></script>
<script>
    $('.reminder').on('change', function(){
        var radio=$('input[name="reminder"]:checked').val();
        if(radio == 1){
            $('.HideShow').show();
        }else{
            $('.HideShow').hide();
        }
    });
    
    
    const maxFileSize = 25 * 1024 * 1024;
    $("#attachment").on('change', function () {
        const fileInput = document.getElementById('attachment');
        const errorMessage = document.getElementById('fileSizeError');

        errorMessage.style.display = 'none';
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size;
            if (fileSize > maxFileSize) {
                errorMessage.style.display = 'block';
                fileInput.value = '';
            }
        }
    });
    function save_attachment(){
        $.ajax({
            type: "POST",
            url: "{{url('/supplier_attachment_save')}}",
            data: new FormData($("#attachment_form")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                if(data.vali_error){
                    alert(data.vali_error);
                    $(window).scrollTop(0);
                    return false;
                }else if(data.success === true){
                    $(window).scrollTop(0);
                    $('#alert_message_attachment').text(data.message).show();
                    setTimeout(function() {
                        $('#alert_message_attachment').text('').hide();
                        $("#attachment_modal").modal('hide');
                        getAllSupplierAttachment();
                    }, 3000);
                }else{
                    alert("Something went wrong! Please try later");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
</script>
<script>
    const today = new Date().toISOString().split("T")[0];
    document.getElementById("reminder_date").setAttribute("min", today);

    function setMinEndDate(reminder_before_days,current_table_date) {
        const startDate = document.getElementById("reminder_date").value;
        const dropdown = document.getElementById("payment_terms");
        dropdown.innerHTML = "";
        if (startDate) {
            if(current_table_date == null || current_table_date == ''){
                var currentDate = new Date(today);
            }else{
                var currentDate = new Date(current_table_date);
            }
            const selectedDate = new Date(startDate);
            const timeDifference = selectedDate - currentDate;
            const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));

            if (daysDifference >= 0) {
                for (let i = 0; i <= daysDifference; i++) {
                    const option = document.createElement("option");
                    option.value = i;
                    option.textContent = i;
                    if (i == reminder_before_days) {
                        option.setAttribute("selected", "selected");
                    }
                    dropdown.appendChild(option);
                }
            } else {
                alert("Selected date is invalid.");
            }
        }
    }
</script>
<script>
    $(document).on('keyup','.multiselect-dropdown-search', function(){
        var email= $(this).val();
        if(email.length>3){
            $.ajax({
                type: "POST",
                url: "{{url('/search_email_list')}}",
                data: {email:email, _token:"{{csrf_token()}}"},
                success: function(data) {
                    console.log(data);
                   if(data.all_emails.length >0){
                    $('.multiselect-dropdown').hide();
                    
                    var emailDropdownList='';
                    data.all_emails.forEach(function(item){
                        emailDropdownList+='<option value="'+item.id+'">'+item.email+'</option>';
                        
                    });
                    $("#reminder_email").html(emailDropdownList);
                    MultiselectDropdown();
                    $('.multiselect-dropdown-search').val(email);
                    
                   }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    })
</script>