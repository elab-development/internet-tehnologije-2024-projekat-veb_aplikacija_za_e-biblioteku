<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Services\OpenLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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
        
        $validated = $request->validated();
        
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_path'] = $coverPath;
        }
        
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('pdfs', 'private');
            $validated['pdf_path'] = $pdfPath;
        }
        
        unset($validated['cover_image'], $validated['pdf_file']);
        
        $book = Book::create($validated);
        
        $this->invalidateBooksCache();

        return response()->json([
            'data' => $book,
            'message' => 'Book created successfully'
        ], 201);
    }

    public function show(Book $book): JsonResponse
    {
        $bookData = $book->toArray();
        
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                $user = $personalAccessToken->tokenable;
            }
        }
        
        if ($user) {
            $userLoan = $book->loans()
                ->where('user_id', $user->id)
                ->where(function($query) {
                    $query->whereNull('returned_at')
                          ->orWhere('returned_at', '');
                })
                ->first();
            
            $bookData['is_borrowed_by_user'] = $userLoan ? true : false;
            $bookData['user_loan'] = $userLoan ? [
                'id' => $userLoan->id,
                'borrowed_at' => $userLoan->borrowed_at,
                'due_at' => $userLoan->due_at,
                'is_overdue' => $userLoan->due_at < now(),
            ] : null;
        } else {
            $bookData['is_borrowed_by_user'] = false;
            $bookData['user_loan'] = null;
        }
        
        return response()->json([
            'data' => $bookData,
            'message' => 'Book retrieved successfully'
        ]);
    }

    public function update(BookUpdateRequest $request, Book $book): JsonResponse
    {
        $this->authorize('update', $book);
        
        $validated = $request->validated();
        
        if ($request->hasFile('cover_image')) {
            if ($book->cover_path && \Storage::disk('public')->exists($book->cover_path)) {
                \Storage::disk('public')->delete($book->cover_path);
            }
            
            $coverPath = $request->file('cover_image')->store('covers', 'public');
            $validated['cover_path'] = $coverPath;
        }
        
        if ($request->hasFile('pdf_file')) {
            if ($book->pdf_path && \Storage::disk('private')->exists($book->pdf_path)) {
                \Storage::disk('private')->delete($book->pdf_path);
            }
            
            $pdfPath = $request->file('pdf_file')->store('pdfs', 'private');
            $validated['pdf_path'] = $pdfPath;
        }
        
        unset($validated['cover_image'], $validated['pdf_file']);
        
        $book->update($validated);
        
        $this->invalidateBooksCache();

        return response()->json([
            'data' => $book->fresh(),
            'message' => 'Book updated successfully'
        ]);
    }

    public function viewPdf(Book $book): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                $user = $personalAccessToken->tokenable;
            }
        }

        if (!$user) {
            abort(401, 'Authentication required');
        }

        $hasActiveSubscription = $user->hasActiveSubscription();
        
        $userLoan = $book->loans()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('returned_at')
                      ->orWhere('returned_at', '');
            })
            ->first();
        
        $isBorrowedByUser = $userLoan ? true : false;
        
        if (!$hasActiveSubscription && !$isBorrowedByUser) {
            abort(403, 'Potrebna je aktivna pretplata ili pozajmljena knjiga za čitanje celih knjiga');
        }

        if (!$book->pdf_path || !\Storage::disk('private')->exists($book->pdf_path)) {
            abort(404, 'PDF fajl nije dostupan');
        }

        $filePath = \Storage::disk('private')->path($book->pdf_path);
        
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $book->title . '.pdf"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query') ?? $request->get('q');
        
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

        if ($book->pdf_path && \Storage::disk('private')->exists($book->pdf_path)) {
            \Storage::disk('private')->delete($book->pdf_path);
        }

        $pdfPath = $request->file('pdf')->store('pdfs', 'private');
        $book->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'data' => $book->fresh(),
            'message' => 'PDF uploaded successfully'
        ]);
    }

    public function readBook(Book $book): JsonResponse
    {
        if (!$book->pdf_path || !\Storage::disk('private')->exists($book->pdf_path)) {
            $user = null;
            $token = request()->bearerToken();
            if ($token) {
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $user = $personalAccessToken->tokenable;
                }
            }

            if (!$user) {
                abort(401, 'Authentication required');
            }

            $hasActiveSubscription = $user->hasActiveSubscription();
            
            $userLoan = $book->loans()
                ->where('user_id', $user->id)
                ->where(function($query) {
                    $query->whereNull('returned_at')
                          ->orWhere('returned_at', '');
                })
                ->first();
            
            $isBorrowedByUser = $userLoan ? true : false;
            
            if (!$hasActiveSubscription && !$isBorrowedByUser) {
                abort(403, 'Potrebna je aktivna pretplata ili pozajmljena knjiga za čitanje celih knjiga');
            }

            $fullContent = "Naslov: {$book->title}\n\n";
            $fullContent .= "Autor: {$book->author}\n\n";
            $fullContent .= "Godina: {$book->year}\n\n";
            $fullContent .= "Žanr: {$book->genre}\n\n";
            $fullContent .= "Opis:\n" . ($book->description ?: 'Opis knjige nije dostupan.') . "\n\n";
            $fullContent .= "Napomena: PDF sadržaj ove knjige nije dostupan. Ovo je osnovni pregled informacija o knjizi.";
            
            return response()->json([
                'data' => [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'total_pages' => 1,
                    'content' => $fullContent,
                    'has_subscription' => $hasActiveSubscription,
                    'is_borrowed_by_user' => $isBorrowedByUser,
                    'is_full_book' => true
                ],
                'message' => 'Book content retrieved successfully'
            ]);
        }

        $user = null;
        $token = request()->bearerToken();
        if ($token) {
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                $user = $personalAccessToken->tokenable;
            }
        }

        if (!$user) {
            abort(401, 'Authentication required');
        }

        $hasActiveSubscription = $user->hasActiveSubscription();
        
        $userLoan = $book->loans()
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('returned_at')
                      ->orWhere('returned_at', '');
            })
            ->first();
        
        $isBorrowedByUser = $userLoan ? true : false;
        
        if (!$hasActiveSubscription && !$isBorrowedByUser) {
            abort(403, 'Potrebna je aktivna pretplata ili pozajmljena knjiga za čitanje celih knjiga');
        }

        try {
            $filePath = \Storage::disk('private')->path($book->pdf_path);
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
                    'has_subscription' => $hasActiveSubscription,
                    'is_borrowed_by_user' => $isBorrowedByUser,
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
                    'has_subscription' => $hasActiveSubscription,
                    'is_borrowed_by_user' => $isBorrowedByUser,
                    'is_full_book' => true
                ],
                'message' => 'Book content retrieved successfully'
            ]);
        }
    }

    public function previewBook(Book $book): JsonResponse
    {
        if (!$book->pdf_path || !\Storage::disk('private')->exists($book->pdf_path)) {
            $previewContent = [
                'book_id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'year' => $book->year,
                'genre' => $book->genre,
                'description' => $book->description,
                'preview_pages' => [
                    [
                        'page_number' => 1,
                        'content' => "Naslov: {$book->title}\n\nAutor: {$book->author}\n\nGodina: {$book->year}\n\nŽanr: {$book->genre}\n\nOpis:\n" . ($book->description ?: 'Opis knjige nije dostupan.')
                    ]
                ],
                'total_pages' => 1,
                'is_preview' => true
            ];
            
            return response()->json([
                'data' => $previewContent,
                'message' => 'Book preview retrieved successfully'
            ]);
        }

        try {
        $filePath = \Storage::disk('private')->path($book->pdf_path);
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
        
        if (!$book->pdf_path || !\Storage::disk('private')->exists($book->pdf_path)) {
            abort(404, 'Book content not found');
        }

        $user = auth()->user();
        $hasActiveSubscription = $user && $user->hasActiveSubscription();
        
        try {
            $filePath = \Storage::disk('private')->path($book->pdf_path);
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

        if (isset($bookData['cover_url'])) {
            $coverPath = $this->downloadCoverImage($bookData['cover_url'], $isbn);
            if ($coverPath) {
                $bookData['cover_path'] = $coverPath;
                $bookData['cover_url'] = \Storage::disk('public')->url($coverPath);
            }
            unset($bookData['cover_url']); // Ukloni originalni URL
        }

        return response()->json([
            'success' => true,
            'data' => $bookData,
            'message' => 'Book data retrieved successfully',
        ]);
    }

    private function downloadCoverImage(string $coverUrl, string $isbn): ?string
    {
        try {
            $response = Http::timeout(10)->get($coverUrl);
            
            if (!$response->successful()) {
                return null;
            }

            $imageContent = $response->body();
            $extension = 'jpg';
            
            $filename = 'cover_' . $isbn . '_' . time() . '.' . $extension;
            $path = 'covers/' . $filename;
            
            \Storage::disk('public')->put($path, $imageContent);
            
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }
}
