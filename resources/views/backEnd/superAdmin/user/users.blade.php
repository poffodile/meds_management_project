@extends('backEnd.layouts.master')

@section('title',' Super Admin Users')

@section('content')

<?php
    if($del_status == '0') { //regular users
        $page_url = url('super-admin/users');
    } else { //archive users
        $page_url = url('super-admin/users/'.'?user=archive');
    }
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
                            @if($del_status == '0')
                            <div class="btn-group">
                                <a href="{{ url('super-admin/user/add') }}">
                                    <button id="editable-sample_new" class="btn btn-primary">
                                        Add Sub Admin <i class="fa fa-plus"></i>  
                                    </button>
                                </a>    
                            </div>
                            @endif
                            @include('backEnd.common.alert_messages')
                        </div>
                     </div>
                     <div class="col-lg-6">
                      <div class="cog-btn-main-area">
                       <a class="btn btn-primary" href="#" data-toggle="dropdown">
                        <i class="fa fa-cog fa-fw"></i>
                      </a> 
                      <ul class="dropdown-menu pull-right">
                        <li>
                            @if($del_status == '0')
                                <a href="{{ url('super-admin/users/'.'?user=archive') }}">
                                Archive User </a>
                            @else
                                <a href="{{ url('super-admin/users') }}">
                                Regular User </a>
                            @endif
                        </li>
                    </ul>
                      </div>   
                     </div>
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
                                        <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" placeholder="Enter Name"></label>
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
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    if($users->isEmpty()){ ?>
                                        <?php
                                            echo '<tr style="text-align:center">
                                                  <td colspan="4">No system admin found.</td>
                                                  </tr>';
                                        ?>
                                    <?php 
                                    } 

                                    else
                                    {
                                        foreach($users as $key => $value) 
                                        {  ?>

                                    <tr class="">
                                        
                                        <td>{{ $value->name }}</td>
                                        <td class="transform-none">{{ $value->email }}</td>
                                        <td class="action-icn">
                                            @if($del_status == '0')
                                            <a href="{{ url('/super-admin/user/edit/'.$value->id) }}" class="edit"><span style= "color: #000;"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a>
                                            <a href="{{ url('/super-admin/user/send-set-pass-link/'.$value->id) }}" class="send-set-pass-link-btn-admin" ><span style= "color: #000;"><i class="fa fa-envelope-o fa-lg" data-toggle="tooltip" title="Send Credential Mail"></i></span></a>
                                            <a href="{{ url('super-admin/user/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                            @else
                                                <a href="{{ url('/super-admin/user/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><span style= "color: #000;"><i data-toggle="tooltip" title="View" class="fa fa-eye fa-lg"></i></span></a>
                                            @endif
                                        </td>

                                        <!-- <td class="action-icn">
                                            @if($del_status == '0')
                                            <a href="{{ url('/super-admin/user/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                           <a href="{{ url('/super-admin/user/send-set-pass-link/'.$value->id) }}" class="send-set-pass-link-btn-admin" ><i class="fa fa-envelope-o" data-toggle="tooltip" title="Send Credential Mail"></i></a>
                                            <a href="{{ url('super-admin/user/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                            @else
                                                <a href="{{ url('/super-admin/user/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><i data-toggle="tooltip" title="View" class="fa fa-eye"></i></a>
                                            @endif
                                        </td> -->
                                    </tr>
                                     <?php } } ?>
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

<!--script for this page only-->

<script>
    $('document').ready(function(){
        $('.send-set-pass-link-btn-admin').click(function(){

            //var system_admin_id = $(this).attr('id');
            
            var email_url = $(this).attr('href');
            $('.loader').show();
            $.ajax({
                type:'get',
                //url : '{{ url('admin/system-admin/send-set-pass-link') }}'+'/'+system_admin_id,
                url : email_url,
                success:function(resp){
                   alert(resp); 
                   $('.loader').hide();
                }
            });
            return false;
        });
    });
</script>

@endsection