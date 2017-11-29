<?php $user = \User::find(Sentry::getUser()->id); ?>
<div class="panel panel-default" id="post_partum">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">        
          Post Partum Import
        </a>
      </h4>
    </div>
   <div class="panel-body">
<div class="container">
@if ( isset($message) )
<div class="row">
{{ $message }}
</div>
@endif
<div class="row" id="ajax_div">

  </div>
  </div>
  </div>
  </div>

<script type="text/javascript">
$(function(){

  // $(window).bind("beforeunload", function() { 
  //       window.parent.location.reload();
  //   });
  var region_id = {{ $region_id }};

   $.ajax({
            url: '/admin/post_partum_import/process_imported_dates_post_partum/'+region_id,
            type: 'GET',            
            //data: data,
            success:function(result){             
               $("#ajax_div").html(result);
              
             } 
        });

});




 
</script>



