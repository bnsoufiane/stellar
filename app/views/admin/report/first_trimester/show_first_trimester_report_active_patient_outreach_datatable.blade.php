<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
        <th>Insurance Company: {{$values['insurance_company']->name}}</th>
        <?php
        for ($i = 1; $i <= 31; $i++) {
            echo "<th></th>";
        }
        ?>
    </tr>
    <tr>
        <th>Region: {{$values['region']->name}}</th>
        <?php
        for ($i = 1; $i <= 31; $i++) {
            echo "<th></th>";
        }
        ?>
    </tr>
    <tr>
        <th>Program: {{$values['program']->name}}</th>
        <?php
        for ($i = 1; $i <= 31; $i++) {
            echo "<th></th>";
        }
        ?>
    </tr>
    <tr>
        <th>Patient Id</th>
        <th>Medicaid ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Date of birth</th>
        <th>Gender</th>
        <th>Address1</th>
        <th>Address2</th>
        <th>City</th>
        <th>State</th>
        <th>Zip</th>
        <th>County</th>
        <th>Phone</th>
        <th>Trac Phone</th>
        <th>Due Date</th>
        <th>Opt-in Date</th>
        <th>Enrolled</th>
        <th>Scheduled Visit Date</th>
        <th>Scheduled Visit Notes</th>
        <th>Outreach Date</th>
        <th>Outreach Code</th>
        <th>Outreach Notes</th>
        <th>Actual Visit Date</th>
        <th>Doctor ID</th>
        <th>Actual Visit Notes</th>
        <th>Incentive Type</th>
        <th>Incentive Amount</th>
        <th>Incentive Date</th>
        <th>Incentive Code</th>
        <th>E-script</th>
        <th>Next Scheduled Visit</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>


@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>
        mytable = $('table').DataTable({
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": []
            },

            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null
            ],
            "fnServerParams": function (aoData) {
                aoData.push(
                        {name: "insurance_company", value: {{$values['input']['insurance_company']}}},
                        {name: "region", value: {{$values['input']['region']}}},
                        {name: "program", value: {{$values['input']['program']}}},
                        {name: "date_range", value: "{{$values['input']['date_range']}}"},
                        {name: "pregnancy_report_type", value: "{{$values['input']['pregnancy_report_type']}}"}
                )
            },

        @foreach ($options as $k => $o)
        {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach

        @foreach ($callbacks as $k => $o)
        {{ json_encode($k) }}: {{ $o }},
        @endforeach


        })
        ;

        var data = {
            'insurance_company': "{{$values['input']['insurance_company']}}",
            'region': "{{$values['input']['region']}}",
            'program': "{{$values['input']['program']}}",
            'date_range': "{{$values['input']['date_range']}}",
            'pregnancy_report_type': "{{$values['input']['pregnancy_report_type']}}"
        }


        var url = [];
        for (var d in data)
            url.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
        url = url.join("&");

        var _href = " <?php echo URL::route('admin.reports.generate_first_trimester_report_csv') ?>?" + url;
        $('<a class="DTTT_button" id="program_report_csv" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop

