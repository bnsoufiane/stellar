{{ Form::model($doctor, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

@if(isset($region_id))
    <input type="hidden" name="region_id" value="{{$region_id}}"/>
@endif
@if(isset($practice_group_id))
    <input type="hidden" name="practice_group_id" value="{{$practice_group_id}}"/>
@endif

<div class="input-group input-group-lg sepH_a @if ($errors->has('pcp_id')) has-error @endif">
    {{ Form::label('pcp_id', 'PCP ID : ', array('class' => 'control-label')) }}
    {{ Form::text('pcp_id', Input::old('pcp_id'), array('class' => 'form-control')) }}
    @if ($errors->has('pcp_id'))
        <span class="help-block">{{ $errors->first('pcp_id') }}</span>
    @endif
</div>

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

<div class="sepH_c text-right"></div>

