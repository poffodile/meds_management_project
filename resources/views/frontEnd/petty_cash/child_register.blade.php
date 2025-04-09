@include('frontEnd.petty_cash.layout.header')

<section class="main_section_page_petty px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Child Register</h3>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="jobsection">
                    <a href="{{url('petty-cash/child-register-add')}}" class="profileDrop button_green"> Add <i class="fa-solid fa-plus"></i></a>
                    <a href="#" class="profileDrop button_green">Residential</a>
                    <a href="#" class="profileDrop button_green">Supported Accomodation</a>
                    <a href="#" class="profileDrop button_green">Leavers</a>
                </div>
            </div>
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="cash_table">
                    <div class="table-responsive productDetailTable pt-3">
                        <table id="" class="table mb-0" cellspacing="0" width="100%">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Child Initials & Name</th>
                                    <th>Address</th>
                                    <th>Flat/Room</th>
                                    <th>DOB</th>
                                    <th>Weekly Rate</th>
                                    <th>Subs</th>
                                    <th>Extras</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Local Authority</th>
                                    <th>Social Worker</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Hazel Fleming </td>
                                    <td>Mercury House</td>
                                    <td></td>
                                    <td></td>
                                    <td>£4602.78</td>
                                    <td></td>
                                    <td></td>
                                    <td>03/24/2025</td>
                                    <td>05/24/2025</td>
                                    <td>Halton</td>
                                    <td>Tendai Marizani</td>
                                    <td>Alex.Reece@halton.gov.uk <br> 07826877525</td>
                                    <td><a href=""><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Hazel Fleming </td>
                                    <td>Mercury House</td>
                                    <td></td>
                                    <td></td>
                                    <td>£4602.78</td>
                                    <td></td>
                                    <td></td>
                                    <td>03/24/2025</td>
                                    <td>05/24/2025</td>
                                    <td>Halton</td>
                                    <td>Tendai Marizani</td>
                                    <td>Alex.Reece@halton.gov.uk <br> 07826877525</td>
                                    <td><a href=""><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Hazel Fleming </td>
                                    <td>Mercury House</td>
                                    <td></td>
                                    <td></td>
                                    <td>£4602.78</td>
                                    <td></td>
                                    <td></td>
                                    <td>03/24/2025</td>
                                    <td>05/24/2025</td>
                                    <td>Halton</td>
                                    <td>Tendai Marizani</td>
                                    <td>Alex.Reece@halton.gov.uk <br> 07826877525</td>
                                    <td><a href=""><i class="fa-solid fa-eye"></i></a></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="14">Total</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('frontEnd.petty_cash.layout.footer')