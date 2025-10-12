<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Book;
use App\Events\LoanCreated;
use App\Events\LoanReturned;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $loans = Loan::where('user_id', $user->id)
            ->with(['book:id,title,author,year,genre'])
            ->orderBy('borrowed_at', 'desc')
            ->paginate(15);

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
}
