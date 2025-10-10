@extends('backEnd.layouts.master')

@section('title',' Childs')

@section('content')

<?php
    $page_url = url('admin/child-sections');
    
?>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">
    <!-- page start-->

    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <div class="panel-body">
                    <div class="adv-table editable-table">
                     <div class="row">   
                      <div class="col-lg-6">   
                        <div class="clearfix">
                            <div class="btn-group">
                                <a href="javascript:void(0)">
                                    <button type="button" id="editable-sample_new" class="btn btn-primary open-modal" data-type="Add">
                                        Add Child Section <i class="fa fa-plus"></i>
                                    </button>
                                </a>
                            </div>
                            @include('backEnd.common.alert_messages')
                        </div>
                      </div>
                      <!-- <div class="col-lg-6">
                            <div class="cog-btn-main-area">
                                <a class="btn btn-primary" href="#" data-toggle="dropdown">
                                    <i class="fa fa-cog fa-fw"></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    <li>
                                        
                                        <a href="{{ url('admin/service-users/'.'?user=archive') }}">
                                        Archive User </a>
                                    
                                        <a href="{{ url('admin/service-users') }}">
                                        Regular User </a>
                                        
                                    </li>
                                </ul>
                            </div>  
                      </div> -->
                     </div>
                        <div class="space15"></div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div id="editable-sample_length" class="dataTables_length">
                                    <form method='post' action="{{ $page_url }}" id="records_per_page_form">
                                        <label>
                                            <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
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
                                        <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" ></label>
                                        <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    if($section_query->isEmpty()) { ?>
                                        <?php
                                            echo '<tr style="text-align:center">
                                                  <td colspan="4">No Child found.</td>
                                                  </tr>';
                                        ?>
                                    <?php 
                                    } 
									else
                                    {
                                        foreach($section_query as $key => $value) 
                                        {  ?>

                                    <tr>
                                        <td class="user_name">{{ $value->section }}</td>
                                        <td>
                                            @if($value->status == 1)
                                                <a href="javascript:void(0)" onclick="status_change({{$value->id}},0)" class="btn btn-success">Active</a>
                                            @else
                                                <a href="javascript:void(0)" onclick="status_change({{$value->id}},1)" class="btn btn-danger">Inactive</a>
                                            @endif
                                        </td>
                                        <td class="action-icn">
                                            <a href="#" class="edit"><span style="color: #000;"><i title="Edit" data-type='Edit' data-id="{{ $value->id }}" data-section="{{ $value->section }}" data-status="{{ $value->status }}" class="fa fa-edit fa-lg open-modal"></i></a>
                                            <a href="{{ url('admin/child-section/delete/'.$value->id) }}" class="delete"><span style= "color: red"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                        </td>

                                        
                                    </tr>
                                    <?php } } ?>
                              
                                </tbody>
                            </table>
                        </div>

                        <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                        @if($section_query->links() !== null) 
                            {{ $section_query->appends(request()->input())->links() }}
                        @endif

                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- page end-->
    </section>
</section>
<!-- The Second Modal -->
<div class="modal fade popupcloseBtn" id="childSectionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="childSection_form" method="post" action="{{url('admin/child-section-save')}}">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <div class="form-group row">
                            <label class="col-lg-3 col-sm-3 ">Child Section <span class="radStar ">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" name="section" class="form-control" placeholder="Child Section" id="section">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 col-sm-3 ">Status</label>
                            <div class="col-sm-9">
                            <select name="status" id="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
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
<!--main content end-->

<script>
    $('document').ready(function(){
        $('.send-set-pass-link-btn').click(function(){
            
            var send_btn = $(this);
          
            var url_link = $(this).attr('href');
            $('.loader').show(); 
            $.ajax({
                type:'get',
                url : url_link,
                success:function(resp){

                    if(resp == true){
                        var usr = send_btn.closest('tr').find('.user_name').text();
                        alert('Email sent to '+usr+' successfully');
                    
                    } else{
                        alert('{{ COMMON_ERROR }}');
                    }
                    $('.loader').hide(); 
                }
            });
            return false;
        });
    });
    function status_change(id,status){
        var id=id;
        var status=status;
        var token='<?php echo csrf_token();?>'
        $.ajax({  
            type:"POST",
            url:"{{url('admin/childsection_status_change')}}",
            data:{id:id,status:status,_token:token},
            success:function(data)
            {
                console.log(data);
                if($.trim(data)=="done"){
                    window.location.reload();
                }else{
                    alert("Something went wrong! Please try again later");
                }
            }
        }); 
    }
    $('.open-modal').on('click',function(){
        $("#childSectionModal").modal('show');
        var type=$(this).data('type');
        $("#exampleModalLabel").text(type+" Child Section");
        if(type == 'Edit'){
            var id=$(this).data('id');
            var section=$(this).data('section');
            var status=$(this).data('status');

            $("#id").val(id);
            $("#section").val(section);
            $("#status").val(status);
        }
    });
    $("#saveChanges").on('click',function(){
        var id=$("#id").val();
        var section=$("#section").val();
        var status=$("#status").val();
        if(section == ''){
            $("#section").css('border','1px solid red').focus();
            return false;
        }else if(status == '' || status == undefined){
            $("#section").css('border','');
            $("#status").css('border','1px solid red').focus();
            return false;
        }else{
            $("#section").css('border','');
            $("#status").css('border','');
            $("#childSection_form").submit();
        }
    });
</script>


@endsection