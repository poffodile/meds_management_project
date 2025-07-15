@extends('backEnd.layouts.master')
@section('title',' Add New')
@section('content')

<!--main content start-->
<section id="main-content" class="">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        @if(isset($purchase_expenses))
                        Edit
                        @else
                        Add
                        @endif
                        Purchase Expenses Type 
                    </header>
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" action="{{ url('admin/sales-finance/purchase/save-purchase-expenses-type') }}" role="form">
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Title <span class="radStar">*</span></label>
                                    <div class="col-lg-10">
                                        <input type="hidden" name="purchase_expense_id" value="{{ isset($purchase_expenses) ? $purchase_expenses->id : '' }}">
                                        <input type="text" name="title" class="form-control" id="" value="{{ isset($purchase_expenses) ? $purchase_expenses->title : '' }}" placeholder="Title">
                                        @error('title')
                                            <div class="radStar">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Status</label>
                                    <div class="col-lg-10">
                                        <select class="form-control" name="status">
                                            <option value="1" {{ isset($purchase_expenses) && $purchase_expenses->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ isset($purchase_expenses) && $purchase_expenses->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <button type="submit" class="btn btn-default">Cencel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

            </div>
        </div>
        <!-- page end-->
    </section>
</section>

@endsection