<div>
    <!-- It is never too late to be what you might have been. - George Eliot -->
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="thirdModalLabel">{{ $modalTitle }}</h4>
                </div>
                <form id="{{ $formId }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Tag <span class="radStar ">*</span></label>
                            <input type="hidden" name="tag_id" id="tag_id">
                            <input type="text" name="title" class="form-control editInput" id="{{ $inputId }}" value="" placeholder="{{ $placeholderText }}">
                        </div>
                        <div class="mb-3">
                            <label class="radStar text-center"> Note: Comma not allowed in the tag. The previous name will not be populated by the rename tag.</label>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select id="{{ $statusId }}" name="status" class="form-control editInput">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer customer_Form_Popup">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="{{ $saveButtonId }}">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>