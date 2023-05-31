<?php

namespace Zhiyi\Plus\Lib\BaiduAudit;

use Zhiyi\Plus\Lib\Log\Elog;
use Zhiyi\Plus\Models\Answer;
use Zhiyi\Plus\Models\BaiduAuditItem;
use Zhiyi\Plus\Packages\AuditService\BaiduVcrClient;

class AuditAnswer extends Audit
{

    /**
     * 获取需要审核的数据
     * 通过 `AuditService::getAuditData()` 调用到此处
     *
     * @param $id
     * @return array
     */
    public function getContentData($id)
    {
        $model = Answer::whereId($id)->first();
        if (!$model) {
            Elog::cliLog(AuditService::LOG_MODULE_NAME, 'answerQuery', sprintf("查询不到answer记录，ID为【%s】", $id), Elog::LEVEL_ERROR);
            return [];
        }

        $data = [];

        // summary 即去除样式/媒体的所有文本输入 不使用content字段
        if ($model->summary) {
            $data[] = [
                'resourceType' => BaiduVcrClient::RES_TYPE_TEXT,
                'data' => [
                    'source' => $model->summary,
                    'type' => BaiduAuditItem::TYPE_ANSWER_CONTENT,
                    'id' => $id,
                ]
            ];
        }

        if ($model->images) {
            foreach ($model->images as $imageUrl) {
                $data[] = [
                    'resourceType' => BaiduVcrClient::RES_TYPE_IMAGE,
                    'data' => [
                        'source' => $imageUrl->url(),
                        'type' => BaiduAuditItem::TYPE_ANSWER_IMAGE,
                        'id' => $id,
                    ]
                ];
            }
        }

        if ($model->video && $model->video['resource']) {
            $data[] = [
                'resourceType' => BaiduVcrClient::RES_TYPE_MEDIA,
                'data' => [
                    'source' => $model->video['resource']->url(),
                    'type' => BaiduAuditItem::TYPE_ANSWER_MEDIA,
                    'id' => $id,
                ]
            ];
        }
        return $data;
    }
}
