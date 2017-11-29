<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <?php
    $columns_count = 17;
    ?>
    <tr>
        <th>Insurance Company: {{$values['insurance_company']->name}}</th>
        <?php
        for ($i = 1; $i <= $columns_count; $i++) {
            echo "<th></th>";
        }
        ?>
    </tr>
    <tr>
        <th>Region: {{$values['region']->name}}</th>
        <?php
        for ($i = 1; $i <= $columns_count; $i++) {
            echo "<th></th>";
        }
        ?>
    </tr>
    <tr>
        <th>Program: {{$values['program']->name}}</th>
        <?php
        for ($i = 1; $i <= $columns_count; $i++) {
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
        <th>Outreach Date</th>
        <th>Outreach Code</th>
        <th>Outreach Notes</th>
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
                null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null
            ],
            "fnServerParams": function (aoData) {
                aoData.push(
                        {name: "insurance_company", value: {{$values['input']['insurance_company']}}},
                        {name: "region", value: {{$values['input']['region']}}},
                        {name: "program", value: {{$values['input']['program']}}},
                        {name: "outreach_codes", value: {{json_encode($values['input']['outreach_codes'])}}},
                        {name: "date_range", value: "{{$values['input']['date_range']}}"}
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
            'outreach_codes': {{json_encode($values['input']['outreach_codes'])}},
            'date_range': "{{$values['input']['date_range']}}"
        }


        var url = [];
        for (var d in data)
            url.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
        url = url.join("&");

        var _href = " <?php echo URL::route('admin.reports.generate_outreach_codes_report_csv') ?>?" + url;
        $('<a class="DTTT_button" id="program_report_csv" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop

