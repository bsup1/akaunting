<?php

namespace App\Http\Controllers\Contractors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contractor\Contractor as Request;
use App\Models\Contractor\Contractor;
use App\Traits\Uploads;
use Illuminate\Http\Request as FRequest;
use App\Models\Auth\User;
use App\Models\Setting\Currency;
use App\Utilities\ImportFile;

class Contractors extends Controller
{
    use Uploads;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $contractors = Contractor::collect();

        return view('contractors.contractors.index', compact('contractors', 'emails'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $currencies = Currency::enabled()->pluck('name', 'code');

        return view('contractors.contractors.create', compact('currencies'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (empty($request->input('create_user'))) {
            if (empty($request['email'])) {
                $request['email'] = '';
            }

            $contractor = Contractor::create($request->all());
        } else {
            // Check if user exist
            $user = User::where('email', $request['email'])->first();
            if (!empty($user)) {
                $message = trans('messages.error.contractor', ['name' => $user->name]);

                flash($message)->error();

                return redirect()->back()->withInput($request->except('create_user'))->withErrors(
                    ['email' => trans('contractors.error.email')]
                );
            }

            // Create user first
            $data = $request->all();
            $data['locale'] = setting('general.default_locale', 'en-GB');

            $user = User::create($data);
            $user->roles()->attach(['3']);
            $user->companies()->attach([session('company_id')]);

            // Finally create contractor
            $request['user_id'] = $user->id;

            $contractor = Contractor::create($request->all());
        }

        // Upload logo
        if ($request->file('logo')) {
            $media = $this->getMedia($request->file('logo'), 'contractors');

            $contractor->attachMedia($media, 'logo');
        }

        $message = trans('messages.success.added', ['type' => trans_choice('general.contractors', 1)]);

        flash($message)->success();

        return redirect('contractors/contractors');
    }

    /**
     * Duplicate the specified resource.
     *
     * @param  Contractor  $contractor
     *
     * @return Response
     */
    public function duplicate(Contractor $contractor)
    {
        $clone = $contractor->duplicate();

        $message = trans('messages.success.duplicated', ['type' => trans_choice('general.contractors', 1)]);

        flash($message)->success();

        return redirect('contractors/contractors/' . $clone->id . '/edit');
    }

    /**
     * Import the specified resource.
     *
     * @param  ImportFile  $import
     *
     * @return Response
     */
    public function import(ImportFile $import)
    {
        $rows = $import->all();

        foreach ($rows as $row) {
            $data = $row->toArray();

            if (empty($data['email'])) {
                $data['email'] = '';
            }

            $data['company_id'] = session('company_id');

            Contractor::create($data);
        }

        $message = trans('messages.success.imported', ['type' => trans_choice('general.contractors', 2)]);

        flash($message)->success();

        return redirect('contractors/contractors');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Contractor  $contractor
     *
     * @return Response
     */
    public function edit(Contractor $contractor)
    {
        $currencies = Currency::enabled()->pluck('name', 'code');

        return view('contractors.contractors.edit', compact('contractor', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Contractor  $contractor
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Contractor $contractor, Request $request)
    {
        if (empty($request->input('create_user'))) {
            if (empty($request['email'])) {
                $request['email'] = '';
            }

            $contractor->update($request->all());
        } else {
            // Check if user exist
            $user = User::where('email', $request['email'])->first();
            if (!empty($user)) {
                $message = trans('messages.error.contractor', ['name' => $user->name]);

                flash($message)->error();

                return redirect()->back()->withInput($request->except('create_user'))->withErrors(
                    ['email' => trans('contractors.error.email')]
                );
            }

            // Create user first
            $user = User::create($request->all());
            $user->roles()->attach(['3']);
            $user->companies()->attach([session('company_id')]);

            $request['user_id'] = $user->id;

            $contractor->update($request->all());
        }

        // Upload logo
        if ($request->file('logo')) {
            $media = $this->getMedia($request->file('logo'), 'contractors');

            $contractor->attachMedia($media, 'logo');
        }

        $message = trans('messages.success.updated', ['type' => trans_choice('general.contractors', 1)]);

        flash($message)->success();

        return redirect('contractors/contractors');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Contractor  $contractor
     *
     * @return Response
     */
    public function destroy(Contractor $contractor)
    {
        $relationships = $this->countRelationships($contractor, [
            'invoices' => 'invoices',
            'revenues' => 'revenues',
            'bills' => 'bills',
            'payments' => 'payments',
        ]);

        if (empty($relationships)) {
            $contractor->delete();

            $message = trans('messages.success.deleted', ['type' => trans_choice('general.contractors', 1)]);

            flash($message)->success();
        } else {
            $message = trans('messages.warning.deleted', ['name' => $contractor->name, 'text' => implode(', ', $relationships)]);

            flash($message)->warning();
        }

        return redirect('contractors/contractors');
    }

    public function currency()
    {
        $contractor_id = request('contractor_id');

        $contractor = Contractor::find($contractor_id);

        return response()->json($contractor);
    }

    public function contractor(Request $request)
    {
        if (empty($request['email'])) {
            $request['email'] = '';
        }

        $contractor = Contractor::create($request->all());

        return response()->json($contractor);
    }

    public function field(FRequest $request)
    {
        $html = '';

        if ($request['fields']) {
            foreach ($request['fields'] as $field) {
                switch ($field) {
                    case 'password':
                        $html .= \Form::passwordGroup('password', trans('auth.password.current'), 'key', [], null, 'col-md-6 password');
                        break;
                    case 'password_confirmation':
                        $html .= \Form::passwordGroup('password_confirmation', trans('auth.password.current_confirm'), 'key', [], null, 'col-md-6 password');
                        break;
                }
            }
        }

        $json = [
            'html' => $html
        ];

        return response()->json($json);
    }
}
