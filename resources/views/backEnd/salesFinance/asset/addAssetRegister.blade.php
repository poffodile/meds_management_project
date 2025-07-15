@extends('backEnd.layouts.master')

@section('title',':User Form')

@section('content')

<style type="text/css">
    .position-center label {
        font-size: 13px;
        font-weight: 500;
    }

    .position-center {
        width: 100%
    }

    .ui-widget-content .ui-icon {
        background-image: url(images/ui-icons_444444_256x240.png) !important;
    }

    .position-center .assign-access {
        font-size: 16px;
        font-weight: 500;
    }

    .add_field_button-area {
        margin: 20px 0px 0px 0px;
    }

    .col-lg-offset-3 .btn.btn-primary {
        margin: 0px 10px 0px 0px;
    }

    .qual_upload {
        margin: 20px 0px 0px 0px;
    }


    .input-group-addon {
        border: none;
    }

    .input-group-addon.remove-addon {
        padding: 5px 0px 15px 0px;
    }

    .custom-legend {
        position: absolute;
        top: -10px;
        left: 20px;
        background-color: white;
        font-weight: bold;
        padding: 0 10px;
    }

    .custom-fieldset {
        position: relative;
        border: 2px solid #00000026;
        padding: 10px;
        margin-top: 20px;
    }

    .appointment_button {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .count {
        height: 30px;
        width: 30px;
        line-height: 15px;
        border-radius: 15px;
    }

    .leftNum {
        background: #53a6dc;
        padding: 4px;
        color: #fff;
        text-align: center;
        font-size: 14px;
        border-radius: 50%;
        width: 30px;
        height: 28px;
        line-height: 22px;
        margin-right: 4px;
    }

    .callIcon {
        font-size: 30px;
        color: #53a6dc;
        margin-left: 6px;
    }

    .addTextarea textarea {
        text-indent: 10px;
        width: 100%;
    }

    .addDateAndTime {
        display: flex;
    }

    .addDateAndTime .editInput {
        border: 1px solid #dee2e6;
    }

    .Priority {
        display: flex;
        padding-top: 6px;
    }

    .Priority label {
        padding: 10px;
        white-space: nowrap;
    }

    .statuswating {
        display: flex;
    }

    .statuswating a i {
        font-size: 22px;
        color: #53a6dc;
        line-height: 31px;
        margin-left: 8px;
    }

    .displaynone {
        display: none;
    }
</style>
<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        Fixed Asset Register form
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form id="assetRegisterFormData" class="customerForm">
                                <input type="hidden" id="id" name="id" value="<?php if(isset($register) && $register->id !=''){echo $register->id;}?>">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <h4 class="contTitle">Asset Details</h4>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3  control-label">Asset Name <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="asset_name" name="asset_name" value="<?php if(isset($register) && $register->asset_name !=''){echo $register->asset_name;}?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Date <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <input type="date" class="form-control" id="date" name="date" value="<?php if(isset($register) && $register->date !=''){echo $register->date;}?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Asset Type <span class="radStar">*</span></label>
                                            <div class="col-sm-9">
                                                <select name="asset_type" id="asset_type" class="form-control editInput">
                                                    <?php foreach ($AssetCategoryList as $cat) { ?>
                                                        <option value="{{$cat->id}}" <?php if(isset($register) && $register->asset_type == $cat->id){echo "selected";}?>>{{$cat->name}}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div> 
                                    </div>
                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <h4 class="contTitle">Cost</h4>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">B/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_bfwd" name="cost_bfwd" value="<?php if(isset($register) && $register->cost_bfwd !=''){echo $register->cost_bfwd;}?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Additions</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_addition" name="cost_addition" value="<?php if(isset($register) && $register->cost_addition !=''){echo $register->cost_addition;}?>">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Disposals</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" onkeyup="calculate()" id="cost_disposal" name="cost_disposal" value="<?php if(isset($register) && $register->cost_disposal !=''){echo $register->cost_disposal;}?>">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">C/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="cost_fwd" name="cost_fwd" value="<?php if(isset($register) && $register->cost_fwd !=''){echo $register->cost_fwd;}?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <h4 class="contTitle">Depreciation</h4>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Type of Depreciation</label>
                                            <div class="col-sm-9">
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
                                            <label for="inputName" class="col-sm-3 control-label">B/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" onkeyup="calculate()" id="depreciation_bfwd" name="depreciation_bfwd" value="<?php if(isset($register) && $register->depreciation_bfwd !=''){echo $register->depreciation_bfwd;}?>">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Disps</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" onkeyup="calculate()" id="depreciation" name="depreciation" value="<?php if(isset($register) && $register->depreciation !=''){echo $register->depreciation;}?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">Charge</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control numberInput" id="charge" name="charge" value="<?php if(isset($register) && $register->charge !=''){echo $register->charge;}?>" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">C/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="depreciation_cfwd" name="depreciation_cfwd" readonly value="<?php if(isset($register) && $register->depreciation_cfwd !=''){echo $register->depreciation_cfwd;}?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 col-lg-6 col-xl-6">
                                        <h4 class="contTitle">N.B.V</h4>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">B/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" id="nbv_bfwd" name="nbv_bfwd" placeholder="00.00" readonly value="<?php if(isset($register) && $register->nbv_cfwd !=''){echo $register->nbv_cfwd;}?>">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-3 control-label">C/fwd</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control editInput" id="nbv_cfwd" name="nbv_cfwd" placeholder="00.00" readonly value="">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <h4 class="contTitle">NQ</h4>
                                            <div class="col-sm-9">

                                                <label class="radio-inline">
                                                    <input type="radio"  name="nq" id="yes" <?php if(isset($register) && $register->nq ==1){echo "checked";}else{echo "unchecked";}?> value="1">Yes
                                                </label>
                                                <label class="radio-inline">
                                                        <input type="radio" name="nq" id="no" value="0" <?php if(isset($register) && $register->nq ==0){echo "checked";}else{echo "unchecked";}?>>No
                                                </label>
                                                <label for="" class="ps-2"><small>(NO CAPITAL ALLOWANCES CLAIM)</small></label>

                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-10">
                                            <div class="add-admin-btn-area pddtp">
                                                <button type="button" class="btn btn-primary save-btn" onclick="getSaveData()">Save</button>
                                                <a href="{{ url('admin/sales-finance/assets/asset-register') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
<script>
  var assetSaveUrl = "{{ url('admin/sales-finance/assets/asset-register-save') }}";
  var redirectUrl = "{{url('admin/sales-finance/assets/asset-register')}}";
</script>

<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>
@endsection