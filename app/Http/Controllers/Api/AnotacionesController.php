<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Controller: AnotacionesController
 * API para consultar anotaciones de eventos del CAD.
 */
class AnotacionesController extends Controller
{
    /**
     * Obtiene las anotaciones de un evento por su numero de secuencia.
     */
    public function index(string $numeroSecuencia): JsonResponse
    {
        try {
            $result = DB::connection('sqlsrv_cad')
                ->select('EXEC Sp_Anotaciones @NumeroSecuencia = ?', [$numeroSecuencia]);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar anotaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
