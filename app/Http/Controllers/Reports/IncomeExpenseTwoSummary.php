<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Income\Revenue;
use App\Models\Expense\Payment;
use App\Models\Setting\Category;
use App\Models\Contractor\Contractor;
use Charts;
use Date;

class IncomeExpenseTwoSummary extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $contractor = request('contractor');
        $contractors = Contractor::pluck('name', 'id')->toArray();
        $contractors[''] = trans('general.form.select.field', ['field' => trans('general.all')]);
        $categories = Category::enabled()->pluck('name', 'id')->toArray();
        $compares = [];
        $totals = [
            'incomes' => [
                'amount' => 0,
                'currency_code' => setting('general.default_currency')
            ],
            'payments' => [
                'amount' => 0,
                'currency_code' => setting('general.default_currency')
            ],
            'payments_in_dollar' => [
                'amount' => 0,
                'currency_code' => 'CAD'
            ],
            'remaining_payments' => [
                'amount' => 0,
                'currency_code' => setting('general.default_currency')
            ]
        ];

        foreach($categories as $category_id => $category)
        {
            $incomes = 0;
            foreach (Revenue::where('category_id', $category_id)->when($contractor, function ($query) use ($contractor) {
                return $query->where('customer_id', $contractor);
            })->get() as $revenue) {
                $incomes += $revenue->getConvertedAmount();
            }

            $payments = 0;
            $paymentsInDollar = 0;
            foreach (Payment::where('category_id', $category_id)->when($contractor, function ($query) use ($contractor) {
                return $query->where('vendor_id', $contractor);
            })->get() as $payment) {
                $payments += $payment->getConvertedAmount();
                $paymentsInDollar += $payment->cad_amount;
            }

            $compares[$category_id] = [
                'name' => $category,
                'incomes' => [
                    'amount' => $incomes,
                    'currency_code' => setting('general.default_currency')
                ],
                'payments' => [
                    'amount' => $payments,
                    'currency_code' => setting('general.default_currency')
                ],
                'payments_in_dollar' => [
                    'amount' => $paymentsInDollar,
                    'currency_code' => 'CAD'
                ],
                'remaining_payments' => [
                    'amount' => $incomes - $payments,
                    'currency_code' => setting('general.default_currency')
                ]
            ];

            $totals['incomes']['amount'] += $incomes;
            $totals['payments']['amount'] += $payments;
            $totals['payments_in_dollar']['amount'] += $paymentsInDollar;
            $totals['remaining_payments']['amount'] += $incomes - $payments;
        }

        return view('reports.income_expense_two_summary.index', compact('contractors', 'compares', 'totals'));
    }
}
