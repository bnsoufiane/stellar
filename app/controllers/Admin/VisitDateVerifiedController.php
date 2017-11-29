<?php

namespace Admin;

use View;

class VisitDateVerifiedController extends BaseController
{

    public function index()
    {
        $visit_date_verified = \VisitDateVerified::all();
        $this->layout->content = View::make('admin/general_settings/visit_date_verified')
            ->with('visit_date_verified', $visit_date_verified)
            ->with('route', array('admin.visit_date_verified.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $visit_date_verified = \Input::get('visit_date_verified');
        $visit_date_verified_ids = \Input::get('visit_date_verified_ids');

        try {
            if (isset($visit_date_verified)) {
                $i = 0;
                $updated_visit_date_verified = [];
                foreach ($visit_date_verified as $item) {
                    if (strlen($item) < 1) {
                        continue;
                    }
                    if ($visit_date_verified_ids[$i] != 0) {
                        $phone = \VisitDateVerified::find($visit_date_verified_ids[$i]);
                    } else {
                        $phone = new \VisitDateVerified();
                    }
                    $phone->title = $item;
                    $phone->save();
                    $updated_visit_date_verified[] = $phone->id;
                    $i++;
                }
                if (count($updated_visit_date_verified)) {
                    \VisitDateVerified::whereNotIn('id', $updated_visit_date_verified)->delete();
                }
            } else {
                \VisitDateVerified::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.visit_date_verified.index')
                ->with('success', 'Visit Date Verified By have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.visit_date_verified.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
