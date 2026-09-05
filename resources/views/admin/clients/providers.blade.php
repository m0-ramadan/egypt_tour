@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('المتاجر'))

@section('css')
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatables.css') }}">
    <!-- Plugins css Ends-->
@endsection

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">

                {{-- <x-breadcrumb :items="[
                    'لوحة التحكم' => '/admin',
                    'حسابات التجار' => '',
                ]" /> --}}

            </div>

            <div class="card-header">
                <h5>التجار</h5>
                {{-- <a class="btn btn-success" href="{{ route('admin.employees.create') }}">Add Employees</a> --}}
                <a class="btn btn-success" href="{{ route('admin.client.sendnotifications', ['t' => 2]) }}">ارسل اشعارات
                    لكل</a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="display" id="basic-1">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الأسم</th>
                                <th>الأيميل</th>
                                <th>التليفون</th>
                                <th>المخزن</th>
                                <th>عدد المنتجات</th>
                                <th>لحالة</th>
                                <th>العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $key => $client)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->product_count }}</td>
                                    <td>{{ $client->product_sum }}</td>

                                    <td class="d-flex gap-1">

                                        <a href="{{ route('admin.client.changeStatus', $client->id) }}"
                                            class="btn btn-sm btn-outline-primary">

                                            @csrf
                                            @method('PATCH')
                                            {{ $client->status_label }}
                                        </a>

                                    </td>
                                    <td>
                                        <a href="{{ route('admin.employees.edit', $client->id) }}" class="btn btn-success">
                                            <i class="fa fa-edit text-white"></i>
                                        </a>
                                        <button class="btn btn-success"> <a
                                                href="{{ route('admin.client.sendNotificationToUser', $client->id) }}"><i
                                                    class="fa fa-bell  text-white"></i> </a> </button>

                                        <form method="POST" action="{{ route('admin.client.destroy', $client->id) }}"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this transfer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Plugins JS start-->
    <script src="{{ asset('assets/js/datatable/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatable/datatables/datatable.custom.js') }}"></script>
    <script src="{{ asset('assets/js/tooltip-init.js') }}"></script>
    <!-- Plugins JS Ends-->
@endsection
