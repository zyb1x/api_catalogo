<?php

use App\Http\Controllers\HerramientasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrestamosController;

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

Route::post('/register', [AuthController::class, 'registro']);
Route::post('/login',    [AuthController::class, 'login']);


Route::get('/herramientas',          [HerramientasController::class, 'index']);
Route::get('/herramientas/{id}',     [HerramientasController::class, 'show']);
Route::post('/herramientas',         [HerramientasController::class, 'store']);
Route::match(['put', 'post'], '/herramientas/{id}', [HerramientasController::class, 'update']);
Route::delete('/herramientas/{id}',  [HerramientasController::class, 'destroy']);

Route::get('/categorias', fn() => response()->json([
    'resultado' => true,
    'datos' => \App\Models\Categoria::all()
]));


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::get('/user',          [AuthController::class, 'perfil']);
    Route::put('/user',          [AuthController::class, 'actualizar']);
    Route::post('/user/imagen',  [AuthController::class, 'actualizarAvatar']);
    Route::put('/user/password', [AuthController::class, 'actualizarContrasena']);

    Route::get('/prestamos',              [PrestamosController::class, 'index']);
    Route::get('/prestamos/{id}',         [PrestamosController::class, 'show']);
    Route::post('/prestamos',              [PrestamosController::class, 'store']);
    Route::patch('/prestamos/{id}/cancelar', [PrestamosController::class, 'cancelar']);
});
