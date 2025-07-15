<!-- He who is contented is rich. - Laozi -->
<div class="modal fade" id="jobTitleModal" tabindex="-1" aria-labelledby="jobTitleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">Job Title - Add</h4>
            </div>
            <form id="jobTitleForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Job Title <span class="radStar ">*</span></label>
                        <input type="hidden" name="job_title_id" id="job_title_id">
                        <input type="text" name="name" class="form-control editInput" id="job_title" value="" placeholder="Job Title">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select id="status" name="status" class="form-control editInput">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="saveJobTitle()" id="">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openjobTitleModal(appendJobTitle) {
        document.getElementById('job_title_id').setAttribute('data-jobTitle-id', appendJobTitle);
        $('#jobTitleModal').modal('show');
    }

    function saveJobTitle() {

        let JobTitleId = document.getElementById('job_title_id').getAttribute('data-jobTitle-id');

        var formData = $('#jobTitleForm').serialize();
        $.ajax({
            url: '{{ route("customer.ajax.saveJobTitle") }}', // Define the URL route for saving
            method: 'Post',
            data: formData,
            success: function(response) {
                $('#jobTitleModal').modal('hide');
                alert('Job title saved successfully!');
                getCustomerJobTitle(document.getElementById(JobTitleId));
                // Use the currentFormId to find the specific form or field to update
                // For example, you might want to set the region value in the form itself
            },
            error: function(error) {
                console.error('Error saving jobtitle:', error);
            }
        });
    }

    function getCustomerJobTitle(jobTitle) {
        console.log('jobTitle',jobTitle);
        $.ajax({
            url: '{{ route("customer.ajax.getCustomerJobTitle") }}',
            method: 'GET',
            success: function(response) {
                console.log("jxcnjfjnfnk", response.data);
                jobTitle.innerHTML = '';

                const optionJob = document.createElement('option');
                optionJob.text = "Please Select";
                jobTitle.appendChild(optionJob);

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    jobTitle.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>