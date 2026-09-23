@include('admin.i18n.locale')
@extends('admin.layout.master')

@section('title', admin_t('View Article'))

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

        .profile-card {
            background: var(--dark-card);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, .1);
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .3);
        }

        .profile-header {
            background: var(--primary-gradient);
            padding: 30px;
            color: #fff;
        }

        .profile-body {
            padding: 30px;
        }

        .info-box {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
        }

        .info-label {
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #fff;
            font-weight: 600;
        }

        .content-box {
            white-space: pre-wrap;
            line-height: 1.9;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.articles.index') }}">Articles</a></li>
                <li class="breadcrumb-item active">View Article</li>
            </ol>
        </nav>

        <div class="profile-card">
            <div class="profile-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">{{ adminTrans($article->title) ?: 'Without Title' }}</h4>
                    <small class="opacity-75">{{ $article->slug ?? '-' }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-light">Edit</a>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-light">Back</a>
                </div>
            </div>

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Type</div>
                            <div class="info-value">{{ $article->article_type ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Status</div>
                            <div class="info-value">{{ $article->is_active ?? true ? 'Published' : 'Unpublished' }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="info-box">
                            <div class="info-label">Featured Status</div>
                            <div class="info-value">{{ $article->is_featured ?? false ? 'Featured' : 'Standard' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Publish Date</div>
                            <div class="info-value">
                                {{ optional($article->published_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">Created At</div>
                            <div class="info-value">
                                {{ optional($article->created_at)->translatedFormat('d M Y - h:i A') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Short Description</div>
                            <div class="info-value content-box">{!! adminTrans($article->excerpt) ?: 'No short description' !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="info-box">
                            <div class="info-label">Content</div>
                            <div class="info-value content-box">{!! adminTrans($article->content) ?: 'No content' !!}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Title</div>
                            <div class="info-value">{{ adminTrans($article->seo_title) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label">SEO Description</div>
                            <div class="info-value content-box">{{ adminTrans($article->seo_description) ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <div class="info-label"> Article Image </div>
                            <div class="info-value content-box">
                                @if ($article->featured_image)
                                    <img src="{{ asset('storage/' . $article->featured_image) }}"
                                        style="width:150px;height:150px;object-fit:cover;border-radius:10px;">
                                @else
                                    No Image
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
