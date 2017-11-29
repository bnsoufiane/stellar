{{ Form::model($program, array('route' => $route, 'method' => isset($method) ? $method : 'POST', 'class' => 'post_partum_form')) }}

<?php

if (count($actual_visits) > 0) {
    $actual_visit = $actual_visits[0];
} else {
    $actual_visit = new stdClass();
    $actual_visit->scheduled_visit_date = "0000-00-00 00:00:00";
    $actual_visit->actual_visit_date = "0000-00-00 00:00:00";
    $actual_visit->doctor_id = "";
    $actual_visit->incentive_type = "";
    $actual_visit->incentive_value = "";
    $actual_visit->gift_card_serial = "";
    $actual_visit->incentive_date_sent = "0000-00-00 00:00:00";
    $actual_visit->visit_notes = "";
}

$program->scheduled_visit_date = \Helpers::format_date_display($actual_visit->scheduled_visit_date);
$program->actual_visit_date = \Helpers::format_date_display($actual_visit->actual_visit_date);
$program->doctor_id = $actual_visit->doctor_id;
$program->incentive_type = $actual_visit->incentive_type;
$program->incentive_value = $actual_visit->incentive_value;
$program->gift_card_serial = $actual_visit->gift_card_serial;
$program->incentive_date_sent = \Helpers::format_date_display($actual_visit->incentive_date_sent);
$program->visit_notes = $actual_visit->visit_notes;

?>

<input type="hidden" name="patient_id" value="{{$patient->id}}"/>
<input type="hidden" name="program_id" value="{{$program->id}}"/>
<input type="hidden" name="program_instance_id" value="{{$program->program_instance_id}}"/>

<div class="input-group input-group-lg sepH_a @if ($errors->has('delivery_date')) has-error @endif">
    {{ Form::label('delivery_date', 'Delivery Date : ', array('class' => 'control-label')) }}
    {{ Form::text('delivery_date', Input::old('delivery_date'), array('class' => 'form-control datepicker disallow_future_dates')) }}
    @if ($errors->has('delivery_date'))
        <span class="help-block">{{ $errors->first('delivery_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('birth_weight')) has-error @endif">
    {{ Form::label('birth_weight', 'Birth Weight : ', array('class' => 'control-label')) }}
    {{ Form::text('birth_weight', Input::old('birth_weight'), array('class' => 'form-control')) }}
    @if ($errors->has('birth_weight'))
        <span class="help-block">{{ $errors->first('birth_weight') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('gestational_age')) has-error @endif">
    {{ Form::label('gestational_age', 'Gestational Age : ', array('class' => 'control-label')) }}
    {{ Form::text('gestational_age', Input::old('gestational_age'), array('class' => 'form-control')) }}
    @if ($errors->has('gestational_age'))
        <span class="help-block">{{ $errors->first('gestational_age') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('pediatrician_id')) has-error @endif">
    {{ Form::label('pediatrician_id', 'Pediatrician Id : ', array('class' => 'control-label')) }}
    {{ Form::text('pediatrician_id', Input::old('pediatrician_id'), array('class' => 'form-control')) }}
    @if ($errors->has('pediatrician_id'))
        <span class="help-block">{{ $errors->first('pediatrician_id') }}</span>
    @endif
</div>


<div class="input-group input-group-lg sepH_a @if ($errors->has('patient_notes')) has-error @endif">
    {{ Form::label('patient_notes', 'Patient Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('patient_notes', Input::old('patient_notes'), array('class' => 'form-control')) }}
    @if ($errors->has('patient_notes'))
        <span class="help-block">{{ $errors->first('patient_notes') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('postpartum_start')) has-error @endif">
    {{ Form::label('postpartum_start', 'Post Partum Start : ', array('class' => 'control-label')) }}
    {{ Form::text('postpartum_start', Input::old('postpartum_start'), array('class' => 'form-control datepicker')) }}
    @if ($errors->has('postpartum_start'))
        <span class="help-block">{{ $errors->first('postpartum_start') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('postpartum_end')) has-error @endif">
    {{ Form::label('postpartum_end', 'Post Partum End : ', array('class' => 'control-label')) }}
    {{ Form::text('postpartum_end', Input::old('postpartum_end'), array('class' => 'form-control datepicker')) }}
    @if ($errors->has('postpartum_end'))
        <span class="help-block">{{ $errors->first('postpartum_end') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('actual_visit_date')) has-error @endif">
    {{ Form::label('actual_visit_date', 'Actual visit date : ', array('class' => 'control-label')) }}
    {{ Form::text('actual_visit_date', Input::old('actual_visit_date'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_field')) }}
    @if ($errors->has('actual_visit_date'))
        <span class="help-block">{{ $errors->first('actual_visit_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('doctor_id')) has-error @endif">
    {{ Form::label('doctor_id', 'Doctor Id : ', array('class' => 'control-label')) }}
    {{ Form::text('doctor_id', Input::old('doctor_id'), array('class' => 'form-control actual_visit_date_related_field', $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('doctor_id'))
        <span class="help-block">{{ $errors->first('doctor_id') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_type')) has-error @endif">
    {{ Form::label('incentive_type', 'Incentive Type : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_type', Input::old('incentive_type'), array('class' => 'form-control actual_visit_date_related_field',  $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('incentive_type'))
        <span class="help-block">{{ $errors->first('incentive_type') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_value')) has-error @endif">
    {{ Form::label('incentive_value', 'Incentive Value : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_value', Input::old('incentive_value'), array('class' => 'form-control actual_visit_date_related_field',  $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('incentive_value'))
        <span class="help-block">{{ $errors->first('incentive_value') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('gift_card_serial')) has-error @endif">
    {{ Form::label('gift_card_serial', 'Gift Card Serial : ', array('class' => 'control-label')) }}
    {{ Form::text('gift_card_serial', Input::old('gift_card_serial'), array('class' => 'form-control actual_visit_date_related_field',  $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('gift_card_serial'))
        <span class="help-block">{{ $errors->first('gift_card_serial') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_date_sent')) has-error @endif">
    {{ Form::label('incentive_date_sent', 'Incentive Date Sent : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_date_sent', Input::old('incentive_date_sent'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_related_field',  $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('incentive_date_sent'))
        <span class="help-block">{{ $errors->first('incentive_date_sent') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('visit_notes')) has-error @endif">
    {{ Form::label('visit_notes', 'Visit Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('visit_notes', Input::old('visit_notes'), array('class' => 'form-control actual_visit_date_related_field',  $program->actual_visit_date?'':'disabled')) }}
    @if ($errors->has('visit_notes'))
        <span class="help-block">{{ $errors->first('visit_notes') }}</span>
    @endif
</div>

<div class="manual_outreach_rows">

    <div class="manual_outreach_row">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('manual_outreach[]')) has-error @endif">
            {{ Form::checkbox('manual_outreach[]', 'true', null, array('class' => 'manual_outreach_field')) }}
            {{ Form::label('manual_outreach[]', 'Manual Outreach : ', array('class' => 'control-label control-label2')) }}
            @if ($errors->has('manual_outreach[]'))
                <span class="help-block">{{ $errors->first('manual_outreach[]') }}</span>
            @endif
        </div>

        <div class="input-group input-group-lg sepH_a @if ($errors->has('manual_outreach_date[]')) has-error @endif">
            {{ Form::label('manual_outreach_date[]', 'Outreach Date : ', array('class' => 'control-label')) }}
            {{ Form::text('manual_outreach_date[]', Input::old('manual_outreach_date[]'), array('class' => 'form-control datepicker disallow_future_dates manual_outreach_related_field', 'disabled')) }}
            @if ($errors->has('manual_outreach_date[]'))
                <span class="help-block">{{ $errors->first('manual_outreach_date[]') }}</span>
            @endif
        </div>

        <div class="input-group input-group-lg sepH_a @if ($errors->has('manual_outreach_code[]')) has-error @endif">
            {{ Form::label('manual_outreach_code[]', 'Outreach Code : ', array('class' => 'control-label')) }}
            {{Form::select('manual_outreach_code[]', $outreach_codes, Input::old('manual_outreach_code[]'), array('class' => 'form-control form-control2 manual_outreach_related_field', 'disabled'))}}
            @if ($errors->has('manual_outreach_code[]'))
                <span class="help-block">{{ $errors->first('manual_outreach_code[]') }}</span>
            @endif
        </div>

        <div class="input-group input-group-lg sepH_a @if ($errors->has('manual_outreach_notes[]')) has-error @endif">
            {{ Form::label('manual_outreach_notes[]', 'Outreach Notes : ', array('class' => 'control-label')) }}
            {{ Form::text('manual_outreach_notes[]', Input::old('manual_outreach_notes[]'), array('class' => 'form-control manual_outreach_related_field', 'disabled')) }}
            @if ($errors->has('manual_outreach_notes[]'))
                <span class="help-block">{{ $errors->first('manual_outreach_notes[]') }}</span>
            @endif
        </div>
    </div>

</div>

<div class="input-group input-group-lg sepH_a">
    <a class="btn btn-sm btn-primary add_new_outreach">Add New Outreach</a>
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('scheduled_visit_date')) has-error @endif">
    {{ Form::label('scheduled_visit_date', 'Scheduled visit date : ', array('class' => 'control-label')) }}
    {{ Form::text('scheduled_visit_date', Input::old('scheduled_visit_date'), array('class' => 'form-control datepicker')) }}
    @if ($errors->has('scheduled_visit_date'))
        <span class="help-block">{{ $errors->first('scheduled_visit_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('manually_added')) has-error @endif">
    {{ Form::checkbox('manually_added', 'true') }}
    {{ Form::label('manually_added', 'E-Script : ', array('class' => 'control-label control-label2')) }}
    @if ($errors->has('manually_added'))
        <span class="help-block">{{ $errors->first('manually_added') }}</span>
    @endif
</div>

<div class="sepH_c text-right"></div>
<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
</div>
{{ Form::close() }}
