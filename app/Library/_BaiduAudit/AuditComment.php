<?php


namespace Zhiyi\Plus\Lib\BaiduAudit;


use Zhiyi\Plus\Lib\Log\Elog;
use Zhiyi\Plus\Models\BaiduAuditItem;
use Zhiyi\Plus\Models\Comment;
use Zhiyi\Plus\Packages\AuditService\BaiduVcrClient;

class AuditComment extends Audit
{

    /**
     * @param $id
     * @return array
     */
    public function getContentData($id)
    {
        $commentModel = Comment::whereId($id)->first();
        if (!$commentModel) {
            Elog::cliLog(AuditService::LOG_MODULE_NAME, "commentQuery", sprintf("查询不到Comment记录，ID为【%s】", $id), Elog::LEVEL_ERROR);
            return [];
        }

        //灌水评论不进行审核
        if ($commentModel->is_virtual) {
            return [];
        }

        return [[
            'resourceType' => BaiduVcrClient::RES_TYPE_TEXT,
            'data' => [
                'source' => $commentModel->body,
                'type' => BaiduAuditItem::TYPE_COMMENT,
                'id' => $id,
            ]
        ]];
    }
}
