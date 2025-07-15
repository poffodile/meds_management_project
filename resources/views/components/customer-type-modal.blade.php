<div class="modal fade" id="cutomer_type_modal" tabindex="-1" aria-labelledby="thirdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">Add Customer Type</h4>
            </div>
            <form id="customer_type_form">
                @csrf
                <div class="modal-body">
                    <div id="alert_message_customer_type" style="display:none" class="alert alert-success"></div>
                    <input type="hidden" id="customer_type_home_id" name="home_id" value="{{Auth::user()->home_id;}}">
                    <div class="mb-3">
                        <label>Customer Type <span class="radStar ">*</span></label>
                        <input type="text" name="title" class="form-control editInput" id="customer_type_name" value="" placeholder="Customer Type">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select id="customer_type_status" name="status" class="form-control editInput">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="save_customer_type()">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function save_customer_type() {
        $.ajax({
            type: "POST",
            url: "{{url('/save_customer_type')}}",
            data: new FormData($("#customer_type_form")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                if (data.vali_error) {
                    alert(data.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if ($.trim(data) == "error") {
                    alert("Something went wrong. Please try later!");
                } else {
                    $(window).scrollTop(0);
                    $('#alert_message_customer_type').text("Customer type Added Successfully Done!").show();
                    getAllCustomerType(data);
                    setTimeout(function() {
                        $('#alert_message_customer_type').text('').hide();
                        $("#cutomer_type_modal").modal('hide');
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
</script>