<?php

if (!isset($actual_visit)) {
    $actual_visit = (object)[
            'metric' => \Program::METRIC_URINE,
            'scheduled_visit_date' => null,
            'scheduled_visit_date_notes' => null,
    ];
}

$scheduled_visit_id = isset($actual_visit->id) ? $actual_visit->id : 'new_visit_0';

?>

<div class="scheduled_visit_row">

    {{Form::hidden('scheduled_visit_ids[]', $scheduled_visit_id)}}

    <div class="input-group input-group-lg sepH_a">
        {{ Form::label('metric_'.$scheduled_visit_id, 'Metric: ', array('class' => 'control-label')) }}
        {{Form::select('metric_'.$scheduled_visit_id, array(\Program::METRIC_URINE => 'Urine', \Program::METRIC_BLOOD => 'Blood', \Program::METRIC_EYE => 'Eye', \Program::METRIC_BLOOD_AND_URINE => 'Blood & Urine', \Program::METRIC_URINE_EYE => 'Urine & Eye', \Program::METRIC_BLOOD_EYE => 'Blood & Eye', \Program::METRIC_BLOOD_URINE_EYE => 'Blood, Urine, Eye'), $actual_visit->metric, array('class' => 'form-control'))}}
    </div>

    <div class="input-group input-group-lg sepH_a">
        {{ Form::label('scheduled_visit_date_'.$scheduled_visit_id, 'Scheduled visit date : ', array('class' => 'control-label')) }}
        {{ Form::text('scheduled_visit_date_'.$scheduled_visit_id, \Helpers::format_date_display($actual_visit->scheduled_visit_date), array('class' => 'form-control form-control2 datepicker', 'autocomplete' => 'off')) }}
    </div>

    <div class="input-group input-group-lg sepH_a">
        {{ Form::label('scheduled_visit_date_notes_'.$scheduled_visit_id, 'Scheduled Visit Date Notes : ', array('class' => 'control-label')) }}
        {{ Form::text('scheduled_visit_date_notes_'.$scheduled_visit_id, $actual_visit->scheduled_visit_date_notes, array('class' => 'form-control form-control2', 'autocomplete' => 'off')) }}
    </div>

    <br/>

</div>