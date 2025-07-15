@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Product Group')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .parent-container {
        position: absolute;
        background: #fff;
    }

    #productListTable th,
    #productListTable td {
        text-align: center;
    }

    .dropdown-item {
        padding: 6px 15px;
        font-size: 13px;
        color: #212529;
        text-align: inherit;
        text-decoration: none;
        display: block;
        width: 100%;
        background-color: transparent;
        border: 0;
        border-radius: 0;
        transition: all 0.2s ease-in-out;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #212529;
    }
</style>
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Product Group </h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                            <div class="jobsection justify-content-end">
                                <a href="javascript:void(0)" class="btn btn-warning" onclick="open_productgroupmodal()"><i class="fa fa-plus"></i> Add</a>
                                <a href="javascript:void(0)" id="deleteSelectedRows" class="btn btn-danger">Delete</a>
                                <span class="text-danger text-center deletemsg"></span>
                            </div>
                        </div>

                        <!--Start Import/Export Popup -->
                        {{-- <div id="importExportpopup" class="importExportMrgin">
                                <div class="popup-content">
                                    <div class="popupTitle">
                                        <span class="">Import/Export - Product</span>
                                        <span class="close" id="closeimportExportPopup">&times;</span>
                                    </div>
                                    <div class="contantbodypopup">
                                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center p-0 mt-3 mb-2">
                                            <div class="card">
                                                <form id="msform">
                                                    <!-- progressbar -->
                                                    <ul id="progressbar">
                                                        <li class="active" id="account"><strong>Import/Export</strong></li>
                                                        <li id="personal"><strong>Action</strong></li>
                                                        <li id="payment"><strong>Upload</strong></li>
                                                        <li id="confirm"><strong>Summary</strong></li>
                                                    </ul>

                                                    <fieldset>
                                                        <div class="form-card">
                                                            <div class="newJobForm p-3 mb-3">
                                                                <h4 class="contTitle text-start">Import Templates</h4>
                                                                <label class="col-form-label">Download an empty template to add new products or prices</label>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <label class="importTemp">Product & price <a href="#!"> <span class="material-symbols-outlined">download</span></a></label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="importTemp"> Supplier & price <a href="#!"> <span class="material-symbols-outlined">download</span> </a> </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="newJobForm p-3">
                                                                <h4 class="contTitle text-start">Import</h4>
                                                            </div>
                                                        </div>
                                                        <input type="button" name="next" class="next action-button" value="Next" />
                                                    </fieldset>

                                                    <fieldset>
                                                        <div class="form-card">
                                                            <label class="fieldlabels">First Name: *</label> <input type="text" />
                                                        </div>
                                                        <input type="button" name="next" class="next action-button" value="Next" />
                                                        <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                                                    </fieldset>


                                                    <fieldset>
                                                        <div class="form-card">
                                                            <label class="fieldlabels">Upload Your Photo:</label>
                                                            <input type="file" name="pic" accept="image/*">
                                                        </div> 
                                                        <input type="button" name="next" class="next action-button" value="Submit" /> 
                                                        <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                                                    </fieldset>


                                                    <fieldset>
                                                        <div class="form-card">
                                                            <h2 class="purple-text text-center"><strong>SUCCESS !</strong></h2> <br>
                                                            <div class="row justify-content-center">
                                                                <div class="col-3"> <img src="https://i.imgur.com/GwStPmg.png" class="fit-image"> </div>
                                                            </div> <br><br>
                                                            <div class="row justify-content-center">
                                                                <div class="col-7 text-center">
                                                                    <h5 class="purple-text text-center">You Have Successfully Signed Up </h5>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                                                    </fieldset>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        <!-- End off Import/Export Popup -->

                        <div class="col-lg-12">
                            <div class="maimTable productDetailTable mb-4 table-responsive">
                                <table id="myTable" class="table border-top border-bottom" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"> <label for="selectAll"> </label></th>
                                            <th>#</th>
                                            <th>Product Group</th>
                                            <th>Description</th>
                                            <th>Cost Price</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productGroups as $productGroup)
                                        <tr>
                                            <td><input type="checkbox" id="" class="delete_checkbox" value="{{$productGroup->id}}"></td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $productGroup->name }}</td>
                                            <td>{{ $productGroup->description }}</td>
                                            <td>{{ $productGroup->cost }}</td>
                                            <td>{{ $productGroup->price }}</td>
                                            <td>
                                                @if($productGroup->status == 1)
                                                <span class="grencheck"><i class="fa fa-check-circle"></i></span>
                                                @else
                                                <span class="grayCheck"><i class="fa fa-check-circle"></i></span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a href="#" class="btn btn-primary btn-sm" data-toggle="dropdown">
                                                        Action <i class="fa fa-caret-down"></i>
                                                    </a>
                                                    <div class="dropdown-menu fade-up m-0">
                                                        <a href="javascript:void(0)" class="dropdown-item fetch_data" data-id="{{$productGroup->id}}" data-home_id="{{$productGroup->home_id}}" data-name="{{$productGroup->name}}" data-description="{{$productGroup->description}}" data-code="{{$productGroup->code}}" data-cost="{{$productGroup->cost}}" data-price="{{$productGroup->price}}" data-status="{{$productGroup->status}}">Edit Details</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ********************************** -->
<div class="modal fade" id="itemsCatagoryModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="itemsCatagoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title fs-5" id="itemsCatagoryModalLabel">Product Groups</h4>
            </div>
            <div class="modal-body ">
                <div class="contantbodypopup p-0">
                    <div class="" id="message"></div>
                    <form action="" id="add_product_group_form">
                        <input type="hidden" id="id" name="id">
                        <div class="row pt-3">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Product Group <span class="radStar">*</span></label>
                                    <input type="text" class="form-control editInput" name="name" id="name" value="">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Description</label>
                                    <textarea class="form-control textareaInput" name="description" id="description" rows="3" placeholder=""></textarea>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Code</label>
                                    <input type="text" class="form-control editInput" id="code" name="code" value="">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Status</label>
                                    <select class="form-control editInput selectOptions" status="status" id="status">
                                        <option> Active </option>
                                        <option> Inactive </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Cost Price</label>
                                    <input type="text" class="form-control editInput" name="cost" id="costPrice" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="0.00">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2 col-form-label">Price</label>
                                    <input type="text" class="form-control editInput" id="productPrice" name="price" oninput="this.value = this.value.replace(/[^0-9]/g, '');" value="0.00">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <h4 class="contTitle">Product Details</h4>
                                <div class="mb-3">
                                    <div class="row d-flex align-items-center">
                                        <label class="col-sm-2 col-form-label">Select product</label>
                                        <div class="col-sm-3">
                                            <input type="text" class="form-control editInput" id="search-product" placeholder="Type to add product">
                                            <div class="parent-container"></div>
                                        </div>
                                        <div class="col-sm-7">
                                            <div class="plusandText">
                                                <span class="afterPlusText"> (Type to add product or <a href="javascript:void(0)" onclick="openProductListModal();" id="proClickHerePopup" class="taxt_blue">Click here</a> to view all product)</span>
                                            </div>

                                            <!--Start (Type to add product or Popup -->
                                            @include('components.product-list')
                                            <!-- End off (Type to add product or Popup -->
                                        </div>
                                    </div>
                                </div>
                                <div class="maimTable productDetailTable mb-4 table-responsive">
                                    <table class="table border-top border-bottom" id="productListTable">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Product </th>
                                                <th>Cost Price</th>
                                                <th>Price </th>
                                                <th>Qty</th>
                                                <th>Amount </th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody id="product_group_products_list">
                                        </tbody>
                                        <tfoot class="table totlepayment add_table_insrt33" id="containerA">
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> <!-- end modal body -->
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" onclick="saveProductGroup()">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- ***************************************** -->

<script>
    function saveProductGroup() {

        const tableRows = document.querySelectorAll('#productListTable tbody tr');
        const products = [];


        // Loop through each row to gather data
        tableRows.forEach(row => {
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

        console.log(products);
        var id = $("#id").val();
        var url = '{{ route("item.ajax.saveProductGroup") }}';
        if (id != '') {
            url = '{{ route("item.ajax.editProductGroup") }}';
        }

        FormData = $('#add_product_group_form').serialize();
        $.ajax({
            url: url,
            method: 'Post',
            data: {
                FormData: FormData,
                products: JSON.stringify({
                    products
                })
            },
            success: function(response) {
                // Show the message div
                console.log(response);
                if (isAuthenticated(response) == false) {
                    return false;
                }
                const messageDiv = document.getElementById('message');
                messageDiv.style.display = 'block';

                // Check if the response was successful
                if (response.success) {
                    // Success scenario
                    messageDiv.classList.remove('error-message');
                    messageDiv.classList.add('success-message');
                    messageDiv.textContent = response.message;

                    // Optionally hide the modal after success
                    setTimeout(function() {
                        $('#itemsCatagoryModal').modal('hide');
                        location.reload();
                    }, 3000); // Adjust delay if needed
                } else {
                    // Failure scenario
                    messageDiv.classList.remove('success-message');
                    messageDiv.classList.add('error-message');
                    messageDiv.textContent = response.message;
                }

                // Hide the message after some time
                setTimeout(function() {
                    $(messageDiv).fadeOut(); // Smooth fade-out effect
                }, 3000)
            },
            error: function(xhr, status, error) {
                console.error(error);
                if (xhr.status === 422) {
                    // Get the errors object from the response
                    const errors = xhr.responseJSON.errors;

                    // Extract the first error message
                    const firstErrorKey = Object.keys(errors)[0];
                    const firstErrorMessage = errors[firstErrorKey][0];

                    document.getElementById('message').style.display = 'block'
                    document.getElementById('message').classList.add('error-message');
                    document.getElementById('message').textContent = firstErrorMessage;

                    setTimeout(function() {
                        $('#message').fadeOut(); // Use fadeOut for a smooth effect
                    }, 3000);
                }
            }
        });
    }

    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#search-product').on('keyup', function() {
            let query = $(this).val();
            const divList = document.querySelector('.parent-container');

            if (query === '') {
                divList.innerHTML = '';
            }

            // Make an AJAX call only if query length > 2
            if (query.length > 2) {
                $.ajax({
                    url: "{{ route('item.ajax.searchProduct') }}", // Laravel route
                    method: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        console.log(response);
                        // $('#results').html(response);
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                        divList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'container'; // Optional: Add a class to the div for styling

                        // Step 2: Create a ul (unordered list)
                        const ul = document.createElement('ul');
                        ul.id = "productList";
                        // Step 3: Loop through the data and create li (list item) for each entry
                        response.forEach(item => {
                            const li = document.createElement('li'); // Create a new li element
                            li.textContent = item.product_name; // Set the text of the li item
                            li.id = item.id;
                            li.className = "editInput";
                            ul.appendChild(li); // Append the li to the ul
                        });

                        // Step 4: Append the ul to the div
                        div.appendChild(ul);

                        // Step 5: Append the div to the parent container in the HTML
                        divList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            divList.innerHTML = '';
                            document.getElementById('search-product').value = '';
                            // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedId = event.target.id; // Get the ID of the clicked <li>
                                console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                                getProductData(selectedId);
                            }
                        });

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                $('#results').empty(); // Clear results if the input is empty
            }
        });

    });

    function getProductData(selectedId) {
        $.ajax({
            url: '{{ route("item.ajax.getProductFromId") }}',
            method: 'Post',
            data: {
                id: selectedId
            },
            success: function(response) {
                console.log(response);
                if (isAuthenticated(response) == false) {
                    return false;
                }
                productGroupTable(response.data, 'productListTable');
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    var totalAmount = 0;
    var GrandCostPrice = 0;
    var GrandPrice = 0;

    function productGroupTable(data, tableId) {
        // Get the table body element
        const tableBody = document.querySelector(`#${tableId} tbody`);

        // Check if data array is empty
        if (data.length === 0) {
            // Create a row to display the "No products found" message
            const noDataRow = document.createElement('tr');
            const noDataCell = document.createElement('td');

            // Span across all columns in the table (adjust the colspan if your table has more columns)
            noDataCell.setAttribute('colspan', 4);
            noDataCell.textContent = 'No products found';
            noDataCell.style.textAlign = 'center'; // Center the message

            // Append the cell to the row and the row to the table body
            noDataRow.appendChild(noDataCell);
            tableBody.appendChild(noDataRow);
        } else {
            // Populate rows as usual if data is not empty
            const emptyErrorRow = document.getElementById('EmptyError');
            if (emptyErrorRow) {
                emptyErrorRow.remove();
            }
            data.forEach(item => {
                const row = document.createElement('tr');
                // calculateCostPrice();
                // Create cells and append them to the row
                const codeCell = document.createElement('td');
                codeCell.textContent = item.product_code;
                row.appendChild(codeCell);

                // Create a hidden input field for the product ID
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.className = 'product_id';
                hiddenInput.name = 'product_ids[]'; // For form submission
                hiddenInput.value = item.id; // Set the product ID
                row.appendChild(hiddenInput);

                const nameCell = document.createElement('td');
                nameCell.innerHTML = item.product_name;
                row.appendChild(nameCell);

                const costCell = document.createElement('td');
                const inputCost = document.createElement('input');
                inputCost.type = 'text'; // Set input type
                inputCost.className = 'cost_price input50';
                inputCost.name = 'cost_price[]'; // Set input name (useful for form submission)
                inputCost.value = item.cost_price; // Set the default value (optional)
                GrandCostPrice = GrandCostPrice + Number(item.cost_price);
                costCell.appendChild(inputCost);
                row.appendChild(costCell);

                const priceCell = document.createElement('td');
                const inputPrice = document.createElement('input');
                inputPrice.type = 'text'; // Set input type
                inputPrice.className = 'product_price input50';
                inputPrice.addEventListener('input', function() {
                    updateAmount(row);
                });
                inputPrice.name = 'product_price[]'; // Set input name (useful for form submission)
                inputPrice.value = item.price; // Set the default value (optional)
                GrandPrice = GrandPrice + Number(item.price);
                priceCell.appendChild(inputPrice);
                row.appendChild(priceCell);

                const qtyCell = document.createElement('td');
                const inputQty = document.createElement('input');
                inputQty.type = 'text'; // Set input type
                inputQty.className = 'qty input50';
                inputQty.addEventListener('input', function() {
                    updateAmount(row);
                });
                inputQty.name = 'qty[]'; // Set input name (useful for form submission)
                inputQty.value = '1'; // Set the default value (optional)
                qtyCell.appendChild(inputQty);
                row.appendChild(qtyCell);

                const amountCell = document.createElement('td');
                amountCell.innerHTML = '$' + parseFloat(item.price).toFixed(2);
                amountCell.className = "price";
                row.appendChild(amountCell);
                totalAmount = totalAmount + Number(item.price);
                const deleteCell = document.createElement('td');
                deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                row.appendChild(deleteCell);

                // Append the row to the table body
                tableBody.appendChild(row);


            });
            $("#costPrice").val(parseFloat(GrandCostPrice).toFixed(2));
            $("#productPrice").val(parseFloat(GrandPrice).toFixed(2));
            var htmlCode = `<tr>
                                <td colspan="4"></td>
                                <td>Total</td><td id="GrandTotalAmount">$` + parseFloat(totalAmount).toFixed(2) + `</td>
                                <td></td>
                          </tr>`;
            $("#containerA").html(htmlCode);
            // console.log("html "+totalAmount) 
        }
    }

    document.querySelector("#productListTable").addEventListener("click", function(e) {
        if (e.target && e.target.classList.contains("deleteRow")) {
            // Remove the parent row of the clicked delete button
            const row = e.target.closest("tr");
            if (row) {
                row.remove();
                const amountCell = row.querySelector(".price");
                const amount = parseFloat(amountCell.textContent.replace(/[^\d.]/g, "")) || 0;
                totalAmount -= amount;
                if (totalAmount === 0) {
                    $("#costPrice").val('0.00');
                    $("#containerA").hide();
                } else {
                    $("#containerA").show();
                }
                document.getElementById("GrandTotalAmount").textContent = "$" + totalAmount.toFixed(2);
                $("#productPrice").val(totalAmount.toFixed(2));
            }
        }
    });

    // function calculateCostPrice() {
    //     const inputs = document.querySelectorAll('.cost_price');
    //     const product_price = document.querySelectorAll('.product_price');

    //     // Add an event listener to each input field
    //     inputs.forEach(input => {
    //         console.log(input);
    //         input.addEventListener('input', function() {
    //             calculateSum(inputs, 'costPrice'); // Pass 'inputs' to the function
    //         });
    //     });

    //     product_price.forEach(input => {
    //         console.log('price'+input);
    //         input.addEventListener('input', function() {
    //             calculateSum(product_price, 'productPrice'); // Pass 'product_price' to the function
    //         });
    //     });
    // }

    // function calculateSum(inputs, appendPlace) {
    //     let total = 0;
    //     inputs.forEach(input => {
    //         total += parseFloat(input.value) || 0; // Add value or 0 if empty
    //     });
    //     // console.log(total);
    //     document.getElementById(appendPlace).value = total; // Display total
    // }

    // Function to update the amount in the row
    function updateAmount(row) {
        // console.log(row)
        // const priceInput = row.querySelector('.price');
        const priceInput = row.querySelector('.product_price');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('td:nth-last-child(2)'); // Assuming the last cell is the amount cell
        const price = parseFloat(priceInput.value) || 0; // Default to 0 if not a valid number
        const qty = parseInt(qtyInput.value) || 1; // Default to 0 if not a valid number
        const amount = price * qty; // Calculate the amount
        amountCell.textContent = '$' + amount.toFixed(2); // Update the amount cell with the calculated amount

        var calculation = 0;
        $('.price').each(function() {
            const priceText = $(this).text();
            const numericValue = parseFloat(priceText.replace(/[^\d.]/g, ''));
            // console.log(typeof(numericValue));
            calculation = calculation + numericValue;
        });
        totalAmount = calculation;
        document.getElementById('GrandTotalAmount').innerHTML = '$' + totalAmount.toFixed(2);
        $("#productPrice").val(totalAmount.toFixed(2));
    }

    document.querySelector("#listAllProduct").addEventListener("click", function(e) {
        const productIds = document.getElementById('selectedProductIds').value;
        const productIdsArray = JSON.parse(productIds || '[]');
        productIdsArray.forEach(id => {
            getProductData(id); // Call the function for each ID
        });
    });
</script>

<script>
    $('.fetch_data').on('click', function() {

        var id = $(this).data('id');
        var name = $(this).data('name');
        var description = $(this).data('description');
        var code = $(this).data('code');
        var cost = $(this).data('cost');
        var price = $(this).data('price');
        var status = $(this).data('status');

        $("#id").val(id);
        $("#name").val(name);
        $("#description").val(description);
        $("#code").val(code);
        $("#status").val(status);
        $("#costPrice").val(cost);
        $("#productPrice").val(price);

        $.ajax({
            url: '{{ url("item/ProductGroupProductsList") }}',
            method: 'Post',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            },
            success: function(response) {
                console.log(response);
                if (isAuthenticated(response) == false) {
                    return false;
                }
                const tableBody = document.querySelector(`#productListTable tbody`);
                if (response.data.length === 0) {
                    const noDataRow = document.createElement('tr');
                    noDataRow.id = 'EmptyError'
                    const noDataCell = document.createElement('td');
                    noDataCell.setAttribute('colspan', 7);
                    noDataCell.innerHTML = '<span class="text-center" style="color:#dc3545;">Sorry, there are no items available</span>';
                    noDataCell.style.textAlign = 'center';
                    noDataRow.appendChild(noDataCell);
                    tableBody.appendChild(noDataRow);
                } else {
                    tableBody.innerHTML = '';
                    totalAmount = 0;
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
                        GrandCostPrice = GrandCostPrice + Number(item.cost_price);
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
                        GrandPrice = GrandPrice + Number(item.price);
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
                        amountCell.innerHTML = '$' + item.quantity * parseFloat(item.price).toFixed(2);
                        amountCell.className = "price";
                        row.appendChild(amountCell);
                        totalAmount = totalAmount + Number(item.price) * item.quantity;
                        const deleteCell = document.createElement('td');
                        deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                        row.appendChild(deleteCell);
                        tableBody.appendChild(row);

                    });
                    $("#costPrice").val(parseFloat(cost).toFixed(2));
                    $("#productPrice").val(parseFloat(price).toFixed(2));
                    var htmlCode = `<tr>
                                            <td colspan="4"></td>
                                            <td>Total</td><td id="GrandTotalAmount">$` + parseFloat(totalAmount).toFixed(2) + `</td>
                                            <td></td>
                                    </tr>`;
                    $("#containerA").html(htmlCode);

                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
        $("#itemsCatagoryModal").modal('show');
    });

    function open_productgroupmodal() {
        $('#add_product_group_form')[0].reset();
        const tableBody = document.querySelector(`#productListTable tbody`);
        const tableFoot = document.querySelector(`#productListTable tfoot`);
        tableBody.innerHTML = '';
        tableFoot.innerHTML = '';
        $("#itemsCatagoryModal").modal('show');
    }
</script>

<script>
    $("#deleteSelectedRows").on('click', function() {
        let ids = [];

        $('.delete_checkbox:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            alert("Please check the checkbox for delete");
        } else {
            if (confirm("Are you sure to delete?")) {
                // console.log(ids);
                var token = '<?php echo csrf_token(); ?>'
                var model = 'ProductGroup';
                $.ajax({
                    type: "POST",
                    url: "{{url('/bulk_delete')}}",
                    data: {
                        ids: ids,
                        model: model,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);
                        if (isAuthenticated(data) == false) {
                            return false;
                        }
                        if (data) {
                            location.reload();
                        } else {
                            alert("Something went wrong");
                        }
                        // return false;
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                    }
                });
            }
        }

    });
    $('.delete_checkbox').on('click', function() {
        if ($('.delete_checkbox:checked').length === $('.delete_checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });
</script>

@include('frontEnd.salesAndFinance.item.common.addcataloguemodal')

@endsection