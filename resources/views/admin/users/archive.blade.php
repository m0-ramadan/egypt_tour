@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title')
    user
@endsection

@section('content')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>users</h5>

                <a class="btn btn-success" href="{{ route('admin.user.create') }}">Add </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>email</th>
                                <th>phone</th>
                                <th>phone2</th>
                                <th>id_number</th>
                                <th>address</th>
                                <th>wallet</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $key => $user)
                                <tr>
                                    <td> {{ ++$key }} </td>
                                    <td>{{ $user->name }} </td>
                                    <td>{{ $user->email }} </td>
                                    <td>{{ $user->phone }} </td>
                                    <td>{{ $user->phone2 }} </td>
                                    <td>{{ $user->id_number }} </td>
                                    <td>{{ $user->address }}</td>
                                    <td>{{ $user->wallet }}</td>

                                    <td>



                                        <a class="btn btn-primary mb-5"
                                            href="{{ route('admin.user.restore', $user->id) }}">Restore</a>



                                        <br>
                                        <form method="post" action="{{ route('admin.user.forcedelete', $user->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-primary">forceDelete</button>
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
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                pagingType: 'full_numbers',
                dom: 'lBfrtip',
                buttons: [{
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: [0, 1, 2, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: [0, 1, 2, 4, 5, 6, 7, 8]
                        }
                    },

                    {
                        extend: 'print',
                        exportOptions: {
                            columns: [0, 1, 2, 4, 5, 6, 7, 8]
                        }
                    },
                    'colvis'
                ],


            });
        });
    </script>
@endsection
