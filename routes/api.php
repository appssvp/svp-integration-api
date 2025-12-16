<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\HCapturaComentarioController;
use App\Http\Controllers\Api\HIngresoController;
use App\Http\Controllers\Api\HCapturaOperacionController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\HCapturaEvasionController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\ListaPersonalOperacionController;
use App\Http\Controllers\Api\HCapturaProveedorController;
use App\Http\Controllers\Api\ProductividadController;
use App\Http\Controllers\Api\HCapturaSaldoProveedorController;
use App\Http\Controllers\Api\SqlSvp\HistoricoViajesController;
use App\Http\Controllers\Api\SqlSvp\FraudesController;
use App\Http\Controllers\Api\DictamenAppController;
use App\Http\Controllers\Api\HCapturaForzadoController;



// API pública de prueba
Route::get('/prueba', function () {
    return response()->json([
        'mensaje' => 'API funcionando correctamente ',
        'hora' => now()->toDateTimeString()
    ]);
});

Route::get('/comentarios', [HCapturaComentarioController::class, 'index']);

//Route::get('/comentarios-xml', [HCapturaComentarioController::class, 'indexXml']);

Route::middleware('auth:sanctum')->get('/comentarios-xml', [HCapturaComentarioController::class, 'indexXml']);

// Ruta para el resumen de los ingresos 
Route::middleware('auth:sanctum')->get('/h-ingresos', [HIngresoController::class, 'index']);
Route::middleware('auth:sanctum')->get('/h-ingresos/resumen', [HIngresoController::class, 'resumen']);
Route::middleware('auth:sanctum')->get('/importe-recuperado', [HIngresoController::class, 'importeRecuperado']);
Route::middleware('auth:sanctum')->get('/resumen-estatus', [HIngresoController::class, 'resumenPorEstatus']);
Route::middleware('auth:sanctum')->get('/ingresos/resumen-por-mes', [HIngresoController::class, 'resumenPorMes']);
Route::middleware('auth:sanctum')->get('/ingresos/resumen-dia', [HIngresoController::class, 'resumenPorDia']);
Route::middleware('auth:sanctum')->get('/ingresos/resumen-hora', [HIngresoController::class, 'resumenPorHora']);
Route::middleware('auth:sanctum')->get('/ingresos/resumen-anio', [HIngresoController::class, 'resumenPorAnio']);
Route::middleware('auth:sanctum')->get('/ingresos/resumen-total-mensual', [HIngresoController::class, 'resumenTotalPorMes']);



// Ruta para el resumen de los capturas 
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-por-mes', [HCapturaOperacionController::class, 'resumenPorMes']);
Route::middleware('auth:sanctum')->get('/comentarios-captura', [HCapturaComentarioController::class, 'index']);
Route::middleware('auth:sanctum')->get('/resumen-capturas', [HCapturaComentarioController::class, 'resumenPorLugarYTipo']);
Route::middleware('auth:sanctum')->get('/capturas-operacion', [HCapturaOperacionController::class, 'index']);
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-horas', [HCapturaOperacionController::class, 'resumenPorHora']);
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-dia-captura', [HCapturaOperacionController::class, 'resumenPorDiaCaptura']);
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-meses', [HCapturaOperacionController::class, 'resumenPorMesCaptura']);
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-anual-captura', [HCapturaOperacionController::class, 'resumenPorAnioCaptura']);







Route::middleware('auth:sanctum')->get('/capturas-evasiones', [HCapturaEvasionController::class, 'index']);
Route::middleware('auth:sanctum')->get('/capturas-evasiones/resumen-mensual', [HCapturaEvasionController::class, 'resumenMensualSimple']);

// Detalle de forzados
Route::middleware('auth:sanctum')->get('/captura-forzados', [HCapturaForzadoController::class, 'index']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-via', [HCapturaForzadoController::class, 'resumenPorVia']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-estatus', [HCapturaForzadoController::class, 'resumenPorEstatus']);
Route::middleware('auth:sanctum')->get('/captura-forzados/importe-recuperado', [HCapturaForzadoController::class, 'importeRecuperado']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-por-mes-forzados', [HCapturaForzadoController::class, 'resumenPorMes']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-hora', [HCapturaForzadoController::class, 'resumenPorHora']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-dia', [HCapturaForzadoController::class, 'resumenPorDia']);
Route::middleware('auth:sanctum')->get('/captura-forzados/resumen-anual', [HCapturaForzadoController::class, 'resumenPorAnio']);









// tabla de resumen de captura por operacion
Route::middleware('auth:sanctum')->get('/capturas-operacion/resumen-captura', [HCapturaOperacionController::class, 'resumenPorLugar']);

// tabla de resumen de captura de comentarios
Route::middleware('auth:sanctum')->get('/comentarios-captura/resumen-comentarios', [HCapturaComentarioController::class, 'resumenPorComentario']);
Route::middleware('auth:sanctum')->get('/comentarios-captura/resumen-lugares', [HCapturaComentarioController::class, 'resumenPorLugar']);
Route::middleware('auth:sanctum')->get('/comentarios-captura/resumen-por-mes', [HCapturaComentarioController::class, 'resumenPorMes']);


// Proveedores
Route::middleware('auth:sanctum')->get('/proveedores', [ProveedorController::class, 'index']);
Route::middleware('auth:sanctum')->post('/proveedores', [ProveedorController::class, 'store']);
Route::middleware('auth:sanctum')->put('/proveedores/{id}', [ProveedorController::class, 'update']);
Route::middleware('auth:sanctum')->delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);



// Lista de Porveedores
Route::middleware('auth:sanctum')->get('/lista-personal-operacion', [ListaPersonalOperacionController::class, 'index']);
Route::middleware('auth:sanctum')->get('/capturas-proveedores', [HCapturaProveedorController::class, 'index']);


Route::middleware('auth:sanctum')->get('/productividad/resumen-nombres', [ProductividadController::class, 'resumenPorNombre']);
Route::middleware('auth:sanctum')->get('/productividad/resumen-por-operador', [ProductividadController::class, 'resumenPorNombreEspecifico']);
Route::middleware('auth:sanctum')
    ->get('/productividad/app-movil-detalle', 
        [ProductividadController::class, 'detalleAppMovil']
    );

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/productividad/resumen-por-operador/resumen-por-nombre-especifico', [ProductividadController::class, 'resumenPorNombreEspecifico']);
    Route::get('/productividad/resumen-por-nombre', [ProductividadController::class, 'resumenPorNombre']);
});
    



// Lista de personal
Route::middleware('auth:sanctum')->post('/lista-personal-operacion', [ListaPersonalOperacionController::class, 'store']);
Route::middleware('auth:sanctum')->delete('/lista-personal-operacion/{id}', [ListaPersonalOperacionController::class, 'destroy']);
Route::middleware('auth:sanctum')->put('/lista-personal-operacion/{id}', [ListaPersonalOperacionController::class, 'update']);


// Saldos
Route::middleware('auth:sanctum')->get('/saldos-recibidos', [HCapturaSaldoProveedorController::class, 'index']);
Route::middleware('auth:sanctum')->get('/saldos-recibidos/resumen-{proveedor}', [HCapturaSaldoProveedorController::class, 'resumenProveedor']);



//Historico de viajes svp
Route::middleware('auth:sanctum')->get('/svp/saldo-tag', [HistoricoViajesController::class, 'obtenerSaldoPorTag']);
Route::middleware('auth:sanctum')->get('/svp/cruces-tag', [HistoricoViajesController::class, 'obtenerCrucesPorTag']);
Route::middleware('auth:sanctum')->get('/svp/cruces-frecuentes', [HistoricoViajesController::class, 'obtenerResumenFrecuentePorTag']);
Route::middleware('auth:sanctum')->get('/svp/matriculas-frecuentes', [HistoricoViajesController::class, 'obtenerMatriculasFrecuentesPorTag']);
Route::middleware('auth:sanctum')->get('/svp/tag-aus-matricula', [HistoricoViajesController::class, 'obtenerTagAusPorMatricula']);
Route::middleware('auth:sanctum')->get('svp/tag-por-matricula', [HistoricoViajesController::class, 'obtenerTagPorMatricula']);

//Fraudes
Route::middleware('auth:sanctum')->get('/fraudes/resumen', [FraudesController::class, 'obtenerResumenFraudesPorFecha']);




// Dictamen apoyos
Route::middleware('auth:sanctum')->get('/ingresos/rango', [DictamenAppController::class, 'obtenerIngresosPorRango']);
Route::middleware('auth:sanctum')->get('/cruces/tag', [DictamenAppController::class, 'obtenerCrucesPorTag']);
Route::middleware('auth:sanctum')->get('/cruces/placa', [DictamenAppController::class, 'obtenerCrucesPorPlaca']);
Route::middleware('auth:sanctum')->post('/dictamen-app', [DictamenAppController::class, 'store']);
Route::middleware('auth:sanctum')->get('/forzados/rango', [DictamenAppController::class, 'obtenerForzadosPorRango']);
Route::middleware('auth:sanctum')->post('/dictamen-forzados/store', [DictamenAppController::class, 'storeForzados']);




Route::get('/imagen-local', function () {
    $path = storage_path('app/public/imagenes/carro1.jpg');

    if (file_exists($path)) {
        return Response::file($path);
    }

    return response()->json(['error' => 'Imagen no encontrada'], 404);
});



Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    $user = $request->user();
    $token = $user->createToken('Token API SIMUCI')->plainTextToken;

    return response()->json(['token' => $token]);
});

