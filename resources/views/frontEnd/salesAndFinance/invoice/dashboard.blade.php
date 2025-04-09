@include('frontEnd.salesAndFinance.jobs.layout.header')

<section class="home_section_cont px-3 ">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body row">
                        <div class="col-md-2">
                            <div class="pageTitle p-1">
                                <h3>Invoices Dashboard</h3>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="">
                                <input type="date" class="form-control editInput" id="inputName" value="Month">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="">
                                <select class="form-control editInput selectOptions" id="inputCustomer">
                                    <option>Job Type</option>
                                    <option>Customer-2</option>
                                    <option>Customer-3</option>
                                    <option>Customer-4</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="customInputContainer">
                                <div class="customInput">
                                    <div class="selectedData">Select Customer</div>
                                    <i class="fa-solid fa-angle-right"></i>
                                </div>
                                <div class="options">
                                    <div class="searchInput">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" id="searchInput" placeholder="Search">
                                    </div>
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="customInputContainer">
                                <div class="customInput">
                                    <div class="selectedData">Select Project</div>
                                    <i class="fa-solid fa-angle-right"></i>
                                </div>
                                <div class="options">
                                    <div class="searchInput">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" id="searchInput" placeholder="Search">
                                    </div>
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="customInputContainer">
                                <div class="customInput">
                                    <div class="selectedData">Select Region</div>
                                    <i class="fa-solid fa-angle-right"></i>
                                </div>
                                <div class="options">
                                    <div class="searchInput">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input type="text" id="searchInput" placeholder="Search">
                                    </div>
                                    <ul></ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="pageTitleBtn p-0">
                                <a href="#" class="profileDrop dashboardSearchBtn"><i class="material-symbols-outlined"> data_loss_prevention </i> Search </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="">
            <div class="row">
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Total Invoices Due</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Total Draft Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Total Overdue Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Total Outstanding Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt</i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Overdue Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Active Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt</i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Invoices not Posted To Accounts Software</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt  </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt</i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <h4 class="card-title">Completed Invoices</h4>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                    <span>Count</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="numberCount"><a href="#!">0</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                            <div class="row card_text mt-3">
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined">arrow_upward_alt</i></a> </h6>
                                    <span>This Month</span>
                                </div>
                                <div class="col-6">
                                    <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                    <span>Last Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card link_box mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Total Payments</h4>
                                    <div class="row card_text mt-3">
                                        <div class="col-6">
                                            <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined">arrow_upward_alt</i></a> </h6>
                                            <span>Count</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="numberCount"><a href="#!">0</a> </h6>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                    <div class="row card_text mt-3">
                                        <div class="col-6">
                                            <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined"> arrow_upward_alt </i></a> </h6>
                                            <span>This Month</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="card link_box mt-4">
                                <div class="card-body">
                                    <h4 class="card-title">Potential Invoice Payments</h4>
                                    <div class="row card_text mt-3">
                                        <div class="col-6">
                                            <h6 class="numberCount"><a href="#!" class="up_arrow">22 <i class="material-symbols-outlined"> arrow_upward_alt</i></a> </h6>
                                            <span>Count</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="numberCount"><a href="#!">0</a> </h6>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                    <div class="row card_text mt-3">
                                        <div class="col-6">
                                            <h6 class="slry_pakage"><a href="#!" class="up_arrow">$55.00 <i class="material-symbols-outlined">arrow_upward_alt  </i></a> </h6>
                                            <span>This Month</span>
                                        </div>
                                        <div class="col-6">
                                            <h6 class="slry_pakage"><a href="#!">$0.00</a> </h6>
                                            <span>Last Month</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <div id='PieChart'></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card link_box mt-4">
                        <div class="card-body">
                            <div id="lineChart">
                                <a href="https://www.zingchart.com/" rel="noopener" class="zc-ref">Powered by ZingChart</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.zingchart.com/zingchart.min.js"></script>

@include('frontEnd.salesAndFinance.jobs.layout.footer')