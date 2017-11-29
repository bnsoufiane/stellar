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

@if(isset($first_trimester) && $first_trimester != null)

        <!-- <div id="pregnancy_fields" style="display: none;"> -->
<div id="pregnancy_fields">

    @include('admin/regions/patients/visits/first_trimester/delivery_information')

    <div class="scheduled_visit_fields">
        <?php

        if (count($actual_visits) > 0 && isset($actual_visits[0]->sign_up) && $actual_visits[0]->sign_up) {
            $sign_up_not_set = true;
        } else {
            $sign_up_not_set = false;
        }

        if (count($actual_visits) > 0 && isset($actual_visits[0]->sign_up) && $actual_visits[0]->sign_up) {
            array_shift($actual_visits);
        }
        ?>

        @include('admin/regions/patients/visits/first_trimester/visit_fields')

    </div>

</div>

<div class="sepH_c text-right"></div>
<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
</div>

@else
    <div class="form-group sepH_c">
        <a href="{{ URL::route('admin.programs.add_new_first_trimester', array($patient->id, $program->id)) }}"
           class="btn btn-lg btn-primary ">Add
            New First Trimester Instance</a>
    </div>
@endif

{{ Form::close() }}
