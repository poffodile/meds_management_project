@include('frontEnd.salesAndFinance.jobs.layout.header')
<section class="main_section_page px-3 pt-0">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 col-lg-4 col-xl-4 ">
            <div class="pageTitle">
                <h3>Products</h3>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
            <div class="jobsection">
                <a href="#" class="profileDrop" onclick="itemsAddProductModal(1)">Add</a>
                <a href="{{url('/item/products/active')}}" class="profileDrop">Active ({{count($product)}})</a>
                <a href="{{url('/item/products/inactive')}}" class="profileDrop">Inactive ({{count($product_inactive)}})</a>
                {{-- <a href="#" class="profileDrop" id="impExpClickbtnPopup">Import/Export</a> --}}
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

    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="maimTable mt-2">
                <div class="printExpt">
                    <div class="prntExpbtn">
                        <a href="#!">Print</a>
                        <a href="#!">Export</a>
                    </div>
                    <div class="searchFilter">
                        <a href="#!">Show Search Filter</a>
                    </div>

                </div>
                <div class="markendDelete">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="jobsection d-flex">
                                <input type="button" class="btn profileDrop" id="getCheckedValues" value="Delete">
                                <span class="alert text-danger text-center deletemsg"></span>
                                {{-- <div class="pageTitleBtn p-0">
                                    <div class="nav-item dropdown">
                                        <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false"> Bulk Action </a>
                                        <div class="dropdown-menu fade-up m-0">
                                            <a href="#" class="dropdown-item col-form-label">Set Accont Codes</a>
                                            <a href="#" class="dropdown-item col-form-label">Set Tax Rats</a>
                                            <a href="#" class="dropdown-item col-form-label">Fix duplicate product codes</a>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        <div class="col-md-5">
                            {{-- <div class="pageTitleBtn p-0">
                                <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center" style=" width:60px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> </label></th>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>Type</th>
                            <th>Product Category</th>
                            <th>Description</th>
                            <th>Customer</th>
                            <th>Cost Price</th>
                            <th>Markup</th>
                            <th>Price</th>
                            <th>Tax Rate</th>
                            <th>Created On</th>
                            <th>Last Updated On</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($product_list_array as $product_value)
                            <tr>
                                <td class="text-center"><input type="checkbox" class="checkproductcategory" name="checkproductcategory{{ $i }}" id="checkproductcategory{{ $i }}" value="{{ $product_value['id'] }}" ></td>
                                <td>{{$i}}</td>
                                <td>{{ $product_value['product_name'] }}</td>
                                <td>{{ $product_value['product_code'] }}</td>
                                <td>{{ $product_value['product_type_name'] }}</td>
                                <td>{{ $product_value['cat_name'] }}</td>
                                <td>{{ $product_value['description'] }}</td>
                                <td>{{ $product_value['customer_name'] }}</td>
                                <td>{{ $product_value['cost_price'] }} </td>
                                <td>{{ $product_value['margin'] }}</td>
                                <td>{{ $product_value['price'] }} </td>
                                <td>{{ $product_value['tax_rate_value'] }}</td>
                                <td>{{ $product_value['created_at'] }}</td>
                                <td>{{ $product_value['updated_at'] }}</td>

                                <td>
                                    @if ($product_value['status'] == 1)
                                        <span class="grencheck"
                                            onclick="changeproductstatus({{ $product_value['id'] }},0)"><i
                                                class="fa-solid fa-circle-check"></i></span>
                                    @else
                                        <span class="graycheck"
                                            onclick="changeproductstatus({{ $product_value['id'] }},1)"><i
                                                class="fa-solid fa-circle-check"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <div class="pageTitleBtn p-0">
                                        <div class="nav-item dropdown">
                                            <a href="#" class="nav-link dropdown-toggle profileDrop"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Action </a>
                                            <div class="dropdown-menu fade-up m-0">
                                                <a href="#" class="dropdown-item col-form-label" onclick="itemsAddProductModal(3,{{ $product_value['id'] }})">Edit
                                                    Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @php
                                $i++;
                            @endphp
                        @endforeach

                    </tbody>
                </table>

                


                
            </div> <!-- End off main Table -->
        </div>
    </di>
</div>
</section>




<script>
    function changeproductstatus(id, status) {
        var token = "<?= csrf_token() ?>";
        if (confirm("Are you sure want to change the status?")) {
            $.ajax({
                type: 'POST',
                url: '{{ route('item.changeProductStatus') }}',
                data: {
                    id: id,
                    status: status,
                    _token: token
                },
                success: function(data) {
                    location.reload();
                }

            });
        }
    }
</script>
<script>
    document.getElementById('getCheckedValues').addEventListener('click', function() {
        const checkedValues = Array.from(document.querySelectorAll('.checkproductcategory:checked'))
            .map(checkbox => checkbox.value);
        //alert("Checked values: " + checkedValues.join(", "));
        var id = checkedValues.join(", ");
        var token = "<?= csrf_token() ?>";
        if(id==""){
            $('.deletemsg').text("Please select product");
            return false;
        }else{
            if (confirm("Are you sure want to delete this?")) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('item.deleteProduct') }}',
                    data: {
                        id: id,
                        _token: token
                    },
                    success: function(data) {
                        console.log(data);

                        location.reload();
                    }

                });
            }
        }
    });
</script>
<script>
    const checkAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.checkproductcategory');

    checkAll.addEventListener('change', function() {
        $('.deletemsg').text("");
        checkboxes.forEach(checkbox => {
            if (!checkbox.disabled) { // Only set checked state for enabled checkboxes
                checkbox.checked = this.checked;
            }
        });
    });

    checkboxes.forEach(checkbox => {       
        checkbox.addEventListener('change', function() {
            $('.deletemsg').text("");
            if (!this.checked) {
                checkAll.checked = false; // Uncheck "Check All" if any checkbox is unchecked
            } else {
                // If all enabled checkboxes are checked, check the "Check All" checkbox
                const allChecked = Array.from(checkboxes).every(cb => cb.checked || cb.disabled);
                checkAll.checked = allChecked;
            }
        });
    });
</script>

@include('frontEnd.salesAndFinance.item.common.addproductmodal')

@include('frontEnd.salesAndFinance.jobs.layout.footer')