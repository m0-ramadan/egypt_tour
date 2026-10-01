@extends('website.layouts.master')
@section('title', __('Payment Status'))
@section('robots', 'noindex, nofollow')
@section('preferred_theme', 'light')
@section('body_class', 'checkout-page')
@section('css')
    @vite('resources/css/pages/checkout-status.css')
@endsection
@section('content')
@php
    $isPaid = $payment->status === \App\Models\Payment::STATUS_PAID;
    $isFailed = in_array($payment->status, [\App\Models\Payment::STATUS_FAILED, 'cancelled'], true) || request('result') === 'cancelled';
@endphp
<section class="payment-result"><div class="result-card">
    <div class="result-icon {{ $isPaid ? '' : ($isFailed ? 'failed' : 'pending') }}"><i class="la {{ $isPaid ? 'la-check' : ($isFailed ? 'la-times' : 'la-clock') }}"></i></div>
    <h1>{{ $isPaid ? __('Payment Successful') : ($isFailed ? __('Payment Not Completed') : __('Payment Is Processing')) }}</h1>
    <p>{{ $isPaid ? __('Your booking has been paid successfully. A confirmation will be sent to your email.') : ($isFailed ? __('Your booking is saved, but the payment was not completed. Please contact us to retry.') : __('We are confirming the payment with the provider. Your booking will update automatically after confirmation.')) }}</p>
    <div class="result-ref"><strong>{{ __('Booking Reference') }}:</strong> {{ $payment->booking->booking_number }}<br><strong>{{ __('Amount') }}:</strong> {{ $payment->currency_code }} {{ number_format((float)$payment->amount, 2) }}</div>
    <a href="{{ route('website.home') }}">{{ __('Back to Home') }}</a>
</div></section>
@endsection
