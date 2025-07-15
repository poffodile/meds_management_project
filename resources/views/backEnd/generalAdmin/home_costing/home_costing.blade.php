@extends('backEnd.layouts.master')
@section('title',' Home Costing')
@section('content')


<style>
    .panel-heading .nav>li.active>a, .panel-heading .nav>li>a:hover {
    color: #ffffff;
    background: #1fb5ad;
}
    
.tab-bg-dark-navy-blue {
    background: #48484b;
}

</style>

<!--main content start-->
<section id="main-content">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-sm-12">
                <div class="panel">
                    <header class="panel-heading">
                        Home Costing
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    <div class="panel-body">
                        <div class="adv-table editable-table ">
                            <div class="clearfix clearfix_space">
                                <div class="btn-group">
                                    <a href="{{url('admin/general-admin/home-costing/add')}}" id="editable-sample_new" class="btn btn-primary"> Add New <i class="fa fa-plus"></i></a>
                                </div>
                            </div>

                            <section class="panel">
                                <header class="panel-heading tab-bg-dark-navy-blue ">
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a data-toggle="tab" href="#companyOverheads">Company Overheads</a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#staffCosts"> Staff Costs</a>
                                        </li>
                                        <li class="">
                                            <a data-toggle="tab" href="#directCosts">YP Direct Costs</a>
                                        </li>
                                    </ul>
                                </header>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div id="companyOverheads" class="tab-pane active">

                                            <div class="adv-table table-responsive">
                                                <table class="display table table-bordered table-striped" id="">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Director Name</th>
                                                            <th>Per Annum</th>
                                                            <th>Per Month</th>
                                                            <th>Per Week</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th>1.</th>
                                                            <td>Managing Director</td>
                                                            <td>£250,000.00</td>
                                                            <td>£20,833.33</td>
                                                            <td>£4,807.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Director / RI</td>
                                                            <td>£105,000.00</td>
                                                            <td>£8,750.00</td>
                                                            <td>£2,019.23</td>
                                                        </tr>
                                                        <tr>
                                                            <th>3.</th>
                                                            <td>RI</td>
                                                            <td>£63,000.00</td>
                                                            <td>£5,250.00 </td>
                                                            <td>£1,211.54</td>
                                                        </tr>
                                                        <tr>
                                                            <th>4.</th>
                                                            <td>Service Manager</td>
                                                            <td>£55,000.00</td>
                                                            <td>£4,583.33</td>
                                                            <td>£1,057.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>5.</th>
                                                            <td>Ops Manager</td>
                                                            <td>£47,250.00</td>
                                                            <td>£3,937.50</td>
                                                            <td>£908.65</td>
                                                        </tr>
                                                        <tr>
                                                            <th>6.</th>
                                                            <td>Office Manager</td>
                                                            <td>£28,350.00</td>
                                                            <td>£2,362.50</td>
                                                            <td>£545.19</td>
                                                        </tr>
                                                        <tr>
                                                            <th>7.</th>
                                                            <td>Residential Service Manager</td>
                                                            <td>£53,550.00</td>
                                                            <td>£4,462.50</td>
                                                            <td>£1,029.81</td>
                                                        </tr>
                                                        <tr>
                                                            <th>8.</th>
                                                            <td>Behaviour Support Lead</td>
                                                            <td>£53,550.00</td>
                                                            <td>£4,462.50</td>
                                                            <td>£1,029.81</td>
                                                        </tr>
                                                        <tr>
                                                            <th>9.</th>
                                                            <td>Finance Officer</td>
                                                            <td>£31,500.00</td>
                                                            <td>£2,625.00</td>
                                                            <td>£605.77</td>
                                                        </tr>
                                                        <tr>
                                                            <th>10.</th>
                                                            <td>Supported Living Service Manager</td>
                                                            <td>£47,250.00</td>
                                                            <td>£3,937.50</td>
                                                            <td>£908.65</td>
                                                        </tr>
                                                        <tr>
                                                            <th>11.</th>
                                                            <td>Head of Finance</td>
                                                            <td>£42,000.00</td>
                                                            <td>£3,500.00</td>
                                                            <td>£807.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>12.</th>
                                                            <td>Finance Assistant</td>
                                                            <td>£24,150.00</td>
                                                            <td>£2,012.50</td>
                                                            <td>£464.42</td>
                                                        </tr>
                                                        <tr>
                                                            <th>13.</th>
                                                            <td>Admin Assistant</td>
                                                            <td>£24,150.00</td>
                                                            <td>£2,012.50</td>
                                                            <td>£464.42</td>
                                                        </tr>
                                                        <tr>
                                                            <th>14.</th>
                                                            <td>HR Manager</td>
                                                            <td>£42,000.00</td>
                                                            <td>£3,500.00</td>
                                                            <td>£807.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>15.</th>
                                                            <td>Clinical Physcologist</td>
                                                            <td>£55,000.00</td>
                                                            <td>£4,583.33</td>
                                                            <td>£1,057.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>16.</th>
                                                            <td>Maintenance</td>
                                                            <td>£39,900.00</td>
                                                            <td>£3,325.00</td>
                                                            <td>£767.31</td>
                                                        </tr>
                                                        <tr>
                                                            <th>17.</th>
                                                            <td>IT Assistant</td>
                                                            <td>£23,100.00</td>
                                                            <td>£1,925.00</td>
                                                            <td>£444.23</td>
                                                        </tr>
                                                        <tr>
                                                            <th>18.</th>
                                                            <td>IT Manager</td>
                                                            <td>£37,800.00</td>
                                                            <td>£3,150.00</td>
                                                            <td>£726.92</td>
                                                        </tr>
                                                        <tr>
                                                            <th>19.</th>
                                                            <td>Employer NI, Pension/tax</td>
                                                            <td>£171,788.40</td>
                                                            <td>£14,315.70</td>
                                                            <td>£3,303.62</td>
                                                        </tr>
                                                        <tr>
                                                            <th>20.</th>
                                                            <td>Marketing/Social Media/Advertising</td>
                                                            <td>£24,000.00</td>
                                                            <td>£2,000.00</td>
                                                            <td>£461.54</td>
                                                        </tr>
                                                        <tr>
                                                            <th>21.</th>
                                                            <td>Accountants</td>
                                                            <td>£20,000.00</td>
                                                            <td>£1,666.67</td>
                                                            <td>£384.62</td>
                                                        </tr>
                                                        <tr>
                                                            <th>22.</th>
                                                            <td>External HR</td>
                                                            <td>£6,500.00</td>
                                                            <td>£541.67</td>
                                                            <td>£125.00</td>
                                                        </tr>
                                                        <tr>
                                                            <th>23.</th>
                                                            <td>Legal/Professional/consultancy fees</td>
                                                            <td>£90,000.00</td>
                                                            <td>£7,500.00</td>
                                                            <td>£1,730.77</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>EL/PL Insurance & Private Medical</td>
                                                            <td>£65,000.00</td>
                                                            <td>£5,416.67</td>
                                                            <td>£1,250.00</td>
                                                        </tr>
                                                        <tr>
                                                            <th>25.</th>
                                                            <td>Vehicle Fleet Insurance</td>
                                                            <td>£42,000.00</td>
                                                            <td>£3,500.00</td>
                                                            <td>£807.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>26.</th>
                                                            <td>HP Vehicles</td>
                                                            <td>£28,000.00</td>
                                                            <td>£2,333.33</td>
                                                            <td>£538.46</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Health & Safety </td>
                                                            <td>£12,000.00</td>
                                                            <td>£1,000.00</td>
                                                            <td>£230.77</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Premises Maintenance</td>
                                                            <td>£80,000.00</td>
                                                            <td>£6,666.67</td>
                                                            <td>£1,538.46</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Maintenance van/fuel</td>
                                                            <td>£10,000.00</td>
                                                            <td>£833.33</td>
                                                            <td>£192.31</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Sundry business expenses</td>
                                                            <td>£12,000.00</td>
                                                            <td>£1,000.00</td>
                                                            <td>£230.77</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Recruitment and DBS expenses</td>
                                                            <td>£50,000.00</td>
                                                            <td>£4,166.67</td>
                                                            <td>£961.54</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Office Rent/Utilities/phones</td>
                                                            <td>£12,000.00</td>
                                                            <td>£1,000.00</td>
                                                            <td>£230.77</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Operational Workforce Training </td>
                                                            <td>£45,000.00</td>
                                                            <td>£3,750.00</td>
                                                            <td>£865.38</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Training materials</td>
                                                            <td>£10,000.00</td>
                                                            <td>£833.33</td>
                                                            <td>£192.31</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Mortgage Repayments</td>
                                                            <td>£120,000.00</td>
                                                            <td>£10,000.00</td>
                                                            <td>£2,307.69</td>
                                                        </tr>
                                                        <tr>
                                                            <th>24.</th>
                                                            <td>Computer & Maintenance costs</td>
                                                            <td>£30,000.00</td>
                                                            <td>£2,500.00</td>
                                                            <td>£576.92</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th>24.</th>
                                                            <th></th>
                                                            <th>£1,850,838.40</th>
                                                            <th>£154,236.53</th>
                                                            <th>£35,593.05</th>
                                                           
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                        </div>
                                        <div id="staffCosts" class="tab-pane">
                                            <div class="adv-table table-responsive">
                                                <table class="display table table-bordered table-striped" id="">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th></th>
                                                            <th>Hours</th>
                                                            <th>£ p/h</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>Hourly rate inc Emp NI/pen</th>
                                                            <th>Annual Leave Cover</th>
                                                            <th>Weekly Emp NI/Pen</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th>1.</th>
                                                            <td>Registered Manager </td>
                                                            <td>40</td>
                                                            <td>£22.11</td>
                                                            <td>£884.40</td>
                                                            <td>16.80%</td>
                                                            <td>£25.82</td>
                                                            <td></td>
                                                            <td>£148.58</td>
                                                        </tr>
                                                        <tr>
                                                            <th>1.</th>
                                                            <td>Registered Manager </td>
                                                            <td>40</td>
                                                            <td>£22.11</td>
                                                            <td>£884.40</td>
                                                            <td>16.80%</td>
                                                            <td>£25.82</td>
                                                            <td></td>
                                                            <td>£148.58</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Deputy </td>
                                                            <td>40</td>
                                                            <td>£18.80</td>
                                                            <td>£752.10</td>
                                                            <td>16.80%</td>
                                                            <td>£21.96</td>
                                                            <td>£94.60</td>
                                                            <td>£126.35</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Deputy </td>
                                                            <td>40</td>
                                                            <td>£16.68</td>
                                                            <td>£667.00</td>
                                                            <td>16.80%</td>
                                                            <td>£19.48</td>
                                                            <td>£83.90</td>
                                                            <td>£112.06</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Senior </td>
                                                            <td>40</td>
                                                            <td>£14.95</td>
                                                            <td>£598.00</td>
                                                            <td>16.80%</td>
                                                            <td>£17.46</td>
                                                            <td>£75.22</td>
                                                            <td>£100.46</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Senior  </td>
                                                            <td>40</td>
                                                            <td>£14.38</td>
                                                            <td>£575.00</td>
                                                            <td>16.80%</td>
                                                            <td>£16.79</td>
                                                            <td>£72.33</td>
                                                            <td>£96.60</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 1 </td>
                                                            <td>40</td>
                                                            <td>£14.09</td>
                                                            <td>£563.50</td>
                                                            <td>16.80%</td>
                                                            <td>£16.45</td>
                                                            <td>£70.88</td>
                                                            <td>£94.67</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 2 </td>
                                                            <td>40</td>
                                                            <td>£12.65</td>
                                                            <td>£506.00</td>
                                                            <td>16.80%</td>
                                                            <td>£14.78</td>
                                                            <td>£63.65</td>
                                                            <td>£85.01</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 3</td>
                                                            <td>40</td>
                                                            <td>£12.65</td>
                                                            <td>£506.00</td>
                                                            <td>16.80%</td>
                                                            <td>£14.78</td>
                                                            <td>£63.65</td>
                                                            <td>£85.01</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 4</td>
                                                            <td>49</td>
                                                            <td>£12.65</td>
                                                            <td>£619.85</td>
                                                            <td>16.80%</td>
                                                            <td>£14.78</td>
                                                            <td>£63.65</td>
                                                            <td>£104.13</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 5</td>
                                                            <td>40</td>
                                                            <td>£12.65</td>
                                                            <td>£506.00</td>
                                                            <td>16.80%</td>
                                                            <td>£14.78</td>
                                                            <td>£63.65</td>
                                                            <td>£85.01</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>RSW 6 </td>
                                                            <td>49</td>
                                                            <td>£12.65</td>
                                                            <td>£619.85</td>
                                                            <td>16.80%</td>
                                                            <td>£14.78</td>
                                                            <td>£63.65</td>
                                                            <td>£104.13</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Sleep in Support (2 per night) </td>
                                                            <td>112</td>
                                                            <td>£7.50</td>
                                                            <td>£840.00</td>
                                                            <td>16.80%</td>
                                                            <td>£8.76</td>
                                                            <td>£37.74</td>
                                                            <td>£141.12</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>A/L Cover </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>£752.90</td>
                                                            <td></td>
                                                            <td></td>
                                                            <th>£752.90</th>
                                                            <th>£1,283.13</th>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Employer NI, Pension </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>£1,283.13</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Car Fuel </td>
                                                            <td>£7,280.00</td>
                                                            <td>£606.67</td>
                                                            <td>£140.00</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Car Maintainence  </td>
                                                            <td>£950.00</td>
                                                            <td>£79.17</td>
                                                            <td>£18.27</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Rent </td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>TV/Internet/utilities </td>
                                                            <td>£15,000.00</td>
                                                            <td>£1,250.00</td>
                                                            <td>£288.46</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Reg 44 Monthly costs </td>
                                                            <td>£3,960.00</td>
                                                            <td>£330.00</td>
                                                            <td>£76.15</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>Ofsted Annual Fee </td>
                                                            <td>£3,120.00</td>
                                                            <td>£260.00</td>
                                                            <td>£60.00</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td> </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th>£9,636.76</th>
                                                            <td>p/w</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="directCosts" class="tab-pane">
                                            <div class="adv-table table-responsive">
                                                <table class="display table table-bordered table-striped" id="">
                                                
                                                    <tbody>
                                                        <tr>
                                                            <th>1.</th>
                                                            <td>Summer Holiday </td>
                                                            <td>£7,000.00</td>
                                                            <td>£583.33</td>
                                                            <td>£134.62</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td>YP H&H, food, clothing, actvity, pocket money, staff food, rewards etc</td>
                                                            <td>£48,000.00</td>
                                                            <td>£4,000.00</td>
                                                            <td>£700.00</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>3.</th>
                                                            <td></td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>4.</th>
                                                            <td></td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <td>£0.00</td>
                                                            <th>£834.62</th>
                                                            <th>p/w</th>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="adv-table table-responsive">
                                                <table class="display table table-bordered table-striped" id="">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Overhead allocation per home</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>£35,593.05</th>
                                                            <th>weekly oh</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th></th>
                                                            <td></td>
                                                            <td>Home</td>
                                                            <td>Vacancies</td>
                                                            <td>% time oh</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th>1.</th>
                                                            <td>Portion</td>
                                                            <td>Neptune</td>
                                                            <td>1</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>2.</th>
                                                            <td></td>
                                                            <td>Mercury</td>
                                                            <td>3</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>3.</th>
                                                            <td></td>
                                                            <td>Jupiter</td>
                                                            <td>3</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>4.</th>
                                                            <td></td>
                                                            <td>Poseidon</td>
                                                            <td>4</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>5.</th>
                                                            <td></td>
                                                            <td>Apollo</td>
                                                            <td>1</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>6.</th>
                                                            <td></td>
                                                            <td>Zeus</td>
                                                            <td>2</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>7.</th>
                                                            <td></td>
                                                            <td>Hebe</td>
                                                            <td>2</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>8.</th>
                                                            <td></td>
                                                            <td>Alexandra</td>
                                                            <td>4</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>9.</th>
                                                            <td></td>
                                                            <td>Iris</td>
                                                            <td>3</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>10.</th>
                                                            <td></td>
                                                            <td>Athena</td>
                                                            <td>2</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>11.</th>
                                                            <td></td>
                                                            <td>The Grove</td>
                                                            <td>3</td>
                                                            <td>7.50%</td>
                                                            <td>£2,669.48</td>
                                                            <td>Care Home</td>
                                                        </tr>
                                                        <tr>
                                                            <th>12.</th>
                                                            <td></td>
                                                            <td>Moss Lane</td>
                                                            <td>5</td>
                                                            <td>3.50%</td>
                                                            <td>£1,245.76</td>
                                                            <td>16+</td>
                                                        </tr>
                                                        <tr>
                                                            <th>13.</th>
                                                            <td></td>
                                                            <td>Anda house</td>
                                                            <td>4</td>
                                                            <td>3.50%</td>
                                                            <td>£1,245.76</td>
                                                            <td>16+</td>
                                                        </tr>
                                                        <tr>
                                                            <th>14.</th>
                                                            <td></td>
                                                            <td>Hawthorne</td>
                                                            <td>3</td>
                                                            <td>3.50%</td>
                                                            <td>£1,245.76</td>
                                                            <td>16+</td>
                                                        </tr>
                                                        <tr>
                                                            <th>15.</th>
                                                            <td></td>
                                                            <td>Garmoyle</td>
                                                            <td>5</td>
                                                            <td>3.50%</td>
                                                            <td>£1,245.76</td>
                                                            <td>16+</td>
                                                        </tr>
                                                        <tr>
                                                            <th>16.</th>
                                                            <td></td>
                                                            <td>Grey</td>
                                                            <td>5</td>
                                                            <td>3.50%</td>
                                                            <td>£1,245.76</td>
                                                            <td>16+</td>
                                                        </tr>
                                                        <tr>
                                                            <th></th>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>100.00%</td>
                                                            <th>£35,593.05</th>
                                                            <td>weekly overhead</td>
                                                        </tr>
                                                        <tr>
                                                            <th></th>
                                                            <td></td>
                                                            <td colspan="2">Total Overhead allocation inc direct costs</td>
                                                            <td>£13,760.71</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <th></th>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <th>£13,760.71</th>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!-- <section class="panel">
                              
                            
                                <div class="panel-body">
                                      <div class="tabsBtn">
                                            <div class="dt-buttons"> 
                                                <button class="dt-button buttons-csv buttons-html5" type="button"><span>Export</span></button> 
                                                <button class="dt-button buttons-collection buttons-colvis" type="button"><span>Column visibility</span></button> 
                                            </div>
                                        </div>
                                    <div class="adv-table table-responsive">
                                        <table class="display table table-bordered table-striped" id="staffWorker">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Surname</th>
                                                    <th>Forename</th>
                                                    <th>Address</th>
                                                    <th>Post Code</th>
                                                    <th>DOB</th>
                                                    <th>Acct Number </th>
                                                    <th>Sort Code </th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>

@endsection