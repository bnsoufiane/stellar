<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Automated Reports</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          Pregnancy Discontinue Report
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
      <div class="row">
        <div class="form-group sepH_c view_report_area" style="float:left;margin-right:.5%;border:none">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=2&program=33&date_range=&pregnancy_report_type=4&all_dates=') }}" class="btn btn-md btn-primary ">Keystone First (SE region PA)</a>
                </div>
                <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=3&program=33&date_range=&pregnancy_report_type=4&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas Northeast</a>
                </div>
                 <div class="form-group sepH_c view_report_area">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=8&program=33&date_range=&pregnancy_report_type=4&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas PA</a>
                </div>
            </div>
            
            * daterange = current month - first date of month to current date<br>
            * sort by discontinue date - oldest date first
           
             
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Pregnancy Active Patient Opt-in Report
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">
      <div class="row">
        <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=2&program=33&date_range=&pregnancy_report_type=0&all_dates=') }}" class="btn btn-md btn-primary ">Keystone First (SE region PA)</a>
                </div>
                <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=3&program=33&date_range=&pregnancy_report_type=0&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas Northeast</a>
                </div>
                <div class="form-group sepH_c view_report_area"  style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=8&program=33&date_range=&pregnancy_report_type=0&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas PA</a>
                </div>
                
                
               
                
                </div>
                
            * daterange = current month - first date of month to current date<br>
            * sort by optin date - oldest date first</div>
     
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingThree">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Pregnancy Delivery Report
        </a>
      </h4>
    </div>
    <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
      <div class="panel-body">
      <div class="row">
        <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=2&program=33&date_range=&pregnancy_report_type=2&all_dates=') }}" class="btn btn-md btn-primary ">Keystone First (SE region PA)</a>
                </div>
                <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=3&program=33&date_range=&pregnancy_report_type=2&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas Northeast</a>
                </div>
                <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a href="{{ url('admin/automated_pregnancy_reports?insurance_company=1&region=8&program=33&date_range=&pregnancy_report_type=2&all_dates=') }}" class="btn btn-md btn-primary ">Amerihealth Caritas PA</a>
                </div>
        </div>
        
            * daterange = current month - first date of month to current date<br>
            * sort by delivery date - oldest date first
        
      </div>
    </div>
  </div>
    <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingThree">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
         Member Roster Report
        </a>
      </h4>
    </div>
    <div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
      <div class="panel-body">
      <div class="row">

        <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=2&program=44&date_range=&report_version=1&all_dates=') }}"  class="btn btn-md btn-primary "> Keystone First  - KF K2YC NATR</a>
            </div>
            <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=2&program=51&date_range=&report_version=1&all_dates=') }}"  class="btn btn-md btn-primary "> Keystone First  - WC15 KF </a>
            </div>
            <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=3&program=45&date_range=&report_version=1&all_dates=1') }}"  class="btn btn-md btn-primary "> Amerihealth Caritas Northeast  - Keys to Your Care </a>
            </div>

             <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=3&program=49&date_range=&report_version=1&all_dates=') }}"  class="btn btn-md btn-primary "> Amerihealth Caritas Northeast  - WC15-NE </a>
            </div>
        </div>
        
                * daterange = current month - first date of month to current date<br>
                * Sort by Optin date - Oldest first
        
      </div>
    </div>
  </div>
   <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingThree">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
       Member Encounters Report
        </a>
      </h4>
    </div>
    <div id="collapseFive" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
      <div class="panel-body">
            <div class="row">

           <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=2&program=44&date_range=&report_version=2&all_dates=') }}"  class="btn btn-md btn-primary "> Keystone First  - KF K2YC NATR</a>
            </div>
            <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=2&program=51&date_range=&report_version=2&all_dates=') }}"  class="btn btn-md btn-primary "> Keystone First  - WC15 KF </a>
            </div>
            <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=3&program=45&date_range=&report_version=2&all_dates=1') }}"  class="btn btn-md btn-primary "> Amerihealth Caritas Northeast  - Keys to Your Care </a>
            </div>

             <div class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                <a href="{{ url('admin/automated_roster_reports?insurance_company=1&region=3&program=49&date_range=&report_version=2&all_dates=') }}"  class="btn btn-md btn-primary "> Amerihealth Caritas Northeast  - WC15-NE </a>
            </div>
            </div>
            
            * daterange = current month - first date of month to current date<br>
            * Sort by Optin date - Oldest first
            
      </div>
    </div>
  </div>
</div>
@stop



