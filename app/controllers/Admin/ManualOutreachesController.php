<?php

namespace Admin;

use View;

class ManualOutreachesController extends BaseController
{
    public function edit($patient_id, $program_id, $manual_outreach_id)
    {
        $outreach_codes = \OutreachCode::orderBy('code_name')->get()->lists('code_name', 'id');
        $manual_outreach = \ManualOutreach::find($manual_outreach_id);

        if (!$manual_outreach) {
            \App::abort(404);
        }
        $manual_outreach->outreach_date = \Helpers::format_date_display($manual_outreach->outreach_date);

        $this->layout->content = View::make('admin/regions/patients/manual_outreachs/edit')
            ->with('outreach_codes', $outreach_codes)
            ->with('manual_outreach', $manual_outreach)
            ->with('patient_id', $patient_id)
            ->with('program_id', $program_id)
            ->with('route', array('admin.manual_outreaches.update', $manual_outreach_id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $input['outreach_date'] = date('Y-m-d', strtotime($input['outreach_date']));

        $manual_outreach = \ManualOutreach::find($id);
        $manual_outreach->fill($input);
        $manual_outreach->save();

        return \Redirect::route('admin.programs.patient_visits', array($manual_outreach->patient_id, $manual_outreach->program_id))
            ->with('success', 'A manual outreach has been successfully updated.');
    }

    public function destroy($id)
    {
        $manual_outreach = \ManualOutreach::find($id);

        if (!$manual_outreach) {
            \App::abort(404);
        }

        $manual_outreach->delete();
        return array('ok' => 1);
    }
}
