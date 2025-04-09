@extends('backEnd.layouts.master')

@section('title',' Childs')

@section('content')

<?php
    if($del_status == '0') { //regular users
        $page_url = url('admin/service-users');
    } else { //archive users
        $page_url = url('admin/service-users/'.'?user=archive');
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
                                <a href="service-users/add">
                                    <button id="editable-sample_new" class="btn btn-primary">
                                        Add Child <i class="fa fa-plus"></i>
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
                                        <a href="{{ url('admin/service-users/'.'?user=archive') }}">
                                        Archive User </a>
                                    @else
                                        <a href="{{ url('admin/service-users') }}">
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
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    if($service_users->isEmpty()) { ?>
                                        <?php
                                            echo '<tr style="text-align:center">
                                                  <td colspan="4">No Child found.</td>
                                                  </tr>';
                                        ?>
                                    <?php 
                                    } 
									else
                                    {
                                        foreach($service_users as $key => $value) 
                                        {  ?>

                                    <tr>
                                        <td class="user_name">{{ $value->name }}</td>
                                        <td class="transform-none">{{ $value->email }}</td>
                                        <td class="action-icn">
                                            @if($del_status == '0')
                                                <a href="{{ url('admin/service-users/edit/'.$value->id) }}" class="edit"><span style= "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-users/send-set-pass-link/'.$value->id) }}" class="mail send-set-pass-link-btn"><span style= "color: #000"><i data-toggle="tooltip" title="Send Credential Mail" class="fa fa-envelope-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-users/care-history/'.$value->id) }}"> <span style= "color: #000"><i data-toggle="tooltip" class="fa fa-user-md fa-lg" title="Care History" aria-hidden="true"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/careteam/'.$value->id) }}" class="care_team"><span style= "color: #000"><i data-toggle="tooltip" title="Care Team" class="fa fa-users fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/moods/'.$value->id) }}" class=""><span style= "color: #000"><i data-toggle="tooltip" title="Emotional Health" class="fa fa-smile-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/external-service/'.$value->id) }}" class=""><span style= "color: #000"><i data-toggle="tooltip" title="External Service" class="fa fa-external-link-square fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-users/migrations/'.$value->id) }}" class="Home Migration"><span style= "color: #000"><i data-toggle="tooltip" title="Migration" class="fa fa-upload fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-users/contacts/'.$value->id) }}" class="contacts"><span style= "color: #000"><i data-toggle="tooltip" title="Contacts" class="fa fa-phone fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/dynamic-forms/'.$value->id) }}" class="contacts"><span style= "color: #000"><i data-toggle="tooltip" title="Dynamic Forms" class="fa fa-wpforms fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/file-managers/'.$value->id) }}" class="contacts"><span style= "color: #000"><i data-toggle="tooltip" title="File Manager" class="fa fa-file-pdf-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/my-money/history/'.$value->id) }}" class="contacts"><span style= "color: #000"><i data-toggle="tooltip" title="My Money History" class="fa fa-money fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/my-money/request/'.$value->id) }}" class="contacts"><span style= "color: #000"><i data-toggle="tooltip" title="My Money Request" class="fa fa-credit-card fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/logbooks/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="Daily Log" class="fa fa-address-book-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/living-skills/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="Independent Living Skills" class="fa fa-user-times fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/calendar/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="Calendar" class="fa fa-calendar fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/rmps/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="RMP" class="fa fa-meh-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/bmps/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="BMP" class="fa fa-universal-access fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/risks/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="Risk" class="fa fa-scissors fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-user/earning-schemes/'.$value->id) }}"><span style= "color: #000"><i data-toggle="tooltip" title="Earning Scheme" class="fa fa-star-half-o fa-lg"></i></a>
                                                
                                                <a href="{{ url('admin/service-users/delete/'.$value->id) }}" class="delete"><span style= "color: red"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                                                                
                                            @else
                                                <a href="{{ url('admin/service-users/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><span style= "color: #000"><i data-toggle="tooltip" title="View" class="fa fa-eye fa-lg"></i></a>
                                                
                                            @endif
                                            </td>

                                        <!-- <td class="action-icn">
                                            @if($del_status == '0')
                                            <a href="{{ url('admin/service-users/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                            <a href="{{ url('admin/service-users/send-set-pass-link/'.$value->id) }}" class="mail send-set-pass-link-btn"><i data-toggle="tooltip" title="Send Credential Mail" class="fa fa-envelope-o"></i></a>
                                            <a href="{{ url('admin/service-users/care-history/'.$value->id) }}"> <i class="fa fa-user-md " title="Care History" aria-hidden="true"></i></a>
                                            <a href="{{ url('admin/service-user/careteam/'.$value->id) }}" class="care_team"
                                            ><i data-toggle="tooltip" title="Care Team" class="fa fa-users"></i></a>
                                            <a href="{{ url('admin/service-user/moods/'.$value->id) }}" class=""><i data-toggle="tooltip" title="Emotional Health" class="fa fa-smile-o"></i></a>
                                            <a href="{{ url('admin/service-user/external-service/'.$value->id) }}" class=""><i data-toggle="tooltip" title="External Service" class="fa fa-external-link-square"></i></a>
                                            <a href="{{ url('admin/service-users/migrations/'.$value->id) }}" class="Home Migration"><i data-toggle="tooltip" title="Migration" class="fa fa-upload"></i></a>
                                            <a href="{{ url('admin/service-user/incident-reports/'.$value->id) }}" class=""><i data-toggle="tooltip" title="Incident Report" class="fa fa-bolt"></i></a>
                                            <a href="{{ url('admin/service-users/contacts/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="Contacts" class="fa fa-phone"></i></a>
                                            <a href="{{ url('admin/service-user/dynamic-forms/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="Dynamic Forms" class="fa fa-wpforms"></i></a>
                                            <a href="{{ url('admin/service-user/file-managers/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="File Manager" class="fa fa-file-pdf-o"></i></a>
                                            <a href="{{ url('admin/service-user/my-money/history/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="My Money History" class="fa fa-money"></i></a>
                                            <a href="{{ url('admin/service-user/my-money/request/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="My Money Request" class="fa fa-credit-card"></i></a>
                                            <a href="{{ url('admin/service-user/my-money/request/'.$value->id) }}" class="contacts"><i data-toggle="tooltip" title="Placement Plan" class="fa fa-map-marker"></i></a>
                                            <a href="{{ url('admin/service-user/logbooks/'.$value->id) }}"><i data-toggle="tooltip" title="Daily Log" class="fa fa-address-book-o"></i></a>
                                            <a href="{{ url('admin/service-user/living-skills/'.$value->id) }}"><i data-toggle="tooltip" title="Independent Living Skills" class="fa fa-user-times"></i></a>
                                            <a href="{{ url('admin/service-user/calendar/'.$value->id) }}"><i data-toggle="tooltip" title="Calendar" class="fa fa-calendar"></i></a>
                                            <a href="{{ url('admin/service-user/rmps/'.$value->id) }}"><i data-toggle="tooltip" title="RMP" class="fa fa-meh-o"></i></a>
                                            <a href="{{ url('admin/service-user/bmps/'.$value->id) }}"><i data-toggle="tooltip" title="BMP" class="fa fa-universal-access"></i></a>
                                            <a href="{{ url('admin/service-user/risks/'.$value->id) }}"><i data-toggle="tooltip" title="Risk" class="fa fa-scissors"></i></a>
                                            <a href="{{ url('admin/service-user/earning-schemes/'.$value->id) }}"><i data-toggle="tooltip" title="Earning Scheme" class="fa fa-star-half-o"></i></a>
                                            <a href="{{ url('admin/service-users/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                            @else
                                            <a href="{{ url('admin/service-users/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><i data-toggle="tooltip" title="View" class="fa fa-eye"></i></a>
                                            @endif
                                        </td> -->
                                    </tr>
                                    <?php } } ?>
                              
                                </tbody>
                            </table>
                        </div>

                        <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                        @if($service_users->links() !== null) 
                            {{ $service_users->appends(request()->input())->links() }}
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
</script>


@endsection