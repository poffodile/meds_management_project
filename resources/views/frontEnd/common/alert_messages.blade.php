@if(Session::has('success'))
    <div class="notification-box ">
        <div class="alert alert-success alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Success! </strong> {!! Session::get('success') !!}
        </div>
    </div>
@endif

@if(Session::has('error'))
    <div class="notification-box ">
        <div class="alert alert-warning alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Warning! </strong> {!! Session::get('error') !!}
        </div>
    </div>
@endif
@if(Session::has('login_error'))
    <div class="notification-box">
        <div class="alert alert-warning alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Warning! </strong> {!! Session::get('login_error') !!}
            <a id="yes_logout" href="{{url('/yes_logout')}}" class="btn btn-warning">Yes</a>
            <button id="no_logout" class="btn btn-danger">No</button>
        </div>
    </div>
@endif

<!-- ajax notifications -->

    <div class="notification-box ajax-alert-suc" style="display: none;">
        <div class="alert alert-success alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Success! </strong> <span class="msg"></span>
        </div>
    </div>

    <div class="notification-box ajax-alert-err" style="display: none; ">
        <div class="alert alert-warning alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Error! </strong> <span class="msg"></span>
        </div>
    </div>

    <!-- <div class="notification-box unauth-err" style="display: none;">
        <div class="alert alert-warning alert-dismissible m-0" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <strong>Warning! </strong> {{ UNAUTHORIZE_ERR }}
        </div>
    </div> -->

@if(!Session::has('login_error'))
<script>
    setTimeout(function(){$(".notification-box").fadeOut()}, 5000);
    //setTimeout(function(){$(".unauth-err").fadeOut()}, 5000);
</script>
@endif