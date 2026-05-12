<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CobradorEstado;
use Illuminate\Http\Request;

class CobradorEstadoApiController extends Controller
{
    public function sync(Request $request)
    {
        $estado = CobradorEstado::updateOrCreate(
            [
                'cobrador_id' => $request->cobrador_id
            ],
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $estado
        ]);
    }
}