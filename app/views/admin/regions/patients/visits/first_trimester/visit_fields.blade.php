<div class="input-group input-group-lg sepH_a @if ($errors->has('sign_up')) has-error @endif">
    {{ Form::label('sign_up', 'Sign Up : ', array('class' => 'control-label')) }}
    {{ Form::text('sign_up', Input::old('delivery_date'), array('class' => 'form-control datepicker disallow_future_dates')) }}
    @if ($errors->has('sign_up'))
        <span class="help-block">{{ $errors->first('sign_up') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('actual_visit_date')) has-error @endif">
    {{ Form::label('actual_visit_date', 'Actual visit date : ', array('class' => 'control-label')) }}
    {{ Form::text('actual_visit_date', Input::old('actual_visit_date'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_field')) }}
    @if ($errors->has('actual_visit_date'))
        <span class="help-block">{{ $errors->first('actual_visit_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('visit_date_verified_by')) has-error @endif">
    {{ Form::label('visit_date_verified_by', 'Visit Date Verified By : ', array('class' => 'control-label')) }}
    {{Form::select('visit_date_verified_by', $visit_date_verified, !empty($actual_visit->visit_date_verified_by)?$actual_visit->visit_date_verified_by:null, array('class' => 'form-control form-control2'))}}
    @if ($errors->has('visit_date_verified_by'))
        <span class="help-block">{{ $errors->first('visit_date_verified_by') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('julian_date')) has-error @endif">
    {{ Form::label('julian_date', 'Julian date : ', array('class' => 'control-label')) }}
    {{ Form::text('julian_date', Input::old('julian_date'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_field')) }}
    @if ($errors->has('julian_date'))
        <span class="help-block">{{ $errors->first('julian_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('doctor_id')) has-error @endif">
    {{ Form::label('doctor_id', 'Doctor Id : ', array('class' => 'control-label')) }}
    {{ Form::text('doctor_id', Input::old('doctor_id'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('doctor_id'))
        <span class="help-block">{{ $errors->first('doctor_id') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_type')) has-error @endif">
    {{ Form::label('incentive_type', 'Incentive Type : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_type', Input::old('incentive_type'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('incentive_type'))
        <span class="help-block">{{ $errors->first('incentive_type') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_value')) has-error @endif">
    {{ Form::label('incentive_value', 'Incentive Value : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_value', Input::old('incentive_value'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('incentive_value'))
        <span class="help-block">{{ $errors->first('incentive_value') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('gift_card_serial')) has-error @endif">
    {{ Form::label('gift_card_serial', 'Gift Card Serial : ', array('class' => 'control-label')) }}
    {{ Form::text('gift_card_serial', Input::old('gift_card_serial'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('gift_card_serial'))
        <span class="help-block">{{ $errors->first('gift_card_serial') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_date_sent')) has-error @endif">
    {{ Form::label('incentive_date_sent', 'Incentive Date Sent : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_date_sent', Input::old('incentive_date_sent'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('incentive_date_sent'))
        <span class="help-block">{{ $errors->first('incentive_date_sent') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('visit_notes')) has-error @endif">
    {{ Form::label('visit_notes', 'Visit Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('visit_notes', Input::old('visit_notes'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('visit_notes'))
        <span class="help-block">{{ $errors->first('visit_notes') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('visit_notes')) has-error @endif">
    {{ Form::label('gift_card_returned', 'Gift Card Returned', array('class' => 'control-label control-label2')) }}
    {{ Form::checkbox('gift_card_returned', 'true', Input::old('gift_card_returned'), array('class' => 'gift_card_returned')) }}
    @if ($errors->has('gift_card_returned'))
        <span class="help-block">{{ $errors->first('gift_card_returned') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('incentive_returned_date')) has-error @endif">
    {{ Form::label('incentive_returned_date', 'Incentive Returned Date : ', array('class' => 'control-label')) }}
    {{ Form::text('incentive_returned_date', Input::old('incentive_returned_date'), array('class' => 'form-control datepicker disallow_future_dates actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('incentive_returned_date'))
        <span class="help-block">{{ $errors->first('incentive_returned_date') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('gift_card_returned_notes')) has-error @endif">
    {{ Form::label('gift_card_returned_notes', 'Incentive Returned Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('gift_card_returned_notes', Input::old('gift_card_returned_notes'), array('class' => 'form-control actual_visit_date_related_field', 'disabled')) }}
    @if ($errors->has('gift_card_returned_notes'))
        <span class="help-block">{{ $errors->first('gift_card_returned_notes') }}</span>
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

<div class="input-group input-group-lg sepH_a @if ($errors->has('manually_added')) has-error @endif">
    {{ Form::checkbox('manually_added', 'true') }}
    {{ Form::label('manually_added', 'Manually Added : ', array('class' => 'control-label control-label2')) }}
    @if ($errors->has('manually_added'))
        <span class="help-block">{{ $errors->first('manually_added') }}</span>
    @endif
</div>
