<?php

namespace Admin;

use View;

class MemberCompletedRequiredVisitDatesController extends BaseController
{

    public function index()
    {
        $member_completed_required_visit_dates = \MemberCompletedRequiredVisitDate::all();
        $this->layout->content = View::make('admin/general_settings/member_completed_required_visit_dates')
            ->with('member_completed_required_visit_dates', $member_completed_required_visit_dates)
            ->with('route', array('admin.member_completed_required_visit_dates.update'))
            ->with('method', 'POST');
    }

    public function update()
    {
        $member_completed_required_visit_dates = \Input::get('member_completed_required_visit_dates');
        $member_completed_required_visit_dates_ids = \Input::get('member_completed_required_visit_dates_ids');

        try {
            if (isset($member_completed_required_visit_dates)) {
                $i = 0;
                $updated_member_completed_required_visit_dates = [];
                foreach ($member_completed_required_visit_dates as $item) {
                    if (strlen($item) < 1) {
                        continue;
                    }
                    if ($member_completed_required_visit_dates_ids[$i] != 0) {
                        $obj = \MemberCompletedRequiredVisitDate::find($member_completed_required_visit_dates_ids[$i]);
                    } else {
                        $obj = new \MemberCompletedRequiredVisitDate();
                    }
                    $obj->title = $item;
                    $obj->save();
                    $updated_member_completed_required_visit_dates[] = $obj->id;
                    $i++;
                }
                if (count($updated_member_completed_required_visit_dates)) {
                    \MemberCompletedRequiredVisitDate::whereNotIn('id', $updated_member_completed_required_visit_dates)->delete();
                }
            } else {
                \MemberCompletedRequiredVisitDate::whereNotIn('id', [])->delete();
            }

            return \Redirect::route('admin.member_completed_required_visit_dates.index')
                ->with('success', 'Member Completed Required Visit Dates have been successfully updated.');

        } catch (\Exception $e) {
            return \Redirect::route('admin.member_completed_required_visit_dates.index')
                ->with('error', 'An error has occurred.');
        }


    }

}
