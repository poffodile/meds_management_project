<!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
<div class="modal fade" id="accountCodeModal" tabindex="-1" aria-labelledby="accountCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">Departmental Code - Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="accountCodeForm">
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-4 col-form-label">Name <span class="radStar">*</span></label>
                        <div class="col-sm-8">
                            <input type="hidden" name="account_code_id" id="account_code_id">
                            <input type="text" name="name" class="form-control editInput" id="">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-4 col-form-label">Departmental Code </label>
                        <div class="col-sm-8">
                            <input type="text" name="departmental_code" class="form-control editInput" id="">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-4 col-form-label">Status</label>
                        <div class="col-sm-8">
                            <select id="" name="status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="pageTitleBtn">
                        <button type="button" class="profileDrop" onclick="saveAccountCode();" id="">Save</button>
                        <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
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