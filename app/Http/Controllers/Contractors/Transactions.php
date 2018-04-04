<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\Models\Banking\Transaction;

use Auth;

class Transactions extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $transactions = Transaction::getUserTransactions(Auth::user()->contractor->id, 'revenues');

        return view('contractors.transactions.index', compact('transactions'));
    }
}
