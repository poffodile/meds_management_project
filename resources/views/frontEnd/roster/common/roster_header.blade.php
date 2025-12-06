 <div class="row">
                <div class="col-md-12">
                    <div class="wrappermenu">
                        <nav>
                            <input type="checkbox" id="show-search">
                            <input type="checkbox" id="show-menu">
                            <label for="show-menu" class="menu-icon"><i class="fas fa-bars"></i></label>
                            <div class="content">
                                <ul class="links">
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Dashboard</a></li>
                                    <li><a href="{{ url('/roster/manage-dashboard') }}"> <i class="fa fa-tachometer"></i> Manager Dashboard</a></li>
                                    <li><a href="{{ url('/roster/schedule-shift') }}"> <i class="fa fa-tachometer"></i> Schedule</a></li>
                                    <li><a href="{{ url('/roster/carer-availability') }}"> <i class="fa fa-tachometer"></i> Carer Availability</a></li>
                                    <li><a href="{{ url('/roster/messaging-center') }}"> <i class="fa fa-tachometer"></i> Messaging Center</a></li>
                                    <li><a href="{{ url('/roster/staff-task') }}"> <i class="fa fa-tachometer"></i> Staff Tasks</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Care Documents</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Reports</a></li>
                                    <li><a href="{{ url('/roster/leave-request') }}"> <i class="fa fa-tachometer"></i> Leave Requests</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>