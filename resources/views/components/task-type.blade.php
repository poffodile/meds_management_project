<div class="modal fade" id="taskTypeModal" tabindex="-1" aria-labelledby="taskTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="thirdModalLabel">Add Task Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskTypeForm">
                    <div class="mb-3 row">
                        <label for="inputJobRef" class="col-sm-4 col-form-label">Task Type <span class="radStar">*</span></label>
                        <div class="col-sm-8">
                            <input type="hidden" name="task_type_id" id="task_type_id">
                            <input type="text" name="title" class="form-control editInput" id="">
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
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" onclick="saveTaskType();">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openTaskTypeModal(appendtaskType) {
        document.getElementById('task_type_id').setAttribute('data-taskType-id', appendtaskType);
        $('#taskTypeModal').modal('show');
    }

    function saveTaskType() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        let task_type = document.getElementById('task_type_id').getAttribute('data-taskType-id');
        var formData = $('#taskTypeForm').serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

        console.log("formData", formData);
        $.ajax({
            url: '{{ Route("General.ajax.save_task_type_data") }}', // Define the URL route for saving
            method: 'Post',
            data: formData,
            success: function(response) {
                $('#taskTypeModal').modal('hide');
                alert(response.data);
                getTaskType(document.getElementById(task_type));
            },
            error: function(error) {
                console.error('Error saving task type:', error);
            }
        });
    }

    function getTaskType(taskTypeList) {
        $.ajax({
            url: '{{ route("General.ajax.getTaskTypeList") }}',
            method: 'GET',
            success: function(response) {
                console.log("taskType", response.data);
                taskTypeList.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    taskTypeList.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>