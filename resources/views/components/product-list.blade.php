<style>
    .addProduvtBg.costUpdatePop {
        padding: 10px;
        background-color: #edf6fb;
        margin: 0;
    }
    .addProduvtBg.costUpdatePop p {
        font-size: 13px;
        margin-bottom: 0;
        color: #168fdb;
    }
    /* .addProduvtBg.costUpdatePop a.udateBtn{

    } */
</style>

<!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
<div class="modal fade" id="productModalBAC" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="productModalBACLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title fs-5" id="productModalBACLabel">Product List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <div class="modal-body ">
                    <div class="extraInformationTab">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button class="nav-link active" id="nav-product-tab" data-bs-toggle="tab" data-bs-target="#nav-product" type="button" role="tab" aria-controls="nav-Notes" aria-selected="true">Product(s) <span class="productCount" id="productCount"></span></button>
                                <button class="nav-link" onclick="getproductsOnType(2, 'setServiceInTable')" id="nav-secrices-tab" data-bs-toggle="tab" data-bs-target="#nav-secrices" type="button" role="tab" aria-controls="nav-secrices" aria-selected="false">Service(s) <span class="productCount" id="serviceCount"></span></button>
                                <button class="nav-link" onclick="getproductsOnType(3, 'setConsumableInTable')" id="nav-consumable-tab" data-bs-toggle="tab" data-bs-target="#nav-consumable" type="button" role="tab" aria-controls="nav-consumable" aria-selected="false">Consumable(s) <span class="productCount" id="consumableCount"></span></button>
                                <button class="nav-link" id="nav-productGroup-tab" onclick="getproductsOnType(4, 'setGroupInTable')" data-bs-toggle="tab" data-bs-target="#nav-productGroup" type="button" role="tab" aria-controls="nav-productGroup" aria-selected="false">Product Group(s) <span class="productCount" id="groupCount"></span></button>
                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-product" role="tabpanel" aria-labelledby="nav-product-tab" tabindex="0">
                                <div class="py-4">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <input type="hidden" id="selectedProductIds" name="product_ids" value="[]">
                                            <select class="form-control editInput selectOptions" id="product_categories">
                                                <option>--Any Category--</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control editInput" placeholder="Search Term" aria-label="Recipient's username" aria-describedby="button-addon2">
                                                <button class="btn btn-outline-secondary editInput profileDrop" type="button" id="button-addon2">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="alphabeticListHolder">
                                        <ul class="alphabeticList">
                                            <li><a href="#" class="alphabeticLink" data-term="all" data-search_mode="ALL">All</a></li>
                                            @for ($i = 65; $i <= 90; $i++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ chr($i) }}" data-search_mode="STARTS">{{ chr($i) }}</a></li>
                                            @endfor
                                            <li>&nbsp;</li>
                                            @for($j = 0; $j <= 9; $j++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ $j }}" data-search_mode="STARTS">{{ $j }}</a></li>
                                            @endfor
                                        </ul>
                                        <br class="clear">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="pagecounter pb-2">
                                        <h6>1-1 of 1 product(s) </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="setProductInTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Category </th>
                                                        <th>Product</th>
                                                        <th>Description </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="insrt_product_and_detail">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-secrices" role="tabpanel" aria-labelledby="nav-secrices-tab" tabindex="0">
                                <div class="py-4">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <select class="form-control editInput selectOptions" id="product_categories">
                                                <option>--Any Category--</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control editInput" placeholder="Search Term" aria-label="Recipient's username" aria-describedby="button-addon2">
                                                <button class="btn btn-outline-secondary editInput profileDrop" type="button" id="button-addon2">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="alphabeticListHolder">
                                        <ul class="alphabeticList">
                                            <li><a href="#" class="alphabeticLink" data-term="all" data-search_mode="ALL">All</a></li>
                                            @for ($i = 65; $i <= 90; $i++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ chr($i) }}" data-search_mode="STARTS">{{ chr($i) }}</a></li>
                                            @endfor
                                            <li>&nbsp;</li>
                                            @for($j = 0; $j <= 9; $j++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ $j }}" data-search_mode="STARTS">{{ $j }}</a></li>
                                            @endfor
                                        </ul>
                                        <br class="clear">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="pagecounter pb-2">
                                        <h6>1-1 of 1 product(s) </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="setServiceInTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Category </th>
                                                        <th>Product</th>
                                                        <th>Description </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="insrt_product_and_detail">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-consumable" role="tabpanel" aria-labelledby="nav-consumable-tab" tabindex="0">
                                <div class="py-4">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <select class="form-control editInput selectOptions" id="product_categories">
                                                <option>--Any Category--</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control editInput" placeholder="Search Term" aria-label="Recipient's username" aria-describedby="button-addon2">
                                                <button class="btn btn-outline-secondary editInput profileDrop" type="button" id="button-addon2">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="alphabeticListHolder">
                                        <ul class="alphabeticList">
                                            <li><a href="#" class="alphabeticLink" data-term="all" data-search_mode="ALL">All</a></li>
                                            @for ($i = 65; $i <= 90; $i++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ chr($i) }}" data-search_mode="STARTS">{{ chr($i) }}</a></li>
                                                @endfor
                                                <li>&nbsp;</li>
                                                @for($j = 0; $j <= 9; $j++)
                                                    <li><a href="#" class="alphabeticLink" data-term="{{ $j }}" data-search_mode="STARTS">{{ $j }}</a></li>
                                                    @endfor
                                        </ul>
                                        <br class="clear">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="pagecounter pb-2">
                                        <h6>1-1 of 1 product(s) </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="setConsumableInTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Category </th>
                                                        <th>Product</th>
                                                        <th>Description </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="insrt_product_and_detail">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="nav-productGroup" role="tabpanel" aria-labelledby="nav-productGroup-tab" tabindex="0">
                                <div class="py-4">
                                    <div class="row">
                                       <div class="col-lg-7">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control editInput" placeholder="Search Term" aria-label="Recipient's username" aria-describedby="button-addon2">
                                                <button class="btn btn-outline-secondary editInput profileDrop" type="button" id="button-addon2">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="alphabeticListHolder">
                                        <ul class="alphabeticList">
                                            <li><a href="#" class="alphabeticLink" data-term="all" data-search_mode="ALL">All</a></li>
                                            @for ($i = 65; $i <= 90; $i++)
                                                <li><a href="#" class="alphabeticLink" data-term="{{ chr($i) }}" data-search_mode="STARTS">{{ chr($i) }}</a></li>
                                                @endfor
                                                <li>&nbsp;</li>
                                                @for($j = 0; $j <= 9; $j++)
                                                    <li><a href="#" class="alphabeticLink" data-term="{{ $j }}" data-search_mode="STARTS">{{ $j }}</a></li>
                                                    @endfor
                                        </ul>
                                        <br class="clear">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="pagecounter pb-2">
                                        <h6>1-1 of 1 product(s) </h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="productDetailTable">
                                            <table class="table" id="setGroupInTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Product Group</th>
                                                        <th>Description </th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="insrt_product_and_detail">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end modal body -->
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn profileDrop" data-bs-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn profileDrop" id="listAllProduct">Save changes</button> -->
            </div>
        </div>
    </div>
</div>
<!-- CalculatePop Modal -->
<div class="modal fade" id="calculatePop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="productGroupModalLabel"> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 class="contTitle text-start">Product</h4>
                <div class="productDetailTable mt-2">
                    <table class="table" id="containerA">
                        <thead class="table-light">
                            <tr>
                                <th>Group Code</th>
                                <th>Group Product </th>
                                <th>Group Description</th>
                                <th>Qty</th>
                                <th>Cost Price($)</th>
                                <th>Price($)</th>
                                <th>Amount($) </th>
                                <th>Profit($)</th>
                            </tr>
                        </thead>
                        <tbody id="productGroupModalData">
                        
                        </tbody>
                    </table>
                </div>

                <h4 class="contTitle text-start">Products</h4>
                <div class="addProduvtBg costUpdatePop row">
                 <div class="col-md-10">
                    <p>One or moere product prices have been changed. Select 'Update' next to each item to use the default product price. <br>Alternatively select 'Update All Prices' to use the default product price for all items.</p>
                  </div>
                    <div class="col-md-2">
                       <a href="#!" class="profileDrop">Update All Prices </a>
                    </div>
                </div>
                <div class="mb-3 mt-2 row">
                    <div class="col-sm-3">
                        <input type="text" class="form-control editInput" id="inputCountry" placeholder="Search Product">
                    </div>
                    <div class="col-sm-7">
                        <div class="plusandText">
                            <a href="#!" class="formicon" id="cost_product_popup"><i class="fa-solid fa-square-plus"></i></a>
                            <span class="afterPlusText"> (Type to view product or <a href="#!" onclick="openProductListModal()">Click here</a> to view all assets)</span>
                        </div>
                    </div>
                </div>

                <div class="costProdut">
                    <div class="productDetailTable">
                        <table class="table" id="containerA">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Product </th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Cost Price($)</th>
                                    <th>Price($)</th>
                                    <th>Amount($) </th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- <tr>
                                    <td colspan="7">
                                        
                                    </td>
                                </tr> -->
                                <tr>
                                    <td colspan="3"></td>
                                    <td>Totale</td>
                                    <td>0</td>
                                    <td>$0.00</td>
                                    <td>$0.00</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <!-- End off Modal-body -->
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop">Add as Group</button>
                <button type="button" class="profileDrop">Add as Item</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- End offCalculatePop Modal -->
<script>
    function openProductListModal() {
        $.ajax({
            url: '{{ route("item.ajax.getCategoriesList") }}',
            method: 'GET',
            success: function(response) {
                console.log(response);
                var productCat = document.getElementById('product_categories');
                productCat.innerHTML = '';
                let newOption = document.createElement('option');
                newOption.text = "-Any Category-";
                productCat.appendChild(newOption);
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.name;
                    productCat.appendChild(option);
                });
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        getproductsOnType(1, 'setProductInTable');

        $.ajax({
            url: '{{ route("item.ajax.getProductCounts") }}',
            method: 'GET',
            success: function(response) {
                console.log("CountPeoduct", response.data);
                document.getElementById('productCount').textContent = response.data.product.toString().padStart(2, '0');
                document.getElementById('serviceCount').textContent = response.data.service.toString().padStart(2, '0');
                document.getElementById('consumableCount').textContent = response.data.consumable.toString().padStart(2, '0');
                document.getElementById('groupCount').textContent = response.data.product_group.toString().padStart(2, '0');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });

        $('#productModalBAC').modal('show');
    }

    function populateTable(data, tableId) {
        console.log("tableId", tableId);
        var type=data.type;
        var data=data.data;
        const tableBody = document.querySelector(`#${tableId} tbody`);
        tableBody.innerHTML = ''; // Clear the table body

        if (data.length === 0) {
            const noDataRow = document.createElement('tr');
            const noDataCell = document.createElement('td');

            noDataCell.setAttribute('colspan', 4);
            noDataCell.textContent = 'No products found';
            noDataCell.style.textAlign = 'center'; // Center the message

            noDataRow.appendChild(noDataCell);
            tableBody.appendChild(noDataRow);
        } else {
            data.forEach(item => {
             
                const row = document.createElement('tr');
                row.setAttribute('data-id', item.id);
                if(type == 4){
                    row.addEventListener('click', (event) => {
                        if (event.target && event.target.nodeName === 'TD') {
                            const clickedRow = event.target.parentNode;
                            const productInput = clickedRow.querySelector('input.product_id');
                            const productId = parseInt(clickedRow.getAttribute('data-id'));
                            const productIdsInput = document.getElementById('selectedProductIds');
                            let productIds = JSON.parse(productIdsInput.value || '[]');
                            if (!productIds.includes(productId)) {
                                productIds.push(productId);
                            } else {
                                productIds = productIds.filter(id => id !== productId);
                            }
                            productIdsInput.value = JSON.stringify(productIds);
                            // getProductData(productId);
                            
                        }
                    });
                    const nameCell = document.createElement('td');
                    nameCell.textContent = item.name;
                    row.appendChild(nameCell);

                    const descriptionCell = document.createElement('td');
                    descriptionCell.textContent = item.description;
                    row.appendChild(descriptionCell);

                    const ModalCell = document.createElement('td');
                    ModalCell.innerHTML = '<a href="javascript:void(0)" class="formicon" id="openPopupButton" onclick="get_modal('+item.id+')"><i class="fa-solid fa-square-plus"></i></a>';
                    row.appendChild(ModalCell);
                }else{
                    row.addEventListener('click', (event) => {
                        if (event.target && event.target.nodeName === 'TD') {
                            const clickedRow = event.target.parentNode;
                            const productInput = clickedRow.querySelector('input.product_id');
                            const productId = parseInt(clickedRow.getAttribute('data-id'));
                            const productIdsInput = document.getElementById('selectedProductIds');
                            let productIds = JSON.parse(productIdsInput.value || '[]');
                            if (!productIds.includes(productId)) {
                                productIds.push(productId);
                            } else {
                                productIds = productIds.filter(id => id !== productId);
                            }
                            productIdsInput.value = JSON.stringify(productIds);
                            getProductData(productId);
                            
                        }
                    });

                    const codeCell = document.createElement('td');
                    codeCell.textContent = item.product_code;
                    row.appendChild(codeCell);

                    const categoryCell = document.createElement('td');
                    categoryCell.innerHTML = item.name;
                    row.appendChild(categoryCell);

                    const productCell = document.createElement('td');
                    productCell.textContent = item.product_name;
                    row.appendChild(productCell);

                    const descriptionCell = document.createElement('td');
                    descriptionCell.textContent = item.description;
                    row.appendChild(descriptionCell);
                }
                console.log("row", tableBody.appendChild(row));
                tableBody.appendChild(row);
            });
        }
    }

    function getproductsOnType(type, tableId) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: '{{ route("item.ajax.getProductList") }}',
            method: 'Post',
            data: {
                type: type
            },
            success: function(response) {
                console.log(response);
                populateTable(response, tableId);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    function get_modal(id){
        $("#calculatePop").modal('show');
        $.ajax({
            url: '{{ url("item/ProductGroupProductsdetails") }}',
            method: 'Post',
            data: {
                id: id, _token:'{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.data && response.data.length > 0 && response.data[0].name) {
                    var productGroupModalData=response.data[0];
                    document.getElementById('productGroupModalLabel').innerHTML='Product Group: '+productGroupModalData.name;
                    var productGroupModalDesign='';
                    productGroupModalData.product_group_product.forEach(item => {
                        const amount=item.price*item.quantity;
                        const profit=amount-item.cost_price;
                        productGroupModalDesign+=`<tr>
                                <td>
                                    <div class="CSPlus">
                                        <span class="plusandText">
                                            <input type="text" class="form-control editInput input80" name="modal_code" id="modal_code" value="`+item.product_code+`">
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput" name="modal_group_product" id="modal_group_product" value="`+item.product_name+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <textarea class="form-control textareaInput rounded-0" name="modal_description" id="modal_description" rows="1" placeholder="Description">`+productGroupModalData.description+`</textarea>
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_qty" name="modal_qty" value="`+item.quantity+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_cost" name="modal_cost" value="`+item.cost_price+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_price" name="modal_price" value="`+item.price+`">
                                    </div>
                                </td>

                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_amount" name="modal_amount" value="`+amount+`">
                                    </div>
                                </td>
                                <td>
                                    <div class="">
                                        <input type="text" class="form-control editInput input50" id="modal_profit" name="modal_profit" value="`+profit+`">
                                    </div>
                                </td>
                            </tr>`;
                    });
                    $('#productGroupModalData').html(productGroupModalDesign);
                }else{
                    document.getElementById('productGroupModalLabel').innerHTML='Product Group: ';
                    $('#productGroupModalData').html('<tr><td colspan="8" class="text-center" style="color: #e10078;">Sorry, there are no items available</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
</script>