<?php
/**
 * 配料表Wiki
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2023/8/9
 * Time: 15:22
 */

namespace App\Http\Controllers\Api\V1;

use App\Models\Wiki;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

class WikiController extends Controller
{

    /**
     * 获取当前ocr的数据详情
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWikiDetail(Request $request)
    {
        $row = [];
        $title = $request->get('title');
        if ($title) {
            $row = Wiki::where('title', $title)
                ->orWhere('title','like','%'.$title.'%')
                ->get();
        }

        return response()->json(
            ['data' => $row],
            Response::HTTP_OK
        );
    }

}
