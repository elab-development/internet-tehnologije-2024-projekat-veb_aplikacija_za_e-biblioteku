<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Book;
use App\Events\LoanCreated;
use App\Events\LoanReturned;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Loan::class);
        
        $user = $request->user();
        $query = Loan::with(['book:id,title,author,year,genre', 'user:id,name,email']);
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }
        
        if ($request->boolean('only_active')) {
            $query->whereNull('returned_at');
        }
        
        if ($request->boolean('overdue')) {
            $query->whereNull('returned_at')
                  ->where('due_at', '<', now());
        }
        
        $loans = $query->orderBy('borrowed_at', 'desc')->paginate(15);

        return response()->json([
            'data' => $loans->items(),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page' => $loans->lastPage(),
                'per_page' => $loans->perPage(),
                'total' => $loans->total(),
            ],
            'message' => 'Loans retrieved successfully'
        ]);
    }

    //kreiraj loan
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Loan::class);
        
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ], [
            'book_id.required' => 'Book ID is required.',
            'book_id.exists' => 'Book not found.',
        ]);

        $user = $request->user();
        $bookId = $request->book_id;


        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $bookId,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $loan->load(['book:id,title,author,year,genre']);

        event(new LoanCreated($loan));

        return response()->json([
            'data' => $loan,
            'message' => 'Loan created successfully'
        ], 201);
    }

    public function show(Loan $loan): JsonResponse
    {
        $this->authorize('view', $loan);

        $loan->load(['book:id,title,author,year,genre', 'user:id,name,email']);

        return response()->json([
            'data' => $loan,
            'message' => 'Loan retrieved successfully'
        ]);
    }

    public function borrow(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();

        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $loan->load(['book:id,title,author,year,genre']);

        event(new LoanCreated($loan));

        return response()->json([
            'data' => $loan,
            'message' => 'Book borrowed successfully'
        ], 201);
    }

    public function return(Request $request, Loan $loan): JsonResponse
    {
        $user = $request->user();

        if ($loan->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'message' => 'Access denied. You can only return your own loans.'
            ], 403);
        }

        if ($loan->returned_at) {
            return response()->json([
                'message' => 'This book has already been returned.'
            ], 400);
        }

        $loan->update(['returned_at' => now()]);
        $loan->load(['book:id,title,author,year,genre']);

        event(new LoanReturned($loan));

        return response()->json([
            'data' => $loan,
            'message' => 'Book returned successfully'
        ]);
    }

    public function update(Request $request, Loan $loan): JsonResponse
    {
        $this->authorize('update', $loan);

        $request->validate([
            'due_at' => 'sometimes|date|after:borrowed_at',
            'returned_at' => 'sometimes|nullable|date|after:borrowed_at',
        ]);

        $loan->update($request->only(['due_at', 'returned_at']));
        $loan->load(['book:id,title,author,year,genre', 'user:id,name,email']);

        return response()->json([
            'data' => $loan,
            'message' => 'Loan updated successfully'
        ]);
    }

    public function exportCsv(Request $request): Response
    {
        $this->authorize('exportCsv', Loan::class);
        
        $loans = Loan::with(['book:id,title,author', 'user:id,name,email'])
            ->when($request->boolean('only_active'), fn($q) => $q->whereNull('returned_at'))
            ->orderBy('borrowed_at', 'desc')
            ->get();
        
        $csv = "ID,User,Email,Book,Author,Borrowed At,Due At,Returned At,Status\n";
        foreach ($loans as $loan) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $loan->id,
                $loan->user->name,
                $loan->user->email,
                $loan->book->title,
                $loan->book->author,
                $loan->borrowed_at->format('Y-m-d H:i:s'),
                $loan->due_at->format('Y-m-d H:i:s'),
                $loan->returned_at?->format('Y-m-d H:i:s') ?? 'N/A',
                $loan->returned_at ? 'Returned' : ($loan->isOverdue() ? 'Overdue' : 'Active')
            );
        }
        
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="loans_export_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ]);
    }
}
