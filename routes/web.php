<?php

use App\Http\Controllers\ProfileController;
use App\Http\Livewire\Admin\Card\Cards;
use App\Http\Livewire\Admin\Card\Create as CardCreate;
use App\Http\Livewire\Admin\Card\Edit as CardEdit;
use App\Http\Livewire\Admin\Category\Categoies;
use App\Http\Livewire\Admin\Category\Create as CategoryCreate;
use App\Http\Livewire\Admin\Category\Edit as CategoryEdit;
use App\Http\Livewire\Admin\Dashboard;
use App\Http\Livewire\Admin\Logs;
use App\Http\Livewire\Admin\Platform\Create as PlatformCreate;
use App\Http\Livewire\Admin\Platform\Edit as PlatformEdit;
use App\Http\Livewire\Admin\Platform\Platforms;
use App\Http\Livewire\Admin\User\Edit as UserEdit;
use App\Http\Livewire\Admin\User\Users;
use App\Models\Card;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/card_id/{uuid}', function ($uuid) {

    $user = Card::join('user_cards', 'cards.id', 'user_cards.card_id')
        ->join('users', 'users.id', 'user_cards.user_id')
        ->where('cards.uuid', $uuid)
        ->get()
        ->first();

    if (!$user) {
        return abort(404);
    }

    dd($user);
});

Route::fallback(function () {
    if (request()->segment(1) == 'admin') {
        return redirect()->route('admin.login.form');
    }
    return abort(404);
});

Route::get('/optimize', function () {
    Artisan::call('optimize:clear');
});

Route::get('/storage-link', function () {
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
    dd("done");
});

Route::middleware('auth:admin')->group(function () {

    Route::get('admin/dashboard', Dashboard::class);

    // users
    Route::get('admin/users', Users::class);
    Route::get('admin/user/{id}/edit', UserEdit::class);

    // categories
    Route::get('admin/categories', Categoies::class);
    Route::get('admin/category/create', CategoryCreate::class);
    Route::get('admin/category/{id}/edit', CategoryEdit::class);

    // platforms
    Route::get('admin/platforms', Platforms::class);
    Route::get('admin/platform/create', PlatformCreate::class);
    Route::get('admin/platform/{id}/edit', PlatformEdit::class);

    // categories
    Route::get('admin/cards', Cards::class);
    Route::get('admin/card/create', CardCreate::class);
    Route::get('admin/card/{id}/edit', CardEdit::class);
    Route::get('/downloadCardsCSV', [Cards::class, 'downloadCsv'])->name('export');

    // logs
    Route::get('admin/logs', Logs::class);

    // profile
    Route::post('/changePassword', [ProfileController::class, 'changePassword'])->name('profile.change.password');
});

// card_id
Route::get('/card_id/{uuid}', function ($uuid) {

    $user = Card::join('user_cards', 'cards.id', 'user_cards.card_id')
        ->join('users', 'users.id', 'user_cards.user_id')
        ->where('cards.uuid', $uuid)
        ->get()
        ->first();
    if (!$user) {
        return abort(404);
    }

    $userPlatforms = [];
    $platforms = DB::table('user_platforms')
        ->select(
            'platforms.id',
            'platforms.title',
            'platforms.icon',
            'platforms.input',
            'platforms.baseUrl',
            'user_platforms.created_at',
            'user_platforms.path',
            'user_platforms.label',
            'user_platforms.platform_order',
            'user_platforms.direct',
        )
        ->join('platforms', 'platforms.id', 'user_platforms.platform_id')
        ->where('user_id', $user->id)
        ->orderBy(('user_platforms.platform_order'))
        ->get();

    for ($i = 0; $i < $platforms->count(); $i++) {
        array_push($userPlatforms, $platforms[$i]);
    }

    $userPlatforms = array_chunk($userPlatforms, 4);

    return view('profile', compact('user', 'userPlatforms'));
});

require __DIR__ . '/auth.php';
