<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    public function toggle(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Morate biti prijavljeni da biste lajkovali knjigu'
            ], 401);
        }

        $existingLike = Like::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $isLiked = false;
            $message = 'Knjiga je odlajkovana';
        } else {
            Like::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
            $isLiked = true;
            $message = 'Knjiga je lajkovana';
        }

        $likesCount = $book->likes()->count();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'is_liked' => $isLiked,
                'likes_count' => $likesCount,
            ]
        ]);
    }

    public function status(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();
        
        $isLiked = $user ? $book->isLikedBy($user) : false;
        $likesCount = $book->likes()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'is_liked' => $isLiked,
                'likes_count' => $likesCount,
            ]
        ]);
    }
}