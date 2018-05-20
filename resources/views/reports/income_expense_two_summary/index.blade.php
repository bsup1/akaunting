@extends('layouts.admin')

@section('title', trans_choice('reports.summary.income_expense_two', 1))

@section('content')
<!-- Default box -->
<div class="box box-success">
    <div class="box-header">
        {!! Form::open(['url' => 'reports/income-expense-two-summary', 'role' => 'form', 'method' => 'GET']) !!}
        <div class="pull-left" style="margin-left: 23px">
            {!! Form::label('contractor', trans_choice('general.contractors', 1).': ', ['class' => 'control-label']) !!}
            {!! Form::select('contractor', $contractors, request('contractor'), ['class' => 'form-control input-filter input-sm', 'onchange' => 'this.form.submit()']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    <div class="box-body">
        <div class="table table-responsive">
            <table class="table table-bordered table-striped table-hover" id="tbl-payments">
                <thead>
                    <tr>
                        <th>{{ trans_choice('general.categories', 1) }}</th>
                        <th>{{ trans_choice('general.incomes', 2) }}</th>
                        <th>{{ trans_choice('general.payments', 2) }}</th>
                        <th>{{ trans_choice('general.payments_in_dollar', 2) }}</th>
                        <th>{{ trans_choice('general.remaining_payments', 2) }}</th>
                    </tr>
                </thead>
                <tbody>
                @if ($compares)
                    @foreach($compares as $category_id => $category)
                        <tr>
                            <th>{{ $category['name'] }}</th>
                            <td class="text-right">@money($category['incomes']['amount'], $category['incomes']['currency_code'], true)</td>
                            <td class="text-right">@money($category['payments']['amount'], $category['payments']['currency_code'], true)</td>
                            <td class="text-right">@money($category['payments_in_dollar']['amount'], $category['payments_in_dollar']['currency_code'], true)</td>
                            <td class="text-right">@money($category['remaining_payments']['amount'], $category['remaining_payments']['currency_code'], true)</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5">
                            <h5 class="text-center">{{ trans('general.no_records') }}</h5>
                        </td>
                    </tr>
                @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th>{{ trans_choice('general.totals', 1) }}</th>
                        <th class="text-right">
                            <span>@money($totals['incomes']['amount'], $totals['incomes']['currency_code'], true)</span>
                        </th>
                        <th class="text-right">
                            <span>@money($totals['payments']['amount'], $totals['payments']['currency_code'], true)</span>
                        </th>
                        <th class="text-right">
                            <span>@money($totals['payments_in_dollar']['amount'], $totals['payments_in_dollar']['currency_code'], true)</span>
                        </th>
                        <th class="text-right">
                            <span>@money($totals['remaining_payments']['amount'], $totals['remaining_payments']['currency_code'], true)</span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
@endsection
