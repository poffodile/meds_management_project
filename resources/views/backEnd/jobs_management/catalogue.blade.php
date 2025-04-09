@extends('backEnd.layouts.master')

@section('title','Users')

@section('content')

<?php
    $page_url = url('admin/catalogue');
?>


<style type="text/css">
 .position-center label {
    font-size: 20px;
    font-weight: 500;
}   
.custom-fieldset {
    position: relative;
    border: 2px solid #00000026;
    padding: 10px;
    margin-top: 20px;
    margin:10px;
}
.custom-legend {
    position: absolute;
    top: -10px;
    left: 20px;
    background-color: white;
    font-weight: bold;
    padding: 0 10px;
}
.modal-body .row {
    margin-bottom: 1rem;
}
p.floatLeft.redText.marginTop8px.marginBottom10 {
    text-align: left;
    font-size: 12px;
    color: #f00;
}
.workflowText{
    border-top:none;
    border-bottom:1px solid #ccc;
}
thead#flowhead {
    background: #eee;
}
.noti_button {
    text-align: end;
    margin: 0px 10px 10px;
}
.custom-fieldset .nav-tabs > .active > a,
.custom-fieldset .nav-tabs > .active > a:focus,
.custom-fieldset .nav-tabs > .active > a:hover {
    background-color: #1fb5ad;
    color: #fff;
    border: 1px solid #1fb5ad;
    border-bottom-color: transparent;
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
                         <div class="row"> 
                          <div class="col-lg-6">  
                            <div class="clearfix">
                                <div class="btn-group">
                                    <a href="javascript:void(0)" onclick="open_model(null)">
                                        <button id="editable-sample_new" class="btn btn-primary">
                                            Add Catalogue <i class="fa fa-plus"></i>
                                        </button>
                                    </a>    
                                </div>
                                @include('backEnd.common.alert_messages')
                            </div>
                           </div>
                           <div class="col-lg-6">
                            <div class="cog-btn-main-area">
                             <a class="btn btn-primary" href="#" data-toggle="dropdown">
                                    <i class="fa fa-cog fa-fw"></i>
                                </a>
                                <ul class="dropdown-menu pull-right">
                                    
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
                                        <th>Catalogue</th>
                                        <th>Type</th>
                                        <th>Item Count</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if(count($catalogue) == 0){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="7">No Catalogue found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 

                                        else
                                        {
                                            foreach($catalogue as $key => $val) 
                                            {  
                                                
                                                ?>

                                        <tr >
                                            <td class="user_name">{{ ucfirst($val->name) }}</td>
                                            <td class="transform-none" style="text-transform: none;">{{($val->catalogue_type == 1 ? 'Catalogue Pricing Only' : 'Mixed Pricing')}}</td>
                                            <td>{{$val->product_catalogue_prices_count}}</td>
                                            
                                            <td>
                                                @if($val->status == 1)
                                                    <a href="javascript:" onclick="status_change('{{base64_encode($val->id)}}',0)" class="btn btn-success">Active</a>
                                                @else
                                                <a href="javascript:" class="btn btn-danger" onclick="status_change('{{base64_encode($val->id)}}',1)">Inactive</a>
                                                @endif
                                            </td>
                                            <td class="action-icn">
                                                <a href="javascript:void(0)" class="edit" data-id="{{$val->id}}" data-name="{{$val->name}}" data-description="{{$val->description}}" data-catalogue_type="{{$val->catalogue_type}}" data-status="{{$val->status}}"><span style= "font-size: 13px; color: #000;"><span style= "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a> &nbsp &nbsp &nbsp 

                                                <a href="javascript:" onclick="delete_job('{{base64_encode($val->id)}}')" class="text-danger"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o fa-lg"></i></a>
                                             
                                            </td>
                                            
                                        </tr>
                                        <?php } } ?>
                                  
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- <div class="row"><div class="col-lg-6"><div class="dataTables_info" id="editable-sample_info">Showing 1 to 28 of 28 entries</div></div><div class="col-lg-6"><div class="dataTables_paginate paging_bootstrap pagination"><ul><li class="prev disabled"><a href="#">← Prev</a></li><li class="next"><a href="#">Next → </a></li></ul></div></div></div> -->
                           
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- Model workflow start here -->
        <div class="modal fade in" id="workflowModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content modal-lg">
            <div class="modal-header terques-bg">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"> Catalogue </h4>
            </div>
            <div class="modal-body">
                <div class="row">  
                    <div class="foor-box-wrap foor-plan">
                        <form id="flowForm">
                        <div class="alert text-center" id="catlogue_message" style="display:none"></div>
                            <input type="hidden" id="id" name="id">
                            <input type="hidden" id="TabId" name="TabId">
                            <input type="hidden" id="catalogue_id" name="catalogue_id">
                            @csrf
                            <div class="custom-fieldset"> 
                                <ul class="nav nav-tabs">
                                    <li class="active" id="catalogue"><a data-toggle="tab" href="#home" onclick="tabClick(0)">Catalogue</a></li>
                                    <li id="cataloguePrice"><a data-toggle="tab" href="#menu1" id="tabclick" onclick="tabClick(1)">Catalogue Price</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div id="home" class="tab-pane fade in active">
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Catalogue Name<span class="radStar ">*</span></label>
                                            <div class="col-lg-9">
                                                <input type="text" name="name" id="name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Description</label>
                                            <div class="col-lg-9">
                                                <textarea name="description" id="description" cols="5" rows="5" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Type</label>
                                            <div class="col-lg-9">
                                            <select id="catalogue_type" name="catalogue_type" class="form-control editInput">
                                                <option value="1">Catalogue Pricing Only</option>
                                                <option value="2">Mixed Pricing</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Status</label>
                                            <div class="col-lg-9">
                                            <select id="status" name="status" class="form-control editInput">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="menu1" class="tab-pane fade">
                                    <div class="row" style="margin-top:10px;">
                                        <div class="col-md-2">
                                            <a href="javascript:void(0)" onclick="add_item()" class="btn btn-primary"> Add Item</a>
                                        </div>
                                        <div class="col-md-2">
                                           <a href="javascript:void(0)" class="btn btn-primary">Import</a>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group">
                                                <input type="text" placeholder="Your Catalogue" class="form-control">
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-default">Search</button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3"></div>
                                    </div>
                                    <table class="table table-striped table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th>Default Price</th>
                                                <th>Catalogue Price</th>
                                                <th></th>
                                            </tr>
                                            
                                        </thead>
                                        <tbody id="CatalogueData">
                                            
                                        </tbody>
                                    </table>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="noti_button">
                                <input type="button" class="btn btn-primary" value="Save & Continue" onclick="getSaveData()"></input>
                                <a href="javascript:" class="btn btn-primary" onclick="document.getElementById('workflowModal').style.display='none'">Cancel</a>
                            </div>
                        </form>
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- page end-->
         @include('backEnd.common.product_list')
    </section>
</section>
<!--main content end-->

<script>
    function status_change(id,status){
        var id=id;
        var status=status;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/catalogue_status_change')}}",
                data:{id:id,status:status,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
                }
            }); 
    }
    function delete_job(id){
       if(confirm("Do you want to delete it ?")){
        var id=id;
        var token='<?php echo csrf_token();?>'
            $.ajax({  
                type:"POST",
                url:"{{url('admin/catalogue_delete')}}",
                data:{id:id,_token:token},
                success:function(data)
                {
                    console.log(data);
                    if($.trim(data)=="done"){
                        window.location.reload();
                    }
                }
            }); 
       }
        
    }
   function open_model(id){
    $("#tabclick").show();
    if(id == null){
        $("#tabclick").hide();
        $("#flowForm")[0].reset();
    }
    $("#workflowModal").modal('show');
   }
   $(".edit").on('click', function(){
        var id=$(this).data('id');
        // alert(id);return false;
        var name=$(this).data('name');
        var description=$(this).data('description');
        var catalogue_type=$(this).data('catalogue_type');
        var status=$(this).data('status');

        $("#id").val(id);
        $("#name").val(name);
        $("#description").val(description);
        $("#catalogue_type").val(catalogue_type);
        $("#status").val(status);
        $("#catalogue_id").val(id);
        $.ajax({
            url: '{{ url("admin/ProductCataloguePriceList") }}',
            method: 'Post',
            data: {
                _token:'{{ csrf_token() }}',cat_id:id 
            },
            success: function(response) {
                // console.log(response.data);
                var html1='';
                if (response.data.length === 0) {
                    html1 = `<tr><td colspan="5" class="text-center" style="color: #e10078;">Sorry, there are no items available</td></tr>`;
                }else{
                    response.data.forEach((item) => {
                        var formattedPrice1 = parseFloat(item.catalogue_price).toFixed(2); 
                        html1+=`  <tr> 
                                    <td scope="row">`+ (item.product_code ?? "") +`<input type="hidden" name="product_id" id="product_id" value="`+ item.product_id +`"> <input type="hidden" name="product_type" id="product_type" value="`+ item.product_type +`"><input type="hidden" name="ProductCatPrice" id="ProductCatPrice" value="`+item.id+`"></td> 
                                    <td>`+item.product_name+`</td> 
                                    <td class="text-end">£`+item.default_price+`</td> 
                                    <td class="text-center">
                                        <input type="text" value="`+formattedPrice1+`" name="item_catalogue_item_prices[]" class="text item_price numericOnly catalogueItemCustomPrice" data-item_id="11" tabindex="1">
                                    </td>
                                    <td>
                                        <img src="<?php echo url('public/frontEnd/jobs/images/delete.png');?>" alt="" class="data_delete image_style" data-delete="`+item.id+`">
                                    </td>
                                </tr>`;
                        
                    });  
                }
                $("#CatalogueData").html(html1); 
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
        open_model(id);
   });
   function getSaveData(){
        var id=$("#id").val();
        var name=$("#name").val();
        var description=$("#description").val();
        var catalogue_type=$("#catalogue_type").val();
        var status=$("#status").val();
        var TabId=$("#TabId").val();
        var catalogue_id=$("#catalogue_id").val();
        var token='<?php echo csrf_token();?>'
        let tableData = [];
        $('#CatalogueData tr').each(function () {
        const row = $(this);
            let rowData = {
                id:row.find('input[name="ProductCatPrice"]').val(),
                product_id: row.find('input[name="product_id"]').val(),
                product_type: row.find('input[name="product_type"]').val(),
                product_code: row.find('td:eq(0)').contents().filter(function () {
                    return this.nodeType === Node.TEXT_NODE;
                }).text().trim(),
                product_name: row.find('td:eq(1)').text().trim(),
                price: row.find('td:eq(2)').text().replace('£', '').trim(),
                custom_price: row.find('input[name="item_catalogue_item_prices[]"]').val()
            };
        tableData.push(rowData);
        });
        // console.log(tableData);return false;
        $.ajax({  
            type:"POST",
            url:"{{url('admin/save_catalogue')}}",
            data:{id:id,name:name,description:description,catalogue_type:catalogue_type,status:status,TabId:TabId,tableData:tableData,catalogue_id:catalogue_id,_token:token},
            success:function(data)
            {
                console.log(data);
                const catlogue_message=$('#catlogue_message').show();
                if(data.errors){
                    catlogue_message.text(data.errors);
                    catlogue_message.addClass('alert-danger').css('border','1px solid red');
                    setTimeout(function() {
                        catlogue_message.removeClass('alert-danger').css('border','');
                        $('#catlogue_message').fadeOut();
                        catlogue_message.text('');
                    }, 3000);
                }else if(TabId == 0 && data.success === true){
                    $("#TabId").val(1);
                    $("#catalogue_id").val(data.data.id);
                    $("#tabclick").show();
                    $("#catalogue").removeClass('active');
                    $("#cataloguePrice").addClass('active');
                    $("#home").removeClass('active in');
                    $("#home1").addClass('active');
                    $("#home1").addClass('in');
                    catlogue_message.text(data.message);
                    catlogue_message.addClass('alert-success').css('border','1px solid green');
                    $("#id").val(data.data.id);
                    setTimeout(function() {
                        catlogue_message.removeClass('alert-danger').css('border','');
                        document.getElementById('product_category_form').reset();
                        catlogue_message.text('');
                    }, 3000);
                }else if(TabId == 1 && data.success === true){
                    location.reload();
                }else{
                    alert("Something went wrong. Please try again later");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                console.log('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        }); 
   }
   function tabClick(value){
    $("#TabId").val(value);
}
function getProductData(selectedId) {
    // console.log(selectedId);return false;
        $.ajax({
            url: '{{ url("admin/getProductSelectId") }}',
            method: 'Post',
            data: {
                id: selectedId
            },
            success: function(response) {
                console.log(response.data[0]);
                var data=response.data[0];
                var formattedPrice = parseFloat(data.price).toFixed(2);
                var html=`  <tr> 
                                <td scope="row">`+ (data.product_code ?? "") +`<input type="hidden" name="product_id" id="product_id" value="`+ data.id +`"> <input type="hidden" name="product_type" id="product_type" value="`+ data.product_type +`"></td> 
                                <td>`+data.product_name+`</td> 
                                <td class="text-end">£`+data.price+`</td> 
                                <td class="text-center">
                                    <input type="text" value="`+formattedPrice+`" name="item_catalogue_item_prices[]" class="text item_price numericOnly catalogueItemCustomPrice" data-item_id="11" tabindex="1">
                                </td>
                                <td>
                                    <img src="<?php echo url('public/frontEnd/jobs/images/delete.png');?>" alt="" class="data_delete image_style">
                                </td>
                            </tr>`;
                $("#CatalogueData").append(html);
                $("#ProductListModal").modal('hide');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>

@endsection


