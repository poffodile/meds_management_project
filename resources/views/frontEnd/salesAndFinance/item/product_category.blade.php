@include('frontEnd.salesAndFinance.jobs.layout.header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Product Category</h3>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="#" class="profileDrop" onclick="additemsCatagoryModal(1)">Add</a>
                </div>
            </div>

        </div>
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable mt-2">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            {{-- <a href="#!">Export</a> --}}
                        </div>
                        <div class="searchFilter">
                            {{-- <a href="#!">Show Search Filter</a> --}}
                        </div>
                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <input type="button" class="btn profileDrop" id="getCheckedValues" value="Delete">
                                    <span class="alert text-danger text-center deletemsg"></span>
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
                                <th class="text-center" style=" width:60px;"><input type="checkbox" id="selectAll">
                                    <label for="selectAll"> </label></th>
                                <th>#</th>
                                <th>Product Category</th>
                                <th>Level</th>
                                <th>No. Of Products</th>
                                <th>No. Of Children</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($product_categories_list as $category_value)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="checkproductcategory"
                                            name="checkproductcategory{{ $i }}"
                                            id="checkproductcategory{{ $i }}"
                                            value="{{ $category_value['id'] }}"
                                            @if ($category_value['number_of_children'] != 0) disabled @endif></td>
                                    <td>{{ $i }}</td>
                                    <td>{{ $category_value['product_name'] }}</td>
                                    <td>{{ $category_value['level'] }}</td>
                                    <td>{{ $category_value['number_of_products'] }}</td>
                                    <td>{{ $category_value['number_of_children'] }}</td>
                                    <td>
                                        @if ($category_value['status'] == 1)
                                            <span class="grencheck"
                                                onclick="changestatus({{ $category_value['id'] }},0)"><i
                                                    class="fa-solid fa-circle-check"></i></span>
                                        @else
                                            <span class="graycheck"
                                                onclick="changestatus({{ $category_value['id'] }},1)"><i
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
                                                    <a href="#" class="dropdown-item col-form-label" onclick="edititemsCatagoryModal('{{ $category_value['id'] }}','{{ $category_value['product_name'] }}','{{ $category_value['cat_id'] }}','{{ $category_value['status'] }}')">Edit
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
    function changestatus(id, status) {
        var token = "<?= csrf_token() ?>";
        if (confirm("Are you sure want to change the status?")) {
            $.ajax({
                type: 'POST',
                url: '{{ route('item.changeProductCategoryStatus') }}',
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
            $('.deletemsg').text("Please select product category");
            return false;
        }else{
            if (confirm("Are you sure want to delete this?")) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('item.delete_product_category') }}',
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

<script>
    function edititemsCatagoryModal(id,name,catid,status,th){
        // alert(id);
        // alert(name);
        // alert(catid);
        // alert(status);
        //itemsCatagoryModal
        $('#category_name').val(name);
        $('#parentcategory').val(catid);
        $('#product_category_status').val(status);
        $('#productCategoryID').val(id);
        $('#productCategorytype').val(th);
        $('#itemsCatagoryModal').modal('show');
    }
</script>
<script>
    function additemsCatagoryModal(th){
        //alert();
        $('#category_name').val('');
        $('#parentcategory').val('');
        $('#product_category_status').val(1);
        $('#productCategoryID').val('');
        $('#productCategorytype').val(th);
        $('#itemsCatagoryModal').modal('show');
    }
</script>

@include('frontEnd.salesAndFinance.item.common.productcategoryaddmodal')
@include('frontEnd.salesAndFinance.jobs.layout.footer')
