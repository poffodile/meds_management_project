@include('frontEnd.salesAndFinance.jobs.layout.header')
<section class="main_section_page px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Search Leads</h3>
                </div>
            </div>
        </div>
        @include('frontEnd.salesAndFinance.lead.lead_buttons')
        <di class="row">
            <div class="col-lg-12">
                <div class="maimTable">
                    <div class="printExpt">
                        <div class="prntExpbtn">
                            <a href="#!">Print</a>
                            <a href="#!">Export</a>
                        </div>
                        <div class="searchFilter">
                            <a href="#!" onclick="hideShowDiv()" class="hidebtn">Show Search Filter</a>
                        </div>
                    </div>
                    <div class="searchJobForm" id="divTohide">
                        <form action="" class="p-4">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Lead Ref:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Lead Ref.">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Full Name:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Website:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="website">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Keywords:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Assigned User:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                @foreach($users as $user)
                                                    <option value="{{ $user->id}}">{{ $user->name }}</option>
                                                @endforeach
                                                
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Company Name:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Type Company name">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Address:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Type your address">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Created From:</label>
                                        <div class="col-md-3 pe-0">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                        <div class="col-md-1">
                                            <span class="editInput">to</span>
                                        </div>
                                        <div class="col-md-3 ps-0">
                                            <input type="date" class="form-control editInput" id="inputName" value="John Smith">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Status:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                @foreach($statuses as $status)
                                                <option value="{{ $status->id}}">{{ $status->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Email Address:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Email add">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">City:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="City">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Source:</label>
                                        <div class="col-md-8">
                                            <select class="form-control editInput selectOptions" id="inputJobType">
                                                @foreach($sources as $source)
                                                    <option value="{{ $source->id }}">{{ $source->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Telephone:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Telephone">
                                        </div>
                                    </div>

                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">County:</label>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="County">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Pincode">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label pe-0">Rejected Type:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Rejected Type">
                                        </div>
                                    </div>
                                    <div class="row form-group mb-2">
                                        <label class="col-md-4 col-form-label text-end">Postcode:</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control editInput" id="inputName" placeholder="Mobile">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="pageTitleBtn justify-content-center">
                                        <a href="#" class="profileDrop px-3">Search </a>
                                        <a href="#" class="profileDrop px-3">Clear</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="markendDelete">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="jobsection">
                                    <a href="#" class="profileDrop">Delete</a>
                                    <a href="#" class="profileDrop">Assign To User</a>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="pageTitleBtn p-0">
                                    <!-- <a href="#" class="profileDrop"> <i class="material-symbols-outlined"> settings </i></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="productDetailTable pt-3">
                        <table class="table mb-0" id="containerA">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Full Name </th>
                                    <th>Company Name</th>
                                    <th>Email Address</th>
                                    <th>Telephone</th>
                                    <th>Mobile </th>
                                    <th>Website </th>
                                    <th>Address</th>
                                    <th>City </th>
                                    <th>County </th>
                                    <th>Postcode</th>
                                    <th>Lead Ref.</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>QU-0001</td>
                                    <td>2024-12-06</td>
                                    <td>Webnmob</td>
                                    <td>B-36 Sector 59</td>
                                    <td>1</td>
                                    <td>£220.00</td>
                                    <td>£44.00</td>
                                    <td>£264.00</td>
                                    <td>£0.00</td>
                                    <td>£264.00</td>
                                    <td>£120.00</td>
                                    <td>
                                        <div class="d-inline-flex align-items-center ">
                                            <div class="nav-item dropdown">
                                                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">Action</a>
                                                <div class="dropdown-menu fade-up m-0" style="">
                                                    <a href="#" class="dropdown-item">Send SMS</a>
                                                    <a href="" class="dropdown-item">Preview</a>
                                                    <a href="" class="dropdown-item">Print</a>
                                                    <a href="" class="dropdown-item">Email</a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- End off main Table -->
            </div>
    </div>
    </div>
</section>

@include('frontEnd.salesAndFinance.jobs.layout.footer')