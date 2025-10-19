<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UpdateBookCovers extends Command
{
    protected $signature = 'books:update-covers {--limit=50 : Broj knjiga za ažuriranje}';
    protected $description = 'Ažurira cover slike za knjige';

    protected OpenLibraryService $openLibraryService;

    public function __construct(OpenLibraryService $openLibraryService)
    {
        parent::__construct();
        $this->openLibraryService = $openLibraryService;
    }

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Ažuriranje cover slika za {$limit} knjiga...");

        $books = Book::whereNull('cover_path')->whereNotNull('isbn')->limit($limit)->get();

        $updatedCount = 0;
        foreach ($books as $book) {
            $this->line("Ažuriranje: {$book->title} (ISBN: {$book->isbn})");
            
            $bookData = $this->openLibraryService->fetchBookByIsbn($book->isbn);

            if (isset($bookData['cover_url'])) {
                $coverPath = $this->downloadCoverImage($bookData['cover_url'], $book->isbn);
                if ($coverPath) {
                    $book->cover_path = $coverPath;
                    $book->save();
                    $updatedCount++;
                    $this->info("✓ Cover slika preuzeta za: {$book->title}");
                } else {
                    $this->warn("✗ Nije moguće preuzeti cover sliku za: {$book->title}");
                }
            } else {
                $this->warn("✗ Nema cover URL-a za: {$book->title}");
            }
        }

        $this->info("\nRezultat:");
        $this->info("✓ Uspešno ažurirano: {$updatedCount}");
        $this->info("✗ Neuspešno: " . ($books->count() - $updatedCount));
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
            
            Storage::disk('public')->put($path, $imageContent);
            
            return $path;
        } catch (\Exception $e) {
            return null;
        }
    }
}