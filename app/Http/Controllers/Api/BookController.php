<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Menampilkan daftar semua buku dengan informasi penulis dan kategori,
     * serta mendukung pencarian dan filter (kategori, ketersediaan stok).
     */
    public function index(Request $request)
    {
        $query = Book::with(['author', 'categories']);

        // 4.a Pencarian buku berdasarkan judul atau nama penulis
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('author', function ($authorQuery) use ($search) {
                      $authorQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 4.b Filter buku berdasarkan kategori (bisa berdasarkan ID, slug, atau nama kategori)
        if ($request->filled('category')) {
            $category = $request->query('category');
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('slug', $category)
                  ->orWhere('name', 'like', "%{$category}%")
                  ->orWhere('categories.id', $category);
            });
        }

        // 4.c Filter buku berdasarkan ketersediaan stok (stok > 0)
        if ($request->has('available') && filter_var($request->query('available'), FILTER_VALIDATE_BOOLEAN)) {
            $query->where('stock', '>', 0);
        }

        // Filter stok habis / menipis (< 5) melalui query parameter
        if ($request->has('low_stock') && filter_var($request->query('low_stock'), FILTER_VALIDATE_BOOLEAN)) {
            $query->where('stock', '<', 5);
        }

        return response()->json($query->get(), 200);
    }

    /**
     * Menambah buku baru (dengan memilih penulis dan kategori).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'published_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'author_id' => 'required|exists:authors,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book = Book::create($validated);

        // Pasangkan kategori jika disertakan
        if (!empty($validated['category_ids'])) {
            $book->categories()->sync($validated['category_ids']);
        }

        return response()->json($book->load(['author', 'categories']), 201);
    }

    /**
     * Menampilkan detail buku beserta informasi penulis dan kategorinya.
     */
    public function show(Book $book)
    {
        return response()->json($book->load(['author', 'categories']), 200);
    }

    /**
     * Mengupdate data buku (termasuk stok, harga, dan relasi kategori).
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id,
            'published_year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'author_id' => 'sometimes|exists:authors,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $book->update($validated);

        if (array_key_exists('category_ids', $validated)) {
            $book->categories()->sync($validated['category_ids'] ?? []);
        }

        return response()->json($book->load(['author', 'categories']), 200);
    }

    /**
     * Menghapus buku.
     */
    public function destroy(Book $book)
    {
        $book->categories()->detach();
        $book->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus'
        ], 200);
    }

    /**
     * 4.e Menampilkan daftar buku yang stoknya hampir habis (< 5).
     */
    public function lowStock()
    {
        $lowStockBooks = Book::with(['author', 'categories'])
            ->where('stock', '<', 5)
            ->get();

        return response()->json($lowStockBooks, 200);
    }
}
