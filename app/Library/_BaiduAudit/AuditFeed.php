<?php


namespace Zhiyi\Plus\Lib\BaiduAudit;


use Illuminate\Support\Facades\DB;
use Zhiyi\Plus\Lib\Log\Elog;
use Zhiyi\Plus\Models\BaiduAuditItem;
use Zhiyi\Plus\Models\Comment;
use Zhiyi\Plus\Models\Feed;
use Zhiyi\Plus\Packages\AuditService\BaiduVcrClient;

class AuditFeed extends Audit
{

    /**
     * @param $id
     * @return array
     */
    public function getContentData($id)
    {
        $feedModel = Feed::where('id', $id)->first();
        if (!$feedModel) {
            Elog::cliLog(AuditService::LOG_MODULE_NAME, 'feedQuery', sprintf("查询不到Feed记录，ID为【%s】", $id), Elog::LEVEL_ERROR);
            return [];
        }

        $feedData = [];
        if ($feedModel->title) {
            $feedData[] = [
                'resourceType' => BaiduVcrClient::RES_TYPE_TEXT,
                'data' => [
                    'source' => $feedModel->title,
                    'type' => BaiduAuditItem::TYPE_FEED_TITLE,
                    'id' => $id,
                ]
            ];
        }

        if ($feedModel->feed_content) {
            $feedData[] = [
                'resourceType' => BaiduVcrClient::RES_TYPE_TEXT,
                'data' => [
                    'source' => $feedModel->feed_content,
                    'type' => BaiduAuditItem::TYPE_FEED_CONTENT,
                    'id' => $id,
                ]
            ];
        }

        if ($feedModel->images) {
            foreach ($feedModel->images as $imageUrl) {
                $feedData[] = [
                    'resourceType' => BaiduVcrClient::RES_TYPE_IMAGE,
                    'data' => [
                        'source' => $imageUrl->url(),
                        'type' => BaiduAuditItem::TYPE_FEED_IMAGE,
                        'id' => $id,
                    ]
                ];
            }
        }

        if ($feedModel->video && $feedModel->video['resource']) {
            $feedData[] = [
                'resourceType' => BaiduVcrClient::RES_TYPE_MEDIA,
                'data' => [
                    'source' => $feedModel->video['resource']->url(),
                    'type' => BaiduAuditItem::TYPE_FEED_MEDIA,
                    'id' => $id,
                ]
            ];
        }


        return $feedData;
    }
}
