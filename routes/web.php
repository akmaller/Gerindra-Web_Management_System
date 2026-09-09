<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::redirect('/ppid', '/pages/profil-ppid', 301)->name('ppid');
Route::redirect('/anggota-dewan', '/pages/anggota-legislatif', 301)->name('members.council');
Route::redirect('/layanan-ambulans', '/pages/ambulance', 301)->name('ambulance');
Route::redirect('/layanan-ambulance', '/layanan-ambulans', 301);
Route::redirect('/pages/anggaran-ppid', '/pages/anggaan-ppid', 301);

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

Route::post('/chatbot/message', [ChatbotController::class, 'send'])
    ->middleware('throttle:20,1')->name('chatbot.message');
