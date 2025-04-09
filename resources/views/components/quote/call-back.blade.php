<div>
    <!-- The biggest battle is the war against ignorance. - Mustafa Kemal Atatürk -->
    <div class="modal fade" id="callBackModal" tabindex="-1" aria-labelledby="accountCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content add_Customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="thirdModalLabel">Quote Call Back</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <form id="quoteCallBackForm">
                        <div class="p-3">
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Quote Ref </label>
                                <div class="col-sm-8">
                                    <input type="hidden" name="quote_id" id="set_quote_id">
                                    <input type="text" class="form-control-plaintext editInput" id="setQuoteRef" readonly="">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Call Back Date <span class="radStar">*</span></label>
                                <div class="col-sm-5">
                                    <input type="date" name="call_back_date" class="form-control editInput" id="">
                                </div>
                                <div class="col-sm-3">
                                    <input type="time" name="call_back_time" class="form-control editInput" id="">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Contact Name <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="contact_name" class="form-control editInput" id="">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Contact Phone <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="contact_phone" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="form-control editInput" id="">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label class="col-sm-4 col-form-label">Notify? </label>
                                <div class="col-sm-3">
                                    <input type="checkbox" name="notify" value="1" id="yesOn">
                                    <label class="col-form-label" for="yesOn">Yes, ON </label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="date" name="notify_date" class="form-control editInput" id="" placeholder="Date">
                                </div>
                                <div class="col-sm-2">
                                    <input type="time" name="notify_time" class="form-control editInput" id="" placeholder="Time">
                                </div>
                                <div class="col-sm-5"> </div>
                            </div>
                            <div id="notificationType">
                                <div class="mb-3 row">
                                    <label class="col-sm-4 col-form-label">Notify Who </label>
                                    <div class="col-sm-8">
                                        <input type="text" name="nottify_who" class="form-control editInput" id="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-4 col-form-label">Send As </label>
                                    <div class="col-sm-8">
                                        <span class="">
                                            <input type="checkbox" name="notification" value="1" id="yesOn">
                                            <label class="col-form-label">Notification </label>
                                        </span>
                                        <span class="">
                                            <input type="checkbox" name="email" value="1" id="yesOn">
                                            <label class="col-form-label">Email</label>
                                        </span>
                                        <span class="">
                                            <input type="checkbox" name="sms" value="1" id="yesOn">
                                            <label class="col-form-label">SMS</label>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-4 col-form-label">Notes</label>
                                <div class="col-sm-8">
                                    <textarea class="form-control textareaInput rounded-1" name="Notes" rows="5" placeholder="Notes"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer customer_Form_Popup">
                            <button type="button" class="profileDrop" onclick="saveCallBack();" id="">Save</button>
                            <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.getElementById('notificationType').style.display = 'none'

    function openCallBackModal() {
        const quoteRef = document.getElementById('changeToCallBack').getAttribute('data-quote_ref');
        const quote_id = document.getElementById('changeToCallBack').getAttribute('data-id');

        document.getElementById('setQuoteRef').value = quoteRef;
        document.getElementById('set_quote_id').value = quote_id;
        $('#callBackModal').modal('show');
    }

    document.getElementById('yesOn').addEventListener('change', function() {
        const toggleDiv = document.getElementById('notificationType');
        if (this.checked) {
            toggleDiv.style.display = 'block'; // Show the div
        } else {
            toggleDiv.style.display = 'none'; // Hide the div
        }
    });

    function saveCallBack() {
        const formData = new FormData(document.getElementById('quoteCallBackForm'));
        console.log(formData);
        fetch("{{ route('quote.callback.save') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.data); // Success message
                    // Optionally close the modal
                    document.getElementById('quoteCallBackForm').reset(); // Reset form
                    const modal = bootstrap.Modal.getInstance(document.getElementById('callBackModal'));
                    modal.hide();
                    window.location.reload();
                } else {
                    alert("Something went wrong.");
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>