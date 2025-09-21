<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\SearchController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/pages/{slug}', [PageController::class, 'show'])
    ->name('pages.show');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::get('/post/{bulan}/{tahun}/{slug}', [PostController::class, 'show'])
    ->whereNumber('bulan')     // 1–12, kita cek lagi di controller
    ->whereNumber('tahun')     // 4 digit
    ->name('posts.show');

Route::get('/category/{slug}', [ArchiveController::class, 'category'])->name('category.show');
Route::get('/tag/{slug}', [ArchiveController::class, 'tag'])->name('tag.show');

Route::get('/search', [SearchController::class, 'index'])->name('search');
