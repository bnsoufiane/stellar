<div class="login_register_form">
    <div class="form_wrapper animated-short" id="login_form">
        <h3 class="sepH_c"><span>Login</span></h3>
        {{ Form::open(array('url' => URL::route('sign-in'))) }}

        @if ($error)
            <div class="alert alert-danger alert-white rounded">
                <button data-dismiss="alert" class="close" type="button">×</button>
                <div class="icon"><i class="icon-remove-sign"></i></div>
                <strong>Error!</strong> {{$error}}
            </div>
        @endif

        <div class="input-group input-group-lg sepH_a">
            <span class="input-group-addon"><span class="icon_profile"></span></span>
            {{ Form::text('username', Input::old('username'), array('placeholder' => 'Username', 'class' => 'form-control')) }}
        </div>
        <div class="input-group input-group-lg">
            <span class="input-group-addon"><span class="icon_key_alt"></span></span>
            <input type="password" name="password" class="form-control" placeholder="Password">
        </div>
        <div class="sepH_c text-right"></div>
        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary btn-block">Log in</button>
        </div>
        {{ Form::close() }}
    </div>
</div>
