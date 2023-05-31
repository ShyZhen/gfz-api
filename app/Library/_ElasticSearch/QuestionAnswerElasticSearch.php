<?php

/**
 *  问答索引，完成写入和搜索
 *
 * @author  lin
 * @date 2022-09-22 
 **/

namespace Zhiyi\Plus\Lib\ElasticSearch;

use stdClass;
use Symfony\Component\Console\Question\Question;
use Zhiyi\Plus\Lib\ElasticSearch\Base\ElasticSearch;
use Zhiyi\Plus\Services\AnswerService;
use Zhiyi\Plus\Services\MakeUrl;
use Zhiyi\Plus\Services\QuestionService;

use function GuzzleHttp\Promise\queue;

class QuestionAnswerElasticSearch extends ElasticSearch
{

    public $indexKey = 'question_answer';

    public static $size = 20;



    /**
     *  获取index
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexKey;
    }

    /**
     *
     * @return void
     */
    public function createIndex()
    {
    }


    /**
     * 创建或更新文档
     *
     * @param array $params
     * @return mixed
     */
    public function createOrUpdateDoc($params)
    {
        if (empty($params['body'])) {
            return false;
        }

        $routing = $params['routing'] ?? '';
        if ($this->existsDoc($params['id'], $routing)) {
            $params = [
                'index' => $this->index,
                'id' => $params['id'],
                'refresh' => true,
                'body' => [
                    'doc' => $params['body']
                ],
            ];
            $routing && $params['routing'] = $routing;

            $response = $this->esClient->update($params);
        } else {
            $params = [
                'index' => $this->index,
                'id' => $params['id'],
                'refresh' => true,
                'body' => $params['body']
            ];
            $routing && $params['routing'] = $routing;

            $response = $this->esClient->index($params);
        }

        return $response;
    }

    /**
     * 自定义搜索
     *
     * @param array $word
     * 
     * @return array
     */
    public function customSearch($keyword, $page = 1, $size = 20)
    {
        // 第一步，查出符合条件的question_id及总数
        $query = [
            "bool" => [
                "filter" => [
                    [
                        "bool" => [
                            "should" => [
                                [
                                    'bool' => [
                                        'must' => [
                                            "match_phrase" => ["text" => $keyword],
                                        ],
                                        'must_not' => [
                                            'exists' => ['field' => 'like_count'] // 问题没有这个字段
                                        ]
                                    ]
                                ],
                                [
                                    "has_child" => [
                                        "type" => 'answer',
                                        "query" => ["match_phrase" => ["text" => $keyword]],
                                    ],
                                ],
                            ]
                        ]
                    ],
                ],
                // 'filter' => [
                //     'bool' => [
                //         'must' => [
                //             [
                //                 "has_child" => [
                //                     "type" => 'answer',
                //                     "query" => ["match_all" => new stdClass()],
                //                     'min_children' => 0, // 最少一个
                //                 ]
                //             ],
                //         ]
                //     ]
                // ]
            ],
        ];

        // 总数
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => $query
            ],

        ];
        $response = $this->esClient->count($params);
        if (empty($response['count'])) {
            return ['total' => 0, 'data' => []];
        }
        $total = intval($response['count']);

        $from = ($page - 1) * $size;
        if ($from >= $total) {
            return ['total' => $total, 'data' => []];
        }
        $params = [
            'from' => $from,
            'size' => $size,
            'index' => $this->index,
            'body' => [
                '_source' => ["include" => ['text']],
                'query' => $query,
                'sort' => [
                    // '_score' => [
                    //     'order' => 'desc',
                    // ],
                    'created_at' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];
        $response = $this->esClient->search($params);
        if (empty($response['hits']['hits'])) {
            return ['total' => $total, 'data' => []];
        }

        $questions = $questionIds = [];
        foreach ($response['hits']['hits'] as $v) {
            $questions[] = [
                'id' => ltrim($v['_id'], 'question_'),
                'title' => $v['_source']['text'],
            ];
            $questionIds[] = ltrim($v['_id'], 'question_');
        }

        // 第二步， 遍历每个question_id,找出点赞数最高的答案
        $answerIds = [];
        if ($questions) {
            foreach ($questions as &$q) {
                $q['best_answer'] = $this->getAnswerByQuestionId('question_' . $q['id'], $keyword);
                isset($q['best_answer']['id']) && $answerIds[] = $q['best_answer']['id'];
            }
            unset($q);
        }

        // 第三步，按优先级排序（问题+答案都有关键词 > 问题有关键词 > 答案有关键词，同类结果按时间倒序）
        $data = $one = $two = $three = [];
        if ($questions) {
            foreach ($questions as $q) {
                $questionHasKeyword = mb_strpos($q['title'], $keyword) !== false;
                if ($questionHasKeyword && $q['best_answer']) { // 问题+答案都有关键词
                    $one[] = $q;
                } elseif ($questionHasKeyword) { // 问题有关键词
                    $two[] = $q;
                } else {
                    $three[] = $q;
                }
            }
            $data = array_merge($one, $two, $three);
        }

        // 补充其它信息
        if ($questionIds) {
            $service = new QuestionService();
            $qData = $service->querySimple($questionIds);
            $_qData = [];
            foreach ($qData as $v) {
                $_qData[$v['id']] = $v;
            }
            unset($aQata);
        }
        if ($answerIds) {
            $service  = new AnswerService();
            $aData = $service->querySimple($answerIds);
            $_aData = [];
            foreach ($aData as $v) {
                $_aData[$v['id']] = $v;
            }
            unset($aData);
        }


        foreach ($data as &$v) {
            if (isset($_qData[$v['id']])) {
                $v['images'] = $_qData[$v['id']]['images'];
                $v['video'] = $_qData[$v['id']]['video'];
                $v['user'] = $_qData[$v['id']]['user'];
            }
            if (!empty($v['best_answer']) && !empty($_aData[$v['best_answer']['id']])) {
                $v['best_answer']['user'] = $_aData[$v['best_answer']['id']]['user'];
                $v['url'] = MakeUrl::answerUrl($v['best_answer']['id']);
            } else {
                $v['url'] = MakeUrl::questionUrl($v['id']);
            }
        }

        return ['total' => $total, 'data' => $data];
    }

    /**
     * 获取问题下满足条件的一个答案
     *
     * @param int $id
     * @param string $keyword
     * 
     * @return array
     */
    protected function getAnswerByQuestionId($id, $keyword)
    {
        // 先查匹配关键词+最高赞的答案
        $params = [
            'size' => 1,
            'index' => $this->index,
            'body' => [
                '_source' => ["include" => ['text']],
                'query' => [
                    'bool' => [
                        'filter' => [
                            "bool" => [
                                "must" => [
                                    [
                                        "match_phrase" => ["text" => $keyword]
                                    ],
                                    [
                                        "parent_id" => [
                                            "type" => 'answer',
                                            "id" => $id,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ],
                'sort' => [
                    'like_count' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];
        $response = $this->esClient->search($params);

        // 没结果的话，再查一次不匹配关键词+最高赞的答案
        if (empty($response['hits']['hits'][0])) {
            $params = [
                'size' => 1,
                'index' => $this->index,
                'body' => [
                    '_source' => ["include" => ['text']],
                    'query' => [
                        'bool' => [
                            'filter' => [
                                "bool" => [
                                    "must" => [
                                        [
                                            "parent_id" => [
                                                "type" => 'answer',
                                                "id" => $id,
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'sort' => [
                        'like_count' => [
                            'order' => 'desc',
                        ],
                    ],
                ],
            ];
            $response = $this->esClient->search($params);
        }

        if (empty($response['hits']['hits'][0])) {
            return null;
        }

        $hit = $response['hits']['hits'][0];

        // 只返回匹配到关键词的第一个段落，否则返回去除格式的文本
        $pattern = '#<p>(?:(?<!</p>).)*' . $keyword . '.*?</p>#';
        preg_match($pattern, $hit['_source']['text'], $matches);
        $text = !empty($matches[0]) ? strip_tags($matches[0]) : strip_tags($hit['_source']['text']);
        $text = mb_substr($text, 0, 50);

        $id = ltrim($hit['_id'], 'answer_');

        return ['id' => $id, 'summary' => $text];
    }
}
