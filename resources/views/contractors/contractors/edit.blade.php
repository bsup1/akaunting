@extends('layouts.admin')

@section('title', trans('general.title.edit', ['type' => trans_choice('general.contractors', 1)]))

@section('content')
<!-- Default box -->
<div class="box box-success">
    {!! Form::model($contractor, [
        'method' => 'PATCH',
        'files' => true,
        'url' => ['contractors/contractors', $contractor->id],
        'role' => 'form'
    ]) !!}

    <div class="box-body">
        {{ Form::textGroup('name', trans('general.name'), 'id-card-o') }}

        {{ Form::textGroup('email', trans('general.email'), 'envelope', []) }}

        {{ Form::textGroup('tax_number', trans('general.tax_number'), 'percent', []) }}

        {{ Form::selectGroup('currency_code', trans_choice('general.currencies', 1), 'exchange', $currencies) }}

        {{ Form::textGroup('phone', trans('general.phone'), 'phone', []) }}

        {{ Form::textGroup('website', trans('general.website'), 'globe',[]) }}

        {{ Form::textareaGroup('address', trans('general.address')) }}

        {{ Form::fileGroup('logo', trans_choice('general.logos', 1)) }}

        {{ Form::radioGroup('enabled', trans('general.enabled')) }}

        <div  id="contractor-create-user" class="form-group col-md-12 margin-top">
            @if ($contractor->user_id)
                <strong>{{ trans('contractors.user_created') }}</strong> &nbsp; {{ Form::checkbox('create_user', '1', 1, ['id' => 'create_user', 'disabled' => 'disabled']) }}
            @else
                <strong>{{ trans('contractors.allow_login') }}</strong> &nbsp; {{ Form::checkbox('create_user', '1', null, ['id' => 'create_user']) }}
            @endif
        </div>
    </div>
    <!-- /.box-body -->

    @permission('update-contractors-contractors')
    <div class="box-footer">
        {{ Form::saveButtons('contractors/contractors') }}
    </div>
    <!-- /.box-footer -->
    @endpermission

    {!! Form::close() !!}
</div>
@endsection

@push('js')
    <script src="{{ asset('vendor/almasaeed2010/adminlte/plugins/iCheck/icheck.min.js') }}"></script>
    <script src="{{ asset('public/js/bootstrap-fancyfile.js') }}"></script>
@endpush

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/almasaeed2010/adminlte/plugins/iCheck/square/green.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/bootstrap-fancyfile.css') }}">
@endpush

@push('scripts')
    <script type="text/javascript">
        var text_yes = '{{ trans('general.yes') }}';
        var text_no = '{{ trans('general.no') }}';

        $(document).ready(function(){
            $("#currency_code").select2({
                placeholder: "{{ trans('general.form.select.field', ['field' => trans_choice('general.currencies', 1)]) }}"
            });

            $('#create_user').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
                increaseArea: '20%' // optional
            });

            $('#create_user').on('ifClicked', function (event) {
                $('input[name="user_id"]').remove();

                if ($(this).prop('checked')) {
                    $('.col-md-6.password').remove();

                    $('input[name="email"]').parent().parent().removeClass('has-error');
                    $('input[name="email"]').parent().parent().find('.help-block').remove();
                } else {
                    var email = $('input[name="email"]').val();

                    if (!email) {
                        $('input[name="email"]').parent().parent().removeClass('has-error');
                        $('input[name="email"]').parent().parent().find('.help-block').remove();

                        $('input[name="email"]').parent().parent().addClass('has-error');
                        $('input[name="email"]').parent().after('<p class="help-block">{{ trans('validation.required', ['attribute' => 'email']) }}</p>');
                        $('input[name="email"]').focus();

                        unselect();

                        return false;
                    }

                    $.ajax({
                        url: '{{ url("auth/users/autocomplete") }}',
                        type: 'GET',
                        dataType: 'JSON',
                        data: {column: 'email', value: $('input[name="email"]').val()},
                        beforeSend: function() {
                            $('.iCheck-helper').parent().after('<i class="fa fa-spinner fa-pulse fa-fw loading" style="margin-left: 10px;"></i>');

                            $('input[name="email"]').parent().parent().removeClass('has-error');
                            $('input[name="email"]').parent().parent().find('.help-block').remove();

                            $('.box-footer .btn').attr('disabled', true);
                        },
                        success: function(json) {
                            if (json['errors']) {
                                if (json['data']) {
                                    $('input[name="email"]').parent().parent().addClass('has-error');
                                    $('input[name="email"]').parent().after('<p class="help-block">' + json['data'] + '</p>');
                                    $('input[name="email"]').focus();

                                    return false;
                                }

                                fields = [];

                                fields[0] = 'password';
                                fields[1] = 'password_confirmation';

                                $.ajax({
                                    url: '{{ url("contractors/contractors/field") }}',
                                    type: 'POST',
                                    dataType: 'JSON',
                                    data: {fields: fields},
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    complete: function() {
                                        $('.box-footer .btn').attr('disabled', false);
                                        $('.loading').remove();
                                    },
                                    success: function(json) {
                                        $('#contractor-create-user').after(json['html']);
                                    }
                                });
                            }

                            if (json['success']) {
                                $('input[name="password_confirmation"]').after('<input name="user_id" type="hidden" value="' + json['data']['id'] + '" id="user-id">');
                            }
                        }
                    });
                }
            });

            $('#logo').fancyfile({
                text  : '{{ trans('general.form.select.file') }}',
                style : 'btn-default',
                @if($contractor->logo)
                placeholder : '<?php echo $contractor->logo->basename; ?>'
                @else
                placeholder : '{{ trans('general.form.no_file_selected') }}'
                @endif
            });

            @if($contractor->logo)
                logo_html  = '<span class="logo">';
            logo_html += '    <a href="{{ url('uploads/' . $contractor->logo->id . '/download') }}">';
            logo_html += '        <span id="download-logo" class="text-primary">';
            logo_html += '            <i class="fa fa-file-{{ $contractor->logo->aggregate_type }}-o"></i> {{ $contractor->logo->basename }}';
            logo_html += '        </span>';
            logo_html += '    </a>';
            logo_html += '    {!! Form::open(['id' => 'logo-' . $contractor->logo->id, 'method' => 'DELETE', 'url' => [url('uploads/' . $contractor->logo->id)], 'style' => 'display:inline']) !!}';
            logo_html += '    <a id="remove-logo" href="javascript:void();">';
            logo_html += '        <span class="text-danger"><i class="fa fa fa-times"></i></span>';
            logo_html += '    </a>';
            logo_html += '    {!! Form::close() !!}';
            logo_html += '</span>';

            $('.fancy-file .fake-file').append(logo_html);

            $(document).on('click', '#remove-logo', function (e) {
                confirmDelete("#logo-{!! $contractor->logo->id !!}", "{!! trans('general.attachment') !!}", "{!! trans('general.delete_confirm', ['name' => '<strong>' . $contractor->logo->basename . '</strong>', 'type' => strtolower(trans('general.attachment'))]) !!}", "{!! trans('general.cancel') !!}", "{!! trans('general.delete')  !!}");
            });
            @endif
        });

        function unselect() {
            setTimeout(function(){
                $('#create_user').iCheck('uncheck');
            }, 550);
        }
    </script>
@endpush
