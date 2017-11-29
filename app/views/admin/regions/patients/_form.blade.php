{{ Form::model($user, array('route' => $route, 'method' => isset($method) ? $method : 'POST', 'id' => 'edit_patients_form')) }}

<div class="input-group input-group-lg sepH_a @if ($errors->has('username')) has-error @endif">
    {{ Form::label('username', 'Patient ID : ', array('class' => 'control-label')) }}
    {{ Form::text('username', Input::old('username'), array('class' => 'form-control')) }}
    @if ($errors->has('username'))
        <span class="help-block">{{ $errors->first('username') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('medicaid_id')) has-error @endif">
    {{ Form::label('medicaid_id', 'Medicaid ID : ', array('class' => 'control-label')) }}
    {{ Form::text('medicaid_id', Input::old('medicaid_id'), array('class' => 'form-control')) }}
    @if ($errors->has('medicaid_id'))
        <span class="help-block">{{ $errors->first('medicaid_id') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('first_name')) has-error @endif">
    {{ Form::label('first_name', 'First Name : ', array('class' => 'control-label')) }}
    {{ Form::text('first_name', Input::old('first_name'), array('class' => 'form-control')) }}
    @if ($errors->has('first_name'))
        <span class="help-block">{{ $errors->first('first_name') }}</span>
    @endif
</div>

<!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
<input style="display:none" type="text" name="fakeusernameremembered"/>
<input style="display:none" type="password" name="fakepasswordremembered"/>

<div class="input-group input-group-lg sepH_a @if ($errors->has('last_name')) has-error @endif">
    {{ Form::label('last_name', 'Last Name : ', array('class' => 'control-label')) }}
    {{ Form::text('last_name', Input::old('last_name'), array('class' => 'form-control')) }}
    @if ($errors->has('last_name'))
        <span class="help-block">{{ $errors->first('last_name') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('middle_initial')) has-error @endif">
    {{ Form::label('middle_initial', 'Middle Name : ', array('class' => 'control-label')) }}
    {{ Form::text('middle_initial', Input::old('middle_initial'), array('class' => 'form-control')) }}
    @if ($errors->has('middle_initial'))
        <span class="help-block">{{ $errors->first('middle_initial') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('date_of_birth')) has-error @endif">
    {{ Form::label('date_of_birth', 'Date Of Birth : ', array('class' => 'control-label')) }}
    {{ Form::text('date_of_birth', Input::old('date_of_birth'), array('class' => 'form-control datepicker')) }}
    @if ($errors->has('date_of_birth'))
        <span class="help-block">{{ $errors->first('date_of_birth') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('sex', 'Sex : ', array('class' => 'control-label')) }}
    {{Form::select('sex', array('M'=>'M', 'F'=>'F'), Input::old('sex'), array('class' => 'form-control'))}}
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('address1')) has-error @endif">
    {{ Form::label('address1', 'Address 1 : ', array('class' => 'control-label')) }}
    {{ Form::text('address1', Input::old('address1'), array('class' => 'form-control')) }}
    @if ($errors->has('address1'))
        <span class="help-block">{{ $errors->first('address1') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('address2')) has-error @endif">
    {{ Form::label('address2', 'Address 2 : ', array('class' => 'control-label')) }}
    {{ Form::text('address2', Input::old('address2'), array('class' => 'form-control')) }}
    @if ($errors->has('address2'))
        <span class="help-block">{{ $errors->first('address2') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('city')) has-error @endif">
    {{ Form::label('city', 'City : ', array('class' => 'control-label')) }}
    {{ Form::text('city', Input::old('city'), array('class' => 'form-control')) }}
    @if ($errors->has('city'))
        <span class="help-block">{{ $errors->first('city') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('city')) has-error @endif">
    {{ Form::label('state', 'State : ', array('class' => 'control-label')) }}
    {{ Form::text('state', Input::old('state'), array('class' => 'form-control')) }}
    @if ($errors->has('state'))
        <span class="help-block">{{ $errors->first('state') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('zip')) has-error @endif">
    {{ Form::label('zip', 'Zip : ', array('class' => 'control-label')) }}
    {{ Form::text('zip', Input::old('zip'), array('class' => 'form-control')) }}
    @if ($errors->has('zip'))
        <span class="help-block">{{ $errors->first('zip') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('county')) has-error @endif">
    {{ Form::label('county', 'County : ', array('class' => 'control-label')) }}
    {{ Form::text('county', Input::old('county'), array('class' => 'form-control')) }}
    @if ($errors->has('county'))
        <span class="help-block">{{ $errors->first('county') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('phone1')) has-error @endif">
    {{ Form::label('phone1', 'Phone 1 : ', array('class' => 'control-label')) }}
    {{ Form::text('phone1', Input::old('phone1'), array('class' => 'form-control phone_mask')) }}
    @if ($errors->has('phone1'))
        <span class="help-block">{{ $errors->first('phone1') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('trac_phone')) has-error @endif">
    {{ Form::label('trac_phone', 'Trac Phone : ', array('class' => 'control-label')) }}
    {{ Form::text('trac_phone', Input::old('trac_phone'), array('class' => 'form-control phone_mask')) }}
    @if ($errors->has('trac_phone'))
        <span class="help-block">{{ $errors->first('trac_phone') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('email')) has-error @endif">
    {{ Form::label('email', 'Email : ', array('class' => 'control-label')) }}
    {{ Form::text('email', Input::old('email'), array('class' => 'form-control')) }}
    @if ($errors->has('email'))
        <span class="help-block">{{ $errors->first('email') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('mother_id')) has-error @endif">
    {{ Form::label('mother_id', 'Mother Id : ', array('class' => 'control-label')) }}
    {{ Form::text('mother_id', Input::old('mother_id'), array('class' => 'form-control')) }}
    @if ($errors->has('mother_id'))
        <span class="help-block">{{ $errors->first('mother_id') }}</span>
    @endif
</div>

<?php
if (!empty(Input::old('programs_id'))) {

    $programs = Array();
    foreach ($all_programs as $item) {
        if (array_search($item->id, Input::old('programs_id')) !== false) {
            array_push($programs, $item);
        }
    }
}
?>


@include('admin/modals/programs_modal')

<div class="sepH_c text-right"></div>
@if(!isset($create_page))
    <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary">Save</button>
        <a href="{{ URL::route('admin.regions.patients_roster', array($region->id)) }}" class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}
@endif
