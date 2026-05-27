@include('rotaStaff.components.header')
@include('rotaStaff.pyrll_stylsheet')
<style>
input[type='checkbox'] {
  display: none;
}
.lbl-toggle {
  display: block;
  font-weight: 550;
  text-align: center;
  color: #A77B0E;
  background: #FAE042;
  cursor: pointer;
  border-radius: 7px;
}
.lbl-toggle:hover {
  color: #7C5A0B;
}
.lbl-toggle::before {
  content: ' ';
  display: inline-block;
  border-top: 5px solid transparent;
  border-bottom: 5px solid transparent;
  border-left: 5px solid currentColor;
  vertical-align: middle;
  margin-right: .7rem;
  transform: translateY(-2px);
  transition: transform .2s ease-out;
}
.collapsible-content .content-inner {
  background: rgba(250, 224, 66, .2);
  padding: .5rem 1rem;
}
.collapsible-content {
  max-height: 0px;
  overflow: hidden;
}
.toggle:checked + .lbl-toggle + .collapsible-content {
  max-height: 100vh;
}
.toggle:checked + .lbl-toggle::before {
  transform: rotate(90deg) translateX(-3px);
}
.flt_lft_wdth_100_var{
  
}
.tab_ttle{
  margin: 15px 0px 20px;
}
.pmnt_n_emplyee_prnt{
  width: 100%;
  margin-bottom: 20px;
}
.hw_mny_slct{
  width: 50%;
}
</style>

<ul class="nav nav-tabs rotas" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <a href = "{{ url('/payroll') }}"><button class="nav-link active" id="activerotas-tab" data-bs-toggle="tab"
    data-bs-target="#activerotas" type="button" role="tab" aria-controls="activerotas"
    aria-selected="true">Payroll Console</button></a>
  </li>
  <li class="nav-item" role="presentation">
    <a href = "{{ url('/information_checker') }}"><button class="nav-link" id="oldrotas-tab" data-bs-toggle="tab" data-bs-target="#oldrotas"
    type="button" role="tab" aria-controls="oldrotas" aria-selected="false">Information Checker</button></a>
  </li>
  <li class="nav-item" role="presentation">
    <a href = "{{ url('/overtime') }}"><button class="nav-link" id="createrota-tab" data-bs-toggle="tab" data-bs-target="#createrota"
    type="button" role="tab" aria-controls="createrota" aria-selected="false">Overtime </button></a>
  </li>
  <li class="nav-item" role="presentation">
    <a href = "{{ url('/payroll_glossary') }}"><button class="nav-link" id="oldrotas-tab" data-bs-toggle="tab" data-bs-target="#oldrotas"
    type="button" role="tab" aria-controls="oldrotas" aria-selected="false">Payroll glossary</button></a>
  </li>
</ul>

<div class = "tab_ttle">
  <h3> Payable Overtime </h3>
  Set payment dates for overtime claims
</div>
<div class="wrap-collabsible">
  <input id="collapsible" class="toggle" type="checkbox">
  <label for="collapsible" class="lbl-toggle">More Info</label>
  <div class="collapsible-content">
    <div class="content-inner">
      <p>
        QUnit is by calling one of the object that are embedded in JavaScript, and faster JavaScript program could also used with
        its elegant, well documented, and functional programming using JS, HTML pages Modernizr is a popular browsers without
        plug-ins. Test-Driven Development.
      </p>
    </div>
  </div>
</div>

<div class = "pmnt_n_emplyee_prnt">
  <div class = "flt_lft_wdth_100_var" style = "--wdth_prcntge: 25%">
    Payment type: <br>
    <select>
      <option>Payment date not set </option>
      <option>Payment date set </option>
      <option>All </option>
    </select>
  </div>
  <div class = "emplyees">
    Employees <br>
    <input type = "search">
  </div>
</div>
<div class = "pmnt_n_emplyee_prnt">
  <div class = "flt_lft_wdth_100_var" style = "--wdth_prcntge: 75%">
    Date approved:<br>
    <select>
      <option>All of time </option>
      <option>This month </option>
      <option>Last month </option>
      <option>Custom dates </option>
    </select>
  </div>
  <div class = "emplyees">
    Date worked:<br>
    <select>
      <option>All of time </option>
      <option>This month </option>
      <option>Last month </option>
      <option>Custom dates </option>
    </select>
  </div>
</div>

<div class = "hw_mny_slct">
  0 selected
</div>
<div class = "dte">
  <input type = "date">
</div>


No records
	
Name

Duration

Pay Rate

Approved by

Payment date


<table>
  <tr>
    <th>Name</th>
    <th>Duration</th>
    <th>Pay Rate</th>
    <th>End date</th>
    <th>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</th>
  </tr>
  <tr>
      <td><div class="evry_othr_rprt">Annual leave summary</div></td>
      <td><div class="evry_othr_rprt">25 Sep 2023, 14:35</div></td>
      <td><div class="evry_othr_rprt">18 Oct 2023</div></td>
      <td><div class="evry_othr_rprt">18 Oct 2023</div></td>
      <td><div class="evry_othr_rprt"><a href = "resources\views\rotaStaff\download_image.png" download="proposed_file_name"><div class="dwnld_btn">Download</div></a></div></td>
    </div>
  </tr>
  <tr>
    <td>Absence</td>
    <td>25 Sep 2023, 14:35</td>
    <td>01 Sep 2023</td>
    <td>30 Sep 2023</td>
    <td><a href = "resources\views\rotaStaff\download_image.png" download="proposed_file_name"><div class="dwnld_btn">Download</div></a></td>
  </tr>