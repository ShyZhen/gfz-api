<?php


namespace Zhiyi\Plus\Lib\AlibabaCloud;


use Exception;

class BaseService
{
    /**
     * @var string accessKey and accessSecret
     */
    protected $ak;
    protected $sk;

    /**
     * DypnsService constructor.
     * @param $ak
     * @param $sk
     * @throws Exception
     */
    public function __construct($ak, $sk)
    {
        if (!$ak || !$sk) {
            throw new Exception("服务参数配置为空！");
        }

        $this->ak = $ak;
        $this->sk = $sk;
    }
}
