<?php


namespace Zhiyi\Plus\Lib\BaiduAudit;


abstract class Audit
{

    /**
     * @param $id
     * @return array
     * `
     * [
     *  [
     *   'type' => ‘text|media|audio|image’，
     *   'data' => [
     *      'source' => 'text|mediaUrl|audioUrl|imageUrl' ,
     *   ]
     *  ],
     *  ...
     * ]
     * `
     */
    abstract  public function  getContentData($id);


}
