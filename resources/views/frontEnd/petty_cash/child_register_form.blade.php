@include('frontEnd.petty_cash.layout.header')

<section class="main_section_page_petty px-3">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-lg-4 col-xl-4 ">
                <div class="pageTitle">
                    <h3>Child Register Form</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="newJobForm green_border card mt-3">
                    <form action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 pe-0 col-form-label">Child Initials & Name</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Address</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Flat/Room</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">DOB</label>
                                    <div class="col-md-8">
                                        <input type="date" class="form-control editInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Weekly Rate</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Subs</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Extras</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Start Date</label>
                                    <div class="col-md-8">
                                        <input type="date" class="form-control editInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">End Date</label>
                                    <div class="col-md-8">
                                        <input type="date" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Local Authority</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Social Worker</label>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control editInput">
                                    </div>
                                </div>
                                <div class="row form-group mb-2">
                                    <label class="col-md-4 col-form-label">Email</label>
                                    <div class="col-md-8">
                                        <input type="email" class="form-control editInput">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 px-3">
                <div class="pageTitleBtn">
                    <a href="#" class="profileDrop button_green"><i class="fa-solid fa-floppy-disk"></i> Save</a>
                    <a href="#" class="profileDrop button_green"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <!-- <a href="#" class="profileDrop button_green"> Action <i class="fa-solid fa-arrow-down"></i></a> -->
                </div>
            </div>
        </div>
    </div>
</section>

@include('frontEnd.petty_cash.layout.footer')