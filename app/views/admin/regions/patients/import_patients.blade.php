@extends('admin.layouts.base_iframe')

@section('content')
    <div class="page-head">
        <h4 class="orange_color">{{"$insurance_company->name: $region->name"}}</h4>
    </div>

    <div class="cl-mcont" style="padding: 6px 0 0 0;">

        <div class="block-flat no-padding">
            <div class="content">
                <form role="form">

                    @include('admin/modals/programs_modal')

                    <div class="dropzone import_patients" data-upload-extensions="csv,xls,xlsx"
                         style="padding: 15px 8px 5px 20px">
                        <div class="btn btn-md btn-success btn-rad">Choose file</div>
                        <input type="hidden" name="imported_file" rv-value="model:imported_file"/>
                    </div>
                </form>
                <br/><br/>

                <div class="import_cancel_buttons" style="display: none;">
                    <button class="btn btn-primary add_imported_patients" region_id="{{$region->id}}" type="submit">Add
                        Patients
                    </button>
                    <a class="btn btn-default cancel_importing" name="cancel" value="cancel">Cancel Import</a>
                </div>
                <br/>

                <div class="form-group list-component" id="imported-patients"
                     style="display: none; overflow: auto !important;">

                    <table class="table table-striped table-bordered" width="150%">
                        <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Medicaid ID</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Date Of Birth</th>
                            <th>Sex</th>
                            <th>Address1</th>
                            <th>Address2</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Zip</th>
                            <th>County</th>
                            <th>Phone1</th>
                            <th>CellPhone#</th>
                            <th>Email</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>

                    </table>

                    <br/><br/>

                </div>

            </div>
        </div>
    </div>
@stop
