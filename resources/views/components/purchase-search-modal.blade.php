<div class="modal fade" id="{{ $searchModalId }}" tabindex="-1" aria-labelledby="invoiceCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceCustomerModalLabel">Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form action="">
                            <div
                                class="d-md-flex gap-3 align-items-center">
                                <label for="">Search In: </label>
                                <select name="" id="" class="form-control w-25">
                                    <option value="1">Quote</option>
                                    <option value="2">Job</option>
                                    <option value="3">Invoice</option>
                                </select>
                                <select name="" id="" class="form-control w-25">
                                    <option value="">Alex Hill</option>
                                    <option value="">Alex Hill</option>
                                    <option value="">Alex Hill</option>
                                </select>
                                <input type="text" placeholder="Keywords" class="form-control w-25">
                                <button type="button" class="profileDrop">Search</button>
                            </div>
                        </form>
                    </div>
                    <!-- <div class="col-sm-12 mt-3">
                        <p>1 - 1 of 1 records</p>
                    </div> -->
                    <div class="col-md-12">
                        <div class="productDetailTable table-responsive mt-2 input_style">
                            <table class="table">
                                <thead class="table-light">
                                    <tr style="white-space: nowrap;">
                                        <th>#</th>
                                        <th>Job Ref</th>
                                        <th>Type</th>
                                        <th>Customer</th>
                                        <th>Site</th>
                                        <th>Description</th>
                                        <th>Complete By</th>
                                        <th>Completed On</th>
                                        <th>Status</th>
                                        <th>Product(s)</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-warning">
                                        <td>1</td>
                                        <td>JOB-0031</td>
                                        <td>Service</td>
                                        <td>Alex Hill</td>
                                        <td>uk</td>
                                        <td>Lorem ipsum, dolor sit amet
                                            consectetur adipisicing
                                            elit. Cum aperiam non nisi
                                            maxime dolor recusandae
                                            assumenda, consectetur id
                                            error molestias! Numquam
                                            ullam aut porro non delectus
                                            cum rerum obcaecati
                                            laboriosam eum ut beatae,
                                            voluptatibus magni iure,
                                            odio dolore reprehenderit
                                            omnis dolor perferendis enim
                                            aliquam ducimus
                                            necessitatibus eos
                                            aspernatur? Quis, soluta!
                                        </td>
                                        <td>28/02/2025</td>
                                        <td></td>
                                        <td>Appointed</td>
                                        <td><a href="">View</a></td>
                                        <td>
                                            <button type="button" class="profileDrop">Add</button>
                                        </td>
                                        <td>
                                            <button type="button" class="profileDrop" style="white-space: nowrap;">Add Items</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="productDetailTable table-responsive  input_style">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="col-7">Description
                                        </th>
                                        <th>Cost Price</th>
                                        <th class="col-1">Quantity</th>
                                        <th class="col-1">Include</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Product Test</td>
                                        <td></td>
                                        <td>$100.00</td>
                                        <td><input type="text" class="form-control" value="1"></td>
                                        <td><input type="checkbox"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end">
                                            <button type="button" class="profileDrop">Confirm Selection</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" data-bs-dismiss="modal" aria-label="Close">Close</button>
            </div>
        </div>
    </div>
</div>