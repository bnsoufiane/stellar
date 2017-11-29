<?php

namespace Admin;

use View;

class HowDidYouHearController extends BaseController
{

    public function index()
    {
        $how_did_you_hears = \HowDidYouHear::all();
        $this->layout->content = View::make('admin/general_settings/how_did_you_hear')
            ->with('how_did_you_hears', $how_did_you_hears)
            ->with('route', array('admin.how_did_you_hear.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $labels = \Input::get('label');
        $labels_ids = \Input::get('label_ids');

        try {
            if (isset($labels)) {
                $i = 0;
                $updated_how_did_you_hears = [];
                foreach ($labels as $label) {
                    if (strlen($label) < 2) {
                        continue;
                    }
                    if ($labels_ids[$i] != 0) {
                        $phone = \HowDidYouHear::find($labels_ids[$i]);
                    } else {
                        $phone = new \HowDidYouHear();
                    }
                    $phone->label = $label;
                    $phone->save();
                    $updated_how_did_you_hears[] = $phone->id;
                    $i++;
                }
                if (count($updated_how_did_you_hears)) {
                    \HowDidYouHear::whereNotIn('id', $updated_how_did_you_hears)->delete();
                }
            } else {
                \HowDidYouHear::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.how_did_you_hear.index')
                ->with('success', 'How Did You Hear list has been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.how_did_you_hear.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
