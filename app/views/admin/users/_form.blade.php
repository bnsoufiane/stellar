<?php $current_user_id = Sentry::getUser()->id; ?>

{{ Form::model($user, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

<div class="input-group input-group-lg sepH_a @if ($errors->has('first_name')) has-error @endif">
    {{ Form::label('first_name', 'First Name : ', array('class' => 'control-label')) }}
    {{ Form::text('first_name', Input::old('first_name'), array('class' => 'form-control')) }}
    @if ($errors->has('first_name'))
        <span class="help-block">{{ $errors->first('first_name') }}</span>
    @endif
</div>
<div class="input-group input-group-lg sepH_a @if ($errors->has('last_name')) has-error @endif">
    {{ Form::label('last_name', 'Last Name : ', array('class' => 'control-label')) }}
    {{ Form::text('last_name', Input::old('last_name'), array('class' => 'form-control')) }}
    @if ($errors->has('last_name'))
        <span class="help-block">{{ $errors->first('last_name') }}</span>
    @endif
</div>

<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
<input style="display:none" type="text" name="fakeusernameremembered"/>
<input style="display:none" type="password" name="fakepasswordremembered"/>

<div class="input-group input-group-lg sepH_a @if ($errors->has('username')) has-error @endif">
    {{ Form::label('username', 'Username :', array('class' => 'control-label')) }}
    {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
    @if ($errors->has('username'))
        <span class="help-block">{{ $errors->first('username') }}</span>
    @endif
</div>
<div class="input-group input-group-lg sepH_a @if ($errors->has('password')) has-error @endif">
    {{ Form::label('password', 'Password :', array('class' => 'control-label')) }}
    {{ Form::password('password', array('class' => 'form-control')) }}
    @if ($errors->has('password'))
        <span class="help-block">{{ $errors->first('password') }}</span>
    @endif
</div>

@if($current_user_id !==$user->id)
    <div class="input-group input-group-lg sepH_a">
        {{ Form::label('group', 'Group : ', array('class' => 'control-label')) }}
        {{Form::select('group', $groups,  (isset($user->getGroups()[0]) && isset($user->getGroups()[0]->id))?$user->getGroups()[0]->id:Input::old('group'), array('class' => 'form-control'))}}
    </div>

    <div class="input-group input-group-lg sepH_a insurance_companies_list @if ($errors->has('insurance_company')) has-error @endif">
        {{ Form::label('insurance_company', 'Insurance Company : ', array('class' => 'control-label')) }}
        {{Form::select('insurance_company', $insurance_companies,  (isset($user->insurance_company()->first()->id))?$user->insurance_company()->first()->id:Input::old('insurance_company'), array('class' => 'form-control'))}}
        @if ($errors->has('insurance_company'))
            <span class="help-block">{{ $errors->first('insurance_company') }}</span>
        @endif
    </div>
@endif

<div class="sepH_c text-right"></div>
<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
    <a href="{{ URL::route('admin.users.index') }}" class="btn btn-lg btn-default ">Cancel</a>
</div>
{{ Form::close() }}

