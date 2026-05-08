@extends('backEnd.layouts.master')
@section('title', 'Client Care Plan')
@section('content')

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Client Care Plan
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span>
                    </header>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="btn-group">
                                    <button class="btn btn-primary openCarePlanModal" data-form-type="add">
                                        Add New <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <form action="{{ url('admin/client-care-plan') }}" method="get" class="form-inline pull-right">
                                    <div class="form-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                            </div>
                        </div>

                        <div class="adv-table" style="margin-top: 20px;">
                            <table class="display table table-bordered table-striped" id="dynamic-table">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($care_plans as $plan)
                                    <tr class="gradeX">
                                        <td>{{ ($care_plans->currentPage() - 1) * $care_plans->perPage() + $loop->iteration }}</td>
                                        <td>{{ $plan->name }}</td>
                                        <td>
                                            <div class="onoffswitch">
                                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox statusChange" id="myonoffswitch{{ $plan->id }}" data-id="{{ $plan->id }}" {{ $plan->status == 1 ? 'checked' : '' }}>
                                                <label class="onoffswitch-label" for="myonoffswitch{{ $plan->id }}">
                                                    <span class="onoffswitch-inner"></span>
                                                    <span class="onoffswitch-switch"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="btn btn-primary btn-xs openCarePlanModal" 
                                               data-form-type="edit" 
                                               data-id="{{ $plan->id }}" 
                                               data-name="{{ $plan->name }}" 
                                               data-status="{{ $plan->status }}">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <a href="{{ url('admin/client-care-plan/delete/'.$plan->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this?')">
                                                <i class="fa fa-trash-o"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="dataTables_info">
                                        Showing {{ $care_plans->firstItem() }} to {{ $care_plans->lastItem() }} of {{ $care_plans->total() }} entries
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="pull-right">
                                        {{ $care_plans->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>

    <!-- Modal -->
    <div class="modal fade" id="carePlanModal" tabindex="-1" role="dialog" aria-labelledby="carePlanModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="carePlanModalTitle">Add Client Care Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="carePlanForm">
                        <div class="form-group">
                            <input type="hidden" name="care_plan_id" id="care_plan_id">
                            <label class="col-lg-3 col-sm-3 ">Name <span class="radStar ">*</span></label>
                            <input type="text" name="name" class="form-control checkVali" placeholder="Enter care plan name" id="name">
                        </div>
                        <div class="form-group">
                            <label class="col-lg-3 col-sm-3 ">Status <span class="radStar ">*</span></label>
                            <select name="status" id="status" class="form-control checkVali">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="saveChanges" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).on('click','.openCarePlanModal',function(){
        var formType = $(this).data('formType');
        if(formType === 'add'){
            $("#carePlanModalTitle").text("Add Client Care Plan");
            $("#carePlanForm")[0].reset();
            $("#care_plan_id").val('');
            $("#status").val(1);
        }else if(formType === 'edit'){
            $("#carePlanModalTitle").text("Edit Client Care Plan");
            var name = $(this).data('name');
            var status = $(this).data('status');
            var id = $(this).data('id');

            $("#care_plan_id").val(id);
            $("#name").val(name);
            $("#status").val(status);
        }
        $("#carePlanModal").modal('show');
    });

    $(document).on('click', '#saveChanges', function () {
        var care_plan_id = $('#care_plan_id').val();
        var name = $('#name').val();
        var status = $('#status').val();
        var token = "{{ csrf_token() }}";
        var url = "{{ url('admin/client-care-plan/save') }}";

        var error = false;
        $('.checkVali').each(function(){
            if($(this).val() == null || $(this).val() == ""){
                $(this).css('border','1px solid red');
                error = true;
            }else{
                $(this).css('border','1px solid #e2e2e4');
            }
        });

        if(error){
            return false;
        }else{
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: care_plan_id,
                    name: name,
                    status: status,
                    _token: token
                },
                success: function (response) {
                    if (response.success === true) {
                        location.reload();
                    } else {
                        alert(response.errors ? response.errors : "Something went wrong");
                        return false;
                    }
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage);
                }
            });
        }
    });

    $(document).on('change', '.statusChange', function () {
        var id = $(this).data('id');
        var status = $(this).prop('checked') ? 1 : 0;
        var token = "{{ csrf_token() }}";
        var url = "{{ url('admin/client-care-plan/status-change') }}";

        $.ajax({
            type: "POST",
            url: url,
            data: {
                id: id,
                status: status,
                _token: token
            },
            success: function (response) {
                if (response.success === true) {
                    // Success
                } else {
                    alert(response.message);
                }
            }
        });
    });
</script>

@endsection
