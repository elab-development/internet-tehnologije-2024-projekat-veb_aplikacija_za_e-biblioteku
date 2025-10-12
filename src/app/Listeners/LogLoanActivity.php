<?php

namespace App\Listeners;

use App\Events\LoanCreated;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogLoanActivity
{
    public function __construct()
    {
        //
    }

    public function handle(LoanCreated $event): void
    {
        $loan = $event->loan;
        
        Log::info('Loan created', [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'book_id' => $loan->book_id,
            'book_title' => $loan->book->title,
            'borrowed_at' => $loan->borrowed_at,
            'due_at' => $loan->due_at,
            'action' => 'loan_created'
        ]);

        AuditLog::log('borrowed', 'Loan', $loan->id, $loan->user_id, [
            'book_id' => $loan->book_id,
            'book_title' => $loan->book->title,
            'due_at' => $loan->due_at->toISOString(),
        ]);
    }
}
