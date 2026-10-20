<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;

class StatisticsController extends Controller
{
    /**
     * 4.d Menampilkan statistik: total buku, total penulis, total kategori.
     */
    public function index()
    {
        return response()->json([
            'total_books' => Book::count(),
            'total_authors' => Author::count(),
            'total_categories' => Category::count(),
            'total_available_books' => Book::where('stock', '>', 0)->count(),
            'total_low_stock_books' => Book::where('stock', '<', 5)->count(),
        ], 200);
    }
}
