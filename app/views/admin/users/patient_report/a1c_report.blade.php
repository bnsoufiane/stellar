<?php $current_metric = "Not Available"; ?>

@foreach ($patient_program_visits as $patient_program_visit)

    @if($patient_program_visit->metric !== $current_metric)

        <?php $current_metric = $patient_program_visit->metric; ?>

        <tr>
            <td>{{$current_metric}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Scheduled Visit</td>
            <td>Actual Visit Date</td>
            <td>Incentive Type</td>
            <td>Value</td>
            <td>Incentive Code</td>
            <td>Date Sent</td>
            <td>Dr. Name</td>
            <td>Dr. Phone</td>
        </tr>

    @endif

    <tr>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->scheduled_visit_date:"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->actual_visit_date:"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->incentive_type:"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?"$$patient_program_visit->incentive_value":"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->gift_card_serial:"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->incentive_date_sent:"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?"$patient_program_visit->first_name $patient_program_visit->last_name ":"Not Available"}}</td>
        <td>{{($patient_program_visit!=null)?$patient_program_visit->phone:"Not Available"}}</td>
    </tr>
@endforeach
