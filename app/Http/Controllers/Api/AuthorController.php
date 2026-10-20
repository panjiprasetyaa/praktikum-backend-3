<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Menampilkan daftar semua penulis.
     */
    public function index()
    {
        $authors = Author::withCount('books')->get();
        return response()->json($authors, 200);
    }

    /**
     * Menambah penulis baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:authors,email',
            'bio' => 'nullable|string',
        ]);

        $author = Author::create($validated);
        return response()->json($author, 201);
    }

    /**
     * Menampilkan detail penulis beserta daftar bukunya.
     */
    public function show(Author $author)
    {
        return response()->json($author->load('books'), 200);
    }

    /**
     * Mengupdate data penulis.
     */
    public function update(Request $request, Author $author)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:authors,email,' . $author->id,
            'bio' => 'nullable|string',
        ]);

        $author->update($validated);
        return response()->json($author, 200);
    }

    /**
     * Menghapus penulis (dengan konfirmasi bahwa buku terkait juga terhapus).
     */
    public function destroy(Author $author)
    {
        $booksCount = $author->books()->count();
        $authorName = $author->name;

        $author->delete();

        return response()->json([
            'message' => "Penulis '{$authorName}' berhasil dihapus.",
            'confirmation' => "Sebanyak {$booksCount} buku terkait penulis ini juga telah dihapus secara cascade."
        ], 200);
    }
}
