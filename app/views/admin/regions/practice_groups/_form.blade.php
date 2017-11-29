{{ Form::model($practice_group, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

@if(isset($region_id))
    <input type="hidden" name="region_id" value="{{$region_id}}"/>
@endif

<div class="input-group input-group-lg sepH_a @if ($errors->has('group_id')) has-error @endif">
    {{ Form::label('group_id', 'Group ID : ', array('class' => 'control-label')) }}
    {{ Form::text('group_id', Input::old('group_id'), array('class' => 'form-control')) }}
    @if ($errors->has('group_id'))
        <span class="help-block">{{ $errors->first('group_id') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('name')) has-error @endif">
    {{ Form::label('name', 'Name : ', array('class' => 'control-label')) }}
    {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    @if ($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('specialty')) has-error @endif">
    {{ Form::label('specialty', 'Specialty : ', array('class' => 'control-label')) }}
    {{ Form::text('specialty', Input::old('specialty'), array('class' => 'form-control')) }}
    @if ($errors->has('specialty'))
        <span class="help-block">{{ $errors->first('specialty') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('phone')) has-error @endif">
    {{ Form::label('phone', 'Phone : ', array('class' => 'control-label')) }}
    {{ Form::text('phone', Input::old('phone'), array('class' => 'form-control')) }}
    @if ($errors->has('phone'))
        <span class="help-block">{{ $errors->first('phone') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('fax')) has-error @endif">
    {{ Form::label('fax', 'Fax : ', array('class' => 'control-label')) }}
    {{ Form::text('fax', Input::old('fax'), array('class' => 'form-control')) }}
    @if ($errors->has('fax'))
        <span class="help-block">{{ $errors->first('fax') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('address')) has-error @endif">
    {{ Form::label('address', 'Address : ', array('class' => 'control-label')) }}
    {{ Form::text('address', Input::old('address'), array('class' => 'form-control')) }}
    @if ($errors->has('address'))
        <span class="help-block">{{ $errors->first('address') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('city')) has-error @endif">
    {{ Form::label('city', 'City : ', array('class' => 'control-label')) }}
    {{ Form::text('city', Input::old('city'), array('class' => 'form-control')) }}
    @if ($errors->has('city'))
        <span class="help-block">{{ $errors->first('city') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('state')) has-error @endif">
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

<div class="sepH_c text-right"></div>

