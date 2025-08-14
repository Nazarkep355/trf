<?php

namespace App\Http\Sections;

use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Section;

class WhiteUserPage extends Section implements Initializable
{

    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-lightbulb-o');
    }

    public function onDisplay($payload = []) {

    }
}
