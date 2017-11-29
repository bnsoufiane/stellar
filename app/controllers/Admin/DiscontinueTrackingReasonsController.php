<?php

namespace Admin;

use View;

class DiscontinueTrackingReasonsController extends BaseController
{

    public function index()
    {
        $discontinue_tracking_reasons = \DiscontinueTrackingReason::all();
        $this->layout->content = View::make('admin/general_settings/discontinue_tracking_reasons')
            ->with('discontinue_tracking_reasons', $discontinue_tracking_reasons)
            ->with('route', array('admin.discontinue_tracking_reasons.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $reasons = \Input::get('reason');
        $reasons_ids = \Input::get('reason_ids');

        try {
            if (isset($reasons)) {
                $i = 0;
                $updated_discontinue_tracking_reasons = [];
                foreach ($reasons as $reason) {
                    if (strlen($reason) < 2) {
                        continue;
                    }
                    if ($reasons_ids[$i] != 0) {
                        $phone = \DiscontinueTrackingReason::find($reasons_ids[$i]);
                    } else {
                        $phone = new \DiscontinueTrackingReason();
                    }
                    $phone->reason = $reason;
                    $phone->save();
                    $updated_discontinue_tracking_reasons[] = $phone->id;
                    $i++;
                }
                if (count($updated_discontinue_tracking_reasons)) {
                    \DiscontinueTrackingReason::whereNotIn('id', $updated_discontinue_tracking_reasons)->delete();
                }
            } else {
                \DiscontinueTrackingReason::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.discontinue_tracking_reasons.index')
                ->with('success', 'Discontinue Tracking Reasons have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.discontinue_tracking_reasons.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
