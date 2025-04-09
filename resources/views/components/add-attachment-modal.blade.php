<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="{{ $purchaseModalId }}" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title pupTitle">{{ $modalTitle }}</h5>
                <button aria-hidden="true" data-bs-dismiss="modal" class="btn-close" type="button"></button>
            </div>
            <div id="attachment_messagse" class="mt-3 text-center"></div>
            <div class="modal-body">
                <form role="form" id="{{ $purchaseformId }}">
                    <input type="hidden" id="attachment_id" name="id">
                    <div><span id="error-message" class="error"></span></div>
                    <div class="row form-group">
                        <label class="col-lg-3 col-sm-3 col-form-label">{{ $refTitle }} Ref </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control-plaintext editInput" id="{{$refTitle}}_ref" value="" name="{{$refTitle}}_ref" readonly>
                            <input type="hidden" class="form-control editInput" id="{{ $hiddenForeignId }}" name="{{ $hiddenForeignId }}">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Type</label>
                        <div class="col-md-9">
                        <?php $attachmentType = App\Models\AttachmentType::getActiveAttachmentType(Auth::user()->home_id);?>
                            <select name="attachment_type" id="{{ $typeId }}" class="form-control editInput">
                                <option value="" selected disabled>Please Select</option>
                                @foreach($attachmentType as $value)
                                <option value="{{ $value->id }}">{{ $value->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">File Name <span class="radStar ">*</span></label>
                        <div class="col-md-9">
                            <input type="file" name="file" class="form-control editInput " placeholder="" id="{{ $selectfileName }}">
                            <span class="col-form-label">(Max file size 25 MB)</span>
                            <p id="fileSizeError" style="color: red;"></p>
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Title</label>
                        <div class="col-md-9">
                            <input type="text" name="title" class="form-control editInput " placeholder="Title" id="{{ $inputTitle }}">
                        </div>
                    </div>
                    <div class="row form-group mt-3">
                        <label class="col-lg-3 col-sm-3 col-form-label">Description<br>(Max 500 characters)</label>
                        <div class="col-md-9">
                            <textarea name="description" class="form-control textareaInput" rows="4" placeholder="Description" id="{{ $inputDescription }}" maxlength="500"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" id="{{ $saveButtonId }}">Save</button>
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const maxFileSize = 25 * 1024 * 1024;
    const fileInput = document.getElementById('{{ $selectfileName }}');
    var errorMessage = document.getElementById('fileSizeError');

    fileInput.addEventListener('change', function() {
        errorMessage.innerHTML = '';
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size;
            if (fileSize > maxFileSize) {
                errorMessage.innerHTML = 'File larger than 25 MB.';

                fileInput.value = '';
            }
        }
    });

    $("#{{$saveButtonId}}").on('click', function(){
        if(errorMessage.textContent.length>0){
            alert("Please upload file max size of 25 MB");
            return false;
        }else{
            $.ajax({
                type: "POST",
                url: "{{ $saveButtonUrl }}",
                data: new FormData($("#{{$purchaseformId}}")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if(response.vali_error){
                            alert(response.vali_error);
                            $(window).scrollTop(0);
                            return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#attachment_messagse').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#attachment_messagse').removeClass('success-message').text('').hide();
                            $("#{{$purchaseModalId}}").modal('hide');
                            getAllAttachment(response.data);
                        }, 3000);
                    }else if(response.success === false){
                        $('#attachment_messagse').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#attachment_messagse').text('').fadeOut();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    // alert('Error - ' + errorMessage + "\nMessage: " + error);
                    $('#attachment_messagse').addClass('error-message').text(error).show();
                        setTimeout(function() {
                            $('#attachment_messagse').text('').fadeOut();
                        }, 3000);
                }
            });
        }
    });
</script>