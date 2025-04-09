@extends('backEnd.layouts.master')

@section('title',':User Form')

@section('content')

<style type="text/css">
.position-center label {
font-size: 20px;
font-weight: 500;
} 
 .ui-widget-content .ui-icon {
    background-image: url(images/ui-icons_444444_256x240.png) !important;
}
.position-center .assign-access {
font-size: 16px;
font-weight: 500;
}

.add_field_button-area {
 margin:20px 0px 0px 0px;   
}

.col-lg-offset-3 .btn.btn-primary {
margin: 0px 10px 0px 0px;   
}

.qual_upload {
margin:20px 0px 0px 0px;    
}


.input-group-addon {
border:none;    
}

.input-group-addon.remove-addon {
padding: 5px 0px 15px 0px;    
}
.currency {
    padding: 6px 8px;
    text-shadow: 0 1px 0 #ffffff;
    border: 1px solid #ccc;
    background-color: #efefef;
    margin-right: 8px; 
    display: inline-block;
    /* height: calc(1.5em + .75rem + 2px); */
    /* line-height: calc(1.5em + .75rem); */
    text-align: center;
    border-radius: 4px;
}

.d-flex {
    display: flex; /* Makes the container a flexbox */
    align-items: center; /* Aligns items vertically */
    gap: 8px; /* Adds space between children */
}

input.form-control {
    flex-grow: 1; /* Ensures input takes the remaining width */
}
.delete_row {
    display: inline-block;
    width: 16px;
    height: 16px;
    background-image: url(../public/frontEnd/jobs/images/delete.png);
    background-repeat: no-repeat;
    text-indent: -9999px;
    position: relative;
    top: 10px;
    cursor: pointer;
}
.noti_button {
    margin-left: 75%;
}
</style>
 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                       {{$task}}  Product
                    </header>
                    <div class="panel-body">
                        <div class="">
                            <form class="form-horizontal" id="form_data">
                            <div class="alert text-center" id="product_message" style="display:none"></div>
                            <label>Product Details</label>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Customer</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="Customer_id" id="Customer_id">
                                                <option disabled selected>-All-</option>
                                                @foreach($customer as $cust_val)
                                                    <option value="{{$cust_val->id}}" <?php if(isset($product) && $product->customer_only == $cust_val->id){echo "selected";}else{}?>>{{$cust_val->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                           
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Product Category</label>
                                        <div class="col-lg-7">
                                            <select class="form-control" name="product_category" id="product_category">
                                                <option disabled selected>-Any Category-</option>
                                                @foreach($product_category as $cat)
                                                <option value="{{$cat->id}}" <?php if(isset($product) && $product->cat_id == $cat->id){echo "selected";}else{}?>>{{$cat->full_category}}</option>
                                                @endforeach
                                            </select>
                                            
                                        </div>
                                        <div class="col-lg-2" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal('product_category_modal')"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>    
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Product Name<span class="radStar "> *</span></label>
                                        <div class="col-lg-9">
                                            <input type="text" name="product_name" id="name" class="form-control" placeholder="Name" value="<?php if(isset($product)){echo $product->product_name;}else{}?>" maxlength="255">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Product Type</label>
                                        <div class="col-lg-9">
                                        <select class="form-control" id="product_type" name="product_type">
                                            <option value="1" <?php if(isset($product) && $product->product_type == 1){echo "selected";}else{}?>>Product</option>
                                            <option value="2" <?php if(isset($product) && $product->product_type == 2){echo "selected";}else{}?>>Services</option>
                                            <option value="3" <?php if(isset($product) && $product->product_type == 3){echo "selected";}else{}?>>Consumable</option>
                                        </select>
                                        </div>
                                    </div> 
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Product Code</label>
                                        <div class="col-lg-6">
                                            <input type="text" name="product_code" id="product_code" class="form-control" placeholder="Product Code" value="<?php if(isset($product)){echo $product->product_code;}else{}?>" maxlength="255">
                                            
                                        </div>
                                        <?php if(isset($product)){}else{?>
                                        <div class="col-lg-3">
                                            <input type="button" value="Generate" id="generate" class="btn btn-primary" onclick="gerate_code()">
                                        </div>
                                        <?php }?>
                                    </div>         
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Cost Price</label>
                                        <div class="col-lg-9 d-flex align-items-center">
                                            <span class="currency">$</span>
                                            <input type="text" name="cost_price" id="cost_price" class="form-control" placeholder="Cost Price" value="<?php if(isset($product)){echo $product->cost_price;}else{}?>">
                                        </div>
                                    </div> 

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Markup</label>
                                        <div class="col-lg-9 d-flex align-items-center">
                                            <input type="text" name="markup" id="markup" class="form-control" placeholder="External Tax Code" value="<?php if(isset($product)){echo $product->margin;}else{}?>">
                                            <span class="currency">%</span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Price<span class="radStar"> *</span></label>
                                        <div class="col-lg-9 d-flex align-items-center">
                                            <span class="currency">$</span>
                                            <input type="text" name="price" id="price" class="form-control" value="<?php if(isset($product)){echo $product->price;}else{}?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Description</label>
                                        <div class="col-lg-9">
                                            <textarea name="description" id="description" rows="5" cols="10" class="form-control"><?php if(isset($product)){echo $product->description;}else{}?></textarea>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Show On Template</label>
                                        <div class="col-lg-9">
                                            <input type="checkbox" class="" id="show_temp" name="show_temp" <?php if(isset($product) && $product->show_temp == 1){echo "checked";}else{}?>>
                                        </div>
                                    </div>
                                </div>



                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Bar Code</label>
                                        <div class="col-lg-9">
                                            <input type="text" id="bar_code" name="bar_code" class="form-control" value="<?php if(isset($product)){echo $product->bar_code;}else{}?>">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Select Tax Rate</label>
                                        <div class="col-lg-7">
                                            <select class="form-control" name="tax_id" id="tax_id">
                                                <option disabled selected>-Please Select-</option>
                                                @foreach($tax as $rate)
                                                <option value="{{$rate->id}}" <?php if(isset($product) && $product->tax_rate == $rate->id){echo "selected";}else{}?>>{{$rate->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                            
                                        </div>
                                        <div class="col-lg-2" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal('tax_rate_modal')"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Purchase Tax Rate</label>
                                        <div class="col-lg-7">
                                            <select class="form-control" name="tax_rate" id="tax_rate">
                                                <option disabled selected>-Please Select-</option>
                                                @foreach($tax as $val)
                                                <option value="{{$val->id}}" <?php if(isset($product) && $product->tax_rate == $val->id){echo "selected";}else{}?>>{{$val->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                            
                                        </div>
                                        <div class="col-lg-2" id="inputPlusCircle">
                                            <a class="javascript:void(0)" onclick="get_modal('tax_rate_modal')"><i class="fa  fa-plus-circle"></i> </a>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Nominal Code</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="nominal_code" name="nominal_code" value="<?php if(isset($product)){echo $product->nominal_code;}else{}?>">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Sales Account Code</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="sales_acc_code" id="sales_acc_code">
                                                <option disabled selected>-Please Select-</option>
                                                @foreach($acc_code as $code)
                                                <option value="{{$code->id}}" <?php if(isset($product) && $product->sales_acc_code == $code->id){echo "selected";}else{}?>>{{$code->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Purchase Account Code</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="purchase_acc_code" id="purchase_acc_code">
                                                <option disabled selected>-Please Select-</option>
                                                @foreach($acc_code as $codes)
                                                <option value="{{$codes->id}}" <?php if(isset($product) && $product->purchase_acc_code == $codes->id){echo "selected";}else{}?>>{{$codes->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Expense Account Code</label>
                                        <div class="col-lg-9">
                                            <select class="form-control" name="expense_acc_code" id="expense_acc_code">
                                                <option disabled selected>-Please Select-</option>
                                                @foreach($acc_code as $value)
                                                <option value="{{$value->id}}" <?php if(isset($product) && $product->expense_acc_code == $value->id){echo "selected";}else{}?>>{{$value->name}}</option>
                                                @endforeach
                                                
                                            </select>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Location</label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="location" name="location" value="<?php if(isset($product)){echo $product->location;}else{}?>">
                                            
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Attachment</label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control" id="attachment" name="attachment">
                                            
                                            <input type="hidden" value="<?php if(isset($product)){echo $product->attachment;}else{}?>" name="old_image" id="old_image">
                                        </div>
                                    </div>
                                    <table class="table">
                                        <thead style="background-color: #0606060f;">
                                            <th>Supplier</th>
                                            <th>Part Number</th>
                                            <th>Cost Price</th>
                                            <th><button class="btn btn-primary" type="button" onclick="suplier_row()">+</button></th>
                                        </thead>
                                        <tbody id="result">
                                            <?php foreach($supplier as $v){?>
                                        <tr>
                                            <td>
                                                <input type="hidden" id="product_supplier_list_id" name="product_supplier_list_id[]" value="<?php echo $v->id; ?>">
                                                <select id="supplier_id" name="supplier_id[]" class="form-control">
                                                    <option selected disabled>Select Supplier</option>
                                                    <?php $inc=1; foreach($data as $d){?>
                                                        <option value="{{$inc}}" <?php if($v->supplier_id == $inc){echo "selected";}else{}?>>{{$d}}</option>
                                                        <?php $inc++;}?>
                                                </select>
                                            </td>
                                            <td><input type="text" id="part_number" name="part_number[]" value="{{$v->part_number}}"></td>
                                            <td><span class="currency">$</span><input type="text" id="cost_price_supplier" name="cost_price_supplier[]" value="{{$v->cost_price_supplier}}"></td>
                                            <td class="delete_row">X</td>
                                        </tr>
                                        <?php }?>
                                        </tbody>
                                    </table>
                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-lg-offset-2 col-lg-10">
                                            <div class="add-admin-btn-area">   
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="productID" id="id" value="<?php if(isset($product)){echo $product->id;}else{}?>">
                                                <button type="button" class="btn btn-primary save-btn" onclick="get_save_data()">Save</button>

                                                <a href="{{ url('admin/product_list') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
         <!-- Product Category Modal start -->
         <div class="modal fade in" id="product_category_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">Add - Product Category </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="from_outside_border">
                                    <div class="alert text-center" id="cat_message" style="display:none"></div>
                                    <form id="product_category_form">
                                        @csrf
                                        
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Product Category<span class="radStar "> *</span></label>
                                            <div class="col-lg-9">
                                                <input type="text" name="product_category_name" id="product_category_name" class="form-control">
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Parent Category</label>
                                            <div class="col-lg-9">
                                            <select id="product_cat_id" name="product_cat_id" class="form-control editInput">
                                                <option disabled selected>-Any Category-</option>
                                                @foreach($product_category as $cat)
                                                <option value="{{$cat->id}}" <?php if(isset($product) && $product->cat_id == $cat->id){echo "selected";}else{}?>>{{$cat->full_category}}</option>
                                                @endforeach
                                            </select>
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Status</label>
                                            <div class="col-lg-9">
                                            <select id="product_category_status" name="product_category_status" class="form-control editInput">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="noti_button">
                                            <a href="javascript:" class="btn btn-primary" onclick="save_product_category()">Save</a>
                                            <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <!-- end here -->
           <!-- Tax Rate Modal start -->
         <div class="modal fade in" id="tax_rate_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header terques-bg">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title pupTitle">Add - Tax Rate </h4>
                    </div>
                    <div class="modal-body pdbotm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="from_outside_border">
                                    <div class="alert text-center" id="tax_message" style="display:none"></div>
                                    <form id="tax_rate_form">
                                        @csrf
                                        
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Tax Rate Name<span class="radStar "> *</span></label>
                                            <div class="col-lg-9">
                                                <input type="text" name="tax_rate_name" id="tax_rate_name" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Tax Rate<span class="radStar "> *</span></label>
                                            <div class="col-lg-9">
                                                <input type="text" name="tax_rateModal" id="tax_rateModal" class="form-control">
                                            </div>
                                        </div>

                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Status</label>
                                            <div class="col-lg-9">
                                            <select id="tax_rate_status" name="tax_rate_status" class="form-control editInput">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">External Taxt Code</label>
                                            <div class="col-lg-9">
                                                <input type="text" name="external_tax_code" id="external_tax_code" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Expiry Date</label>
                                            <div class="col-lg-9">
                                                <input type="date" name="expiry_date" id="expiry_date" class="form-control">
                                            </div>
                                        </div>

                                        <div class="noti_button">
                                            <a href="javascript:" class="btn btn-primary" onclick="save_tax_rate()">Save</a>
                                            <a href="javascript:" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <!-- end here -->
	</section>
</section>	
<script>
    function get_save_data(){
    var id = $('#id').val();
    var name = $("#name").val();
    var price = $("#price").val();
    var token = '<?php echo csrf_token();?>';

    var firstErrorField = null;

    if (token == '' || token == null) {
        $("#name").css('border','1px solid red');
        if (!firstErrorField) firstErrorField = $('#name');
    }else if(token == ''){
        $("#name").css('border','');
        $("#price").css('border','1px solid red');
        if (!firstErrorField) firstErrorField = $('#price');
    }
    if (firstErrorField) {
        firstErrorField.focus();
        return false;
    } else {
        $.ajax({
            type: "POST",
            url: "{{url('admin/product_save_data')}}",
            data: new FormData($("#form_data")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {
                console.log(data);
                const product_message=$('#product_message').show();
                    if(data.errors){
                        $("html, body").animate({ scrollTop: 0 }, "slow");
                        product_message.text(data.errors);
                        product_message.addClass('alert-danger').css('border','1px solid red');
                        setTimeout(function() {
                            product_message.removeClass('alert-danger').css('border','');
                            $('#product_message').fadeOut();
                            product_message.text('');
                        }, 3000);
                    }else if(data.success === true){
                            product_message.text(data.message);
                            product_message.addClass('alert-success').css('border','1px solid green');
                            setTimeout(function() {
                                product_message.removeClass('alert-danger').css('border','');
                                document.getElementById('product_category_form').reset();
                                product_message.text('');
                                window.location.href = '<?php echo url('admin/product_list');?>';
                            }, 3000);
                    }else{
                        alert("Something went wrong. Please try again later");
                    }
                    
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
}

function suplier_row(){
   var token='<?php echo csrf_token();?>'
    $.ajax({
            type: "POST",
            url: "{{url('admin/supplier_result')}}",
            data: {_token:token},
            success: function(data) {
                console.log(data);
                $('#result').append(data);
            }
        });
}
    $('#result').on('click', '.delete_row', function() {
        $(this).closest('tr').remove();
    });
</script>					
<script>
    function gerate_code(){
        var name=$('#name');
        if(name.val() == ''){
            name.css('border','1px solid red');
            return false;
        }
        name.css('border','');
        var product_count='<?php echo $product_count+1;?>'
        var name=$("#name").val();
        var firstTwoLetters = name.substring(0, 2).toUpperCase();
        $("#product_code").val(""+firstTwoLetters+"-000"+product_count);
        $("#generate").hide();
    }
</script>
<script>
    function get_modal(modal){
        $("#"+modal).modal('show');
    }
</script>
<script>
    function save_product_category(){
        var product_category_name=$("#product_category_name").val();
        var product_cat_id=$("#product_cat_id").val();
        var product_category_status=$("#product_category_status").val();
        if(product_category_name == ''){
            $("#product_category_name").css('border','1px solid red');
            return false;
        }else{
            $("#product_category_name").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('admin/product_cat_save_data')}}",
                data: {name:product_category_name,cat_id:product_cat_id,status:product_category_status,_token:'{{ csrf_token() }}'},
                success: function(data) {
                    console.log(data);
                    const cat_message=$('#cat_message').show();
                    if(data.errors){
                        cat_message.text(data.errors);
                        cat_message.addClass('alert-danger').css('border','1px solid red');
                        setTimeout(function() {
                            cat_message.removeClass('alert-danger').css('border','');
                            $('#cat_message').fadeOut(3000);
                        }, 3000);
                    }else if(data.success === true){
                            cat_message.text(data.message);
                            cat_message.addClass('alert-success').css('border','1px solid green');
                            updateCategoryDropdown(data.data);
                            setTimeout(function() {
                                cat_message.removeClass('alert-danger').css('border','');
                                document.getElementById('product_category_form').reset();
                                $('#cat_message').fadeOut();
                                $('#product_category_modal').modal('hide');
                            }, 3000);
                    }else{
                        alert("Something went wrong. Please try again later");
                    }
                    
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
    function updateCategoryDropdown(categories) {
        // console.log(categories);return false;
        let $dropdown = $('#product_category');
        let $product_cat_id = $('#product_cat_id');
        $dropdown.empty();
        $product_cat_id.empty();
        $dropdown.append('<option disabled selected>-Any Category-</option>');

        categories.forEach(category => {
            console.log(category.full_category);
            let fullCategory = category.full_category ? category.full_category : category.name;
            $dropdown.append(`<option value="${category.id}">${fullCategory}</option>`);
            $product_cat_id.append(`<option value="${category.id}">${fullCategory}</option>`);
        });
    }
    function save_tax_rate(){
        var tax_rate_name=$("#tax_rate_name").val();
        var tax_rate=$("#tax_rateModal").val();
        var tax_rate_status=$("#tax_rate_status").val();
        var external_tax_code=$("#external_tax_code").val();
        var expiry_date=$("#expiry_date").val();
        var status=$("#tax_rate_status").val();
        if(tax_rate_name == ''){
            $("#tax_rate_name").css('border','1px solid red');
            return false;
        }else if(tax_rate == ''){
            $("#tax_rateModal").css('border','1px solid red');
            return false;
        }else{
            $("#tax_rate_name").css('border','');
            $("#tax_rateModal").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('admin/tax_save_data')}}",
                data: {name:tax_rate_name,tax_rate:tax_rate,tax_code:external_tax_code,exp_date:expiry_date,status:status,_token:'{{ csrf_token() }}'},
                success: function(data) {
                    console.log(data);
                    const tax_message=$('#tax_message').show();
                    if(data.errors){
                        tax_message.text(data.errors);
                        tax_message.addClass('alert-danger').css('border','1px solid red');
                        setTimeout(function() {
                            tax_message.removeClass('alert-danger').css('border','');
                            $('#tax_message').fadeOut();
                        }, 3000);
                    }else if(data.success === true){
                            tax_message.text(data.message);
                            tax_message.addClass('alert-success').css('border','1px solid green');
                            $("#tax_id").append('<option value="'+data.data.id+'">'+data.data.name+'</option>')
                            $("#tax_rate").append('<option value="'+data.data.id+'">'+data.data.name+'</option>')
                            setTimeout(function() {
                                tax_message.removeClass('alert-danger').css('border','');
                                document.getElementById('tax_rate_form').reset();
                                $('#tax_message').fadeOut();
                                $('#product_category_modal').modal('hide');
                            }, 3000);
                    }else{
                        alert("Something went wrong. Please try again later");
                    }
                    
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
</script>
@endsection