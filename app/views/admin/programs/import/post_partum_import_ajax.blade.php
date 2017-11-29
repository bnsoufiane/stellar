@if (isset($finished))
<div class="row">
Import process is complete. <a  href="/{{ $result_file }}"> Click here to download failed summary report.</a>

</div>
@else
<form >
Import Record:: Member ID: {{ $import_row->username }} | Member Name: {{ $import_row->first_name }} {{$import_row->last_name}}
@foreach ($open_pp as $pp)
    <div class="radio" >
      <label><input type="radio" name="post_partum" value="{{ $pp->id }}_{{ $pp->pp_id }}" checked >Delivery date: {{ Carbon\Carbon::parse($pp->delivery_date)->format('m/d/Y') }}
      @if($pp->expiry == 'Expired') 
      | {{ $pp->expiry }}
      @endif 
      @if((isset($pp->scheduled_visit_date)) && ($pp->scheduled_visit_date != '0000-00-00 00:00:00'))
      | Scheduled Visit: {{ Carbon\Carbon::parse($pp->scheduled_visit_date)->format('m/d/Y') }}
      @endif  </label>
    </div>
@endforeach
    
     <div onClick="import_row('save')" class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a  class="btn btn-md btn-info">Save and Import Next Record</a>
      </div>
      <div onClick="import_row('cancel')" class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a class="btn btn-md btn-primary ">Skip This Record and Continue</a>
      </div>
      <div class="row">
      <div onClick="pause()" class="form-group sepH_c view_report_area" style="float:left;margin-right: .5%">
                    <a  class="btn btn-md btn-warning">Pause Import</a>
      </div>
      </div>
</form>
@endif
<script type="text/javascript">
function pause() {
  var content = '<div class="panel-heading" role="tab" id="headingOne"><h4 class="panel-title"> Post Partum Import</h4></div><div class="panel-body"><div class="container"><div class="row"><div class="alert alert-success">Import has been paused. To continue this import, select "Resume Paused Import" from the Action Button.</div></div> <center><div onClick="(function(){ window.parent.location.reload() })();return false;" class="form-group sepH_c view_report_area" style="float:center"><a class="btn btn-lg btn-warning ">Click Here to Close Import Process</a></div> </center></div></div>';
     $("#post_partum").html(content);
  }
  function import_row(action) {
    console.log('clicked');
    var import_id = {{ isset($import_row->id) ? $import_row->id : 0  }};
    var id = $('input[name=post_partum]:checked').val();
    var id_array = id.split("_");
    var existing_id = id_array[0];  
    var pp_id = id_array[1];  
    if (action=='save')
    {
      var user_action = "save";
    } 
    else if(action = "cancel")
    {
      var user_action = "cancel";
    }
    // else
    // {
    //   var content = '<p> You have successfully paused import process!</p>';
    //    $("#ajax_div").html(content);
    // }
    //console.log("Aaa"+edata+"  "+idata);
    var region_id = {{ isset($region_id) ? $region_id : 0 }};
    var data = { row_id_import_data: import_id ,
      row_id_existing_data:existing_id,
      pp_id:pp_id,
      region_id:region_id,
      user_action: user_action
    }
    if (import_id > 0)
    {
      $.ajax({
            url: '/admin/post_partum_import/ajax/'+region_id,
            type: 'GET',            
            data: data,
        success:function(result){ 
           //console.log("success event");
       
                  
               $("#ajax_div").html(result);
              
             } 
        });

    }    
   
   
};
</script>