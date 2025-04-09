<div class="btn-group">
    <a href="{{ url('admin/sales-finance/leads/add') }}">
        <button id="editable-sample_new" class="btn btn-primary">Add Lead <i class="fa fa-plus"></i></button>
    </a>
    <a href="{{ url('admin/sales-finance/leads') }}">
        <button id="editable-sample_new" class="btn btn-primary">All Leads ({{ App\Lead::getAllLeadCount(Session::get('scitsAdminSession')->home_id) }})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/unassigned') }}">
        <button id="editable-sample_new" class="btn btn-primary">Unassigned ({{ App\Lead::getUnassignedCount(Session::get('scitsAdminSession')->home_id) }})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/actioned') }}">
        <button id="editable-sample_new" class="btn btn-primary">Actioned ({{ App\Lead::getActionedLead(Session::get('scitsAdminSession')->home_id)}})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/rejected') }}">
        <button id="editable-sample_new" class="btn btn-primary">Rejected ({{ App\Lead::getRejectedCount(Session::get('scitsAdminSession')->home_id) }})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/authorization') }}">
        <button id="editable-sample_new" class="btn btn-primary">Authorization ({{ App\Lead::getAuthorizationCount(Session::get('scitsAdminSession')->home_id) }})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/converted') }}">
        <button id="editable-sample_new" class="btn btn-primary">Converted ({{ App\Models\Customer::getConvertedCustomersCount(Session::get('scitsAdminSession')->home_id) }})</button>
    </a>
    <a href="{{ url('admin/sales-finance/leads/tasks') }}">
        <button id="editable-sample_new" class="btn btn-primary">Tasks </button>
    </a>
</div>