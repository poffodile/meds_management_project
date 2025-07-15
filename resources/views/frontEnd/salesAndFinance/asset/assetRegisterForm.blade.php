@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Fixed Asset Register form')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
  .form_heading{
    width: 100%;
    font-size: 16px;
    margin-bottom: 10px;
    border-top: 2px solid #ddd;
    padding-top: 20px;
    text-align: center;
  }
</style>

<!--main content start-->
<section class="wrapper">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 p-0">
        <div class="panel">
          <header class="panel-heading px-5">
            <h4>Fixed Asset Register form</h4>
          </header>
          <div class="panel-body">
            <div class="col-lg-12">
              <form id="assetRegisterFormData" class="customerForm">
                <input type="hidden" name="id" id="id" value="<?php if(isset($register) && $register->id !=''){echo $register->id;}?>">
                @csrf
                <div class="mt-4">
                  <label class="form_heading border-0 p-0">Asset Details</label>
                  <div class="row">
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Asset Name <span class="radStar">*</span></label>
                        <input type="text" class="form-control editInput" id="asset_name" name="asset_name" placeholder="Asset Name" value="<?php if(isset($register) && $register->asset_name !=''){echo $register->asset_name;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Date <span class="radStar">*</span></label>
                        <input type="Date" class="form-control editInput" id="date" name="date" value="<?php if(isset($register) && $register->date !=''){echo $register->date;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Asset Type <span class="radStar">*</span></label>
                        <select name="asset_type" id="asset_type" class="form-control editInput">
                          <?php foreach($AssetCategoryList as $cat){?>
                            <option value="{{$cat->id}}" <?php if(isset($register) && $register->asset_type == $cat->id){echo "selected";}?>>{{$cat->name}}</option>
                          <?php }?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- End  off newJobForm -->
                <div class="mt-4">
                  <label class="form_heading">Cost</label>
                  <div class="row">
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label>B/fwd</label>
                        <input type="text" class="form-control editInput numberInput" id="cost_bfwd" name="cost_bfwd" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_bfwd !=''){echo $register->cost_bfwd;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Additions </label>
                        <input type="text" class="form-control editInput numberInput" id="cost_addition" name="cost_addition" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_addition !=''){echo $register->cost_addition;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Disposals </label>
                        <input type="text" class="form-control editInput numberInput" id="cost_disposal" name="cost_disposal" placeholder="Disposals" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_disposal !=''){echo $register->cost_disposal;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label>C/fwd</label>
                        <input type="text" class="form-control editInput" id="cost_fwd" name="cost_fwd" placeholder="00.00" value="<?php if(isset($register) && $register->cost_fwd !=''){echo $register->cost_fwd;}?>" readonly>
                        </div>
                    </div>
                  </div>
                </div>
                <!-- End  off newJobForm -->
                <div class="mt-4">
                  <label class="form_heading">Depreciation</label>
                  <div class="row">
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Type of Depreciation</label>
                        <select name="depreciation_type" id="depreciation_type" class="form-control editInput" onchange="calculate()">
                          <?php foreach($DepreciationTypeList as $type){?>
                            <option value="{{$type->id}}" data-attr="{{$type->percentage}}" <?php if(isset($register) && $register->depreciation_type ==$type->id){echo "selected";}?>>{{$type->percentage}} (%)</option>
                          <?php }?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label>B/fwd</label>
                        <input type="text" class="form-control editInput numberInput" id="depreciation_bfwd" name="depreciation_bfwd" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->depreciation_bfwd !=''){echo $register->depreciation_bfwd;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Disps</label>
                        <input type="text" class="form-control editInput numberInput" id="depreciation" name="depreciation" placeholder="Disps" onkeyup="calculate()" value="<?php if(isset($register) && $register->depreciation !=''){echo $register->depreciation;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> Charge</label>
                        <input type="text" class="form-control editInput" id="charge" name="charge" placeholder="00.00" value="<?php if(isset($register) && $register->charge !=''){echo $register->charge;}?>" readonly>
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> C/fwd</label>
                        <input type="text" class="form-control editInput" id="depreciation_cfwd" name="depreciation_cfwd" placeholder="00.00" readonly value="<?php if(isset($register) && $register->depreciation_cfwd !=''){echo $register->depreciation_cfwd;}?>">
                        </div>
                    </div>
                  </div>
                </div>
                <!-- End  off newJobForm -->
                <div class="mt-4">
                  <label class="form_heading">N.B.V</label>
                  <div class="row">
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> B/fwd</label>
                        <input type="text" class="form-control editInput" id="nbv_bfwd" name="nbv_bfwd" placeholder="00.00" readonly value="<?php if(isset($register) && $register->nbv_cfwd !=''){echo $register->nbv_cfwd;}?>">
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label> C/fwd</label>
                        <input type="text" class="form-control editInput" id="nbv_cfwd" name="nbv_cfwd" placeholder="00.00" readonly>
                      </div>
                    </div>
                    <div class="col-md-4 col-lg-4 col-xl-3">
                      <div class="form-group">
                        <label>NQ</label>
                        <div class="col-form-label nq_input">
                        <input type="radio" name="nq" id="yes" <?php if(isset($register) && $register->nq ==1){echo "checked";}else{echo "unchecked";}?> value="1">
                        <label for="yes">Yes</label>
                        <input type="radio" name="nq" id="no" <?php if(isset($register) && $register->nq ==0){echo "checked";}else{echo "unchecked";}?> value="0">
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
              </form>
              <div class="jobsection justify-content-end">
                <a href="javascript:void(0)" onclick="getSaveData()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                <a href="{{url('sales-finance/assets/asset-register')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- <div class="row">
      <div class="col-md-12 col-lg-12 col-xl-12 px-3">
        <div class="pageTitleBtn">
          <a href="javascript:void(0)" onclick="getSaveData()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
          <a href="{{url('sales-finance/assets/asset-register')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
      </div>
    </div> -->
  </div>
</section>

<script>
  var assetSaveUrl = "{{ url('sales-finance/assets/asset-regiser-save') }}";
  var redirectUrl = "{{url('sales-finance/assets/asset-register')}}";
</script>

<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>

@endsection