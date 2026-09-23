@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('Contact Messages'))

@section('css')

    <style>
        :root {
            --primary-color: #696cff;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #20c997;
            --danger-color: #fd7e14;
            --info-color: #0c63e4;
            --warning-color: #ffc107;
            --dark-bg: #1e1e2d;
            --dark-card: #2b3b4c;
        }

        body {
            font-family: "Cairo", sans-serif !important;
            background: var(--dark-bg);
            color: #fff;
        }

        .main-card {
            background: var(--dark-card);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
        }

        .main-header {
            background: var(--primary-gradient);
            color: #fff;
            padding: 25px 30px;
        }

        .stats-card {
            background: var(--dark-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .3);
            border-top: 4px solid var(--primary-color);
            transition: transform .3s ease;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stats-label {
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
        }

        .filter-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, .1);
        }

        .item-card {
            background: rgba(255, 255, 255, .05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            transition: all .3s ease;
        }

        .item-card:hover {
            background: rgba(105, 108, 255, .1);
            border-color: var(--primary-color);
        }

        .badge-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .status-new {
            background: rgba(255, 193, 7, .2);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, .3);
        }

        .status-replied {
            background: rgba(32, 201, 151, .2);
            color: #20c997;
            border: 1px solid rgba(32, 201, 151, .3);
        }

        .status-closed {
            background: rgba(12, 99, 228, .2);
            color: #0c63e4;
            border: 1px solid rgba(12, 99, 228, .3);
        }
    </style>
@endsection

@section('content')
    @php
        $totalMessages = $contacts->total() ?? $contacts->count();
        $newMessages = \App\Models\Inquiry::where('status', 'new')->count();
        $repliedMessages = \App\Models\Inquiry::where('status', 'replied')->count();
        $closedMessages = \App\Models\Inquiry::where('status', 'closed')->count();
    @endphp

    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Contact Us</li>
            </ol>
        </nav>

        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon" style="background: var(--primary-gradient); color:#fff;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stats-number">{{ number_format($totalMessages) }}</div>
                    <div class="stats-label">Total Messages</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(255,193,7,.2); color:#ffc107; border:1px solid rgba(255,193,7,.3);">
                        <i class="fas fa-circle-exclamation"></i>
                    </div>
                    <div class="stats-number">{{ number_format($newMessages) }}</div>
                    <div class="stats-label">Messages New</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(32,201,151,.2); color:#20c997; border:1px solid rgba(32,201,151,.3);">
                        <i class="fas fa-reply"></i>
                    </div>
                    <div class="stats-number">{{ number_format($repliedMessages) }}</div>
                    <div class="stats-label">Was Reply On It</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-4">
                <div class="stats-card">
                    <div class="stats-icon"
                        style="background: rgba(12,99,228,.2); color:#0c63e4; border:1px solid rgba(12,99,228,.3);">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stats-number">{{ number_format($closedMessages) }}</div>
                    <div class="stats-label">Closed</div>
                </div>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET" action="{{ route('admin.contact-us.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Search By name Or Email Or Subject">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>new</option>
                            <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>replied</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>closed</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from" value="{{ request('from') }}">
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-primary w-100" type="submit">Filter</button>
                        <a href="{{ route('admin.contact-us.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-card">
            <div class="main-header">
                <h5 class="mb-0">Messages Communication Us List</h5>
                <small class="opacity-75">Manage Messages Incoming From Location</small>
            </div>

            <div class="p-4">
                @forelse($contacts as $contact)
                    @php
                        $phoneNum = $contact->phone ?? null;
                        $cleanPhone = $phoneNum ? preg_replace('/[^0-9]/', '', $phoneNum) : null;
                    @endphp
                    <div class="item-card">
                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                            <div>
                                <h6 class="mb-1">{{ $contact->subject ?? 'Without Title' }}</h6>
                                <small class="text-light opacity-75">
                                    {{ $contact->name ?? '-' }}
                                    @if(!empty($contact->email))
                                        - <a href="mailto:{{ $contact->email }}" class="text-info text-decoration-none" title="Message via Email">
                                            <i class="fas fa-envelope text-primary me-1"></i>{{ $contact->email }}
                                        </a>
                                    @endif
                                </small>
                            </div>

                            <span class="badge-status status-{{ $contact->status ?? 'new' }}">
                                {{ $contact->status ?? 'new' }}
                            </span>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-5">
                                <strong>Phone:</strong>
                                @if($phoneNum)
                                    <span class="dir-ltr d-inline-block font-monospace mx-1">{{ $phoneNum }}</span>
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-success rounded-circle px-2 py-1 me-1" title="Message via WhatsApp">
                                        <i class="fab fa-whatsapp fs-6"></i>
                                    </a>
                                    <a href="tel:{{ $phoneNum }}" class="btn btn-sm btn-info rounded-circle px-2 py-1" title="Phone Call">
                                        <i class="fas fa-phone-alt fs-6"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                            <div class="col-md-4"><strong>Date:</strong>
                                {{ optional($contact->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                            <div class="col-md-3"><strong>Number Log:</strong> #{{ $contact->id }}</div>
                        </div>

                        <div class="mb-3">
                            <strong>Message:</strong>
                            {{ \Illuminate\Support\Str::limit($contact->message ?? '-', 220) }}
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.contact-us.show', $contact) }}" class="btn btn-info btn-sm">View</a>

                            @if (Route::has('admin.contact-us.reply'))
                                <form action="{{ route('admin.contact-us.reply', $contact) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-success btn-sm" type="submit">Reply</button>
                                </form>
                            @endif

                            @if (Route::has('admin.contact-us.status'))
                                <form action="{{ route('admin.contact-us.status', $contact) }}" method="POST"
                                    class="d-flex gap-2">
                                    @csrf
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="new">new</option>
                                        <option value="replied">replied</option>
                                        <option value="closed">closed</option>
                                    </select>
                                    <button class="btn btn-dark btn-sm" type="submit">Update</button>
                                </form>
                            @endif

                            <form action="{{ route('admin.contact-us.destroy', $contact) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">No Messages available</div>
                @endforelse

                @if (method_exists($contacts, 'links'))
                    <div class="mt-4">{{ $contacts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
