@extends('frontEnd.layouts.master')
@include('rotaStaff.components.header')

<style>
  a.nav-link {
    background-color: #fff;
    text-decoration: none;
    color: #1f88b5;
    transition: all 0.3s ease 0s;
    -webkit-transition: all 0.3s ease 0s;
    border-radius: 0;
    -webkit-border-radius: 0;
  }

  .nav-tabs .nav-link:focus,
  .nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6;
    isolation: isolate;
    background-color: #ffffff;
    border-radius: 0;
  }

  section#container {
    height: auto;
  }
  button.fc-button {
    top: 0px;
}
.all_leave_with_color {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}
th .fc-scrollgrid-sync-inner {
    background: #f6f6f6;
    padding: 10px;
}
.tab-content {
    background: #fff;
    padding: 20px;
}
</style>




  <section id="main-content">
<!-- @extends('rotaStaff.components.rota_master') -->
@section('content')
 <section id="main-content">
    <div class="wrapper">
      <div class="col-lg-12">
        <div class="row">
          <div class="col-lg-12">
              <header class="panel-heading tab-bg-dark-navy-blue ">
                    <ul class="nav nav-tabs calender-Tab">
                        <li class="active">
                            <a data-toggle="tab" href="#home">Calendar</a>
                        </li>
                        <li class="">
                            <a data-toggle="tab" href="#about">Pending requests</a>
                        </li>
                       
                    </ul>
                </header>
                <div class="">
                    <div class="tab-content">
                        <div id="home" class="tab-pane active calendar">
                            <div class="all_leave_with_color d-flex align-items-center mt-3">
                              <span class="annual leave"></span>Annual leave
                              <span class="sick leave"></span>Sickness
                              <span class="late leave"></span>Lateness
                              <span class="toil leave"></span>TOIL
                              <span class="other_leave leave"></span>Other absence
                            </div>
                            <!-- <div id='calendar'></div> -->
                            <div id="calendar" class="has-toolbar"></div>
                        </div>
                        <div id="about" class="tab-pane content-info-title">
                          
                         <select id="leave_order">
                            <option value="1">Date raised (Newest first)</option>
                            <option value="2">Date raised (Oldest first)</option>
                          </select>
                          <h2>Pending requests (<span id="leave_count"></span>)</h2>
                          <div class="row" id="blank_id"></div>
                        </div>
                      
                    </div>
                </div>
          
            <!-- <ul class="nav nav-tabs calender-Tab nav-fill" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <a class="nav-link active" id="calendar-tab" data-toggle="tab" href="#calendarTb" role="tab" aria-controls="calendarTb" aria-selected="true">Calendar</a>
              </li>
              <li class="nav-item" role="presentation">
                <a class="nav-link" id="pendingRequests-tab" data-toggle="tab" href="#pendingRequests" role="tab" aria-controls="pendingRequests" aria-selected="false">Pending requests</a>
              </li>
            </ul>

            <div class="tab-content tabs-info-content-space-bar" id="myTabContent">
              <div class="tab-pane  show active calendar" id="calendarTb" role="tabpanel" aria-labelledby="calendar-tab">
                <div class="all_leave_with_color d-flex align-items-center my-3">
                  <span class="annual leave"></span>Annual leave
                  <span class="sick leave"></span>Sickness
                  <span class="late leave"></span>Lateness
                  <span class="toil leave"></span>TOIL
                  <span class="other_leave leave"></span>Other absence
                </div>
                <div id='calendar'></div>
              </div>

              <div class="tab-pane  content-info-title" id="pendingRequests" role="tabpanel" aria-labelledby="pendingRequests-tab">
                <select id="leave_order">
                  <option value="1">Date raised (Newest first)</option>
                  <option value="2">Date raised (Oldest first)</option>
                </select>
                <h2>Pending requests (<span id="leave_count"></span>)</h2>
                <div class="row" id="blank_id"></div>
              </div>
            </div> -->

          </div>
          <!-- Col Md 9 End -->

        </div>
      </div>
    </div>
  </section>

  <div class="modal fade unapproved_modal_content" id="exampleModalUnapproved" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 50vw;">
      <div class="modal-content content-modal">
        <div class="modal-header modal-head">
          <input type="hidden" id="edit_leave">
          <h5 class="modal-title" id="exampleModalLabel"><span>Are you sure you want to approve this leave?</span></h5>
          <button type="button" class=" close-btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="modal-body">
          <div class="col-md-12">
            <p>This will show in the calendar with perticular date. Once approved, the notification will be sent to <span id="leave_person"></span>.</p>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="close-btn" data-bs-dismiss="modal">Close</button>
          <button type="button" class="approve-btn" id="approve_leave">Approve</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script>
    function getInitials(fullName) {
      const words = fullName.split(' ');
      if (words.length >= 1) {
        const firstNameInitial = words[0][0];

        if (words.length >= 2) {
          const lastNameInitial = words[words.length - 1][0];
          return `${firstNameInitial}${lastNameInitial || ''}`;
        } else {
          return firstNameInitial;
        }
      } else {
        return 'Invalid input';
      }
    }

    $(document).ready(function() {
      var token = "<?= csrf_token() ?>";
      $('#approve_leave').on('click', function() {
        var id = $('#edit_leave').val();
        $.ajax({
          url: "{{ url('/approve_leave') }}",
          type: "post",
          dataType: 'json',
          data: {
            id: id,
            _token: token
          },
          success: function(result) {
            console.log(result);
            location.reload();
          }
        });
      });


      var type = document.getElementById('leave_order').value;
      $.ajax({
        url: "{{ url('/pending-request-data') }}",
        type: "post",
        dataType: 'json',
        data: {
          type: type,
          _token: token
        },
        success: function(result) {
          console.log(result);
          if ((result.pending_leave).length == 0) {
            document.getElementById('blank_id').innerHTML = "<p>Everything is up to date, have a cuppa!</p>";
          } else {
            document.getElementById('leave_count').innerHTML = '';
            document.getElementById('leave_count').innerHTML = (result.pending_leave).length;
            for (i = 0; i < (result.pending_leave).length; i++) {
              console.log(result.pending_leave[i]['name']);
              var name = result.pending_leave[i]['name'];
              var fs = getInitials(result.pending_leave[i]['name']);
              console.log(fs);
              var node = `
              <div class="col-md-6 my-2">
              <div class="pending_rquest">
                <div class="parent_div">
                  <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota">
                      <div class="short_name">${fs}</div>
                    </div>
                    <div class="col-md-10 p-2">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <a href="./timeline-view.html" class="rota_shift_employee_name">
                            <h5>${name}</h5>
                          </a>
                        </div>
                        <div>
                          <button type="button" style="background-color:${result.pending_leave[i]['color']};" onclick="openunapprovemodal(${ result.pending_leave[i]['staffleave_id']}, '${name}');" class="unapproved_btn my-2">Unapproved</button>
                        </div>
                        <!-- Button trigger modal -->
                      </div>
                      <div class="d-flex flex-column">
                        <div class="start_end_date"><strong>Start:&nbsp;</strong>${ moment(result.pending_leave[i]['start_date']).format('ddd, Do MMM') }<strong>&nbsp;&nbsp;&nbsp;End:&nbsp;</strong>${ moment(result.pending_leave[i]['end_date']).format('ddd, Do MMM')  }</div>
                        <div class="pe-3">${result.pending_leave[i]["notes"]}</div> 
                        <div class="order-1">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            `;
              var theDiv = document.getElementById("blank_id");
              theDiv.innerHTML += node;
            }
          }
        }
      });


      $('#leave_order').on('change', function() {
        var type = this.value;
        $.ajax({
          url: "{{ url('/pending-request-data') }}",
          type: "post",
          dataType: 'json',
          data: {
            type: type,
            _token: token
          },
          success: function(result) {
            console.log(result);

            if ((result.pending_leave).length == 0) {
              document.getElementById('blank_id').innerHTML = "<p>Everything is up to date, have a cuppa!</p>";
            } else {
              document.getElementById('leave_count').innerHTML = '';
              document.getElementById('blank_id').innerHTML = '';
              document.getElementById('leave_count').innerHTML = (result.pending_leave).length;
              for (i = 0; i < (result.pending_leave).length; i++) {
                console.log(result.pending_leave[i]['name'][0]);
                var name = result.pending_leave[i]['name'];
                var myArray = name.split(' ');
                let first = myArray[0]?.charAt(0) || '';
                let second = myArray[1]?.charAt(0) || '';
                let fs = first.concat(second);
                var node = `<div class="col-md-6 my-2">
              <div class="pending_rquest">
                <div class="parent_div">
                  <div class="d-flex justify-content-between">
                    <div class="d-flex flex-column align-items-center justify-content-center col-md-2 date_of_shift_rota">
                      <div class="short_name">${fs}</div>
                    </div>
                    <div class="col-md-10 p-2">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <a href="./timeline-view.html" class="rota_shift_employee_name">
                            <h5>${name}</h5>
                          </a>
                        </div>
                        <div>
                          <button type="button" style="background-color:${result.pending_leave[i]['color']};" onclick="openunapprovemodal(${ result.pending_leave[i]['staffleave_id']}, '${name}');" class="unapproved_btn my-2">Unapproved</button>
                        </div>
                        <!-- Button trigger modal -->
                      </div>
                      <div class="d-flex flex-column">
                        <div class="start_end_date"><strong>Start:&nbsp;</strong>${ moment(result.pending_leave[i]['start_date']).format('ddd, Do MMM') }<strong>&nbsp;&nbsp;&nbsp;End:&nbsp;</strong>${ moment(result.pending_leave[i]['end_date']).format('ddd, Do MMM')  }</div>
                        <div class="pe-3">${result.pending_leave[i]["notes"]}</div> 
                        <div class="order-1">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
                var theDiv = document.getElementById("blank_id");
                theDiv.innerHTML += node;
              }
            }
          }
        });

      });
    });

    function openunapprovemodal(leave_id, name) {
      $('#edit_leave').val(leave_id);
      document.getElementById('leave_person').innerHTML = name;
      $('#exampleModalUnapproved').modal('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
      var initialLocaleCode = 'en';
      var localeSelectorEl = document.getElementById('locale-selector');
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },

        initialDate: moment().format('YYYY-MM-DD'),
        locale: initialLocaleCode,
        buttonIcons: false, // show the prev/next text
        weekNumbers: true,
        navLinks: true, // can click day/week names to navigate views
        editable: true,
        dayMaxEvents: true, // allow "more" link when too many events
        events: <?= $calander ?>
      });

      calendar.render();
      // build the locale selector's options

      calendar.getAvailableLocaleCodes().forEach(function(localeCode) {
        var optionEl = document.createElement('option');
        optionEl.value = localeCode;
        optionEl.selected = localeCode == initialLocaleCode;
        optionEl.innerText = localeCode;
        localeSelectorEl.appendChild(optionEl);
      });


      // when the selected option changes, dynamically change the calendar option
      localeSelectorEl.addEventListener('change', function() {
        if (this.value) {
          calendar.setOption('locale', this.value);
        }
      });
    });
  </script>