@extends('backEnd.layouts.master')

@section('title',' Super Admin Users')

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
                                <div class="btn-group">
                                    <a href="{{ url('super-admin/social-app/add') }}">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Social App <i class="fa fa-plus"></i>
                                        </button>
                                    </a>    
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                            <div class="space15"></div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div id="editable-sample_length" class="dataTables_length">
                                        <form method='post' action="{{ url('super-admin/social-apps') }}" id="records_per_page_form">
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
                                    <form method='get' action="{{ url('super-admin/social-apps') }}">
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
                                        <th class="col-lg-8">Name</th>
                                        <th class="col-lg-4">Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if($social_app->isEmpty()){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="4">No social app found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 

                                        else
                                        {
                                            foreach($social_app as $key => $value) 
                                            {  

                                                ?>

                                        <tr class="">
                                            
                                            <td>{{ ucfirst($value->name) }}</td>
                                            <td class="action-icn">
                                                <a href="{{ url('/super-admin/social-app/edit/'.$value->id) }}" class="edit"><span style = "color: #000;"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a>
                                                <a href="{{ url('super-admin/social-app/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                            </td>

                                            <!-- <td class="action-icn" width="25%">
                                                <a href="{{ url('/super-admin/social-app/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                <a href="{{ url('super-admin/social-app/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                            </td> -->
                                        </tr>
                                         <?php } } ?>
                                    </tbody>
                                </table>
                            </div>
                            @if($social_app->links() !== null) 
                            {{ $social_app->links() }}
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