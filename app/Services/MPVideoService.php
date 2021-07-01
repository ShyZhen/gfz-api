<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */

namespace App\Services;

use Illuminate\Http\Response;
use App\Repositories\Eloquent\MPVideoItemRepository;

class MPVideoService extends Service
{
    private $mpVideoItemRepository;

    /**
     * @param MPVideoItemRepository $mpVideoItemRepository
     */
    public function __construct(
        MPVideoItemRepository $mpVideoItemRepository
    ) {
        $this->mpVideoItemRepository = $mpVideoItemRepository;
    }

    public function getListByType($type): \Illuminate\Http\JsonResponse
    {
        $data = $this->mpVideoItemRepository->model()
            ::where('type', $type)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    public function getAllList(): \Illuminate\Http\JsonResponse
    {
        $data = $this->mpVideoItemRepository->model()
            ::where('type', '<>', '')
            ->orderByDesc('id')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    public function getScroll($type, $id): \Illuminate\Http\JsonResponse
    {
        $data['current'] = $this->mpVideoItemRepository->find($id);
        if ($type == 1) {
            // ↑前10后3
            $data['pre_video'] = $this->mpVideoItemRepository->get10Items(1, $id, 10);
            $data['after_video'] = $this->mpVideoItemRepository->get10Items(2, $id, 3);
        } else {
            // ↓前3后10
            $data['pre_video'] = $this->mpVideoItemRepository->get10Items(1, $id, 3);
            $data['after_video'] = $this->mpVideoItemRepository->get10Items(2, $id, 10);
        }

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }
}
