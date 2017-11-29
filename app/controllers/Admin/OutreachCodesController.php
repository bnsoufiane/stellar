<?php

namespace Admin;

use View;

class OutreachCodesController extends BaseController
{

    public function index()
    {
        $outreach_codes = \OutreachCode::all();
        $this->layout->content = View::make('admin/general_settings/outreach_codes')
            ->with('outreach_codes', $outreach_codes)
            ->with('route', array('admin.outreach_codes.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $codes = \Input::get('code');
        $codes_names = \Input::get('code_name');
        $codes_ids = \Input::get('code_ids');

        try {
            if (isset($codes)) {
                $i = 0;
                $updated_outreach_codes = [];
                foreach ($codes as $code) {
                    if (strlen($code) < 1) {
                        continue;
                    }
                    if ($codes_ids[$i] != 0) {
                        $outreach_code = \OutreachCode::find($codes_ids[$i]);
                    } else {
                        $outreach_code = new \OutreachCode();
                    }
                    $outreach_code->code = $code;
                    $outreach_code->code_name = $codes_names[$i];
                    $outreach_code->save();
                    $updated_outreach_codes[] = $outreach_code->id;
                    $i++;
                }
                if (count($updated_outreach_codes)) {
                    \OutreachCode::whereNotIn('id', $updated_outreach_codes)->delete();
                }
            } else {
                \OutreachCode::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.outreach_codes.index')
                ->with('success', 'Outreach Codes have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.outreach_codes.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
