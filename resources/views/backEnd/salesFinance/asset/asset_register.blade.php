@extends('backEnd.layouts.master')
@section('title','Fixed Asset Register')
@section('content')

<style>
.bg_color{
    background: #b0b5b9;
}
</style>
<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">Fixed Asset Register</header>
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <a href="{{url('admin/sales-finance/assets/asset-register?cat=')}}" class="btn @if(isset($selected_cat_id) && $selected_cat_id ==0)bg_color @else btn-primary @endif">Summary</a>
                            <?php foreach ($AssetCategoryList as $cat) { ?>
                            <a href="{{url('admin/sales-finance/assets/asset-register?cat=')}}{{base64_encode($cat->id)}}" class="btn @if(isset($selected_cat_id) && $selected_cat_id ==$cat->id)bg_color @else btn-primary @endif">{{$cat->name}}</a>
                            <?php } ?>
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="javascript:void(0)" id="depreciaion_type" class="btn btn-primary" data-toggle="modal" data-target="#Fixed_Asset_Register"> Add New <i class="fa fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="space15"></div>
                            @if($selected_cat_id == 0)
                            <div class="fixedAssetSummary table-responsive">
                                <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">S. N.</th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                        <th scope="col"></th>
                                        <?php foreach ($AssetCategoryList as $cat) { ?>
                                            <th scope="col">{{$cat->name}} </th>
                                        <?php }?>
                                    </tr>
                                
                                </thead>
                                <tbody>
                                    
                                        <tr>
                                            <td colspan="3"></td>
                                            <th scope="col">Total</th>
                                            <td colspan="{{ count($AssetCategoryList) }}"></td>
                                        </tr>

                                    
                                        <tr>
                                            <td></td>
                                            <th scope="col">Cost</th>
                                            <td colspan="{{ count($AssetCategoryList) + 2 }}"></td>
                                        </tr>
                                        @php
                                            $costBfwd = $costAdd = $costDisposal = $costCfwd = [];
                                            foreach ($AssetCategoryList as $cat) {
                                                $items = $list->where('asset_type', $cat->id);
                                                $costBfwd[$cat->id] = $items->sum('cost_bfwd');
                                                $costAdd[$cat->id] = $items->sum('cost_addition');
                                                $costDisposal[$cat->id] = $items->sum('cost_disposal');
                                                $costCfwd[$cat->id] = $costBfwd[$cat->id] + $costAdd[$cat->id] - $costDisposal[$cat->id];
                                            }
                                        @endphp

                                        
                                        <tr>
                                            <td>1.</td><td></td><td>Bfwd</td>
                                            <td>{{ number_format(array_sum($costBfwd),2 ,'.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>{{ number_format($costBfwd[$cat->id],2 ,'.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>2.</td><td></td><td>Additions</td>
                                            <td>{{ number_format(array_sum($costAdd),2 ,'.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>{{ number_format($costAdd[$cat->id],2 ,'.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>3.</td><td></td><td>Disposals</td>
                                            <td>-{{ number_format(array_sum($costDisposal), 2, '.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>-{{ number_format($costDisposal[$cat->id], 2, '.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>4.</td><td></td><td>Cfwd</td>
                                            <th>{{ number_format(array_sum($costCfwd), 2, '.', '') }}</th>
                                            @foreach ($AssetCategoryList as $cat)
                                                <th>{{ number_format($costCfwd[$cat->id], 2, '.', '') }}</th>
                                            @endforeach
                                        </tr>

                                    
                                        <tr>
                                            <td></td>
                                            <th scope="col">Depreciation</th>
                                            <td colspan="{{ count($AssetCategoryList) + 2 }}"></td>
                                        </tr>
                                        @php
                                            $depBfwd = $charge = $elimDisp = $depCfwd = $nbvCfwd = $nbvBfwd = [];
                                            foreach ($AssetCategoryList as $cat) {
                                                $items = $list->where('asset_type', $cat->id);
                                                $depBfwd[$cat->id] = $items->sum('depreciation_bfwd');
                                                $charge[$cat->id] = $items->sum('charge');
                                                $elimDisp[$cat->id] = $items->sum('depreciation');
                                                $depCfwd[$cat->id] = $depBfwd[$cat->id] + $charge[$cat->id] - $elimDisp[$cat->id];

                                                $nbvCfwd[$cat->id] = $costCfwd[$cat->id] - $depCfwd[$cat->id];
                                                $nbvBfwd[$cat->id] = $costBfwd[$cat->id] - $depBfwd[$cat->id];
                                            }
                                        @endphp

                                    
                                        <tr>
                                            <td>1.</td><td></td><td>Bfwd</td>
                                            <td>{{ number_format(array_sum($depBfwd), 2, '.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>{{ number_format($depBfwd[$cat->id], 2, '.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>2.</td><td></td><td>Charge for year</td>
                                            <td>{{ number_format(array_sum($charge), 2, '.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>{{ number_format($charge[$cat->id], 2, '.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>3.</td><td></td><td>Eliminated on disposals</td>
                                            <td>-{{ number_format(array_sum($elimDisp), 2, '.', '') }}</td>
                                            @foreach ($AssetCategoryList as $cat)
                                                <td>-{{ number_format($elimDisp[$cat->id], 2, '.', '') }}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>4.</td><td></td><td>Cfwd</td>
                                            <th>{{ number_format(array_sum($depCfwd), 2, '.', '') }}</th>
                                            @foreach ($AssetCategoryList as $cat)
                                                <th>{{ number_format($depCfwd[$cat->id], 2, '.', '') }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <td>5.</td>
                                            <td></td>
                                            <th>NBV Cfwd</th>
                                            <th>{{ number_format(array_sum($costCfwd) + array_sum($depCfwd), 2, '.', '') }}</th>
                                            @foreach ($AssetCategoryList as $cat)
                                                <th>{{ number_format($costCfwd[$cat->id] + $depCfwd[$cat->id], 2, '.', '') }}</th>
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td>6.</td>
                                            <td></td>
                                            <th>NBV Bfwd</th>
                                            <th>{{ number_format(array_sum($costBfwd) + array_sum($depBfwd), 2, '.', '') }}</th>
                                            @foreach ($AssetCategoryList as $cat)
                                                <th>{{ number_format($costBfwd[$cat->id] + $depBfwd[$cat->id], 2, '.', '') }}</th>
                                            @endforeach
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                            @else
                            <div class="maimtable productDetailTable mb-4  table-responsive">
                                <table class="table border-top border-bottom tablechange" id="containerB">
                                <thead>
                                    <tr>
                                    <th>#</th>
                                    <th class="border-end">Asset Name</th>
                                    <th class="border-end">Date</th>
                                    <th colspan="4" class="border-end text-center">Cost</th>
                                    <th colspan="4" class="border-end text-center">Depreciation</th>
                                    <th colspan="2" class="border-end text-center">N.B.V</th>
                                    <th colspan="2">Actions</th>
                                    </tr>
                                    <tr>
                                    <th></th>
                                    <th class="border-end"></th>
                                    <th class="border-end"></th>
                                    <th>B/fwd</th>
                                    <th>Additions</th>
                                    <th>Disposals</th>
                                    <th class="border-end">C/fwd</th>
                                    <th>B/fwd</th>
                                    <th>Disps</th>
                                    <th>Charge</th>
                                    <th class="border-end">C/fwd</th>
                                    <th>B/fwd</th>
                                    <th class="text-start border-end">C/fwd</th>
                                    <th></th>
                                    </tr>
                                </thead>
                                <tbody id="assetRegisterList">
                                    <?php
                                    $cost_bfwd = 0;
                                    $cost_disposal = 0;
                                    $cost_addition = 0;
                                    $cost_fwd = 0;
                                    $depreciation_bfwd = 0;
                                    $depreciation = 0;
                                    $charge = 0;
                                    $depreciation_cfwd = 0;
                                    $nbv_bfwd = 0;
                                    $nbv_cfwd = 0;
                                    foreach ($list as $key => $val) {
                                    $cost_bfwd = $cost_bfwd + $val->cost_bfwd;
                                    $cost_disposal = $cost_disposal + $val->cost_disposal;
                                    $cost_addition = $cost_addition + $val->cost_addition;
                                    $cost_fwd = $cost_fwd + $val->cost_fwd;
                                    $depreciation_bfwd = $depreciation_bfwd + $val->depreciation_bfwd;
                                    $depreciation = $depreciation + $val->depreciation;
                                    $charge = $charge + $val->charge;
                                    $depreciation_cfwd = $depreciation_cfwd + $val->depreciation_cfwd;
                                    $nbv_bfwd = $nbv_bfwd + $val->nbv_bfwd;
                                    $nbv_cfwd = $nbv_cfwd + $val->nbv_cfwd;
                                    ?>
                                    <tr>
                                        <td>{{++$key}}</td>
                                        <td class="border-end">{{$val->asset_name}}</td>
                                        <td class="border-end">{{ date('d/m/Y', strtotime($val->date)) }}</td>
                                        <td>{{ $val->cost_bfwd ? '£' . $val->cost_bfwd : '' }}</td>
                                        <td>{{ $val->cost_disposal ? '£' . $val->cost_disposal : ''}}</td>
                                        <td>{{ $val->cost_addition ? '£' . $val->cost_addition : ''}}</td>
                                        <td class="border-end">{{ $val->cost_fwd ? '£' . $val->cost_fwd : ''}}</td>
                                        <td>{{ $val->depreciation_bfwd  ? '£' . $val->depreciation_bfwd : ''}}</td>
                                        <td>{{ $val->depreciation  ? '£' . $val->depreciation : ''}}</td>
                                        <td>{{ $val->charge  ? '£' . $val->charge : ''}}</td>
                                        <td class="border-end">{{ $val->depreciation_cfwd  ? '£' . $val->depreciation_cfwd : ''}}</td>
                                        <td>{{ $val->nbv_bfwd  ? '£' . $val->nbv_bfwd : ''}}</td>
                                        <td class="border-end">{{ $val->nbv_cfwd  ? '£' . $val->nbv_cfwd : ''}}</td>
                                        <td> <a href="{{url('admin/sales-finance/assets/asset-register-edit?key=' . base64_encode($val->id))}}" class="openModalBtn"><i class="fa fa-pencil" aria-hidden="true"></i></a> |
                                        <a href="#!" class="register_delete" data-id="{{$val->id}}"><i class="fa fa-trash text-danger" aria-hidden="true"></i></a>
                                        </td>
                                        <!-- <td>
                                        <div class="d-flex justify-content-end">
                                            <div class="nav-item dropdown">
                                            <a href="#!" class="nav-link dropdown-toggle btn btn-warning" data-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </a>
                                            <div class="dropdown-menu fade-up m-0" style="z-index:9999">
                                                <a href="{{url('sales-finance/assets/asset-register-edit?key=' . base64_encode($val->id))}}" class="dropdown-item">Edit</a>
                                                <hr class="dropdown-divider">
                                                <a href="javascript:void(0)" data-id="{{$val->id}}" class="dropdown-item register_delete">Delete</a>
                                            </div>
                                            </div>
                                        </div>
                                        </td> -->
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr id="footer_table">
                                    <th></th>
                                    <th class="border-end"></th>
                                    <th class="border-end">Total:</th>
                                    <th id="tablecost_bfwd">£{{number_format($cost_bfwd, 2)}}</th>
                                    <th id="tablecost_disposal">£{{number_format($cost_disposal,2)}}</th>
                                    <th id="tablecost_addition">£{{number_format($cost_addition,2)}}</th>
                                    <th id="tablecost_fwd" class="border-end">£{{number_format($cost_fwd,2)}}</th>
                                    <th id="tabledepreciation_bfwd">£{{number_format($depreciation_bfwd,2)}}</th>
                                    <th id="tabledepreciation">£{{number_format($depreciation,2)}}</th>
                                    <th id="tablecharge">£{{number_format($charge,2)}}</th>
                                    <th id="tabledepreciation_cfwd" class="border-end">£{{number_format($depreciation_cfwd,2)}}</th>
                                    <th id="tablenbv_bfwd">£{{number_format($nbv_bfwd,2)}}</th>
                                    <th id="tablenbv_cfwd" class="text-start border-end">£{{number_format($nbv_cfwd,2)}}</th>
                                    <th></th>
                                    </tr>
                                </tfoot>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
<!-- Modal start here -->
 <div class="modal fade popupcloseBtn in" id="Fixed_Asset_Register" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header terques-bg">
                <h5 class="modal-title pupTitle" id="Fixed_Asset_RegisterLabel">Add Fixed Asset Register</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body pdbotm">
                <form id="assetRegisterFormData" class="customerForm">
                    <input type="hidden" id="id" name="id" value="">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_error popup_alrt_msg" style="display: none;">
                                <div class="popup_notification-box">
                                    <div class="alert alert-danger alert-dismissible m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">&times;</span></button>
                                        <strong>Success!</strong> <span class="popup_error_txt">Some error occured, Please try again after sometime.</span>.
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 popup_success popup_alrt_msg" style="display: none;">
                                <div class="popup_notification-box">
                                    <div class="alert alert-success alert m-0" role="alert">
                                        <button type="button" class="close close-msg-btn"><span aria-hidden="true">×</span></button>
                                        <strong>Success!</strong> <span class="popup_success_txt">Data is made editable</span>.
                                    </div>
                                </div>
                            </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <label class="form_heading">Asset Details</label>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Asset Name <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="asset_name" name="asset_name" value="" max="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Date <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <input type="date" class="form-control" id="date" name="date" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">Asset Type <span class="radStar">*</span></label>
                                <div class="col-sm-8">
                                    <select name="asset_type" id="asset_type" class="form-control editInput">
                                        <?php foreach ($AssetCategoryList as $cat) { ?>
                                            <option value="{{$cat->id}}">{{$cat->name}}</option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div> 
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <label class="form_heading">Cost</label>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">B/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_bfwd" name="cost_bfwd" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Additions</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_addition" name="cost_addition" value="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Disposals</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_disposal" name="cost_disposal" value="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">C/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="cost_fwd" name="cost_fwd" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <label class="form_heading">Depreciation</label>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Type of Depreciation</label>
                                <div class="col-sm-10">
                                    <select name="depreciation_type" id="depreciation_type" class="form-control editInput" onchange="calculate()">
                                    <?php foreach ($DepreciationTypeList as $type) { ?>
                                    <option value="{{$type->id}}" data-attr="{{$type->percentage}}" 
                                        <?php if (isset($register) && $register->depreciation_type == $type->id) {
                                                echo "selected";
                                            } ?>>{{$type->percentage}} (%)</option>
                                    <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">B/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" onkeyup="calculate()" id="depreciation_bfwd" name="depreciation_bfwd" value="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Disps</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" onkeyup="calculate()" id="depreciation" name="depreciation" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">Charge</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control numberInput" id="charge" name="charge" value="" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">C/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="depreciation_cfwd" name="depreciation_cfwd" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <label class="form_heading">N.B.V</label>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">B/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control editInput" id="nbv_bfwd" name="nbv_bfwd" placeholder="00.00" readonly value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputName" class="col-sm-2 control-label">C/fwd</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control editInput" id="nbv_cfwd" name="nbv_cfwd" placeholder="00.00" readonly>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="inputName" class="col-sm-4 control-label">NQ</label>
                                <div class="col-sm-8">

                                    <label class="radio-inline">
                                        <input type="radio"  name="nq" id="yes" value="1">Yes
                                    </label>
                                    <label class="radio-inline">
                                            <input type="radio" name="nq" id="no" value="0" checked="">No
                                    </label>
                                    <label for="" class="ps-2"><small>(NO CAPITAL ALLOWANCES CLAIM)</small></label>

                                </div>
                            </div>
                        </div>
                        
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Close</button>
                <button type="button" id="save_data" class="btn btn-primary" onclick="getSaveData()">Add</button>
            </div>
        </div>
    </div>
</div>
<!-- End here -->
</section>

<script>
    var assetSaveUrl = "{{ url('admin/sales-finance/assets/asset-register-save') }}";
    var redirectUrl = "{{url('admin/sales-finance/assets/asset-register')}}";
    var deleteAssetRegisterUrl = "{{ url('admin/sales-finance/assets/asset-register-delete') }}";
    function status_change(id, status) {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('admin/sales-finance/assets/depreciation-status-change')}}",
            data: {
                id: id,
                status: status,
                _token: token
            },
            success: function (response) {
                console.log(response);
                if (response.success === true) {
                    location.reload();
                } else {
                    alert("Something went wrong");
                    return false;
                }
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.message);
            }
        });
    }
</script>
<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@endsection