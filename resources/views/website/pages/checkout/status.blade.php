@extends('website.layouts.master')
@section('title', __('Payment Status'))
@section('robots', 'noindex, nofollow')
@section('preferred_theme', 'light')
@section('body_class', 'checkout-page')
@section('css')
<style>
.checkout-page .why-choose-section,.checkout-page .luxury-cta-section{display:none!important}.payment-result{background:#f8f6f1;padding:70px 15px;min-height:65vh}.result-card{max-width:650px;margin:auto;background:#fff;border-radius:24px;padding:42px;text-align:center;box-shadow:0 18px 45px rgba(21,39,67,.1)}.result-icon{width:82px;height:82px;border-radius:50%;display:grid;place-items:center;margin:0 auto 20px;font-size:2.5rem;background:#eaf8f1;color:#1ca36e}.result-icon.pending{background:#fff6e7;color:#c98a32}.result-icon.failed{background:#fff0f0;color:#c84444}.result-card h1{font-family:'Playfair Display',serif;color:#1c325c}.result-ref{background:#f7f8fa;border-radius:12px;padding:14px;margin:22px 0;color:#40516c}.result-card a{display:inline-flex;background:#d4a05d;color:#173763;padding:13px 24px;border-radius:999px;font-weight:800}
</style>
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
