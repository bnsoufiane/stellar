<thead>
<tr>
    <td>Patient Id: {{$patient->username}}</td>
    <td></td>
    <td>Last Name, First Name:<br/>{{"$patient->last_name, $patient->first_name"}}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
</thead>
<tr>
    <td>
        DOB: {{\Helpers::format_date_display($patient->date_of_birth)}} &nbsp;&nbsp;
        Address: {{"$patient->address1, $patient->address2 $patient->state $patient->zip"}}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<tr>
    <td>Patient Activity for {{$year}}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<tr>
    <td>Insurance Company/Region: {{"$insurance_company->name / $region->name"}}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
<tr>
    <?php
    $programs_str = '';
    ?>

    @foreach ($programs as $program)
        @if ($programs_str !== '')
            <?php $programs_str .= ', '?>
        @endif
        <?php $programs_str .= $program->name;?>
    @endforeach

    <td>Programs: {{$programs_str}}</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
</tr>
