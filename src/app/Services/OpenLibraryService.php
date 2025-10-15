<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenLibraryService
{
    private string $baseUrl = 'https://openlibrary.org';

    public function fetchBookByIsbn(string $isbn): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/isbn/{$isbn}.json");
            
            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            return [
                'title' => $data['title'] ?? null,
                'author' => $this->extractAuthor($data['authors'] ?? []),
                'year' => $this->extractYear($data['publish_date'] ?? null),
                'description' => $data['description'] ?? null,
                'isbn' => $isbn,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractAuthor(array $authors): ?string
    {
        if (empty($authors)) {
            return null;
        }

        $firstAuthor = $authors[0];
        
        if (is_string($firstAuthor)) {
            return $firstAuthor;
        }

        if (isset($firstAuthor['key'])) {
            $authorKey = str_replace('/authors/', '', $firstAuthor['key']);
            $authorResponse = Http::timeout(5)->get("{$this->baseUrl}/authors/{$authorKey}.json");
            
            if ($authorResponse->successful()) {
                $authorData = $authorResponse->json();
                return $authorData['name'] ?? null;
            }
        }

        return null;
    }

    private function extractYear(?string $publishDate): ?int
    {
        if (!$publishDate) {
            return null;
        }

        if (preg_match('/(\d{4})/', $publishDate, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
