@extends('backEnd.layouts.master')
@section('title',' Daily Log Category')
@section('content')

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
                                    <!-- <div class="btn-group">
                                        <a href="{{ url('admin/homelist/add') }}">
                                            <button id="editable-sample_new" class="btn btn-primary">
                                                Add Home <i class="fa fa-plus"></i>
                                            </button>
                                        </a>    
                                    </div> -->
                                    @include('backEnd.common.alert_messages')
                                </div>

                                <button type="button" class="btn btn-primary m-10 openDailyLogSubModal" data-form-type="add" style="float:right;">Add Daily Log Sub Category</button>
                                <div class="space15"></div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div id="editable-sample_length" class="dataTables_length">
                                            <form method='post' action="{{ url('admin/daily-log-sub-category') }}" id="records_per_page_form">
                                                <label>
                                                    <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                        <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                        <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                        <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                    </select> records per page
                                                </label>
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <form method='get' action="{{ url('admin/daily-log-sub-category') }}">
                                            <div class="dataTables_filter" id="editable-sample_filter">
                                                <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" ></label>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Sub Category</th>
                                            <th>Icon</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                            <?php $index = 1;?>
                                            @forelse($sub_categorys as $key=>$val)
                                            @if(!empty($val->dailyLogCategory->category))
                                            <tr >
                                                <td>{{$index}}</td>
                                                <td>{{ $val->dailyLogCategory->category ?? ""}}</td>
                                                <td>{{ $val->sub_cat }}</td>
                                                <td>{{ $val->icon }}</td>
                                                <td>
                                                    @if($val->status == 1)
                                                        <a href="javascript:" onclick="status_change('{{$val->id}}',0)" class="btn btn-success">Active</a>
                                                    @else
                                                        <a href="javascript:" class="btn btn-danger" onclick="status_change('{{$val->id}}',1)">Inactive</a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" class="openDailyLogSubModal" data-form-type="edit" data-id="{{ $val->id }}" data-daily_cat_id="{{ $val->daily_cat_id }}" data-sub_cat="{{$val->sub_cat}}" data-icon="{{$val->icon}}" data-color="{{ trim($val->color) }}" data-status="{{ $val->status ?? '0'}}"><span style = "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a> | 
                                                    <a href="{{ url('admin/daily-log-sub-category/delete/'.$val->id) }}" class="delete"><i data-toggle="tooltip" title="" class="fa fa-trash-o fa-lg" data-original-title="Delete"></i></a>
                                                </td>
                                            </tr>
                                            <?php $index++;?>
                                            @endif
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text text-center">No records</td>
                                                </tr>
                                            @endforelse                                                                               
                                        </tbody>
                                    </table>
                                    {{ $sub_categorys->links('pagination::bootstrap-4') }}
                                </div>

                            </div>
                        </div>
                    </section>
                </div>
            </div>
<!-- Font awesome(icons) -->
       @include('backEnd.common.icon_list')
<!-- Font awesome(icons) end -->
            <!-- page end-->
            <div class="modal fade" id="dailyLogSubCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dailyLogSubCategoryTitle"> </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="dailyLogSubCategoryForm">
                                <div class="form-group">
                                    <label class="col-lg-3 col-sm-3 ">Category <span class="radStar ">*</span></label>
                                    <select name="daily_cat_id" id="daily_cat_id" class="form-control checkVali">
                                        <option selected disabled>Select Category</option>
                                        @foreach($categorys as $cat)
                                        <option value="{{$cat->id}}">{{$cat->category}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="hidden" name="dailylogsubcategory_id" id="dailylogsubcategory_id">
                                    <label class="col-lg-3 col-sm-3 ">Name <span class="radStar ">*</span></label>
                                    <input type="text" name="sub_cat" class="form-control checkVali" placeholder="Enter daily log sub category name" id="sub_cat">
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Icon <span class="radStar ">*</span></label>
                                    <div class="col-lg-8" id="iconDiv">
                                        <span class="group-ico icon-box"><i class="fa fa-address-book" id="iconShow"></i> </span>
                                        <input type="hidden" name="category_icon" value="fa fa-address-book" class="category_icon checkVali" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label">Font Color <span class="radStar ">*</span></label>
                                    <div class="col-lg-8">
                                        <input type="color" id="favcolor" name="color" value="#1f88b5" class="checkVali">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-4 control-label">Background Color <span class="radStar ">*</span></label>
                                    <div class="col-lg-8">
                                        <input type="color" id="background_color" name="background_color" value="#1f88b5" class="checkVali">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 col-sm-3 ">Status <span class="radStar ">*</span></label>
                                    <select name="status" id="status" class="form-control checkVali">
                                        <option selected disabled>Select Status</option>
                                        <option value="1">Active</option>
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
    </section>
<script>
    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('admin/daily-log-sub-category/status-change')}}",
            data: {
                id: id,
                status: status,
                _token: token
            },
            success: function (response) {
                console.log(response);
                if (response.success === true) {
                    location.reload();
                } else {
                    alert("Something went wrong");
                    return false;
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
            }
        });
    }
    $(document).on('click','.openDailyLogSubModal',function(){
        var formType = $(this).data('formType');
        if(formType === 'add'){
            $("#dailyLogSubCategoryTitle").text("Add daily log category");
            $("#dailyLogSubCategoryForm")[0].reset();
        }else if(formType === 'edit'){
            $("#dailyLogSubCategoryTitle").text("Edit daily log category");
            var daily_cat_id = $(this).data('daily_cat_id');
            var sub_cat = $(this).data('sub_cat');
            var icon = $(this).data('icon');
            var color = $(this).data('color');
            var status = $(this).data('status');
            var id = $(this).data('id');

            $("#daily_cat_id").val(daily_cat_id);
            $("#sub_cat").val(sub_cat);
            $('.category_icon').val(icon);
            
            $("#iconShow").removeAttr('class').attr('class',icon);
            $('#favcolor').val(color);
            $("#status").val(status);
            $("#dailylogsubcategory_id").val(id);
        }else{
            alert("Something went wrong");
            location.reload();
            return false;
        }
        $("#dailyLogSubCategory").modal('show');
    });
    $(document).on('click','#saveChanges', function(){
        var daily_cat_id = $("#daily_cat_id").val();
        var sub_cat = $("#sub_cat").val();
        var icon = $(".category_icon").val();
        var color = $("#favcolor").val();
        var background_color = $("#background_color").val();
        var status = $("#status").val();
        var dailylogsubcategory_id = $("#dailylogsubcategory_id").val();

        var url = "{{ url('admin/daily-log-sub-category/add/') }}";
        if(dailylogsubcategory_id){
            url = "{{ url('admin/daily-log-sub-category/edit/') }}";
        }
        var token = '<?php echo csrf_token(); ?>'
        var error = false;
        $(".checkVali").each(function(){
            if($(this).val() == '' || $(this).val() == undefined){
                $(this).css('border','1px solid red').focus();
                error = true;
                return false;
            }else{
                $(this).css('border','');
                $("#iconDiv").css('border','');
                error = false;
            }
        });
        if(icon == ''){
            $("#iconDiv").css('border','1px solid red');
            error = true;
        }
        if(error){
            return false;
        }else{
            $("#iconDiv").css('border','');
            $.ajax({
                type: "POST",
                url: url,
                data: {
                    id: dailylogsubcategory_id,
                    daily_cat_id:daily_cat_id,
                    sub_cat:sub_cat,
                    icon:icon,
                    color:color,
                    background_color:background_color,
                    status: status,
                    _token: token
                },
                success: function (response) {
                    console.log(response);
                    if (response.success === true) {
                        location.reload();
                    } else {
                        alert("Something went wrong");
                        return false;
                    }
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
                }
            });
        }

    });
</script>
<script>
$(document).ready(function(){
        $('.fontwesome-panel').hide();

        $('.icon-box').on('click',function(){
           $('.fontwesome-panel').show();

        });

        $('.fontawesome-cross').on('click',function(){
           $('.fontwesome-panel').hide(); 
        });

        $('#icons-fonts .fa-hover a').on('click', function () {
            var trim_txt = $(this).find('i');
            var new_class = trim_txt.attr('class');
            $('.icon-box i').attr('class',new_class);                  
            $('.fontwesome-panel').hide(); 
            $('.category_icon').val(new_class);                  
        });
    });

</script>

<script>

    $('#icons').hide();
    $('.icon-box').on('click',function(){
        $('#icons').toggle();
    });

</script>
    <!--main content end-->

@endsection