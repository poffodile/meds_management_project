@include('frontEnd.salesAndFinance.jobs.layout.header')
<style>
    .addError {
        border: 1px solid red;
    }
</style>
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Tax Rates</h3>
                </div>
            </div>
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="pageTitleBtn">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-lg-8 col-xl-8 px-3">
                <div class="jobsection">
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#customerPop" class="profileDrop">Add</a>
                    <a href="{{url('/tax_rate?mode=Active')}}" class="profileDrop">Active</a>
                    <a href="{{url('/tax_rate?mode=Inactive')}}" class="profileDrop">Inactive</a>
                </div>
            </div>
            <div class="col-md-4 col-lg-4 col-xl-4 ">
            </div>
        </div>
        <div class="alert alert-success text-center" id="msg" style="display:none;height:50px">
            <p id="status_meesage"></p>
        </div>
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!">Show Search Filter</a>
                        </div>
                    </div>
                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="javascript:void(0)" id="deleteSelectedRows" class="profileDrop">Delete</a>
                                    <!-- <a href="#" class="profileDrop">Mark As completed</a> -->
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <table id="exampleOne" class="display tablechange" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center" style=" width:30px;"><input type="checkbox" id="selectAll"> <label for="selectAll"> All Select</label></th>
                                <th>#</th>
                                <th>Tax Rate Name</th>
                                <th>Tax Rate</th>
                                <th>Tax Code</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody id="result">
                            <?php foreach ($tax_rate as $key => $val) { ?>
                                <tr>
                                    <td><input type="checkbox" id="" class="delete_checkbox" @php if($val->tax_rate == 20 ){ echo "disabled"; } @endphp value="{{$val->id}}"></td>
                                    <td>{{++$key}}</td>
                                    <td>{{$val->name}}</td>
                                    <td>{{$val->tax_rate}}</td>
                                    <td>{{$val->tax_code}}</td>
                                    <td>{{$val->exp_date}}</td>
                                    <td>
                                        <?php if ($val->status == 1) { ?>
                                            <span class="grencheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                        <?php } else { ?>
                                            <span class="grayCheck" onclick="status_change({{$val->id}},{{$val->status}})"><i class="fa-solid fa-circle-check"></i></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown"> Action </a>
                                                <div class="dropdown-menu fade-up m-0">
                                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#customerPop" class="dropdown-item modal_dataFetch" data-id="{{ $val->id }}" data-name="{{ $val->name }}" data-tax_rate="{{$val->tax_rate}}" data-tax_code="{{$val->tax_code}}" data-exp_date="{{$val->exp_date}}" data-status="{{ $val->status }}">Edit Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div> <!-- End off main Table -->
                <!--  Modal start here -->
                <div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content add_Customer">
                            <div class="modal-header">
                                <h5 class="modal-title" id="customerModalLabel">Tax Rate - Add</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="text-center" id="message_save"></div>
                                    <div class="col-md-12 col-lg-12 col-xl-12">
                                        <div class="formDtail">
                                            <form id="form_data" class="customerForm pt-0">
                                                <input type="hidden" name="id" id="id">
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Name<span class="radStar ">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="name" name="name" value="">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Tax Rate<span class="radStar ">*</span></label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="tax_rate" name="tax_rate" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputProject" class="col-sm-3 col-form-label">Status</label>
                                                    <div class="col-sm-9">
                                                        <select class="form-control editInput selectOptions" id="statusModal" name="status">
                                                            <option value="1">Active</option>
                                                            <option value="0">Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">External Tax Code</label>
                                                    <div class="col-sm-9">
                                                        <input type="text" class="form-control editInput" id="tax_code" name="tax_code" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                                                    </div>
                                                </div>
                                                <div class="mb-2 row">
                                                    <label for="inputName" class="col-sm-3 col-form-label">Expiry Date</label>
                                                    <div class="col-sm-9">
                                                        <input type="date" class="form-control editInput" id="exp_date" name="exp_date" value="">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div> <!-- End row -->
                            </div>
                            <div class="modal-footer customer_Form_Popup">
                                <button type="button" class="profileDrop" id="save_data">Save</button>
                                <!-- <button type="button" class="profileDrop" id="save_dataClose">Save & Close</button> -->
                                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end here -->
            </div>
    </div>
    </div>
    <script src="{{url('public/backEnd/js/multiselect.js')}}"></script>
    <script>
        $('#save_dataClose').on('click', function() {
            var name = $("#name").val();
            if (name == '') {
                $("#name").addClass('addError');
                return false;
            } else {
                saveData();
                $("#customerPop").modal('hide');
            }
        });

        $('#save_data').on('click', function() {
            saveData();
        });

        function saveData() {
            var token = '<?php echo csrf_token(); ?>';
            var name = $("#name").val();
            var tax_rate = $("#tax_rate").val();
            var tax_code = $("#tax_code").val();
            var exp_date = $("#exp_date").val();
            var status = $.trim($('#statusModal option:selected').val());
            var home_id = '<?php echo $home_id; ?>';
            var id = $("#id").val();
            var message;

            if (id == '') {
                message = "Added Successfully Done";
            } else {
                message = "Edited Successfully Done";
            }
            if (name == '') {
                $("#name").addClass('addError');
                return false;
            } else if (tax_rate == '') {
                $("#name").removeClass('addError');
                $("#tax_rate").addClass('addError');
                return false;
            } else {
                $.ajax({
                    type: "POST",
                    url: '{{ url("/save_tax_rate") }}',
                    data: {
                        id: id,
                        home_id: home_id,
                        name: name,
                        tax_rate: tax_rate,
                        tax_code: tax_code,
                        exp_date: exp_date,
                        status: status,
                        _token: token
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.vali_error) {
                            $('#message_save').addClass('error-message').text(response.vali_error).show();
                            setTimeout(function() {
                                $('#error-message').text('').fadeOut();
                            }, 3000);
                            return false;
                        } else if (response.success === true) {
                            $('#message_save').addClass('success-message').text(response.message).show();
                            setTimeout(function() {
                                $('#message_save').removeClass('success-message').text('').hide();
                                location.reload();
                            }, 3000);
                        }

                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + error);
                    }

                });
            }
        }
        $('.modal_dataFetch').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var tax_rate = $(this).data('tax_rate');
            var tax_code = $(this).data('tax_code');
            var exp_date = $(this).data('exp_date');
            var status = $(this).data('status');

            $('#id').val(id);
            $('#name').val(name);
            $("#tax_rate").val(tax_rate);
            $("#tax_code").val(tax_code);
            $("#exp_date").val(exp_date);
            $('#statusModal').val(status);
        });

        function status_change(id, status) {
            var token = '<?php echo csrf_token(); ?>'
            var model = "Construction_tax_rate";
            $.ajax({
                type: "POST",
                url: "{{url('/status_change')}}",
                data: {
                    id: id,
                    status: status,
                    model: model,
                    _token: token
                },
                success: function(data) {
                    console.log(data);
                    if ($.trim(data) == 1) {
                        $("#status_meesage").text("status Changed Successfully Done");
                        $("#msg").show();
                        setTimeout(function() {
                            location.reload();
                        }, 3000);


                    }

                }
            });
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
                    var model = 'Construction_tax_rate';
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
</section>
@include('frontEnd.salesAndFinance.jobs.layout.footer')