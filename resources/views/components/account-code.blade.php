<!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
<div class="modal fade" id="accountCodeModal" tabindex="-1" aria-labelledby="accountCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">Departmental Code - Add</h4>
            </div>
            <form id="accountCodeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name <span class="radStar">*</span></label>
                        <input type="hidden" name="account_code_id" id="account_code_id">
                        <input type="text" name="name" class="form-control editInput" id="">
                    </div>
                    <div class="mb-3">
                        <label>Departmental Code </label>
                        <input type="text" name="departmental_code" class="form-control editInput" id="">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select id="" name="status" class="form-control editInput">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a href="javascript:void(0)" class="btn btn-warning" onclick="saveAccountCode();" id=""> Save</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAccountCodeModal(appendJobTitle) {
        document.getElementById('account_code_id').setAttribute('data-accountCode-id', appendJobTitle);
        $('#accountCodeModal').modal('show');
    }

    function saveAccountCode() {

        let JobTitleId = document.getElementById('account_code_id').getAttribute('data-accountCode-id');

        var formData = $('#accountCodeForm').serialize();
        console.log(formData);
        $.ajax({
            url: '{{ route("invoice.ajax.saveAccountCode") }}', // Define the URL route for saving
            method: 'Post',
            data: formData,
            success: function(response) {
                $('#accountCodeModal').modal('hide');
                alert('Account Code saved successfully!');
                // getCustomerJobTitle(document.getElementById(JobTitleId));
                // Use the currentFormId to find the specific form or field to update
                // For example, you might want to set the region value in the form itself
                getAllAccountCodeList(response);
            },
            error: function(error) {
                console.error('Error saving account code:', error);
            }
        });
    }

    function getAccountCode(accountCode) {
        $.ajax({
            url: '{{ route("Invoice.ajax.getAccountCode") }}',
            method: 'GET',
            success: function(response) {
                console.log("accountCode", response.data);
                accountCode.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    accountCode.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>