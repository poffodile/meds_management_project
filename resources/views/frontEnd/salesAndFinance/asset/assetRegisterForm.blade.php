@include('frontEnd.salesAndFinance.jobs.layout.header')
<section class="main_section_page px-3 pt-0">
  <div class="container-fluid">

    <div class="row">
      <div class="col-md-4 col-lg-4 col-xl-4 ">
        <div class="pageTitle">
          <h3>Fixed Asset Register form</h3>
        </div>
      </div>
      <div class="col-md-8 col-lg-8 col-xl-8 px-3">
        <div class="pageTitleBtn">
          <a href="javascript:void(0)" onclick="getSaveData()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
          <a href="{{url('sales-finance/assets/asset-register')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
          <!-- <a href="#" class="profileDrop"> Action <i class="fa-solid fa-arrow-down"></i>
                </span></a> -->
        </div>
      </div>
      <form id="assetRegisterFormData" class="customerForm">
        <input type="hidden" name="id" id="id" value="<?php if(isset($register) && $register->id !=''){echo $register->id;}?>">
        @csrf
        <div class="row">
          <div class="col-lg-12">
            <div class="newJobForm mt-4">
              <label class="upperlineTitle">Home</label>
              <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="asset_name" class="col-sm-2 col-form-label"> Asset Name <span
                            class="radStar">*</span></label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput" id="asset_name" name="asset_name" placeholder="Asset Name" value="<?php if(isset($register) && $register->asset_name !=''){echo $register->asset_name;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="date" class="col-sm-2 col-form-label"> Date <span
                            class="radStar">*</span></label>
                        <div class="col-sm-9">
                          <input type="Date" class="form-control editInput" id="date" name="date" value="<?php if(isset($register) && $register->date !=''){echo $register->date;}?>">
                        </div>
                      </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="asset_type" class="col-sm-2 col-form-label"> Asset Type <span
                            class="radStar">*</span></label>
                        <div class="col-sm-9">
                          <select name="asset_type" id="asset_type" class="form-control editInput">
                            <?php foreach($AssetCategoryList as $cat){?>
                              <option value="{{$cat->id}}" <?php if(isset($register) && $register->asset_type == $cat->id){echo "selected";}?>>{{$cat->name}}</option>
                            <?php }?>
                          </select>
                        </div>
                        </select>

                      </div>
                    </div>
                  </div>
                </div>

              </div>
           
            <!-- End  off newJobForm -->

            <div class="newJobForm mt-4">
              <label class="upperlineTitle">Cost</label>
              <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="cost_bfwd" class="col-sm-2 col-form-label">B/fwd</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput numberInput" id="cost_bfwd" name="cost_bfwd" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_bfwd !=''){echo $register->cost_bfwd;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="cost_disposal" class="col-sm-2 col-form-label"> Disposals </label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput numberInput" id="cost_disposal" name="cost_disposal" placeholder="Disposals" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_disposal !=''){echo $register->cost_disposal;}?>">
                        </div>
                      </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="cost_addition" class="col-sm-2 col-form-label"> Additions </label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput numberInput" id="cost_addition" name="cost_addition" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->cost_addition !=''){echo $register->cost_addition;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="cost_fwd" class="col-sm-2 col-form-label">C/fwd</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput" id="cost_fwd" name="cost_fwd" placeholder="00.00" value="<?php if(isset($register) && $register->cost_fwd !=''){echo $register->cost_fwd;}?>" readonly>
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>

           
            <!-- End  off newJobForm -->

            <div class="newJobForm mt-4">
              <label class="upperlineTitle">Depreciation</label>
              <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="depreciation_bfwd" class="col-sm-2 col-form-label">B/fwd</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput numberInput" id="depreciation_bfwd" name="depreciation_bfwd" placeholder="00.00" onkeyup="calculate()" value="<?php if(isset($register) && $register->depreciation_bfwd !=''){echo $register->depreciation_bfwd;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for=" depreciation_type" class="col-sm-2 col-form-label"> Type of Depreciation</label>
                        <div class="col-sm-9">
                          <select name="depreciation_type" id="depreciation_type" class="form-control editInput" onchange="calculate()">
                            <?php foreach($DepreciationTypeList as $type){?>
                              <option value="{{$type->id}}" data-attr="{{$type->percentage}}" <?php if(isset($register) && $register->depreciation_type ==$type->id){echo "selected";}?>>{{$type->percentage}} (%)</option>
                            <?php }?>
                          </select>
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="charge" class="col-sm-2 col-form-label"> Charge</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput" id="charge" name="charge" placeholder="00.00" value="<?php if(isset($register) && $register->charge !=''){echo $register->charge;}?>" readonly>
                        </div>
                      </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="depreciation" class="col-sm-2 col-form-label"> Disps</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput numberInput" id="depreciation" name="depreciation" placeholder="Disps" onkeyup="calculate()" value="<?php if(isset($register) && $register->depreciation !=''){echo $register->depreciation;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="depreciation_cfwd" class="col-sm-2 col-form-label"> C/fwd</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput" id="depreciation_cfwd" name="depreciation_cfwd" placeholder="00.00" readonly value="<?php if(isset($register) && $register->depreciation_cfwd !=''){echo $register->depreciation_cfwd;}?>">
                        </div>

                      </div>
                    </div>
                  </div>
                </div>
              </div>

           
            <!-- End  off newJobForm -->

            <div class="newJobForm mt-4">
              <label class="upperlineTitle">N.B.V</label>
              <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-6">
                  <div class="formDtail">
                      <div class="mb-3 row">
                        <label for="nbv_cfwd" class="col-sm-2 col-form-label"> B/fwd</label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control editInput" id="nbv_bfwd" name="nbv_cfwd" placeholder="00.00" readonly value="<?php if(isset($register) && $register->nbv_cfwd !=''){echo $register->nbv_cfwd;}?>">
                        </div>
                      </div>
                      <div class="mb-3 row">
                        <label for="NQ" class="col-sm-2 col-form-label">NQ</label>
                        <div class="col-sm-9 col-form-label nq_input">
                          <input type="radio" name="nq" id="yes" <?php if(isset($register) && $register->nq ==1){echo "checked";}else{echo "unchecked";}?> value="1">
                          <label for="yes">Yes</label>
                          <input type="radio" name="nq" id="no" <?php if(isset($register) && $register->nq ==0){echo "checked";}else{echo "unchecked";}?> value="0">
                          <label for="no">NO</label>
                          <label for="" class="ps-2"><small>(NO CAPITAL ALLOWANCES CLAIM)</small></label>
                        </div>
                      </div>
                    </div>
                    <!-- <div class="mb-3 row">
                      <label for="charge" class="col-sm-2 col-form-label"> Charge</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control editInput" id="charge" name="charge" placeholder="00.00" readonly>
                      </div>
                    </div>
                    <div class="mb-3 row">
                      <label for="depreciation" class="col-sm-2 col-form-label"> Disps</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control editInput numberInput" id="depreciation" name="depreciation" placeholder="Disps" onkeyup="calculate()">
                      </div>
                    </div> -->
                    <div class="mb-3 row">
                      <label for="depreciation_cfwd" class="col-sm-2 col-form-label"> C/fwd</label>
                      <div class="col-sm-9">
                        <input type="text" class="form-control editInput" id="nbv_cfwd" name="depreciation_cfwd" placeholder="00.00" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          </div>
        </form>
      </div>

      <!-- <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 px-3">
          <div class="pageTitleBtn">
            <a href="javascript:void(0)" onclick="getSaveData()" class="profileDrop"><i class="fa-solid fa-floppy-disk"></i> Save</a>
            <a href="{{url('sales-finance/assets/asset-register')}}" class="profileDrop"><i class="fa-solid fa-arrow-left"></i> Back</a>
            
          </div>
        </div>
      </div> -->

</section>

<script>
  var assetSaveUrl = "{{ url('sales-finance/assets/asset-regiser-save') }}";
  var redirectUrl = "{{url('sales-finance/assets/asset-register')}}";
</script>

<script src="{{ url('public/js/salesFinance/asset/asset_category.js')}}" defer></script>

@include('frontEnd.salesAndFinance.jobs.layout.footer')