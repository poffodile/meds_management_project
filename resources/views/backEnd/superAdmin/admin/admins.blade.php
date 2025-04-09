@extends('backEnd.layouts.master')
@section('title',' System Admins')
@section('content')

<style>
    #qrcode {
        padding: 0px 15px;
    }

    #qrcode img{
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .re-generateQR {
        border: 1px solid #1fb5ad;
        box-shadow: none;
        color: #fff;
        background: #1fb5ad;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin-right: 11px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .re-generateQR span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .re-generateQR span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .re-generateQR:hover span {
        padding-right: 20px;
    }

    .re-generateQR:hover span:after {
        opacity: 1;
        right: 0;
    }

    .download-btn {
        border: 1px solid #1fb5ad;
        box-shadow: none;
        color: #fff;
        background: #1fb5ad;
        padding: 6px 12px;
        text-align: center;
        border-radius: 3px;
        margin: 15px;
        margin-right: 11px;
        transition: all 0.5s;
        cursor: pointer;
    }

    .download-btn span {
        cursor: pointer;
        display: inline-block;
        position: relative;
        transition: 0.5s;
    }

    .download-btn span:after {
        content: '\00bb';
        position: absolute;
        opacity: 0;
        top: 0;
        right: -10px;
        transition: 0.5s;
    }

    .download-btn:hover span {
        padding-right: 20px;
    }

    .download-btn:hover span:after {
        opacity: 1;
        right: 0;
    }
</style>

<?php
if ($del_status == '0') { //regular users
    $page_url = url('admin/system-admins');
} else { //archive users
    $page_url = url('admin/system-admins/' . '?user=archive');
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
                        <div class="adv-table editable-table ">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="clearfix">
                                        @if($del_status == '0')
                                        <div class="btn-group">
                                            <a href="system-admin/add">
                                                <button id="editable-sample_new" class="btn btn-primary">
                                                    Add Company <i class="fa fa-plus"></i>
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
                                                <a href="{{ url('admin/system-admins/'.'?user=archive') }}">
                                                    Archive User </a>
                                                @else
                                                <a href="{{ url('admin/system-admins') }}">
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
                                            <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" placeholder=""></label>
                                            <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Admin Name</th>
                                            <th>Email</th>
                                            <th>QR Code</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($system_admins->isEmpty()) { ?>
                                            <?php
                                            echo '<tr style="text-align:center">
                                                  <td colspan="4">No system admin found.</td>
                                                  </tr>';
                                            ?>
                                            <?php
                                        } else {
                                            foreach ($system_admins as $key => $value) {
                                            ?>
                                                <tr class="">
                                                    <td>{{ $value->company }}</td>
                                                    <td>{{ $value->name }}</td>
                                                    <td class="transform-none">{{ $value->email }}</td>
                                                    <td style="width: 25%;">@if($value->qr_code_id == NULL) <button class="re-generateQR" onclick="generateQR(<?= $value->id ?>);"><span>Generate QR</span></button> <button><i class="fa fa-qrcode" aria-hidden="true"></i></button> @else <button class="re-generateQR" onclick="generateQR(<?= $value->id ?>);"><span>Re-Generate QR</span></button> <button class="re-generateQR" onclick="ViewQR(<?= $value->id ?>);"><span>View</span></button> @endif </td>
                                                    <td class="action-icn">
                                                        @if($del_status == '0')
                                                        <a href="{{ url('admin/system-admin/homes/'.$value->id) }}"><i data-toggle="tooltip" title="Homes" class="fa fa-home"></i></a>
                                                        <a href="{{ url('admin/system-admin/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                        <a href="{{ url('admin/system-admin/send-set-pass-link/'.$value->id) }}" class="send-set-pass-link-btn-admin" id="{{ $value->id }}"><i class="fa fa-envelope-o" data-toggle="tooltip" title="Send Credential Mail"></i></a>
                                                        
                                                        @else
                                                            <a href="{{ url('admin/system-admin/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><i data-toggle="tooltip" title="View" class="fa fa-eye"></i></a>
                                                        @endif
                                                        <a href="{{ url('admin/system-admin/package/detail/'.$value->id) }}"><i class="fa fa-file-powerpoint-o" data-toggle="tooltip" title="Current Package"></i></a>
                                                        <a href="{{ url('admin/system-admin/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                                    </td>

                                                    <!-- <td class="action-icn">
                                                        @if($del_status == '0')
                                                        <a href="{{ url('admin/system-admin/homes/'.$value->id) }}"><i data-toggle="tooltip" title="Homes" class="fa fa-home"></i></a>
                                                        <a href="{{ url('admin/system-admin/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>
                                                        <a href="{{ url('admin/system-admin/send-set-pass-link/'.$value->id) }}" class="send-set-pass-link-btn-admin" id="{{ $value->id }}"><i class="fa fa-envelope-o" data-toggle="tooltip" title="Send Credential Mail"></i></a>
                                                        <a href="{{ url('admin/users/access-rights/'.$value->id) }}" class="right"s><i data-toggle="tooltip" title="User Rights" class="fa fa-legal"></i></a>
                                                        <a href="{{ url('admin/system-admin/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                                        @else
                                                        <a href="{{ url('admin/system-admin/edit/'.$value->id.'?del_status='.$del_status) }}" class="edit"><i data-toggle="tooltip" title="View" class="fa fa-eye"></i></a>
                                                        @endif
                                                        <a href="{{ url('admin/system-admin/package/detail/'.$value->id) }}"><i class="fa fa-file-powerpoint-o" data-toggle="tooltip" title="Current Package"></i></a>
                                                    </td> -->
                                                </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            @if($system_admins->links() !== null)
                            {{ $system_admins->appends(request()->input())->links() }}
                            @endif
                        </div>
                    </div>
                    <div id="qrcode"></div>
                    <div id='download'></div>
                </section>
            </div>
        </div>
        </div>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    $('document').ready(function(){
        $('.send-set-pass-link-btn-admin').click(function(){
            var system_admin_id = $(this).attr('id');
            $('.loader').show();
            $.ajax({
                type:'get',
                url : '{{ url('admin/system-admin/send-set-pass-link') }}'+'/'+system_admin_id,
                success:function(resp){
                   alert(resp); 
                   $('.loader').hide();
                }
            });
            return false;
        });
    });

    function generateQR(id){
        if (confirm("Are you sure you want to gerenarte re-generate QR code") == true) {
            var token = "<?=csrf_token()?>";  
            $.ajax({
                url:"{{ url('/qrcode') }}",    
                type: "post",    
                dataType: 'json',
                data: {id: id, val: 1, _token: token},
                success:function(result){
                    console.log(result);
                    document.getElementById('qrcode') .innerHTML = ""; 
                    document.getElementById('download') .innerHTML = ""; 
                    console.log(blkstr.join(", "));
                    var qrcode = new QRCode(document.getElementById("qrcode"), {
                        text: `${result.qr_code_id}`,
                        width: 180, //default 128
                        height: 180,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                    document.getElementById('download').innerHTML = '<button class="download-btn" onclick="myFunction()"><span>Download</span></button>';

                }
            });
        } 
    }

    function ViewQR(id) {
        var token = "<?=csrf_token()?>";  
            $.ajax({
                url:"{{ url('/qrcode') }}",    
                type: "post",    
                dataType: 'json',
                data: {id: id, val: 0, _token: token},
                success:function(result){
                    console.log(result);
                    document.getElementById('qrcode') .innerHTML = ""; 
                    document.getElementById('download') .innerHTML = ""; 
                    var qrcode = new QRCode(document.getElementById("qrcode"), {
                        text: `${result.qr_code_id}`,
                        width: 180, //default 128
                        height: 180,
                        colorDark : "#000000",
                        colorLight : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });
                    document.getElementById('download').innerHTML = '<button class="download-btn" onclick="myFunction()"><span>Download</span></button>';
                }
            });
    }  

    function myFunction(){
        let a = document.getElementById('qrcode')
        img = a.getElementsByTagName('img')[0]
        var element = document.createElement('a');
        element.setAttribute('href', img.src);
        element.setAttribute('download', 'qr.png');
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
</script>
@endsection