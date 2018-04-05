<?php

namespace App\Http\Middleware;

use App\Events\ContractorMenuCreated;
use Auth;
use Closure;
use Menu;

class ContractorMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if logged in
        if (!Auth::check()) {
            return $next($request);
        }

        Menu::create('ContractorMenu', function ($menu) {
            $menu->style('adminlte');

            $user = Auth::user();

            // Dashboard
            $menu->add([
                'url'   => 'contractors/',
                'title' => trans('general.dashboard'),
                'icon'  => 'fa fa-dashboard',
                'order' => 1,
            ]);

            // Invoices
            $menu->add([
                'url'   => 'contractors/invoices',
                'title' => trans_choice('general.invoices', 2),
                'icon'  => 'fa fa-wpforms',
                'order' => 2,
            ]);

            // Payments
            $menu->add([
                'url'   => 'contractors/payments',
                'title' => trans_choice('general.payments', 2),
                'icon'  => 'fa fa-money',
                'order' => 3,
            ]);

            // Payments
            $menu->add([
                'url'   => 'contractors/transactions',
                'title' => trans_choice('general.transactions', 2),
                'icon'  => 'fa fa-list',
                'order' => 4,
            ]);

            // Fire the event to extend the menu
            event(new ContractorMenuCreated($menu));
        });

        return $next($request);
    }
}
