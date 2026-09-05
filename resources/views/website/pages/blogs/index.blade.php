@extends('website.layouts.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $pageTitle = isset($category)
        ? ($category->display_title ?? ($category->title ?? ($category->name ?? __('Blog Category')))) .
            ' - ' .
            __('Etro Tours')
        : __('Blogs') . ' - ' . __('Etro Tours');

    $heroTitle = isset($category)
        ? $category->display_title ?? ($category->title ?? ($category->name ?? __('Travel Blog')))
        : __('Etro Tours Travel Blog');

    $heroSubtitle = isset($category)
        ? __('Discover useful travel articles, destination guides, and expert insights about :category.', [
            'category' => $category->display_title ?? ($category->title ?? ($category->name ?? __('Category'))),
        ])
        : __(
            'Discover the wonders of Egypt through our expert travel insights, destination guides, and cultural explorations.',
        );

    $blogsRoute = Route::has('website.blogs') ? route('website.blogs') : url('/blogs');
@endphp

@section('title', $pageTitle)
@section('description', $heroSubtitle)
@section('keywords',
    trim(
    collect([$heroTitle, 'Etro Tours blog', 'Egypt travel blog', 'destination guides'])->filter()->implode(', '),
    ', ',
    ))
@section('image', asset('website/photos/home2.webp'))

@section('css')
    <style>
        .hero-section {
            height: 60vh;
            min-height: 500px;
            max-height: 700px;
            background: linear-gradient(rgba(28, 50, 92, 0.5), rgba(26, 75, 102, 0.6)), url('{{ asset('website/photos/home2.webp') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 1400px) {
            .hero-section {
                height: 65vh;
                max-height: 750px;
            }
        }

        @media (max-width: 1399px) and (min-width: 1200px) {
            .hero-section {
                height: 60vh;
                min-height: 550px;
                max-height: 700px;
            }
        }

        @media (max-width: 1199px) and (min-width: 992px) {
            .hero-section {
                height: 55vh;
                min-height: 500px;
                max-height: 650px;
            }
        }

        @media (max-width: 991px) and (min-width: 768px) {
            .hero-section {
                height: 50vh;
                min-height: 450px;
                max-height: 600px;
                background-attachment: scroll;
            }
        }

        @media (max-width: 767px) and (min-width: 576px) {
            .hero-section {
                height: 45vh;
                min-height: 400px;
                max-height: 550px;
                background-attachment: scroll;
            }
        }

        @media (max-width: 575px) {
            .hero-section {
                height: 40vh;
                min-height: 350px;
                max-height: 500px;
                background-attachment: scroll;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                height: 35vh;
                min-height: 300px;
                max-height: 450px;
            }
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.4;
            animation: wave 20s ease-in-out infinite;
        }

        @keyframes wave {

            0%,
            100% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(-50px);
            }
        }

        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 100px 20px 0;
            animation: fadeInUp 1.2s ease-out;
        }

        @media (max-width: 991px) {
            .hero-content {
                padding: 90px 20px 0;
            }
        }

        @media (max-width: 767px) {
            .hero-content {
                padding: 80px 15px 0;
            }
        }

        @media (max-width: 575px) {
            .hero-content {
                padding: 70px 15px 0;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-badge {
            background: rgba(197, 149, 91, 0.9);
            color: var(--primary-navy, #1c325c);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(197, 149, 91, 0.3);
        }

        @media (max-width: 575px) {
            .hero-badge {
                padding: 8px 20px;
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #ffffff 0%, #f8f0e0 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
            font-weight: 300;
            letter-spacing: 1px;
            line-height: 1.6;
        }

        @media (max-width: 767px) {
            .hero-subtitle {
                font-size: 1.1rem;
                margin-bottom: 25px;
            }
        }

        @media (max-width: 575px) {
            .hero-subtitle {
                font-size: 1rem;
                margin-bottom: 20px;
            }
        }

        .breadcrumb-section {
            background: var(--pearl-luxury, #faf8f3);
            padding: 15px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.2);
        }

        .breadcrumb-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .breadcrumb {
            background: transparent;
            margin: 0;
            padding: 0;
        }

        .breadcrumb-item {
            color: var(--primary-navy, #1c325c);
            font-size: 0.95rem;
        }

        .breadcrumb-item a {
            color: var(--primary-navy, #1c325c);
            text-decoration: none;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .breadcrumb-item a:hover {
            color: var(--rich-gold, #c5955b);
        }

        .breadcrumb-icon {
            font-size: 1.1rem;
            color: var(--rich-gold, #c5955b);
        }

        .breadcrumb-item.active {
            color: var(--rich-gold, #c5955b);
            font-weight: 600;
        }

        .blog-card-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
        }

        .modern-blog-card {
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: var(--shadow-medium, 0 8px 30px rgba(28, 50, 92, .12));
            transition: all 0.4s ease;
            border: 1px solid rgba(197, 149, 91, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .modern-blog-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-dramatic, 0 20px 45px rgba(28, 50, 92, .18));
        }

        .blog-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 4/3;
            flex-shrink: 0;
            background: #eee;
        }

        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .modern-blog-card:hover .blog-img {
            transform: scale(1.05);
        }

        .blog-overlay {
            position: absolute;
            inset: 0;
            background: rgba(28, 50, 92, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modern-blog-card:hover .blog-overlay {
            opacity: 1;
        }

        .overlay-content {
            color: white;
            text-align: center;
            font-weight: 600;
        }

        .overlay-content i {
            display: block;
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .blog-content-wrapper {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-navy, #1c325c);
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .blog-card-title a {
            color: inherit;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .blog-card-title a:hover {
            color: var(--rich-gold, #c5955b);
        }

        .blog-date {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 12px;
            background: var(--light-sand, #efe4d3);
            border-radius: 8px;
            color: var(--primary-navy, #1c325c);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .blog-date i {
            color: var(--rich-gold, #c5955b);
            font-size: 1.1rem;
            margin-right: 8px;
            flex-shrink: 0;
        }

        .blog-description {
            margin-bottom: 20px;
            flex: 1;
        }

        .blog-description p {
            color: var(--warm-gray, #777);
            line-height: 1.5;
            margin: 0;
            font-size: 0.95rem;
        }

        .blog-footer {
            padding-top: 15px;
            border-top: 1px solid rgba(197, 149, 91, 0.2);
            margin-top: auto;
        }

        .btn-blog {
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-gold, 0 8px 20px rgba(197, 149, 91, .25));
            width: 100%;
            justify-content: center;
        }

        .btn-blog:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(197, 149, 91, 0.4);
            color: var(--primary-navy, #1c325c);
        }

        .luxury-sidebar {
            background: white;
            border-radius: 20px;
            padding: 0;
            box-shadow: var(--shadow-medium, 0 8px 30px rgba(28, 50, 92, .12));
            border: 1px solid rgba(197, 149, 91, 0.15);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .sidebar-widget {
            padding: 25px;
            border-bottom: 1px solid rgba(197, 149, 91, 0.1);
        }

        .sidebar-widget:last-child {
            border-bottom: none;
        }

        .sidebar-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .sidebar-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--gradient-gold, #c5955b);
            border-radius: 2px;
        }

        .search-form {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 50px 12px 15px;
            border: 2px solid rgba(197, 149, 91, 0.2);
            border-radius: 25px;
            font-size: 0.95rem;
            background: var(--light-sand, #efe4d3);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--rich-gold, #c5955b);
            background: white;
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gradient-gold, #c5955b);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-navy, #1c325c);
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            transform: translateY(-50%) scale(1.1);
        }

        .category-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .category-tag {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(197, 149, 91, 0.1);
            color: var(--primary-navy, #1c325c);
            text-decoration: none;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(197, 149, 91, 0.2);
        }

        .category-tag:hover {
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            transform: translateY(-2px);
        }

        .popular-article {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.1);
            transition: all 0.3s ease;
        }

        .popular-article:last-child {
            border-bottom: none;
        }

        .popular-article:hover {
            background: rgba(197, 149, 91, 0.05);
            margin: 0 -15px;
            padding: 15px;
            border-radius: 10px;
        }

        .popular-img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #eee;
        }

        .popular-content h4 {
            font-size: 0.95rem;
            color: var(--primary-navy, #1c325c);
            margin-bottom: 5px;
            line-height: 1.3;
            font-weight: 700;
        }

        .popular-content h4 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .popular-content h4 a:hover {
            color: var(--rich-gold, #c5955b);
        }

        .popular-date {
            font-size: 0.8rem;
            color: var(--warm-gray, #777);
            margin: 0;
        }

        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            gap: 20px;
        }

        .pagination-wrapper .pagination {
            margin: 0;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-wrapper .page-link {
            color: var(--primary-navy, #1c325c);
            border: 1px solid rgba(197, 149, 91, 0.25);
            border-radius: 12px;
            margin: 0 2px;
            padding: 10px 14px;
            font-weight: 600;
            background: white;
        }

        .pagination-wrapper .page-item.active .page-link {
            background: var(--gradient-gold, #c5955b);
            border-color: var(--rich-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
        }

        .pagination-wrapper .page-link:hover {
            background: rgba(197, 149, 91, 0.15);
            color: var(--primary-navy, #1c325c);
        }

        .social-links {
            display: flex;
            gap: 10px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gradient-gold, #c5955b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-navy, #1c325c);
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(197, 149, 91, 0.3);
            color: var(--primary-navy, #1c325c);
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 35px;
            text-align: center;
            color: var(--warm-gray, #777);
            border: 1px solid rgba(197, 149, 91, 0.15);
            box-shadow: var(--shadow-medium, 0 8px 30px rgba(28, 50, 92, .12));
        }

        .why-choose-section {
            background: var(--pearl-luxury, #faf8f3);
            padding: 80px 0;
            position: relative;
            border-top: 1px solid rgba(197, 149, 91, 0.2);
        }

        .section-header {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-size: clamp(1.5rem, 3vw, 2.2rem);
            margin-bottom: 20px;
        }

        .section-subtitle {
            color: var(--warm-gray, #777);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 60px;
            line-height: 1.6;
        }

        .choose-card {
            background: white;
            border-radius: 25px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: var(--shadow-medium, 0 8px 30px rgba(28, 50, 92, .12));
            border: 2px solid transparent;
            transition: all 0.4s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .choose-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-dramatic, 0 20px 45px rgba(28, 50, 92, .18));
            border-color: var(--rich-gold, #c5955b);
        }

        .choose-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-gold, #c5955b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.2rem;
            color: white;
            box-shadow: var(--shadow-gold, 0 8px 20px rgba(197, 149, 91, .25));
            transition: all 0.3s ease;
        }

        .choose-card:hover .choose-icon {
            transform: scale(1.1);
        }

        .choose-title {
            font-family: 'Playfair Display', serif;
            color: var(--primary-navy, #1c325c);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .choose-features {
            text-align: left;
        }

        .feature-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(197, 149, 91, 0.2);
            color: var(--warm-gray, #777);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .luxury-cta-section {
            background: linear-gradient(135deg, var(--primary-navy, #1c325c), #1a4b66);
            padding: 70px 0;
            position: relative;
            overflow: hidden;
            border-top: 1px solid rgba(197, 149, 91, 0.3);
        }

        .luxury-cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" fill="none"><path d="M0 10L10 0L20 10L30 0L40 10L50 0L60 10L70 0L80 10L90 0L100 10V20H0V10Z" fill="rgba(197,149,91,0.1)"/></svg>') repeat-x;
            opacity: 0.3;
        }

        .luxury-cta-content {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 50px;
            border: 1px solid rgba(197, 149, 91, 0.3);
            box-shadow: var(--shadow-dramatic, 0 20px 45px rgba(28, 50, 92, .18));
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .cta-icon-container {
            width: 80px;
            height: 80px;
            background: var(--gradient-gold, #c5955b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--primary-navy, #1c325c);
            box-shadow: var(--shadow-gold, 0 8px 20px rgba(197, 149, 91, .25));
            flex-shrink: 0;
        }

        .cta-content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .cta-text-content {
            flex: 1;
            min-width: 300px;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }

        .trust-features {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .trust-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-size: 0.95rem;
        }

        .trust-feature i {
            color: var(--rich-gold, #c5955b);
            font-size: 1.1rem;
        }

        .luxury-cta-btn {
            background: var(--gradient-gold, #c5955b);
            color: var(--primary-navy, #1c325c);
            padding: 16px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-gold, 0 8px 20px rgba(197, 149, 91, .25));
            white-space: nowrap;
        }

        .luxury-cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(197, 149, 91, 0.4);
            color: var(--primary-navy, #1c325c);
        }

        .fixed-mobile-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .blog-card-area {
                padding: 60px 0;
            }

            .fixed-mobile-btn {
                display: block;
            }

            .pagination-wrapper {
                flex-direction: column;
            }

            .luxury-cta-content {
                padding: 40px;
            }

            .cta-title {
                font-size: 1.8rem;
            }

            .cta-content-wrapper {
                flex-direction: column;
                text-align: center;
            }

            .trust-features {
                justify-content: center;
            }
        }

        .mobile-enquiry-btn {
            background: #25d366;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .mobile-enquiry-btn:hover {
            background: #20b859;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
            color: white;
        }

        @media (max-width: 480px) {
            .luxury-cta-section {
                padding: 50px 0;
            }

            .luxury-cta-content {
                padding: 30px;
            }

            .cta-title {
                font-size: 1.6rem;
            }

            .cta-subtitle {
                font-size: 1rem;
            }

            .trust-feature {
                font-size: 0.9rem;
            }

            .luxury-cta-btn {
                padding: 14px 25px;
                font-size: 1rem;
                width: 100%;
                justify-content: center;
            }
        }

        html[data-theme='dark'] .hero-section {
            background: linear-gradient(rgba(7, 15, 29, 0.74), rgba(12, 31, 57, 0.82)), url('{{ asset('website/photos/home2.webp') }}');
            background-size: cover;
            background-position: center;
        }

        html[data-theme='dark'] .breadcrumb-section,
        html[data-theme='dark'] .blog-card-area,
        html[data-theme='dark'] .why-choose-section {
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%) !important;
        }

        html[data-theme='dark'] .breadcrumb-item,
        html[data-theme='dark'] .breadcrumb-item a,
        html[data-theme='dark'] .blog-card-title,
        html[data-theme='dark'] .sidebar-title,
        html[data-theme='dark'] .section-header,
        html[data-theme='dark'] .choose-title {
            color: var(--charcoal-deep) !important;
        }

        html[data-theme='dark'] .modern-blog-card,
        html[data-theme='dark'] .luxury-sidebar,
        html[data-theme='dark'] .choose-card {
            background: #111827 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: var(--shadow-medium) !important;
        }

        html[data-theme='dark'] .blog-image,
        html[data-theme='dark'] .popular-img {
            background: #0f172a !important;
        }

        html[data-theme='dark'] .blog-date,
        html[data-theme='dark'] .search-input,
        html[data-theme='dark'] .category-tag,
        html[data-theme='dark'] .empty-state {
            background: #172033 !important;
            color: var(--warm-gray) !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[data-theme='dark'] .blog-description p,
        html[data-theme='dark'] .popular-date,
        html[data-theme='dark'] .section-subtitle,
        html[data-theme='dark'] .feature-item {
            color: var(--warm-gray) !important;
        }

        html[data-theme='dark'] .blog-footer,
        html[data-theme='dark'] .sidebar-widget,
        html[data-theme='dark'] .popular-article,
        html[data-theme='dark'] .feature-item {
            border-color: rgba(148, 163, 184, 0.14) !important;
        }

        html[data-theme='dark'] .search-input:focus {
            background: #0f172a !important;
        }

        html[data-theme='dark'] .category-tag:hover,
        html[data-theme='dark'] .pagination-wrapper .page-item.active .page-link,
        html[data-theme='dark'] .social-link {
            color: #0f172a !important;
        }

        html[data-theme='dark'] .popular-article:hover {
            background: rgba(244, 195, 106, 0.08) !important;
        }

        html[data-theme='dark'] .pagination-wrapper .page-link {
            background: #111827 !important;
            color: var(--charcoal-deep) !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }

        html[dir='rtl'] .hero-content,
        html[dir='rtl'] .blog-content-wrapper,
        html[dir='rtl'] .sidebar-widget,
        html[dir='rtl'] .choose-features,
        html[dir='rtl'] .cta-text-content {
            text-align: right;
        }

        html[dir='rtl'] .sidebar-title::after {
            left: auto;
            right: 0;
        }

        html[dir='rtl'] .blog-date i {
            margin-right: 0;
            margin-left: 8px;
        }

        html[dir='rtl'] .search-input {
            padding: 12px 15px 12px 50px;
        }

        html[dir='rtl'] .search-btn {
            right: auto;
            left: 5px;
        }

        html[dir='rtl'] .breadcrumb-item a,
        html[dir='rtl'] .blog-date,
        html[dir='rtl'] .trust-feature {
            flex-direction: row-reverse;
        }
    </style>
@endsection

@section('content')

    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="la la-newspaper"></i>
                    {{ __('Travel Insights') }}
                </div>

                <h1 class="hero-title">{{ $heroTitle }}</h1>

                <p class="hero-subtitle">
                    {{ $heroSubtitle }}
                </p>
            </div>
        </div>
    </section>

    <section class="breadcrumb-section">
        <div class="container">
            <div class="breadcrumb-container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('website.home') }}">
                            <i class="la la-home breadcrumb-icon"></i>
                            {{ __('Home') }}
                        </a>
                    </li>

                    @if (isset($category))
                        <li class="breadcrumb-item">
                            <a href="{{ $blogsRoute }}">{{ __('Blogs') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ $category->display_title ?? ($category->title ?? ($category->name ?? __('Category'))) }}
                        </li>
                    @else
                        <li class="breadcrumb-item active">{{ __('Blogs') }}</li>
                    @endif
                </ol>
            </div>
        </div>
    </section>

    <section class="blog-card-area">
        <div class="container">
            <div class="row">

                <div class="col-lg-8">
                    <div class="row">
                        @forelse ($articles as $article)
                            @php
                                $articleTitle = $article->display_title ?: __('Article');

                                $articleImage = $article->featured_image
                                    ? asset('storage/' . ltrim($article->featured_image, '/'))
                                    : asset('website/photos/home2.webp');

                                $articleDate = $article->published_at ?? ($article->created_at ?? now());

                                $articleCategoryTitle =
                                    $article->category?->display_title ??
                                    ($article->category?->title ?? ($article->category?->name ?? __('General')));

                                $articleCategorySlug = $article->category?->slug ?? Str::slug($articleCategoryTitle);

                                $articleExcerpt =
                                    $article->display_excerpt ?: Str::limit(strip_tags($article->display_content), 120);

                                $articleUrl = Route::has('website.blogs.show.legacy')
                                    ? route('website.blogs.show.legacy', [$articleCategorySlug, $article->slug])
                                    : (Route::has('website.blogs.show')
                                        ? route('website.blogs.show', $article->slug)
                                        : url('/blogs/' . $article->slug));
                            @endphp

                            <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                                <div class="modern-blog-card">
                                    <div class="blog-image">
                                        <a href="{{ $articleUrl }}">
                                            <img src="{{ $articleImage }}" alt="{{ $articleTitle }}" class="blog-img"
                                                loading="lazy">

                                            <div class="blog-overlay">
                                                <div class="overlay-content">
                                                    <i class="la la-eye"></i>
                                                    <span>{{ __('Read Article') }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="blog-content-wrapper">
                                        <h3 class="blog-card-title">
                                            <a href="{{ $articleUrl }}">
                                                {{ $articleTitle }}
                                            </a>
                                        </h3>

                                        <div class="blog-date">
                                            <i class="la la-calendar"></i>
                                            <span>{{ \Carbon\Carbon::parse($articleDate)->locale(app()->getLocale())->translatedFormat('D, d M Y') }}</span>
                                        </div>

                                        <div class="blog-description">
                                            <p>{{ $articleExcerpt }}</p>
                                        </div>

                                        <div class="blog-footer">
                                            <a href="{{ $articleUrl }}" class="btn-blog">
                                                {{ __('Read More') }} <i class="la la-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    {{ __('No articles found.') }}
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if (method_exists($articles, 'links') && $articles->hasPages())
                        <div class="pagination-wrapper">
                            {{ $articles->links() }}
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Search Articles') }}</h3>

                            <form action="{{ $blogsRoute }}" method="get" class="search-form">
                                <input type="text" name="keyword" class="search-input"
                                    placeholder="{{ __('Search for articles...') }}" value="{{ request('keyword') }}">

                                <button type="submit" class="search-btn">
                                    <i class="la la-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Categories') }}</h3>

                            <div class="category-tags">
                                @forelse ($categories as $blogCategory)
                                    @php
                                        $catTitle =
                                            $blogCategory->display_title ??
                                            ($blogCategory->title ?? ($blogCategory->name ?? __('Category')));

                                        $catSlug = $blogCategory->slug ?? Str::slug($catTitle);

                                        $catUrl = Route::has('website.blogs.category')
                                            ? route('website.blogs.category', $catSlug)
                                            : url('/blog/' . $catSlug);
                                    @endphp

                                    <a href="{{ $catUrl }}" class="category-tag">
                                        {{ $catTitle }}
                                    </a>
                                @empty
                                    <div class="empty-state">
                                        {{ __('No categories found.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Popular Articles') }}</h3>

                            @forelse ($popularArticles as $popular)
                                @php
                                    $popularTitle = $popular->display_title ?: __('Article');

                                    $popularImage = $popular->featured_image
                                        ? asset('storage/' . ltrim($popular->featured_image, '/'))
                                        : asset('website/photos/home2.webp');

                                    $popularDate = $popular->published_at ?? ($popular->created_at ?? now());

                                    $popularCategoryTitle =
                                        $popular->category?->display_title ??
                                        ($popular->category?->title ?? ($popular->category?->name ?? __('General')));

                                    $popularCategorySlug =
                                        $popular->category?->slug ?? Str::slug($popularCategoryTitle);

                                    $popularUrl = Route::has('website.blogs.show.legacy')
                                        ? route('website.blogs.show.legacy', [$popularCategorySlug, $popular->slug])
                                        : (Route::has('website.blogs.show')
                                            ? route('website.blogs.show', $popular->slug)
                                            : url('/blogs/' . $popular->slug));
                                @endphp

                                <div class="popular-article">
                                    <img src="{{ $popularImage }}" alt="{{ $popularTitle }}" class="popular-img"
                                        loading="lazy">

                                    <div class="popular-content">
                                        <h4>
                                            <a href="{{ $popularUrl }}">
                                                {{ $popularTitle }}
                                            </a>
                                        </h4>

                                        <p class="popular-date">
                                            {{ \Carbon\Carbon::parse($popularDate)->locale(app()->getLocale())->translatedFormat('D, d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    {{ __('No popular articles found.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="luxury-sidebar">
                        <div class="sidebar-widget">
                            <h3 class="sidebar-title">{{ __('Follow & Connect') }}</h3>

                            <div class="social-links">
                                <a href="https://www.facebook.com/" target="_blank" class="social-link">
                                    <i class="lab la-facebook-f"></i>
                                </a>

                                <a href="https://twitter.com/" target="_blank" class="social-link">
                                    <i class="lab la-twitter"></i>
                                </a>

                                <a href="https://www.instagram.com/" target="_blank" class="social-link">
                                    <i class="lab la-instagram"></i>
                                </a>

                                <a href="https://www.tripadvisor.com/" target="_blank" class="social-link">
                                    <i class="la la-tripadvisor"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>

    {{-- <div class="fixed-mobile-btn d-lg-none">
        <a href="https://api.whatsapp.com/send?phone=201553383000" target="_blank" class="mobile-enquiry-btn">
            <i class="lab la-whatsapp"></i>
            {{ __('WhatsApp Us') }}
        </a>
    </div> --}}

    <section class="why-choose-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-header">
                        {{ __('Why Travel With Etro Tours?') }}
                    </h2>
                    <p class="section-subtitle">
                        {{ __('Your entire vacation is designed around your requirements with expert guidance every step of the way.') }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-cog"></i>
                        </div>

                        <h3 class="choose-title">{{ __('100% Tailor Made') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Your entire vacation is designed around your requirements.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Explore your interests at your own speed.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Select your preferred style of accommodations.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Create the perfect trip with the help of our specialists.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-lightbulb"></i>
                        </div>

                        <h3 class="choose-title">{{ __('Expert Knowledge') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Our specialists have traveled extensively across Egypt.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('The same specialist will handle your trip from start to finish.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Make the most of your time and budget.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-user-graduate"></i>
                        </div>

                        <h3 class="choose-title">{{ __('The Best Guides') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Our guides make the difference between a good trip and an outstanding one.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Safety and wellbeing are always our number one priority.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Real insight into Egypt, not just dates and names.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="la la-shield-alt"></i>
                        </div>

                        <h3 class="choose-title">{{ __('Fully Protected') }}</h3>

                        <div class="choose-features">
                            <div class="feature-item">
                                {{ __('Trusted travel planning from arrival to departure.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Professional support before and during your journey.') }}
                            </div>
                            <div class="feature-item">
                                {{ __('Comfortable service standards and secure travel coordination.') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="luxury-cta-section">
        <div class="container">
            <div class="luxury-cta-content">
                <div class="cta-icon-container">
                    <i class="la la-phone"></i>
                </div>

                <div class="cta-content-wrapper">
                    <div class="cta-text-content">
                        <h2 class="cta-title">{{ __('Ready to Plan Your Dream Trip?') }}</h2>
                        <p class="cta-subtitle">
                            {{ __('Speak with our Egypt specialists for your perfect luxury journey.') }}
                        </p>

                        <div class="trust-features">
                            <div class="trust-feature">
                                <i class="la la-shield-alt"></i>
                                <span>{{ __('Free Consultation') }}</span>
                            </div>

                            <div class="trust-feature">
                                <i class="la la-clock"></i>
                                <span>{{ __('24/7 Support') }}</span>
                            </div>

                            <div class="trust-feature">
                                <i class="la la-award"></i>
                                <span>{{ __('Best Price Guarantee') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('website.contact.index') }}" class="luxury-cta-btn">
                        <i class="la la-calendar-check"></i>
                        {{ __('Start Planning') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('js')
    <script src="{{ request()->root() }}/website/js/new/jquery.min.js"></script>
    <script src="{{ request()->root() }}/website/js/new/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const target = document.querySelector(this.getAttribute('href'));

                    if (!target) {
                        return;
                    }

                    e.preventDefault();

                    window.scrollTo({
                        top: target.offsetTop - 100,
                        behavior: 'smooth'
                    });
                });
            });

            document.querySelectorAll('.modern-blog-card, .choose-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            window.addEventListener('load', () => {
                document.body.classList.add('loaded');
            });
        });
    </script>
@endsection
