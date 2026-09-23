@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Activities User'))

@section('css')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .wrapper-card {
            background: var(--dark-card);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .wrapper-header {
            background: var(--primary-gradient);
            padding: 25px 30px;
            color: #fff;
        }

        .activity-row {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Activities User</li>
            </ol>
        </nav>

        <div class="wrapper-card">
            <div class="wrapper-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Activities {{ $user->name ?? 'User' }}</h5>
                    <small class="opacity-75">View All Activities Related With This User</small>
                </div>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-light">Back</a>
            </div>

            <div class="p-4">
                @forelse ($activities as $activity)
                    <div class="activity-row">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>Type:</strong>
                                <div>{{ $activity->type ?? ($activity->action ?? '-') }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Description:</strong>
                                <div>{{ $activity->description ?? '-' }}</div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Date:</strong>
                                <div>{{ optional($activity->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">No There are Activities For this User</div>
                @endforelse

                @if (method_exists($activities, 'links'))
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
