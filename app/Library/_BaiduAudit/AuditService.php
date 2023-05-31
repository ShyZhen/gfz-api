<?php


namespace Zhiyi\Plus\Lib\BaiduAudit;


use Exception;
use Illuminate\Database\Eloquent\Builder;
use PhpAmqpLib\Message\AMQPMessage;
use Zhiyi\Plus\Exceptions\BaiduAuditNotifyException;
use Zhiyi\Plus\Exceptions\RabbitMqClientException;
use Zhiyi\Plus\Lib\Log\Elog;
use Zhiyi\Plus\Models\Answer;
use Zhiyi\Plus\Models\BaiduAuditItem;
use Zhiyi\Plus\Models\Comment;
use Zhiyi\Plus\Models\Feed;
use Zhiyi\Plus\Models\Question;
use Zhiyi\Plus\Models\Sensitive;
use Zhiyi\Plus\Packages\AuditService\BaiduVcrClient;
use Zhiyi\Plus\Services\Notice;
use Zhiyi\Plus\Utils\RabbitMqClient;
use Zhiyi\Plus\Utils\Server;
use Zhiyi\Plus\Utils\Times;

class AuditService
{
    /**
     * @var string 推送消息类型
     */
    const MESSAGE_TYPE_FEED = 'feed';
    const MESSAGE_TYPE_COMMENT = 'comment';
    const MESSAGE_TYPE_QUESTION = 'question';
    const MESSAGE_TYPE_ANSWER = 'answer';

    /**
     * 审核不通过提示内容
     */
    const AUDIT_REJECT_TIPS = '涉及敏感内容';

    /**
     * @var string 推送记录redis key
     */
    const REDIS_PUSH_RECORD_KEY = 'baiduAudit:pushRecord:%s:%d';
    const REDIS_PUSH_RECORD_KEY_TTL = '1800';  //最多等百度30分钟返回，否则允许重试
    const REDIS_PUSH_RECORD_LOCK_KEY = 'baiduAudit:pushRecordLock:%s:%d';
    const REDIS_PUSH_RECORD_LOCK_TTL = '120'; //同一条记录2分钟内不允许重复推送
    const REDIS_PUSH_RECORD_COUNT_KEY = "baiduAudit:pushRecordCount:%s:%d";

    /**
     * @var string  记录里日志名称
     */
    const LOG_MODULE_NAME = 'baidu-audit';


    /**
     * @var string 参数加密
     */
    const ENCRYPT_PARAM = 'key';
    const ENCRYPT_RANDOM_LEN = 10;

    /**
     * @var string 推送和接收队列
     */
    public static $pushQueueName = 'baidu_audit_push_queue';
    public static $receiveQueueName = 'baidu_audit_receive_queue';

    /**
     * 异步推送需要审核数据到mq
     *
     * @param $method
     * @param array $data
     * @return bool
     * @throws RabbitMqClientException
     */
    public static function publishData($method, array $data)
    {
        //开发环境不审核
        if (Server::isLocal()) {
            return false;
        }

        $message = [
            'method' => $method,
            'data' => $data,
        ];

        return RabbitMqClient::getInstance()->publish(self::$pushQueueName, $message);
    }

    /**
     * 接收百度返回结果数据到mq
     *
     * @param $message
     * @return bool
     * @throws RabbitMqClientException
     */
    public static function receiveBaiduData($message)
    {
        if (empty($message)) {
            return false;
        }

        return RabbitMqClient::getInstance()->publish(self::$receiveQueueName, $message);
    }


    /**
     * @throws RabbitMqClientException
     */
    public static function pushToBaidu()
    {
        $mq = RabbitMqClient::getInstance();
        $mq->consume(self::$pushQueueName, function (AMQPMessage $message) {
            $messageData = json_decode($message->getBody(), true);
            if (!$messageData) {
                $message->ack();
                return false;
            }

            //根据mq消息获取具体需要审核的消息
            $data = self::getAuditData($messageData);
            if (!$data) {
                $message->ack();
                return false;
            }

            //推送百度审核
            self::handleMessage($messageData, $data);

            $message->ack();
        });
    }

    /**
     * 处理MQ消息
     *
     * @param $messageData
     * @param $data
     */
    private static function handleMessage($messageData, $data)
    {
        $lockKey = sprintf(self::REDIS_PUSH_RECORD_LOCK_KEY, $messageData['method'], $messageData['data']['id']);
        $countKey = sprintf(self::REDIS_PUSH_RECORD_COUNT_KEY, $messageData['method'], $messageData['data']['id']);
        if (app()->redis->set($lockKey, 1, 'ex', self::REDIS_PUSH_RECORD_LOCK_TTL, 'nx')) {
            app()->redis->set($countKey, count($data), 'ex', '3600');
            foreach ($data as $item) {
                try {
                    $redisKey = self::getPushRecordKey($messageData['method'], $messageData['data']['id']);
                    $member = self::getPushRecordMember(
                        $item['data']['id'],
                        $item['data']['type'],
                        $item['data']['source']
                    );

                    if (!app()->redis->sismember($redisKey, $member)) {
                        $res = self::toBaidu($item);
                        self::addPushRecord($redisKey, $member);

                        //文本类型同步返回数据
                        if ($item['resourceType'] == BaiduVcrClient::RES_TYPE_TEXT) {
                            self::saveTextEvidence($res, $item['data']['type'], $item['data']['id']);
                        }
                    } else {
                        Elog::cliLog(
                            self::LOG_MODULE_NAME,
                            'toBaidu',
                            sprintf("重复推送item，ID【%s】-类型【%s】", $messageData['data']['id'], $item['data']['type']),
                            Elog::LEVEL_ERROR
                        );
                    }
                } catch (Exception $e) {
                    app()->redis->decr($countKey);
                    $err = Elog::formatExceptionMsg($e);
                    Elog::cliLog(
                        AuditService::LOG_MODULE_NAME,
                        'toBaidu',
                        sprintf(
                            "baiduAuditPush:%s%s%s%s",
                            json_encode($messageData),
                            PHP_EOL,
                            $err,
                            PHP_EOL
                        ),
                        Elog::LEVEL_ERROR
                    );
                }
            }
            app()->redis->del($lockKey);
        } else {
            Elog::cliLog(
                self::LOG_MODULE_NAME,
                'toBaidu',
                sprintf("重复推送，类型【%s】-id【%s】", $messageData['method'], $messageData['data']['id']),
                Elog::LEVEL_ERROR
            );
        }
    }

    /**
     * 处理审核结果
     *
     * @throws RabbitMqClientException
     */
    public static function handleAuditResult()
    {
        $mq = RabbitMqClient::getInstance();

        $mq->consume(self::$receiveQueueName, function (AMQPMessage $message) {
            $messageData = json_decode($message->getBody(), true);
            if (!$messageData) {
                $message->ack();
                return false;
            }
            try {
                self::handleAuditResultMessage($messageData);
            } catch (\Exception $e) {
                $err = Elog::formatExceptionMsg($e);
                Elog::cliLog(AuditService::LOG_MODULE_NAME, 'receiveBaiduResult', sprintf(
                    "baiduAuditReceive:%s%s%s%s",
                    json_encode($messageData),
                    PHP_EOL,
                    $err,
                    PHP_EOL
                ), Elog::LEVEL_ERROR);
            } finally {
                $message->ack();
            }
        });
    }

    /**
     * 处理审核结果
     *
     * @param $messageData
     * @return bool
     * @throws BaiduAuditNotifyException
     */
    public static function handleAuditResultMessage($messageData)
    {
        $data = is_string($messageData['messageBody']) ?
            json_decode($messageData['messageBody'], true) : $messageData['messageBody'];
        $params = self::parseResultMessage($data);

        $saveData = [
            'messageId' => $messageData['messageId'],
            'source' => $data['source'],
            'label' => $data['label'],
            'type' => $params['type'],
            'id' => $params['id'],
            'createTime' => $params['createTime'] ?? '',
            'finishTime' => $params['finishTime'] ?? '',
            'evidence' => json_encode(self::assembleEvidence($data['results'] ?? []), JSON_UNESCAPED_UNICODE),
        ];

        return self::saveEvidence($saveData);
    }

    /**
     * @param $data
     * @return bool
     */
    private static function saveEvidence($data)
    {
        //移除记录
        self::removePushRecord($data);

        $model = BaiduAuditItem::query()
            ->where('type', $data['type'])
            ->when($data['type'] == BaiduAuditItem::TYPE_FEED_IMAGE, static function (Builder $builder) use ($data) {
                return $builder->where('source', $data['source']);
            })
            ->where('item_id', $data['id'])
            ->first();
        if (!$model) {
            $model = new BaiduAuditItem();
        }

        $model->message_id = $data['messageId'];
        $model->source = $data['source'];
        $model->status = $data['label'];
        $model->type = $data['type'];
        $model->item_id = $data['id'];
        $model->audit_create_time = $data['createTime'] ?? '';
        $model->audit_finish_time = $data['finishTime'] ?? '';
        $model->evidence = $data['evidence'];
        $model->save();

        $countKey = sprintf(self::REDIS_PUSH_RECORD_COUNT_KEY, self::getMethodNameByType($data['type']), $data['id']);
        if (
            0 === ($c = app()->redis->decr($countKey))
            || $model->status != BaiduVcrClient::RESULT_NORMAL
        ) {
            self::changeFeedOrCommentAuditStatus($data);
            0 >= $c && app()->redis->del($countKey);
        }

        return true;
    }

    /**
     * 修改原动态或评论的状态
     *
     * @param $data
     * @return bool
     */
    protected static function changeFeedOrCommentAuditStatus($data)
    {
        $methodName = self::getMethodNameByType($data['type']);

        switch ($methodName) {
            case self::MESSAGE_TYPE_COMMENT:
                $commentModel = Comment::whereId($data['id'])->first();
                $commentModel->baidu_audit_status = $data['label'];

                // //当前评论是待审核和百度审核是拒绝的时修改
                // $commentModel->status == Comment::STATUS_INIT
                //     && $data['label'] == BaiduVcrClient::RESULT_REJECT
                //     && $commentModel->status = Comment::STATUS_REJECT;

                // // 审核不通过调用系统消息
                // if ($data['label'] == BaiduVcrClient::RESULT_REJECT){
                //     $commentModel->reject_reason = self::AUDIT_REJECT_TIPS;
                //     // 审核不通过，需记录到系统通知接口
                //     $notice = new Notice();
                //     $times = new Times();
                //     $rejectReason = "你于".$times->utcChangeToPrc($commentModel->created_at).'发布的评论审核未获通过，已删除，原因为'.self::AUDIT_REJECT_TIPS;
                //     $notice -> system($commentModel->user_id, $rejectReason, $notice::NOTICE_SYSTEM_COMMENT);
                // }

                $return = $commentModel->save();
                break;
            case self::MESSAGE_TYPE_FEED:
                $id = $data['id'];
                $feedModel = Feed::whereId($data['id'])->first();
                if (!$feedModel) break;

                $currentStatus = self::judgeStatus($id);

                $feedModel->baidu_audit_status = $currentStatus;
                // $feedModel->audit_status == Feed::AUDIT_STATUS_INIT
                //     && $currentStatus == BaiduVcrClient::RESULT_REJECT
                //     && $feedModel->audit_status = 2;

                // // 审核不通过调用系统消息接口
                // if ($currentStatus == BaiduVcrClient::RESULT_REJECT){
                //     $feedModel->reject_reason = self::AUDIT_REJECT_TIPS;
                //     // 审核不通过，需记录到系统通知
                //     $notice = new Notice();
                //     $times = new Times();
                //     $rejectReason = "你于".$times->utcChangeToPrc($feedModel->created_at).'发布的动态审核未获通过，已删除，原因为'.self::AUDIT_REJECT_TIPS;
                //     $notice -> system($feedModel->user_id, $rejectReason, $notice::NOTICE_SYSTEM_FEEDS);
                // }

                $return = $feedModel->save();
                break;
            case self::MESSAGE_TYPE_QUESTION:
                $id = $data['id'];
                $questionModel = Question::whereId($data['id'])->first();
                if (!$questionModel) {
                    break;
                }
                $currentStatus = self::judgeStatus($id, self::MESSAGE_TYPE_QUESTION);
                $questionModel->baidu_audit_status = $currentStatus;
                $return = $questionModel->save();
                break;
            case self::MESSAGE_TYPE_ANSWER:
                $id = $data['id'];
                $answerModel = Answer::whereId($data['id'])->first();
                if (!$answerModel) {
                    break;
                }
                $currentStatus = self::judgeStatus($id, self::MESSAGE_TYPE_ANSWER);
                $answerModel->baidu_audit_status = $currentStatus;
                $return = $answerModel->save();
                break;
            default:
                $return = false;
                break;
        }

        return $return;
    }


    /**
     * 解析验证参数
     *
     * @param $data
     * @return mixed
     * @throws BaiduAuditNotifyException
     */
    private static function parseResultMessage($data)
    {
        //只接收审核完成状态的
        if ($data['status'] != BaiduVcrClient::TASK_STATUS_FINISHED) {
            $message = "只接收审核完成状态的，当前状态为：{$data['status']}";
            $errMessage = $data['error']['message'] ?? '';
            $errMessage && $message .= "，错误信息：{$errMessage}";
            throw  new BaiduAuditNotifyException($message, -2);
        }

        $source = $data['source'];
        if (!$source) {
            throw  new BaiduAuditNotifyException("通知结果source字段为空", -2);
        }

        $sourcePart = parse_url($source, PHP_URL_QUERY);
        $params = self::decryptParams($sourcePart);
        if (false === $params) {
            throw  new BaiduAuditNotifyException("通知结果已超时", -2);
        }
        if (!$params) {
            throw  new BaiduAuditNotifyException("通知结果source中缺失参数", -2);
        }

        return $params;
    }

    /**
     * 聚合百度返回的证据
     *
     * @param $baiduEvidenceList
     * @return array
     */
    public static function assembleEvidence($baiduEvidenceList)
    {

        $results = [];
        foreach ($baiduEvidenceList as $evidence) {
            $results[$evidence['type']] = [];
            foreach ($evidence['items'] as $item) {
                if (!isset($results[$evidence['type']][$item['subType']])) {
                    $results[$evidence['type']][$item['subType']] = [
                        'subType' => $item['subType'],
                        'label' => $item['label'],
                        'confidence' => $item['confidence'],
                        'evidence' => $item['evidence'] ?? '',
                        'target' => $item['target'] ?? '',
                        'extra' => $item['extra'] ?? '',
                    ];
                    if (!empty($item['highlight'])) {
                        $results[$evidence['type']][$item['subType']]['highlight'] = $item['highlight'];
                    }
                    if (isset($item['timeInSeconds'])) {
                        $results[$evidence['type']][$item['subType']]['minSecond'] = $item['timeInSeconds'];
                        $results[$evidence['type']][$item['subType']]['maxSecond'] = $item['timeInSeconds'];
                    }
                } else {
                    if (isset($item['timeInSeconds'])) {
                        $results[$evidence['type']][$item['subType']]['minSecond'] =
                            min($item['timeInSeconds'], $results[$evidence['type']][$item['subType']]['minSecond']);
                        $results[$evidence['type']][$item['subType']]['maxSecond'] =
                            max($item['timeInSeconds'], $results[$evidence['type']][$item['subType']]['maxSecond']);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * 获取审核的数据
     *
     * @param $message
     * @return mixed
     */
    public static function getAuditData($message)
    {
        $class = "\Zhiyi\Plus\Lib\BaiduAudit\Audit" . ucfirst($message['method']);
        /**
         * @var Audit $class
         */
        $class = new $class();

        return $class->getContentData($message['data']['id']);
    }


    /**
     * 推送到百度
     *
     * @param $data
     * @return bool|mixed
     * @throws
     * @throws Exception
     */
    public static function toBaidu($data, $sync = 0)
    {
        if (!$data) {
            return false;
        }

        $baiduClient = new BaiduVcrClient();

        $key = self::ENCRYPT_PARAM;
        $query = "{$key}=" . self::encryptParams(
                $data['data']['id'],
                $data['data']['type']
            );

        switch ($data['resourceType']) {
            case BaiduVcrClient::RES_TYPE_MEDIA:
                $res = $baiduClient->putMedia($data['data']['source'] . '?' . $query);
                break;
            case BaiduVcrClient::RES_TYPE_AUDIO:
                $res = $baiduClient->putAudio($data['data']['source'] . '?' . $query);
                break;
            case BaiduVcrClient::RES_TYPE_IMAGE:
                if ($sync) {
                    $res = $baiduClient->putImageSync($data['data']['source'] . '?' . $query);
                } else {
                    $res = $baiduClient->putImage($data['data']['source'] . '?' . $query);
                }
                break;
            case BaiduVcrClient::RES_TYPE_TEXT:
                $res = $baiduClient->putText($data['data']['source']);

                // 加系统敏感词判断
                $res = self::checkSensitives($data['data']['source'], $res);
                break;
            default:
                throw new Exception('待审核资源的类型错误，必须是media|audio|image|text');
        }

        return $res;
    }

    /**
     * 保存文本审核
     *
     * @param $res
     * @param $type
     * @param $id
     * @return bool
     */
    private static function saveTextEvidence($res, $type, $id)
    {
        $data = [
            'messageId' => '',
            'source' => $res['text'],
            'label' => $res['label'],
            'type' => $type,
            'id' => $id,
            'evidence' => json_encode(self::assembleEvidence($res['results'] ?? []), JSON_UNESCAPED_UNICODE),
        ];

        return self::saveEvidence($data);
    }


    /**
     * @param $id
     * @param string $flag
     * @return bool
     */
    public static function judgeStatus($id, $flag = 'feed')
    {
        switch ($flag) {
            case 'question':
                $types = BaiduAuditItem::getQuestionTypes();
                break;
            case 'answer':
                $types = BaiduAuditItem::getAnswerTypes();
                break;
            default:
                $types = BaiduAuditItem::getFeedTypes();
                break;
        }

        $typeKeys = array_keys($types);
        $models = BaiduAuditItem::query()->select('status')->whereIn('type', $typeKeys)->where('item_id', $id)->get();
        $allStatus = [
            BaiduVcrClient::RESULT_NORMAL => 1,
            BaiduVcrClient::RESULT_REVIEW => 2,
            BaiduVcrClient::RESULT_REJECT => 3,
        ];
        $currentStatus = BaiduVcrClient::RESULT_NORMAL;
        foreach ($models as $model) {
            ($allStatus[strtoupper($model->status)] ?? 0) > ($allStatus[strtoupper($currentStatus)] ?? 0)
            && $currentStatus = $model->status;
        }

        return $currentStatus;
    }

    /**
     * 修复未百度审核的feed或comment
     *
     * @return array
     * @throws RabbitMqClientException
     */
    public static function fixNotAuditItem()
    {
        $feedIdList = self::getNotAuditFeeds();
        $commentIdList = self::getNotAuditComments();
        $questionIdList = self::getNotAuditQuestions();
        $answerIdList = self::getNotAuditAnswers();

        if ($feedIdList) {
            foreach ($feedIdList as $feedId) {
                self::publishData(self::MESSAGE_TYPE_FEED, [
                    'id' => $feedId,
                ]);
            }
        }

        if ($questionIdList) {
            foreach ($questionIdList as $questionId) {
                self::publishData(self::MESSAGE_TYPE_QUESTION, [
                    'id' => $questionId,
                ]);
            }
        }

        if ($answerIdList) {
            foreach ($answerIdList as $answerId) {
                self::publishData(self::MESSAGE_TYPE_ANSWER, [
                    'id' => $answerId,
                ]);
            }
        }

        if ($commentIdList) {
            foreach ($commentIdList as $commentId) {
                self::publishData(self::MESSAGE_TYPE_COMMENT, [
                    'id' => $commentId,
                ]);
            }
        }

        return [
            'feedIdList' => $feedIdList,
            'commentIdList' => $commentIdList,
            'questionIdList' => $questionIdList,
            'answerIdList' => $answerIdList,
        ];
    }

    /**
     * 获取未审核的feedId list
     *
     * @return array
     */
    public static function getNotAuditComments()
    {
        $comments = Comment::where('baidu_audit_status', '')
            ->select('id')
            ->where('status', 0)
            ->where('created_at', '>=', date("Y-m-d", strtotime("-3 day")))
            ->limit(100)
            ->get()
            ->toArray();

        if ($comments) {
            return array_column($comments, 'id');
        }

        return [];
    }

    /**
     * 获取未审核的feedId list
     *
     * @return array
     */
    public static function getNotAuditFeeds()
    {
        $feeds = Feed::select('id')
            ->where('baidu_audit_status', '')
            ->where('audit_status', 0)
            ->where('created_at', '>=', date("Y-m-d", strtotime("-3 day")))
            ->limit(100)
            ->get()
            ->toArray();

        if ($feeds) {
            return array_column($feeds, 'id');
        }

        return [];
    }

    /**
     * @return array
     */
    public static function getNotAuditQuestions()
    {
        $questions = Question::select('id')
            ->where('baidu_audit_status', '')
            ->where('audit_status', 0)
            ->where('created_at', '>=', date("Y-m-d", strtotime("-3 day")))
            ->limit(100)
            ->get()
            ->toArray();

        if ($questions) {
            return array_column($questions, 'id');
        }

        return [];
    }

    public static function getNotAuditAnswers()
    {
        $anwers = Answer::select('id')
            ->where('baidu_audit_status', '')
            ->where('audit_status', 0)
            ->where('created_at', '>=', date("Y-m-d", strtotime("-3 day")))
            ->limit(100)
            ->get()
            ->toArray();

        if ($anwers) {
            return array_column($anwers, 'id');
        }

        return [];
    }


    /**
     * @param $redisKey
     * @param $member
     * @return int
     */
    public static function addPushRecord($redisKey, $member)
    {
        return app()->redis->sadd($redisKey, [$member])
            && app()->redis->expire($redisKey, self::REDIS_PUSH_RECORD_KEY_TTL);
    }

    /**
     * @param $data
     * @return bool
     */
    public static function removePushRecord($data)
    {
        $methodName = self::getMethodNameByType($data['type']);
        $id = $data['id'];
        $type = $data['type'];
        $source = explode('?', $data['source'])[0] ?? '';

        $key = self::getPushRecordKey($methodName, $id);
        $member = self::getPushRecordMember($id, $type, $source);

        return app()->redis->sismember($key, $member) && app()->redis->srem($key, $member);
    }

    /**
     * 判断当前数据的是否还存在在redis中，当清空时候认为是最后一条
     *
     * @param $data
     * @return int
     */
    public static function pushRecordIsEmpty($data)
    {
        $methodName = self::getMethodNameByType($data['type']);
        $id = $data['id'];
        $key = self::getPushRecordKey($methodName, $id);

        return !app()->redis->exists($key);
    }

    /**
     * @param $methodName
     * @param $id
     * @return string
     */
    public static function getPushRecordKey($methodName, $id)
    {
        return sprintf(self::REDIS_PUSH_RECORD_KEY, $methodName, $id);
    }

    /**
     * 转化member
     *
     * @param $type
     * @param $itemId
     * @param $source
     * @return string
     */
    private static function getPushRecordMember($itemId, $type, $source)
    {
        return md5(sprintf("%s:%s:%s", $itemId, $type, $source));
    }


    /**
     * 将审核内容转化成消息方式
     *
     * @param $typeId
     * @return string
     */
    public static function getMethodNameByType($typeId)
    {
        $name = '';
        switch (1) {
            case $typeId == BaiduAuditItem::TYPE_COMMENT:
                $name = self::MESSAGE_TYPE_COMMENT;
                break;
            case in_array($typeId, $feedTypes = array_keys(BaiduAuditItem::getFeedTypes())):
                $name = self::MESSAGE_TYPE_FEED;
                break;
            case in_array($typeId, $questionTypes = array_keys(BaiduAuditItem::getQuestionTypes())):
                $name = self::MESSAGE_TYPE_QUESTION;
                break;
            case in_array($typeId, $answerTypes = array_keys(BaiduAuditItem::getAnswerTypes())):
                $name = self::MESSAGE_TYPE_ANSWER;
                break;
            default:
                break;
        }

        return $name;
    }

    /**
     *参数加密
     *
     * @param $id
     * @param $type
     * @return string
     */
    public static function encryptParams($id, $type)
    {
        //$random = Str::random(self::ENCRYPT_RANDOM_LEN);
        //$now = now()->timestamp;
        return sprintf("%s-%s", $id, $type);

        //return Crypt::encryptString($query);
    }

    /**
     * @param $payload
     * @return array|bool
     */
    public static function decryptParams($payload)
    {
        //$payload = Crypt::decryptString($payload);
        parse_str($payload, $params);
        $key = self::ENCRYPT_PARAM;
        if (!isset($params[$key])) {
            return [];
        }
        $params = $params[$key];

        //        $len = self::ENCRYPT_RANDOM_LEN;
        //        $expires = substr($params, $len, 10);
        //
        //        if (!$expires || strlen($expires) != 10 || (now()->timestamp - $expires > 3600 *24)) {
        //            return false;
        //        }
        //
        //        $query = (string) substr($params, $len + 10);
        list($id, $type) = explode('-', $params);

        if (!$id || !$type) {
            return [];
        }

        return [
            'type' => $type,
            'id' => $id,
        ];
    }

    /**
     * 检查是否在系统敏感词内
     *
     * @param string $source
     * @param array $data
     * @return void
     */
    public static function checkSensitives($source, $data)
    {
        $sensitive = Sensitive::check($source);
        if ($sensitive) {
            // 如果有审核结果的，更改审核结果
            if (isset($data['label'])) {
                $data['label'] == BaiduVcrClient::RESULT_NORMAL && $data['label'] = BaiduVcrClient::RESULT_REVIEW;
            } else {
                $data['label'] = BaiduVcrClient::RESULT_REVIEW;
            }

            $data['results'][] = [
                'items' => [
                    [
                        'confidence' => 99,
                        'subType' => 'sensitive',
                        'label' => 'REJECT',
                        'extra' => $sensitive['word'],
                        'highlight' => $sensitive['highlight'],
                    ]
                ],
                'type' => 'bad_behavior'
            ];
        }

        return $data;
    }
}
