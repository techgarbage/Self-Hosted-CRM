@extends('layouts.master')
@section('heading')
    <span>
        {{__('All clients')}}
        <input type="button" class="btn btn-danger" onclick="location.href = '/clients/excel';" value="Export excel"/>
    </span>
    <span>
        {!! Form::open([
            'url' => '/clients/importExcel',
            'files'=>true,
            'enctype' => 'multipart/form-data',
            'class' => 'btn btn-info'
            ])
        !!}
            {!! Form::file('file',  null, ['class' => '']) !!}
            {!! Form::submit('Import excel', ['class' => 'btn btn-success pull-right']) !!}
        {!! Form::close() !!}
    </span>
@stop

@section('content')
    <hr/>

    <table class="table table-hover " id="clients-table">
        <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Mail') }}</th>
            <th>{{ __('Number') }}</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
    </table>

@stop

@push('scripts')
<script>
    $(function () {
        $('#clients-table').DataTable({
            processing: true,
            serverSide: true,

            ajax: '{!! route('clients.data') !!}',
            columns: [

                {data: 'namelink', name: 'name'},
                {data: 'company_name', name: 'company_name'},
                {data: 'email', name: 'email'},
                {data: 'primary_number', name: 'primary_number'},
                @if(Entrust::can('client-update'))   
                { data: 'edit', name: 'edit', orderable: false, searchable: false},
                @endif
                @if(Entrust::can('client-delete'))   
                { data: 'delete', name: 'delete', orderable: false, searchable: false},
                @endif

            ]
        });
    });
</script>
@endpush
