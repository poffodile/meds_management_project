@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')
<script>
  // Ignore this in your implementation
  window.isMbscDemo = true;
</script>
<style>
  .dropbtn {
    font-size: 16px;
    border: none;
    cursor: pointer;
  }

  .dropdown {
    position: relative;
    display: inline-block;
  }

  .dropdown-content {
    display: none;
    position: absolute;
    right: 0px;
    background-color: #f1f1f1;
    min-width: 200px;
    box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
    z-index: 1;
  }

  .dropdown-content a,
  .dropdown-content button {
    color: #1f88b5;
    padding: 5px 12px;
    text-decoration: none;
    display: inline-block;
    font-size: 15px;
    outline: none;
    border: none;
    font-weight: 500;
  }

  .dropdown-content ul {
    list-style-type: none;
    padding-left: 0px;
    margin-bottom: 0rem;
  }

  .dropdown-content .dropdown_icon {
    fill: #106e97cf;
    margin-right: 10px;
  }

  .dropdown-content ul li {
    padding: .50rem .50rem .50rem 1rem;
    transition: all 0.3s ease-in-out;
  }

  .dropdown-content ul li:hover {
    background-color: #1f88b529;
  }

  .dropdown-content ul li .delete-icon {
    fill: #990000;
    margin-right: 10px;
  }

  .dropdown-content ul li a {
    color: initial;
    font-weight: 500;
  }

  .dropdown-content ul li a:hover {
    color: initial;
  }

  .dropdown-content ul li:last-child .delete_btn {
    color: #990000;
  }

  .dropdown-content ul li:last-child:hover {
    background-color: #99000024;
  }

  .dropdown-content ul li a {
    cursor: pointer;
    flex: 1;
  }

  .form-control {
    border: 2px solid #a1a9b3;
  }

  .form-control:focus {
    border: 2px solid #1f88b5;
    box-shadow: rgb(0 0 0 / 25%) 0px 2px 8px;
  }

  .modal-content .modal-header {
    background-color: #1f88b5;
    color: #fff;
    display: flex;
  }

  .modal-content .modal-header h4.modal-title {
    color: #fff;
  }

  .modal-dialog .close_btn_modal {
    border: none;
    outline: none;
    background-color: inherit;
    color: #1f88b5;
    border: 2px solid #1f88b5;
    padding: 5px 16px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease-in-out;
  }

  .modal-dialog .close_btn_modal:hover {
    background-color: #0d688e;
    color: #fff;
    border: 2px solid #0d688e;
  }

  .modal-dialog .save_btn_modal {
    border: none;
    outline: none;
    padding: 8px 20px;
    background-color: #1f88b5;
    color: #fff;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease-in-out;
  }

  .modal-dialog .save_btn_modal:hover {
    background-color: #0d688e;
    border-color: #0d688e;
  }

  .modal-header .modal_close_btn {
    color: #fff;
    border: none;
    outline: none;
    background-color: inherit;
    font-size: 22px;
  }

  .active-rots-slt-from h4 {
    margin-top: 0px;
  }

  .disable_btn {
    z-index: -1;
    position: relative;
    background-color: rgba(188, 203, 214) !important;
    border: 2px solid rgba(188, 203, 214) !important;
  }

  .hide1 {
    display: none
  }

  #publishModal .modal-dialog,
  #unpublishModal .modal-dialog {
    max-width: 800px;
  }

  /* .rotaBtn {
    text-align: right;
    position: relative;
    top: 40px;
} */
  .rotaBtn button {
    background: #1f88b5;
    color: white;
    font-weight: 500;
    padding: 7px 10px;
    border-radius: 5px;
  }

  .paraentBtnRota {
    display: flex;
  }

  .rotaBtn {
    margin-left: 30px;
    margin-top: 25px;
  }

  section#container {
    height: auto;
  }

  .shrink_all .py-2 {
    white-space: nowrap;
  }
</style>
<!-- Top Bar Info Section End Here -->



<section class="wrapper">


  <div class="panel">
    <header class="panel-heading tab-bg-dark-navy-blue ">
      <ul class="nav nav-tabs">
        <li class="active">
          <a data-toggle="tab" href="#activeRotas">Active Rotas</a>
        </li>
        <li class="">
          <a data-toggle="tab" href="#oldRotas">Old Rotas</a>
        </li>
        <li class="">
          <a data-toggle="tab" href="#createRota">Create rota</a>
        </li>
      </ul>
    </header>
    <div class="panel-body">
      <div class="tab-content">
        <div id="activeRotas" class="tab-pane active">
          <form class="active-rots-slt-from">
            <div class="row">
              <div class="col-lg-12">
                <div class="paraentBtnRota">
                  <h3>Active rotas</h3>
                  <div class="rotaBtn">
                  </div>
                </div>
              </div>
              <div class="col-lg-3 col-md-3">
                <input type="date" placeholder="Select range..." id="search_date" class="form-control">
              </div>
              <div class="col-lg-3 col-md-3">
                <input type="text" class="form-control form-select" id="rota_name_search" placeholder="Rota name...">
              </div>
              <div class="col-md-3 col-lg-3">
                <button type="button" class="filter_btn">Clear all filter</button>
              </div>
              <div class="col-lg-3 col-md-3">
                <select name="sortBy" id="sortBy" class="form-select form-control">
                  <option value="1">Name (A-Z)</option>
                  <option value="2">Name (Z-A)</option>
                  <option value="3">Start date (Newest first)</option>
                  <option value="4">Start date (Oldest first)</option>
                </select>
              </div>
              <div id="result"></div>
            </div>
          </form>
          <div class="my-5 publish_rota_content">
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-center">
                <h4>Published rotas</h4>
                <span class="no_of_rota_publish" id="active_publish_rota_count"></span>
              </div>
              <div class="toggle_btns">
                <button class="view_all_btn" onclick="showRotaPublish()" id="viewPublish">View all</button>
                <button class="show_less_btn" onclick="lessRotaPublish()" id="lessPublish">Show less</button>
              </div>
            </div>
            <div class="content_about_publish" id="beforePublishRota">

              <h5>In progress</h5>
              <div id="new_publish_rota"></div>

              <div class="publishedRotasList p-t-20">
                <h5>Future rotas</h5>
                <p class="pb-8 radtext">Rotas that are starting in the future will appear here.</p>
              </div>
            </div>
          </div>
          <div class="unpublish_rota_content">
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-center">
                <h4>Unpublished rotas</h4>
                <span class="no_of_rota_publish" id="active_unpublish_rota_count"></span>
              </div>
              <div class="toggle_btns">
                <button class="view_all_btn" onclick="unPublishview()" id="viewUnpublish">View all</button>
                <button class="show_less_btn" onclick="unPublishless()" id="lessUnpublish">Show less</button>
              </div>
            </div>
          </div>
          <div id="unpublish_rota_content_detail">
            <div id="new_unpublish_rota">

            </div>
          </div>
          <div class="">
            <div class="no-rate-see">
              <h2>No rotas to see here yet...</h2>
            </div>
          </div>
        </div>
        <div id="oldRotas" class="tab-pane">
          <form class="active-rots-slt-from">
            <div class="row">
              <div class="col-lg-12">
                <h3>Old rotas</h3>
              </div>
              <!-- <div class="col-lg-3 col-md-3">
                <h4 class="d-none"><a href="#">Create new Rota</a></h4>
              </div> -->
              <div class="col-lg-3 col-md-3">
                <input type="date" placeholder="Select range..." id="old_search_date" class="form-control">
              </div>
              <div class="col-lg-3 col-md-3">
                <input type="text" class="form-control" id="old_rota_name_search" placeholder="Rota name...">
              </div>
              <div class="col-md-3">
                <button type="button" class="filter_btn">Clear all filter</button>
              </div>
              <div class="col-md-3 col-lg-3">
                <select name="" class="form-select form-control" id="old_sortBy">
                  <option value="1">Name (A-Z)</option>
                  <option value="2">Name (Z-A )</option>
                  <option value="3">Start date (Newest first)</option>
                  <option value="4">Start date (Oldest first)</option>
                </select>
              </div>

            </div>
            <div class="row">


            </div>
          </form>
          <div class="row">
            <div class="my-5 publish_rota_content">
              <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center">
                  <h4>Published rotas</h4>
                  <span class="no_of_rota_publish" id="old_publish_rota_count"></span>
                </div>
                <div class="toggle_btns">
                  <button class="view_all_btn" onclick="showRotaPublish()" id="viewPublish" style="display: none;">View all</button>
                  <button class="show_less_btn" onclick="lessRotaPublish()" id="lessPublish">Show less</button>
                </div>
              </div>
              <div class="content_about_publish" id="beforePublishRota">
                <h5>In progress</h5>
                <div id="old_publish_rota">
                </div>
              </div>
            </div>

            <div class="unpublish_rota_content">
              <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center">
                  <h4>Unpublished rotas</h4>
                  <span class="no_of_rota_publish" id="old_unpublish_rota_count"></span>
                </div>
                <div class="toggle_btns">
                  <button class="view_all_btn" onclick="unPublishview()" id="viewUnpublish" style="display: none;">View all</button>
                  <button class="show_less_btn" onclick="unPublishless()" id="lessUnpublish">Show less</button>
                </div>
              </div>
            </div>
            <div class="col-md-12 col-lg-12" id="unpublish_rota_content_detail">
              <div id="old_unpublish_rota">
              </div>
            </div>
          </div>
        </div>
        <div id="createRota" class="tab-pane">
          <form action="{{ url('/add-rota-data') }}" method="POST" class="select-rota formOne" id="createRota">
            <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
            <div class="row">
              <div class="col-md-12 mt-4">
                <h3>Create a new rota</h3>
              </div>
              <div class="form-group my-3">
                <label for="rota-name" class="col-md-2 mb-2">Rota name</label>
                <div class="col-sm-4 col-md-4">
                  <input type="text" name="rota_name" id="rota-name" required class="form-control" placeholder="Enter a new rota name">
                </div>
              </div>
              <div class="form-group my-3">
                <label for="select-days" class="col-md-2 mb-2">Rota duration</label>
                <div class="col-sm-4 col-md-4">
                  <select name="rotaPeriodLength" class="form-select form-control" id="select-days">
                    <option value="7">7 days</option>
                    <option value="8">8 days</option>
                    <option value="9">9 days</option>
                    <option value="10">10 days</option>
                    <option value="11">11 days</option>
                    <option value="12">12 days</option>
                    <option value="13">13 days</option>
                    <option value="14">14 days</option>
                    <option value="15">15 days</option>
                    <option value="16">16 days</option>
                    <option value="17">17 days</option>
                    <option value="30">Calendar month</option>
                  </select>
                </div>
              </div>
              <div class="form-group my-3">
                <label for="select-date" class="col-md-2 mb-2">Rota start date</label>
                <div class="col-sm-4 col-md-4">
                  <input type="date" id="select-date" required name="start_date" class="form-control" placeholder="Select date">
                </div>
              </div>
              <div class="d-none">
                <div class="col-md-12 mt-2">
                  <h3>Select your rota view</h3>
                </div>
                <div class="col-lg-4 select-rota">
                  <div class="box-createrota-boz card-btn card">
                    <input type="radio" name="rota_view" class="radio-btn" value="1">
                    <div class="bg-color">
                      <i class="fa fa-th" aria-hidden="true"></i>
                      <h3>Table view</h3>
                      <p>Set your shift times and easily assign this across employees and dates at once, with a click.</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 select-rota">
                  <div class="box-createrota-boz card card-btn">
                    <input type="radio" name="rota_view" class="radio-btn" value="2">
                    <div class="bg-color">
                      <i class="fa fa-calendar"></i>
                      <h3>Timeline view</h3>
                      <p>Add your shift times and assign this to as many employees as you need, right then and there. </p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 select-rota">
                  <div class="box-createrota-boz card-btn card">
                    <input type="radio" name="rota_view" class="radio-btn" value="3">
                    <div class="bg-color">
                      <i class="fa fa-clone"></i>
                      <h3>Drag and drop view</h3>
                      <p>Drag and drop each employee onto the shift you'd like them to work.</p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-12 mt-4">
                <div class="submit-btn my-3">
                  <button type="submit" id="rota_submit_btn" class="">Create your rota</button> <!-- disable_btn -->
                </div>
              </div>
            </div>
          </form>
          <form action="" class="select-rota formTwo" id="copyRota" style="display: none;">
            <div class="row">
              <div class="col-md-12 mt-4">
                <h3>Copy an existing rota</h3>
              </div>
              <div class="form-group col-md-12 my-3">
                <label for="select-days" class="mb-2">Select a rota to copy</label>
                <div class="col-sm-3 col-md-4">
                  <select name="whichRotaToCopy" class="form-select form-control" id="whichRotaToCopy">
                    <option disabled="" value="">Select rota...</option>
                    <option value="d9a73a7f-f3cb-41af-b3a5-e2d7115109f5">Mon 09 Jan 2023 - Shift 3</option>
                    <option value="bd2e1487-ee40-43cf-9462-c977a314fcce">Wed 04 Jan 2023 - Shift 1</option>
                    <option value="a8d09c6a-0e39-452e-8fdc-e61ea51c253d">Mon 21 Nov 2022 - Nov week 3
                    </option>
                  </select>
                </div>
              </div>
              <div class="form-group col-md-12 my-3">
                <label for="rota-name" class="mb-2">Copy the notes for this rota?</label>
                <div class="row">
                  <div class="col-md-2 select-rota">
                    <div class="card-btn card">
                      <input type="radio" class="radio-btn" name="select-btn" checked>
                      <div class="change-color">
                        <div class="custom-btn">
                          <p class="bg-color-custom-btn"></p>
                        </div>
                        <div class="bg-color">Yes</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 select-rota">
                    <div class="card-btn card">
                      <input type="radio" class="radio-btn" name="select-btn">
                      <div class="change-color">
                        <div class="custom-btn">
                          <p class="bg-color-custom-btn"></p>
                        </div>
                        <div class="bg-color">No</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group col-md-12 my-3">
                <label for="rota-name" class="mb-2">Copy colour coding and labels for this rota?</label>
                <div class="row">
                  <div class="col-md-2 select-rota">
                    <div class="card-btn card">
                      <input type="radio" class="radio-btn" name="select-btn-rota" checked>
                      <div class="change-color">
                        <div class="custom-btn">
                          <p class="bg-color-custom-btn"></p>
                        </div>
                        <div class="bg-color">Yes</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2 select-rota">
                    <div class="card-btn card">
                      <input type="radio" class="radio-btn" name="select-btn-rota">
                      <div class="change-color">
                        <div class="custom-btn">
                          <p class="bg-color-custom-btn"></p>
                        </div>
                        <div class="bg-color">No</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-group col-md-12 my-3">
                <label for="rota-name" class="mb-2">Rota name</label>
                <div class="col-sm-4 col-md-4">
                  <input type="text" id="rota-name" class="form-control" placeholder="Enter a new rota name">
                </div>
              </div>
              <div class="form-group col-md-12 my-3">
                <label for="select-date" class="mb-2">Rota start date</label>
                <div class="col-sm-4 col-md-4">
                  <input type="date" id="select-date" name="start_date" class="form-control" placeholder="Select date">
                </div>
              </div>
              <div class="col-md-12 mt-2">
                <h3>Select your rota view</h3>
              </div>
              <div class="col-lg-4 select-rota">
                <div class="box-createrota-boz card-btn card">
                  <input type="radio" class="radio-btn" name="select-btn">
                  <div class="bg-color">
                    <i class="fa fa-th" aria-hidden="true"></i>
                    <h3>Table view</h3>
                    <p>Set your shift times and easily assign this across employees and dates at once, with a click.</p>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 select-rota">
                <div class="box-createrota-boz card card-btn">
                  <input type="radio" class="radio-btn" name="select-btn">
                  <div class="bg-color">
                    <i class="fa fa-calendar"></i>
                    <h3>Timeline view</h3>
                    <p>Add your shift times and assign this to as many employees as you need, right then and there. </p>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 select-rota">
                <div class="box-createrota-boz card-btn card">
                  <input type="radio" class="radio-btn" name="select-btn">
                  <div class="bg-color">
                    <i class="fa fa-clone"></i>
                    <h3>Drag and drop view</h3>
                    <p>Drag and drop each employee onto the shift you'd like them to work.</p>
                  </div>
                </div>
              </div>
              <div class="submit-btn my-3">
                <button type="submit">Create your rota</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>


</section>
<!-- Rename Modal -->
<div class="modal" id="renameModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Rename rota</h4>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"> ✖ </button>
      </div>

      <!-- <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Rename rotar</h4>
        </div> -->

      <div class="modal-body">
        <div class="col-md-12">
          <div class="p-t-20 p-b-20">
            <input type="text" id="team-name" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <input type="hidden" id="renameid" value="" />
        <button type="button" class="close_btn_modal" data-bs-dismiss="modal">Close</button>
        <button type="button" id="rename_save_btn" data-bs-dismiss="modal" class="save_btn_modal">Save</button>
      </div>
    </div>
  </div>
</div>
<!-- Rename Modal End -->

<!-- Publish Modal -->
<div class="modal" id="publishModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Are you sure you want to publish this rota?</h1>
        <button type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"> ✖ </button>
      </div>
      <div class="modal-body">
        <div class="col-md-12">
          <input type="hidden" id="publish_rota_id" value="" />
          <span>This will show <span id="shift_name_for_publish"></span> to your employees. They will be able to see their shifts and absence conflicts.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="close_btn_modal" data-bs-dismiss="modal">Close</button>
        <button type="button" id="publish_model_btn" data-bs-dismiss="modal" class="save_btn_modal">Publish Rota</button>
      </div>
    </div>
  </div>
</div>
<!-- Publish Modal End-->

<!-- Unpublish Modal -->
<div class="modal" id="unpublishModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">Are you sure you want to unpublish this rota?</h4>
        <button type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"> ✖ </button>
      </div>
      <div class="modal-body">
        <div class="col-md-12">
          <input type="hidden" id="unpublish_rota_id" value="" />
          <span>This will hide <span id="shift_name_for_unpublish"></span> from your employees. They will no longer be able to access it or see their shifts and absence conflicts.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="close_btn_modal" data-bs-dismiss="modal">Close</button>
        <button type="button" id="unpublish_model_btn" data-bs-dismiss="modal" class="save_btn_modal">Unpublish Rota</button>
      </div>
    </div>
  </div>
</div>
<!-- Unpublish Modal End-->
<!-- Modal View start -->
<div class="modal" id="exampleModalViewDetail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel">Evening Shift</h4>
        <button type="button" class="modal_close_btn" data-bs-dismiss="modal" aria-label="Close"> ✖ </button>
      </div>
      <div class="modal-body">
        <div class="p-b-30">
          <div class="d-flex align-items-center">
            <span class="me-3">Select Week</span>
            <div class="col-md-4 col-lg-4 me-3">
              <select name="" class="form-select form-control" id="rota_starting_date">
              </select>
            </div>
            <span>Week total our: <strong><span id="week_total"></span> (Incl. breaks)</strong></span>
          </div>
          <div class="w_full view_detail_modal">
            <div class="d-flex align-items-center shrink_all">
              <div class="py-2">Employee</div>
              <div class="py-2">Curr. contracted hrs</div>
              <div class="py-2 ps-2">Days worked</div>
              <div class="py-2 d-flex">
                <div class="">Breaks</div>
                <small>(Total)</small>
              </div>
              <div class="py-2 d-flex">
                <div class="w_full">Total hrs</div>
                <form action="">
                  <small class="d-flex align-items-center">
                    Incl. breaks?
                    <label for="break_check">
                      <input type="checkbox" class="d-none" onchange="add_break(this)" id="break_check" value="1">
                      <span class="custom_checkbox">
                        <span class="d-flex align-items-center justify-content-center"><i class="fa fa-check" aria-hidden="true"></i></span>
                      </span>
                    </label>
                  </small>
                </form>
              </div>
            </div>
          </div>
          <div class="w_full row_detail" id="add_emp_record">
            <div class="d-flex align-items-center justify-content-end hour_count">
              <div class="m-0 py-3">
                <p class="fw-bolder hide2" id="total_emp_hour"></p>
                <p class="fw-bolder hide1" id="total_emp_hour_with_break"></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal View end -->
@include('rotaStaff.components.footer')
<script>
  $(document).ready(function() {

    $('#createrota-tab2').on('click', function() {
      var tab1 = document.getElementById("activerotas-tab");
      var div1 = document.getElementById("activerotas");
      tab1.classList.remove("active");
      div1.classList.remove("active");
      div1.classList.remove("show");

      var tab3 = document.getElementById("createrota-tab");
      var div3 = document.getElementById("createrota");
      tab3.className += " active";
      div3.className += " active";
      div3.className += " show";
    });

    const selectDate = document.getElementById('select-date');
    selectDate.addEventListener('change', (event) => {
      var element = document.getElementById("rota_submit_btn");
      element.classList.remove("disable_btn");
    });

    var token = "<?= csrf_token() ?>";
    $.ajax({
      url: "{{ url('/get_all_rota_data') }}",
      dataType: 'json',
      data: {
        _token: token
      },
      success: function(result) {
        console.log(result);
        if (isAuthenticated(result) == false) {
          return false;
        }
        document.getElementById('active_publish_rota_count').innerHTML = result.active_publish_rota_count;
        document.getElementById('active_unpublish_rota_count').innerHTML = result.active_unpublish_rota_count;
        document.getElementById('old_publish_rota_count').innerHTML = result.old_publish_rota_count;
        document.getElementById('old_unpublish_rota_count').innerHTML = result.old_unpublish_rota_count;

        if (result.new_active.length === 0) {
          document.querySelector('#new_publish_rota').insertAdjacentHTML(
            'afterbegin', `<p class="pb-8">Rotas that are currently active and in progress will appear here.</p>`
          );
        } else {
          for (let index = 0; index < result.new_active.length; index++) {
            document.querySelector('#new_publish_rota').insertAdjacentHTML(
              'afterbegin', result.new_active[index]
            );
          }
        }

        if (result.new_inactive.length === 0) {
          document.querySelector('#new_unpublish_rota').insertAdjacentHTML(
            'afterbegin', `<p class="pb-8">Rotas that are currently active and not in progress will appear here.</p>`
          );
        } else {
          for (let index = 0; index < result.new_inactive.length; index++) {
            document.querySelector('#new_unpublish_rota').insertAdjacentHTML(
              'afterbegin', result.new_inactive[index]
            );
          }
        }

        if (result.old_active.length === 0) {
          document.querySelector('#old_publish_rota').insertAdjacentHTML(
            'afterbegin', `<p class="pb-8">Rotas that are no longer active but are still published will appear here.</p>`
          );
        } else {
          for (let index = 0; index < result.old_active.length; index++) {
            document.querySelector('#old_publish_rota').insertAdjacentHTML(
              'afterbegin', result.old_active[index]
            );
          }
        }

        if (result.old_inactive.length === 0) {
          document.querySelector('#old_unpublish_rota').insertAdjacentHTML(
            'afterbegin', `<p class="pb-8">Rotas that are no longer active but are still unpublished will appear here.</p>`
          );
        } else {
          for (let index = 0; index < result.old_inactive.length; index++) {
            document.querySelector('#old_unpublish_rota').insertAdjacentHTML(
              'afterbegin', result.old_inactive[index]
            );
          }
        }

      }
    });
    const source = document.getElementById('rota_name_search');
    const result = document.getElementById('result');

    const inputHandler = function(e) {
      var search_name = e.target.value;
      // $.ajax({
      //   url: "{{ url('/get_record_of_rota') }}",
      //   type: "post",
      //   dataType: 'json',
      //   data: {
      //     search_name: search_name,
      //     _token: token
      //   },
      //   success: function(result) {
      //     console.log(result);
      //     if (isAuthenticated(result) == false) {
      //         return false;
      //     }
      //   }
      // });
      // result.innerHTML = e.target.value;
      filter_data(null, search_name, null, 1);
    }

    source.addEventListener('input', inputHandler);
    source.addEventListener('propertychange', inputHandler);
    $('#sortBy').on('change', function() {
      var sortBy = $(this).val();
      filter_data(null, null, sortBy, 1);
    });
    $('#search_date').on('change', function() {
      var search_date = $(this).val();
      // alert(search_date)
      filter_data(search_date, null, null, 1);
    });

    $('#old_search_date').on('change', function() {
      var old_search_date = $(this).val();
      // alert(old_search_date)
      filter_data(old_search_date, null, null, 2);
    });
    $('#old_rota_name_search').on('input', function() {
      var old_rota_name_search = $(this).val();
      // alert(old_rota_name_search)
      filter_data(null, old_rota_name_search, null, 2);
    });
    $('#old_sortBy').on('change', function() {
      var old_sortBy = $(this).val();
      // alert(old_sortBy)
      filter_data(null, null, old_sortBy, 2);
    });

    $('#rename_save_btn').on('click', function() {
      var rota_id = document.getElementById('renameid').value;
      var rota_name = document.getElementById('team-name').value;
      var token = "<?= csrf_token() ?>";
      $.ajax({
        url: "{{ url('/update_rota_name') }}",
        type: "post",
        dataType: 'json',
        data: {
          rota_id: rota_id,
          rota_name: rota_name,
          _token: token
        },
        success: function(result) {
          console.log(result);
          if (isAuthenticated(result) == false) {
            return false;
          }
          location.reload();
        }
      });
    });
    $('#publish_model_btn').on('click', function() {
      var publish_rota_id = document.getElementById('publish_rota_id').value;
      var token = "<?= csrf_token() ?>";
      $.ajax({
        url: "{{ url('/publish_rota_employee') }}",
        type: "post",
        dataType: 'json',
        data: {
          publish_rota_id: publish_rota_id,
          _token: token
        },
        success: function(result) {
          console.log(result);
          if (isAuthenticated(result) == false) {
            return false;
          }
          location.reload();
        }
      });
    });
    $('#unpublish_model_btn').on('click', function() {
      var unpublish_rota_id = document.getElementById('unpublish_rota_id').value;
      var token = "<?= csrf_token() ?>";
      $.ajax({
        url: "{{ url('/unpublish_rota_employee') }}",
        type: "post",
        dataType: 'json',
        data: {
          unpublish_rota_id: unpublish_rota_id,
          _token: token
        },
        success: function(result) {
          console.log(result);
          if (isAuthenticated(result) == false) {
            return false;
          }
          location.reload();
        }
      });
    });
  });

  let viewMore = document.getElementById('viewPublish').style.display = "none";

  function lessRotaPublish() {
    let showLess = document.getElementById('lessPublish');
    let viewMore = document.getElementById('viewPublish');
    let contentPublish = document.getElementById('beforePublishRota');
    if (viewMore.style.display === "none") {
      showLess.style.display = "none";
      viewMore.style.display = "block";
      contentPublish.style.display = "none";
    }
  }

  function showRotaPublish() {
    let showLess = document.getElementById('lessPublish');
    let viewMore = document.getElementById('viewPublish');
    let contentPublish = document.getElementById('beforePublishRota');
    if (showLess.style.display === "none") {
      showLess.style.display = "block";
      viewMore.style.display = "none";
      contentPublish.style.display = "block";
    }
  }

  let unPublishViewMore = document.getElementById('viewUnpublish').style.display = "none";

  function unPublishless() {
    let unPublishViewMore = document.getElementById('viewUnpublish');
    let unPublishLess = document.getElementById('lessUnpublish');
    let contentUnpublish = document.getElementById('unpublish_rota_content_detail');
    if (unPublishViewMore.style.display === "none") {
      unPublishViewMore.style.display = "block";
      unPublishLess.style.display = "none";
      contentUnpublish.style.display = "none";
    }
  }

  function unPublishview() {
    let unPublishViewMore = document.getElementById('viewUnpublish');
    let unPublishLess = document.getElementById('lessUnpublish');
    let contentUnpublish = document.getElementById('unpublish_rota_content_detail');
    if (unPublishViewMore.style.display === "block") {
      unPublishViewMore.style.display = "none";
      unPublishLess.style.display = "block";
      contentUnpublish.style.display = "block";
    }
  }
</script>
<script>
  function renamedata(id, name) {
    $('#team-name').val(name);
    $('#renameid').val(id);
    $('#renameModal').modal('show');
  }

  function publishRotaEmployee(id, name) {
    $('#publish_rota_id').val(id);
    document.getElementById('shift_name_for_publish').innerHTML = name;
    $('#publishModal').modal('show');
  }

  function unpublishRotaEmployee(id, name) {
    $('#unpublish_rota_id').val(id);
    document.getElementById('shift_name_for_unpublish').innerHTML = name;
    $('#unpublishModal').modal('show');
  }

  function DeleteRotaEmployee(id, name) {
    var token = "<?= csrf_token() ?>";
    $.ajax({
      url: "{{ url('/delete_rota_employee') }}",
      type: "post",
      dataType: 'json',
      data: {
        id: id,
        _token: token
      },
      success: function(result) {
        console.log(result);
        if (isAuthenticated(result) == false) {
          return false;
        }
        location.reload();
      }
    });
  }

  function RotaView(id, name) {

    var token = "<?= csrf_token() ?>";
    $.ajax({
      url: "{{ url('/get_all_shift') }}",
      type: "post",
      dataType: 'json',
      data: {
        id: id,
        _token: token
      },
      success: function(result) {
        console.log(result);
        if (isAuthenticated(result) == false) {
          return false;
        }
        $('#rota_starting_date').append("<option>Week 1 - " + result + "</option>");
      }
    });


    $.ajax({
      url: "{{ url('/get_rota_employee') }}",
      type: "post",
      dataType: 'json',
      data: {
        id: id,
        _token: token
      },
      success: function(result) {
        console.log(result);

        if (isAuthenticated(result) === false) return false;

        // Clear existing records
        const recordContainer = document.querySelector('#add_emp_record');
        if (!recordContainer) return console.error('#add_emp_record not found');
        // $(".shrink_all").html('');
        $(".dynamic_data").html('');


        let total_emp_minutes = 0;
        let total_emp_minutes_with_break = 0;
        let total_emp_hours = 0;

        result.forEach((emp, empIndex) => {
          let emp_total_minutes = 0;
          let emp_total_break = 0;

          for (let i = 0; i < emp.shift_start_time.length; i++) {
            let startTime = moment(emp.shift_start_time[i].shift_start_time);
            let endTime = moment(emp.shift_end_time[i].shift_end_time);
            let breakMin = parseInt(emp.break[i].break || 0);

            let duration = moment.duration(endTime.diff(startTime));
            let minutes = parseInt(duration.asMinutes());
            emp_total_minutes += minutes;
            emp_total_break += breakMin;
          }

          let durationWithoutBreak = moment.duration(emp_total_minutes, 'minutes');
          let durationWithBreak = moment.duration(emp_total_minutes + emp_total_break, 'minutes');

          total_emp_minutes += emp_total_minutes;
          total_emp_minutes_with_break += emp_total_minutes + emp_total_break;

          // rota days loop
          let loopData = '';
          let startDate = moment(emp.rota_start_date);
          let endDate = moment(emp.rota_end_date);
          let rotaDay = moment(emp.rota_day_date);

          for (let m = moment(startDate); m.diff(endDate, 'days') <= 0; m.add(1, 'days')) {
            const dateIsSame = m.isSame(rotaDay, 'day');
            loopData += `<p class='ms-2 fw-bolder' ${dateIsSame ? "style='color:#ad005c;'" : ''}>${m.format('dd').charAt(0)}</p>`;
          }

          recordContainer.insertAdjacentHTML('beforeend', `
            <div class="d-flex align-items-center shrink_all dynamic_data">
              <div class="w_19 py-2">${emp.name}</div>
              <div class="w_19 py-2">${durationWithoutBreak.hours()} hrs</div>
              <div class="w_19 py-2"><span class="d-flex">${loopData}</span></div>
              <div class="w_19 py-2">
                <div class="w_full">${emp_total_break} min</div>
              </div>
              <div class="w_19 py-2">
                <div id="without_break">
                  <div class="w_full hide2">${durationWithoutBreak.hours()} hrs and ${durationWithoutBreak.minutes()} mins.</div>
                  <div class="w_full hide1">${durationWithBreak.hours()} hrs and ${durationWithBreak.minutes()} mins.</div>
                </div>
              </div>
            </div>
          `);
        });

        // Final total calculations
        const totalWithoutBreak = moment.duration(total_emp_minutes, 'minutes');
        const totalWithBreak = moment.duration(total_emp_minutes_with_break, 'minutes');

        document.getElementById('total_emp_hour').innerHTML = `${totalWithoutBreak.hours()} hrs and ${totalWithoutBreak.minutes()} mins.`;
        document.getElementById('total_emp_hour_with_break').innerHTML = `${totalWithBreak.hours()} hrs and ${totalWithBreak.minutes()} mins.`;
        document.getElementById('week_total').innerHTML = `${totalWithoutBreak.hours()} hrs and ${totalWithoutBreak.minutes()} mins.`;
      }
    });

    $('#exampleModalViewDetail').modal('show');
  }

  function add_break(th) {
    if ($(th).is(':checked')) {
      $('.hide2').css('display', 'none');
      $('.hide1').css('display', 'block');
    } else {
      $('.hide2').css('display', 'block');
      $('.hide1').css('display', 'none');
    }
  }

  function filter_data(date = null, name = null, sortBy = null, type) {
    var token = "<?= csrf_token() ?>";
    $.ajax({
      url: "{{ url('/get_all_rota_data') }}",
      dataType: 'json',
      data: {
        date: date,
        name: name,
        sortBy: sortBy,
        type: type,
        _token: token
      },
      success: function(result) {
        console.log(result);
        if (isAuthenticated(result) == false) {
          return false;
        }
        document.getElementById('active_publish_rota_count').innerHTML = result.active_publish_rota_count;
        document.getElementById('active_unpublish_rota_count').innerHTML = result.active_unpublish_rota_count;
        document.getElementById('old_publish_rota_count').innerHTML = result.old_publish_rota_count;
        document.getElementById('old_unpublish_rota_count').innerHTML = result.old_unpublish_rota_count;

        if (result.new_active.length === 0) {
          document.querySelector('#new_publish_rota').innerHTML = '<p class="pb-8">Rotas that are currently active and in progress will appear here.</p>';
        } else {
          document.querySelector('#new_publish_rota').innerHTML = '';
          for (let index = 0; index < result.new_active.length; index++) {
            document.querySelector('#new_publish_rota').insertAdjacentHTML(
              'beforeend', result.new_active[index]
            );
          }
        }

        if (result.new_inactive.length === 0) {
          document.querySelector('#new_unpublish_rota').innerHTML = '<p class="pb-8">Rotas that are currently active and not in progress will appear here.</p>';
        } else {
          document.querySelector('#new_unpublish_rota').innerHTML = '';
          for (let index = 0; index < result.new_inactive.length; index++) {
            document.querySelector('#new_unpublish_rota').insertAdjacentHTML(
              'beforeend', result.new_inactive[index]
            );
          }
        }

        if (result.old_active.length === 0) {
          document.querySelector('#old_publish_rota').innerHTML = '<p class="pb-8">Rotas that are no longer active but are still published will appear here.</p>';
        } else {
          document.querySelector('#old_publish_rota').innerHTML = '';
          for (let index = 0; index < result.old_active.length; index++) {
            document.querySelector('#old_publish_rota').insertAdjacentHTML(
              'beforeend', result.old_active[index]
            );
          }
        }

        if (result.old_inactive.length === 0) {
          document.querySelector('#old_unpublish_rota').innerHTML = '<p class="pb-8">Rotas that are no longer active but are still unpublished will appear here.</p>';
        } else {
          document.querySelector('#old_unpublish_rota').innerHTML = '';
          for (let index = 0; index < result.old_inactive.length; index++) {
            document.querySelector('#old_unpublish_rota').insertAdjacentHTML(
              'beforeend', result.old_inactive[index]
            );
          }
        }

      }
    });
  }
</script>