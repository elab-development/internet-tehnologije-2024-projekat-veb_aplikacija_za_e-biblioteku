<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Book::query();

        if ($request->has('q') && !empty($request->q)) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('author', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('genre') && !empty($request->genre)) {
            $query->where('genre', $request->genre);
        }

        if ($request->has('sort')) {
            $sortFields = explode(',', $request->sort);
            foreach ($sortFields as $sortField) {
                $direction = 'asc';
                if (str_starts_with($sortField, '-')) {
                    $direction = 'desc';
                    $sortField = substr($sortField, 1);
                }
                
                if (in_array($sortField, ['title', 'author', 'year', 'genre', 'created_at'])) {
                    $query->orderBy($sortField, $direction);
                }
            }
        } else {
            $query->orderBy('title', 'asc');
        }

        //paginacija
        $perPage = min($request->get('per_page', 15), 50); //max 50 po stranici
        $books = $query->paginate($perPage);

        return response()->json([
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'from' => $books->firstItem(),
                'to' => $books->lastItem(),
            ],
            'links' => [
                'first' => $books->url(1),
                'last' => $books->url($books->lastPage()),
                'prev' => $books->previousPageUrl(),
                'next' => $books->nextPageUrl(),
            ],
            'message' => 'Books retrieved successfully'
        ]);
    }

    public function store(BookStoreRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return response()->json([
            'data' => $book,
            'message' => 'Book created successfully'
        ], 201);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'year' => $book->year,
                'genre' => $book->genre,
                'isbn' => $book->isbn,
                'cover_url' => $book->cover_url,
                'pdf_url' => $book->pdf_url,
                'created_at' => $book->created_at,
                'updated_at' => $book->updated_at,
            ],
            'message' => 'Book retrieved successfully'
        ]);
    }

    public function update(BookUpdateRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return response()->json([
            'data' => $book,
            'message' => 'Book updated successfully'
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query');
        
        if (empty($query)) {
            return response()->json([
                'data' => [],
                'message' => 'Search query is required'
            ], 400);
        }

        $books = Book::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
              ->orWhere('author', 'LIKE', "%{$query}%")
              ->orWhere('isbn', 'LIKE', "%{$query}%");
        })->orderBy('title', 'asc')->get();

        return response()->json([
            'data' => $books,
            'query' => $query,
            'total' => $books->count(),
            'message' => 'Search completed successfully'
        ]);
    }

    public function uploadCover(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ], [
            'cover.required' => 'Cover image is required.',
            'cover.image' => 'File must be an image.',
            'cover.mimes' => 'Image must be jpg, jpeg, png, or webp format.',
            'cover.max' => 'Image size must not exceed 2MB.'
        ]);

        //TODO moze li efikasnije
        if ($book->cover_path && \Storage::disk('public')->exists($book->cover_path)) {
            \Storage::disk('public')->delete($book->cover_path);
        }

        $coverPath = $request->file('cover')->store('covers', 'public');
        $book->update(['cover_path' => $coverPath]);

        return response()->json([
            'data' => $book->fresh(),
            'message' => 'Cover uploaded successfully'
        ]);
    }


    public function uploadPdf(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480'
        ], [
            'pdf.required' => 'PDF file is required.',
            'pdf.file' => 'File must be a valid file.',
            'pdf.mimes' => 'File must be a PDF.',
            'pdf.max' => 'PDF size must not exceed 20MB.'
        ]);

        if ($book->pdf_path && \Storage::disk('public')->exists($book->pdf_path)) {
            \Storage::disk('public')->delete($book->pdf_path);
        }

        $pdfPath = $request->file('pdf')->store('pdfs', 'public');
        $book->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'data' => $book->fresh(),
            'message' => 'PDF uploaded successfully'
        ]);
    }

    public function downloadPdf(Book $book): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (!$book->pdf_path || !\Storage::disk('public')->exists($book->pdf_path)) {
            abort(404, 'PDF not found');
        }

        $filePath = \Storage::disk('public')->path($book->pdf_path);
        
        return response()->download($filePath, $book->title . '.pdf', [
            'Content-Type' => 'application/pdf'
        ]);
    }

    public function restore($id): JsonResponse
    {
        $book = Book::withTrashed()->findOrFail($id);
        
        if (!$book->trashed()) {
            return response()->json([
                'message' => 'Book is not deleted'
            ], 400);
        }

        $book->restore();

        return response()->json([
            'data' => $book,
            'message' => 'Book restored successfully'
        ]);
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully'
        ]);
    }
}
