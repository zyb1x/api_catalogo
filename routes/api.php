<?php

use App\Http\Controllers\HerramientasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/herramientas',          [HerramientasController::class, 'index']);
Route::get('/herramientas/{id}',     [HerramientasController::class, 'show']);
Route::post('/herramientas',         [HerramientasController::class, 'store']);
Route::match(['put', 'post'], '/herramientas/{id}', [HerramientasController::class, 'update']);
Route::delete('/herramientas/{id}',  [HerramientasController::class, 'destroy']);

Route::get('/categorias', fn() => response()->json([
    'resultado' => true,
    'datos' => \App\Models\Categoria::all()
]));

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
