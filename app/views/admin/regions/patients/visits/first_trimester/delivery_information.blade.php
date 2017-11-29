<div class="row sepH_a">
    <div class="col-lg-8">
        <div class="input-group">
            {{ Form::label('enrolled_by', 'Enrolled By: ', array('class' => 'control-label')) }}
            {{Form::select('enrolled_by', array(\Program::ENROLLED_BY_UNDEFINED => 'Undefined', \Program::ENROLLED_BY_HC=> 'HC', \Program::ENROLLED_BY_STELLAR => 'Stellar'), Input::old('enrolled_by'), array('class' => 'form-control'))}}
        </div>
    </div>
</div>


<div class="row sepH_a">
    <div class="col-lg-6">
        <div class="input-group">
            {{ Form::label('date_added', 'Opt-In Date : ', array('class' => 'control-label control-label2' )) }}
            {{ Form::text('date_added', Input::old('date_added'), array('class' => 'form-control form-control2 datepicker disallow_future_dates')) }}
            @if ($errors->has('date_added'))
                <span class="help-block">{{ $errors->first('date_added') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('due_date')) has-error @endif">
            {{ Form::label('due_date', 'Due Date : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('due_date', Input::old('due_date'), array('class' => 'form-control form-control2 datepicker')) }}
            @if ($errors->has('due_date'))
                <span class="help-block">{{ $errors->first('due_date') }}</span>
            @endif
        </div>
    </div>
</div>


<div class="row sepH_a"
     style="margin-top: 40px;">

    <div class="col-lg-12">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('postpartum_start')) has-error @endif">
            {{ Form::label('postpartum_start', 'First Trimester Start Date: ', array('class' => 'control-label')) }}
            {{ Form::text('postpartum_start', Input::old('postpartum_start'), array('class' => 'form-control datepicker')) }}
            @if ($errors->has('postpartum_start'))
                <span class="help-block">{{ $errors->first('postpartum_start') }}</span>
            @endif
        </div>

        <div class="input-group input-group-lg sepH_a @if ($errors->has('postpartum_end')) has-error @endif">
            {{ Form::label('postpartum_end', 'First Trimester End Date : ', array('class' => 'control-label')) }}
            {{ Form::text('postpartum_end', Input::old('postpartum_end'), array('class' => 'form-control datepicker')) }}
            @if ($errors->has('postpartum_end'))
                <span class="help-block">{{ $errors->first('postpartum_end') }}</span>
            @endif
        </div>
    </div>

    <div class="col-lg-3">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('discontinue')) has-error @endif">
            {{ Form::checkbox('discontinue', 'true') }}
            {{ Form::label('discontinue', 'Discontinue Tracking : ', array('class' => 'control-label control-label2')) }}
            @if ($errors->has('discontinue'))
                <span class="help-block">{{ $errors->first('discontinue') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group input-group-lg sepH_a">
            {{Form::select('discontinue_reason', $discontinue_tracking_reasons, Input::old('discontinue_reason'), array('class' => 'form-control form-control2'))}}
        </div>
    </div>

    <div class="col-lg-6">
        <div class="input-group">
            {{ Form::label('discontinue_date', 'Discontinue Date : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('discontinue_date', Input::old('discontinue_date'), array('class' => 'form-control form-control2 datepicker')) }}
        </div>
    </div>

</div>


<div class="row sepH_a" style="margin-bottom: 40px !important;">
    <div class="col-lg-8">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('how_did_you_hear')) has-error @endif">
            {{ Form::label('how_did_you_hear', 'How Did You Hear : ', array('class' => 'control-label control-label2')) }}
            {{Form::select('how_did_you_hear', $how_did_you_hear, Input::old('how_did_you_hear'), array('class' => 'form-control form-control2'))}}
        </div>
    </div>
</div>
