@extends('backEnd.layouts.master')
@section('title',' User Rights')
@section('content')

<!--main content start-->
<section id="main-content" class="">
    <section class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">user access rights</header>
                    <div class="panel-body">
                        <div class="row">
                            <form role="form" action="{{ url('admin/users/access-right/update') }}" method="post">
                                <div class="col-sm-12">
                                    @include('backEnd/common/access_rights_list')
                                </div>
                                <div class="col-sm-12">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="user_id" value="{{ $user_id }}">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                    <a href="{{ url('admin/users') }}">
                                        <button type="button" class="btn btn-default">Cancel</button>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('.acc_heading_chkbox').click(function() {

            if ($(this).is(':checked')) {
                $(this).parent().siblings('ul').find('input').prop('checked', true);
            } else {
                $(this).parent().siblings('ul').find('input').prop('checked', false);
            }
        });
    });
</script>
@endsection