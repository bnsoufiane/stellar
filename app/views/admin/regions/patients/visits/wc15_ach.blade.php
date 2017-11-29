{{ Form::model($program, array('route' => $route, 'method' => isset($method) ? $method : 'POST',
 'id'=>'wc15_scheduled_visits')) }}

<input type="hidden" name="patient_id" value="{{$patient->id}}"/>
<input type="hidden" name="program_id" value="{{$program->id}}"/>

<div class="input-group input-group-lg sepH_a @if ($errors->has('patient_notes')) has-error @endif"
     style="margin-bottom: 40px !important;">
    {{ Form::label('patient_notes', 'Patient Notes : ', array('class' => 'control-label')) }}
    {{ Form::text('patient_notes', Input::old('patient_notes'), array('class' => 'form-control')) }}
    @if ($errors->has('patient_notes'))
        <span class="help-block">{{ $errors->first('patient_notes') }}</span>
    @endif
</div>

<!-- <div id="pregnancy_fields" style="display: none;"> -->
<div id="pregnancy_fields">

    @include('admin/regions/patients/visits/wc15_ach/delivery_information')

    <div class="scheduled_visit_fields">
        <?php

        if (count($actual_visits) < 2) {
            $actual_visit = new stdClass();
            $actual_visit->scheduled_visit_date = "0000-00-00 00:00:00";
            $actual_visit->actual_visit_date = "0000-00-00 00:00:00";
            $actual_visit->incentive_type = "";
            $actual_visit->incentive_value = "";
            $actual_visit->gift_card_serial = "";
            $actual_visit->incentive_date_sent = "0000-00-00 00:00:00";
            $actual_visit->visit_notes = "";

            $actual_visits[] = $actual_visit;
        }
        if (count($actual_visits) > 0 && isset($actual_visits[0]->sign_up) && $actual_visits[0]->sign_up) {
            $sign_up_not_set = true;
        } else {
            $sign_up_not_set = false;
        }

        ?>

        @include('admin/regions/patients/visits/wc15_ach/sign_up_fields')

        <?php
        if (count($actual_visits) > 0 && isset($actual_visits[0]->sign_up) && $actual_visits[0]->sign_up) {
            array_shift($actual_visits);
        }
        ?>
        <?php $visit_number = 1; ?>
        @foreach($actual_visits as $actual_visit)
            {{Form::hidden('old_scheduled_visit_ids[]', isset($actual_visit->id)?$actual_visit->id:null)}}
            @include('admin/regions/patients/visits/wc15_ach/scheduled_visit_fields_row')
            <?php $visit_number++; ?>
        @endforeach

    </div>

    <div class="row sepH_a">
        <div class="col-lg-4"></div>
        <div class="col-lg-8">
            <a class="btn btn-sm btn-primary add_new_row">Add New Row</a>
        </div>
    </div>

</div>

<div class="sepH_c text-right"></div>
<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
</div>

{{ Form::close() }}
