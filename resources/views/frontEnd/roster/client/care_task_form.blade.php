@extends('frontEnd.layouts.master')
@section('title','Care Task')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-10">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex gap-3 align-items-center">
                            <div>
                                <button class="borderBtn"><i class='bx  bx-arrow-left f18'></i>Back</button>
                            </div>
                            <div>
                                <h1 class="mainTitlep mb-0">Add Care Task</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt20">
                    <div class="col-lg-12">
                        <form action="">
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Task Basics</h5>

                                <div class="carer-form">
                                    <div class="row mb-4 mt20">
                                        <div class="col-md-12">
                                            <label>Task Title *</label>
                                            <input type="text" class="form-control" placeholder="e.g. Monthly Supervision - John Smith">
                                        </div>
                                        <div class="col-lg-6  m-t-10">
                                            <label>Task Type *</label>
                                            <select class="form-control">
                                                <option>Assessment</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6  m-t-10">
                                            <label>Task Category *</label>
                                            <select class="form-control">
                                                <option>Personal Care</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-12 m-t-10">
                                            <label> Priority Level *</label>
                                            <select class="form-control">
                                                <option>High</option>
                                                <option>Medium</option>
                                                <option>Low</option>
                                            </select>
                                        </div>

                                    </div>


                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Client & Care Plan</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">
                                            <label>Client*</label>
                                            <select class="form-control">
                                                <option>Assessment</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-12  m-t-10">
                                            <label>Care Plan *</label>
                                            <select class="form-control">
                                                <option>Personal Care</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Scheduling
                                </h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-6 ">
                                            <label>Frequency *</label>
                                            <select class="form-control">
                                                <option>Assessment</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6">
                                            <label>Location</label>
                                            <select class="form-control">
                                                <option>Personal Care</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Scheduled Date *</label>
                                            <input type="date" class="form-control">
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Scheduled Time</label>
                                            <input type="time" class="form-control">
                                        </div>
                                        <div class="col-lg-4  m-t-10">
                                            <label>Duration (minutes)</label>
                                            <input type="number" class="form-control" placeholder="30">
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Assignment</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">

                                            <label>Assigned Carer</label>
                                            <select class="form-control">
                                                <option>Assessment</option>
                                                <option>Spot Check</option>
                                            </select>
                                            <small class="formIns">Only active carers are shown </small>


                                        </div>
                                        <div class="col-lg-12  m-t-10">

                                            <label>Link to Visit (Time-Specific)</label>
                                            <select class="form-control">
                                                <option>Personal Care</option>
                                                <option>Spot Check</option>
                                            </select>
                                            <small class="formIns">Task will appear for carer during this visit </small>

                                        </div>
                                        <div class="col-lg-12  m-t-10">

                                            <label>Link to Shift</label>
                                            <select class="form-control">
                                                <option>Personal Care</option>
                                                <option>Spot Check</option>
                                            </select>
                                            <small class="formIns">Task will appear for carer during this shift </small>

                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head"> <i class="careRiskIcon bx  bx-alert-triangle me-2"></i> Risk & Safeguarding</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">

                                        <div class="col-lg-12">
                                            <label>Risk Level</label>
                                            <select class="form-control">
                                                <option>Assessment</option>
                                                <option>Spot Check</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <div class="checkboxp">
                                                <input type="checkbox" id="safeguarding">
                                                <label for="safeguarding">
                                                    Safeguarding Risk (Will notify Registered Manager)
                                                </label>
                                            </div>

                                            <div class="checkboxp">
                                                <input type="checkbox" id="twoPerson">
                                                <label for="twoPerson">
                                                    Requires Two Person Support
                                                </label>
                                            </div>

                                            <div class="checkboxp mb-0">
                                                <input type="checkbox" id="ppeRequired">
                                                <label for="ppeRequired">
                                                    PPE Required
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-lg-12  m-t-10">
                                            <label>Risk Mitigation Notes</label>
                                            <textarea class="form-control" rows="3" cols="20" placeholder="Provide detailed instructions for completing this task..."></textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <div class="emergencyMain p-4 mb25">
                                <h5 class="h5Head">Task Details</h5>
                                <div class="carer-form">
                                    <div class="row mb-4 mt20">
                                        <div class="col-lg-12">
                                            <label>Task Description</label>
                                            <textarea class="form-control" rows="3" cols="20" placeholder="Provide detailed instructions for completing this task..."></textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-end gap-3">
                                    <button class="borderBtn">Cancel</button>
                                    <button class="bgBtn blackBtn"><i class='bx  bx-save f18'></i> Create Task</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

</main>