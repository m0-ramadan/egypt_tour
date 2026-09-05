<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payments\PaymobService;
use Illuminate\Console\Command;

class ReconcilePaymobPayments extends Command
{
    protected $signature = 'payments:reconcile-paymob
        {--minutes=15 : Reconcile pending payments older than this many minutes}
        {--limit=100 : Maximum number of payments per run}';

    protected $description = 'Reconcile stale pending Paymob payments using Paymob transaction inquiry.';

    public function handle(PaymobService $paymob): int
    {
        if (! $paymob->enabled()) {
            $this->info('Paymob is disabled; nothing to reconcile.');

            return self::SUCCESS;
        }

        $minutes = max(1, (int) $this->option('minutes'));
        $limit = max(1, min(1000, (int) $this->option('limit')));

        $payments = Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereHas('paymentMethod', function ($query) {
                $query->where('code', 'paymob')
                    ->orWhere('provider', 'paymob');
            })
            ->where('created_at', '<=', now()->subMinutes($minutes))
            ->oldest()
            ->limit($limit)
            ->get();

        $success = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            try {
                $paymob->reconcile($payment);
                $success++;
            } catch (\Throwable $exception) {
                $failed++;
                report($exception);
                $this->warn(
                    ($payment->transaction_reference ?: ('#' . $payment->id))
                    . ': ' . $exception->getMessage()
                );
            }
        }

        $this->info("Reconciled {$success} payment(s); {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
