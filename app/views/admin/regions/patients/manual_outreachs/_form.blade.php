{{ Form::model($manual_outreach, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('outreach_date', 'Outreach Date : ', array('class' => 'control-label')) }}
    {{ Form::text('outreach_date', Input::old('outreach_date'), array('class' => 'form-control datepicker')) }}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('outreach_code', 'Outreach Code : ', array('class' => 'control-label')) }}
    {{Form::select('outreach_code', $outreach_codes, Input::old('outreach_code'), array('class' => 'form-control form-control2'))}}
</div>

<div class="input-group input-group-lg sepH_a">
    {{ Form::label('outreach_notes', 'Outreach Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('outreach_notes', Input::old('outreach_notes'), array('class' => 'form-control')) }}
</div>


<div class="sepH_c text-right"></div>

