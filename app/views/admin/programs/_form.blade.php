{{ Form::model($program, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

@if(isset($region_id))
    <input type="hidden" name="region_id" value="{{$region_id}}"/>
@endif

<div class="input-group input-group-lg sepH_a @if ($errors->has('name')) has-error @endif">
    {{ Form::label('name', 'Name', array('class' => 'control-label')) }}
    {{ Form::text('name', Input::old('name'), array('class' => 'form-control')) }}
    @if ($errors->has('name'))
        <span class="help-block">{{ $errors->first('name') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('abbreviation')) has-error @endif">
    {{ Form::label('abbreviation', 'Abbreviation : ', array('class' => 'control-label')) }}
    {{ Form::text('abbreviation', Input::old('abbreviation'), array('class' => 'form-control')) }}
    @if ($errors->has('abbreviation'))
        <span class="help-block">{{ $errors->first('abbreviation') }}</span>
    @endif
</div>

<div class="input-group input-group-lg">
    {{ Form::label('type', 'Type', array('class' => 'control-label')) }}
    {{Form::select('type', $types_array, Input::old('type'))}}
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('notes')) has-error @endif">
    {{ Form::label('notes', 'Notes', array('class' => 'control-label')) }}
    {{ Form::text('notes', Input::old('notes'), array('class' => 'form-control')) }}
    @if ($errors->has('notes'))
        <span class="help-block">{{ $errors->first('notes') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('sms_content')) has-error @endif">
    {{ Form::label('sms_content', 'Sms Content', array('class' => 'control-label')) }}
    {{ Form::text('sms_content', Input::old('sms_content'), array('class' => 'form-control')) }}
    @if ($errors->has('sms_content'))
        <span class="help-block">{{ $errors->first('sms_content') }}</span>
    @endif
</div>

<div class="input-group input-group-lg sepH_a @if ($errors->has('call_text')) has-error @endif">
    {{ Form::label('call_text', 'Phone Call Text', array('class' => 'control-label')) }}
    {{ Form::text('call_text', Input::old('call_text'), array('class' => 'form-control')) }}
    @if ($errors->has('call_text'))
        <span class="help-block">{{ $errors->first('call_text') }}</span>
    @endif
</div>

<!--
<div class="dropzone upload_no_ajax" data-upload-extensions="jpg,mp3,wav,wma,ogg,m4a,au,aac,amr,mid" style="padding: 15px 8px 5px 20px">
    <div class="btn btn-md btn-success btn-rad">Choose file</div>
    <input type="hidden" name="call_mp3" rv-value="call_mp3"/>
</div>
-->

<div class="input-group input-group-lg sepH_a @if ($errors->has('email_content')) has-error @endif">
    {{ Form::label('email_content', 'Email Content', array('class' => 'control-label')) }}
    {{ Form::text('email_content', Input::old('email_content'), array('class' => 'form-control')) }}
    @if ($errors->has('email_content'))
        <span class="help-block">{{ $errors->first('email_content') }}</span>
    @endif
</div>

<div class="input-group input-group-lg">
    {{ Form::label('contact_frequency', 'Contact Frequency', array('class' => 'control-label')) }}<br/>
    {{Form::selectRange('contact_frequency_times', 1, 10, Input::old('contact_frequency_times'))}} times
    per {{Form::select('contact_frequency_period', $periods_table, Input::old('contact_frequency_period'))}}
</div>
<br/>

<div class="input-group input-group-lg">
    {{ Form::label('visit_requirement', 'Visit Requirement', array('class' => 'control-label')) }}<br/>
    {{Form::selectRange('visit_requirement_times', 1, 10, Input::old('visit_requirement_times'))}} times
    per {{Form::select('visit_requirement_period', $periods_table, Input::old('visit_requirement_times_period'))}}
</div>

<div class="sepH_c text-right"></div>
<div class="form-group sepH_c">
    <button type="submit" class="btn btn-lg btn-primary ">Save</button>
    <a href="{{ URL::route('admin.regions.programs_roster', $region_id) }}" class="btn btn-lg btn-default ">Cancel</a>
</div>
{{ Form::close() }}

