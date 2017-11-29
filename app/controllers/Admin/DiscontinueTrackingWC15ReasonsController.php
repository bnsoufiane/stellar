<?php

namespace Admin;

use View;

class DiscontinueTrackingWC15ReasonsController extends BaseController
{

    public function index()
    {
        $discontinue_tracking_wc15_reasons = \DiscontinueTrackingWC15Reason::all();
        $this->layout->content = View::make('admin/general_settings/discontinue_tracking_wc15_reasons')
            ->with('discontinue_tracking_wc15_reasons', $discontinue_tracking_wc15_reasons)
            ->with('route', array('admin.discontinue_tracking_wc15_reasons.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $reasons = \Input::get('reason');
        $reasons_ids = \Input::get('reason_ids');

        try {
            if (isset($reasons)) {
                $i = 0;
                $updated_discontinue_tracking_wc15_reasons = [];
                foreach ($reasons as $reason) {
                    if (strlen($reason) < 2) {
                        continue;
                    }
                    if ($reasons_ids[$i] != 0) {
                        $phone = \DiscontinueTrackingWC15Reason::find($reasons_ids[$i]);
                    } else {
                        $phone = new \DiscontinueTrackingWC15Reason();
                    }
                    $phone->reason = $reason;
                    $phone->save();
                    $updated_discontinue_tracking_wc15_reasons[] = $phone->id;
                    $i++;
                }
                if (count($updated_discontinue_tracking_wc15_reasons)) {
                    \DiscontinueTrackingWC15Reason::whereNotIn('id', $updated_discontinue_tracking_wc15_reasons)->delete();
                }
            } else {
                \DiscontinueTrackingWC15Reason::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.discontinue_tracking_wc15_reasons.index')
                ->with('success', 'Discontinue Tracking WC15 Reasons have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.discontinue_tracking_wc15_reasons.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
