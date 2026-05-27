@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')

<!-- @include('rotaStaff.components.rota_master') -->

<style>
  .add_trans {
    transform: scale(1.2);
  }

  .main-shodw-box {
    width: 100%;
    background-color: #fff;
    display: inline-block;
    border-radius: 6px;
    box-shadow: rgb(187 184 184 / 25%) 0px 2px 8px;
    position: relative;
    height: auto !important;
  }

  .first-from,
  .secound-from,
  .three-from,
  .four-from {
    padding: 10px 6px 10px 39px !important;
  }

  .search_employees .search_icon {
    position: absolute;
    top: 11px !important;
    right: 13px;
    color: #a1a9b3;
    font-size: 18px;
  }

  .ntn-prt {
    display: flex;
    margin-top: 22px !important;
  }

  .ntn-prt h5 {
    margin-top: 3px;
  }

  .main-white-crical {
    margin: 15px 0;
  }

  .right-side-info .tab-content .fade {
    opacity: 1;
  }

  @media (max-width: 991px) {
    .right-side-info .tab-content {
      padding: 0px 20px 20px 20px;
      height: auto !important;
    }

    .left-sidebar-info {
      width: 60px !important;
    }

    .main-summery-box-strock {
      position: absolute;
      top: 29% !important;
    }

    .aside.right-side-info {
      margin-top: 200px;
    }
  }

  .from-equal a {
    width: 200px;
  }

  .tab-first-infos a {
    /* background: #e10078 !important; */
    border-radius: 5px !important;
  }

  #container {
    height: auto !important;
  }
</style>
<?php 
  function formatHours($decimalHours) {
    
    if ($decimalHours == 0 || $decimalHours == null) {
        return "0h 0min";
    }

    $hours = floor($decimalHours);
    $minutes = round(($decimalHours - $hours) * 60);

    return "{$hours}h {$minutes}min";
  }
?>
<section class="wrapper">
  <div class="row">
    <div class="col-lg-12">
      <div class="row">
        <!-- Top Bar Info Section Start Here -->

        <!-- Top Bar Info Section End Here -->
        <div class="col-lg-8 col-12">
          <div class="main-shodw-box">
            <div class="row">
              <div class="col-lg-6 over-title-top">
                <h2>Overview</h2>
              </div>
              <div class="col-lg-6 add-btn-top">
                <h4><a href="{{ url('/absence/type=1') }}">+ Add time off</a></h4>
              </div>
            </div>

            <div class="col-lg-12">
              <div class="flx-box">
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3 class="entry-title"> {{ \Carbon\Carbon::parse('Now -3 days')->format('D d M') }}</a>
                    </h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::parse('Now -3 days')->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_min_three }} </h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3>{{ \Carbon\Carbon::parse('Now -2 days')->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::parse('Now -2 days')->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_min_two }}</h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3>{{ \Carbon\Carbon::parse('Now -1 days')->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::parse('Now -1 days')->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_min_one }}</h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main add_trans" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3> {{ \Carbon\Carbon::now()->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::Now()->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_current }}</h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3>{{ \Carbon\Carbon::parse('Now +1 days')->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::parse('Now +1 days')->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_plus_one }}</h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3>{{ \Carbon\Carbon::parse('Now +2 days')->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('{{ \Carbon\Carbon::parse('Now +2 days')->format('Y-m-d') }}')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_plus_two }}</h2>
                    </div>
                  </a>
                </div>
                <div class="crical-part-info-main" onclick="moveUp(id)">
                  <div class="cricat-up-text">
                    <h3>{{ \Carbon\Carbon::parse('Now +3 days')->format('D d M') }}</h3>
                  </div>
                  <div class="crical-info-prt"> </div>
                  <a onclick="change_leaves_data('<?php echo \Carbon\Carbon::parse('Now +3 days')->format('D d M'); ?>')">
                    <div class="stock-crical-info">
                      <h2>{{ $total_leave_plus_three }}</h2>
                    </div>
                  </a>
                </div>
              </div>
              <div class="line-hr"></div>
            </div>
            <div class="my-summary-info">
              <div class="row">
                <div class="col-lg-12 col-md-12">
                  <h4 class="my-summry-part">Summary</h4>
                </div>
                <div class="col-lg-3">
                  <div class="main-white-crical"></div>
                  <div class="main-summery-box-strock">
                    <h2><span id="all_leave"></span></h2>
                  </div>
                </div>
                <div class="col-lg-9 col-md-9">
                  <form>
                    <div class="from-equal">
                      <div class="from-group">
                        <a href="{{ url('/calendar') }}" class="first-from"><span><span id="annual_leave"></span> Annual leave</span></a>
                      </div>
                      <div class="from-group">
                        <a href="{{ url('/calendar') }}" class="secound-from"><span><span id="other_leave"></span> Other leave</span></a>
                      </div>
                    </div>
                    <div class="from-equal">
                      <div class="from-group">
                        <a href="{{ url('/calendar') }}" class="three-from"><span><span id="sickness_leave"></span> Sickness leave</span></a>
                      </div>
                      <div class="from-group">
                        <a href="{{ url('/calendar') }}" class="four-from"><span><span id="lateness_leave"></span> lateness leave</span></a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Right Side Bar Info Start Here -->
        <div class="col-lg-4 col-12">
          <aside class="right-side-info">
            <div class="right-side-info-bar">
              <h2>My Summary</h2>
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Absence</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Overtime</button>
                </li>
              </ul>
            </div>
            <div class="tab-content" id="myTabContent">
              <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <div class="tab-first-infos">
                  <h3><a href="{{ url('absence/type=2') }}">Request time off</a></h3>
                </div>
                <div class="main-equal-side">
                  <!-- <div class="main-sidebar-crial">
                    <div class="crical-onw-white"></div>
                    <div class="crical-strok"> </div>
                  </div> -->
                  <div class="crical-part-info-main">
                  <div class="crical-info-prt"> </div>
                  <!-- <a> -->
                    <div class="stock-crical-info">
                      <h2>{{ $totalSickLateLeave }}</h2>
                    </div>
                  <!-- </a> -->
                </div>
                  <div class="crical-content-bar">
                    <h4><strong><?php echo formatHours($renaming_hour);?></strong> remaining</h4>
                    <h4><strong><?php echo formatHours($allowance_hour);?></strong> allowance</h4>
                  </div>
                </div>
                <p class="next m-t-20">Next up - No absences coming up</p>
                <div class="btn-info-sep">
                  <p>You've also taken</p>
                  <div class="ntn-prt">
                    <a href="#">{{ $sickness }} Lateness</a>
                    <a href="#">{{ $lateness }} Sickness</a>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade p-1" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="tab-first-infos">
                  <h3><a href="{{ url('absence/type=2') }}">Request time off</a></h3>
                </div>
                <!-- <div class="main-equal-side">
                  <div class="main-sidebar-crial">
                    <div class="crical-onw-white"></div>
                    <div class="crical-strok"> </div>
                  </div>
                  <div class="crical-content-bar">
                    <h4><strong>-5 hrs 0 mins</strong> remaining</h4>
                    <h4><strong>0 hrs 0 mins</strong> allowance</h4>
                  </div>
                </div> -->
                <?php 
                  $explode_hours=explode('.',$over_time);
                ?>
                <p class="next m-t-40"><i class="fa fa-clock" aria-hidden="true"></i> <strong>Time off in lieu</strong>  <?php if(!empty($explode_hours)){ echo $explode_hours[0];}else{ echo 0;}?>h <?php if(!empty($explode_hours)){ echo $explode_hours[1] ?? 0;}else{ echo 0;}?>m available to take</p>
                <p class="next m-t-10"><i class="fa fa-money" aria-hidden="true"></i> <strong>Payable</strong> 0h 0m pending payment</p>
                <div class="btn-info-sep">
                  <h3>Claims</h3>
                  <p>You have 0 overtime claims pending</p>
                  <div class="ntn-prt">
                    <a href="{{url('/overtime?key=').base64_encode(Auth::user()->id)}}">Overtime history</a>
                    <a href="{{url('/my-profile/'.Auth::user()->id.'/time-sheet')}}">Log overtime</a>
                  </div>
                </div>
              </div>

            </div>
          </aside>
        </div>
        <!-- Right Side Bar Info end Here -->
        <!-- Card information start here -->
        <div class="row my-3">
          <div class="col-md-4 my-2">
            <div class="card">
              <div class="detail-area turbotalent">
                <div>
                  <h2 class="heading">Turbotalent</h2>
                </div>
                <div>
                  <p class="sub-heading">navigator</p>
                </div>
              </div>
              <div class="detail">
                <div class="headline">
                  <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</p>
                </div>
                <div class="more-headline">
                  <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium amet sint deserunt.
                    Non mollitia labore explicabo! <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span></p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 my-2">
            <div class="card">
              <!-- <a href="{{ url('/payroll') }}"> -->
              <div class="payroll">
                <div>
                  <h2 class="heading">Payroll</h2>
                </div>
                <div>
                  <p class="sub-heading">navigator</p>
                </div>
              </div>
              <!-- </a> -->
              <div class="detail">
                <div class="headline">
                  <p>Manage payroll to get employee payments right and maintain compliance with HMRC
                    legislation.</p>
                </div>
                <div class="more-headline">
                  <p>Stores all your employee salaries and wages in one place, alongside tax codes and
                    National Insurance numbers.   <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span>
                    <!--You can also run regular payroll reports, giving you
                    information to enter into your payroll system or to send to your accountant. -->
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 my-2">
            <div class="card">
              <!-- <a href="{{ url('/recruitment') }}" style="text-decoration: none;"> -->
                <div class="turbotalent">
                  <div>
                    <h2 class="heading">Recruitment</h2>
                  </div>
                  <div>
                    <p class="sub-heading">navigator</p>
                  </div>
                </div>
              <!-- </a> -->
              <div class="detail">
                <div class="headline">
                  <p>Manage job vacancies.</p>
                </div>
                <div class="more-headline">
                  <p>Add details of job applicants, store key documents securely, and track applicant's stages
                    in the recruitment process. <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span></p>
                </div>
              </div>
            </div>
            <!-- </div>
              <div class="col-md-4 my-2">
                <div class="card">
                  <div class="turbotalent">
                    <div>
                      <h2 class="heading">Turbotalent</h2>
                    </div>
                    <div>
                      <p class="sub-heading">navigator</p>
                    </div>
                  </div>
                  <div class="detail">
                    <div class="headline">
                      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</p>
                    </div>
                    <div class="more-headline">
                      <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium amet sint deserunt. Non mollitia labore explicabo! <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span></p>
                    </div>
                  </div>
                </div>
              </div> -->
            <!-- <div class="col-md-4 my-2">
                <div class="card">
                  <div class="detail-area">
                    <div>
                      <h2 class="heading">payroll</h2>
                    </div>
                    <div>
                      <p class="sub-heading">navigator</p>
                    </div>
                  </div>
                  <div class="detail">
                    <div class="headline">
                      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</p>
                    </div>
                    <div class="more-headline">
                      <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium amet sint deserunt. Non mollitia labore explicabo! <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span></p>
                    </div>
                  </div>
                </div>
              </div> -->
            <!-- <div class="col-md-4 my-2">
                <div class="card">
                  <div class="backtowork">
                    <div>
                      <h2 class="heading">backtowork</h2>
                    </div>
                    <div>
                      <p class="sub-heading">navigator</p>
                    </div>
                  </div>
                  <div class="detail">
                    <div class="headline">
                      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit.</p>
                    </div>
                    <div class="more-headline">
                      <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Praesentium amet sint deserunt. Non mollitia labore explicabo! <span class="see-more-card"><a href="#" style="text-decoration: none;">See More</a></span></p>
                    </div>
                  </div>
                </div>
              </div> -->
          </div>
          <!-- Card information end here -->
        </div>
      </div>
    </div>
  </div>
</section>
@include('rotaStaff.components.footer')

<script>
  function change_leaves_data(date) {
    var token = "<?= csrf_token() ?>";
    var user_id="<?php echo $user_id;?>"
    $.ajax({
      url: "{{ url('/get_leave_record_for_1_week') }}",
      type: "post",
      dataType: 'json',
      data: {
        date: date,
        user_id:user_id,
        _token: token
      },
      success: function(result) {
        console.log(result);
        document.getElementById('all_leave').innerHTML = "";
        document.getElementById('other_leave').innerHTML = result.other;
        document.getElementById('annual_leave').innerHTML = result.annual;
        document.getElementById('sickness_leave').innerHTML = result.sickness;
        document.getElementById('lateness_leave').innerHTML = result.lateness;
        document.getElementById('all_leave').innerHTML = parseInt(result.lateness) + parseInt(result
          .other) + parseInt(result.sickness) + parseInt(result.annual);

      }
    });
  }
  $(document).ready(function() {

    var token = "<?= csrf_token() ?>";
    var date = moment().format('YYYY-MM-DD');
    var user_id="<?php echo $user_id;?>"
    $.ajax({
      url: "{{ url('/get_leave_record_for_1_week') }}",
      type: "post",
      dataType: 'json',
      data: {
        date: date,
        user_id:user_id,
        _token: token
      },
      success: function(result) {

        console.log(result);
        document.getElementById('other_leave').innerHTML = result.other;
        document.getElementById('annual_leave').innerHTML = result.annual;
        document.getElementById('sickness_leave').innerHTML = result.sickness;
        document.getElementById('lateness_leave').innerHTML = result.lateness;
        document.getElementById('all_leave').innerHTML = parseInt(result.lateness) + parseInt(
          result.other) + parseInt(result.sickness) + parseInt(result.annual);
      }
    });
  });

  const allTimingAreas = document.querySelectorAll('.crical-part-info-main');
  allTimingAreas.forEach((element, index) => {
    element.setAttribute("id", `crical-part-info-main-${index}`)
  });
  const selectedElement = document.querySelector('.add_trans').id.split('-')[4];
  var styleElem = document.head.appendChild(document.createElement("style"));
  styleElem.innerHTML = `.my-summary-info:before {left: calc(2.4% + 14.4%*${selectedElement});}`;
  const moveUp = (id) => {
    const getIdNumber = id.split("-")
    const selectedElement = document.querySelector('.add_trans').id.split('-')[4];
    const mainElement = document.getElementById(id);
    allTimingAreas.forEach((element, index) => {
      if (element.id == mainElement.id) {
        mainElement.classList.add('add_trans');
      } else if (element.id != mainElement.id) {
        element.classList.remove('add_trans');
      }
    });
    if (getIdNumber[4] > selectedElement) {
      var styleElem = document.head.appendChild(document.createElement("style"));
      styleElem.innerHTML = `.my-summary-info:before {left: calc(2.4% + 14.4%*${getIdNumber[4]});}`;
    } else {
      var styleElem = document.head.appendChild(document.createElement("style"));
      styleElem.innerHTML = `.my-summary-info:before {left: calc(2.4% + 14.4%*${getIdNumber[4]});}`;
    }
  }
</script>