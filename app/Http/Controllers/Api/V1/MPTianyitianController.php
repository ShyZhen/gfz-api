<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TianyitianRedpackage;
use Illuminate\Http\Response;

class MPTianyitianController extends Controller
{
    public function getRedList()
    {
        $rows = TianyitianRedpackage::where([
            'is_deleted' => 0,
        ])->orderByDesc('id')
            ->get();

        return response()->json(
            ['data' => $rows],
            Response::HTTP_OK
        );
    }
}
