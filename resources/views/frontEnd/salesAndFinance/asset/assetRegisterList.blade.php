@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Fixed Asset Register')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
  .searchJobForm {
    border: 1px solid #eee;
    margin-bottom: 20px;
  }

  .form_heading {
    width: 100%;
    font-size: 16px;
    margin-bottom: 10px;
    /* border-top: 2px solid #ddd;
    padding-top: 20px; */
    text-align: center;
    font-weight: 600 !important;
  }
  .bg_color{
    background: #b0b5b9;
  }
  .fixedAssetSummary table.table thead tr th, .fixedAssetSummary table.table tbody tr td, .fixedAssetSummary table.table tbody tr th {
        font-size: 12px;
        line-height: 22px;
        vertical-align: middle;
    }
  
</style>

<!--main content start-->
<section class="wrapper">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 p-0">
        <div class="panel">
          <header class="panel-heading px-5">
            <h4>Fixed Asset Register</h4>
          </header>
          <div class="panel-body">
            <div class="col-lg-12">
              <div class="col-lg-6">
                <a href="{{url('sales-finance/assets/asset-register?cat=')}}" class="btn @if(isset($selected_cat_id) && $selected_cat_id ==0)bg_color @else btn-warning @endif">Summary</a>
                <?php foreach ($AssetCategoryList as $cat) { ?>
                  <a href="{{url('sales-finance/assets/asset-register?cat=')}}{{base64_encode($cat->id)}}" class="btn @if(isset($selected_cat_id) && $selected_cat_id ==$cat->id)bg_color @else btn-warning @endif">{{$cat->name}}</a>
                    <?php } ?>
              </div>
              <div class="col-lg-6 jobsection justify-content-end">
                <!-- <a href="{{url('sales-finance/assets/asset-regiser-add')}}" class="btn btn-warning"><i class="fa fa-plus"></i> Add</a> -->
                <a href="javascript:void(0)" class="btn btn-warning" data-toggle="modal" data-target="#Fixed_Asset_Register"><i class="fa fa-plus"></i> Add</a>
                <!-- <a href="javascript:void(0)" class="btn btn-warning">Export</a> -->
                @if($selected_cat_id != 0)
                <div class="searchFilter">
                  <a href="#!" onclick="hideShowDiv()" class="btn btn-primary">Search</a>
                </div>
                @endif
              </div>
              <div>
                <div class="searchJobForm" id="divTohide" style="display:none">
                  <div class="row">
                    <div class="col-lg-12">
                      <div class="extraInformationTab">
                        <form id="search_dataForm" class="p-4">
                          @csrf
                          <div class="row justify-content-center">
                            <div class="col-md-12">
                              <div class="row form-group pb-0 pageTitleBtn justify-content-center">
                                <div class="col-md-4">
                                  <label>Date From:</label>
                                  <!-- <input type="date" class="form-control editInput" id="edd_startDate" name="start_date"> -->
                                  <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                    <input name="start_date" id="edd_startDate" type="text" value="" autocomplete="off" class="form-control">

                                    <span class="input-group-btn datetime-picker2 btn_height">
                                      <button class="btn btn-primary" type="button" id="openCalendarBtn">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                      </button>
                                    </span>
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <label>Date To:</label>
                                  <!-- <input type="date" class="form-control editInput" id="edd_endDate" name="end_date"> -->
                                  <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                    <input name="end_date" id="edd_endDate" type="text" value="" autocomplete="off" class="form-control" disabled>

                                    <span class="input-group-btn datetime-picker2 btn_height">
                                      <button class="btn btn-primary" type="button" id="openCalendarBtn1">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                      </button>
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-12">
                              <div class="pageTitleBtn justify-content-center">
                                <a href="javascript:void(0)" onclick="searchBtn()" class="btn btn-primary px-3">Search </a>
                                <!-- <button type="submit" class="btn btn-warning px-3" onclick="return searchBtn()">Search</button> -->
                                <a href="javascript:void(0)" onclick="clearBtn('search_dataForm')" class="btn btn-default px-3 ms-2">Clear</a>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- <div class="extraInformationTab">
                  <div class="row">
                    <div class="col-md-12" id="search_data">
                    </div>
                  </div>
                </div> -->
              </div>
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
                        <td> <a href="{{url('sales-finance/assets/asset-register-edit?key=' . base64_encode($val->id))}}" class="openModalBtn"><i class="fa fa-pencil" aria-hidden="true"></i></a> |
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
          <!-- Button trigger modal -->
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Fixed Asset Register Modal start here -->
<div class="modal fade" id="Fixed_Asset_Register" tabindex="-1" aria-labelledby="Fixed_Asset_RegisterLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        <h4 class="modal-title" id="Fixed_Asset_RegisterLabel">Add Fixed Asset Register</h4>
      </div>
      <div calss="row">
        <div class="col-md-12 col-lg-12 col-xl-12 mt-4">
          <div class="mt-1 mb-0 text-center" style="display:none" id="message_save"></div>
        </div>
      </div>
      <form id="assetRegisterFormData" class="customerForm">
        <input type="hidden" name="id" id="id" value="">
        @csrf
        <div class="modal-body">
          <div class="">
            <label class="form_heading border-0 p-0">Asset Details</label>
            <div class="row">
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Asset Name <span class="radStar">*</span></label>
                  <input type="text" class="form-control editInput" id="asset_name" name="asset_name" placeholder="Asset Name" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Date <span class="radStar">*</span></label>
                  <input type="Date" class="form-control editInput" id="date" name="date" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Asset Type <span class="radStar">*</span></label>
                  <select name="asset_type" id="asset_type" class="form-control editInput">
                    <?php foreach ($AssetCategoryList as $cat) { ?>
                      <option value="{{$cat->id}}">{{$cat->name}}</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <!-- End  off newJobForm -->
          <div class="mt-4">
            <label class="form_heading">Cost</label>
            <div class="row">
              <div class="col-md-6 ">
                <div class="form-group">
                  <label>B/fwd</label>
                  <input type="text" class="form-control editInput numberInput" id="cost_bfwd" name="cost_bfwd" placeholder="00.00" onkeyup="calculate()" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Additions </label>
                  <input type="text" class="form-control editInput numberInput" id="cost_addition" name="cost_addition" placeholder="00.00" onkeyup="calculate()" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Disposals </label>
                  <input type="text" class="form-control editInput numberInput" id="cost_disposal" name="cost_disposal" placeholder="Disposals" onkeyup="calculate()">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label>C/fwd</label>
                  <input type="text" class="form-control editInput" id="cost_fwd" name="cost_fwd" placeholder="00.00" value="" readonly>
                </div>
              </div>
            </div>
          </div>
          <!-- End  off newJobForm -->
          <div class="mt-4">
            <label class="form_heading">Depreciation</label>
            <div class="row">
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Type of Depreciation</label>
                  <select name="depreciation_type" id="depreciation_type" class="form-control editInput" onchange="calculate()">
                    <?php foreach ($DepreciationTypeList as $type) { ?>
                      <option value="{{$type->id}}" data-attr="{{$type->percentage}}" <?php if (isset($register) && $register->depreciation_type == $type->id) {
                                                                                        echo "selected";
                                                                                      } ?>>{{$type->percentage}} (%)</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label>B/fwd</label>
                  <input type="text" class="form-control editInput numberInput" id="depreciation_bfwd" name="depreciation_bfwd" placeholder="00.00" onkeyup="calculate()" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Disps</label>
                  <input type="text" class="form-control editInput numberInput" id="depreciation" name="depreciation" placeholder="Disps" onkeyup="calculate()" value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> Charge</label>
                  <input type="text" class="form-control editInput" id="charge" name="charge" placeholder="00.00" value="" readonly>
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> C/fwd</label>
                  <input type="text" class="form-control editInput" id="depreciation_cfwd" name="depreciation_cfwd" placeholder="00.00" readonly value="">
                </div>
              </div>
            </div>
          </div>
          <!-- End  off newJobForm -->
          <div class="mt-4">
            <label class="form_heading">N.B.V</label>
            <div class="row">
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> B/fwd</label>
                  <input type="text" class="form-control editInput" id="nbv_bfwd" name="nbv_bfwd" placeholder="00.00" readonly value="">
                </div>
              </div>
              <div class="col-md-6 ">
                <div class="form-group">
                  <label> C/fwd</label>
                  <input type="text" class="form-control editInput" id="nbv_cfwd" name="nbv_cfwd" placeholder="00.00" readonly>
                </div>
              </div>
              <div class="col-md-12 ">
                <div class="form-group">
                  <label>NQ</label>
                  <div class="col-form-label nq_input">
                    <input type="radio" name="nq" id="yes" value="1">
                    <label for="yes">Yes</label>
                    <input type="radio" name="nq" id="no" value="0">
                    <label for="no">NO</label>
                    <label for="" class="ps-2"><small>(NO CAPITAL ALLOWANCES CLAIM)</small></label>
                  </div>
                </div>
                <!-- <div class="form-group">
                  <label> Charge</label>
                  <input type="text" class="form-control editInput" id="charge" name="charge" placeholder="00.00" readonly>
                </div>
                <div class="form-group">
                  <label> Disps</label>
                    <input type="text" class="form-control editInput numberInput" id="depreciation" name="depreciation" placeholder="Disps" onkeyup="calculate()">
                </div> -->
              </div>
            </div>
          </div>
          <!-- End  off newJobForm -->
        </div>
        <div class="modal-footer customer_Form_Popup">
          <a href="{{url('sales-finance/assets/asset-register')}}" class="btn btn-default"> Back</a>
          <a href="javascript:void(0)" onclick="getSaveData()" class="btn btn-warning"> Save</a>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- end here -->

<script>
  var searchUrl = "{{url('sales-finance/assets/asset-register-search')}}";
  var deleteAssetRegisterUrl = "{{url('sales-finance/assets/asset-register-delete')}}";
  var assetSaveUrl = "{{ url('sales-finance/assets/asset-regiser-save') }}";
  var redirectUrl = "{{url('sales-finance/assets/asset-register')}}";
  $(document).ready(function() {
    $('#edd_startDate').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true,
      todayHighlight: true
    });
    $(document).on('click', '#openCalendarBtn', function() {
      $('#edd_startDate').focus();
    });
    $('#edd_endDate').datepicker({
      format: 'dd-mm-yyyy',
      autoclose: true,
      todayHighlight: true
    });
    $('#openCalendarBtn1').click(function() {
      $('#edd_endDate').focus();
    });
  });
</script>

<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@endsection
<!-- <script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}"></script> -->