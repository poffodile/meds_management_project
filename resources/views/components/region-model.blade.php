<!-- The only way to do great work is to love what you do. - Steve Jobs -->
<div class="modal fade" id="regionModal" tabindex="-1" aria-labelledby="regionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="thirdModalLabel">Add Region</h4>
            </div>
            <form id="region">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Region <span class="radStar ">*</span></label>
                        <input type="hidden" name="region_id" id="regionButton">
                        <input type="text" name="title" class="form-control editInput" id="titleRegion" value="" placeholder="Region">
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
                    <button type="button" class="btn btn-warning" onclick="saveRegion()" id="">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRegionModal(appendRegions) {
        document.getElementById('regionButton').setAttribute('data-region-id', appendRegions);
        $('#regionModal').modal('show');
    }

    function saveRegion() {
        // const region = document.getElementById('region').value;
        let regionId = document.getElementById('regionButton').getAttribute('data-region-id');

        var formData = $('#region').serialize();
        $.ajax({
            url: '{{ route("quote.ajax.saveRegion") }}', // Define the URL route for saving
            method: 'Post',
            data: formData,
            success: function(response) {
                $('#regionModal').modal('hide');
                alert('Region saved successfully!');
                getRegions(document.getElementById(regionId));
                // Use the currentFormId to find the specific form or field to update
                // For example, you might want to set the region value in the form itself
            },
            error: function(error) {
                console.error('Error saving region:', error);
            }
        });
    }

    function getRegions(regions) {
        $.ajax({
            url: '{{ route("quote.ajax.getRegions") }}',
            success: function(response) {
                console.log(response.data);
                regions.innerHTML = '';
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.title;
                    regions.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>