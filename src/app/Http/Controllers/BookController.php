<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Services\OpenLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Smalot\PdfParser\Parser;

class BookController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cacheKey = $this->generateCacheKey($request);
        
        return Cache::remember($cacheKey, 300, function () use ($request) {
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

            $perPage = min($request->get('per_page', 15), 50);
        $books = $query->paginate($perPage);

            $booksData = $books->items();

        return response()->json([
                'data' => $booksData,
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
        });
    }

    public function store(BookStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Book::class);
        
        $book = Book::create($request->validated());
        
        $this->invalidateBooksCache();

        return response()->json([
            'data' => $book,
            'message' => 'Book created successfully'
        ], 201);
    }

    public function show(Book $book): JsonResponse
    {
        $bookData = $book->toArray();
        
        return response()->json([
            'data' => $bookData,
            'message' => 'Book retrieved successfully'
        ]);
    }

    public function update(BookUpdateRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);
        
        $book->update($request->validated());
        
        $this->invalidateBooksCache();

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

        $booksData = $books->toArray();

        return response()->json([
            'data' => $booksData,
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

    public function readBook(Book $book): JsonResponse
    {
        if (!$book->pdf_path || !\Storage::disk('public')->exists($book->pdf_path)) {
            abort(404, 'Book content not found');
        }

        try {
            $filePath = \Storage::disk('public')->path($book->pdf_path);
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            $totalPages = count($pages);
            
            $fullContent = '';
            foreach ($pages as $index => $page) {
                $pageNumber = $index + 1;
                $pageText = $page->getText();
                $fullContent .= "=== STRANICA {$pageNumber} ===\n\n";
                $fullContent .= $pageText . "\n\n";
            }
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'total_pages' => $totalPages,
                    'content' => $fullContent,
                    'has_subscription' => true,
                    'is_full_book' => true
                ],
                'message' => 'Book content retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            $fullContent = "Knjiga: {$book->title}\n";
            $fullContent .= "Autor: {$book->author}\n";
            $fullContent .= "Godina: {$book->year}\n";
            $fullContent .= "Žanr: {$book->genre}\n\n";
            $fullContent .= "Sadržaj knjige nije dostupan u ovom formatu.\n";
            $fullContent .= "Koristite /preview ili /page endpoint-e za čitanje.\n";
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'total_pages' => 50,
                    'content' => $fullContent,
                    'has_subscription' => true,
                    'is_full_book' => true
                ],
                'message' => 'Book content retrieved successfully'
            ]);
        }
    }

    public function previewBook(Book $book): JsonResponse
    {
        if (!$book->pdf_path || !\Storage::disk('public')->exists($book->pdf_path)) {
            abort(404, 'Book content not found');
        }

        try {
        $filePath = \Storage::disk('public')->path($book->pdf_path);
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            $totalPages = count($pages);
            
            $previewContent = [];
            $maxPreviewPages = min(3, $totalPages);
            
            for ($i = 0; $i < $maxPreviewPages; $i++) {
                $pageNumber = $i + 1;
                $pageText = $pages[$i]->getText();
                
                $previewText = substr($pageText, 0, 500);
                if (strlen($pageText) > 500) {
                    $previewText .= '...';
                }
                
                $previewContent[] = [
                    'page_number' => $pageNumber,
                    'content' => "PREVIEW - Potrebna pretplata za čitanje cele knjige\n\n" . $previewText,
                    'watermark' => 'PREVIEW - Potrebna pretplata za čitanje cele knjige'
                ];
            }
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'preview_pages' => $previewContent,
                    'total_pages' => $totalPages,
                    'preview_limit' => 3,
                    'subscription_required' => true
                ],
                'message' => 'Preview retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            $previewContent = [];
            for ($i = 1; $i <= 3; $i++) {
                $previewContent[] = [
                    'page_number' => $i,
                    'content' => "Stranica {$i} - PREVIEW\n\nOvo je preview sadržaj knjige '{$book->title}'.\nMožete da vidite samo prve 3 stranice.\nZa čitanje cele knjige potrebna je pretplata.\n\nAutor: {$book->author}\nGodina: {$book->year}\nŽanr: {$book->genre}",
                    'watermark' => 'PREVIEW - Potrebna pretplata za čitanje cele knjige'
                ];
            }
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'preview_pages' => $previewContent,
                    'total_pages' => 50,
                    'preview_limit' => 3,
                    'subscription_required' => true
                ],
                'message' => 'Preview retrieved successfully'
            ]);
        }
    }

    public function readBookPage(Book $book, Request $request): JsonResponse
    {
        $pageNumber = $request->get('page', 1);
        $maxPreviewPages = 3;
        
        if (!$book->pdf_path || !\Storage::disk('public')->exists($book->pdf_path)) {
            abort(404, 'Book content not found');
        }

        $user = auth()->user();
        $hasActiveSubscription = $user && $user->hasActiveSubscription();
        
        try {
            $filePath = \Storage::disk('public')->path($book->pdf_path);
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $pages = $pdf->getPages();
            $totalPages = count($pages);
            
            if ($pageNumber > $totalPages || $pageNumber < 1) {
                return response()->json([
                    'message' => 'Stranica ne postoji',
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages
                ], 404);
            }
            
            if (!$hasActiveSubscription && $pageNumber > $maxPreviewPages) {
                return response()->json([
                    'message' => 'Potrebna je aktivna pretplata za čitanje više stranica',
                    'max_preview_pages' => $maxPreviewPages,
                    'subscription_required' => true,
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages
                ], 403);
            }
            
            $pageIndex = $pageNumber - 1;
            $pageText = $pages[$pageIndex]->getText();
            
            if (!$hasActiveSubscription) {
                $pageText = "PREVIEW - Potrebna pretplata za čitanje cele knjige\n\n" . $pageText;
            }
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages,
                    'content' => $pageText,
                    'has_subscription' => $hasActiveSubscription,
                    'is_preview' => !$hasActiveSubscription && $pageNumber <= $maxPreviewPages
                ],
                'message' => 'Page retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            $totalPages = 50;
            
            if ($pageNumber > $totalPages || $pageNumber < 1) {
                return response()->json([
                    'message' => 'Stranica ne postoji',
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages
                ], 404);
            }
            
            if (!$hasActiveSubscription && $pageNumber > $maxPreviewPages) {
                return response()->json([
                    'message' => 'Potrebna je aktivna pretplata za čitanje više stranica',
                    'max_preview_pages' => $maxPreviewPages,
                    'subscription_required' => true,
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages
                ], 403);
            }
            
            $pageContent = "Stranica {$pageNumber} od {$totalPages}\n\n";
            $pageContent .= "Sadržaj knjige: {$book->title}\n";
            $pageContent .= "Autor: {$book->author}\n";
            $pageContent .= "Godina: {$book->year}\n";
            $pageContent .= "Žanr: {$book->genre}\n\n";
            
            if ($pageNumber <= 10) {
                $pageContent .= "Ovo je sadržaj stranice {$pageNumber}.\n";
                $pageContent .= "Tekst knjige počinje ovde...\n\n";
                $pageContent .= "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n";
                $pageContent .= "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n";
                $pageContent .= "Ut enim ad minim veniam, quis nostrud exercitation.\n\n";
            } else {
                $pageContent .= "Ovo je stranica {$pageNumber} knjige.\n";
                $pageContent .= "Sadržaj se nastavlja...\n\n";
            }
            
            if (!$hasActiveSubscription) {
                $pageContent = "PREVIEW - Potrebna pretplata za čitanje cele knjige\n\n" . $pageContent;
            }
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'current_page' => $pageNumber,
                    'total_pages' => $totalPages,
                    'content' => $pageContent,
                    'has_subscription' => $hasActiveSubscription,
                    'is_preview' => !$hasActiveSubscription && $pageNumber <= $maxPreviewPages
                ],
                'message' => 'Page retrieved successfully'
            ]);
        }
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
        $this->authorize('delete', $book);
        
        $book->delete();
        
        $this->invalidateBooksCache();

        return response()->json([
            'message' => 'Book deleted successfully'
        ]);
    }

    public function exportCsv(Request $request)
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

        if ($request->has('year_from')) {
            $query->where('year', '>=', $request->year_from);
        }

        if ($request->has('year_to')) {
            $query->where('year', '<=', $request->year_to);
        }

        $books = $query->orderBy('title', 'asc')->get();

        $filename = 'books_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($books) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID',
                'Title',
                'Author', 
                'Year',
                'Genre',
                'ISBN',
                'Created At',
                'Updated At'
            ]);

            foreach ($books as $book) {
                fputcsv($file, [
                    $book->id,
                    $book->title,
                    $book->author,
                    $book->year,
                    $book->genre,
                    $book->isbn,
                    $book->created_at->format('Y-m-d H:i:s'),
                    $book->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generateCacheKey(Request $request): string
    {
        $params = [
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 15),
            'q' => $request->get('q', ''),
            'genre' => $request->get('genre', ''),
            'sort' => $request->get('sort', ''),
        ];
        
        return 'books_index_' . md5(serialize($params));
    }

    private function invalidateBooksCache(): void
    {
        $store = Cache::getStore();
        
        if ($store instanceof \Illuminate\Cache\DatabaseStore) {
            \DB::table('cache')->where('key', 'like', 'laravel-cache-books_index_%')->delete();
        } elseif (method_exists($store, 'flush')) {
            Cache::flush();
        } else {
            $pattern = 'books_index_*';
            
            if (method_exists($store, 'getRedis')) {
                $keys = $store->getRedis()->keys($pattern);
                if (!empty($keys)) {
                    $store->getRedis()->del($keys);
                }
            } else {
                Cache::flush();
            }
        }
    }

    public function fetchByIsbn(Request $request, OpenLibraryService $openLibraryService): JsonResponse
    {
        $request->validate([
            'isbn' => 'required|string|min:10|max:13',
        ]);

        $isbn = $request->isbn;
        $bookData = $openLibraryService->fetchBookByIsbn($isbn);

        if (!$bookData) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found in Open Library',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bookData,
            'message' => 'Book data retrieved successfully',
        ]);
    }
}
