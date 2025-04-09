@extends('backEnd.layouts.master')

@section('title','Users')

@section('content')

<?php
    $page_url = url('admin/product_group');
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
                                            Add Product Group <i class="fa fa-plus"></i>
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
                                        <th>Product Group</th>
                                        <th>Description</th>
                                        <th>Cost Price</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        if(count($product_group) == 0){ ?>
                                            <?php
                                                echo '<tr style="text-align:center">
                                                      <td colspan="7">No Product Group found.</td>
                                                      </tr>';
                                            ?>
                                        <?php 
                                        } 

                                        else
                                        {
                                            foreach($product_group as $key => $val) 
                                            {  
                                                
                                                ?>

                                        <tr >
                                            <td class="user_name">{{ ucfirst($val->name) }}</td>
                                            <td class="transform-none" style="text-transform: none;">{{$val->description}}</td>
                                            <td>{{$val->cost}}</td>
                                            <td>{{$val->price}}</td>
                                            
                                            <td>
                                                @if($val->status == 1)
                                                    <a href="javascript:" onclick="status_change('{{base64_encode($val->id)}}',0)" class="btn btn-success">Active</a>
                                                @else
                                                <a href="javascript:" class="btn btn-danger" onclick="status_change('{{base64_encode($val->id)}}',1)">Inactive</a>
                                                @endif
                                            </td>
                                            <td class="action-icn">
                                                <a href="javascript:void(0)" class="edit" data-id="{{$val->id}}" data-name="{{$val->name}}" data-description="{{$val->description}}" data-code="{{$val->code}}" data-cost="{{$val->cost}}" data-price="{{$val->price}}" data-status="{{$val->status}}"><span style= "font-size: 13px; color: #000;"><span style= "color: #000"><i data-toggle="tooltip" title="Edit" class="fa fa-edit fa-lg"></i></span></a> &nbsp &nbsp &nbsp 

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
                <h4 class="modal-title"> Product Groups </h4>
            </div>
            <div class="modal-body">
                <div class="row">  
                    <div class="foor-box-wrap foor-plan">
                        <form id="flowForm">
                        <div class="alert text-center" id="catlogue_message" style="display:none"></div>
                            <input type="hidden" id="id" name="id">
                            @csrf
                            <div class="custom-fieldset"> 
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Product Group<span class="radStar "> *</span></label>
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
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Code</label>
                                            <div class="col-lg-9">
                                                <input type="text" name="code" id="code" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Status</label>
                                            <div class="col-lg-9">
                                            <select id="status" name="status" class="form-control editInput">
                                                <option value="1">Active</option>
                                                <option value="2">Inactive</option>
                                            </select>
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Cost Price</label>
                                            <div class="col-lg-9">
                                                <input type="text" name="cost" id="costPrice" class="form-control" value="0.00" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <label class="col-lg-3 control-label">Price</label>
                                            <div class="col-lg-9">
                                                <input type="text" name="price" id="productPrice" class="form-control" value="0.00" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <h4 class="text-left text-primary">Product Details</h4>
                                        <div class="mb-3 row">
                                            <label class="col-sm-2 control-label">Select Product</label>
                                            <div class="col-sm-3"><input type="text" name="name" id="name" class="form-control" placeholder="Type to add product"></div>
                                            <div class="parent-container"></div>
                                            <div class="col-sm-7">
                                                <div class="d-flex">
                                                    <span class="afterPlusText"> (Type to add product or <a href="javascript:void(0)" onclick="add_item();" id="proClickHerePopup" class="text-primary">Click here</a> to view all product)</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <table class="table table-striped table-hover table-bordered" id="productListTable">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Product</th>
                                                        <th>Cost Price</th>
                                                        <th>Price</th>
                                                        <th>QTY</th>
                                                        <th>Amount</th>
                                                        <th>Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="product_group_products_list">
                                                   
                                                </tbody>
                                                <tfoot id="containerA"></tfoot>
                                            </table>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="noti_button">
                                <input type="button" class="btn btn-primary" value="Save" onclick="getSaveData()"></input>
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
        var name=$(this).data('name');
        var description=$(this).data('description');
        var code=$(this).data('code');
        var cost=$(this).data('cost');
        var price=$(this).data('price');
        var status=$(this).data('status');

        $("#id").val(id);
        $("#name").val(name);
        $("#description").val(description);
        $("#code").val(code);
        $("#costPrice").val(cost);
        $("#productPrice").val(price);
        $("#status").val(status);
        $.ajax({
            url: '{{ url("admin/ProductGroupProductsList") }}',
            method: 'Post',
            data: {
                _token:'{{ csrf_token() }}',id:id 
            },
            success: function(response) {
                console.log(response);
                const tableBody = document.querySelector(`#productListTable tbody`);
                if (response.data.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id='EmptyError'
                        const noDataCell = document.createElement('td');
                        noDataCell.setAttribute('colspan', 7);
                        noDataCell.innerHTML = '<span class="text-center" style="color: #e10078;">Sorry, there are no items available</span>';
                        noDataCell.style.textAlign = 'center';
                        noDataRow.appendChild(noDataCell);
                        tableBody.appendChild(noDataRow);
                }else{
                    tableBody.innerHTML = '';
                    totalAmount=0;
                    response.data.forEach(item => {
                            const row = document.createElement('tr');
                            const codeCell = document.createElement('td');
                            codeCell.textContent = item.product_code;
                            row.appendChild(codeCell);

                            const hiddenInputId = document.createElement('input');
                            hiddenInputId.type = 'hidden';
                            hiddenInputId.className = 'ids';
                            hiddenInputId.name = 'ids[]'; 
                            hiddenInputId.value = item.id;
                            row.appendChild(hiddenInputId);

                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.className = 'product_id';
                            hiddenInput.name = 'product_ids[]'; 
                            hiddenInput.value = item.product_id;
                            row.appendChild(hiddenInput);

                            const nameCell = document.createElement('td');
                            nameCell.innerHTML = item.product_name;
                            row.appendChild(nameCell);

                            const costCell = document.createElement('td');
                            const inputCost = document.createElement('input');
                            inputCost.type = 'text'; 
                            inputCost.className = 'cost_price input50';
                            inputCost.name = 'cost_price[]'; 
                            inputCost.value = item.cost_price;
                            GrandCostPrice=GrandCostPrice+Number(item.cost_price);
                            costCell.appendChild(inputCost);
                            row.appendChild(costCell);

                            const priceCell = document.createElement('td');
                            const inputPrice = document.createElement('input');
                            inputPrice.type = 'text'; 
                            inputPrice.className = 'product_price input50';
                            inputPrice.addEventListener('input', function() {
                                updateAmount(row);
                            });
                            inputPrice.name = 'product_price[]'; 
                            inputPrice.value = item.price;
                            GrandPrice=GrandPrice+Number(item.price);
                            priceCell.appendChild(inputPrice);
                            row.appendChild(priceCell);

                            const qtyCell = document.createElement('td');
                            const inputQty = document.createElement('input');
                            inputQty.type = 'text'; 
                            inputQty.className = 'qty input50';
                            inputQty.addEventListener('input', function() {
                                updateAmount(row);
                            });
                            inputQty.name = 'qty[]';
                            inputQty.value = item.quantity; 
                            qtyCell.appendChild(inputQty);
                            row.appendChild(qtyCell);

                            const amountCell = document.createElement('td');
                            amountCell.innerHTML = '$'+ item.quantity*parseFloat(item.price).toFixed(2);
                            amountCell.className = "price";
                            row.appendChild(amountCell);
                            totalAmount=totalAmount+Number(item.price)*item.quantity;
                            const deleteCell = document.createElement('td');
                            deleteCell.innerHTML = '<i class="fa fa-trash-o fa-lg fa-2x deleteRow" style="color: red;"></i>';
                            row.appendChild(deleteCell);
                            tableBody.appendChild(row);

                        });
                        $("#costPrice").val(parseFloat(cost).toFixed(2));
                        $("#productPrice").val(parseFloat(price).toFixed(2));
                        var htmlCode=`<tr>
                                            <td colspan="4"></td>
                                            <td>Total</td><td id="GrandTotalAmount">$`+parseFloat(totalAmount).toFixed(2)+`</td>
                                            <td></td>
                                    </tr>`;
                        $("#containerA").html(htmlCode); 
                     
                    }
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
        var code=$("#code").val();
        var status=$("#status").val();
        var cost=$("#costPrice").val();
        var price=$("#productPrice").val();
        var token='<?php echo csrf_token();?>'
        const tableRows = document.querySelectorAll('#productListTable tbody tr');
        // console.log(tableRows);return false;
        const products = [];
        tableRows.forEach(row => {
            if (row.id === 'EmptyError') {
                return;
            }
            const productIdField = row.querySelector('.product_id');
            const id = row.querySelector('.ids');
            // console.log(row);
            const product = {
                id: id ? id.value : null,
                code: row.cells[0].textContent.trim(),
                product: row.cells[1].textContent.trim(),
                product_id: productIdField ? productIdField.value : null,
                cost_price: row.querySelector('.cost_price').value,
                price: row.querySelector('.product_price').value,
                qty: row.querySelector('.qty').value,
                // amount: row.querySelector('.amount').value,
            };

            products.push(product); // Add product to the array
        });
        // console.log(products);return false;
        $.ajax({  
            type:"POST",
            url:"{{url('admin/save_productGroup')}}",
            data:{id:id,name:name,description:description,code:code,status:status,status:status,cost:cost,price:price,products:JSON.stringify({
                    products
                }),_token:token},
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
                }else if(data.success === true){
                    catlogue_message.text(data.message);
                    catlogue_message.addClass('alert-success').css('border','1px solid green');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
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
                productGroupTable(response.data, 'productListTable');
                $("#ProductListModal").modal('hide');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    var totalAmount=0;
    var GrandCostPrice=0;
    var GrandPrice=0;
    function productGroupTable(data, tableId) {
        const tableBody = document.querySelector(`#${tableId} tbody`);
        
        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            const noDataCell = document.createElement('td');

            noDataCell.setAttribute('colspan', 4);
            noDataCell.textContent = 'No products found';
            noDataCell.style.textAlign = 'center';

            noDataRow.appendChild(noDataCell);
            tableBody.appendChild(noDataRow);
        } else {
            const emptyErrorRow = document.getElementById('EmptyError');
            if (emptyErrorRow) {
                emptyErrorRow.remove();
            }
            data.forEach(item => {
                const row = document.createElement('tr');
                const codeCell = document.createElement('td');
                codeCell.textContent = item.product_code;
                row.appendChild(codeCell);
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.className = 'product_id';
                hiddenInput.name = 'product_ids[]';
                hiddenInput.value = item.id;
                row.appendChild(hiddenInput);

                const nameCell = document.createElement('td');
                nameCell.innerHTML = item.product_name;
                row.appendChild(nameCell);

                const costCell = document.createElement('td');
                const inputCost = document.createElement('input');
                inputCost.type = 'text';
                inputCost.className = 'cost_price input50';
                inputCost.name = 'cost_price[]';
                inputCost.value = item.cost_price; 
                GrandCostPrice=GrandCostPrice+Number(item.cost_price);
                costCell.appendChild(inputCost);
                row.appendChild(costCell);

                const priceCell = document.createElement('td');
                const inputPrice = document.createElement('input');
                inputPrice.type = 'text'; 
                inputPrice.className = 'product_price input50';
                inputPrice.addEventListener('input', function() {
                    updateAmount(row);
                });
                inputPrice.name = 'product_price[]'; 
                inputPrice.value = item.price; 
                GrandPrice=GrandPrice+Number(item.price);
                priceCell.appendChild(inputPrice);
                row.appendChild(priceCell);

                const qtyCell = document.createElement('td');
                const inputQty = document.createElement('input');
                inputQty.type = 'text'; 
                inputQty.className = 'qty input50';
                inputQty.addEventListener('input', function() {
                    updateAmount(row);
                });
                inputQty.name = 'qty[]'; 
                inputQty.value = '1'; 
                qtyCell.appendChild(inputQty);
                row.appendChild(qtyCell);

                const amountCell = document.createElement('td');
                amountCell.innerHTML = '$'+ parseFloat(item.price).toFixed(2);
                amountCell.className = "price";
                row.appendChild(amountCell);
                totalAmount=totalAmount+Number(item.price);
                const deleteCell = document.createElement('td');
                deleteCell.innerHTML = '<i class="fa fa-trash-o fa-lg fa-2x deleteRow" style="color: red;"></i>';
                row.appendChild(deleteCell);

                tableBody.appendChild(row);


            });
            $("#costPrice").val(parseFloat(GrandCostPrice).toFixed(2));
            $("#productPrice").val(parseFloat(GrandPrice).toFixed(2));
            var htmlCode=`<tr>
                                <td colspan="4"></td>
                                <td>Total</td><td id="GrandTotalAmount">$`+parseFloat(totalAmount).toFixed(2)+`</td>
                                <td></td>
                          </tr>`;
            $("#containerA").html(htmlCode);
        }
    }

    document.querySelector("#productListTable").addEventListener("click", function(e) {
        if (e.target && e.target.classList.contains("deleteRow")) {
            
            const row = e.target.closest("tr");
            if (row) {
                row.remove();
                const amountCell = row.querySelector(".price");
                const amount = parseFloat(amountCell.textContent.replace(/[^\d.]/g, "")) || 0;
                totalAmount -= amount;
                if(totalAmount === 0){
                    $("#costPrice").val('0.00');
                   $("#containerA").hide();
                }else{
                    $("#containerA").show();
                }
                document.getElementById("GrandTotalAmount").textContent = "$" + totalAmount.toFixed(2);
                $("#productPrice").val(totalAmount.toFixed(2));
            }
        }
    });
    function updateAmount(row) {
        // console.log(row)
        const priceInput = row.querySelector('.product_price');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('td:nth-last-child(2)');
        const price = parseFloat(priceInput.value) || 0; 
        const qty = parseInt(qtyInput.value) || 1;
        const amount = price * qty; 
        amountCell.textContent = '$'+amount.toFixed(2); 

        var calculation=0;
        $('.price').each(function () {
            const priceText = $(this).text();
            const numericValue = parseFloat(priceText.replace(/[^\d.]/g, ''));
            calculation=calculation+numericValue;
        });
        totalAmount=calculation;
        document.getElementById('GrandTotalAmount').innerHTML='$'+totalAmount.toFixed(2);
        $("#productPrice").val(totalAmount.toFixed(2));
    }

    
</script>

@endsection


