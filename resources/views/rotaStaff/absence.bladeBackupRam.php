@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')
@include('rotaStaff.pyrll_user_prfile_tmplte')
@include('rotaStaff.pyrll_stylsheet')
<style>
.hr_gap{
  height: 15px;
}
.leave_yr{
  float: right;
  margin-right: -390px;
  width: 50%;
}
.fil_ab_n_leave_yr_prnt{
  width: 100%;
}
.uppr_stats{
  width: 100%;
  height: 200px;
  text-align: center;
  font-size: 23px;
  font-weight: bold;
  display: block;
  float: left;
}
.uppr_stat_all_itms{
  margin-top: 30px;
  font-size: 17px;
  font-weight: 500;
}
.uppr_stat_itm{
  width: 33%;
  float: left;
  text-align: center;
  display: block;
}
.crrnt_n_ftre{
  font-size: 16px;
  float: left;
  margin-top: 30px;
}
.sick_icn{
  width: 175px;
  height: 90px;
  background-color: grey;
}
.crrnt_n_ftre_section_1{
  float: left;
  width: 100%;
  margin-top: 8px;
  height: 150px;
}
.crrnt_n_ftre_section_2{
  float: left;
  width: 100%;
  height: 150px;
}
.crrnt_n_ftre_section_3{
  float: left;
  width: 100%;
  height: 150px;
}
.txt_n_bxes_prnt{

}
.txt_n_bxes_chld_1{
  float: left;
  width: 20%;
  font-size: 18px;
  font-weight: 500;
}
.txt_n_bxes_chld_2{
  float: left;
  width: 20%;
  font-size: 18px;
  font-weight: 500;
}
.txt_n_bxes_chld_3{
  float: left;
  width: 20%;
  font-size: 18px;
  font-weight: 500;
}
.crrnt_n_ftre_icns{
  float: right;
}

.accordion {
  background-color: #eee;
  color: black;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  text-align: left;
  border: none;
  outline: none;
  transition: 0.4s;
}

/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
.active, .accordion:hover {
  background-color: #ccc;
}

/* Style the accordion panel. Note: hidden by default */
.panel {
  padding: 0 18px;
  background-color: white;
  display: none;
  overflow: hidden;
}
.inbetween_block{
  width: 100%;
  float: left;
  height: 20px;
}
</style>

<hr>


<div class = "fil_ab_n_leave_yr_prnt">
  <div class = "flt_lft_wdth_100_var" style = "--wdth_prcntge: 50%">
    Filter absences<br>
    <select>
     <option value ="All absences">All absences</option>
     <option value ="Annual leave">Annual leave</option>
     <option value ="Lateness">Lateness</option>
     <option value ="Sickness">Sickness</option>
     <option value ="Furlough">Furlough</option>
     <option value ="Other">Other</option>
    </select>
  </div>
  <div class = "leave_yr">
    Leave year<br>
    <select>
     <option value ="2016">01 Jan 2016 - 31 Dec 2016</option>
     <option value ="2017">01 Jan 2017 - 31 Dec 2017</option>
     <option value ="2018">01 Jan 2018 - 31 Dec 2018</option>
     <option value ="2019">01 Jan 2019 - 31 Dec 2019</option>
     <option value ="2020">01 Jan 2020 - 31 Dec 2020</option>
     <option value ="2021">01 Jan 2021 - 31 Dec 2021</option>
     <option value ="2022">01 Jan 2022 - 31 Dec 2022</option>
     <option value ="2023">01 Jan 2023 - 31 Dec 2023</option>
     <option value ="2024">01 Jan 2024 - 31 Dec 2024</option>
    </select>
  </div>
</div>
<div class = "uppr_stats">
  All absences
  <div class = "uppr_stat_all_itms">
    <div class = "uppr_stat_itm">
      Annual leave to take
      <div class = "occurences">
        161 hrs 0 mins out of 224 hrs 0 mins
      </div>
      <div class = "rptble_btn_1" style = "--bckgrnd_clr: rgb(61,176,241); --wdth_prcntge: 168px;  --margin_: 8px auto">
        <a href="{{ url('/add_annual_leave') }}">
        Add annual leave</a>
      </div>
    </div>
    <div class = "uppr_stat_itm"">
      Sickness
      <div class = "occurences">
        2
        <span style = "color: grey">Occurences</span>
      </div>
      <div class = "rptble_btn_1" style = "--bckgrnd_clr: rgb(61,176,241); --wdth_prcntge: 168px;  --margin_: 8px auto">
        <a href = "{{ url('/add_sickness') }}">
        Add</a>
      </div>
    </div>
    <div class = "uppr_stat_itm">
      Lateness
      <div class = "occurences">
        0
        <span style = "color: grey">Occurences</span>
      </div>
      <div class = "rptble_btn_1" style = "--bckgrnd_clr: rgb(61,176,241); --wdth_prcntge: 168px;  --margin_: 8px auto">
        <a href = "{{ url('/add_lateness') }}">
        Add</a>
      </div>
    </div>
  </div>
</div>
</div>


<button class="accordion">Current & future <span style = "font-size: 14">(click to expand)</span></button>
<div class="panel">
  <p>
    <div class = "crrnt_n_ftre">
    Current & future
    </div> 
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Sickness </a></span><br>
        <span style = "font-size: 16px">Fri 24 Feb 2023 (Ongoing)</span><br>
        <span style = "font-size: 14px">hjjkj</span><br>
        <span style = "font-size: 12px">Logged by michael carter</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_2">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Sickness</a></span><br>
        <span style = "font-size: 15px">Sat 25 Feb 2023 (Ongoing)</span><br>
        <span style = "font-size: 12px">Logged by michael carter</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
  </p>
</div>


<div class = "inbetween_block">
</div>


<button class="accordion">Absence history</button>
<div class="panel">
  <p>
    <div class = "crrnt_n_ftre">
    Absence history
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Mandatory leave </a></span><br>
        <span style = "font-size: 16px">Fri 24 Feb 2023 (Ongoing)</span><br>
        <span style = "font-size: 14px">hjjkj</span><br>
        <span style = "font-size: 12px">Logged by michael carter</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
  </p>
</div>


<div class = "inbetween_block">
</div>


<button class="accordion">Public holidays</button>
<div class="panel">
  <p>
    <div class = "crrnt_n_ftre">
    Public Holidays
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
        </div>
      </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
    <div class = "crrnt_n_ftre_section_1">
      <div class = "txt_n_bxes_chld_1">
        <div class = "sick_icn">
      </div>
    </div>
      <div class = "txt_n_bxes_chld_2">
        <span style = "font-size: 16px; color: rgb(61,176,241)">
        <a href = "{{ url('/update_sickness') }}">
        Public Holiday </a></span><br>
        <span style = "font-size: 16px">Mon 02 Jan 2023 (7 hrs)</span><br>
        <span style = "font-size: 12px">New Year's Day (substitute day)</span>
      </div>
      <div class = "crrnt_n_ftre_icns">
        <i class = "fa fa-file"></i>
        <i class = "fa fa-file"></i>
      </div>
    </div>
  </p>
</div>

<script>
  var acc = document.getElementsByClassName("accordion");
  var i;

  for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function() {
      /* Toggle between adding and removing the "active" class,
      to highlight the button that controls the panel */
      this.classList.toggle("active");

      /* Toggle between hiding and showing the active panel */
      var panel = this.nextElementSibling;
      if (panel.style.display === "block") {
        panel.style.display = "none";
      } else {
        panel.style.display = "block";
      }
    });
  }
</script>