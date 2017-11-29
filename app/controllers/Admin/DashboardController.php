<?php

namespace Admin;

use View;

class DashboardController extends BaseController
{
    public function showIndex()
    {
        $this->layout->content = View::make('admin/dashboard/index');
    }
}
