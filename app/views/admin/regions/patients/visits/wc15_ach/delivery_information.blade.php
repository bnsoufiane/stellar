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
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('delivery_date')) has-error @endif">
            {{ Form::label('delivery_date', 'Date of Birth: ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('delivery_date', Input::old('delivery_date'), array('class' => 'form-control form-control2 datepicker disallow_future_dates')) }}
            @if ($errors->has('delivery_date'))
                <span class="help-block">{{ $errors->first('delivery_date') }}</span>
            @endif
        </div>
    </div>
</div>

<div class="row sepH_a">

    <div class="col-lg-6">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('birth_weight')) has-error @endif">
            {{ Form::label('birth_weight', 'Birth Weight : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('birth_weight', Input::old('birth_weight'), array('class' => 'form-control form-control2')) }}
            @if ($errors->has('birth_weight'))
                <span class="help-block">{{ $errors->first('birth_weight') }}</span>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group input-group-lg sepH_a">
            {{ Form::label('gestational_age', 'Gestational Age : ', array('class' => 'control-label control-label2')) }}
            {{ Form::text('gestational_age', Input::old('gestational_age'), array('class' => 'form-control form-control2')) }}
        </div>
    </div>
</div>

<div class="row sepH_a">

    <div class="col-lg-6">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('confirmed')) has-error @endif">
            {{ Form::checkbox('confirmed', 'true') }}
            {{ Form::label('confirmed', 'Confirmed : ', array('class' => 'control-label control-label2')) }}
            @if ($errors->has('confirmed'))
                <span class="help-block">{{ $errors->first('confirmed') }}</span>
            @endif
        </div>
    </div>
    @if($program->type == \Program::TYPE_WC15_AHC)
        <div class="col-lg-6">
            <div class="input-group input-group-lg sepH_a @if ($errors->has('mother_k2yc')) has-error @endif">
                {{ Form::checkbox('mother_k2yc', 'true') }}
                {{ Form::label('mother_k2yc', 'Mother K2YC : ', array('class' => 'control-label control-label2')) }}
                @if ($errors->has('mother_k2yc'))
                    <span class="help-block">{{ $errors->first('mother_k2yc') }}</span>
                @endif
            </div>
        </div>
    @endif
</div>


<div class="row sepH_a"
     style="margin-top: 70px;">
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


<div class="row sepH_a" style="margin-bottom: 90px !important;">
    <div class="col-lg-8">
        <div class="input-group input-group-lg sepH_a @if ($errors->has('how_did_you_hear')) has-error @endif">
            {{ Form::label('how_did_you_hear', 'How Did You Hear : ', array('class' => 'control-label control-label2')) }}
            {{Form::select('how_did_you_hear', $how_did_you_hear, Input::old('how_did_you_hear'), array('class' => 'form-control form-control2'))}}
        </div>
    </div>
</div>
