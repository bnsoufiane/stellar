{{ Form::model($program, array('route' => $route, 'method' => isset($method) ? $method : 'POST',
 'id'=>'pregnancy_scheduled_visits')) }}

<input type="hidden" name="patient_id" value="{{$patient->id}}"/>
<input type="hidden" name="program_id" value="{{$program->id}}"/>
<input type="hidden" name="program_instance_id" value="{{!empty($pregnancy)?$pregnancy->id : 0}}"/>

<div id="pregnancy_instance" @if(!isset($pregnancy) || $pregnancy == null) style="display: none;" @endif>

    <div class="input-group input-group-lg sepH_a @if ($errors->has('patient_notes')) has-error @endif"
         style="margin-bottom: 40px !important;">
        {{ Form::label('patient_notes', 'Patient Notes : ', array('class' => 'control-label')) }}
        {{ Form::text('patient_notes', Input::old('patient_notes'), array('class' => 'form-control')) }}
        @if ($errors->has('patient_notes'))
            <span class="help-block">{{ $errors->first('patient_notes') }}</span>
        @endif
    </div>

    <div id="pregnancy_fields">

        @include('admin/regions/patients/visits/pregnancy/delivery_information')

        <div class="scheduled_visit_fields">
            <?php

            if (count($actual_visits) == 0) {
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

            @include('admin/regions/patients/visits/pregnancy/sign_up_fields')

            <?php
            if (count($actual_visits) > 0 && isset($actual_visits[0]->sign_up) && $actual_visits[0]->sign_up) {
                array_shift($actual_visits);
            }
            ?>

            @foreach($actual_visits as $actual_visit)
                {{Form::hidden('old_scheduled_visit_ids[]', isset($actual_visit->id)?$actual_visit->id:null)}}
                @include('admin/regions/patients/visits/pregnancy/scheduled_visit_fields_row')
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
        <a type="submit" class="btn btn-lg btn-default cancel_add_new_pregnancy">Cancel</a>
        <button type="submit" class="btn btn-lg btn-primary ">Save</button>
    </div>

    {{ Form::close() }}



    {{--<div style="display: none;">--}}
        {{--@include('admin/regions/patients/visits/pregnancy/scheduled_visit_fields_row')--}}
    {{--</div>--}}
</div>
