@if(Session::has('success'))
    <div class="alert alert-success fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
        <strong>Success!</strong> {!! Session::get('success') !!}
    </div>
@endif

@if(Session::has('error'))
    <div class="alert ryt alert-danger fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
        <strong>Warning!</strong> {!! Session::get('error') !!}
    </div>
@endif

@if (count($errors) > 0)
    <div class="alert ryt alert-danger fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(Session::has('undo'))
    <div class="alert1 alert-success fade in">
        <button data-dismiss="alert" class="close close-sm" type="button">
            <i class="fa fa-times"></i>
        </button>
         {!! Session::get('undo') !!}
    </div>
@endif

<script>
    // setTimeout(function(){$(".alert").fadeOut()}, 5000);
    setTimeout(function(){$(".alert-success").fadeOut()}, 5000);
    setTimeout(function(){$(".alert-danger").fadeOut()}, 5000);
</script>