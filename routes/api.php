<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\StatisticsController;
use Illuminate\Support\Facades\Route;

// 1. Manajemen Penulis (Author)
Route::apiResource('authors', AuthorController::class);

// 2. Manajemen Kategori (Category)
Route::apiResource('categories', CategoryController::class);

// 4.e Endpoint buku stok hampir habis (< 5)
Route::get('books/low-stock', [BookController::class, 'lowStock']);

// 3. Manajemen Buku (Book)
Route::apiResource('books', BookController::class);

// 4.d Statistik Perpustakaan
Route::get('statistics', [StatisticsController::class, 'index']);
