<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
        <th>Insurance Company: {{$values['insurance_company']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        @if ($values['program']->type == \Program::TYPE_PREGNANCY)
            <th></th>
        @endif
    </tr>
    <tr>
        <th>Region: {{$values['region']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        @if ($values['program']->type == \Program::TYPE_PREGNANCY)
            <th></th>
        @endif
    </tr>
    <tr>
        <th>Program: {{$values['program']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        @if ($values['program']->type == \Program::TYPE_PREGNANCY)
            <th></th>
        @endif
    </tr>
    <tr>
        <th>Metric</th>
        <th>Patient Id</th>
        <th>Medicaid ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Date Of Birth</th>
        @if ($values['program']->type == \Program::TYPE_PREGNANCY)
            <th>Enroll Date</th>
        @endif
        <th>Scheduled Visit Date</th>
        <th>Actual Visit Date</th>
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
                null, null, null, null, null, null, null, null, null, null, null, null @if ($values['program']->type == \Program::TYPE_PREGNANCY) , null @endif
            ],
            "fnServerParams": function (aoData) {
                aoData.push(
                        {name: "insurance_company", value: {{$values['input']['insurance_company']}}},
                        {name: "region", value: {{$values['input']['region']}}},
                        {name: "program", value: {{$values['input']['program']}}},
                        {name: "date_range", value: "{{$values['input']['date_range']}}"}
                )
            },
            "aoColumnDefs": [
                {
                    "aTargets": [6],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 6;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        var date = new Date(data[iCol]);
                        date = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
                        return date;
                    }
                },
                {
                    "aTargets": [7],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 7;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        var date = new Date(data[iCol]);
                        date = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
                        return date;
                    }
                },
            ],


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
            'date_range': "{{$values['input']['date_range']}}"
        }


        var url = [];
        for (var d in data)
            url.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
        url = url.join("&");

        var _href = " <?php echo URL::route('admin.reports.generate_scheduled_visit_report_csv') ?>?" + url;
        $('<a class="DTTT_button" id="program_report_csv" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop
