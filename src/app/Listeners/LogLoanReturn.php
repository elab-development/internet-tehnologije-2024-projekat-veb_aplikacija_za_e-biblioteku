<?php

namespace App\Listeners;

use App\Events\LoanReturned;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogLoanReturn
{
    public function __construct()
    {
        //
    }

    public function handle(LoanReturned $event): void
    {
        $loan = $event->loan;
        
        Log::info('Loan returned', [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'book_id' => $loan->book_id,
            'book_title' => $loan->book?->title ?? 'Unknown',
            'borrowed_at' => $loan->borrowed_at,
            'returned_at' => $loan->returned_at,
            'loan_duration_days' => $loan->borrowed_at->diffInDays($loan->returned_at),
            'action' => 'loan_returned'
        ]);

        AuditLog::log('returned', 'Loan', $loan->id, $loan->user_id, [
            'book_id' => $loan->book_id,
            'book_title' => $loan->book?->title ?? 'Unknown',
            'borrowed_at' => $loan->borrowed_at->toISOString(),
            'returned_at' => $loan->returned_at->toISOString(),
            'duration_days' => $loan->borrowed_at->diffInDays($loan->returned_at),
        ]);
    }
}
