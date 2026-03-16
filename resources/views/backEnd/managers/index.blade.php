@extends('backEnd.layouts.master')

@section('title',' Company Managers')

@section('content')
<script type="text/javascript" src="{{ url('public/backEnd/js/sweetalert.min.js')}}"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">
<?php
if ($del_status == '0') { //regular users
    $page_url = url('admin/managers');
} else { //archive users
    $page_url = url('admin/users/' . '?user=archive');
}
?>


<style type="text/css">
    #editable-sample_filter>label {
        width: 100%;
    }

    .space15 {
        margin: 0px 0px 10px 0px;
    }

    /* .action-icn a i {
        color: #1fb5ad;
    } */

    .action-icn a .fa-trash-o {
        color: #FF0000;
    }
</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix">
                                @if($del_status == '0')
                                <div class="btn-group">
                                    <a href="{{ url('admin/managers/add') }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Manager <i class="fa fa-plus"></i>
                                        </button>
                                    </a>
                                </div>
                                @endif
                                <!-- <div class="btn-group pull-right">
                                    <a class="btn btn-primary" href="#" data-toggle="dropdown">
                                        <span class="hidden-480">
                                        Action </span>
                                        <i class="fa fa-angle-down"></i>
                                    </a>
                                    <ul class="dropdown-menu pull-right">
                                        <li>
                                            @if($del_status == '0')
                                                <a href="{{ url('admin/users/'.'?user=archive') }}">
                                                Archive User </a>
                                            @else
                                                <a href="{{ url('admin/users') }}">
                                                Regular User </a>
                                            @endif
                                        </li>
                                    </ul>    
                                </div> -->
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ $page_url }}" id="records_per_page_form">
                                            <label>
                                                <select name="limit" size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                    <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                    <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                    <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                                </select> records per page
                                            </label>
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <form method='post' action="{{ $page_url }}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="dataTables_filter" id="editable-sample_filter">
                                            <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium"></label>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Contact No</th>
                                            <th>Active</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if ($users->isEmpty()) { ?>
                                            <?php
                                            echo '<tr style="text-align:center">
                                                      <td colspan="4">No User found.</td>
                                                      </tr>';
                                            ?>
                                            <?php
                                        } else {
                                            foreach ($users as $key => $value) {  ?>
                                                <tr>
                                                    <td class="user_name">{{ ucfirst($value->name) }}</td>
                                                    <td class="transform-none" style="text-transform: none;">{{ $value->email }}</td>
                                                    <td>{{ ucfirst($value->phone_no) }}</td>
                                                    <td>
                                                        <div class="action toggle-td">
                                                            <?php if ($value['status'] == '1') { ?>
                                                                <a class="toggle status" manager_id="{{ $value['id'] }}" status="{{ $value['status'] }}"><i class="fa fa-toggle-on"></i></a>
                                                            <?php } else { ?>
                                                                <a class="toggle status" manager_id="{{ $value['id'] }}" status="{{ $value['status'] }}"><i class="fa fa-toggle-off"></i></a>
                                                            <?php } ?>
                                                        </div>
                                                    </td>
                                                    <td class="action-icn">
                                                        @if($del_status == '0')
                                                        <a href="{{ url('admin/managers/edit/'.$value->id) }}" class="edit"><span style="color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a>&nbsp&nbsp&nbsp
                                                        <a href="{{ url('admin/managers/send-set-pass-link/'.$value->id) }}" id="{{ $value->id }}" class="mail send-set-pass-link-btn"><span style="color: #000"><i data-toggle="tooltip" title="Send Credential Mail" class="fa fa-envelope-o fa-lg"></i></span></a>&nbsp&nbsp&nbsp
                                                        <a href="{{ url('admin/managers/delete/'.$value->id) }}" class="delete"><span style="color: #000"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></span></a>
                                                        @else
                                                        <a href="{{ url('admin/users/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><span style="color: #000"><i data-toggle="tooltip" title="View" class="fa fa-eye fa-lg"></i></span></a>
                                                        @endif
                                                    </td>
                                                </tr>
                                        <?php }
                                        } ?>

                                    </tbody>
                                </table>
                            </div>

                            @if($users->links() !== null)
                            {{ $users->appends(request()->input())->links() }}
                            @endif
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->

<script>
    $('document').ready(function() {
        $('.send-set-pass-link-btn').click(function() {

            var send_btn = $(this);
            var user_id = send_btn.attr('id');

            $('.loader').show();

            $.ajax({
                type: 'get',
                url: "{{ url('admin/managers/send-set-pass-link') }}" + '/' + user_id,
                success: function(resp) {
                    if (resp == true) {
                        var usr = send_btn.closest('tr').find('.user_name').text();
                        alert('Email sent to ' + usr + ' successfully');
                    } else {
                        alert('{{ COMMON_ERROR }}');
                    }
                    $('.loader').hide();
                }
            });
            return false;
        });
    });
</script>

<script type="text/javascript">
    $(document).on('click', '.status', function() {
        var toggle_btn = $(this);
        var manager_id = $(this).attr('manager_id');
        var status_value = $(this).attr('status'); // current status

        console.log(manager_id);
        console.log(status_value);
        $('.loader').show();
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/manager/change-status') }}",
            data: {manager_id: manager_id, status: status_value},
            success: function(data) {
                $('.loader').hide();
                if (data == '0') {
                    toggle_btn.closest('.toggle-td').html('<a class="toggle status" manager_id="' + manager_id + '" status="0"><i class="fa fa-toggle-off"></i></a>');

                    swal("Success!", "Changes saved successfully!", "success");
                } else if (data == '1') {

                    toggle_btn.closest('.toggle-td').html('<a class="toggle status" manager_id="' + manager_id + '" status="1"><i class="fa fa-toggle-on"></i></a>');

                    swal("Success!", "Changes saved successfully!", "success");

                } else if (data == 'false') {
                    swal({
                            text: "Manager is already selected",
                            icon: "warning",
                            dangerMode: true,
                        })
                        .then((willDelete) => {
                            if (willDelete) {
                                return true;
                            } else {
                                return false;
                            }
                        });
                }
            }
        });
    });
</script>

@endsection