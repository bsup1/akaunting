<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\View\View;
use anlutro\LaravelSettings\Facade as Settingg;

class Menu
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $contractor = null;
        $company_id = session('company_id');

        // Get all companies
        $companies = Auth::user()->companies()->limit(10)->get()->sortBy('name');
        foreach ($companies as $com) {
            $com->setSettings();
        }

        // Get contractor
        if (Auth::user()->contractor) {
            $contractor = Auth::user();
        }

        $view->with(['companies' => $companies, 'contractor' => $contractor]);
    }
}
